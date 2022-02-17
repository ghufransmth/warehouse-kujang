<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Sales;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\DeliveryOrder;
use App\Models\Driver;
use App\Models\Satuan;

use Carbon\Carbon;
use DB;
use PDF;
use Excel;

class DeliveryOrderController extends Controller
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

    public function index(Request $request){
        $driver         = Driver::all();
        if(session('filter_driver')==""){
            $selecteddriver = "";
            $request->session()->put('filter_driver', $selecteddriver);
        }else{
            $selecteddriver = session('filter_driver');
        }

        if(session('filter_tgl_faktur_do_start')==""){
            $tgl_start         = date('Y-m-d', strtotime(' - 30 days'));
            $request->session()->put('filter_tgl_faktur_do_start', $tgl_start);
            $filter_tgl_faktur_do_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_do_start')));
        }else{
            $filter_tgl_faktur_do_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_do_start')));
        }

        if(session('filter_tgl_faktur_do_end')==""){
            $request->session()->put('filter_tgl_faktur_do_end', date('Y-m-d'));
            $filter_tgl_faktur_do_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_do_end')));
        }else{
            $filter_tgl_faktur_do_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_do_end')));
        }

        return view('backend/deliveryorder/index',compact('driver','selecteddriver','filter_tgl_faktur_do_start','filter_tgl_faktur_do_end'));
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

        $request->session()->put('filter_tgl_faktur_do_start', $request->filter_tgl_faktur_do_start);
        $request->session()->put('filter_tgl_faktur_do_end', $request->filter_tgl_faktur_do_end);
        $request->session()->put('filter_driver', $request->filter_driver);

        $deliveryorder = DeliveryOrder::select('tbl_penjualan.*','tbl_delivery_order.driver_id','tbl_delivery_order.no_do','tbl_delivery_order.status_do','tbl_delivery_order.created_at as tgl_do','tbl_delivery_order.id as do_id','tbl_delivery_order.type_payment','tbl_delivery_order.titip_bayar','tbl_delivery_order.tgl_warkat')
                    ->join('tbl_penjualan','tbl_penjualan.id','tbl_delivery_order.faktur_id');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $deliveryorder->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $deliveryorder->orderBy('updated_at','DESC');
        }
        if($search) {
          $deliveryorder->where(function ($query) use ($search) {
                  $query->orWhere('no_faktur','LIKE',"%{$search}%");
          });
        }
        $deliveryorder->whereDate('tgl_faktur','>=',date('Y-m-d',strtotime($request->filter_tgl_faktur_do_start)));
        $deliveryorder->whereDate('tgl_faktur','<=',date('Y-m-d',strtotime($request->filter_tgl_faktur_do_end)));

        if($request->filter_driver != ""){
            $deliveryorder->where('driver_id',$request->filter_driver);
        }


        $totalData = $deliveryorder->get()->count();

        $totalFiltered = $deliveryorder->get()->count();

        $deliveryorder->limit($limit);
        $deliveryorder->offset($start);
        $data = $deliveryorder->get();
        foreach ($data as $key=> $result){
            $disabled   = $result->status_do==1?'disabled':'';
            $enc_id     = $this->safe_encode(Crypt::encryptString($result->do_id));
            $action     = "";
            $action.="";
            $action.="<div class='btn-group'>";
                if($request->user()->can('requestpurchaseorder.cancel')){

                    $action.='<a href="#" onclick="changeDriver(this,'.$key.')"  class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip '.$disabled.'" title="Ganti Driver"><i class="fa fa-car"></i> Driver</a>&nbsp;';
                }
                if($request->user()->can('requestpurchaseorder.cancel')){
                    $action.='<a href="#" onclick="pengiriman(this,'.$key.')" class="btn btn-secondary btn-xs icon-btn md-btn-flat product-tooltip" title="Pengiriman"><i class="fa fa-send"></i> Pengiriman</a>&nbsp;';
                }
            $action.="</div>";

            if ($result->status_do=='0') {
                $status = '<span class="label label-danger">BELUM DIKIRIM</span>';
            }else if($result->status_do=='1'){
                $status = '<span class="label label-primary">TERKIRIM</span>';
            }


            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->enc_id         = $enc_id;
            $result->tglfaktur      = date("d/m/Y",strtotime($result->tgl_faktur));
            $result->tgldo          = date("d/m/Y",strtotime($result->tgl_do));
            $result->tglwarkat      = date("d/m/Y",strtotime($result->tgl_warkat));
            $result->titipbayar     = number_format($result->titip_bayar,0,',','.');
            $result->nofakturpopup  = '<a href="#" onclick="detail(this,'.$key.')" title="detail">'.$result->no_faktur.'</a>&nbsp;';
            $result->nofaktur       = $result->no_faktur;
            $result->outletcode     = $result->gettoko?$result->gettoko->code:'-';
            $result->namecode       = $result->gettoko?$result->gettoko->name:'-';
            $result->addresscode    = $result->gettoko?$result->gettoko->alamat:'-';
            $result->sales          = $result->getsales?$result->getsales->nama:'-';
            $result->driver         = $result->getDriver?$result->getDriver->nama:'-';
            $result->total          = number_format($result->total_harga,0,',','.');
            $result->detail         = $this->getDetailPenjualan($result->id);
            $result->status         = $status;
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


    public function getDetailPenjualan($id){
        $detail   = DetailPenjualan::select('tbl_detail_penjualan.id','tbl_detail_penjualan.qty','tbl_detail_penjualan.harga_product','tbl_detail_penjualan.diskon','tbl_detail_penjualan.total_harga','kode_product','nama','id_satuan')->join('tbl_product','tbl_product.id','tbl_detail_penjualan.id_product')->where('id_penjualan',$id)->get();
        $html     = "";
        $total    = 0;
        foreach ($detail as $key => $value) {
            $satuan = Satuan::find($value->id_satuan);
            $krt    = 0;
            $lsn    = $satuan?($satuan->id==2?$value->qty:0):0;
            $pcs    = $satuan?($satuan->id==1?$value->qty:0):0;
            $html .= "<tr>";
                $html .= "<td>".$value->kode_product."</td>";
                $html .= "<td>".$value->nama."</td>";
                $html .= "<td class='text-right'>".number_format($value->harga_product,0,',','.')."</td>";
                $html .= "<td class='text-center'>".$krt."</td>";
                $html .= "<td class='text-center'>".$lsn."</td>";
                $html .= "<td class='text-center'>".$pcs."</td>";
                $html .= "<td class='text-right'>".number_format($value->total_harga,0,',','.')."</td>";
            $html .= "</tr>";
            $total  += $value->total_harga;
        }
        $detaildata = array(
            '0' => $html,
            '1' => number_format($total,0,',','.'),
        );
        return $detaildata;
    }
    public function tambah(Request $request){
        // Penjualan
        $sales          = Sales::all();

        $driver         = Driver::all();

        if(session('filter_sales')==""){
            $selectedsales = "";
            $request->session()->put('filter_sales', $selectedsales);
        }else{
            $selectedsales = session('filter_sales');
        }

        if(session('filter_tgl_faktur_start')==""){
            $tgl_start         = date('Y-m-d', strtotime(' - 30 days'));
            $request->session()->put('filter_tgl_faktur_start', $tgl_start);
            $filter_tgl_faktur_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_start')));
        }else{
            $filter_tgl_faktur_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_start')));
        }


        if(session('filter_tgl_faktur_end')==""){
            $request->session()->put('filter_tgl_faktur_end', date('Y-m-d'));
            $filter_tgl_faktur_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_end')));

        }else{
            $filter_tgl_faktur_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_end')));
        }


        return view('backend/deliveryorder/form',compact('sales','selectedsales','driver','filter_tgl_faktur_start','filter_tgl_faktur_end'));
    }

    public function getDataPenjualan(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $request->session()->put('filter_tgl_faktur_start', $request->filter_tgl_faktur_start);
        $request->session()->put('filter_tgl_faktur_end', $request->filter_tgl_faktur_end);
        $request->session()->put('filter_sales', $request->filter_sales);

        $penjualan = Penjualan::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $penjualan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $penjualan->orderBy('updated_at','DESC');
        }
        if($search) {
          $penjualan->where(function ($query) use ($search) {
                  $query->orWhere('no_faktur','LIKE',"%{$search}%");
          });
        }

        $penjualan->whereDate('tgl_faktur','>=',date('Y-m-d',strtotime($request->filter_tgl_faktur_start)));
        $penjualan->whereDate('tgl_faktur','<=',date('Y-m-d',strtotime($request->filter_tgl_faktur_end)));

        if($request->filter_sales != ""){
            $penjualan->where('id_sales',$request->filter_sales);
        }

        $penjualan->where('is_do',0);

        $totalData = $penjualan->get()->count();

        $totalFiltered = $penjualan->get()->count();

        $penjualan->limit($limit);
        $penjualan->offset($start);
        $data = $penjualan->get();
        foreach ($data as $key=> $result){
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));

            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->check          ='<div class="custom-control custom-checkbox valigntop">
            <input class="get_value custom-control-input editor-active" type="checkbox" id="customCheckbox_'.$result->id.'" data-id="'.$result->id.'" value="'.$result->id.'">
            <label for="customCheckbox_'.$result->id.'" class="custom-control-label"></label>
            </div>';
            $result->tglfaktur      = date("d/m/Y",strtotime($result->tgl_faktur));
            $result->nofaktur       = $result->no_faktur;
            $result->outletcode     = $result->gettoko? $result->gettoko->code:'-';
            $result->namecode       = $result->gettoko?$result->gettoko->name:'-';
            $result->addresscode    = $result->gettoko?$result->gettoko->alamat:'-';
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


    public function generateNoDO()
    {
         // <!-- DO20220215 0000000005 -->
            $next_no     ='';
            $max_data_id = DeliveryOrder::orderBy('id', 'DESC')->first();

            if ($max_data_id) {
                $datado       = DeliveryOrder::find($max_data_id->id);
                $no_do        = $datado->no_do;
                $ambil        = substr($no_do, -10);
            }

            if ($max_data_id==null) {
                $next_no = '0000000001';
            }elseif (strlen($ambil)<10) {
                $next_no = '0000000001';
            }elseif ($ambil == '9999999999') {
                $next_no = '0000000001';
            }else {
                $next_no = substr('0000000000', 0, 10-strlen($ambil+1)).($ambil+1);
            }

            return 'DO'.date('Y').date('m').date('d').$next_no;
    }

    public function simpan(Request $req){

        $datafaktur        = $req->insert;
        $driver            = $req->driver;
        $no_do             = $this->generateNoDO();
        foreach ($datafaktur as $key => $value) {
            $delivery = new DeliveryOrder;
            $delivery->faktur_id        = $value[0];
            $delivery->driver_id        = $driver;
            $delivery->no_do            = $no_do;
            $delivery->status_do        = 0;
            $delivery->created_by       = $req->user()->username;
            $delivery->save();

            $updateisdo = Penjualan::find($value[0]);
            $updateisdo->is_do = 1;
            $updateisdo->save();
        }
        $desc = 'Data berhasil diperbarui.';

        $json_data = array(
                "success"         => TRUE,
                "message"         => $desc
            );
        return json_encode($json_data);
    }

    public function updateDriver(Request $req){
        try{
            DB::beginTransaction();
            $enc_id          = $req->enc_id;
            $driver          = $req->driver;
            $dec_id          = $this->safe_decode(Crypt::decryptString($enc_id));
            $deliveryorder   = DeliveryOrder::find($dec_id);

            if ($deliveryorder) {
                if($deliveryorder->status_do=='0'){
                    if($deliveryorder->driver_id == $driver){
                        $json_data = array(
                            "success"         => TRUE,
                            "message"         => 'Tidak ada perubahan driver'
                        );
                    }else{
                        $deliveryorder->driver_id= $driver;
                        $deliveryorder->save();
                        DB::commit();
                        $json_data = array(
                            "success"         => TRUE,
                            "message"         => 'Driver berhasil diperbarui'
                        );
                    }
                }else{
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Perubahan tidak dapat dilakukan karena status delivery order sudah terkirim'
                    );
                }
            } else {
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Data tidak ditemukan'
                );
            }
        } catch (DecryptException $e) {
            DB::rollback();
            $json_data = array(
                "success"         => FALSE,
                "message"         => $e->getMessage()
            );
        }
        return json_encode($json_data);


        $desc = 'Data berhasil diperbarui.';

        $json_data = array(
                "success"         => TRUE,
                "message"         => $desc
            );
        return json_encode($json_data);
    }

    public function pengiriman(Request $req){
        try{
            DB::beginTransaction();
            $enc_id          = $req->enc_id;
            $typepayment     = $req->typepayment;

            $p_titip_bayar   = $req->p_titip_bayar;
            $p_tgl_warkat    = date('Y-m-d',strtotime($req->p_tgl_warkat));

            $dec_id          = $this->safe_decode(Crypt::decryptString($enc_id));
            $deliveryorder   = DeliveryOrder::find($dec_id);

            if ($deliveryorder) {
                if($deliveryorder->typepayment==null){
                    $deliveryorder->type_payment    = $typepayment;
                    $deliveryorder->titip_bayar     = $typepayment==0?$p_titip_bayar:null;
                    $deliveryorder->tgl_warkat      = $typepayment==1?$p_tgl_warkat:null;
                    $deliveryorder->status_do       = 1;
                    $deliveryorder->pengiriman_by   = $req->user()->username;
                    $deliveryorder->tgl_kirim       = date('Y-m-d');
                    $deliveryorder->save();
                    DB::commit();
                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Pembayaran berhasil dilakukan'
                    );
                }else{
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Perubahan tidak dapat dilakukan karena sudah dilakukan pembayaran'
                    );
                }
            } else {
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Data tidak ditemukan'
                );
            }
        } catch (DecryptException $e) {
            DB::rollback();
            $json_data = array(
                "success"         => FALSE,
                "message"         => $e->getMessage()
            );
        }
        return json_encode($json_data);
    }


}
