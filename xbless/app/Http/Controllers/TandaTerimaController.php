<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\TandaTerimaExports;
use App\Exports\DataPengirimanExports;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\InvoiceTandaTerima;
use App\Models\InvoiceSuratJalan;
use App\Models\PurchaseOrderLog;
use App\Models\InvoicePayment;
use App\Models\InvoicePiutang;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\ExpedisiVia;
use App\Models\InvoiceLog;
use App\Models\Perusahaan;
use App\Models\Provinsi;
use App\Models\Expedisi;
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

class TandaTerimaController extends Controller
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

    private function get_nota_tanda_terima($no_tanda_terima){
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.id','invoice_tanda_terima.no_tanda_terima','invoice.no_nota as no_nota','invoice.total as total_invoice','invoice.sales_id as sales_id','invoice.duedate as invoice_duedate','invoice.min_duedate as invoice_min_duedate');
        $query->join('invoice','invoice.id', 'invoice_tanda_terima.invoice_id');
        $query->where('invoice_tanda_terima.no_tanda_terima', $no_tanda_terima);
        $invoice = $query->get();

        return $invoice;
    }

    private function get_nota_tanda_terima_invoice($no_tanda_terima){
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.id','invoice_tanda_terima.no_tanda_terima','invoice.no_nota as no_nota','invoice.total as total_invoice','invoice.sales_id as sales_id','invoice.duedate as invoice_duedate','invoice.min_duedate as invoice_min_duedate');
        $query->join('invoice','invoice.id', 'invoice_tanda_terima.invoice_id');
        $query->where('invoice_tanda_terima.no_tanda_terima', $no_tanda_terima);
        $invoice = $query->get();

        foreach ($invoice as $key => $value) {
            $no_invoices[] = $value->no_nota;
        }

        return implode("<br>",$no_invoices);
    }

    private function gennott($data){
        $no_tt = InvoiceTandaTerima::where('perusahaan_id', $data->perusahaan_id)
                                    ->whereNotNull('no_tanda_terima')
                                    ->orderBy('id', 'DESC')->first();
        $nmperusahaan = Perusahaan::find($data->perusahaan_id);
        $namapt = $nmperusahaan->kode;
        if($no_tt != null || $no_tt > 0){
            $getNo = explode('-', $no_tt->no_tanda_terima);
            $getDate = explode('/', $no_tt->no_tanda_terima);
            if ($getDate[1] == date('y')) {
                if ($getNo[0] == $namapt) {
                    $noUrut = explode('-', $getDate[0])[1]+1;
                } else {
                    $noUrut = 1;
                }
            } else {
                $noUrut = 1;
            }
        }else{
            $noUrut = 1;
        }

        $result_nota = strtoupper($namapt).'-'.sprintf("%'.05d", $noUrut).'/'.date('y');

        return $result_nota;
    }

    public function proses(){

        $dt = new Carbon();

        $tgl_start = date('d-m-Y', strtotime(' - 30 days'));;
        $tgl_end = date('d-m-Y');
        $sales = Sales::all();
        $member = Member::all();
        $perusahaan = Perusahaan::all();
        $cities = City::orderBy('name', 'asc')->get();
        $invoice = Invoice::select('id', 'no_nota', 'purchase_no', 'member_id','sales_id','perusahaan_id','total',
                                    'discount', 'pay_status','created_at','create_user','subtotal','expedisi','via_expedisi', 'dateorder')
                    ->where('pay_status', 0)
                    ->where('flag_tanda_terima', 0)
                    ->orderBy('id', 'DESC')->paginate(30);
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
            $loggudang = PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%GUDANG%')->first();
            $loginvoice= PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%diproses GUDANG%')->first();

            $result->enc_id             = $enc_id;
            $result->member_name        = $member_inv->name;
            $result->member_city        = $member_inv->city;
            $result->perusahaan_name    = $perusahaan_inv->name;
            $result->nama_expedisi      = $expedisi->name;
            $result->nama_expedisi_via  = $expedisivia?$expedisivia->name:'-';
            $result->sales_name         = $sales_inv->name;
            $result->total              = $total;
            $result->created_by         = $purchase->createdon;
            $result->gudang             = $loggudang->create_user;
            $result->loginv             = $loginvoice?$loginvoice->create_user:'-';
            $result->create             = date("d M Y",strtotime($result->dateorder));
            $result->create_invo        = Carbon::parse($result->created_at)->diffForHumans();
        }
        // return response()->json([
        //     'data' => $invoice,
        // ]);
        return view('backend/tandaterima/proses', compact('invoice','tgl_start','tgl_end','sales','member','perusahaan', 'cities'));
    }

    public function process_tanda_terima(Request $request){
        $dt = new Carbon();
        if($request->idinv){
            try {
                foreach ($request->idinv as $key => $value) {
                    $invoice = Invoice::find($value);

                    $discount = $invoice->subtotal*($invoice->discount/100);
                    $afterdiscount = round($invoice->subtotal - $discount);
                    $ppn = round($afterdiscount*(10/100));
                    $grandtotal = $afterdiscount + $ppn;

                    $invoice_tanda_terima = new InvoiceTandaTerima();
                    $invoice_tanda_terima->invoice_id      = $invoice->id;
                    $invoice_tanda_terima->member_id       = $invoice->member_id;
                    $invoice_tanda_terima->perusahaan_id   = $invoice->perusahaan_id;
                    $invoice_tanda_terima->nilai           = $grandtotal;
                    $invoice_tanda_terima->invoice_date    = $invoice->created_at;
                    $invoice_tanda_terima->create_user     = auth()->user()->username;
                    $invoice_tanda_terima->flag_giro_cek   = 0;
                    $invoice_tanda_terima->create_date     = date("Y-m-d", strtotime($invoice->dateorder));
                    $invoice_tanda_terima->save();

                    if($invoice_tanda_terima){
                        $invoice->flag_tanda_terima        = 1;
                        $invoice->invoice_date_tt          = $dt->toDateTimeString();
                        $invoice->save();
                    }

                    $dt = new Carbon();
                    $invoicelog = new InvoiceLog();
                    $invoicelog->user_id        = auth()->user()->id;
                    $invoicelog->invoice_id     = $invoice->id;
                    $invoicelog->keterangan     = auth()->user()->username." telah Memproses Invoice No ".$invoice->no_nota." Ke Tanda Terima";
                    $invoicelog->create_date    = $dt->toDateTimeString();
                    $invoicelog->create_user    = auth()->user()->username;
                    $invoicelog->save();
                }

                $grouptanter = InvoiceTandaTerima::groupBy('perusahaan_id')->whereNull('no_tanda_terima')->get();
                foreach ($grouptanter as $key => $value) {
                    $no_tanter = $this->gennott($value);
                    $updatnoTT = InvoiceTandaTerima::where('perusahaan_id', $value->perusahaan_id)->whereNull('no_tanda_terima')->get();
                    foreach($updatnoTT as $key => $result){
                        $detailtanter = InvoiceTandaTerima::find($result->id);
                        $detailtanter->no_tanda_terima = $no_tanter;
                        $detailtanter->save();

                        // INSERT DATA PAYMENT
                    //     $getInvoice = Invoice::find($result->invoice_id);
                    //     $invoice_payment = new InvoicePayment();
                    //     $invoice_payment->no_tanda_terima   = $detailtanter->no_tanda_terima;
                    //     $invoice_payment->member_id         = $detailtanter->member_id;
                    //     $invoice_payment->payment_id        = 7;
                    //     $invoice_payment->payment_date      = $dt->toDateTimeString();
                    //     $invoice_payment->liquid_date       = $dt->toDateTimeString();
                    //     $invoice_payment->sudah_dibayar     = 0;
                    //     $invoice_payment->sisa              = $grandtotal;
                    //     $invoice_payment->total_pembayaran  = $grandtotal;
                    //     $invoice_payment->cicilan_ke        = 0;
                    //     $invoice_payment->save();

                    //     // INSERT DATA PIUTANG
                    //     $invoice_piutang = new InvoicePiutang();
                    //     $invoice_piutang->invoice_id            = $getInvoice->id;
                    //     $invoice_piutang->no_tt                 = $detailtanter->no_tanda_terima;
                    //     $invoice_piutang->invoice_payment_id    = $invoice_payment->id;
                    //     $invoice_piutang->total                 = $grandtotal;
                    //     $invoice_piutang->sisa                  = $grandtotal;
                    //     $invoice_piutang->payment_id            = 7;
                    //     $invoice_piutang->tanggal               = date("Y-m-d");
                    //     $invoice_piutang->flag                  = 0;
                    //     $invoice_piutang->create_user            = auth()->user()->username;
                    //     $invoice_piutang->save();
                    }
                }
                return response()->json([
                    "success"   => TRUE,
                    "message"   => 'Data Invoice Berhasil Diproses'
                ]);
            } catch (exception $e) {
                return response()->json([
                    "success"   => FALSE,
                    "message"   => 'Data Invoice Gagal Diproses'
                ]);
            }
        }else{
            return response()->json([
                "success"   => FALSE,
                "message"   => 'Maaf Silahkan Pilih Invoice untuk di Proses'
            ]);
        }
    }

    public function filter_data(Request $request){
        $tgl_start = $request->tgl_start;
        $tgl_end = $request->tgl_end;
        $sales = Sales::all();
        $member = Member::all();
        $perusahaan = Perusahaan::all();
        $selectedperusahaan = '';
        $query = Invoice::select('id', 'no_nota', 'purchase_no', 'member_id','sales_id','perusahaan_id','total',
                                    'discount', 'pay_status','created_at','create_user','subtotal','expedisi','via_expedisi', 'dateorder');

        if($request->tgl_start != ""){
            $query->whereDate('dateorder','>=',date('Y-m-d', strtotime($tgl_start)));
            $query->whereDate('dateorder','<=',date('Y-m-d', strtotime($tgl_end)));
        }

        if($request->perusahaan != ""){
            $query->where('perusahaan_id', $request->perusahaan);
        }

        if($request->customer != ""){
            $query->where('member_id', $request->customer);
        }

        if($request->invoice != ""){
            $query->where('no_nota', 'LIKE', '%'.$request->invoice.'%');
        }

        if($request->sales != ""){
            $salesVal = $request->sales;
            $data = $query->whereHas('getsales', function($q) use ($salesVal){
                    $q->whereIn('name', $salesVal);
                });
        }

        if($request->city != ""){
            $city = $request->city;
            $data = $query->whereHas('getMember', function($q) use ($city){
                    $q->whereIn('city', $city);
                });
        }

        $query->where('pay_status', 0);
        $query->where('flag_tanda_terima', 0);
        $query->orderBy('id', 'DESC');
        $invoice = $query->paginate(30);
        foreach ($invoice as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $perusahaan_inv = Perusahaan::find($result->perusahaan_id);
            $member_inv = Member::where('id', $result->member_id)->first();
            $sales_inv = Sales::where('id', $result->sales_id)->first();
            $purchase = PurchaseOrder::where('no_nota', $result->purchase_no)->first();
            $expedisi = Expedisi::find($result->expedisi);
            $expedisivia = ExpedisiVia::find($result->via_expedisi);

            // $discount = $result->total*($result->discount/100);
            // $afterdiscount = round($result->total - $discount);
            // $ppn = round($afterdiscount*(10/100));
            // $total = $afterdiscount + $ppn;

            $discount = round($result->subtotal*($result->discount/100));
            $afterdiscount = round($result->subtotal - $discount);
            $ppn = round($afterdiscount*(10/100));
            $total = $afterdiscount + $ppn;

            // LOG USER
            $loggudang = PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%GUDANG%')->first();
            $loginvoice= PurchaseOrderLog::where('purchase_id', $purchase->id)->where('keterangan', 'LIKE', '%diproses GUDANG%')->first();

            $result->enc_id             = $enc_id;
            $result->member_name        = $member_inv->name;
            $result->member_city        = $member_inv->city;
            $result->perusahaan_name    = $perusahaan_inv->name;
            $result->nama_expedisi      = $expedisi->name;
            $result->nama_expedisi_via  = $expedisivia?$expedisivia->name:'-';
            $result->sales_name         = $sales_inv->name;
            $result->total              = $total;
            $result->created_by         = $purchase->createdon;
            $result->gudang             = $loggudang->create_user;
            $result->loginv             = $loginvoice?$loginvoice->create_user:'-';
            $result->create             = date("d M Y",strtotime($result->dateorder));
            $result->create_invo        = Carbon::parse($result->created_at)->diffForHumans();
        }
        // return response()->json([
        //     'data' => $request->all(),
        // ]);
        return view('backend/tandaterima/filter', compact('invoice','tgl_start','tgl_end','sales','member','perusahaan'));
    }

    public function index(Request $request){

        $perusahaan = Perusahaan::all();
        $members = Member::all();

        if(session('filter_perusahaan')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_perusahaan');
        }
        return view('backend/tandaterima/index',compact('perusahaan','selectedperusahaan', 'members'));
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_customer', $request->filter_customer);

        $querydb = InvoiceTandaTerima::select('invoice_tanda_terima.*','invoice.no_nota as no_invoice','perusahaan.name as perusahaan_name','member.name as member_name','member.city as member_kota','member.address as member_alamat','member.address_toko as member_alamat_toko');
        $querydb->join('invoice', 'invoice.id','invoice_tanda_terima.invoice_id');
        $querydb->join('perusahaan','perusahaan.id','invoice_tanda_terima.perusahaan_id');
        $querydb->join('member','member.id','invoice_tanda_terima.member_id');
        $querydb->groupBy('invoice_tanda_terima.no_tanda_terima');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $querydb->orderBy('id','DESC');
        }

        if($request->filter_perusahaan != ""){
            $querydb->where('invoice_tanda_terima.perusahaan_id',$request->filter_perusahaan);
        }
        if($request->filter_customer != ""){
            $querydb->where('invoice_tanda_terima.member_id',$request->filter_customer);
        }

       if($search) {
        $querydb->where(function ($query) use ($search) {
            $query->orWhere('invoice_tanda_terima.no_tanda_terima','LIKE',"%{$search}%");
            $query->orWhere('invoice.no_nota','LIKE',"%{$search}%");
        });
      }
      $totalData = $querydb->get()->count();

      $totalFiltered = $querydb->get()->count();

      $querydb->limit($limit);
      $querydb->offset($start);

      $data = $querydb->get();
      foreach($data as $key=> $value)
      {
        $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
        $action = "";
        $action.="";
        $action.="<div>";
        if($request->user()->can('tandaterima.invoiceprint')){
            $action.='<a href="#!" id="tanda_terima" role="button" onclick="pilih_menu('.$value->id.', this.name)" data-toggle="modal" data-target="#modal_pilihan" name="menu_tanda_terima" class="btn btn-danger btn-sm mr-1 icon-btn md-btn-flat product-tooltip" title="Print Tanda Terima"><i class="fa fa-print fa-lgs"></i></a>';
        }
        if($request->user()->can('tandaterima.invoiceprintdatakirim')){
            $action.='<a href="#!" id="pengiriman" role="button" onclick="pilih_menu('.$value->id.', this.name)" data-toggle="modal" data-target="#modal_pilihan" name="menu_pengiriman" class="btn btn-primary btn-sm mr-1 icon-btn md-btn-flat product-tooltip" title="Print Data Pengiriman"><i class="fa fa-print fa-lg"></i>&nbsp<i class="fa fa-truck fa-lg"></i></a>';
        }
        if($request->user()->can('tandaterima.invoiceinputpengiriman')){
            $action.='<a href="#!" id="input_pengiriman" role="button" onclick="input_pengiriman(this.name)" data-toggle="modal" data-target="#modal_pengiriman" name="'.$value->no_tanda_terima.'" class="btn btn-success btn-sm icon-btn md-btn-flat product-tooltip" title="Input Data Pengiriman"><i class="fa fa-truck fa-lg"></i></a>';
        }
            $action.="</div>";

        $no_invoice = $this->get_nota_tanda_terima_invoice($value->no_tanda_terima);

        $value->no              = $key+$page;
        $value->id              = $value->id;
        $value->no_tanda_terima = $value->no_tanda_terima;

        // foreach($no_invoice as $key => $invoice){
        //     $value->no_nota     = $invoice->no_nota;
        // }
        $value->no_nota     = $no_invoice;

        $value->member_name     = $value->member_name;
        $value->kota            = $value->member_alamat.' '.$value->member_kota;
        $value->perusahaan      = $value->perusahaan_name;
        $value->tanggal_dibuat  = date('d M y H:i', strtotime(date($value->created_at)));
        $value->action          = $action;
      }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
            );
    //   if ($request->user()->can('historyadjstok.index')) {
    //     $json_data = array(
    //               "draw"            => intval($request->input('draw')),
    //               "recordsTotal"    => intval($totalData),
    //               "recordsFiltered" => intval($totalFiltered),
    //               "data"            => $data
    //               );
    //   }else{
    //      $json_data = array(
    //               "draw"            => intval($request->input('draw')),
    //               "recordsTotal"    => 0,
    //               "recordsFiltered" => 0,
    //               "data"            => []
    //               );
    //   }
      return json_encode($json_data);
    }

    public function menu_data_list(Request $request){
        $list = '';
        $list.='<a href="#!" class="btn bg-warning" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="print"><i class="fa fa-print"></i>&nbsp; Print</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-success" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="excel"><i class="fa fa-file-excel-o"></i>&nbsp; Excel</a>&nbsp;';
        $list.='<a href="#!" class="btn bg-danger" onclick="'.$request->menu.'('.$request->enc_id.', this.name)" name="pdf"><i class="fa fa-file-pdf-o"></i>&nbsp; PDF</a>';

        return response()->json([
            'list' => $list
        ]);
    }

    public function input_pengiriman(Request $request){
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.expedisi','invoice_tanda_terima.id','invoice_tanda_terima.no_tanda_terima','invoice_tanda_terima.delivery_date','invoice_tanda_terima.resi_no','invoice_tanda_terima.nilai','invoice_tanda_terima.nilai_pengiriman','invoice.no_nota as no_nota','invoice.total as total_invoice','invoice.sales_id as sales_id','invoice.duedate as invoice_duedate','invoice.min_duedate as invoice_min_duedate', 'invoice.expedisi as invoice_expedisi');
        $query->join('invoice','invoice.id', 'invoice_tanda_terima.invoice_id');
        $query->where('invoice_tanda_terima.no_tanda_terima', $request->enc_id);
        $invoice = $query->get();
        //dd($invoice);
        $total = $query->count();

        $expedisi = Expedisi::all();

        $content = '';
        $js = '';
        foreach($invoice as $key => $value){
            if($value->delivery_date == "" || $value->delivery_date==null ){
                $date_kirim = "".date("Y-m-d",strtotime("-1 day", strtotime(date("Y-m-d"))))."";
            }else{
                $date_kirim = "".date("Y-m-d",strtotime("+0 day", strtotime($value->delivery_date)))."";
            }

            // $selectedExpedisi = $value->expedisi;
            $selectedExpedisi = Expedisi::find($value->invoice_expedisi);
            //dd($selectedExpedisi);
            $content.='<div class="panel-heading text-dark">';
                $content.='<h5 class="panel-title">Invoice No : <b>'.$value->no_nota.' : </b></h5>';
            $content.='</div>';
            $content.='<div class="panel-body">';
                $content.='<div class="row">';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Tanggal Kirim :</label>';
                            $content.='<input type="date" id="delivery_date_'.$value->id.'" name="delivery_date_'.$value->id.'" class="form-control" data-mask="99/99/9999" placeholder="Masukkan Tanggal Kirim" value="'.$date_kirim.'">';
                        $content.='</div>';
                    $content.='</div>';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Expedisi :</label>';
                            $content.= '<select class="exspedisi_'.$value->id.' form-control" id="exspedisi_'.$value->id.'" name="exspedisi_'.$value->id.'">';
                            $content.='<option value="'.$selectedExpedisi->name.'">'.$selectedExpedisi->name.'</option>';
                            foreach($expedisi as $key => $result){
                                if($result->id == $value->invoice_expedisi){
                                    $selecteddetail = 'selected';
                                }else{
                                    $selecteddetail = '';
                                }

                                $content.='<option value="'.$result->id.'" '.$selecteddetail.'>'.ucfirst($result->name).'</option>';
                            }
                            $content.='</select>';
                        $content.='</div>';
                    $content.='</div>';
                $content.='</div>';
                $content.='<div class="row">';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>No Resi :</label>';
                            $content.='<input type="text" id=resi_no_'.$value->id.'" name="resi_no_'.$value->id.'" class="form-control" placeholder="No Resi" value="'.$value->resi_no.'">';
                        $content.='</div>';
                    $content.='</div>';
                    $content.='<div class="col-md-6">';
                        $content.='<div class="form-group">';
                            $content.='<label>Nilai (Rp.) :</label>';
                            $content.='<input type="text" id=nilai_'.$value->id.' class="form-control numberformat" value='.number_format($value->nilai_pengiriman, 0,'.','.').' name=nilai_'.$value->id.'>';
                        $content.='</div>';
                    $content.='</div>';
                $content.='</div>';
            $content.='</div>';

            // $js.="$('.exspedisi_".$value->id."').select2({
            //     dropdownParent: $('#modal_pengiriman')
            // })";

        }
        return response()->json([
            'html' => $content,
            'js' => $js
        ]);
    }

    public function simpan_pengiriman(Request $request){
        $tanda_terima = InvoiceTandaTerima::where('no_tanda_terima', $request->id_tanter)->get();

        try {
            foreach ($tanda_terima as $key => $value) {
                $update_tanter  = InvoiceTandaTerima::find($value->id)->update([
                    'resi_no'          => $request->input('resi_no_'.$value->id),
                    'expedisi'         => $request->input('exspedisi_'.$value->id),
                    'delivery_date'    => date("Y-m-d",strtotime("+0 day", strtotime($request->input('delivery_date_'.$value->id)))),
                    'nilai_pengiriman' => str_replace('.','',$request->input('nilai_'.$value->id)),
                ]);
                $invoice = InvoiceTandaTerima::find($value->id)->getInvoice->update([
                    'resi_no'       => $request->input('resi_no_'.$value->id),
                    'expedisi'      => $request->input('exspedisi_'.$value->id),
                    'delivery_date' => date("Y-m-d",strtotime("+0 day", strtotime($request->input('delivery_date_'.$value->id)))),
                ]);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Data Berhasil Diupdate'
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Data Tidak Ada'
            ]);
        }
    }

    public function data_tanda_terima($menu, $idtt){
        $dt = new Carbon();
        $tanggal = $dt->now()->isoFormat('D MMM Y');
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.*','perusahaan.id as perusahan_id','perusahaan.name as perusahaan_name','perusahaan.rek_no as perushaan_rekno','member.id as member_id','member.name as member_name','member.address_toko as member_toko','member.city as member_kota');
        $query->join('perusahaan','invoice_tanda_terima.perusahaan_id','perusahaan.id');
        $query->join('member','invoice_tanda_terima.member_id','member.id');
        $query->where('invoice_tanda_terima.id', $idtt);
        $tanda_terima = $query->first();
        $tanda_terima->print_tanggal = $tanggal;
        $grandtotal = InvoiceTandaTerima::where('no_tanda_terima', $tanda_terima->no_tanda_terima)->sum('nilai');
        $tanda_terima->grandtotal = $grandtotal;

        $querydetail = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder');
        $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
        $querydetail->where('invoice_tanda_terima.no_tanda_terima', $tanda_terima->no_tanda_terima);

        $detail_tanda_terima = $querydetail->get();

        foreach ($detail_tanda_terima as $key => $value) {
            $value->no = $key+1;
            $value->pertanggal = date('d M y', strtotime(date($value->dateorder)));
        }

        $title = 'Tanda Terima '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

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
            $pdf = PDF::loadView('backend.tandaterima.print_tanda_terima.pdf',['title' => $title,'tanda_terima' => $tanda_terima,'detail_tanda_terima' => $detail_tanda_terima,'tanggal' => $tanggal ],[],$config);
            ob_get_clean();
            return $pdf->stream('Tanda Terima "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/tandaterima/print_tanda_terima/print', compact('tanda_terima','detail_tanda_terima','tanggal','title'));
        }else if($menu == 'excel'){
            return Excel::download(new TandaTerimaExports($idtt),'Tanda_Terima_'.$tanggal.'.xlsx');
        }
    }

    public function data_pengiriman($menu, $idtt){

        $dt = new Carbon();
        $tanggal = $dt->now()->isoFormat('D MMM Y');
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.*','perusahaan.id as perusahan_id','perusahaan.name as perusahaan_name','perusahaan.rek_no as perusahaan_rekno','member.id as member_id','member.name as member_name','member.address_toko as member_toko','member.city as member_kota');
        $query->join('perusahaan','invoice_tanda_terima.perusahaan_id','perusahaan.id');
        $query->join('member','invoice_tanda_terima.member_id','member.id');
        $query->where('invoice_tanda_terima.id', $idtt);
        $tanda_terima = $query->first();
        $tanda_terima->print_tanggal = $tanggal;
        $grandtotal = InvoiceTandaTerima::where('no_tanda_terima', $tanda_terima->no_tanda_terima)->sum('nilai');
        $tanda_terima->grandtotal = $grandtotal;

        $querydetail = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder','invoice.expedisi as invoice_expedisi','invoice.via_expedisi as invoice_expedisi_via','invoice.delivery_date as invoice_deliver','invoice.resi_no as invoice_resi');
        $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
        $querydetail->where('invoice_tanda_terima.no_tanda_terima', $tanda_terima->no_tanda_terima);

        $detail_tanda_terima = $querydetail->get();

        foreach ($detail_tanda_terima as $key => $value) {
            $expedisi = Expedisi::find($value->invoice_expedisi);
            $expedisi_via = ExpedisiVia::find($value->invoice_expedisi_via);

            $value->no = $key+1;
            $value->pertanggal     = date('d M y', strtotime(date($value->dateorder)));
            $value->expedisi        = $expedisi?$expedisi->name:'-';
            if($expedisi_via){
                $value->expedisi_via    = $expedisi_via->name;
            }
            $value->delivery        = date('d M y', strtotime(date($value->invoice_deliver)));
        }

        $grandtotal = InvoiceTandaTerima::where('no_tanda_terima', $tanda_terima->no_tanda_terima)->sum('nilai');
        $title = 'Tanda Terima '.$dt->now()->isoFormat('dddd, D MMMM Y').'.pdf';

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
                'title'                 => 'CETAK INVOICE PENGIRIMAN',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
            $pdf = PDF::loadView('backend.tandaterima.print_data_pengiriman.pdf',['title' => $title,'tanda_terima' => $tanda_terima,'detail_tanda_terima' => $detail_tanda_terima, 'tanggal' => $tanggal ],[],$config);
            ob_get_clean();
            return $pdf->stream('Data Pengiriman "'.date('d_m_Y H_i_s').'".pdf');
        }else if($menu == 'print'){
            return view('backend/tandaterima/print_data_pengiriman/print', compact('tanda_terima','detail_tanda_terima','tanggal','title'));
        }else if($menu == 'excel'){
            return Excel::download(new DataPengirimanExports($idtt),'Data_Pengiriman_'.$tanggal.'.xlsx');
        }
    }

}
