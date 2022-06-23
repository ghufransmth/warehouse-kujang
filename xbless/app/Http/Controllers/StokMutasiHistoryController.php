<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gudang;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\HistoryMutasiStockExports;
use App\Models\StockMutasiDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;

class StokMutasiHistoryController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );
    public function index()
    {
        $gudang     = Gudang::all();
        // $perusahaan = Perusahaan::all();

        if(session('filter_perusahaan')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_perusahaan');
        }

        if(session('filter_gudang')==""){
            $selectedgudang = '';
        }else{
            $selectedgudang = session('filter_gudang');
        }

        if(session('filter_gudang_tujuan')==""){
            $selectedgudangtujuan = '';
        }else{
            $selectedgudangtujuan = session('filter_gudang_tujuan');
        }


        if(session('filter_tgl_start')==""){
            $tgl_start = date('d-m-Y', strtotime(' - 30 days'));
        }else{
            $tgl_start = session('filter_tgl_start');
        }

        if(session('filter_tgl_end')==""){
            $tgl_end = date('d-m-Y');
        }else{
            $tgl_end = session('filter_tgl_end');
        }


        return view('backend/stok/stokmutasihistory/index',compact('selectedgudang','selectedgudangtujuan','selectedperusahaan','tgl_start','tgl_end', 'gudang'));
    }
    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        // $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        // $request->session()->put('filter_gudang_tujuan', $request->filter_gudang_tujuan);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);


        $querydb = StockMutasi::select('*')->whereHas('getdetail');
        // return $querydb->get();

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $querydb->orderBy('id','DESC');
        }

        // if($request->filter_perusahaan != ""){
        //     $querydb->where('log_stock.from_perusahaan_id',$request->filter_perusahaan);
        // }else{
        //     $querydb->where('log_stock.from_perusahaan_id',0);
        // }
        if($request->filter_gudang != ""){
            $querydb->where('gudang_tujuan',$request->filter_gudang);
        }
        // if($request->filter_gudang_tujuan != ""){
        //     $querydb->where('log_stock.to_gudang_id',$request->filter_gudang_tujuan);
        // }else{
        //     $querydb->where('log_stock.to_gudang_id',0);
        // }

        if($request->filter_tgl_start != "" && $request->filter_tgl_end !=""){
            $querydb->whereDate('tgl_mutasi','>=',date('Y-m-d',strtotime($request->filter_tgl_start)));
            $querydb->whereDate('tgl_mutasi','<=',date('Y-m-d',strtotime($request->filter_tgl_end)));

        }
        // return $querydb->get();
       if($search) {
            $querydb->whereHas('product', function($query) use ($search){
                $query->orwhere('nama','LIKE',"%{$search}%");
                $query->orwhere('kode_product','LIKE',"%{$search}%");
            });
      }
      $totalData = $querydb->get()->count();

      $totalFiltered = $querydb->get()->count();

      $querydb->limit($limit);
      $querydb->offset($start);
      $dataa = $querydb->get();
    //   return $data;
    $no = 1;
    $array = array();
    foreach($dataa as $result){
        $data = $result->getdetail;
        foreach($data as $key=> $value)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
          $action = "";


          $value->no                = $no++;
          $value->id                = $value->id;
          $value->nama_produk       = $value->product->nama;
          $value->nama_gudang_awal  = $value->getstockmutasi->getgudangawal->name;
          $value->nama_gudang_tujuan= $value->getstockmutasi->getgudangtujuan->name;
          $value->dari_stock        = $value->stock_awal.' '.$value->satuan->nama;
          $value->ke_stock          = $value->qty_mutasi.' '.$value->satuan->nama;
          $value->new_stock         = ($value->stock_awal - $value->qty_mutasi).' '.$value->satuan->nama;
          $value->note              = 'Mutasi dari <b>'.$value->getstockmutasi->getgudangawal->name.' (Qty : '.$value->dari_stock.')</b> Ke Gudang <b>'.$value->getstockmutasi->getgudangtujuan->name.' (Qty : '.$value->ke_stock.')</b></b>';
          $value->tgl_mutasi        = date('d-m-Y H:i',strtotime($value->getstockmutasi->tgl_mutasi));
          $value->created_by        = $value->getstockmutasi->created_by;
        }
        if($array == []){
            $array = array_merge($array, $data->toArray());
        }else{
            $array = array_merge($array, $data->toArray());
        }
        // $array->push($data);

    }
    // return $array;
    if ($request->user()->can('historymutasistok.index')) {
        $json_data = array(
                  "draw"            => intval($request->input('draw')),
                  "recordsTotal"    => intval($totalData),
                  "recordsFiltered" => intval($totalFiltered),
                  "data"            => $array
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

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }
    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    public function print(Request $request){
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_gudang_tujuan   = session('filter_gudang_tujuan');
        $tgl_start              = session('filter_tgl_start');
        $tgl_end                = session('filter_tgl_end');

        $perusahaan             = Perusahaan::find($filter_perusahaan);
        $gudang                 = Gudang::find($filter_gudang);
        $gudangtujuan           = Gudang::find($filter_gudang_tujuan);

        $querydb = StockMutasi::select('log_stock.*','product.product_name','satuan.name');
        $querydb->join('product_perusahaan_gudang','product_perusahaan_gudang.id','log_stock.product_perusahaan_gudang_id');
        $querydb->join('product','product.id','product_perusahaan_gudang.product_id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->orderBy('id','DESC');

        if($filter_perusahaan != ""){
            $querydb->where('log_stock.from_perusahaan_id',$filter_perusahaan);
        }else{
            $querydb->where('log_stock.from_perusahaan_id',0);
        }
        if($filter_gudang != ""){
            $querydb->where('log_stock.from_gudang_id',$filter_gudang);
        }else{
            $querydb->where('log_stock.from_gudang_id',0);
        }
        if($filter_gudang_tujuan != ""){
            $querydb->where('log_stock.to_gudang_id',$filter_gudang_tujuan);
        }else{
            $querydb->where('log_stock.to_gudang_id',0);
        }

        if($tgl_start != "" && $tgl_end !=""){
            $querydb->whereDate('log_stock.created_at','>=',date('Y-m-d',strtotime($tgl_start)));
            $querydb->whereDate('log_stock.created_at','<=',date('Y-m-d',strtotime($tgl_end)));

        }

        $data = $querydb->get();
        foreach($data as $key=> $value)
        {
            $value->no                = $key+1;
            $value->id                = $value->id;
            $value->nama_produk       = $value->product_name;
            $value->dari_stock        = $value->from_stock.' '.$value->name;
            $value->ke_stock          = $value->to_stock.' '.$value->name;
            $value->new_stock         = ($value->from_stock - $value->to_stock).' '.$value->name;
            $value->tgl_mutasi        = date('d-m-Y H:i',strtotime($value->created_at));
            $value->created_by        = $value->created_by;
        }
        return view('backend/stok/stokmutasihistory/print',compact('data','perusahaan','gudang','gudangtujuan','tgl_start','tgl_end'));
    }
    public function pdf(Request $request){
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_gudang_tujuan   = session('filter_gudang_tujuan');
        $tgl_start              = session('filter_tgl_start');
        $tgl_end                = session('filter_tgl_end');

        $perusahaan             = Perusahaan::find($filter_perusahaan);
        $gudang                 = Gudang::find($filter_gudang);
        $gudangtujuan           = Gudang::find($filter_gudang_tujuan);

        $querydb = StockMutasi::select('log_stock.*','product.product_name','satuan.name');
        $querydb->join('product_perusahaan_gudang','product_perusahaan_gudang.id','log_stock.product_perusahaan_gudang_id');
        $querydb->join('product','product.id','product_perusahaan_gudang.product_id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->orderBy('id','DESC');

        if($filter_perusahaan != ""){
            $querydb->where('log_stock.from_perusahaan_id',$filter_perusahaan);
        }else{
            $querydb->where('log_stock.from_perusahaan_id',0);
        }
        if($filter_gudang != ""){
            $querydb->where('log_stock.from_gudang_id',$filter_gudang);
        }else{
            $querydb->where('log_stock.from_gudang_id',0);
        }
        if($filter_gudang_tujuan != ""){
            $querydb->where('log_stock.to_gudang_id',$filter_gudang_tujuan);
        }else{
            $querydb->where('log_stock.to_gudang_id',0);
        }

        if($tgl_start != "" && $tgl_end !=""){
            $querydb->whereDate('log_stock.created_at','>=',date('Y-m-d',strtotime($tgl_start)));
            $querydb->whereDate('log_stock.created_at','<=',date('Y-m-d',strtotime($tgl_end)));

        }

        $data = $querydb->get();
        foreach($data as $key=> $value)
        {
            $value->no                = $key+1;
            $value->id                = $value->id;
            $value->nama_produk       = $value->product_name;
            $value->dari_stock        = $value->from_stock.' '.$value->name;
            $value->ke_stock          = $value->to_stock.' '.$value->name;
            $value->new_stock         = ($value->from_stock - $value->to_stock).' '.$value->name;
            $value->tgl_mutasi        = date('d-m-Y H:i',strtotime($value->created_at));
            $value->created_by        = $value->created_by;
        }


        $config = [
            'mode'                  => '',
            'format'                => 'A4',
            'default_font_size'     => '12',
            'default_font'          => 'sans-serif',
            'margin_left'           => 5,
            'margin_right'          => 5,
            'margin_top'            => 45,
            'margin_bottom'         => 20,
            'margin_header'         => 0,
            'margin_footer'         => 0,
            'orientation'           => 'P',
            'title'                 => 'CETAK HISTORY MUTASI STOCK',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/stok/stokmutasihistory/pdf', ['data'=>$data,'perusahaan'=>$perusahaan,'gudang'=>$gudang,'gudangtujuan'=>$gudangtujuan,'tgl_start'   => $tgl_start,'tgl_end'   => $tgl_end ],[],$config);
        ob_get_clean();
        return $pdf->download('History Mutasi Stock "'.date('d_m_Y H_i_s').'".pdf');
    }
    public function excel(Request $request)
    {
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $filter_gudang_tujuan   = session('filter_gudang_tujuan');
        $tgl_start         = session('filter_tgl_start');
        $tgl_end           = session('filter_tgl_end');

        return Excel::download(new HistoryMutasiStockExports($filter_perusahaan,$filter_gudang, $filter_gudang_tujuan,$tgl_start,$tgl_end),'History Mutasi Stock.xlsx');
    }

}
