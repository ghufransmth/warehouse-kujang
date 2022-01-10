<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\ProductPerusahaanGudang;
use App\Models\PurchaseOrderDetail;
use App\Models\PerusahaanGudang;
use App\Models\PurchaseOrderLog;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\ExpedisiVia;
use App\Models\ReportStock;
use App\Models\Perusahaan;
use App\Models\TipeHarga;
use App\Models\Invoice;
use App\Models\Satuan;
use App\Models\Expedisi;
use App\Models\Product;
use App\Models\Member;
use App\Models\Gudang;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Models\StockAdj;
use App\Models\Toko;
use App\Models\TransaksiStock;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;

use DB;
use PDF;
use Auth;

class PurchaseController extends Controller
{
    protected $original_column = array(
        1 => "product_code",
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

    public function statusinvoiceawal(){
        $value = array('0'=>'DIPROSES', '1'=>'PROSES KE GUDANG', '2' => 'DITOLAK');
        return $value;
    }

    public function index(){
        // $member     = Member::all();
        // $perusahaan = Perusahaan::all();
        // $gudang     = Gudang::all();

        if(session('filter_perusahaan')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_perusahaan');
        }

        if(session('filter_member')==""){
            $selectedmember = '';
        }else{
            $selectedmember = session('filter_member');
        }
        $member = array();
        $perusahaan = array();
        $gudang = array();
        return view('backend/purchase/index',compact('member','perusahaan','gudang','selectedmember','selectedperusahaan'));
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
        $cek = PurchaseOrder::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function cut_text($string){
        $value = substr($string, 0, 3);
        return $value;
    }


    public function gennoinvo($tanggal, $data)
    {
            $next_no     ='';
            $max_data_id =Invoice::where('perusahaan_id',$data->perusahaan_id)->orderBy('id', 'DESC')->first();

            if ($max_data_id) {
                $datainvoice  = Invoice::find($max_data_id->id);
                $nota         = $datainvoice->no_nota;
                $tahunnota     =explode('/',$nota)[3];
                if($tahunnota == date('y')){
                    $ambil = substr(explode('/',$nota)[0], -5);
                }else{
                    $ambil = '99999';
                }
            }

            if ($max_data_id==null) {
                $next_no = '00001';
            }elseif (strlen($ambil)<5) {
                $next_no = '00001';
            }elseif ($ambil == '99999') {
                $next_no = '00001';
            }else {
                $next_no = substr('00000', 0, 5-strlen($ambil+1)).($ambil+1);
            }

            $nmperusahaan      = Perusahaan::find($data->perusahaan_id);
            $kode_perusahaan   = $nmperusahaan?$nmperusahaan->kode:'-';

            return $next_no.'/'.$kode_perusahaan.'/'.date('m').'/'.date('y');
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        $request->session()->put('filter_toko', $request->filter_toko);
        $request->session()->put('filter_sales', $request->filter_sales);
        $request->session()->put('type', $request->type);
        $penjualan = Penjualan::select('*');

        $penjualan->orderBy('id', 'ASC');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $penjualan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }

        if($search) {
            $penjualan->where(function ($query) use ($search) {
                    $query->orWhere('no_nota','LIKE',"%{$search}%");
                    $query->orWhere('kode_rpo','LIKE',"%{$search}%");
            });
        }
        $totalData = $penjualan->get()->count();

        $totalFiltered = $penjualan->get()->count();
        $penjualan->limit($limit);
        $penjualan->offset($start);
        $data = $penjualan->get();

        foreach($data as $key => $result){
            $aksi = "";
            $result->id             = $result->id;
            $result->no             = $key+$page;
            $result->no_faktur = $result->no_faktur;
            $result->sales = $result->getsales->nama;
            $result->toko = $result->gettoko->name;
            $result->tgl_jatuh_tempo = $result->tgl_jatuh_tempo;
            $result->tgl_transaksi = $result->tgl_faktur;
            $result->total_harga = $result->total_harga;
            $result->tgl_lunas = $result->tgl_lunas;
            if($result->status_lunas == 0){
                $result->status_pembayaran = 'Belum Lunas';
            }else{
                $result->status_pembayaran = 'Lunas';
            }
            $result->created_by = $result->created_by;
            $result->aksi = $aksi;
        }
        if ($request->user()->can('purchaseorder.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
        }else{
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => []
            );
        }

        return json_encode($json_data);


    }
    public function getData_(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_member', $request->filter_member);
        $request->session()->put('type', $request->type);
        $request->session()->put('type_gudang', $request->type_gudang);

        if($request->user()->can('purchaseorder.liststatuspo')){
            $purchase = PurchaseOrder::select('transaction_purchase.id','transaction_purchase.dataorder','no_nota','kode_rpo','note','createdon','total', 'count_cetak','transaction_purchase.perusahaan_id','transaction_purchase.created_at',
                                'transaction_purchase.updated_at','member_id','sales_id','expedisi','expedisi_via','transaction_purchase.status', 'status_po', 'status_gudang','status_invoice','gudang_id','gudang.name as gudangnama','transaction_purchase_detail.transaction_purchase_id',
                                DB::raw('(CASE
                                    WHEN transaction_purchase.status = 0 THEN 1
                                    WHEN transaction_purchase.status = 1 THEN 2
                                    WHEN transaction_purchase.status = 2 THEN 4
                                    WHEN transaction_purchase.status = 3 THEN 3
                                    ELSE 5 END ) AS ord'));
            $purchase->join('transaction_purchase_detail','transaction_purchase_detail.transaction_purchase_id','transaction_purchase.id');
            $purchase->join('gudang','transaction_purchase_detail.gudang_id','gudang.id');
            $purchase->where('flag_status', 0);
            // if($request->user()->can('purchaseorder.liststatuspobyuser')){
            //     $purchase->where('createdon', auth()->user()->username);
            // }
            // if(session('type') ==''){
            //     $purchase->where('transaction_purchase.type','!=',2);
            // }
            $purchase->groupBy('transaction_purchase_detail.transaction_purchase_id');
            $purchase->orderBy('ord', 'ASC');
            $purchase->orderBy('status_gudang', 'ASC');
            $purchase->orderBy('transaction_purchase.updated_at','DESC');

        }else if($request->user()->can('purchaseorder.liststatusgudang')){
            $purchase = PurchaseOrder::select('transaction_purchase.id','transaction_purchase.dataorder', 'no_nota','kode_rpo','note','createdon','total','count_cetak','transaction_purchase.perusahaan_id','transaction_purchase.created_at',
                                'transaction_purchase.updated_at','member_id','sales_id','expedisi','expedisi_via','transaction_purchase.status', 'status_po', 'status_gudang','status_invoice','gudang_id','gudang.name as gudangnama','transaction_purchase_detail.transaction_purchase_id',
                                DB::raw('(CASE
                                    WHEN transaction_purchase.status = 1 AND status_gudang = 0 THEN 1
                                    WHEN status_gudang = 1 AND transaction_purchase.status <> 3 THEN 2
                                    WHEN transaction_purchase.status = 3 THEN 3
                                    WHEN transaction_purchase.status = 2 THEN 4
                                    ELSE 5 END ) AS ord'));
            $purchase->join('transaction_purchase_detail','transaction_purchase_detail.transaction_purchase_id','transaction_purchase.id');
            $purchase->join('gudang','transaction_purchase_detail.gudang_id','gudang.id');
            $purchase->where('flag_status', 0);
            $purchase->groupBy('transaction_purchase_detail.transaction_purchase_id');
            $purchase->orderBy('ord','ASC');
            $purchase->orderBy('transaction_purchase.updated_at', 'DESC');

        }else if($request->user()->can('purchaseorder.liststatusinvoice')){
            $purchase = PurchaseOrder::select('transaction_purchase.id', 'transaction_purchase.dataorder','no_nota','kode_rpo','note','createdon','total', 'count_cetak','transaction_purchase.perusahaan_id','transaction_purchase.created_at',
                                'transaction_purchase.updated_at','member_id','sales_id','expedisi','expedisi_via','transaction_purchase.status', 'status_po', 'status_gudang','status_invoice','gudang_id','gudang.name as gudangnama','transaction_purchase_detail.transaction_purchase_id',
                                DB::raw('(CASE
                                    WHEN transaction_purchase.status = 1 AND status_gudang = 0 THEN 2
                                    WHEN status_gudang = 1 AND transaction_purchase.status <> 3 THEN 1
                                    WHEN transaction_purchase.status = 3 THEN 3
                                    WHEN transaction_purchase.status = 2 THEN 4
                                    ELSE 5 END ) AS ord'));
            $purchase->join('transaction_purchase_detail','transaction_purchase_detail.transaction_purchase_id','transaction_purchase.id');
            $purchase->join('gudang','transaction_purchase_detail.gudang_id','gudang.id');
            $purchase->where('flag_status', 0);
            if(session('type') ==''){
                $purchase->whereIn('transaction_purchase.status_invoice',[1,2]);
            }
            // $purchase->where('transaction_purchase.status_invoice','!=',0);
            $purchase->groupBy('transaction_purchase_detail.transaction_purchase_id');
            $purchase->orderBy('ord','ASC');
            $purchase->orderBy('transaction_purchase.updated_at', 'DESC');

        }else{
            $purchase = PurchaseOrder::select('transaction_purchase.id','transaction_purchase.dataorder', 'no_nota','kode_rpo','note','createdon','total','count_cetak','transaction_purchase.perusahaan_id','transaction_purchase.created_at',
                                'transaction_purchase.updated_at','member_id','sales_id','expedisi','expedisi_via','transaction_purchase.status', 'status_po', 'status_gudang','status_invoice','gudang_id','gudang.name as gudangnama','transaction_purchase_detail.transaction_purchase_id',);
            $purchase->join('transaction_purchase_detail','transaction_purchase_detail.transaction_purchase_id','transaction_purchase.id');
            $purchase->join('gudang','transaction_purchase_detail.gudang_id','gudang.id');
            $purchase->where('flag_status', 0);
            $purchase->groupBy('transaction_purchase_detail.transaction_purchase_id');
            $purchase->orderBy('transaction_purchase.status', 'ASC');
            $purchase->orderBy('status_gudang', 'ASC');
            $purchase->orderBy('transaction_purchase.updated_at','DESC');

        }
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $purchase->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }


        if($request->filter_perusahaan != ""){
            $purchase->where('transaction_purchase.perusahaan_id', $request->filter_perusahaan);
        }

        if($request->filter_member != ""){
            $purchase->where('transaction_purchase.member_id', $request->filter_member);
        }
        if($request->type != ""){
            if($request->type =='0'){
                if($request->user()->can('purchaseorder.liststatuspo')){
                    $purchase->where('transaction_purchase.flag_status', 0);
                }else if($request->user()->can('purchaseorder.liststatusinvoiceawal')){
                    $purchase->whereIn('transaction_purchase.status_invoice',[1,2]);
                    $purchase->where('transaction_purchase.flag_status', 0);
                }
            }
            //LIST PO TOLAK : HARGA BISA DIUBAH
            if($request->type =='1'){
                $purchase->where('transaction_purchase.status_invoice', 2);
                $purchase->where('transaction_purchase.status_gudang', 2);
                $purchase->where('transaction_purchase.flag_status', 0);
                $purchase->where('transaction_purchase.type', 2);
            }
            //VALIDASI HARGA
            if($request->type =='2'){
                $purchase->where('transaction_purchase.status_invoice', 0);
                $purchase->where('transaction_purchase.status_gudang', 0);
                $purchase->where('transaction_purchase.flag_status', 0);
                $purchase->where('transaction_purchase.status', 1);
            }
            //UNTUK GUDANG
            if($request->type =='3'){
                $purchase->where('transaction_purchase.flag_status', 0);
                $purchase->where('gudang_id', $request->type_gudang);
            }
        }
        if($search) {
          $purchase->where(function ($query) use ($search) {
                  $query->orWhere('no_nota','LIKE',"%{$search}%");
                  $query->orWhere('kode_rpo','LIKE',"%{$search}%");
          });
        }
        $totalData = $purchase->get()->count();

        $totalFiltered = $purchase->get()->count();

        $purchase->limit($limit);
        $purchase->offset($start);
        $data = $purchase->get();
        foreach ($data as $key => $result){
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $inv = Invoice::select('no_nota','pay_status')->where('purchase_no',$result->no_nota)->first();
            $rpo = "";
            $rpo.="";
            $rpo.='<h5 class="no-margin"># <b>'.$result->no_nota.'</b></h5>';
            if($inv) {
                $rpo.='<h5 class="no-margin" style="width:300px;">No. INV : <b>'.$inv->no_nota.'</b></h5>';
            }

            $action = "";
            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('purchaseorder.detail')){
                $action.='<a href="#modal_image_produk" id="detail_po" role="button" data-id="'.$enc_id.'" data-toggle="modal" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-eye"></i></a>&nbsp';
            }
            $action.="</div>";

            $rpo.='<div>';
            $rpo.='<div class="row">';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $getBO = $this->cut_text($result->kode_rpo);
                        if($getBO=='BO-'){
                            $type = 'BO';
                            $rpo_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%PO ke Backorder%')->first();
                        }else{
                            $type = 'RPO';
                            $rpo_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%DIPROSES ke RPO%')->first();
                        }
                        if($rpo_created){
                            $rpo.='<small class="display-block text-muted">'.$type.' - '.$rpo_created->create_user.'</small><br><small style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;">'
                            .date("d M Y H:i:s",strtotime($rpo_created->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $po_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%PO untuk di proses INVOICE%')->get();
                        $i_po = 0;
                        foreach($po_created as $kpo => $nilaipo){
                            if($kpo==0){
                                $rpo.='<small class="display-block text-muted">PO - '.$nilaipo->create_user.'<br></small>';
                            }
                            if(++$i_po === count($po_created)){
                                $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            else{
                                $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilaipo->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
            $rpo.='</div>';
            $rpo.='<div class="row">';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $po_inv_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%PO untuk diproses GUDANG%')->get();
                        $i_kpoinv = 0;
                        foreach($po_inv_created as $kpoinv => $nilaipoinv){
                            if($kpoinv==0){
                                $rpo.='<small class="display-block text-muted">INV - Cek Harga - '.$nilaipoinv->create_user.'<br></small>';
                            }
                            if(++$kpoinv === count($po_inv_created)){
                                $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            else{
                                $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilaipoinv->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $po_print_po = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%print pada PO%')->get();
                        $i_poprintpo = 0;
                        foreach($po_print_po as $poprintpo => $nilaipoprintpo){
                            if($poprintpo==0){
                                $rpo.='<small class="display-block text-muted">GDG - Cetak PO - '.$nilaipoprintpo->create_user.'<br></small>';
                            }
                            if(++$poprintpo === count($po_print_po)){
                                $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            else{
                                $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilaipoprintpo->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
            $rpo.='</div>';
            $rpo.='<div class="row">';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $nota = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%ke pembuatan NOTA%')->get();
                        $i_not = 0;
                        foreach($nota as $not => $nilainota){
                            if($not==0){
                                $rpo.='<small class="display-block text-muted">INV - Invoice - '.$nilainota->create_user.'<br></small>';
                            }
                            if(++$i_not === count($nota)){
                                $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            else{
                                $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilainota->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $po_gdg_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%PO untuk diproses INVOICE AKHIR%')->get();
                        $i_kpogdg = 0;
                        foreach($po_gdg_created as $kpogdg => $nilaipogdg){
                            if($kpogdg==0){
                                $rpo.='<small class="display-block text-muted">GDG - Closing PO - '.$nilaipogdg->create_user.'<br></small>';
                            }
                            if(++$i_kpogdg === count($po_gdg_created)){
                                $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            else{
                                $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                            }
                            $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilaipogdg->create_date)).'</small><br>';
                        }
                $rpo.='</div>';
            $rpo.='</div>';
            $rpo.='<div class="row">';
                $po_tolak = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%Menolak PO%')->get();
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                $i_kpotolak = 0;
                foreach($po_tolak as $kpotolak => $nilaipotolak){
                    if($kpotolak==0){
                        $rpo.='<small class="display-block text-muted">Ditolak -  '.$nilaipotolak->create_user.'<br></small>';
                    }
                    if(++$i_kpotolak === count($po_tolak)){
                        $style = 'style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                    }
                    else{
                        $style = 'style="margin-left:20px;font-size:10px;letter-spacing: -1px;"';
                    }
                    $rpo.='<small '.$style.'>'.date("d M Y H:i:s",strtotime($nilaipotolak->create_date)).'</small><br>';
                }
                $rpo.='</div>';

            $rpo.='</div>';

            if($result->count_cetak > 0){
                $rpo.='<br/><small class="display-block" style="color:red"><i class="fa fa-print"></i>&nbsp&nbspSudah diprint sebanyak '.$result->count_cetak.'x<br>';
            }

            $polog     = PurchaseOrderLog::where('purchase_id', $result->id)->orderBy('id','desc')->first();
            if($request->user()->can('purchaseorder.liststatuspo')){
                if ($result->status=='0' &&  $result->status_invoice==0) {
                    $status = '<span class="label label-primary">Baru</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small>';
                }else if($result->status=='1'){
                    $status = '<span class="label label-warning">Diproses</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small>';
                }else if($result->status=='2' || $result->status_invoice==2){
                    $status = '<span class="label label-danger">Ditolak</span><br>
                    <small class="display-block text-muted">Ditolak Oleh : '.$polog->create_user.'</small>';
                }else if($result->status=='3'){
                    $status = '<span class="label label-success">Selesai</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small>';
                }
            }
            else if($request->user()->can('purchaseorder.liststatusinvoiceawal')){

                if($result->status == 0){
                    $status = '<span class="label label-default">BELUM DIPROSES ADMIN</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>';
                }else{
                    if($result->status_invoice == '0'){
                        $status = '<span class="label label-warning">DIPROSES</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">DIPROSES INVOICE</small>';
                    }else if($result->status_invoice == 1 && $result->status_po == 2 && $result->status == 3){
                        $status = '<span class="label label-success">Selesai</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SUKSES MEMBUAT NOTA</small>';
                    }else if($result->status_invoice == '1'){
                        $status = '<span class="label label-success">SELESAI</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SELESAI PROSES INVOICE</small>';
                    }else if($result->status_invoice == '2'){
                        $status = '<span class="label label-danger">DITOLAK</span><br>
                            <small class="display-block text-muted">Ditolak Oleh : '.$polog->create_user.'</small><br>
                            <small class="display-block text-muted">DITOLAK INVOICE</small>';
                    }
                }
            }else if($request->user()->can('purchaseorder.liststatusgudang')){
                if($result->status == 0){
                    $status = '<span class="label label-default">BELUM DIPROSES ADMIN</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>';
                }if($result->status_invoice == 0){
                    $status = '<span class="label label-default">BELUM DIPROSES ADMIN INVOICE</span><br>
                    <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>';
                }else{
                    if($result->status_gudang == '0'){
                        $status = '<span class="label label-warning">DIPROSES</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">DIPROSES GUDANG</small>';
                    }else if($result->status_gudang == 1 && $result->status_po == 2 && $result->status == 3){
                        $status = '<span class="label label-success">Selesai</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SUKSES MEMBUAT NOTA</small>';
                    }else if($result->status_gudang == '1'){
                        $status = '<span class="label label-success">SELESAI</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SELESAI PROSES GUDANG</small>';
                    }else if($result->status_gudang == '2'){
                        $status = '<span class="label label-danger">DITOLAK</span><br>
                            <small class="display-block text-muted">Ditolak Oleh : '.$polog->create_user.'</small><br>
                            <small class="display-block text-muted">DITOLAK GUDANG</small>';
                    }
                }
            }else if($request->user()->can('purchaseorder.liststatusinvoice')){

                if($result->status == '0'){
                    $status = '<span class="label label-default">BELUM DIPROSES ADMIN</span><br>
                        <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>';
                }else{
                    if($result->status_gudang == '0' && $result->status_po == '0' && $result->status != 3){
                        $status = '<span class="label label-warning">DIPROSES</span><br>
                        <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                        <small class="display-block text-muted">SELESAI PROSES GUDANG</small>';
                    }else if($result->status_gudang == '1' && $result->status_po == '2' && $result->status != 3){
                        $status = '<span class="label label-success">Selesai</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SELESAI PROSES GUDANG</small>';
                    }else if($result->status_gudang == '2'){
                        $status = '<span class="label label-danger">DITOLAK</span><br>
                            <small class="display-block text-muted">Ditolak Oleh : '.$polog->create_user.'</small><br>
                            <small class="display-block text-muted">DITOLAK GUDANG</small>';
                    }else if($result->status_gudang == 1 && $result->status_po == 1 && $result->status == 3){
                        $status = '<span class="label label-success">Selesai</span><br>
                            <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small><br>
                            <small class="display-block text-muted">SUKSES MEMBUAT NOTA</small>';
                    }
                }
            }

            $statuslist = $this->actionstatus();
            $statusinvo = $this->statuspo();
            $statusgudang = $this->statusgudang();
            $statusinvoiceawal = $this->statusinvoiceawal();

            $selectedstatus = $result->status;
            $selectedgudang = $result->status_gudang;
            $selectedpo = $result->status_po;


            // Change Status Action List
            $actionstatus = '';

            if($result->status == 0 && $result->status != 3){
                if($request->user()->can('purchaseorder.liststatuspo')){
                    if($result->status_invoice == 2 &&  $result->status_gudang == 2 && $request->type =='0'){

                    }else{
                        $actionstatus.= '<select class="status form-control" id="status" onchange="change_status(this.options[this.selectedIndex].value,'.$result->id.')">';
                        foreach ($statuslist as $kunci => $value) {
                            if($kunci == $selectedstatus){
                                $selecteddetail = 'selected';
                            }else{
                                $selecteddetail = '';
                            }

                            $actionstatus.='<option value="'.$kunci.'"'.$selecteddetail.'>'.ucfirst($value).'</option>';
                        }
                        $actionstatus.= '</select>';
                    }

                }
            }else if($result->status == 1 && $result->status_invoice == 0 && $result->status != 3){
                if($request->user()->can('purchaseorder.liststatusinvoiceawal')){
                    $actionstatus.= '<select class="status form-control" id="status_invoice_awal" onchange="change_status_invoice_awal(this.options[this.selectedIndex].value,'.$result->id.')">';
                    foreach ($statusinvoiceawal as $kunci => $value) {
                        if($kunci == $statusinvoiceawal){
                            $selecteddetail = 'selected';
                        }else{
                            $selecteddetail = '';
                        }
                        $actionstatus.='<option value="'.$kunci.'"'.$selecteddetail.'>'.ucfirst($value).'</option>';
                    }
                    $actionstatus.= '</select>';
                }
            }else if($result->status == 1 && $result->status_invoice == 1 && $result->status_gudang == 0 && $result->status != 3){
                if($request->user()->can('purchaseorder.liststatusgudang')){
                    $actionstatus.= '<select class="status form-control" id="status_gudang" onchange="change_status_gudang(this.options[this.selectedIndex].value,'.$result->id.')">';
                    foreach ($statusgudang as $kunci => $value) {
                        if($kunci == $selectedgudang){
                            $selecteddetail = 'selected';
                        }else{
                            $selecteddetail = '';
                        }
                        $actionstatus.='<option value="'.$kunci.'"'.$selecteddetail.'>'.ucfirst($value).'</option>';
                    }
                    $actionstatus.= '</select>';
                }
            }else if($result->status_gudang == 1 && $result->status_po == 2 && $result->status != 3){

                if($request->user()->can('purchaseorder.liststatusinvoice')){
                    $actionstatus.= '<select class="status form-control" id="status_invoice" onchange="process_invoice(this.options[this.selectedIndex].value,'.$result->id.')">';
                    $actionstatus.= '<option value="">Pilih Action</option>';
                    foreach ($statusinvo as $kunci => $value) {
                        $actionstatus.='<option value="'.$kunci.'">'.ucfirst($value).'</option>';
                    }
                    $actionstatus.= '</select>';
                }
            }

            $customer               = Member::find($result->member_id);
            $expedisi               = Expedisi::find($result->expedisi);
            $expedisi_via           = ExpedisiVia::find($result->expedisi_via);
            $sales                  = Sales::find($result->sales_id);
            // $jumlahharitempo        = date('Y-m-d', strtotime($result->dataorder . " +120 days"));
            $jumlahharitempo        = date('Y-m-d', strtotime($result->dataorder . " +2 days"));
            $result->enc_id         = $enc_id;
            $result->id             = $result->id;
            $result->no             = $key+$page;
            $result->rpo            = $rpo;
            if($inv) {
                if($inv->pay_status==1){
                    $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-primary text-left">LUNAS</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
                }else{
                    if(date('Y-m-d') >= $jumlahharitempo ) {
                        $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left">PURCHASE ORDER <br/>SUDAH 4 BULAN / LEBIH</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
                    }else{
                        $result->customer       = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
                    }
                }
            }else{
                 if(date('Y-m-d') >= $jumlahharitempo ) {
                    $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left">PURCHASE ORDER <br/>SUDAH 4 BULAN / LEBIH</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
                }else{
                    $result->customer       = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
                }
            }
            $result->tgl_po         = date("d M Y",strtotime($result->updated_at)).'<br>'.'<small>('.Carbon::parse($result->updated_at)->diffForHumans().')</small>';
            $result->action_status  = $actionstatus;
            $result->status         = $status;
            $result->expedisi       = $expedisi->name;
            $result->sales          = $sales->name;
            if($request->user()->can('purchaseorder.liststatusgudang') && $request->user()->can('purchaseorder.liststatusinvoice')){
                $result->total      = number_format($result->total,2,',','.');
            }else if($request->user()->can('purchaseorder.liststatusgudang')){
                $result->total      = '';
            }else{
                $result->total      = number_format($result->total,2,',','.');
            }
            $result->namagudang     = $result->gudangnama;

            $result->action         = $action;
        }

        if ($request->user()->can('purchaseorder.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
        }else{
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => []
            );
        }

        return json_encode($json_data);
    }

    public function tambah(){
        // $member = Member::all();
        $member = array();
        $selectedmember ="";
        $sales = Sales::all();
        // $sales = array();
        $selectedsales ="";
        // $expedisi = Expedisi::all();
        $expedisi = array();
        $selectedexpedisi ="";
        // $expedisivia = ExpedisiVia::all();
        $expedisivia = array();
        $selectedexpedisivia ="";
        $selectedproduct ="";
        // $tipeharga = $this->jenisharga();
        $tipeharga = array();
        $selectedtipeharga ="";
        $toko = Toko::all();

        return view('backend/purchase/form',compact('tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia',
                    'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko'));
    }

    public function cekInvoiceBelumLunas(Request $req){
        $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('pay_status',1)->where('member_id',$req->member_id)->get();
        if(count($inv) > 0){
            $json_data = array(
                "success"         => TRUE,
                "info"            => 1,
            );
        }else{
            $json_data = array(
                "success"         => TRUE,
                "info"            => 0,
            );
        }
        return json_encode($json_data);

    }
    public function addproduk(Request $request){
        $total = $request->total;
        echo "
            <tr id='dataajaxproduk_".$total."'>
                <td>
                <select class='select2_produk_".$total."' id='produk_".$total."' name='produk[]' onchange='hitung(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value=''>Pilih Produk </option>
                </select>

                <td>
                <input type='text' class='form-control' name='stock_product[]' id='stock_product_".$total."' readonly>
                </td>
                <td><input type='text' class='form-control' id='harga_product_".$total."' name='harga_product[]' readonly></td>
                <td>
                <select class='select2_satuan_".$total."' id='tipe_satuan_".$total."' name='tipesatuan[]' onchange='satuan(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value='null'>Pilih Tipe Satuan </option>
                </select>
                </td>
                <td><input type='text' class='form-control touchspin".$total."' id='qty_".$total."' name='qty[]' value='1' onkeyup='hitung_qty(".$total.")' onchange='hitung_qty(".$total.")'> </td>
                <td><input type='text' class='form-control total_harga' id='total_".$total."' name='total[]' readonly></td>
                <td><a href='#!' onclick='javascript:deleteProduk(".$total.")' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
            </tr>
            <script>
                select_satuan(".$total.");
                select_product(".$total.");
                $('.touchspin".$total."').TouchSpin({
                    min: 1,
                    max: 9999999999999999999999,
                    buttondown_class: 'btn btn-white',
                    buttonup_class: 'btn btn-white'
                });


            </script>
        ";
    }

    public function search_produk(Request $request){
        $product = Product::select('*')
                    ->orWhere('kode_product', 'LIKE', "%{$request->search}%")
                    ->orWhere('nama', 'LIKE', "%{$request->search}%")
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();
        return json_encode($product);
    }
    public function search_satuan(Request $request){
        $satuan = Satuan::select('*')
                    ->orWhere('nama', 'LIKE', "%{$request->search}%")
                    ->orWhere('qty', 'LIKE', "%{$request->search}%")
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();

        return json_encode($satuan);
    }


    public function getTahun($string){
        $value = substr($string, 0, 3);
        if($value=='BO-'){
            $tahun = substr($string, 7, 2);
        }else{
            $tahun = substr($string, 4, 2);
        }
        return $tahun;
    }

    public function genRPO($sales_id)
    {
            $next_no     ='';
            $max_data_id =PurchaseOrder::where('sales_id',$sales_id)->where('kode_rpo','!=',null)->orderBy('id', 'DESC')->first();
            if ($max_data_id) {
                $datarpo      = PurchaseOrder::find($max_data_id->id);
                $koderpo      = $datarpo->kode_rpo;
                $tahunkode    = $this->getTahun($koderpo);
                if($tahunkode == date('y')){
                    $ambil = substr($koderpo, -5);
                }else{
                    $ambil = '99999';
                }
            }

            if ($max_data_id==null) {
                $next_no = '00001';
            }elseif (strlen($ambil)<5) {
                $next_no = '00001';
            }elseif ($ambil == '99999') {
                $next_no = '00001';
            }else {
                $next_no = substr('00000', 0, 5-strlen($ambil+1)).($ambil+1);
            }
            $sales             = Sales::select('code')->where('id',$sales_id)->first();

            $salescode         = $sales?$sales->code:'-';

            // {{tgl}{bln}{thn}{Kode sales}{5 digit Nomor}
            return date('dmy').$salescode.$next_no;
    }
    public function simpan(Request $req){
        // return $req->all();
        $no_transaksi           = $req->no_transaksi;
        $array_harga_product    = $req->harga_product;
        $array_product          = $req->produk;
        $array_stock_product    = $req->stock_product;
        $array_qty              = $req->qty;
        $id_sales               = $req->sales;
        $status_pembayaran      = $req->status_pembayaran; // 1 = lunas, 0 = belum lunas;
        $tgl_jatuh_tempo        = date('Y-m-d',strtotime($req->tgl_jatuh_tempo));
        $tgl_transaksi          = date('Y-m-d', strtotime($req->tgl_transaksi));
        $array_id_satuan        = $req->tipesatuan;
        $id_toko                = $req->toko;
        $array_total_harga      = $req->total;
        $total_product          = $req->total_produk;
        $total_harga_penjualan  = $req->total_harga_penjualan;
        //VALIDASI
            if($no_transaksi == null){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Nomor transaksi harus diisi'
                ]);
            }
            if($status_pembayaran == null){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Status pembayaran harus diisi'
                ]);
            }
            if(count($array_total_harga) < 1){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Product harus diisi'
                ]);
            }

            for($i=0;$i<$total_product;$i++){
                $satuan = Satuan::find($array_id_satuan[$i]);
                $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                $total_qty = $array_qty[$i] * $satuan->qty;
                if($stockadj->stock_penjualan < $total_qty){
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Stock penjualan tidak cukup'
                    ]);
                }
            }

        //END VALIDASI
        if($total_product > 0){
            $penjualan = new Penjualan;
            $penjualan->no_faktur   = $no_transaksi;
            $penjualan->id_sales    = $id_sales;
            $penjualan->id_toko     = $id_toko;
            $penjualan->tgl_jatuh_tempo = $tgl_jatuh_tempo;
            $penjualan->tgl_faktur  = $tgl_transaksi;
            $penjualan->total_harga = $total_harga_penjualan;
            $penjualan->status_lunas = $status_pembayaran;
            $penjualan->created_by  = auth()->user()->username;
            if($penjualan->save()){
                for($i=0;$i<$total_product;$i++){
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $detail_penjualan = new DetailPenjualan;
                    $detail_penjualan->id_penjualan = $penjualan->id;
                    $detail_penjualan->no_faktur = $penjualan->no_faktur;
                    $detail_penjualan->id_product = $array_product[$i];
                    $detail_penjualan->qty = $array_qty[$i] * $satuan->qty;
                    $detail_penjualan->harga_product = $array_harga_product[$i];
                    $detail_penjualan->total_harga = $array_total_harga[$i];
                    if($detail_penjualan->save()){
                        if($penjualan->status_lunas == 0){
                            $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                            $stockadj->stock_penjualan  -= $detail_penjualan->qty;
                            $stockadj->stock_approve    += $detail_penjualan->qty;
                        }else{
                            $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                            $stockadj->stock_penjualan  -= $detail_penjualan->qty;
                        }
                        if(!$stockadj->save()){
                            return response()->json([
                                'success' => FALSE,
                                'message' => 'Gagal mengupdate stock product'
                            ]);
                            break;
                        }
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Gagal menyimpan detail penjualan'
                        ]);
                    }
                }
                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi  = $penjualan->no_faktur;
                $transaksi_stock->tgl_transaksi = $tgl_transaksi;
                $transaksi_stock->flag_transaksi = 3;
                $transaksi_stock->created_by = auth()->user()->username;
                $transaksi_stock->note = '-';
                if($transaksi_stock->save()){
                    return response()->json([
                        'success' => TRUE,
                        'message' => 'Penjualan berhasil disimpan'
                    ]);
                }else{
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Penjualan gagal disimpan'
                    ]);
                }
            }else{
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Gagal menyimpan table Penjualan'
                ]);
            }
        }



    }
    public function simpan_(Request $req){

        try {
            $dt = new Carbon();
            $total = 0;
            foreach ($req->produk as $x => $value) {
                $total               += str_replace(".", "",$req->total[$x]);
            }

            $purchaseOrder                  = new PurchaseOrder();
            $purchaseOrder->kode_rpo        = $req->draft==1?null:$this->genRPO($req->sales);
            $purchaseOrder->dataorder       = $dt->toDateTimeString();
            $purchaseOrder->member_id       = $req->member;
            $purchaseOrder->sales_id        = $req->sales;
            $purchaseOrder->note            = $req->note;
            $purchaseOrder->duedate         = $dt->toDateTimeString();
            $purchaseOrder->access          = 1;
            $purchaseOrder->flag_status     = 1;
            $purchaseOrder->sub_total       = (string) $total;
            $purchaseOrder->total           = (string) $total;
            $purchaseOrder->createdby       = auth()->user()->fullname;
            $purchaseOrder->createdon       = auth()->user()->username;
            $purchaseOrder->expedisi        = $req->expedisi;
            $purchaseOrder->expedisi_via    = $req->expedisi_via;
            $purchaseOrder->draft           = $req->draft==0?0:1;
            $purchaseOrder->save();
            if($purchaseOrder){
                foreach ($req->produk as $key => $value) {
                    if($value != null){
                        $podetail                          = new PurchaseOrderDetail();
                        $satuan                            = Product::select('product.id','satuan.*','product.is_liner', 'product.product_code_shadow')
                                                                    ->join('satuan','product.satuan_id','satuan.id')
                                                                    ->where('product.id', $value)
                                                                    ->first();
                        $checkproduct                      = Product::find($value);
                        $podetail->transaction_purchase_id = $purchaseOrder->id;
                        $podetail->product_id              = $value;
                        if($satuan->is_liner == 'Y'){
                            $checkproductshadow = Product::where('product_code', 'LIKE', "%{$satuan->product_code_shadow}%")->first();
                            $podetail->product_id_shadow   = $checkproductshadow->id;
                        }
                        $podetail->discount                = 0;
                        $podetail->type                    = $req->tipeharga[$key];
                        $podetail->qty                     = $req->qty[$key];
                        $podetail->price                   = str_replace(".","",$req->hargasatuan[$key]);
                        $podetail->ttl_price               = str_replace(".", "",$req->total[$key]);
                        $podetail->satuan                  = $satuan->name;
                        $podetail->save();
                    }
                }

                $purchaselog = new PurchaseOrderLog;
                $purchaselog->user_id       = auth()->user()->id;
                $purchaselog->purchase_id   = $purchaseOrder->id;
                $purchaselog->keterangan    = $req->draft==0? auth()->user()->username." telah melakukan menambah PO data untuk DIPROSES ke RPO" : auth()->user()->username." telah melakukan menambah Draft PO" ;
                $purchaselog->create_date   = $dt->toDateTimeString();
                $purchaselog->create_user   = auth()->user()->username;
                $purchaselog->save();

                if($purchaselog) {
                    $json_data = array(
                        "success"         => TRUE,
                        "draft"           => $req->draft==0?0:1,
                        "message"         => 'Data berhasil ditambahkan.'
                    );
                }else{
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Data gagal ditambahkan.'
                    );
                }
            }
        } catch (\Throwable $th) {
            $json_data = array(
                'code' => 500,
                'success' => false,
                'msg' => $th->getMessage(),
                'message' => 'silahkan check kembali form anda'
            );
        }

        return json_encode($json_data);
    }

    // To Be Continue
    public function harga_product(Request $request){

        // $memberplus      = Member::select('member.id','type_price.name')->join('type_price','type_price.id','member.operation_price')->where('member.id',$request->member)->first();
        // $tambahan = $memberplus?$memberplus->name:0;
        $product = Product::where('id', $request->produk_id)->with(['getstock'])->first();

        return response()->json([
            'success' => TRUE,
            'data' => $product,
        ]);
    }
    public function total_harga(Request $request){
        $satuan = Satuan::find($request->satuan_id);
        return response()->json([
            'success' => TRUE,
            'data'  => $satuan,
        ]);
    }
    public function expedisi(Request $request){
        $expedisi = Expedisi::select('id', 'name')->where('status', 1)->get();
        return response()->json([
            'data' => $expedisi
        ]);
    }

    public function simpan_expedisi(Request $request){
        $dec_id = $this->safe_decode(Crypt::decryptString($request->idpo));
        $purchase           = PurchaseOrder::find($dec_id);
        $purchase->expedisi = $request->idexp;
        $purchase->save();
        $dt = new Carbon();
        if($purchase){
            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $purchase->id;
            $polog->keterangan    = auth()->user()->username." Telah Mengupdate Expedisi PO ".$purchase->no_nota;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();
        }

        return response()->json([
            'success'   => TRUE,
            'code'      => 200,
            'msg'       => 'Expedisi Berhasil Diupdate'
        ]);
    }

    public function note(Request $request){
        $poawal             = PurchaseOrder::find($request->idpo);
        $poawal->note       = $request->note;
        $poawal->save();
        $dt = new Carbon();
        if($poawal){
            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = auth()->user()->username." Telah Mengupdate Note PO ".$poawal->no_nota;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();
        }

        return response()->json([
            'success'   => TRUE,
            'code'      => 200,
            'msg'       => 'Note Berhasil Diupdate'
        ]);
    }

    public function updatepo(Request $request){
        $field = $request->field;
        $purchasedetail = PurchaseOrderDetail::find($request->id_po);
        $checkproduct = Product::find($purchasedetail->product_id);
        if($checkproduct->is_liner == 'Y'){
            //TAMBAHAN
            if($checkproduct->product_code==$checkproduct->product_code_shadow){
                //MASTER
                $satuanvalue   = $checkproduct->satuan_value;
                $productid     = $checkproduct->id;
            }else{
                //SUB
                $product       = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                $satuanvalue   = $checkproduct->satuan_value;
                $productid     = $product->id;
            }
            $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $purchasedetail->perusahaan_id)->where('gudang_id',$purchasedetail->gudang_id)->first();
            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
        }else{
            // $satuanvalue     = $checkproduct->satuan_value;
            $satuanvalue        = 1;
            $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $purchasedetail->perusahaan_id)->where('gudang_id',$purchasedetail->gudang_id)->first();
            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $purchasedetail->product_id)->first();
        }
        if($field == 'qty_kirim'){
            if($purchasedetail->qty < $request->value){
                $reqsult = 'qty more';
                if($purchasedetail->qty_kirim == NULL){
                    $qty_kirim = $purchasedetail->qty;
                    $qtykirim    = array(
                        'success'  => TRUE,
                        'description' => 'quantity pengiriman disamakan dengan permintaan',
                        'alternate'   => 'karena melebihi quantity permintaan',
                        'value'       => $purchasedetail->qty
                    );
                }else{
                    $qty_kirim = $purchasedetail->qty;
                    // $qty_sisa = $purchasedetail->qty - $purchasedetail->qty_kirim;
                    // $product_gudang->stok = $product_gudang->stok - ($qty_sisa * $satuanvalue);
                    // $product_gudang->save();
                    $qtykirim    = array(
                        'success'  => TRUE,
                        'description' => 'quantity pengiriman disamakan dengan permintaan',
                        'alternate'   => 'karena melebihi quantity permintaan',
                        'value'       => $purchasedetail->qty
                    );
                }
            }else if($purchasedetail->qty >= $request->value){
                $qty_kirim = $request->value;
                $qty_sisa = $purchasedetail->qty - $request->value;
                // if($purchasedetail->qty_kirim == NULL){
                //     $product_gudang->stok = $product_gudang->stok + ($qty_sisa * $satuanvalue);
                //     $product_gudang->save();
                // }else{
                //     if($purchasedetail->qty_kirim > $request->value){
                //         $qty_check = (($purchasedetail->qty_kirim - $request->value) * $satuanvalue);
                //         $product_gudang->stok += $qty_check;
                //         $product_gudang->save();
                //     }else{
                //         $qty_check = (($request->value - $purchasedetail->qty_kirim) * $satuanvalue);
                //         $product_gudang->stok -= $qty_check;
                //         $product_gudang->save();
                //     }
                // }
                $qtykirim    = array(
                    'success'  => FALSE,
                    'value'       => $request->value
                );
            }else{
                $qty_kirim = $request->value;
                $qtykirim    = array(
                    'success'  => FALSE,
                    'value'       => $request->value
                );
            }
            $purchasedetail->$field = $qty_kirim;
            $purchasedetail->save();

            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Berhasil diperbarui',
                'qtykirim'  => $qtykirim
            ]);
        }else{
            $qty_kirim = $request->value;
            $qtykirim = array(
                'success'  => FALSE
            );
            // $product_gudang->save();
            $purchasedetail->$field = $qty_kirim;
            $purchasedetail->save();

            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Berhasil diupdate',
                'qtykirim'  => $qtykirim
            ]);
        }

    }

    public function updatepokrnditolak(Request $request){
        $field = $request->field;
        //dd($field);
        $purchasedetail = PurchaseOrderDetail::find($request->id_po);
        if($field == 'price'){
            $purchasedetail->price     = str_replace(".", "",$request->value);
            $unitsetelahdiskon =  str_replace(".", "",$request->value) - round(($purchasedetail->discount/100 *  str_replace(".", "",$request->value)));
            $purchasedetail->ttl_price = $purchasedetail->qty * $unitsetelahdiskon;
            $purchasedetail->save();
            $updatedata = PurchaseOrderDetail::where('transaction_purchase_id',$purchasedetail->transaction_purchase_id)->get();
            $subtotal =0;
            foreach ($updatedata as $key => $value) {
                $subtotal   += round($value->ttl_price);
            }
            $fixpo	= PurchaseOrder::find($purchasedetail->transaction_purchase_id);
            $fixpo->sub_total = $subtotal;
            $fixpo->total     = $subtotal;
            $fixpo->save();
            $total_price      =  $fixpo->total;
            $subTotal         =  $fixpo->sub_total;
            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Harga Berhasil diperbarui',
                'totalsebelumdiskon'  => number_format(round(str_replace(".", "",$request->value) *  $purchasedetail->qty), 0, ',', '.'),
                'unitsetelahdiskon'   => number_format(round($unitsetelahdiskon),0, ',', '.'),
                'hargasetelahdiskon'  => number_format($purchasedetail->ttl_price),
                'total_price'         => number_format(round($total_price),2, ',', '.'),
                'subTotal'         => number_format(round($subTotal),2, ',', '.'),
            ]);
        }
        if($field == 'diskon'){
            $purchasedetail->discount  = $request->value;
            $unitsetelahdiskon         = $purchasedetail->price - round(($request->value/100 *  $purchasedetail->price));
            $purchasedetail->ttl_price = $purchasedetail->qty * $unitsetelahdiskon;
            $purchasedetail->save();
            $updatedata = PurchaseOrderDetail::where('transaction_purchase_id',$purchasedetail->transaction_purchase_id)->get();
            $subtotal =0;
            foreach ($updatedata as $key => $value) {
                $subtotal   += round($value->ttl_price);
            }
            $fixpo	= PurchaseOrder::find($purchasedetail->transaction_purchase_id);
            $fixpo->sub_total = $subtotal;
            $fixpo->total     = $subtotal;
            $fixpo->save();
            $total_price      =  $fixpo->total;
            $subTotal         =  $fixpo->sub_total;
            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Diskon Berhasil diperbarui',
                'totalsebelumdiskon'  => number_format(round($purchasedetail->price *  $purchasedetail->qty), 0, ',', '.'),
                'unitsetelahdiskon'   => number_format(round($unitsetelahdiskon),0, ',', '.'),
                'hargasetelahdiskon'  => number_format($purchasedetail->ttl_price),
                'total_price'         => number_format(round($total_price),2, ',', '.'),
                'subTotal'            => number_format(round($subTotal),2, ',', '.'),
            ]);
        }

    }

    public function detail(Request $request){
        $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $purchase = PurchaseOrder::find($dec_id);

        if($purchase->status=='2'){
            $dis='readonly';
            $buttonsimpan = "disable";
        }else{
            $dis='';
            $buttonsimpan = "";
        }

        $detail = PurchaseOrderDetail::select('id','transaction_purchase_id','gudang_id','product_id','qty','ttl_price','perusahaan_id','discount','satuan','price', 'qty_kirim','colly','colly_to','weight')->where('transaction_purchase_id', $dec_id);
        $purchasedetail = $detail->get();
        $gudang = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id)->first();
        $member = Member::find($purchase->member_id);
        $perusahaan = Perusahaan::find($purchase->perusahaan_id);
        $gudangname = Gudang::find($gudang->gudang_id);
        $header = '';
        $header.= '<tr>';
            $header.='<th>Produk</th>';
            $header.='<th>Qty Order</th>';
            if($request->user()->can('purchaseorder.liststatusgudang')){
                $header.='<th>Qty Kirim</th>';
                $header.='<th colspan="3" class="col-sm-2 text-center">Colly</th>';
                $header.='<th>Berat (Kg)</th>';
            }
            if($request->user()->can('purchaseorder.liststatuspo') || $request->user()->can('purchaseorder.liststatusinvoice')){
                $header.='<th>Diskon (%)</th>';
                $header.='<th>Unit Sebelum Diskon</th>';
                $header.='<th>Harga Total Sebelum Diskon</th>';
                $header.='<th>Unit Setelah Diskon</th>';
                $header.='<th>Harga Setelah Diskon</th>';
            }
        $header.= '</tr>';

        $html='';
        $subtotal = 0;

        foreach ($purchasedetail as $key => $value) {
            // dd();
            $total  =  round($value->ttl_price);

            $totaldiscount = ($value->discount/100)*($value->price * $value->qty);
            $unitdiskon    = round($value->price*($value->discount/100));


            $product = Product::find($value->product_id);
            $value->product_name = $product->product_name;
            $value->product_code = $product->product_code;
            $value->total_discount = round($value->ttl_price - $totaldiscount);
            $subtotal   += round($value->ttl_price);
            $value->unit_discount = $value->price - $unitdiskon;

            if($value->qty_kirim == NULL){
                $value->qty_kirim = 0;
            }else{
                $value->qty_kirim = $value->qty_kirim;
            }

            if($value->colly == NULL){
                $value->colly = 0;
            }else{
                $value->colly = $value->colly;
            }

            if($value->colly == NULL){
                $value->colly_to = 0;
            }else{
                $value->colly_to = $value->colly_to;
            }

            if($value->weight == NULL){
                $value->weight = 0;
            }else{
                $value->weight = $value->weight;
            }

            $html.='<tr>';
                $html.='<td><b>'.$value->product_name.'</b><br/><p style="color:#1c84c6">'.$value->product_code.'</p></td>';
                $html.='<td>'.$value->qty.' '.$value->satuan.'</td>';
                if($request->user()->can('purchaseorder.liststatusgudang')){
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" min="1" '.$dis.' id="qty_kirim_'.$value->id.'" name="qty_kirim" value="'.$value->qty_kirim.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td>';
                    $html.='<input class="form-control col-xs-3" type="text" id="colly_'.$value->id.'" '.$dis.'  name="colly" value="'.$value->colly.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td> - </td>';
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" id="colly_to_'.$value->id.'" '.$dis.'  name="colly_to" value="'.$value->colly_to.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" id="weight_'.$value->id.'"  '.$dis.'  name="weight" value="'.$value->weight.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                }

                if($request->user()->can('purchaseorder.liststatuspo') || $request->user()->can('purchaseorder.liststatusinvoice')){

                    if($request->user()->can('purchaseorder.updatepokrnditolak')){
                        if($purchase->status_invoice=='2'  && $purchase->status_gudang=='2' && $purchase->flag_status=='0' && $purchase->type=='2'){
                            $html.='<td class="text-left"><input class="form-control col-xs-3 editdiskon" type="text" min="0" id="diskon_'.$value->id.'" '.$dis.'  name="diskon" value="'.$value->discount.'" onchange="updatepokrntolak('.$value->id.', this.name,)"></td>';
                            $html.='<td class="text-left"><input class="form-control col-xs-3 editprice" type="text" min="0" id="price_'.$value->id.'" '.$dis.'  name="price" value="'.$value->price.'" onchange="updatepokrntolak('.$value->id.', this.name,)"></td>';
                        }else{
                            $html.='<td>'.$value->discount.'</td>';
                            $html.='<td class="text-left">'.number_format($value->price, 2, ',', '.').'</td>';
                        }

                    }else{
                        $html.='<td>'.$value->discount.'</td>';
                        $html.='<td class="text-left">'.number_format($value->price, 2, ',', '.').'</td>';
                    }
                    $html.='<td class="text-left" id="hargatotalsebelumdiskon_'.$value->id.'">'.number_format($value->price * $value->qty, 2, ',', '.').'</td>';
                    $html.='<td class="text-left" id="unitsetelahdiskon_'.$value->id.'">'.number_format($value->unit_discount, 2, ',', '.').'</td>';
                    $html.='<td class="text-left" id="hargasetelahdiskon_'.$value->id.'">'.number_format($total, 2, ',', '.').'</td>';

                }
            $html.='</tr>';


        }
        $detailtable[] = $html;
        $purchase->sub_total = $subtotal;

        // you can move this bottom code when the project done on testing by vendor and client
        // fix subtotal when error counting on RPO or BO
        // can move
        $fixpo	= PurchaseOrder::find($dec_id);
        $fixpo->sub_total = $subtotal;
        $fixpo->total     = $subtotal;
        $fixpo->save();
        // end can move

        return response()->json([
            'po'    => [
                'member' => [
                    'name'      => $member->name,
                    'phone'     => $member->phone,
                    'address'   => $member->address
                ],
                'perusahaan' => [
                    'name'      => $perusahaan->name,
                    'alamat'    => $perusahaan->address,
                    'contact'   => $perusahaan->telephone,
                    'kota'      => $perusahaan->city,
                ],
                'purchase'   => [
                    'id'        => $dec_id,
                    'no_nota'   => $purchase->no_nota,
                    'note'      => $purchase->note,
                    'gudang'    => $gudangname->name,
                    'sub_total' => number_format($purchase->sub_total, 2, ',', '.'),
                    'total'     => number_format($purchase->total, 2, ',', '.'),
                    'ttl_pesan' => date("d M Y",strtotime($purchase->created_at))
                ],

            ],
            // 'data' => $perusahaan,
            'po_detail' => $html,
            'header'    => $header,
            'detailpo'  => $purchasedetail,
            'dec_id'      => $dec_id,
            "buttonsimpan"=> $buttonsimpan,
            "dis"       => $dis
        ]);
    }
    // proses status PO
    // proses status PO
    public function status_po(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt                 = new Carbon();
        if($request->status == 1){
            $description            = auth()->user()->username." Telah Mengupdate PO untuk di proses INVOICE";
            $poawal->status         = $request->status;
            $poawal->status_gudang  = 0;
            $poawal->status_invoice = 0;
            $poawal->type           = 0;
            $poawal->updated_at    = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);


        }else if($request->status == 2){
            // DITOLAK PO LAIN-LAIN
            $poawal->note  = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);
                    if($checkproduct->is_liner == 'Y'){
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $product_shadow->id;
                        }
                        $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($value->qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $satuanvalue    = $checkproduct->satuan_value;
                        $satuanvalue       = 1;
                        $perusahaan_gudang      = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang         = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok   = $product_gudang->stok + ($value->qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "PO-".ucfirst(auth()->user()->username)." Menolak PO catatan : " .$request->note;
                //TAMBAHAN
                $poawal->flag_status    =0; //0:PO 1:RPO
                $poawal->status_rpo     =1;
                $poawal->status         =2;
                $poawal->status_gudang  =2;
                $poawal->status_invoice =2;
                $poawal->type           =1;

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $poawal->updated_at     =now();
                $poawal->save();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();

                // TAMBAH KE RPO

                $newPOtoRPO = new PurchaseOrder();
                $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                $newPOtoRPO->dataorder     = $poawal->dataorder;
                $newPOtoRPO->member_id     = $poawal->member_id;
                $newPOtoRPO->sales_id      = $poawal->sales_id;
                $newPOtoRPO->sub_total     = $poawal->sub_total;
                $newPOtoRPO->discount      = $poawal->discount;
                $newPOtoRPO->total         = $poawal->total;
                $newPOtoRPO->note          = $poawal->note;
                $newPOtoRPO->duedate       = $poawal->duedate;
                $newPOtoRPO->status        = 0;
                $newPOtoRPO->pay_status    = $poawal->pay_status;
                $newPOtoRPO->access        = $poawal->access;
                $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                $newPOtoRPO->status_gudang = $poawal->status_gudang;
                $newPOtoRPO->status_po     = $poawal->status_po;
                $newPOtoRPO->status_invoice= $poawal->status_invoice;
                $newPOtoRPO->type          = $poawal->type;
                $newPOtoRPO->flag_status   = 1;
                $newPOtoRPO->read          = $poawal->read;
                $newPOtoRPO->createdby     = $poawal->createdby;
                $newPOtoRPO->createdon     = $poawal->createdon;
                $newPOtoRPO->updatedby     = $poawal->updatedby;
                $newPOtoRPO->updatedon     = $poawal->updatedon;
                $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                $newPOtoRPO->updated_po    = $poawal->updated_po;
                $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                $newPOtoRPO->expedisi      = $poawal->expedisi;
                $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                $newPOtoRPO->update_date   = $poawal->update_date;
                $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                $newPOtoRPO->draft         = $poawal->draft;
                $newPOtoRPO->created_at    = $poawal->created_at;
                $newPOtoRPO->updated_at    = $poawal->updated_at;
                $newPOtoRPO->save();

                // DETAIL
                foreach ($podetail as $key => $detail) {
                        $newdetail = new PurchaseOrderDetail();
                        $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                        $newdetail->perusahaan_id           = $detail->perusahaan_id;
                        $newdetail->gudang_id               = $detail->gudang_id;
                        $newdetail->product_id              = $detail->product_id;
                        $newdetail->product_id_shadow       = $detail->product_id_shadow;
                        $newdetail->type                    = $detail->type;
                        $newdetail->qty                     = $detail->qty;
                        $newdetail->qty_kirim               = $detail->qty_kirim;
                        $newdetail->price                   = $detail->price;
                        $newdetail->discount                = $detail->discount;
                        $newdetail->ttl_price               = $detail->ttl_price;
                        $newdetail->weight                  = $detail->weight;
                        $newdetail->colly                   = $detail->colly;
                        $newdetail->colly_to                = $detail->colly_to;
                        $newdetail->satuan                  = $detail->satuan;
                        $newdetail->created_at              = $detail->created_at;
                        $newdetail->updated_at              = $detail->updated_at;
                        $newdetail->save();
                }

                // COPY LOG PO SEBELUMNYA
                $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                foreach ($recordlogs as $keyr => $recordlog) {
                    $pologold  = new PurchaseOrderLog();
                    $pologold->user_id       = $recordlog->user_id;
                    $pologold->purchase_id   = $newPOtoRPO->id;
                    $pologold->keterangan    = $recordlog->keterangan;
                    $pologold->create_date   = $recordlog->create_date;
                    $pologold->create_user   = $recordlog->create_user;
                    $pologold->save();
                }

                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);

            }
        }

    }

    // proses status
    public function status_gudang(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt = new Carbon();
        if($request->status == 1){
            $cek_approved_gudang = PurchaseOrder::where('status_gudang',1)->where('status_po',2)->where('id',$request->id_po)->first();
            if(!$cek_approved_gudang){
                $description = auth()->user()->username." Telah Mengupdate PO untuk diproses INVOICE AKHIR";
                $detaildata  = 0;
                $adabo       = 0;
                foreach ($podetail as $k => $item) {
                    if($item->qty != $item->qty_kirim){
                        $detaildata += 1;
                    }
                }
                if($detaildata > 0){
                    $purchaseOrderBo                  = new PurchaseOrder();
                    $purchaseOrderBo->kode_rpo        = 'BO-'.$poawal->no_nota;
                    $purchaseOrderBo->dataorder       = now();
                    $purchaseOrderBo->member_id       = $poawal->member_id;
                    $purchaseOrderBo->sales_id        = $poawal->sales_id;
                    $purchaseOrderBo->note            = null;
                    $purchaseOrderBo->duedate         = now();
                    $purchaseOrderBo->access          = 1;
                    $purchaseOrderBo->flag_status     = 2;
                    $purchaseOrderBo->sub_total       = 0;
                    $purchaseOrderBo->total           = 0;
                    $purchaseOrderBo->createdby       = auth()->user()->fullname;
                    $purchaseOrderBo->createdon       = auth()->user()->username;
                    $purchaseOrderBo->expedisi        = $poawal->expedisi;
                    $purchaseOrderBo->expedisi_via    = $poawal->expedisi_via;
                    $purchaseOrderBo->draft           = 0;
                    $purchaseOrderBo->save();
                    $adabo = 1;
                }
                foreach ($podetail as $key => $value) {
                    $product = Product::find($value->product_id);
                    if($product->product_code_shadow==null){
                        $productid     = $product->id;
                        $satuan_value  = 1;
                    }else{
                        if($product->product_code_shadow==$product->product_code){
                            $productid    = $product->id;
                            $satuan_value = $product->satuan_value;
                        }else{
                            $cekinduk      = Product::where('product_code',$product->product_code_shadow)->first();
                            $productid     = $cekinduk->id;
                            $satuan_value  = $product->satuan_value;
                        }
                    }

                    $report = new ReportStock();
                    $report->product_id             = $value->product_id;
                    $report->product_id_shadow      = $productid;
                    $report->transaction_no         = $poawal->no_nota;
                    $report->gudang_id              = $value->gudang_id;
                    $report->perusahaan_id          = $value->perusahaan_id;
                    $report->stock_input            = $value->qty_kirim;
                    $report->purchase_detail_id     = $value->id;
                    $report->note                   = 'Purchase Barang Keluar';
                    $report->keterangan             = 'Purchase Keluar';
                    $report->created_by             = auth()->user()->username;
                    $report->save();
                    // jika qty order != qty_kirim
                    $totalprice = 0;
                    if($value->qty != $value->qty_kirim){
                        $qty                               = $value->qty - $value->qty_kirim;
                        $podetail                          = new PurchaseOrderDetail();
                        $podetail->transaction_purchase_id = $purchaseOrderBo->id;
                        $podetail->product_id              = $value->product_id;
                        $podetail->product_id_shadow       = $value->product_id_shadow;
                        $podetail->discount                = $value->discount;
                        $podetail->type                    = $value->type;
                        $podetail->qty                     = $qty;
                        $podetail->price                   = $value->price;
                        $totalprice                        = $value->price-round($value->discount/100 * $value->price);
                        $podetail->ttl_price               = $totalprice * $qty;
                        $podetail->satuan                  = $value->satuan;
                        $podetail->save();

                        //update podetail
                        $updatepodetail = PurchaseOrderDetail::find($value->id);
                        // $updatepodetail->qty        = $value->qty_kirim;
                        $updatepodetail->ttl_price  = $totalprice * $value->qty_kirim;
                        $updatepodetail->save();

                        //pengembalian stok ;
                        $perusahaan_gudang     = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $qty_check             = $qty * $satuan_value;
                        $product_gudang->stok += $qty_check;
                        $product_gudang->save();
                    }
                }
                //update total
                if($adabo==1){
                    $purchaseOrderBoUpdate                  = PurchaseOrder::find($purchaseOrderBo->id);
                    $totalprice                             = PurchaseOrderDetail::where('transaction_purchase_id', $purchaseOrderBo->id)->sum('ttl_price');
                    $purchaseOrderBoUpdate->sub_total       = $totalprice;
                    $purchaseOrderBoUpdate->total           = $totalprice;
                    $purchaseOrderBoUpdate->save();

                    $dt = new Carbon();
                    $pologbo  = new PurchaseOrderLog();
                    $pologbo->user_id        = auth()->user()->id;
                    $pologbo->purchase_id    = $purchaseOrderBoUpdate->id;
                    $pologbo->keterangan     = auth()->user()->username." Telah Mengupdate PO ke Backorder ".$purchaseOrderBoUpdate->kode_rpo;
                    $pologbo->create_date    = $dt->toDateTimeString();
                    $pologbo->create_user    = auth()->user()->username;
                    $pologbo->save();

                    $total_sub_total = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->sum('ttl_price');
                    $poawal->sub_total     = $total_sub_total;
                    $poawal->total         = $total_sub_total;

                }
                $poawal->status        = $request->status;
                $poawal->status_gudang = 1;
                $poawal->status_po     = 2;
                $poawal->updated_at    = now();
                $poawal->save();


                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }else{
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }

        }else if($request->status == 2){
            // DITOLAK GUDANG
            $poawal->note     = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);
                    ReportStock::where('transaction_no',$poawal->no_nota)->where('product_id', $value->product_id)->delete();
                    if($checkproduct->is_liner == 'Y'){
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $product_shadow->id;
                        }
                        $qty                  = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue            = 1;
                        $qty                    = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $perusahaan_gudang      = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang         = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok   = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "GDG-".ucfirst(auth()->user()->username)." Menolak PO catatan : " .$request->note;
                $poawal->status_gudang  = 2;
                //TAMBAHAN TETEP JADI PO
                $poawal->flag_status    = 0; //0: PO 1: RPO 2:BO
                $poawal->status_rpo     = 1;
                $poawal->status         = 2;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->updated_at     = now();
                $poawal->type           = $request->type;
                $poawal->save();

                if($poawal){
                    $polog  = new PurchaseOrderLog();
                    $polog->user_id        = auth()->user()->id;
                    $polog->purchase_id    = $poawal->id;
                    $polog->keterangan     = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO

                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();
                }
                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);


            }else if($request->status == 2){
                $description = "GDG-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                $poawal->status_gudang = 2;
                $poawal->status_invoice =2;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status   = 0;
                $poawal->status_rpo    = 0;
                $poawal->status        = 0;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);
            }
        }
    }

    public function status_invoice_awal(Request $request){
        $poawal       = PurchaseOrder::find($request->id_po);
        $podetail     = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt           = new Carbon();
        if($request->status == 1){

            $description            = auth()->user()->username." Telah Mengupdate PO untuk diproses GUDANG";
            $poawal->status         = $request->status;
            $poawal->status_invoice = 1;
            $poawal->updated_at     = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id        = auth()->user()->id;
            $polog->purchase_id    = $poawal->id;
            $polog->keterangan     = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);

        }else if($request->status == 2){
            // DITOLAK INVOICE AWAL
            $poawal->note     = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct   = Product::find($value->product_id);
                    if($checkproduct->is_liner == 'Y'){
                        //TAMBAHAN
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow= Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $product_shadow->id;
                        }
                        // $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $qty    = $value->qty;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $qty               = $value->qty;
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue       = 1;
                        $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang    = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }

                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO dengan catatan : " .$request->note;
                $poawal->status_invoice = 2;
                //TAMBAHAN KEMBALI KE RPO
                $poawal->flag_status    = 0; // 0 PO; 1 RPO
                $poawal->status_rpo     = 1;
                $poawal->status         = 2;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->type           = $request->type;
                $poawal->updated_at     = now();
                $poawal->save();

                if($poawal){

                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                    $polog  = new PurchaseOrderLog();
                    $polog->user_id        = auth()->user()->id;
                    $polog->purchase_id    = $poawal->id;
                    $polog->keterangan     = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO
                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                }
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);


            }else if($request->type=='2'){

                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status   = 0;
                $poawal->status_rpo    = 0;
                $poawal->status        = 0;
                $poawal->status_gudang = 2;
                $poawal->status_invoice= 2;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();


                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }


        }
    }

    public function status_invoice(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt = new Carbon();
        //dd($request->type);
        if($request->status == 1){
            $description = auth()->user()->username." Telah Mengupdate PO menjadi INVOICE";
            $poawal->status_gudang = 1;
            $poawal->status_po     = 2;
            $poawal->status        = $request->status;
            $poawal->updated_at    = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);
        }else if($request->status == 2){
            $poawal->note     = $request->note;
            if($request->type=='1'){

                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);

                    if($checkproduct->is_liner == 'Y'){
                        $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();

                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $product_shadow->id;
                        }
                        $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue   = 1;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO dengan catatan : ".$request->note;
                $poawal->status_gudang = 2;
                //TAMBAHAN
                $poawal->flag_status   = 0; //0:PO 1:RPO
                $poawal->status        = 2;
                $poawal->status_rpo    = 1;
                $poawal->status_gudang = 2;
                $poawal->status_invoice= 2;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                if($poawal){

                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                    $polog  = new PurchaseOrderLog();
                    $polog->user_id       = auth()->user()->id;
                    $polog->purchase_id   = $poawal->id;
                    $polog->keterangan    = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO
                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                }

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);

            }else if($request->type=='2'){
                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                $poawal->status_invoice = 0;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status    = 0;
                $poawal->status_rpo     = 0;
                $poawal->status         = 0;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->type           = $request->type;
                $poawal->updated_at     = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id       = auth()->user()->id;
                $polog->purchase_id   = $poawal->id;
                $polog->keterangan    = $description;
                $polog->create_date   = $dt->toDateTimeString();
                $polog->create_user   = auth()->user()->username;
                $polog->save();

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);

            }
        }
    }

    public function check_gudang($idpo){
        $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $idpo)->get();
        $detaildata = 0;
        foreach ($purchasedetail as $key => $value) {
            if($value->qty != $value->qty_kirim){
                $detaildata += 1;
            }
        }
        if($detaildata > 0){
            return response()->json([
                'success'   => FALSE,
                'message'   => 'Terdapat data pada PO ini QTY Kirim berbeda dengan QTY Order, apakah anda ingin memproses?',
                'record'    => $detaildata
            ]);
        }else{
            return response()->json([
                'success'   => TRUE,
                'message'   => 'Apakah anda yakin ingin memproses data ini?',
            ]);
        }
    }

    public function showpo($idpo){
        $purchase = PurchaseOrder::find($idpo);
        $dt = new Carbon();
        return response()->json([
            'date'      => $dt->now()->format('d-m-Y'),
            'nota'      => $purchase->no_nota,
            'sub_total' => $purchase->sub_total,
            'total'     => $purchase->total,

        ]);
    }

    public function process_nota(Request $request){
        // update purchase

        try {
            $purchase = PurchaseOrder::find($request->id_po);
            $purchase->status    = 3;
            $purchase->status_po = 1;
            $purchase->save();

            // Generate Nota
            $no_nota = $this->gennoinvo($request->tanggal, $purchase);

            $invo_nota      = Invoice::where('no_nota')->first();
            $sales          = Sales::where('code',$purchase->sales_id)->first();
            $diskon         = round($purchase->total * ($request->diskon / 100));
            $grandtotal     = $purchase->total - $diskon;
            $ppn            = round($grandtotal * (10 / 100));
            $grandtotalPpn  = $grandtotal+$ppn;
            // check invoice
            if(!$invo_nota){
                // Save Invoice
                $invoice                        = new Invoice();
                $invoice->no_nota               = $no_nota;
                $invoice->purchase_no           = $purchase->no_nota;
                $invoice->dateorder             = date('Y-m-d', strtotime($request->tanggal));
                $invoice->invoice_date_tt       = date('Y-m-d', strtotime($request->tanggal));
                $invoice->member_id             = $purchase->member_id;
                $invoice->sales_id              = $purchase->sales_id;
                $invoice->perusahaan_id         = $purchase->perusahaan_id;
                $invoice->subtotal              = $purchase->sub_total;
                $invoice->discount              = $request->diskon;
                // $invoice->ppn                = 10;
                $invoice->total_before_ppn      = $grandtotal;
                $invoice->total_before_diskon   = $purchase->total;
                $invoice->total                 = $grandtotalPpn;
                $invoice->note                  = "";
                $invoice->memo                  = $request->memo;
                $invoice->expedisi              = $purchase->expedisi;
                if($purchase->expedisi_via != null){
                    $invoice->via_expedisi      = $purchase->expedisi_via;
                }
                $invoice->duedate               = date('Y-m-d', strtotime("+120 day", strtotime($request->tanggal)));
                $invoice->min_duedate           = date('Y-m-d', strtotime("+90 day", strtotime($request->tanggal)));
                $invoice->pay_status            = 0;
                $invoice->dateprint             = date('Y-m-d', strtotime($request->tanggal));
                $invoice->count_print           = 0;
                $invoice->create_user           = auth()->user()->username;
                $invoice->save();

                // make invoice detail
                if($invoice){
                    $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $purchase->id)->get();
                    foreach ($purchasedetail as $key => $value) {
                        if($value->qty_kirim != 0){
                            $productdetail = Product::where('id', $value->product_id)->first();

                            $report = ReportStock::where('transaction_no',$purchase->no_nota)->where('product_id', $value->product_id)->first();
                            if($report){
                                $report->invoice_id             = $invoice->id;
                                $report->save();
                            }

                            $invoicedetail                  = new InvoiceDetail();
                            $invoicedetail->invoice_id      = $invoice->id;
                            $invoicedetail->gudang_id       = $value->gudang_id;
                            $invoicedetail->product_code    = $productdetail->product_code;
                            $invoicedetail->product_name    = $productdetail->product_name;
                            $invoicedetail->product_img     = $productdetail->product_cover;
                            $invoicedetail->qty             = $value->qty_kirim;
                            $invoicedetail->qty_kirim       = $value->qty_kirim;
                            $invoicedetail->satuan          = $value->satuan;
                            $invoicedetail->discount        = $value->discount;
                            $invoicedetail->colly           = $value->colly;
                            $invoicedetail->colly_to        = $value->colly_to;
                            $invoicedetail->weight          = $value->weight;
                            $invoicedetail->price           = $value->price;
                            $invoicedetail->ttl_price       = $value->ttl_price;
                            $invoicedetail->save();
                        }
                    }
                }
            }

            $dt = new Carbon();
            $purchaselog = new PurchaseOrderLog;
            $purchaselog->user_id        = auth()->user()->id;
            $purchaselog->purchase_id    = $purchase->id;
            $purchaselog->keterangan     = auth()->user()->username." telah memproses PO ".$purchase->no_nota." ke pembuatan NOTA";
            $purchaselog->create_date    = $dt->toDateTimeString();
            $purchaselog->create_user    = auth()->user()->username;
            $purchaselog->save();

            $invoicedetailcolly = InvoiceDetail::where('invoice_id', $invoice->id)->orderBy('id', 'DESC')->first();

            $simpancolly = Invoice::find($invoice->id);
            $simpancolly->colly = $invoicedetailcolly->colly_to;
            $simpancolly->save();

            if($purchaselog) {
                $json_data = array(
                    "success"         => TRUE,
                    "message"         => 'Data berhasil diproses ke Invoice.'
                );
            }else{
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Data gagal diproses ke Invoice.'
                );
            }
        }
        catch (\Throwable $th) {
            $json_data = array(
                "success"         => FALSE,
                "message"         => $th->getMessage(),
            );
        }

        return json_encode($json_data);
    }

    public function print($idpo){
        $purchase = PurchaseOrder::select('transaction_purchase.id as id', 'transaction_purchase.member_id','transaction_purchase.no_nota', 'transaction_purchase.note', 'transaction_purchase.created_at',
                                            'transaction_purchase.expedisi','transaction_purchase.expedisi_via','transaction_purchase.perusahaan_id', 'transaction_purchase.no_nota', 'transaction_purchase.count_cetak',
                                            'member.id as mid', 'member.name as mname', 'member.city as mcity', 'member.address as maddress',
                                            'expedisi.id as exid', 'expedisi.name as exname',
                                            'expedisi_via.id as viaid','expedisi_via.name as vianame',
                                            'perusahaan.id as perid','perusahaan.name as pername', 'perusahaan.address as peraddress','perusahaan.city as percity',)
                                    ->leftjoin('member','member.id','transaction_purchase.member_id')
                                    ->leftjoin('expedisi', 'expedisi.id', 'transaction_purchase.expedisi')
                                    ->leftjoin('perusahaan','perusahaan.id','transaction_purchase.perusahaan_id')
                                    ->leftJoin('expedisi_via', 'expedisi_via.id','transaction_purchase.expedisi_via')
                                    ->where('transaction_purchase.id', $idpo)
                                    ->first();
        $purchasedetail = PurchaseOrderDetail::select('transaction_purchase_detail.id as id','transaction_purchase_detail.qty as qty','transaction_purchase_detail.qty_kirim as qtykirim',
                                                    'transaction_purchase_detail.satuan as satuan','transaction_purchase_detail.discount as discount','transaction_purchase_detail.price as price',
                                                    'transaction_purchase_detail.ttl_price as total','transaction_purchase_detail.perusahaan_id','transaction_purchase_detail.gudang_id',
                                                    'transaction_purchase_detail.product_id', 'transaction_purchase_detail.colly','transaction_purchase_detail.colly_to', 'transaction_purchase_detail.weight',
                                                    'product.id as prid', 'product.product_name as prname', 'product.product_desc as prdesc',
                                                    'perusahaan.id as pid', 'perusahaan.name as pname',
                                                    'gudang.id as gid','gudang.name','transaction_purchase_detail.type')
                                                ->leftjoin('product', 'product.id','transaction_purchase_detail.product_id')
                                                ->leftjoin('gudang','gudang.id', 'transaction_purchase_detail.gudang_id')
                                                ->leftjoin('perusahaan','perusahaan.id','transaction_purchase_detail.perusahaan_id')
                                                ->where('transaction_purchase_detail.transaction_purchase_id', $idpo)
                                                ->get();
        foreach ($purchasedetail as $key => $value) {
            $gudang = Gudang::select('name')->where('id', $value->gid)->first();
            $ttlnon	= $value->total * ($value->discount/100);
            $unitnon = $value->price * ($value->discount/100);
            $hargaprodukasli = Product::select('normal_price','export_price')->find($value->prid);
            //$memberplus      = Member::select('member.id','type_price.name')->join('type_price','type_price.id','member.operation_price')->where('member.id',$purchase->mid)->first();
            if($value->type=='2'){
                $harga = $hargaprodukasli->export_price;
                $hargaasli = $harga;
            }else{
                $harga = $hargaprodukasli->normal_price;
                // $tambahan = $memberplus?$memberplus->name:0;
                // $hargaasli = $tambahan==0?$harga: round($harga + ($tambahan/100 * $harga));
                $hargaasli = $harga;
            }
            $value->hargaasli  = $hargaasli;
            $value->hargarpo   = $value->price;
            $value->hargatotal = $value->total;
            $value->hargaunitsetelahdiskon = '<p style="">'.number_format($value->price - $unitnon, 0, '', '.').'</p>';
            // $value->price = $value->price - $unitnon;
            // $value->total = $value->total;
        }

        if(auth()->user()->can('purchaseorder.liststatuspo')){
            $userakses = 'po';
        }else if(auth()->user()->can('purchaseorder.liststatusgudang')){
            $userakses = 'gudang';
        }else if(auth()->user()->can('purchaseorder.liststatusinvoice')){
            $userakses = 'invoice';
        }

        if($userakses == 'gudang'){
            if($purchase->count_cetak){
                $purchase->count_cetak = $purchase->count_cetak + 1;
            }else{
                $purchase->count_cetak = 1;
            }
        }
        $purchase->save();

        $dt = new Carbon();
        $printoleh = auth()->user()->username.", ".$dt->toDateTimeString();
        if($userakses == 'gudang'){
            $purchaselog = new PurchaseOrderLog;
            $purchaselog->user_id       = auth()->user()->id;
            $purchaselog->purchase_id   = $purchase->id;
            $purchaselog->keterangan    = auth()->user()->username." telah melakukan print pada PO ".$purchase->no_nota;
            $purchaselog->create_date    = $dt->toDateTimeString();
            $purchaselog->create_user    = auth()->user()->username;
            $purchaselog->save();
        }
        return view('backend/purchase/print', compact('idpo','purchase','purchasedetail','printoleh', 'gudang','userakses'));
    }

}
