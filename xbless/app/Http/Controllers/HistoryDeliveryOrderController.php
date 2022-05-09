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

class HistoryDeliveryOrderController extends Controller
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


    public function index(Request $request){

        if(session('filter_tgl_do_history_start')==""){
            $tgl_start         = date('Y-m-d');
            $request->session()->put('filter_tgl_do_history_start', $tgl_start);
            $filter_tgl_do_history_start = date('d-m-Y',strtotime(session('filter_tgl_do_history_start')));
        }else{
            $filter_tgl_do_history_start = date('d-m-Y',strtotime(session('filter_tgl_do_history_start')));
        }

        $driver         = Driver::all();
        if(session('filter_do_driver')==""){
            $selecteddriver = "";
            $request->session()->put('filter_do_driver', $selecteddriver);
        }else{
            $selecteddriver = session('filter_do_driver');
        }

        return view('backend/historydeliveryorder/index',compact('filter_tgl_do_history_start','driver','selecteddriver'));
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $request->session()->put('filter_tgl_do_history_start', $request->filter_tgl_do_history_start);
        $request->session()->put('filter_do_driver', $request->filter_do_driver);


        $deliveryorder = DeliveryOrder::select('tbl_penjualan.*','tbl_delivery_order.driver_id','tbl_delivery_order.no_do','tbl_delivery_order.status_do','tbl_delivery_order.created_at as tgl_do','tbl_delivery_order.id as do_id','tbl_delivery_order.type_payment','tbl_delivery_order.titip_bayar','tbl_delivery_order.tgl_warkat','tbl_delivery_order.note')
                    ->join('tbl_penjualan','tbl_penjualan.id','tbl_delivery_order.faktur_id');


            $deliveryorder->orderBy('updated_at','DESC');

        if($search) {
          $deliveryorder->where(function ($query) use ($search) {
                  $query->orWhere('no_do','LIKE',"%{$search}%");
          });
        }
        $deliveryorder->whereDate('tbl_delivery_order.created_at','=',date('Y-m-d',strtotime($request->filter_tgl_do_history_start)));

        if($request->filter_do_driver != ""){
            $deliveryorder->where('driver_id',$request->filter_do_driver);
        }
        $deliveryorder->groupBy('tbl_delivery_order.no_do');
        $totalData = $deliveryorder->get()->count();

        $totalFiltered = $deliveryorder->get()->count();

        $deliveryorder->limit($limit);
        $deliveryorder->offset($start);
        $data = $deliveryorder->get();
        foreach ($data as $key=> $result){
            $enc_id     = $this->safe_encode(Crypt::encryptString($result->do_id));
            $action     = "";
            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('historydeliveryorder.detail')){
                $action.='<a href="#" onclick="detailHistory(this,'.$key.')"  class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" title="Detail History"><i class="fa fa-eye"></i></a>&nbsp;';
            }
            $action.="</div>";

            if ($result->status_do=='0') {
                $status = '<span class="label label-danger">BELUM DIKIRIM</span>';
            }else if($result->status_do=='1'){
                if($result->type_payment=='2'){
                    $status = '<span class="label label-primary">TERKIRIM/BELUM BAYAR</span>';
                }else{
                    $status = '<span class="label label-primary">TERKIRIM</span>';
                }

            }


            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->enc_id         = $enc_id;
            $result->tglfaktur      = date("d/m/Y",strtotime($result->tgl_faktur));
            $result->tgldo          = date("d/m/Y",strtotime($result->tgl_do));
            $result->nofaktur       = $result->no_faktur;
            $result->outletcode     = $result->gettoko?$result->gettoko->code:'-';
            $result->namecode       = $result->gettoko?$result->gettoko->name:'-';
            $result->sales          = $result->getsales?$result->getsales->nama:'-';
            $result->driver         = $result->getDriver?$result->getDriver->nama:'-';
            $result->total          = number_format($result->total_harga,0,',','.');
            $result->status         = $status;
            $result->detail         = $this->getDetailPenjualan($result->no_do);
            $result->detailfaktur   = $this->getFaktur($result->no_do);
            $result->action         = $action;
        }

        if ($request->user()->can('historydeliveryorder.index')) {
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

    public function getDetailPenjualan($no_do){
        $detail   = DetailPenjualan::select('tbl_detail_penjualan.id','tbl_detail_penjualan.qty','tbl_detail_penjualan.harga_product','tbl_detail_penjualan.diskon','tbl_detail_penjualan.total_harga','kode_product','nama','id_satuan')
                    ->join('tbl_product','tbl_product.id','tbl_detail_penjualan.id_product')
                    ->join('tbl_penjualan','tbl_penjualan.id','tbl_detail_penjualan.id_penjualan')
                    ->join('tbl_delivery_order','tbl_penjualan.id','tbl_delivery_order.faktur_id')
                    ->where('tbl_delivery_order.no_do',$no_do)->get();
        $html     = "";
        $jumlah_krt_utuh   = 0;
        $jumlah_krt_ass    = 0;
        foreach ($detail as $key => $value) {
            $satuan = Satuan::find($value->id_satuan);
            $krt_utuh   = 0;
            $krt        = 0;
            $lsn        = $satuan?($satuan->id==2?$value->qty:0):0;
            $pcs        = $satuan?($satuan->id==1?$value->qty:0):0;

            $html .= "<tr>";
                $html .= "<td>".$value->kode_product."</td>";
                $html .= "<td>".$value->nama."</td>";
                $html .= "<td class='text-center'>".$krt_utuh."</td>";
                $html .= "<td class='text-center'>".$krt."</td>";
                $html .= "<td class='text-center'>".$lsn."</td>";
                $html .= "<td class='text-center'>".$pcs."</td>";
            $html .= "</tr>";
            $jumlah_krt_utuh   += $krt_utuh;
            $jumlah_krt_ass    += $krt;
        }
        $detaildata = array(
            '0' => $html,
            '1' => $jumlah_krt_utuh,//jumlah krt utuh
            '2' => $jumlah_krt_ass,//jumlah krt assembling
            '3' => $jumlah_krt_utuh+$jumlah_krt_ass,//total krt

        );
        return $detaildata;
    }

    public function getFaktur($no_do){
        $detail   = Penjualan::select('tbl_penjualan.*')->join('tbl_delivery_order','tbl_delivery_order.faktur_id','tbl_penjualan.id')
        ->where('no_do',$no_do)->get();
        $html     = "";
        $total    = 0;
        foreach ($detail as $key => $value) {
            $tglfaktur      = date("d/m/Y",strtotime($value->tgl_faktur));
            $nofaktur       = $value->no_faktur;
            $outletcode     = $value->gettoko?$value->gettoko->code:'-';
            $namecode       = $value->gettoko?$value->gettoko->name:'-';
            $html .= "<tr>";
                $html .= "<td>".$tglfaktur."</td>";
                $html .= "<td>".$nofaktur."</td>";
                $html .= "<td class='text-left'>".$outletcode."</td>";
                $html .= "<td class='text-left'>".$namecode."</td>";
                $html .= "<td class='text-right'>".number_format($value->total_harga,0,',','.')."</td>";
            $html .= "</tr>";
            $total      += $value->total_harga;
        }
        $detaildata = array(
            '0' => $html,
            '1' => number_format($total,0,',','.'), //total
        );
        return $detaildata;
    }

    public function print(Request $request,$enc_id)
    {
        try{
            $dec_id        = $this->safe_decode(Crypt::decryptString($enc_id));
            $deliveryorder = DeliveryOrder::select('tbl_penjualan.*','tbl_delivery_order.driver_id','tbl_delivery_order.no_do','tbl_delivery_order.status_do','tbl_delivery_order.created_at as tgl_do','tbl_delivery_order.id as do_id','tbl_delivery_order.type_payment','tbl_delivery_order.titip_bayar','tbl_delivery_order.tgl_warkat','tbl_delivery_order.note')
            ->join('tbl_penjualan','tbl_penjualan.id','tbl_delivery_order.faktur_id')
            ->where('tbl_delivery_order.id',$dec_id)->first();

            if ($deliveryorder) {
                $detail         = $this->getDetailPenjualan($deliveryorder->no_do);
                $detailfaktur   = $this->getFaktur($deliveryorder->no_do);
                return view('backend/historydeliveryorder/preview',compact('deliveryorder','detail','detailfaktur'));
            } else {
                Abort('404');
            }
        }
        catch (DecryptException $e) {
            Abort('404');
        }
    }

}
