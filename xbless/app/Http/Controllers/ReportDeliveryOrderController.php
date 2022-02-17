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

class ReportDeliveryOrderController extends Controller
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

        if(session('filter_tgl_faktur_report_start')==""){
            $tgl_start         = date('Y-m-d', strtotime(' - 30 days'));
            $request->session()->put('filter_tgl_faktur_report_start', $tgl_start);
            $filter_tgl_faktur_report_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_report_start')));
        }else{
            $filter_tgl_faktur_report_start = date('d-m-Y',strtotime(session('filter_tgl_faktur_report_start')));
        }

        if(session('filter_tgl_faktur_report_end')==""){
            $request->session()->put('filter_tgl_faktur_report_end', date('Y-m-d'));
            $filter_tgl_faktur_report_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_report_end')));
        }else{
            $filter_tgl_faktur_report_end = date('d-m-Y',strtotime(session('filter_tgl_faktur_report_end')));
        }

        return view('backend/report/deliveryorder/index',compact('filter_tgl_faktur_report_start','filter_tgl_faktur_report_end'));
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

        $request->session()->put('filter_tgl_faktur_report_start', $request->filter_tgl_faktur_report_start);
        $request->session()->put('filter_tgl_faktur_report_end', $request->filter_tgl_faktur_report_end);

        $deliveryorder = DeliveryOrder::select('tbl_penjualan.*','tbl_delivery_order.driver_id','tbl_delivery_order.no_do','tbl_delivery_order.status_do','tbl_delivery_order.created_at as tgl_do','tbl_delivery_order.id as do_id','tbl_delivery_order.type_payment','tbl_delivery_order.titip_bayar','tbl_delivery_order.tgl_warkat','tbl_delivery_order.note')
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
        $deliveryorder->whereDate('tgl_faktur','>=',date('Y-m-d',strtotime($request->filter_tgl_faktur_report_start)));
        $deliveryorder->whereDate('tgl_faktur','<=',date('Y-m-d',strtotime($request->filter_tgl_faktur_report_end)));


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
                if($request->user()->can('requestpurchaseorder.cancel')){
                    $action.='<a href="'.route('reportdeliveryorder.print',$enc_id).'" target="_blank" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" title="Preview"><i class="fa fa-print"></i> Print</a>&nbsp;';
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
            $result->tglwarkat      = date("d/m/Y",strtotime($result->tgl_warkat));
            $result->titipbayar     = number_format($result->titip_bayar,0,',','.');
            $result->nofaktur       = $result->no_faktur;
            $result->outletcode     = $result->gettoko?$result->gettoko->code:'-';
            $result->namecode       = $result->gettoko?$result->gettoko->name:'-';
            $result->addresscode    = $result->gettoko?$result->gettoko->alamat:'-';
            $result->sales          = $result->getsales?$result->getsales->nama:'-';
            $result->driver         = $result->getDriver?$result->getDriver->nama:'-';
            $result->total          = number_format($result->total_harga,0,',','.');
            $result->status         = $status;
            $result->catatan        = '<a href="#" onclick="note(this,'.$key.')" title="note">Catatan</a>&nbsp;';
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
        $penjualan= Penjualan::find($id);
        $detail   = DetailPenjualan::select('tbl_detail_penjualan.id','tbl_detail_penjualan.qty','tbl_detail_penjualan.harga_product','tbl_detail_penjualan.diskon','tbl_detail_penjualan.total_harga','kode_product','nama','id_satuan')->join('tbl_product','tbl_product.id','tbl_detail_penjualan.id_product')->where('id_penjualan',$id)->get();
        $html     = "";
        $subtotal = 0;
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
                $html .= "<td class='text-center'>".$krt.'.'.$lsn.'.'.$pcs."</td>";
                $html .= "<td class='text-right'>".number_format($value->total_harga,0,',','.')."</td>";
                $html .= "<td class='text-left'></td>";
            $html .= "</tr>";
            $hargaproduct = $value->qty * $value->harga_product;
            $subtotal   += $hargaproduct;
            $total      += $value->total_harga;
        }
        $detaildata = array(
            '0' => $html,
            '1' => number_format($total,0,',','.'), //total
            '2' => ucwords($this->terbilang($total)).' Rupiah',
            '3' => number_format($penjualan->total_diskon,0,',','.'), //total_diskon
            '4' => number_format($subtotal,0,',','.'), //sub total
        );
        return $detaildata;
    }
    function penyebut($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = $this->penyebut($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = $this->penyebut($nilai/10)." puluh". $this->penyebut($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . $this->penyebut($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->penyebut($nilai/100) . " ratus" . $this->penyebut($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . $this->penyebut($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->penyebut($nilai/1000) . " ribu" . $this->penyebut($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->penyebut($nilai/1000000) . " juta" . $this->penyebut($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = $this->penyebut($nilai/1000000000) . " milyar" . $this->penyebut(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = $this->penyebut($nilai/1000000000000) . " trilyun" . $this->penyebut(fmod($nilai,1000000000000));
        }
        return $temp;
    }

    function terbilang($nilai) {
        if($nilai<0) {
            $hasil = "minus ". trim($this->penyebut($nilai));
        } else {
            $hasil = trim($this->penyebut($nilai));
        }
        return $hasil;
    }

    public function updateNote(Request $req){
        try{
            DB::beginTransaction();
            $enc_id          = $req->enc_id;
            $note            = $req->note;
            $dec_id          = $this->safe_decode(Crypt::decryptString($enc_id));
            $deliveryorder   = DeliveryOrder::find($dec_id);

            if ($deliveryorder) {
                $deliveryorder->note = $note;
                $deliveryorder->save();
                DB::commit();
                $json_data = array(
                    "success"         => TRUE,
                    "message"         => 'Note berhasil diperbarui'
                );
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

    public function print(Request $request,$enc_id)
    {
        try{
            $dec_id        = $this->safe_decode(Crypt::decryptString($enc_id));
            $deliveryorder = DeliveryOrder::select('tbl_penjualan.*','tbl_delivery_order.driver_id','tbl_delivery_order.no_do','tbl_delivery_order.status_do','tbl_delivery_order.created_at as tgl_do','tbl_delivery_order.id as do_id','tbl_delivery_order.type_payment','tbl_delivery_order.titip_bayar','tbl_delivery_order.tgl_warkat','tbl_delivery_order.note')
            ->join('tbl_penjualan','tbl_penjualan.id','tbl_delivery_order.faktur_id')
            ->where('tbl_delivery_order.id',$dec_id)->first();

            if ($deliveryorder) {
                $detail        = $this->getDetailPenjualan($deliveryorder->id);
                return view('backend/report/deliveryorder/preview',compact('deliveryorder','detail'));
            } else {
                Abort('404');
            }
        }
        catch (DecryptException $e) {
            Abort('404');
        }
    }



}
