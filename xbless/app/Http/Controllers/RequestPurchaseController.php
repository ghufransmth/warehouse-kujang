<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\RequestPurchaseOrderExports;
use App\Models\ProductPerusahaanGudang;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderLog;
use App\Models\PerusahaanGudang;
use App\Models\LogDecrementStok;
use App\Models\PurchaseOrder;
use App\Models\ExpedisiVia;
use App\Models\Expedisi;
use App\Models\Perusahaan;
use App\Models\TipeHarga;
use App\Models\Product;
use App\Models\Gudang;
use App\Models\Member;
use App\Models\Sales;
use App\Models\Invoice;
use Carbon\Carbon;
use DB;
use PDF;
use Excel;

class RequestPurchaseController extends Controller
{
    protected $original_column = array(
        1 => "no_nota",
        2 => "dataorder",
        3 => "member_id",
        4 => "sales_id",
        5 => "duedate",
        6 => "status",
        7 => "pay_status",
        8 => "status_rpo",
        9 => "flag_status",
        10 => "createdby",
        11 => "createdon",
        12 => "expdisi",
        13 => "expdisi_via",
        14 => "created_at",
        15 => "updated_at"
    );

    public function statusFilter(){
        $value = array('99'=>'Semua','1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
    }

    public function status(){
        $value = array(
            '0' => 'NEW',
            '1' => 'PROSES',
            '2' => 'REJECTED',
            '3' => 'SUCCESS'
        );
        return $value;
    }

    public function pay_status(){
        $value = array(
            '0' => 'BL',
            '1' => 'L'
        );
        return $value;
    }

    public function jenisharga(){
        $value = array('1'=>'Harga Normal', '2'=>'Harga Expor');
        return $value;
    }

    public function index(){
        $perusahaan = Perusahaan::all();
        $sales = Sales::all();
        return view('backend/requestpurchase/index', compact('perusahaan', 'sales'));
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

    public function gennota($data)
    {

        $next_no     ='';
        $max_data_id =PurchaseOrder::where('perusahaan_id',$data->perusahaan_id)->where('no_nota','!=',null)->orderBy('id', 'DESC')->first();

        if ($max_data_id) {
            $datapo  = PurchaseOrder::find($max_data_id->id);
            $nota  = $datapo->no_nota;
            $tahunnota =explode('/',$nota)[3];
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
        $purchase          = PurchaseOrder::find($data->transaction_purchase_id);
        $sales             = Sales::select('code')->where('id',$purchase->sales_id)->first();
        $salescode         = $sales?$sales->code:'-';
        return $kode_perusahaan.''.$next_no.'/'.$salescode.'/'.date('m').'/'.date('y');
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $purchase = PurchaseOrder::select('id', 'kode_rpo', 'note','createdon','total',
                                    'created_at','updated_at','member_id','sales_id','expedisi','expedisi_via','status_rpo')
                                    ->where('flag_status', 1)
                                    ->where('status', 0)
                                    ->where('draft', 0);
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $purchase->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $purchase->orderBy('updated_at','DESC');
        }
         if($search) {
          $purchase->where(function ($query) use ($search) {
                  $query->orWhere('kode_rpo','LIKE',"%{$search}%");
                  $query->orWhere('no_nota','LIKE',"%{$search}%");
          });
        }
        $totalData = $purchase->get()->count();

        $totalFiltered = $purchase->get()->count();

        $purchase->limit($limit);
        $purchase->offset($start);
        $data = $purchase->get();
        foreach ($data as $key=> $result){
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $rpo ="";
            $rpo.="";
            $rpo.='<h5 class="no-margin" style="font-size:14px;"># <b>'.$result->kode_rpo.'</b></h5>';
            $rpo.='<div class="row">';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $rpo_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%DIPROSES ke RPO%')->first();
                        if($rpo_created){
                            $rpo.='<small class="display-block text-muted">RPO - '.$rpo_created->create_user.'</small><br><small style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;">'
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

            $action = "";
            $action.="";
            $action.="<div class='btn-group'>";
                if($request->user()->can('requestpurchaseorder.detail')){
                    $action.='<a href="'.route('requestpurchaseorder.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-spinner"></i> Proses</a>&nbsp;';
                }
                if($request->user()->can('requestpurchaseorder.print')){
                    $action.='<a target="_blank" href="'.route('requestpurchaseorder.print',$enc_id).'" class="btn btn-default btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-print"></i> Print</a>&nbsp;';
                }
            $action.="</div><br/><br/>";
            $action.="<div class='btn-group'>";
                if($request->user()->can('requestpurchaseorder.excel')){
                    $action.='<a target="_blank" href="'.route('requestpurchaseorder.excel',$enc_id).'" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-file-excel-o"></i> Excel</a>&nbsp;';
                }
                if($request->user()->can('requestpurchaseorder.pdf')){
                    $action.='<a target="_blank" href="'.route('requestpurchaseorder.pdf',$enc_id).'" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-file-pdf-o"></i> PDF</a>&nbsp;';
                }
                if($request->user()->can('requestpurchaseorder.cancel')){
                    $action.='<a href="#modal_cancel" id="cancel_po" role="button" data-id="'.$enc_id.'" data-toggle="modal" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="cancel" onclick="addid('.$result->id.')"><i class="fa fa-ban"></i> Cancel</a>&nbsp';
                }

            $action.="</div>";

            if ($result->status_rpo=='0') {
                $status = '<span class="label label-primary">Baru</span>';
            }else if($result->status_rpo=='1'){
                $status = '<span class="label label-warning">Ditolak</span>';
            }

            $customer               = Member::find($result->member_id);
            $expedisi               = Expedisi::find($result->expedisi);
            $expedisi_via           = ExpedisiVia::find($result->expedisi_via);
            $sales                  = Sales::find($result->sales_id);

            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->rpo            = $rpo;
            $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('member_id',$result->member_id)->where('pay_status',0)->get();
            if(count($inv) > 0){
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left"> MEMBER INI BELUM MELAKUKAN
                <br/>PEMBAYARAN PADA INVOICE</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }else{
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }
            if($result->updated_at==null){
                $result->tgl_po         = date("d M Y",strtotime($result->created_at)).'<br>'.'<small>('.Carbon::parse($result->created_at)->diffForHumans().')</small>';
            }else{
                $result->tgl_po         = date("d M Y",strtotime($result->updated_at)).'<br>'.'<small>('.Carbon::parse($result->updated_at)->diffForHumans().')</small>';
            }

            $result->status         = $status;
            if($expedisi_via){
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>'.$expedisi_via->name;
            }else{
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>';
            }
            $result->sales          = $sales->name;
            $result->total          = number_format($result->total,2,',','.');
            $result->action         = $action;
        }
        if ($request->user()->can('requestpurchaseorder.index')) {
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

    public function detail($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id) {
            $purchase = PurchaseOrder::find($dec_id);
            $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id)->get();
            $member = Member::find($purchase->member_id);
            $perusahaan = Perusahaan::select('*')->get();
            $product ='';
            foreach ($purchasedetail as $key => $value) {
                $product = Product::find($value->product_id);
                $value->product_name = $product->product_name;
                $value->product_code = $product->product_code;
                $totaldata[] = $value->id;
            }
            $totaldata="";
            $totalprice = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id)->sum('ttl_price');

            // return response()->json(['data' => $totaldata]);
            return view('backend/requestpurchase/detail',compact('enc_id','purchase','purchasedetail','member', 'perusahaan','product','totalprice','totaldata'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function process(Request $request){

        $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id);
        $perusahaan = Perusahaan::find($request->perusahaan_minta);
        $po = PurchaseOrder::find($dec_id);
        $sales = Sales::where('code',$po->sales_id)->first();
        $totaldetail = $purchasedetail->count();

        $detail = $purchasedetail->get();
        $jumlahdetail = 0;
        $jumlahisi = 0;
        $stockada = 0;
        $stockkurang = 0;

        foreach ($detail as $key => $value) {
            if($request->input('gudang_'.$value->id) == null){
                $jumlahdetail += 1;
                $datadetail[] = PurchaseOrderDetail::where('id', $value->id)->first();

            }else{
                $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                        ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                        ->first();
                $checkproduct = Product::find($value->product_id);
                if($checkproduct->is_liner == 'Y'){
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
                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();

                }else{
                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                    // $satuanvalue    = $checkproduct->satuan_value;
                    $satuanvalue    = 1;

                }
                $jumlahisi += 1;
                $dataisi[] = PurchaseOrderDetail::where('id', $value->id)->first();

                if($product_gudang->stok < ($satuanvalue *  $request->input('qty_produk_'.$value->id))){
                    $stockkurang += 1;
                }else{
                    $stockada += 1;
                    $datastok[] = $product_gudang;
                }
            }
        }

        try {
            DB::beginTransaction();
            //MASUK KE BO SEMUA
            if($jumlahdetail == $totaldetail && $jumlahisi == 0 || $stockkurang == $totaldetail){ // -> semua gudang kosong
                $updatepo = PurchaseOrder::where('id', $dec_id)->first();
                $checkBo = substr($updatepo->kode_rpo, 0, 3);

                $updatepo->perusahaan_id = $request->perusahaan_minta;
                if($checkBo !=  'BO-'){
                    $updatepo->kode_rpo   = 'BO-'.$updatepo->kode_rpo;
                }
                $updatepo->read         = 1;
                $updatepo->flag_status  = 2;
                $updatepo->updated_at   = now();
                $totaldiskon = 0;
                $total = 0;
                $totalunit = 0;
                foreach ($detail as $key => $value) {
                    $hargadiskon               = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                    $harganon                  = $request->input('sesudah_total_'.$value->id);
                    $unitnon                   = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

                    // update data detail
                    $product = Product::find($value->product_id);
                    $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                    $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                    $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                    if($product->is_liner == "Y"){
                        $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                        $detailpo->product_id_shadow = $checkproductshadow->id;
                    }
                    $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                    $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                    $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                    $detailpo->ttl_price     = round($harganon);
                    $detailpo->save();
                    if($request->input('diskon_produk_'.$value->id) != ''){
                        $totaldiskon             += $request->input('diskon_produk_'.$value->id);
                    }else{
                        $totaldiskon             += 0;
                    }
                    $total                   += $harganon;
                    $totalunit               += $unitnon;
                }

                $updatepo->discount     = $totaldiskon;
                $updatepo->sub_total    = round($total);
                $updatepo->total        = round($total);
                $updatepo->save();

                // ======= update log for backorder =======//
                $dt = new Carbon();
                $polog  = new PurchaseOrderLog();
                $polog->user_id       = auth()->user()->id;
                $polog->purchase_id   = $updatepo->id;
                $polog->keterangan    = auth()->user()->username." Telah Mengupdate Expedisi PO ke Backorder ".$updatepo->kode_rpo;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();

                DB::commit();
                $json_data = array(
                    "success"   => TRUE,
                    "backorder" => TRUE,
                    "msg"       => 'data berhasil di update ke backorder',
                    "msgAlasan" => 'karena gudang tidak diisi atau semua stok kurang'
                );
            }else if($stockkurang > 0 && $stockada >= 1 || $jumlahdetail > 0 && $jumlahisi >= 1){ // -> beberapa gudang kosong dan beberapa ada gudang
                //MASUK KE BO & PO
                $totaldiskon = 0;
                $total = 0;
                $totalunit = 0;
                    if($stockkurang > 0 || $totaldetail != 0){ // -> beberapa gudang kosong

                        foreach ($datadetail as $key => $value) {

                            $duplicate = PurchaseOrder::where('kode_rpo', 'BO-'.$po->kode_rpo)->first();

                            $hargadiskon               = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                            $harganonbo                = $request->input('sesudah_total_'.$value->id);
                            $unitnon                   = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);
                            if(!$duplicate){

                                $newduplicate                 = $po->replicate();

                                $newduplicate->kode_rpo       = 'BO-'.$newduplicate->kode_rpo;
                                $newduplicate->perusahaan_id  = $request->perusahaan_minta;
                                $newduplicate->read           = 1;
                                $newduplicate->flag_status    = 2;
                                $newduplicate->save();

                                $product = Product::find($value->product_id);

                                $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                                $detailpo->transaction_purchase_id = $newduplicate->id;
                                $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                                $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                                if($product->is_liner == "Y"){
                                    $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                                    $detailpo->product_id_shadow = $checkproductshadow->id;
                                }
                                $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                                $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                                $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                                $detailpo->updated_at    = now();
                                $detailpo->ttl_price     = round($harganonbo);
                                $detailpo->save();
                            }else{
                                // dd('duplicate');
                                $product = Product::find($value->product_id);

                                $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();

                                $detailpo->transaction_purchase_id = $duplicate->id;
                                $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                                $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                                if($product->is_liner == "Y"){
                                    $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                                    $detailpo->product_id_shadow = $checkproductshadow->id;
                                }
                                $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                                $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                                $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                                $detailpo->ttl_price     = round($harganonbo);
                                $detailpo->updated_at    = now();
                                $detailpo->save();
                            }

                            if($request->input('diskon_produk_'.$value->id) != ''){
                                $totaldiskon             += $request->input('diskon_produk_'.$value->id);
                            }else{
                                $totaldiskon             += 0;
                            }
                            $total                       += $harganonbo;
                            $totalunit                   += str_replace(".", "",$request->input('price_produk_'.$value->id)) - $unitnon;
                        }

                        $updatereplipo               = PurchaseOrder::where('kode_rpo','BO-'.$po->kode_rpo)->first();
                        $updatereplipo->discount     = $totaldiskon;
                        $updatereplipo->sub_total    = round($total);
                        $updatereplipo->updated_at   = now();
                        $updatereplipo->total        = round($total);
                        $updatereplipo->save();

                        //======= update log for backorder =======//
                        $dt = new Carbon();
                        $polog  = new PurchaseOrderLog();
                        $polog->user_id        = auth()->user()->id;
                        $polog->purchase_id    = $updatereplipo->id;
                        $polog->keterangan     = auth()->user()->username." Telah Mengupdate PO ke Backorder ".$updatereplipo->kode_rpo;
                        $polog->create_date    = $dt->toDateTimeString();
                        $polog->create_user    = auth()->user()->username;
                        $polog->save();
                    }

                    if($jumlahisi >= 1 || $stockada >= 1){ // -> beberapa gudang ada
                        //KE PO BRO
                        $dt = new Carbon();
                        foreach ($dataisi as $key => $value) {
                            $hargadiskon             = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                            $harganonpo              = $request->input('sesudah_total_'.$value->id);
                            $unitnon                 = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

                            $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                            $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                            $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                            $product = Product::find($value->product_id);
                            if($product->is_liner == "Y"){
                                $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                                $detailpo->product_id_shadow = $checkproductshadow->id;
                            }
                            $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                            $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                            $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                            $detailpo->ttl_price     = $harganonpo;
                            $detailpo->save();

                            //bisa saja master / sub nya
                            $checkproduct = Product::find($value->product_id);
                            if($checkproduct->is_liner == 'Y'){
                                $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                                ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                                ->first();

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
                                $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id',  $productid)->first();
                                $product_gudang->stok    = $product_gudang->stok - ($request->input('qty_produk_'.$value->id) * $satuanvalue);
                                $product_gudang->save();
                            }else{
                                // $satuanvalue   = $checkproduct->satuan_value;
                                $satuanvalue   = 1;
                                $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                                ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                                ->first();
                                $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                                $product_gudang->stok    = $product_gudang->stok - ($request->input('qty_produk_'.$value->id) * $satuanvalue);
                                $product_gudang->save();
                            }
                        }

                        $groupperusahaan = PurchaseOrderDetail::groupBy('perusahaan_id', 'gudang_id')->where('transaction_purchase_id', $dec_id)->get();
                        foreach ($groupperusahaan as $key => $value) {
                            $gennota = $this->gennota($value);
                            $podata  = PurchaseOrder::find($value->transaction_purchase_id);

                            $purchase                  = new PurchaseOrder();
                            $purchase->perusahaan_id   = $value->perusahaan_id;
                            $purchase->no_nota         = $gennota;
                            $purchase->kode_rpo        = $podata->kode_rpo;
                            $purchase->dataorder       = $podata->dataorder;
                            $purchase->member_id       = $podata->member_id;
                            $purchase->sales_id        = $podata->sales_id;
                            $purchase->note            = $podata->note;
                            $purchase->duedate         = $podata->duedate;
                            $purchase->createdby       = $podata->createdby;
                            $purchase->createdon       = $podata->createdon;
                            $purchase->updatedby       = auth()->user()->username;
                            $purchase->updatedon       = "Proses RPO";
                            $purchase->expedisi        = $podata->expedisi;
                            $purchase->expedisi_via    = $podata->expedisi_via;
                            $purchase->access          = 1;
                            $purchase->flag_status     = 0;
                            $purchase->status_gudang   = 0;
                            $purchase->read            = 1;
                            $purchase->created_at      = $podata->created_at;
                            $purchase->updated_at      = now();
                            $purchase->update_date_gudang   = $dt->toDateTimeString();
                            $purchase->save();

                            $discount = 0;
                            $subtotal = 0;
                            $total = 0;

                            // Update Detail PO
                            $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $podata->id)->where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->get();
                            foreach ($purchasedetail as $key => $value) {
                                $detailupdate = PurchaseOrderDetail::find($value->id);
                                $detailupdate->transaction_purchase_id = $purchase->id;
                                $detailupdate->save();

                                $checkproduct   = Product::find($value->product_id);
                                if($checkproduct->is_liner == 'Y'){
                                    $productshadow  = Product::where('product_code', 'LIKE', "%{$checkproduct->product_code_shadow}%")->first();
                                    $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                                    $stockid        = ProductPerusahaanGudang::where('product_id', $productshadow->id)->where('perusahaan_gudang_id', $productgudang->id)->first();
                                }else{
                                    $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                                    $stockid        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $productgudang->id)->where('product_id', $value->product_id)->first();
                                }

                                $hargadiskon             = round($value->price - ($value->price * $value->discount/100));
                                $totalhargadiskon        = $value->qty * $hargadiskon;
                                $harganon                = $value->qty * $value->price;
                                $unitnon                 = $value->price * ($value->price/100);

                                $subtotal += $totalhargadiskon;
                                $total    += $totalhargadiskon;
                                $discount += $value->discount;

                                $logstok                = new LogDecrementStok();
                                $logstok->no_nota       = $gennota;
                                $logstok->id_stock      = $stockid->id;
                                $logstok->decrement     = $value->qty;
                                $logstok->node          = 'process_po';
                                $logstok->created_by    = $purchase->createdon;
                                $logstok->updated_by    = auth()->user()->username;
                                $logstok->save();
                            }


                            // Update price and discount on new record po
                            $purchasecount = PurchaseOrder::find($purchase->id);
                            $purchasecount->discount  = $discount;
                            $purchasecount->sub_total = $subtotal;
                            $purchasecount->total     = round($total);
                            $purchasecount->updated_at= now();
                            $purchasecount->save();

                            // ======= update log for backorder =======//
                            $polog  = new PurchaseOrderLog();
                            $polog->user_id       = auth()->user()->id;
                            $polog->purchase_id   = $purchase->id;
                            $polog->keterangan    = auth()->user()->username." Telah Mengupdate PO untuk diproses selanjutnya";
                            $polog->create_date    = $dt->toDateTimeString();
                            $polog->create_user    = auth()->user()->username;
                            $polog->save();

                            // copy log sebelumnya ya
                            $recordlogs = PurchaseOrderLog::where('purchase_id',$dec_id)->get();
                            foreach ($recordlogs as $keyr => $recordlog) {
                                $pologold  = new PurchaseOrderLog();
                                $pologold->user_id       = $recordlog->user_id;
                                $pologold->purchase_id   = $purchase->id;
                                $pologold->keterangan    = $recordlog->keterangan;
                                $pologold->create_date   = $recordlog->create_date;
                                $pologold->create_user   = $recordlog->create_user;
                                $pologold->save();
                            }
                        }
                        // Hapus Old Record
                        PurchaseOrder::where('id', $dec_id)->delete();
                    }
                DB::commit();
                $json_data = array(
                    "success"       => TRUE,
                    "splitdata"     => TRUE,
                    "msg"           => 'data berhasil di update',
                    "msgalternate"  => 'beberapa request dialihkan ke backorder',
                    "msgAlasan"     => 'karena beberapa gudang kosong dan stok kurang'
                );
            }else if($jumlahdetail == 0 && $jumlahisi == $totaldetail || $stockada == $totaldetail){ // semua gudang ada
                //MASUK KE PO SEMUA
                $dt = new Carbon();
                // Save all input data
                foreach ($dataisi as $key => $value) {
                    $harganon                = $request->input('sesudah_total_'.$value->id);
                    $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                    $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                    $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                    $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                    $product = Product::find($value->product_id);
                    if($product->is_liner == "Y"){
                        if($product->product_code==$product->product_code_shadow){
                            $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code}%")->first();
                            $detailpo->product_id_shadow = $checkproductshadow->id;
                        }else{
                            $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                            $detailpo->product_id_shadow = $checkproductshadow->id;
                        }

                    }

                    $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                    $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                    $detailpo->ttl_price     = $harganon;
                    $detailpo->save();

                    // Potong pada Stok
                    $checkproduct = Product::find($value->product_id);
                    if($checkproduct->is_liner == 'Y'){
                        $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                        ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                        ->first();
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
                        $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok    = $product_gudang->stok - ($request->input('qty_produk_'.$value->id) * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $satuanvalue       = $checkproduct->satuan_value;
                        $satuanvalue       = 1;
                        $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                        ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                        ->first();
                        $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok    = $product_gudang->stok - ($satuanvalue * $request->input('qty_produk_'.$value->id));
                        $product_gudang->save();
                    }

                    $data[] = $detailpo;
                }

                $groupperusahaan = PurchaseOrderDetail::groupBy('perusahaan_id', 'gudang_id')->where('transaction_purchase_id', $dec_id)->get();
                foreach ($groupperusahaan as $key => $value) {
                    $gennota = $this->gennota($value);
                    $podata  = PurchaseOrder::find($value->transaction_purchase_id);

                    $purchase                       = new PurchaseOrder();
                    $purchase->perusahaan_id        = $value->perusahaan_id;
                    $purchase->no_nota              = $gennota;
                    $purchase->kode_rpo             = $podata->kode_rpo;
                    $purchase->dataorder            = $podata->dataorder;
                    $purchase->member_id            = $podata->member_id;
                    $purchase->sales_id             = $podata->sales_id;
                    $purchase->note                 = $podata->note;
                    $purchase->duedate              = $podata->duedate;
                    $purchase->createdby            = $podata->createdby;
                    $purchase->createdon            = $podata->createdon;
                    $purchase->updatedby            = auth()->user()->username;
                    $purchase->updatedon            = "Proses RPO";
                    $purchase->expedisi             = $podata->expedisi;
                    $purchase->expedisi_via         = $podata->expedisi_via;
                    $purchase->access               = 1;
                    $purchase->flag_status          = 0;
                    $purchase->status_gudang        = 0;
                    $purchase->read                 = 1;
                    $purchase->created_at           = $podata->created_at;
                    $purchase->updated_at           = now();
                    $purchase->update_date_gudang   = $dt->toDateTimeString();
                    $purchase->save();

                    $discount = 0;
                    $subtotal = 0;
                    $total = 0;

                    // Update Detail PO
                    $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $podata->id)->where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->get();
                    foreach ($purchasedetail as $key => $value) {
                        $detailupdate = PurchaseOrderDetail::find($value->id);
                        $detailupdate->transaction_purchase_id = $purchase->id;
                        $detailupdate->save();

                        $checkproduct   = Product::find($value->product_id);
                        if($checkproduct->is_liner == 'Y'){
                            $productshadow = Product::where('product_code', 'LIKE', "%{$checkproduct->product_code_shadow}%")->first();
                            $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                            $stockid        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $productgudang->id)->where('product_id', $productshadow->id)->first();
                        }else{
                            $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                            $stockid        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $productgudang->id)->where('product_id', $value->product_id)->first();
                        }

                        $hargadiskon             = round($value->price - ($value->price * $value->discount/100));
                        $totalhargadiskon        = $value->qty * $hargadiskon;
                        $harganon                = $value->qty * $value->price;
                        $unitnon                 = $value->price * ($value->price/100);

                        $subtotal += $totalhargadiskon;
                        $total    += $totalhargadiskon;
                        $discount += $value->discount;

                        $logstok                = new LogDecrementStok();
                        $logstok->no_nota       = $gennota;
                        $logstok->id_stock      = $stockid->id;
                        $logstok->decrement     = $value->qty;
                        $logstok->node          = 'process_po';
                        $logstok->created_by    = $purchase->createdon;
                        $logstok->updated_by    = auth()->user()->username;
                        $logstok->save();
                    }

                    // Update price and discount on new record po
                    $purchasecount = PurchaseOrder::find($purchase->id);
                    $purchasecount->discount  = $discount;
                    $purchasecount->sub_total = $subtotal;
                    $purchasecount->total     = $subtotal;
                    $purchasecount->updated_at = now();
                    $purchasecount->save();

                    // ======= update log for backorder =======//
                    $polog  = new PurchaseOrderLog();
                    $polog->user_id        = auth()->user()->id;
                    $polog->purchase_id    = $purchase->id;
                    $polog->keterangan     = auth()->user()->username." Telah Mengupdate PO untuk diproses selanjutnya";
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // copy log sebelumnya ya
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$dec_id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $purchase->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                }


                PurchaseOrder::where('id', $dec_id)->delete();
                DB::commit();
                $json_data = array(
                    "success"       => TRUE,
                    "clear"         => TRUE,
                    "msg"           => 'data berhasil di update'
                );
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $json_data = array(
                'code' => 500,
                'success' => false,
                'msg' => $th->getMessage(),
            );
        }

        return json_encode($json_data);
    }

    public function perusahaan(Request $request){
        $id = $request->id_perusahaan;

        $gudang = PerusahaanGudang::select('perusahaan_gudang.id as id','perusahaan_gudang.gudang_id as persgudang','perusahaan_gudang.perusahaan_id as persid',
                                            'perusahaan.id as pid','perusahaan.name as pname','gudang.id as gid','gudang.name as gname')
                                    ->join('perusahaan', 'perusahaan_gudang.perusahaan_id','perusahaan.id')
                                    ->join('gudang','perusahaan_gudang.gudang_id','gudang.id')
                                    ->where('perusahaan_id', $id)
                                    ->get();

        if($gudang){
            $product = array();
            foreach ($gudang as $key => $value) {
                $checkproduct = Product::find($request->id_product);

                if($checkproduct->is_liner == 'Y'){
                    if($checkproduct->product_code==$checkproduct->product_code_shadow){
                        //MASTER
                        $satuan_value   = $checkproduct->satuan_value;
                        $product_id     = $checkproduct->id;
                    }else{
                        //SUB
                        $product_shadow = Product::where('product_code','like', $checkproduct->product_code_shadow)->first();
                        $satuan_value   = $checkproduct->satuan_value;
                        $product_id     = $product_shadow->id;
                    }


                    // $product_id    = $product_shadow->id;
                    // $satuan_value  = $product_shadow->satuan_value;
                }else{
                    $product_id = $request->id_product;
                    // $satuan_value  = $checkproduct->satuan_value;
                    $satuan_value  = 1;
                }

                $product_gudang = ProductPerusahaanGudang::select('product_perusahaan_gudang.id as id', 'product_perusahaan_gudang.perusahaan_gudang_id as pergudang','product_perusahaan_gudang.product_id as perpro','product_perusahaan_gudang.stok as prostok',
                                                                    'product.id as proid','product.product_name as proname','perusahaan_gudang.id as perguid','perusahaan_gudang.gudang_id')
                                                            ->join('perusahaan_gudang','product_perusahaan_gudang.perusahaan_gudang_id','perusahaan_gudang.id')
                                                            ->join('product','product_perusahaan_gudang.product_id','product.id')
                                                            ->where('product_perusahaan_gudang.perusahaan_gudang_id', $value->id)
                                                            ->where('product_perusahaan_gudang.product_id', $product_id)
                                                            ->where('product_perusahaan_gudang.stok','>','0')
                                                            ->first();

                if($product_gudang){
                    $product_gudang['gudang_name'] = $value->gname;
                    $product_gudang['qty'] = floor($product_gudang->prostok / $satuan_value);
                }

                if($product_gudang != null){
                    $product[] = $product_gudang;
                }
                // else{
                //     $product = array();
                // }

            }
            $detailproduct = $product;

        }

        return response()->json([
            'data' => $detailproduct
        ]);
    }


    public function pdf($idpo){
        $dec_id = $this->safe_decode(Crypt::decryptString($idpo));
        $purchase = PurchaseOrder::select('transaction_purchase.id as id', 'transaction_purchase.member_id','transaction_purchase.kode_rpo', 'transaction_purchase.note', 'transaction_purchase.created_at',
                                            'transaction_purchase.expedisi','transaction_purchase.expedisi_via','transaction_purchase.perusahaan_id','transaction_purchase.total','transaction_purchase.dataorder',
                                            'member.id as mid', 'member.name as mname', 'member.city as mcity', 'member.address as maddress','member.prov',
                                            'expedisi.id as exid', 'expedisi.name as exname',
                                            'expedisi_via.id as viaid','expedisi_via.name as vianame',
                                            'perusahaan.id as perid','perusahaan.name as pername', 'perusahaan.address as peraddress','perusahaan.city as percity',
                                            'sales.id as sid','sales.name as sname','sales.code as scode')
                                    ->leftJoin('member','member.id','transaction_purchase.member_id')
                                    ->leftJoin('sales','sales.id','transaction_purchase.sales_id')
                                    ->leftJoin('expedisi', 'expedisi.id', 'transaction_purchase.expedisi')
                                    ->leftJoin('perusahaan','perusahaan.id','transaction_purchase.perusahaan_id')
                                    ->leftJoin('expedisi_via', 'expedisi_via.id','transaction_purchase.expedisi_via')
                                    ->where('transaction_purchase.id', $dec_id)
                                    ->first();
        $purchasedetail = PurchaseOrderDetail::select('transaction_purchase_detail.id as id','transaction_purchase_detail.qty as qty','transaction_purchase_detail.qty_kirim as qtykirim',
                                                    'transaction_purchase_detail.satuan as satuan','transaction_purchase_detail.discount as discount','transaction_purchase_detail.price as price',
                                                    'transaction_purchase_detail.ttl_price as total','transaction_purchase_detail.perusahaan_id','transaction_purchase_detail.gudang_id',
                                                    'transaction_purchase_detail.product_id', 'transaction_purchase_detail.colly', 'transaction_purchase_detail.weight',
                                                    'product.id as prid', 'product.product_name as prname', 'product.product_desc as prdesc',
                                                    'perusahaan.id as pid', 'perusahaan.name as pname',
                                                    'gudang.id as gid','gudang.name')
                                                ->leftjoin('product', 'product.id','transaction_purchase_detail.product_id')
                                                ->leftjoin('gudang','gudang.id', 'transaction_purchase_detail.gudang_id')
                                                ->leftjoin('perusahaan','perusahaan.id','transaction_purchase_detail.perusahaan_id')
                                                ->where('transaction_purchase_detail.transaction_purchase_id', $dec_id)
                                                ->get();

        $dt = new Carbon();
        $printoleh = auth()->user()->username.", ".$dt->toDateTimeString();

        $totalharga = 0;
        foreach ($purchasedetail as $key => $value) {
            $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
            $value->totalsesudah     = $value->qty * $hargadiskon;
            $value->unitsesudah      = $hargadiskon;
            $totalharga += $value->totalsesudah;
        }
        if($purchase->total !=  $totalharga){
            $purchase->total = $totalharga;
            $purchase->save();
        }
        $pdf = PDF::loadView('backend.requestpurchase.pdf',['idpo' => $idpo, 'purchase' => $purchase, 'printoleh' => $printoleh,'purchasedetail' => $purchasedetail, 'totalharga' => $totalharga]);
        ob_get_clean();
        return $pdf->stream('purchase.pdf');
    }

    public function print($idpo){
        $dec_id = $this->safe_decode(Crypt::decryptString($idpo));
        $purchase = PurchaseOrder::select('transaction_purchase.id as id', 'transaction_purchase.member_id','transaction_purchase.kode_rpo', 'transaction_purchase.note', 'transaction_purchase.created_at',
                                            'transaction_purchase.expedisi','transaction_purchase.expedisi_via','transaction_purchase.perusahaan_id','transaction_purchase.total','transaction_purchase.dataorder',
                                            'member.id as mid', 'member.name as mname', 'member.city as mcity', 'member.address as maddress','member.prov',
                                            'expedisi.id as exid', 'expedisi.name as exname',
                                            'expedisi_via.id as viaid','expedisi_via.name as vianame',
                                            'perusahaan.id as perid','perusahaan.name as pername', 'perusahaan.address as peraddress','perusahaan.city as percity',
                                            'sales.id as sid','sales.name as sname','sales.code as scode')
                                    ->leftJoin('member','member.id','transaction_purchase.member_id')
                                    ->leftJoin('sales','sales.id','transaction_purchase.sales_id')
                                    ->leftJoin('expedisi', 'expedisi.id', 'transaction_purchase.expedisi')
                                    ->leftJoin('perusahaan','perusahaan.id','transaction_purchase.perusahaan_id')
                                    ->leftJoin('expedisi_via', 'expedisi_via.id','transaction_purchase.expedisi_via')
                                    ->where('transaction_purchase.id', $dec_id)
                                    ->first();
        $purchasedetail = PurchaseOrderDetail::select('transaction_purchase_detail.id as id','transaction_purchase_detail.qty as qty','transaction_purchase_detail.qty_kirim as qtykirim',
                                                    'transaction_purchase_detail.satuan as satuan','transaction_purchase_detail.discount as discount','transaction_purchase_detail.price as price',
                                                    'transaction_purchase_detail.ttl_price as total','transaction_purchase_detail.perusahaan_id','transaction_purchase_detail.gudang_id',
                                                    'transaction_purchase_detail.product_id', 'transaction_purchase_detail.colly', 'transaction_purchase_detail.weight',
                                                    'product.id as prid', 'product.product_name as prname', 'product.product_desc as prdesc',
                                                    'perusahaan.id as pid', 'perusahaan.name as pname',
                                                    'gudang.id as gid','gudang.name')
                                                ->leftjoin('product', 'product.id','transaction_purchase_detail.product_id')
                                                ->leftjoin('gudang','gudang.id', 'transaction_purchase_detail.gudang_id')
                                                ->leftjoin('perusahaan','perusahaan.id','transaction_purchase_detail.perusahaan_id')
                                                ->where('transaction_purchase_detail.transaction_purchase_id', $dec_id)
                                                ->get();

        $dt = new Carbon();
        $printoleh = auth()->user()->username.", ".$dt->toDateTimeString();
        $totalharga = 0;
        foreach ($purchasedetail as $key => $value) {
            $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
            $value->totalsesudah     = $value->qty * $hargadiskon;
            $value->unitsesudah      = $hargadiskon;
            $totalharga += $value->totalsesudah;
        }
        if($purchase->total !=  $totalharga){
            $purchase->total = $totalharga;
            $purchase->save();
        }
        return view('backend/requestpurchase/print', compact('purchase','purchasedetail','printoleh','idpo','totalharga'));
    }

    public function excel($idpo){
        $dec_id = $this->safe_decode(Crypt::decryptString($idpo));
        $purchase = PurchaseOrder::find($dec_id);
        $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_detail.transaction_purchase_id', $dec_id)->get();
        $totalharga = 0;
        foreach ($purchasedetail as $key => $value) {
            $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
            $value->totalsesudah     = $value->qty * $hargadiskon;
            $value->unitsesudah      = $hargadiskon;
            $totalharga += $value->totalsesudah;
        }
        if($purchase->total !=  $totalharga){
            $purchase->total = $totalharga;
            $purchase->save();
        }
        return Excel::download(new RequestPurchaseOrderExports($dec_id),'Request-Purchase-Order.xlsx');
    }

    public function cancel(Request $request){
        $dt = new Carbon();
        $purchase = PurchaseOrder::find($request->enc_id);
        $purchase->status     = 2;
        $purchase->status_rpo = 1;
        $purchase->note = $request->note;
        $purchase->save();
        if($request->status_po_rpo_bo==0){
           $ket = auth()->user()->username." Telah membatalkan PO";
        }else if($request->status_po_rpo_bo==1){
           $ket = auth()->user()->username." Telah membatalkan RPO";
        }else if($request->status_po_rpo_bo==2){
           $ket = auth()->user()->username." Telah membatalkan BO";
        }

        $polog  = new PurchaseOrderLog();
        $polog->user_id        = auth()->user()->id;
        $polog->purchase_id    = $purchase->id;
        $polog->keterangan     = $ket;
        $polog->create_date    = $dt->toDateTimeString();
        $polog->create_user    = auth()->user()->username;
        $polog->save();

        return response()->json([
            'success' => TRUE,
            'code' => 200,
            'msg'  => 'status berhasil dirubah'
        ]);
    }

}
