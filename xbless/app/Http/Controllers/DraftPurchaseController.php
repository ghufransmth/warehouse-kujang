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

class DraftPurchaseController extends Controller
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
        return view('backend/purchasedraft/index', compact('perusahaan', 'sales'));
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


    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $purchase = PurchaseOrder::select('id', 'kode_rpo', 'note','createdon','total',
                                    'created_at','updated_at','member_id','sales_id','expedisi','expedisi_via','status_rpo')
                                    ->where('flag_status', 1)
                                    ->where('draft', 1);
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $purchase->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $purchase->orderBy('updated_at','DESC');
        }
         if($search) {
          $purchase->where(function ($query) use ($search) {
                  $query->orWhere('kode_rpo','LIKE',"%{$search}%");
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
            $rpo.='<h5 class="no-margin"># <b>'.$result->kode_rpo.'</b></h5>';
            $action = "";
            $action.="";
            $action.="<div class='btn-group'>";
                if($request->user()->can('requestpurchaseorder.detail')){
                    $action.='<a href="'.route('draftpurchaseorder.ubah',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
                }

                if($request->user()->can('requestpurchaseorder.cancel')){
                    $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
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
            // $sales                  = Sales::where('code', $result->sales_id)->first();
            $sales                  = Sales::find($result->sales_id);

            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->rpo            = $rpo;
            $inv                    = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('member_id',$result->member_id)->where('pay_status',1)->get();
            if(count($inv) > 0){
                $result->customer   = $customer->name .' - '.$customer->city.'<br/><br/><span class="badge badge-danger text-left"> MEMBER INI BELUM MELAKUKAN
                <br/>PEMBAYARAN PADA INVOICE</span><br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }else{
                $result->customer   = $customer->name .' - '.$customer->city.'<br/><small class="display-block text-muted"> Catatan : <b>'.$result->note.'</b></small>';
            }

            if($result->updated_at==null){
                $result->tgl_po         = date("d M Y",strtotime($result->created_at)).'<br>'.'<small>('.Carbon::parse($result->created_at)->diffForHumans().')</small>';
            }else{
                $result->tgl_po         = date("d M Y",strtotime($result->updated_at)).'<br>'.'<small>('.Carbon::parse($result->updated_at)->diffForHumans().')</small>';
            }

            $result->status         = $status;
            if($result->expedisi_via != null){
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>'.$expedisi_via->name;
            }else{
                $result->expedisi   = $expedisi->name.' <br> <b>Via Expedisi : </b>';
            }
            $result->sales          = $sales->name;
            $result->total          = number_format($result->total,2,',','.');
            $result->action         = $action;
        }


        if ($request->user()->can('draftpurchaseorder.index')) {
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
    public function ubah(Request $request, $enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $purchaseorderdraft = PurchaseOrder::select('*')->where('flag_status', 1)->where('draft', 1)->where('id',$dec_id)->first();
            if($purchaseorderdraft){
                $member = Member::all();
                $selectedmember = $purchaseorderdraft->member_id;

                $sales = Sales::all();
                $selectedsales =$purchaseorderdraft->sales_id;
                $expedisi = Expedisi::all();
                $selectedexpedisi =$purchaseorderdraft->expedisi;
                $expedisivia = ExpedisiVia::all();
                $selectedexpedisivia =$purchaseorderdraft->expedisi_via;
                $selectedproduct ="";
                $tipeharga = $this->jenisharga();
                $selectedtipeharga ="";
                $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('pay_status',0)->where('member_id',$purchaseorderdraft->member_id)->get();
                if(count($inv) > 0){
                   $info = 1;
                }else{
                   $info = 0;
                }

                $details = PurchaseOrderDetail::select('transaction_purchase_detail.*','product.product_code','product.product_name')->join('product','product.id','transaction_purchase_detail.product_id')->where('transaction_purchase_id',$purchaseorderdraft->id)->get();

                return view('backend/purchasedraft/form',compact('tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia',
                'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember','purchaseorderdraft','enc_id','details','info'));
            }else{
                Abort('404');
            }

        } else {
        	return view('errors/noaccess');
        }

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
        try {
            $dec_id = $this->safe_decode(Crypt::decryptString($req->enc_id));
            $dt = new Carbon();
            $total = 0;
            foreach ($req->produk as $x => $value) {
                $total               += str_replace(".", "",$req->total[$x]);
            }
            $purchaseOrder                  = PurchaseOrder::find($dec_id);
            $purchaseOrder->kode_rpo        = $req->draft==1?null:$this->genRPO($req->sales);
            $purchaseOrder->dataorder       = $dt->toDateTimeString();
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
                PurchaseOrderDetail::where('transaction_purchase_id',$dec_id)->delete();
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
                $purchaselog->keterangan    = $req->draft==0? auth()->user()->username." telah melakukan menambah PO data untuk DIPROSES ke RPO" : auth()->user()->username." telah melakukan perubahan data Draft PO" ;
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
    public function hapus(Request $req,$enc_id)
    {
      $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
      $poDraft = PurchaseOrder::find($dec_id);
      if($poDraft){
            PurchaseOrderDetail::where('transaction_purchase_id',$dec_id)->delete();
            $poDraft->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
      }else {
          return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
      }
    }

}
