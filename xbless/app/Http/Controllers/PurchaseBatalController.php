<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderLog;
use App\Models\PerusahaanGudang;
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
use Auth;

class PurchaseBatalController extends Controller
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
        $value = array('0'=>'Baru ', '1'=>'PROSESS', '2' => 'DITOLAK');
        return $value;
    }

    public function index(){

        return view('backend/purchasebatal/index');
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

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $purchase = PurchaseOrder::select('id', 'no_nota','kode_rpo','note','createdon','total',
                                    'created_at','member_id','sales_id','expedisi','expedisi_via','status')
                                    ->where('status', 2)
                                    ->where('flag_status','!=',0);
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $purchase->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
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


            if ($result->status=='0') {
                $status = '<span class="label label-primary">Baru</span>';
            }else if($result->status=='1'){
                $status = '<span class="label label-warning">Diproses</span>';
            }else if($result->status=='2'){
                $status = '<span class="label label-danger">Ditolak</span>';

            }
            $rpo = "";
            $rpo.="";
            $getCekBO = $this->cut_text($result->kode_rpo);
            if($result->no_nota==null){
                if($getCekBO=='BO-'){
                    $rpo.='<h5 class="no-margin"># <b>'.$result->kode_rpo.'</b></h5>';
                }else{
                    $rpo.='<h5 class="no-margin"># <b>'.$result->kode_rpo.'</b></h5>';
                }
            }else{
                $rpo.='<h5 class="no-margin"># <b>'.$result->no_nota.'</b></h5>';
            }

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
            $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $result->id)->get();
            $polog                  = PurchaseOrderLog::where('purchase_id', $result->id)->first();
            $totalharga = 0;
            foreach ($purchasedetail as $k => $value) {
                $product = Product::find($value->product_id);

                $hargadiskon    = $value->price - round($value->price * ($value->discount/100));
                $totalhargadiskon = $value->qty * $hargadiskon;
                $totalharga += $totalhargadiskon;
            }
            $customer               = Member::find($result->member_id);
            $expedisi               = Expedisi::find($result->expedisi);
            $expedisi_via           = ExpedisiVia::find($result->expedisi_via);
            $sales                  = Sales::find($result->sales_id);

            $result->enc_id         = $enc_id;
            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->note           = $result->note==null?'-':$result->note;
            $result->rpo            = $rpo;
            $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('member_id',$result->member_id)->where('pay_status',1)->get();
            if(count($inv) > 0){
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left"> MEMBER INI BELUM MELAKUKAN
                <br/>PEMBAYARAN PADA INVOICE</span><br><br><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }else{
                $result->customer       = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }

            $result->tgl_po         = date("d M Y",strtotime($result->created_at)).'<br>'.'<small>('.Carbon::parse($result->created_at)->diffForHumans().')</small>';
            $result->action_status  = $result->status;
            if(!$polog){
                $result->status         = $status.' <br> <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small>';
            }else{
                if($result->status == 2){
                    $result->status         = $status.' <br> <small class="display-block text-muted">Ditolak Oleh : '.$polog->create_user.'</small>';
                }else{
                    $result->status         = $status.' <br> <small class="display-block text-muted">Dibuat Oleh : '.$result->createdon.'</small>';
                }
            }
            $result->expedisi       = $expedisi->name;
            $result->sales          = $sales->name;
            $result->total          = number_format($totalharga, 0, '', '.').',00';
            $result->action         = $result->id;
        }

        if ($request->user()->can('purchasebatal.index')) {
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

    function delete(Request $request){
        $datapo = $request->datapo;
        foreach ($datapo as $key => $value) {
            //delete detail
            $purchaselog    = PurchaseOrderLog::where('purchase_id', $value);
            if($purchaselog->get()){
                $purchaselog->delete();
            }
            $purchasdetail  = PurchaseOrderDetail::where('transaction_purchase_id', $value)->delete();
            $purchase = PurchaseOrder::where('id', $value)->delete();
        }
        return response()->json([
            'success' => TRUE,
            'code'    => 204,
            'msg'     => 'Po Berhasil Dihapus'
        ]);
    }
}
