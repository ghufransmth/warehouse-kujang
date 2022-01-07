<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use DB;
use Auth;


class BerandaController extends Controller
{
    public function index(){
        $periode_start = date('d-m-Y', strtotime("-1 Month"));
        $periode_end = date('d-m-Y');
    	
        return view('backend/beranda/index', compact('periode_start', 'periode_end'));
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
        $penjualan = $this->omset($request->periode_start, $request->periode_end);
        $pajak = $this->pajak($request->periode_start, $request->periode_end);

        return response()->json([
            'code' => 200,
            'message' => 'data berhasil didapat',
            'detail' => [
                'omset' => $penjualan,
                'pajak' => $pajak
            ]
        ]);
    }

    private function omset($periode_start, $periode_end){
        $penjualan = DB::table('tbl_penjualan')
                    ->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)))
                    ->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)))
                    ->sum('total_harga');

        return $penjualan;
    }

    private function pajak($periode_start, $periode_end){
        $penjualan = DB::table('tbl_penjualan')
                    ->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)))
                    ->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)))
                    ->get();

        $total_pajak = 0;
        foreach ($penjualan as $key => $value) {
            $pajak = ($value->total_harga / 100) * 10;
            $value->pajak = $pajak;

            $total_pajak += $value->pajak;
        }

        return $total_pajak;
    }

    public function getDataUnilever(Request $request){
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

        $dataquery = DB::table('pembelian')->select('*');
        $dataquery->orderBy('id','DESC');
        if($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('name','LIKE',"%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action.='';
            $action.='<div class="btn-group">';
            $action.='<a href="'.route('beranda.unilever.detail', $enc_id).'" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action.='</div>';

            if($result->status_pembelian == 0){
                $status = '<span class="label label-danger">Belum Lunas</span>';
            }else{
                $status = '<span class="label label-primary">Lunas</span>';
            }

            $result->no             = $key+$page;

            $result->status         = $status;
            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public function getDataPenjualan(Request $request){
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

        $dataquery = DB::table('tbl_penjualan')->select('tbl_penjualan.*','tbl_sales.nama','toko.name');
        $dataquery->leftJoin('tbl_sales','tbl_sales.id','tbl_penjualan.id_sales');
        $dataquery->leftJoin('toko','toko.id','tbl_penjualan.id_toko');
        $dataquery->orderBy('tbl_penjualan.id','DESC');
        if($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('sales.nama','LIKE',"%{$search}%");
                $query->orWhere('toko.name','LIKE',"%{$search}%");
                $query->orWhere('tbl_penjualan.no_faktur','LIKE',"%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action.='';
            $action.='<div class="btn-group">';
            $action.='<a href="'.route('beranda.unilever.detail', $enc_id).'" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action.='</div>';

            $result->no             = $key+$page;

            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            // $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }
}
