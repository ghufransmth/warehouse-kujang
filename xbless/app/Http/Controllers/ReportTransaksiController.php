<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\Sales;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use DB;
use Auth;

class ReportTransaksiController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }

    public function index(){
        $periode_start = date('d-m-Y', strtotime("-1 Month"));
        $periode_end = date('d-m-Y');
        $sales  = Sales::all();

        return view('backend/pembayaran/keuangan/index', compact('periode_start', 'periode_end','sales'));
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        if($request->periode_start != ''&& $request->periode_end != ''){
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        }else{
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }
        
        $query = Penjualan::select('tbl_penjualan.id','tbl_penjualan.no_faktur','tbl_penjualan.tgl_jatuh_tempo','tbl_penjualan.tgl_faktur','tbl_penjualan.status_lunas','tbl_sales.nama as sales_name','toko.name as toko_name');
        $query->leftJoin('tbl_sales','tbl_sales.id','tbl_penjualan.id_sales');
        $query->leftJoin('toko','toko.id','tbl_penjualan.id_toko');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $query->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
          $query->orderBy('id','DESC');
        }
        if($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('tbl_penjualan.no_faktur','LIKE',"%{$search}%");
                $query->orWhere('sales.nama','LIKE',"%{$search}%");
                $query->orWhere('toko.name','LIKE',"%{$search}%");
            });
        }

        $query->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $query->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));



        $totalData = $query->get()->count();
  
        $totalFiltered = $query->get()->count();
  
        $query->limit($limit);
        $query->offset($start);
        $data = $query->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";
            
            $action.="";
            $action.="<div class='btn-group'>";
            // $action.='<a href="'.route('diskon.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            // $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            $action.="</div>";

            if($result->status_lunas == 0){
                $status = '<span class="label label-danger">Belum Lunas</span>';
            }else if($result->status_lunas == 1){
                $status = '<span class="label label-primary">Lunas</span>';
            }

            $result->no             = $key+$page;
            $result->tanggal_faktur = date('d F Y', strtotime($result->tgl_faktur));
            $result->tanggal_tempo  = date('d F Y', strtotime($result->tgl_jatuh_tempo));
            $result->tanggal_kirim  = date('d F Y');
            $result->status         = $status;
            $result->cara_bayar     = 'Cash';
            $result->action         = $action;
        }
  
        $json_data = array(
          "draw"            => intval($request->input('draw')),  
          "recordsTotal"    => intval($totalData),
          "recordsFiltered" => intval($totalFiltered),
          "data"            => $data
        );
        // if($request->user()->can('negara.index')) {
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => intval($totalData),
        //     "recordsFiltered" => intval($totalFiltered),
        //     "data"            => $data
        //   );
        // }else{
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => 0,
        //     "recordsFiltered" => 0,
        //     "data"            => []
        //   );
           
        // }    
        return json_encode($json_data); 
    }
}
