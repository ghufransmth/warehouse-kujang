<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\StockAdj;
use Illuminate\Support\Facades\Crypt;

class ReportPembelianController extends Controller
{
    protected $original_column = array(
        1 => "no_faktur"
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
        $pembelian = Pembelian::all();
        $pembelian_detail = PembelianDetail::all();
        $product = Product::select('id','kode_product','nama')->offset(0)->limit(10)->get();
        return view('backend/report/pembelian/index_pembelian',compact('pembelian','pembelian_detail','product'));
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $dataquery = Pembelian::select('pembelian.id','pembelian.no_faktur','pembelian.tgl_faktur','pembelian.keterangan')->where('status_pembelian',1);

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
         }
          else{
           $dataquery->orderBy('id','DESC');
         }

         if($search) {
            $dataquery->where(function ($query) use ($search) {
              $query->orWhere('pembelian.no_faktur','LIKE',"%{$search}%");
            });
          }

          $totalData = $dataquery->get()->count();

          $totalFiltered = $dataquery->get()->count();
          $dataquery->limit($limit);
          $dataquery->offset($start);
          $data = $dataquery->get();
        //   return $data;
          foreach($data as $key=> $value){
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));

            $value->nomor = $key+$page;
            $value->id = $value->id;
            $value->no_faktur = $value->no_faktur;
            $value->tgl_faktur = $value->tgl_faktur;
            $value->keterangan = $value->keterangan;
          }

          $json_data = array(
              "draw"            =>  intval($request->input('draw')),
              "recordsTotal"    => intval($totalData),
              "recordsFiltered" => intval($totalFiltered),
              "data"            => $data
          );

          return response()->json($json_data);
    }

    public function cekData(Request $req){
        // return $req->all();
        $tgl_start           = date('Y-m-d',strtotime($req->tgl_start));
        // $tgl_end           = date('Y-m-d',strtotime($req->tgl_end));

        $query = Pembelian::select('pembelian.tgl_faktur','pembelian.no_faktur');
        $query->where('tgl_faktur',$tgl_start);
        // $query->whereDate('tgl_faktur',$tgl_end);
        $cek=$query->get();
        return response()->json($cek);

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

}
