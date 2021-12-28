<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportPOExports;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;

class ReportPoController extends Controller
{
   
    public function index()
    {
        $perusahaan = Perusahaan::all();
        return view('backend/report/po/index_po',compact('perusahaan'));
    }
    public function cekData(Request $req){
        $perusahaan_id = $req->perusahaan_id;
        $tgl           = date('Y-m-d',strtotime($req->filter_tgl));

        $query = PurchaseOrder::select('*');
        $query->where('perusahaan_id',$perusahaan_id);
        $query->whereDate('dataorder',$tgl);
        $query->where('flag_status',0);
        $cek=$query->get();

        if(count($cek) > 0){
            $json_data = array(
                "success"         => TRUE,
                "message"         => 'Data berhasil diproses'
                );
        }else{
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon Maaf tidak ada data.'
                );
        }
        return json_encode($json_data); 
    }
    
    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }
    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    public function print(Request $request,$perusahaan_id,$tgl_input){
        $tgl           = date('Y-m-d',strtotime($tgl_input));
        $perusahaan    = Perusahaan::find($perusahaan_id);
        $query = PurchaseOrder::select('*');
        $query->where('perusahaan_id',$perusahaan_id);
        $query->whereDate('dataorder',$tgl);
        $query->where('flag_status',0);
        $data=$query->get();
        foreach($data as $key=> $value)
        {
            if($value->status == 0){
                $status ='BARU';
            }elseif($value->status_gudang == 0){
                $status ='DIPROSES';
            }elseif($value->status_gudang == 1){
                $status ='SELESAI';
            }elseif ($value->status_gudang == 2){
                $status ='DITOLAK';
            }else{
                $status='DIBATALKAN';
            }

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_member     = $value->getmember?$value->getmember->name:'-';
            $value->kota_member     = $value->getmember?$value->getmember->city:'-';
            $value->status          = $status;
            
        }
        return view('backend/report/po/index_po_print',compact('data','perusahaan','tgl'));
    }
    public function pdf(Request $request,$perusahaan_id,$tgl_input){
        $tgl           = date('Y-m-d',strtotime($tgl_input));
        $perusahaan    = Perusahaan::find($perusahaan_id);
        $query = PurchaseOrder::select('*');
        $query->where('perusahaan_id',$perusahaan_id);
        $query->whereDate('dataorder',$tgl);
        $query->where('flag_status',0);
        $data=$query->get();
        foreach($data as $key=> $value)
        {
            if($value->status == 0){
                $status ='BARU';
            }elseif($value->status_gudang == 0){
                $status ='DIPROSES';
            }elseif($value->status_gudang == 1){
                $status ='SELESAI';
            }elseif ($value->status_gudang == 2){
                $status ='DITOLAK';
            }else{
                $status='DIBATALKAN';
            }

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_member     = $value->getmember?$value->getmember->name:'-';
            $value->kota_member     = $value->getmember?$value->getmember->city:'-';
            $value->status          = $status;
            
        }
            $config = [
                'mode'                  => '',
                'format'                => 'A4',
                'default_font_size'     => '11',
                'default_font'          => 'sans-serif',
                'margin_left'           => 5,
                'margin_right'          => 5,
                'margin_top'            => 35,
                'margin_bottom'         => 20,
                'margin_header'         => 0,
                'margin_footer'         => 0,
                'orientation'           => 'P',
                'title'                 => 'CETAK PO',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
             $pdf = PDF::loadView('backend/report/po/index_po_pdf', ['data'=>$data,'perusahaan'=>$perusahaan, 'tgl'   => $tgl],[],$config);
             ob_get_clean();
             return $pdf->download('Report PO"'.date('d_m_Y H_i_s').'".pdf');
    }
    public function excel(Request $request,$perusahaan_id,$tgl_input){
        $tgl           = date('Y-m-d',strtotime($tgl_input)); 
        return Excel::download(new ReportPOExports($perusahaan_id,$tgl),'Report PO"'.date('d_m_Y H_i_s').'".xlsx');
    }

}
