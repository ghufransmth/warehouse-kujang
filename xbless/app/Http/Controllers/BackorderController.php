<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
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
use App\Models\Invoice;
use App\Models\Sales;
use Carbon\Carbon;
use DB;

class BackorderController extends Controller
{
    protected $original_column = array(
        1 => "kode_rpo",
        2 => "member_id",
        3 => "dataorder",
        4 => "status",
        5 => "expedisi",
        6 => "sales_id",
        7 => "total",

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
        return view('backend/backorder/index');
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

    // private function gennota($data){
    //     $POall = PurchaseOrder::orderBy('id', 'DESC')->first();
    //     if($POall != null || $POall > 0){
    //         $idocde = $POall->id + 1;
    //     }else{
    //         $idocde = 0;
    //     }
    //     $purchase = PurchaseOrder::find($data->transaction_purchase_id);
    //     $nmperusahaan = Perusahaan::find($data->perusahaan_id);

    //     $singkatan = $nmperusahaan->kode;
    //     $sales = Sales::select('code')->where('id',$purchase->sales_id)->first();
    //     $result_nota = $singkatan.sprintf("%'.05d", $POall->id).'/'.$sales->code.'/'.date('m').'/'.date('y');
    //     return $result_nota;
    // }
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

        $purchase = PurchaseOrder::select('id', 'kode_rpo', 'note','createdon','total','discount', 'sub_total',
                                    'created_at','updated_at','member_id','sales_id','expedisi','expedisi_via','status_rpo')
                                    ->where('flag_status', 2)
                                    ->where('status', 0);
                                    // ->where('status_rpo', 0);
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $purchase->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
          $purchase->orderBy('updated_at','DESC');
        }
         if($search) {
          $purchase->where(function ($query) use ($search) {
                  $query->orWhere('kode_rpo','LIKE',"%{$search}%");
                  $query->orWhere('note','LIKE',"%{$search}%");
          });
        }
        $totalData = $purchase->get()->count();

        $totalFiltered = $purchase->get()->count();

        $purchase->limit($limit);
        $purchase->offset($start);
        $data = $purchase->get();
        foreach ($data as $key=> $result){
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $rpo = "";
            $rpo.="";
            $rpo.='<h5 class="no-margin" style="margin-right:150px;font-size:14px;"># <b>'.$result->kode_rpo.'</b></h5>';
            $rpo.='<div>';
            $rpo.='<div class="row">';
                $rpo.='<div class="col-6" style="margin-bottom:10px;">';
                        $rpo_created = PurchaseOrderLog::where('purchase_id',$result->id)->where('keterangan', 'LIKE', '%PO ke Backorder%')->first();
                        $type = 'BO';
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
            $action = "";
            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('backorder.detail')){
                $action.='<a href="'.route('backorder.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-spinner"></i> Proses</a>&nbsp;';
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

            $totalharga = 0;
            $result->no             = $key+$page;
            $detail = PurchaseOrderDetail::where('transaction_purchase_id', $result->id)->get();
            foreach($detail as $key => $value) {
                $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
                $harganon       = $value->qty * $value->price;
                $unitnon        = $value->price * ($value->discount/100);

                $totalharga += round($hargadiskon * $value->qty);
            }

            $customer               = Member::find($result->member_id);
            $expedisi               = Expedisi::find($result->expedisi);
            $expedisi_via           = ExpedisiVia::find($result->expedisi_via);
            $sales                  = Sales::find($result->sales_id);


            $result->id             = $result->id;
            $result->rpo            = $rpo;
            $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('member_id',$result->member_id)->where('pay_status',0)->get();
            if(count($inv) > 0){
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left"> MEMBER INI BELUM MELAKUKAN
                <br/>PEMBAYARAN PADA INVOICE</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }else{
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }

            $result->tgl_po         = date("d M Y",strtotime($result->updated_at)).'<br>'.'<small>('.Carbon::parse($result->updated_at)->diffForHumans().')</small>';
            $result->status         = $status;
            if($expedisi_via){
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>'.$expedisi_via->name;
            }else{
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>';
            }
            $result->sales          = $sales->name;
            $result->total          = number_format($totalharga,2,',','.');
            $result->action         = $action;
        }
        if ($request->user()->can('backorder.index')) {
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
            if(count($purchasedetail)==0){
                dd('Silahkan hapus transaksi ini dikarenakan transaksi tidak memiliki produk');
            }
            $member = Member::find($purchase->member_id);
            $perusahaan = Perusahaan::select('*')->get();
            $detailperusahaan = Perusahaan::find($purchase->perusahaan_id);
            $totalharga = 0;
            foreach ($purchasedetail as $key => $value) {
                $product = Product::find($value->product_id);

                $value->product_name = $product?$product->product_name:'-';
                $value->productcode  = $product?$product->product_code:'-';
                $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
                $harganon       = $value->qty * $value->price;
                $unitnon        = $value->price * ($value->discount/100);



                $value->totalsebelum     = round($harganon);
                $value->unitsesudah      = $hargadiskon;
                $value->totalsesudah     = round($hargadiskon * $value->qty);

                $totaldata[] = $value->id;
                $totalharga += round($hargadiskon * $value->qty);
            }


            $totalprice = $totalharga;

            // return response()->json(['data'])
            return view('backend/backorder/detail',compact('enc_id','purchase','purchasedetail','member', 'perusahaan','detailperusahaan','product','totalprice','totaldata'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function process(Request $request){


        $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id);
        $po = PurchaseOrder::find($dec_id);
        $perusahaan = Perusahaan::find($po->perusahaan_id);
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
                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                }else{
                    // $satuanvalue   = $checkproduct->satuan_value;
                    $satuanvalue   = 1;
                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
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

        try{
            DB::beginTransaction();
                if($jumlahdetail == $totaldetail && $jumlahisi == 0 || $stockkurang == $totaldetail){
                    // KE BACK ORDER
                    $updatepo = PurchaseOrder::where('id', $dec_id)->first();
                    $checkBo = substr($updatepo->kode_rpo, 0, 3);

                    // $updatepo->perusahaan_id = $request->perusahaan_minta;
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
                        $hargadiskon             = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                        //$harganon              = $request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id));
                        $harganonbo              = $request->input('sesudah_total_'.$value->id);

                        $unitnon                 = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

                        // update data detail
                        $product = Product::find($value->product_id);
                        $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                        $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                        $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                        if($product->is_liner == "Y"){
                            $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                            $detailpo->product_id_shadow = $checkproductshadow->id;
                        }
                        $detailpo->qty          = $request->input('qty_produk_'.$value->id);
                        $detailpo->price        = str_replace(".", "",$request->input('price_produk_'.$value->id));
                        $detailpo->discount     = $request->input('diskon_produk_'.$value->id);
                        $detailpo->ttl_price    = round($harganonbo);
                        $detailpo->save();
                        $totaldiskon            += $request->input('diskon_produk_'.$value->id);
                        $total                  += round($harganonbo);
                        $totalunit		        += round(str_replace(".", "",$request->input('price_produk_'.$value->id)) - $unitnon);

                    }


                    $updatepo->discount     = $totaldiskon;
                    $updatepo->sub_total    = round($total);
                    $updatepo->total        = round($total);
                    $updatepo->save();

                    $dt = new Carbon();
                    $polog  = new PurchaseOrderLog();
                    $polog->user_id       = auth()->user()->id;
                    $polog->purchase_id   = $updatepo->id;
                    $polog->keterangan    = auth()->user()->username." Telah Mengupdate Expedisi PO ke Backorder ".$updatepo->no_nota;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    $json_data = array(
                        "success"   => TRUE,
                        "backorder" => TRUE,
                        "msg"       => 'data berhasil di update ke backorder',
                        "msgAlasan" => 'karena gudang tidak diisi atau semua stok kurang'
                    );
                }else if($stockkurang > 0 && $stockada >= 1 || $jumlahdetail > 0 && $jumlahisi >= 1){
                        if($stockkurang > 0 || $totaldetail != 0){
                            $totaldiskon = 0;
                            $total = 0;
                            $totalunit = 0;
                            foreach ($datadetail as $key => $value) {
                                $updatepo = PurchaseOrder::where('id', $dec_id)->first();
                                $checkBo  = substr($updatepo->kode_rpo, 0, 3);
                                // $updatepo->perusahaan_id = $request->perusahaan_minta;
                                $duplicate = PurchaseOrder::where('kode_rpo', $po->kode_rpo)->first();
                                // condition where gudang->null > 1
                                $hargadiskon             = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                                $harganonbo                  = $request->input('sesudah_total_'.$value->id);
                                $unitnon                 = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

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
                                $detailpo->save();

                                $totaldiskon                 += $request->input('diskon_produk_'.$value->id);
                                $total                       += round($harganonbo);
                                $totalunit		        += round(str_replace(".", "",$request->input('price_produk_'.$value->id)) - $unitnon);
                            }

                            $updatereplipo = PurchaseOrder::where('kode_rpo', $po->kode_rpo)->first();
                            $updatereplipo->discount     = $totaldiskon;
                            $updatereplipo->sub_total    = round($total);
                            $updatereplipo->total        = round($total);
                            $updatereplipo->updated_at   = now();
                            $updatereplipo->save();


                            $dt = new Carbon();
                            $polog  = new PurchaseOrderLog();
                            $polog->user_id       = auth()->user()->id;
                            $polog->purchase_id   = $updatereplipo->id;
                            $polog->keterangan    = auth()->user()->username." Telah Mengupdate Expedisi PO ke Backorder ".$updatereplipo->no_nota;
                            $polog->create_date    = $dt->toDateTimeString();
                            $polog->create_user    = auth()->user()->username;
                            $polog->save();
                        }

                        if($jumlahisi >= 1 || $stockada >= 1){
                            foreach ($dataisi as $key => $value) {
                                $hargadiskon             = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                                $harganonpo              = $request->input('sesudah_total_'.$value->id);
                                $unitnon                 = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

                                $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                                $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                                $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                                $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                                $product = Product::find($value->product_id);
                                if($product->is_liner == "Y"){
                                    $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                                    $detailpo->product_id_shadow = $checkproductshadow->id;
                                }
                                $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                                $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                                $detailpo->ttl_price     = round($harganonpo);
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


                                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $product->id)->first();
                                    $product_gudang->stok    = ($product_gudang->stok - $request->input('qty_produk_'.$value->id) * $satuanvalue);
                                    $product_gudang->save();

                                    // $product = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                                    // $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $product->id)->first();
                                    // $product_gudang->stok    = $product_gudang->stok - $request->input('qty_produk_'.$value->id);
                                    // $product_gudang->save();
                                }else{
                                    $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                                    ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                                    ->first();
                                    $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                                    $product_gudang->stok    = $product_gudang->stok - $request->input('qty_produk_'.$value->id);
                                    $product_gudang->save();
                                }

                                // $data[] = $detailpo;
                            }

                            $groupperusahaan = PurchaseOrderDetail::groupBy('perusahaan_id', 'gudang_id')->where('transaction_purchase_id', $dec_id)->get();
                            // return response()->json(['data' => $groupperusahaan]);
                            foreach ($groupperusahaan as $key => $value) {
                                if($value->perusahaan_id != NULL || $value->gudang_idd != NULL){
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
                                    $purchase->save();

                                    $discount = 0;
                                    $subtotal = 0;
                                    $total = 0;

                                    // Update Detail PO
                                    $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $podata->id)->where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->get();
                                    foreach ($purchasedetail as $key => $value) {
                                        $detailupdate   = PurchaseOrderDetail::find($value->id);
                                        $checkproduct   = Product::find($value->product_id);
                                        if($checkproduct->is_liner == 'Y'){
                                            $productshadow = Product::where('product_code', 'LIKE', "%{$checkproduct->product_code_shadow}%")->first();
                                            $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                                            $stockid        = ProductPerusahaanGudang::where('product_id', $productshadow->id)->where('perusahaan_gudang_id', $productgudang->id)->first();
                                        }else{
                                            $productgudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id', $value->gudang_id)->first();
                                            $stockid        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $productgudang->id)->where('product_id', $value->product_id)->first();
                                        }

                                        $detailupdate->transaction_purchase_id = $purchase->id;
                                        $detailupdate->save();

                                        // Update Pengurangan Stock
                                        $logstok                = new LogDecrementStok();
                                        $logstok->no_nota       = $gennota;
                                        $logstok->id_stock      = $stockid->id;
                                        $logstok->decrement     = $value->qty;
                                        $logstok->node          = 'process_po';
                                        $logstok->created_by    = $purchase->createdon;
                                        $logstok->updated_by    = auth()->user()->username;
                                        $logstok->save();

                                        $hargadiskon             = $value->price - round($value->price * $value->discount/100);
                                        $totalhargadiskon        = $value->qty * $hargadiskon;
                                        $unitnon                 = $value->price * ($value->price/100);


                                        $subtotal   += $totalhargadiskon;
                                        $total      += $totalhargadiskon;
                                        $discount   += $value->discount;

                                        // $subtotal += $value->price;
                                        // $total    += $value->ttl_price;
                                        // $discount += $value->discount;
                                    }

                                    // Update price and discount on new record po
                                    $purchasecount = PurchaseOrder::find($purchase->id);
                                    $purchasecount->discount  = $discount;
                                    $purchasecount->sub_total = round($subtotal);
                                    $purchasecount->total     = round($total);
                                    $purchasecount->save();

                                    $dt = new Carbon();
                                    $polog  = new PurchaseOrderLog();
                                    $polog->user_id       = auth()->user()->id;
                                    $polog->purchase_id   = $purchase->id;
                                    $polog->keterangan    = auth()->user()->username." Telah Mengupdate PO untuk diproses selanjutnya";
                                    $polog->create_date    = $dt->toDateTimeString();
                                    $polog->create_user    = auth()->user()->username;
                                    $polog->save();

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
                            }


                        }
                    DB::commit();
                    $json_data = array(
                        "success"       => TRUE,
                        "splitdata"     => TRUE,
                        "msg"           => 'data berhasil di update',
                        "msgalternate"  => 'beberapa request dialihkan ke backorder',
                        "msgAlasan"     => 'karena beberapa gudang kosong dan stok kurang'
                    );
                }else if($jumlahdetail == 0 && $jumlahisi == $totaldetail || $stockada == $totaldetail){
                    //KIRIM KE PO
                    foreach ($dataisi as $key => $value) {
                        $hargadiskon             = ($request->input('qty_produk_'.$value->id) * str_replace(".", "",$request->input('price_produk_'.$value->id))) * ($request->input('diskon_produk_'.$value->id)/100);
                        $harganonpo              = $request->input('sesudah_total_'.$value->id);
                        $unitnon                 = str_replace(".", "",$request->input('price_produk_'.$value->id)) * ($request->input('diskon_produk_'.$value->id)/100);

                        $detailpo                = PurchaseOrderDetail::where('id', $value->id)->first();
                        $detailpo->perusahaan_id = $request->input('perusahaan_asal_'.$value->id);
                        $detailpo->gudang_id     = $request->input('gudang_'.$value->id);
                        $detailpo->qty           = $request->input('qty_produk_'.$value->id);
                        $product = Product::find($value->product_id);
                        if($product->is_liner == "Y"){
                            $checkproductshadow = Product::where('product_code', 'LIKE', "%{$product->product_code_shadow}%")->first();
                            $detailpo->product_id_shadow = $checkproductshadow->id;
                        }
                        $detailpo->price         = str_replace(".", "",$request->input('price_produk_'.$value->id));
                        $detailpo->discount      = $request->input('diskon_produk_'.$value->id);
                        $detailpo->ttl_price     = round($harganonpo);
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


                            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $product->id)->first();
                            $product_gudang->stok    = ($product_gudang->stok - $request->input('qty_produk_'.$value->id) * $satuanvalue);
                            $product_gudang->save();
                        }else{
                            $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $request->input('perusahaan_asal_'.$value->id))
                                                            ->where('gudang_id',$request->input('gudang_'.$value->id))
                                                            ->first();
                            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                            $product_gudang->stok    = $product_gudang->stok - $request->input('qty_produk_'.$value->id);
                            $product_gudang->save();
                        }

                        // $data[] = $detailpo;
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

                            $hargadiskon             = $value->price - round($value->price * $value->discount/100);
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
                        $purchasecount->sub_total = round($subtotal);
                        $purchasecount->total     = round($total);
                        $purchasecount->updated_at   = now();
                        $purchasecount->save();


                        $dt = new Carbon();
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
                    PurchaseOrder::where('id', $dec_id)->delete();
                    DB::commit();
                    $json_data = array(
                        "success"       => TRUE,
                        "clear"         => TRUE,
                        "msg"           => 'data berhasil di update'
                    );
                }
        }catch (\Throwable $th) {
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
    public function perusahaanxxx(Request $request){
        $id = $request->id_perusahaan;
        $gudang = PerusahaanGudang::select('perusahaan_gudang.id','gudang.*')
                                    ->join('gudang','perusahaan_gudang.gudang_id','gudang.id')
                                    ->where('perusahaan_id',$id)
                                    ->get();

        return response()->json(['data' => $id,'gudang' => $gudang]);
    }

}
