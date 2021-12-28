<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\AmplopExports;
use App\Exports\InvoiceExports;
use App\Exports\PackingListExports;
use App\Exports\SuratJalanExports;
use App\Models\InvoiceSuratJalan;
use App\Models\PurchaseOrderLog;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\InvoiceLog;
use App\Models\Perusahaan;
use App\Models\Provinsi;
use App\Models\Expedisi;
use App\Models\ExpedisiVia;
use App\Models\Country;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Gudang;
use App\Models\Sales;
use App\Models\City;
use Carbon\Carbon;
use DB;
use PDF;
use Excel;

class InvoiceController extends Controller
{

    protected $original_column = array(
        1 => "product_code"
    );

    public function statusFilter(){
        $value = array('99'=>'Semua','1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
    }

    public function jenisharga(){
        $value = array('1'=>'Harga Normal', '2'=>'Harga Expor');
        return $value;
    }

    public function actionstatus(){
        $value = array('0'=>'BARU', '1'=>'PROSESS', '2' => 'DITOLAK');
        return $value;
    }

    public function statuspo(){
        $value = array('1' => 'PROSES INVOICE', '2' => 'DITOLAK');
        return $value;
    }

    public function statusgudang(){
        $value = array('0'=>'DIPROSES', '1'=>'SELESAI', '2' => 'DITOLAK');
        return $value;
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }

    private function cekExist($column,$var,$id){
        $cek = Invoice::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function cut_text($string){
        $value = substr($string, 0, 3);
        return $value;
    }

    private function gensuratjalan($tanggal, $data){
        $POall = InvoiceSuratJalan::orderBy('id', 'DESC')->first();
        if($POall != null || $POall > 0){
            $idocde = $POall->id + 1;
        }else{
            $idocde = 1;
        }
        $data = Invoice::where('id',$data)->first();
        $nmperusahaan = Perusahaan::find($data->perusahaan_id);
        $namapt = substr($nmperusahaan->name, 0, 3);
        $nama = explode(' ', substr($nmperusahaan->name, 4));
        $singkatan = "";
        foreach ($nama as $key => $value) {
            $singkatan .= strtoupper(substr($value,0,1));
        }

        $result_nota = sprintf("%'.05d", $idocde).'/'.$namapt.$singkatan.'/'.date('m', strtotime($tanggal)).'/'.date('y', strtotime($tanggal));
        return $result_nota;
    }

    public function index(Request $request){
        $dt = new Carbon();
        if ($request->ajax()) {
            $request->session()->put('filter_tgl_start_invoice', $request->tgl_start);
            $request->session()->put('filter_tgl_end_invoice', $request->tgl_end);
            $request->session()->put('filter_perusahaan_invoice', $request->perusahaan);
            $request->session()->put('filter_customer_invoice', $request->filter_customer);
            $request->session()->put('filter_invoice', $request->filter_invoice);
            $request->session()->put('filter_sales_invoice', $request->sales);
        }

        // $tgl_start = date('d-m-Y', strtotime(' - 30 days'));;
        // $tgl_end = date('d-m-Y');
        $sales = Sales::all();
        $member = Member::all();
        $perusahaan = Perusahaan::all();
        $selectedperusahaan = '';
        $selectedsales = '';
        // dd(session('filter_sales_invoice'));
        if(session('filter_perusahaan_invoice')==""){
            $filter_perusahaan_invoice = '';
        }else{
            $filter_perusahaan_invoice = session('filter_perusahaan_invoice');
        }

        if(session('filter_customer_invoice')==""){
            $filter_customer_invoice = '';
        }else{
            $filter_customer_invoice = session('filter_customer_invoice');
        }

        if(session('filter_invoice')==""){
            $filter_invoice = '';
        }else{
            $filter_invoice = session('filter_invoice');
        }


        if(session('filter_sales_invoice')==""){
            $filter_sales_invoice =[];
        }else{
            $filter_sales_invoice = session('filter_sales_invoice');
        }


        if(session('filter_tgl_start_invoice')==""){
            $filter_tgl_start_invoice = date('d-m-Y', strtotime(' - 30 days'));
        }else{
            $filter_tgl_start_invoice   = session('filter_tgl_start_invoice');
        }

        if(session('filter_tgl_end_invoice')==""){
            $filter_tgl_end_invoice = date('d-m-Y');
        }else{
            $filter_tgl_end_invoice = session('filter_tgl_end_invoice');
        }

        $invoices = Invoice::select('id', 'no_nota', 'purchase_no', 'member_id','sales_id','perusahaan_id','total',
                                    'discount', 'pay_status','created_at','create_user','subtotal','expedisi','via_expedisi','dateorder');
        // $invoices->where('pay_status', 0);
        // $invoices->where('flag_tanda_terima', 0);

        if($filter_tgl_start_invoice != "" && $filter_tgl_end_invoice !="" ){
            $invoices->whereDate('dateorder','>=',date('Y-m-d', strtotime($filter_tgl_start_invoice)));
            $invoices->whereDate('dateorder','<=',date('Y-m-d', strtotime($filter_tgl_end_invoice)));
        }

        if($filter_perusahaan_invoice != ""){
            $invoices->where('perusahaan_id', $filter_perusahaan_invoice);
        }

        if($filter_customer_invoice != ""){
            $invoices->where('member_id', $filter_customer_invoice);
        }

        if($filter_invoice != ""){
            $invoices->where('no_nota','LIKE',"%{$filter_invoice}%");
        }
        if( $filter_sales_invoice != []){
            $invoices->whereIn('sales_id',  $filter_sales_invoice);
        }

        $invoices->orderBy('id', 'DESC');
        $invoice = $invoices->paginate(30);

        foreach ($invoice as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $perusahaan_inv = Perusahaan::find($result->perusahaan_id);
            $member_inv = Member::where('id', $result->member_id)->first();
            $sales_inv = Sales::where('id', $result->sales_id)->first();
            $purchase = PurchaseOrder::where('no_nota', $result->purchase_no)->first();
            $expedisi = Expedisi::find($result->expedisi);
            $expedisivia = ExpedisiVia::find($result->via_expedisi);

            $discount = round($result->subtotal*($result->discount/100));
            $afterdiscount = round($result->subtotal - $discount);
            $ppn = round($afterdiscount*(10/100));
            $total = $afterdiscount + $ppn;

            // LOG USER
            $loginvoice= PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%diproses GUDANG%')->first();
            $loggudang = PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%diproses INVOICE AKHIR%')->first();


            $result->enc_id             = $enc_id;
            $result->member_name        = $member_inv->name;
            $result->member_city        = $member_inv->city;
            $result->perusahaan_name    = $perusahaan_inv->name;
            $result->sales_name         = $sales_inv->name;
            $result->nama_expedisi      = $expedisi->name;
            $result->nama_expedisi_via  = $expedisivia?$expedisivia->name:'-';
            $result->total              = $total;
            $result->created_by         = $purchase->createdon;
            $result->gudang             = $loggudang?$loggudang->create_user:'-';
            $result->loginv             = $loginvoice?$loginvoice->create_user:'-';
            // $result->create             = date("d M Y",strtotime($result->created_at));
            $result->create             = date("d M Y",strtotime($result->dateorder));
            $result->create_invo        = Carbon::parse($result->created_at)->diffForHumans();
        }
        // return response()->json([
        //     'data' => $invoice,
        // ]);
        if ($request->ajax()) {
            $request->session()->put('filter_tgl_start_invoice', $request->tgl_start);
            $request->session()->put('filter_tgl_end_invoice', $request->tgl_end);
            $request->session()->put('filter_perusahaan_invoice', $request->perusahaan);
            $request->session()->put('filter_customer_invoice', $request->filter_customer);
            $request->session()->put('filter_invoice', $request->filter_invoice);
            $request->session()->put('filter_sales_invoice', $request->sales);

            return view('backend/invoice/filter',compact('invoice','sales','member','perusahaan','filter_perusahaan_invoice','filter_customer_invoice','filter_invoice','filter_sales_invoice','filter_tgl_start_invoice','filter_tgl_end_invoice'))->render();
        }
        return view('backend/invoice/index', compact('invoice','sales','member','perusahaan','filter_perusahaan_invoice','filter_customer_invoice','filter_invoice','filter_sales_invoice','filter_tgl_start_invoice','filter_tgl_end_invoice'));
    }

    public function detail(Request $request){
        $invoice = Invoice::find($request->enc_id);
        $invoicedetail = InvoiceDetail::where('invoice_id', $request->enc_id)->get();

        $member     = Member::find($invoice->member_id);
        $perusahaan = Perusahaan::find($invoice->perusahaan_id);
        $prov       = Provinsi::where('name', $member->prov)->first();
        if($prov){
            $negara      = Country::find($prov->country_id);
            if($negara){
                $nama_negara = $negara->name;
            }else{
                $nama_negara = '-';
            }
        }else{
            $negara         = '-';
            $nama_negara    = '-';
        }

        $discount = round($invoice->subtotal*($invoice->discount/100));
        $afterdiscount = round($invoice->subtotal - $discount);
        $ppn = round($afterdiscount*(10/100));
        $total = $afterdiscount + $ppn;

        $header = '';
        $header.= '<tr>';
            $header.='<th>Produk</th>';
            $header.='<th>Harga Satuan</th>';
            $header.='<th>Qty</th>';
            $header.='<th>Include Diskon</th>';
            $header.='<th>Harga Total</th>';
        $header.= '</tr>';

        $html = '';
        foreach ($invoicedetail as $key => $value) {
            $totaldiscount = ($value->discount/100)*($value->price * $value->qty);
            $unitdiskon  = $value->price*($value->discount/100);

            $product = Product::find($value->product_id);
            $value->total_discount = round($value->ttl_price - $totaldiscount);
            $value->unit_discount = round($value->price - $unitdiskon);
            if($value->discount != NULL){
                $value->discount = $value->discount;
            }else{
                $value->discount = 0;
            }

            $html.='<tr>';
                $html.='<td>'.$value->product_name.'<br/><p style="color:#1c84c6">'.$value->product_code.'</p></td>';
                $html.='<td>'.number_format(($value->price-round($value->discount/100 *$value->price)) , 0, '', '.').',00</td>';
                $html.='<td>'.$value->qty.' '.$value->satuan.'</td>';
                $html.='<td>'.$value->discount.' %</td>';
                $html.='<td>'.number_format($value->ttl_price, 0, '', '.').',00</td>';
            $html.='</tr>';


        }
        $detailtable[]  = $html;
        return response()->json([
            'invo'    => [
                'member'        => [
                    'name'          => $member->name,
                    'contact'       => $member->phone==null?'-':$member->phone,
                    'alamat'        => $member->address==null?'-':$member->address,
                    'email'         => $member->email,
                    'alamat_toko'   => $member->address_toko,
                    'ktp'           => $member->ktp,
                    'bank'          => $member->bank_name==null?'-':$member->bank_name,
                    'rek'           => $member->no_rek==null?'-':$member->no_rek,
                    'city'          => $member->city,
                    'negara'        => $nama_negara
                ],
                'perusahaan'    => [
                    'name'          => $perusahaan->name,
                    'alamat'        => $perusahaan->address,
                    'contact'       => $perusahaan->telephone,
                    'city'          => $perusahaan->city,
                ],
                'invoice'       => [
                    'id'                => $invoice->id,
                    'no_nota'           => $invoice->no_nota,
                    'memo'              => $invoice->memo,
                    'colly'              => $invoice->colly,
                    'date_order'        => date("d M Y",strtotime($invoice->dateorder)),
                    'diskon'            => $invoice->discount==null?0:$invoice->discount,
                    'sub_total'         => number_format($invoice->subtotal, 0, '', '.').',00',
                    'total_diskon'      => number_format($discount, 0, '', '.').',00',
                    'setelah_diskon'    => number_format($afterdiscount, 0, '', '.').',00',
                    'ppn'               => number_format($ppn, 0, '', '.').',00',
                    'total'             => number_format($total, 0, '', '.').',00',
                ]
            ],
            'invoice_detail'    => $detailtable,
            'header'            => $header,
            'prov'              => $prov
        ]);
    }

    public function pengiriman_detail(Request $request){
        $invoice = Invoice::find($request->enc_id);
        $expedisi = Expedisi::all();
        $selectedExpedisi = $invoice->expedisi;

        $listexpedisi = '';
        $listexpedisi.= '<select class="exspedisi form-control" id="exspedisi">';
        foreach($expedisi as $key => $value){
            if($value->id == $selectedExpedisi){
                $selecteddetail = 'selected';
            }else{
                $selecteddetail = '';
            }

            $listexpedisi.='<option value="'.$value->id.'"'.$selecteddetail.'>'.ucfirst($value->name).'</option>';
        }
        $listexpedisi.='</select>';

        if($invoice->delivery_date==null || $invoice->delivery_date == ""){
             $tgl = date('d-m-Y');
         }else{
            $tgl = date('d-m-Y',strtotime($invoice->delivery_date));
         }

        return response()->json([
            'expedisi' => $listexpedisi,
            'tgl'      => $tgl,
            'resi_no'  => $invoice->resi_no
        ]);
    }

    public function simpan_pengiriman(Request $request){
        $invoice = Invoice::find($request->enc_id);
        if($invoice){
            $invoice->delivery_date = date('Y-m-d', strtotime($request->tgl_kirim));
            $invoice->expedisi      = $request->expedisi;
            $invoice->resi_no       = $request->resi_no;
            $invoice->save();

            $dt = new Carbon();
            $invoicelog = new InvoiceLog();
            $invoicelog->user_id        = auth()->user()->id;
            $invoicelog->invoice_id     = $invoice->id;
            $invoicelog->keterangan     = auth()->user()->username." telah mengupdate pengiriman pada nota ".$invoice->no_nota;
            $invoicelog->create_date    = $dt->toDateTimeString();
            $invoicelog->create_user    = auth()->user()->username;
            $invoicelog->save();

            $json_data = array(
                "success"         => TRUE,
                "message"         => 'Data berhasi di kirim.'
            );
        }else{
            $json_data = array(
                "success"         => false,
                "message"         => 'Terjadi kesalahan data.'
            );
        }

        return json_encode($json_data);

    }

    public function simpan_memo_colly(Request $request){

        $invoice = Invoice::find($request->enc_id);
        if($invoice){
            if($request->type=='1'){
                $invoice->colly         = $request->colly;
                $ket = auth()->user()->username." telah mengupdate colly pada nota ".$invoice->no_nota;
            }else{
                $invoice->memo          = $request->memo;
                $ket = auth()->user()->username." telah mengupdate memo pada nota ".$invoice->no_nota;
            }
            $invoice->save();

            $dt = new Carbon();
            $invoicelog = new InvoiceLog();
            $invoicelog->user_id        = auth()->user()->id;
            $invoicelog->invoice_id     = $invoice->id;
            $invoicelog->keterangan     = $ket;
            $invoicelog->create_date    = $dt->toDateTimeString();
            $invoicelog->create_user    = auth()->user()->username;
            $invoicelog->save();

            $json_data = array(
                "success"         => true,
                "message"         => 'Data berhasil diperbarui.'
            );
        }else{
            $json_data = array(
                "success"         => false,
                "message"         => 'Terjadi kesalahan data.'
            );
        }

        return json_encode($json_data);

    }

    public function filter_data(Request $request){
        $tgl_start = $request->tgl_start;
        $tgl_end = $request->tgl_end;
        $sales = Sales::all();
        $member = Member::all();
        $perusahaan = Perusahaan::all();
        $selectedperusahaan = '';

        $request->session()->put('filter_tgl_start_invoice', $request->tgl_start);
        $request->session()->put('filter_tgl_end_invoice', $request->tgl_end);
        $request->session()->put('filter_perusahaan_invoice', $request->perusahaan);
        $request->session()->put('filter_customer_invoice', $request->filter_customer);
        $request->session()->put('filter_invoice', $request->filter_invoice);
        $request->session()->put('filter_sales_invoice', $request->sales);
        // dd($request->sales);

        $query = Invoice::select('id', 'no_nota', 'purchase_no', 'member_id','sales_id','perusahaan_id','total',
                                    'discount', 'pay_status','created_at','create_user');

        if($request->tgl_start != ""){
            $query->whereDate('created_at','>=',date('Y-m-d', strtotime($tgl_start)));
            $query->whereDate('created_at','<=',date('Y-m-d', strtotime($tgl_end)));
        }

        if($request->perusahaan != ""){
            $query->where('perusahaan_id', $request->perusahaan);
        }

        if($request->customer != ""){
            $query->where('member_id', $request->customer);
        }

        if($request->invoice != ""){
            $query->where('no_nota', $request->invoice);
        }


        if($request->sales != ""){
            $query->whereIn('sales_id', $request->sales);
        }

        $query->where('pay_status', 0);
        $query->where('flag_tanda_terima', 0);
        $query->orderBy('id', 'DESC');
        $invoice = $query->paginate(1);
        foreach ($invoice as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $perusahaan_inv = Perusahaan::find($result->perusahaan_id);
            $member_inv = Member::where('id', $result->member_id)->first();
            $sales_inv = Sales::where('id', $result->sales_id)->first();
            $purchase = PurchaseOrder::where('no_nota', $result->purchase_no)->first();

            $discount = $result->total*($result->discount/100);
            $afterdiscount = round($result->total - $discount);
            $ppn = round($afterdiscount*(10/100));
            $total = $afterdiscount + $ppn;
            // LOG USER
            $loggudang = PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%GUDANG%')->first();

            $result->enc_id             = $enc_id;
            $result->member_name        = $member_inv->name;
            $result->member_city        = $member_inv->city;
            $result->perusahaan_name    = $perusahaan_inv->name;
            $result->sales_name         = $sales_inv->name;
            $result->total              = $total;
            $result->created_by         = $purchase->createdon;
            $result->gudang             = $loggudang->create_user;
            $result->create             = date("d M Y",strtotime($result->created_at));
            $result->create_invo        = Carbon::parse($result->created_at)->diffForHumans();
        }
        // return response()->json([
        //     'data' => $request->all(),
        // ]);
        return view('backend/invoice/filter', compact('invoice','tgl_start','tgl_end','sales','member','perusahaan'));
    }

    public function menu_invoice_list(Request $request){
        $invoice = Invoice::find($request->enc_id);
        $list = '';
        $list.='<a href="#!" class="btn bg-warning" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="print"><i class="fa fa-print"></i>&nbsp; Print</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-success" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="excel"><i class="fa fa-file-excel-o"></i>&nbsp; Excel</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-danger" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="pdf"><i class="fa fa-file-pdf-o"></i>&nbsp; PDF</a>';

        return response()->json([
            'list' => $list,
            'colly' => $invoice->colly
        ]);
    }

    // Menu Invoice
    public function menu_invoice($menu, $idinv){

        $dt = new Carbon();
        $invoicequery = Invoice::select('invoice.id','invoice.no_nota','invoice.purchase_no','invoice.memo','invoice.colly','invoice.member_id','invoice.sales_id','invoice.perusahaan_id','invoice.discount','invoice.subtotal','invoice.total','invoice.note','invoice.expedisi','invoice.via_expedisi','invoice.created_at','member.id as mid','member.name as mname','member.email as memail','member.address as malamat','member.address_toko as mtoko','member.city as mcity','member.city_id as mcity_id','member.phone as mphone','member.ktp as mktp','member.prov as mprov','sales.id as sid','sales.name as sname','perusahaan.id as pid','perusahaan.name as pname','perusahaan.address as palamat','perusahaan.city as pcity','perusahaan.telephone as pphone','perusahaan.rek_no as prekno','perusahaan.bank_name as pbank','expedisi.id as exid','expedisi.name as exname','expedisi_via.id as viaid','expedisi_via.name as vianame','dateorder');
        $invoicequery->join('member','member.id','invoice.member_id');
        $invoicequery->join('sales','sales.id','invoice.sales_id');
        $invoicequery->join('perusahaan','perusahaan.id','invoice.perusahaan_id');
        $invoicequery->join('expedisi','expedisi.id','invoice.expedisi');
        $invoicequery->leftJoin('expedisi_via','expedisi_via.id','invoice.via_expedisi');
        $invoicequery->where('invoice.id', $idinv);
        $invoice        = $invoicequery->first();

        $perusahaan     = Perusahaan::find($invoice->perusahaan_id);
        $kodeperusahaan = $perusahaan->kode;
        $discount       = round($invoice->subtotal*($invoice->discount/100));

        $afterdiscount = round($invoice->subtotal - $discount);
        $ppn           = round($afterdiscount*(10/100));
        $total         = $afterdiscount + $ppn;

        $invoice->diskon        = $discount;
        $invoice->harga_diskon  = $afterdiscount;
        $invoice->pajak         = $ppn;
        $invoice->grandtotal    = $total;
        // $invoice->tanggal       = $invoice->created_at->isoFormat('dddd, D MMMM Y');
        // $invoice->tanggal       = $invoice->dateorder->isoFormat('dddd, D MMMM Y');
        $invoice->tanggal       =  $dt->parse($invoice->dateorder)->isoFormat('dddd, D MMMM Y');



        $invoicedetailquery = InvoiceDetail::select('*');
        $invoicedetailquery->where('invoice_id', $invoice->id);
        $invoicedetailquery->where('qty','!=',0);
        $invoicedetail = $invoicedetailquery->get();

        foreach ($invoicedetail as $key => $value) {
            $hargadiskon        = round($value->price*($value->discount/100));
            $value->hargadiskon = $value->price - $hargadiskon;
        }

        $tanggal = $dt->now()->isoFormat('dddd, D MMMM Y');
        $title   = 'Invoice '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

        $dt = new Carbon();
        $invoicelog = new InvoiceLog();
        $invoicelog->user_id        = auth()->user()->id;
        $invoicelog->invoice_id     = $invoice->id;
        $invoicelog->keterangan     = auth()->user()->username." telah mencetak data invoie No ".$invoice->no_nota;
        $invoicelog->create_date    = $dt->toDateTimeString();
        $invoicelog->create_user    = auth()->user()->username;
        $invoicelog->save();

        if($menu == 'pdf'){
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '12',
                'default_font'          => 'sans-serif',
                'margin_left'           => 15,
                'margin_right'          => 15,
                'margin_top'            => 5,
                'margin_bottom'         => 0,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'P',
                'title'                 => 'CETAK INVOICE',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = PDF::loadView('backend.invoice.print_invoice.pdf',['title' => $title,'invoice' => $invoice, 'invoicedetail' => $invoicedetail,'tanggal' => $tanggal,'kodeperusahaan'=> $kodeperusahaan,'perusahaan'=> $perusahaan ],[],$config);
            ob_get_clean();
            return $pdf->stream('Invoice "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            $printinvoice = Invoice::find($idinv);
            if($printinvoice->count_print > 0){
                $printinvoice->count_print = $printinvoice->conut_print + 1;
            }else{
                $printinvoice->count_print = 1;
            }
            $printinvoice->save();
            return view('backend/invoice/print_invoice/print', compact('invoice','invoicedetail','tanggal','title','kodeperusahaan','perusahaan'));
        }else if($menu == 'excel'){
            return Excel::download(new InvoiceExports($idinv),'Invoice_'.$tanggal.'.xlsx');
        }

    }
    // Menu Packing List
    public function menu_packing_list($menu, $idinv){
        $dt = new Carbon();
        $invoicequery = Invoice::select('invoice.id','invoice.no_nota','invoice.purchase_no','invoice.member_id','invoice.sales_id','invoice.perusahaan_id','invoice.discount','invoice.subtotal','invoice.total','invoice.note','invoice.expedisi','invoice.via_expedisi','invoice.created_at','member.id as mid','member.name as mname','member.email as memail','member.address as malamat','member.address_toko as mtoko','member.city as mcity','member.city_id as mcity_id','member.phone as mphone','member.ktp as mktp','member.prov as mprov','sales.id as sid','sales.name as sname','perusahaan.id as pid','perusahaan.name as pname','perusahaan.address as palamat','perusahaan.city as pcity','perusahaan.telephone as pphone','perusahaan.rek_no as prekno','perusahaan.bank_name as pbank','expedisi.id as exid','expedisi.name as exname','expedisi_via.id as viaid','expedisi_via.name as vianame','dateorder');
        $invoicequery->join('member','member.id','invoice.member_id');
        $invoicequery->join('sales','sales.id','invoice.sales_id');
        $invoicequery->join('perusahaan','perusahaan.id','invoice.perusahaan_id');
        $invoicequery->join('expedisi','expedisi.id','invoice.expedisi');
        $invoicequery->leftJoin('expedisi_via','expedisi_via.id','invoice.via_expedisi');
        $invoicequery->where('invoice.id', $idinv);
        $invoice =  $invoicequery->first();

        $tanggalskrg = $dt->now()->isoFormat('dddd, D MMMM Y');
        //$tanggal = $dt->parse($invoice->created_at)->isoFormat('dddd, D MMMM Y');

        $tanggal     =  $dt->parse($invoice->dateorder)->isoFormat('dddd, D MMMM Y');

        $title = 'Invoice '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

        $invoicedetailquery = InvoiceDetail::select('*');
        $invoicedetailquery->where('invoice_id', $invoice->id);
        $invoicedetail = $invoicedetailquery->get();
        $print_by = auth()->user()->username;

        $invoice->print_by = $print_by;

        $invoicelog = new InvoiceLog();
        $invoicelog->user_id        = auth()->user()->id;
        $invoicelog->invoice_id     = $invoice->id;
        $invoicelog->keterangan     = auth()->user()->username." telah mencetak data Packing List No ".$invoice->no_nota;
        $invoicelog->create_date    = $dt->toDateTimeString();
        $invoicelog->create_user    = auth()->user()->username;
        $invoicelog->save();

        if($menu == 'pdf'){
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '12',
                'default_font'          => 'sans-serif',
                'margin_left'           => 15,
                'margin_right'          => 15,
                'margin_top'            => 5,
                'margin_bottom'         => 0,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'L',
                'title'                 => 'CETAK INVOICE',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = PDF::loadView('backend.invoice.packing_list.pdf',['title' => $title,'invoice' => $invoice, 'invoicedetail' => $invoicedetail,'tanggal' => $tanggal,'tanggalskrg'=>$tanggalskrg ],[],$config);
            ob_get_clean();
            return $pdf->stream('Packing List "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/invoice/packing_list/print', compact('invoice','invoicedetail','tanggal','title','tanggalskrg'));
        }else if($menu == 'excel'){
            return Excel::download(new PackingListExports($idinv),'Packing_Lists_'.$tanggalskrg.'.xlsx');
        }
    }
    // Menu Surat Jalan
    public function menu_surat_jalan($menu, $idinv){
        $dt = new Carbon();
        $invoicequery = Invoice::select('invoice.id','invoice.no_nota','invoice.purchase_no','invoice.member_id','invoice.sales_id','invoice.perusahaan_id','invoice.discount','invoice.subtotal','invoice.total','invoice.note','invoice.expedisi','invoice.via_expedisi','invoice.created_at','member.id as mid','member.name as mname','member.email as memail','member.address as malamat','member.address_toko as mtoko','member.city as mcity','member.city_id as mcity_id','member.phone as mphone','member.ktp as mktp','member.prov as mprov','sales.id as sid','sales.name as sname','perusahaan.id as pid','perusahaan.name as pname','perusahaan.address as palamat','perusahaan.city as pcity','perusahaan.telephone as pphone','perusahaan.rek_no as prekno','perusahaan.bank_name as pbank','expedisi.id as exid','expedisi.name as exname','expedisi_via.id as viaid','expedisi_via.name as vianame','dateorder');
        $invoicequery->join('member','member.id','invoice.member_id');
        $invoicequery->join('sales','sales.id','invoice.sales_id');
        $invoicequery->join('perusahaan','perusahaan.id','invoice.perusahaan_id');
        $invoicequery->join('expedisi','expedisi.id','invoice.expedisi');
        $invoicequery->leftJoin('expedisi_via','expedisi_via.id','invoice.via_expedisi');
        $invoicequery->where('invoice.id', $idinv);
        $invoice =  $invoicequery->first();

        $checksuratjalan = InvoiceSuratJalan::where('invoice_id', $invoice->id)->first();
        if(!$checksuratjalan){
            $suratjalan = new InvoiceSuratJalan();
            $suratjalan->invoice_id = $invoice->id;
            $suratjalan->surat_jalan_no = $this->gensuratjalan($dt->toDateTimeString(), $idinv);
            $suratjalan->create_user    = auth()->user()->username;
            $suratjalan->save();
            $invoice->surat_jalan_no = $suratjalan->surat_jalan_no;
        }else{
            $invoice->surat_jalan_no = $checksuratjalan->surat_jalan_no;
        }

        $invoicedetailquery = InvoiceDetail::select('*');
        $invoicedetailquery->where('invoice_id', $invoice->id);
        $invoicedetail = $invoicedetailquery->get();
        // $tanggal = $dt->parse($invoice->created_at)->isoFormat('dddd, D MMMM Y');
        $tanggal       = $dt->parse($invoice->dateorder)->isoFormat('dddd, D MMMM Y');
        $title = 'Invoice '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

        $dt = new Carbon();
        $invoicelog = new InvoiceLog();
        $invoicelog->user_id        = auth()->user()->id;
        $invoicelog->invoice_id     = $invoice->id;
        $invoicelog->keterangan     = auth()->user()->username." telah mencetak data Surat Jalan No ".$invoice->no_nota;
        $invoicelog->create_date    = $dt->toDateTimeString();
        $invoicelog->create_user    = auth()->user()->username;
        $invoicelog->save();

        if($menu == 'pdf'){
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '12',
                'default_font'          => 'sans-serif',
                'margin_left'           => 15,
                'margin_right'          => 15,
                'margin_top'            => 20,
                'margin_bottom'         => 0,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'L',
                'title'                 => 'CETAK INVOICE',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = PDF::loadView('backend.invoice.surat_jalan.pdf',['title' => $title,'invoice' => $invoice, 'invoicedetail' => $invoicedetail,'tanggal' => $tanggal ],[],$config);
            ob_get_clean();
            return $pdf->stream('Invoice "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/invoice/surat_jalan/print', compact('invoice','invoicedetail','tanggal','title'));
        }else if($menu == 'excel'){
            return Excel::download(new SuratJalanExports($idinv), 'Surat_Jalan_'.$tanggal.'.xlsx');
        }
    }
    // Menu Amplop
    public function menu_amplop($menu, $idinv){

        $dt = new Carbon();
        $invoicequery = Invoice::select('invoice.id','invoice.no_nota','invoice.memo','invoice.colly','invoice.purchase_no','invoice.member_id','invoice.sales_id','invoice.perusahaan_id','invoice.discount','invoice.subtotal','invoice.total','invoice.note','invoice.expedisi','invoice.via_expedisi','invoice.created_at','member.id as mid','member.name as mname','member.email as memail','member.address as malamat','member.address_toko as mtoko','member.city as mcity','member.city_id as mcity_id','member.phone as mphone','member.ktp as mktp','member.prov as mprov','sales.id as sid','sales.name as sname','perusahaan.id as pid','perusahaan.name as pname','perusahaan.address as palamat','perusahaan.city as pcity','perusahaan.telephone as pphone','perusahaan.rek_no as prekno','perusahaan.bank_name as pbank','expedisi.id as exid','expedisi.name as exname','expedisi_via.id as viaid','expedisi_via.name as vianame');
        $invoicequery->join('member','member.id','invoice.member_id');
        $invoicequery->join('sales','sales.id','invoice.sales_id');
        $invoicequery->join('perusahaan','perusahaan.id','invoice.perusahaan_id');
        $invoicequery->join('expedisi','expedisi.id','invoice.expedisi');
        $invoicequery->leftJoin('expedisi_via','expedisi_via.id','invoice.via_expedisi');
        $invoicequery->where('invoice.id', $idinv);
        $invoice =  $invoicequery->first();

        $invoicedetailcolly = InvoiceDetail::where('invoice_id', $invoice->id)->orderBy('id', 'DESC')->first();

        if($invoice->colly==null){
            $simpaninvoice = Invoice::find($invoice->id);
            $simpaninvoice->colly = $invoicedetailcolly->colly_to;
            $simpaninvoice->save();
        }

        $areacode = City::where('id', $invoice->mcity_id)->first();
        $provinsi = Provinsi::where('id', $areacode->provinsi_id)->first();
        $negara = Country::where('id',$provinsi->country_id)->first();

        $invoice->area_code = $areacode->area_code;
        $invoice->provinsi = $provinsi->name;
        $invoice->negara = $negara->name;

        $tanggal = $dt->now()->isoFormat('dddd, D MMMM Y');
        $title = 'Invoice '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

        $dt = new Carbon();
        $invoicelog = new InvoiceLog();
        $invoicelog->user_id        = auth()->user()->id;
        $invoicelog->invoice_id     = $invoice->id;
        $invoicelog->keterangan     = auth()->user()->username." telah mencetak Amplop No ".$invoice->no_nota;
        $invoicelog->create_date    = $dt->toDateTimeString();
        $invoicelog->create_user    = auth()->user()->username;
        $invoicelog->save();

        if($menu == 'pdf'){
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '12',
                'default_font'          => 'sans-serif',
                'margin_left'           => 15,
                'margin_right'          => 15,
                'margin_top'            => 5,
                'margin_bottom'         => 0,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'L',
                'title'                 => 'CETAK INVOICE',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = PDF::loadView('backend.invoice.amplop.pdf',['title' => $title,'invoice' => $invoice,'tanggal' => $tanggal ],[],$config);
            ob_get_clean();
            return $pdf->stream('Invoice "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/invoice/amplop/print', compact('invoice','tanggal','title'));
        }else if($menu == 'excel'){
            return Excel::download(new AmplopExports($idinv),'Amplop_'.$tanggal.'.xlsx');
        }
    }
}
