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
use App\Exports\HistoryAdjStockExports;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;

class StokAdjHistoryController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        $gudang     = Gudang::all();
        $perusahaan = Perusahaan::all();

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
        // dd(date('d-m-Y',strtotime($tgl_start)));

        return view('backend/stok/stokadjhistory/index',compact('gudang','selectedgudang','perusahaan','selectedperusahaan','tgl_start','tgl_end'));
    }
    public function getData(Request $request)
    {


      $limit = $request->length;
      $start = $request->start;
      $page  = $start +1;
      $search = $request->search['value'];

      $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
      $request->session()->put('filter_gudang', $request->filter_gudang);
      $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
      $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

      $querydb = StockAdj::select('stock_adj.*','product.product_name','satuan.name');
      $querydb->join('product','product.id','stock_adj.product_id');
      $querydb->join('satuan','product.satuan_id','satuan.id');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $querydb->orderBy('id','DESC');
        }

        if($request->filter_perusahaan != ""){
            $querydb->where('stock_adj.perusahaan_id',$request->filter_perusahaan);
        }else{
            $querydb->where('stock_adj.perusahaan_id',0);
        }
        if($request->filter_gudang != ""){
            $querydb->where('stock_adj.gudang_id',$request->filter_gudang);
        }else{
            $querydb->where('stock_adj.gudang_id',0);
        }

        if($request->filter_tgl_start != "" && $request->filter_tgl_end !=""){
            $querydb->whereDate('stock_adj.created_at','>=',date('Y-m-d',strtotime($request->filter_tgl_start)));
            $querydb->whereDate('stock_adj.created_at','<=',date('Y-m-d',strtotime($request->filter_tgl_end)));

        }

       if($search) {
        $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_name','LIKE',"%{$search}%");
                $query->orWhere('name','LIKE',"%{$search}%");
        });
      }
      $totalData = $querydb->get()->count();

      $totalFiltered = $querydb->get()->count();

      $querydb->limit($limit);
      $querydb->offset($start);
      $data = $querydb->get();
      foreach($data as $key=> $value)
      {
        $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
        $action = "";


        $value->no              = $key+$page;
        $value->id              = $value->id;
        $value->nama_produk     = $value->product_name;
        $value->nama_perusahaan = $value->getperusahaan?$value->getperusahaan->name:'-';
        $value->nama_gudang     = $value->getgudang?$value->getgudang->name:'-';
        $value->stock_lama      = $value->qty_product.' '.$value->name;
        $value->stock_adj       = $value->stock_add.' '.$value->name;
        $value->stock_new       = ($value->qty_product + $value->stock_add).' '.$value->name;
        $value->note            = $value->note;
        $value->tgl_adj         = date('d-m-Y H:i',strtotime($value->created_at));
        $value->created_by      = $value->created_by;
      }
      if ($request->user()->can('historyadjstok.index')) {
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

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }
    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    public function print(Request $request){
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $tgl_start         = session('filter_tgl_start');
        $tgl_end           = session('filter_tgl_end');

        $perusahaan        = Perusahaan::find($filter_perusahaan);
        $gudang            = Gudang::find($filter_gudang);

        $querydb = StockAdj::select('stock_adj.*','product.product_name','satuan.name');
        $querydb->join('product','product.id','stock_adj.product_id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->orderBy('id','DESC');

        if($filter_perusahaan != ""){
            $querydb->where('stock_adj.perusahaan_id',$filter_perusahaan);
        }else{
            $querydb->where('stock_adj.perusahaan_id',0);
        }
        if($filter_gudang != ""){
            $querydb->where('stock_adj.gudang_id',$filter_gudang);
        }else{
            $querydb->where('stock_adj.gudang_id',0);
        }
        if($tgl_start != "" && $tgl_end !=""){
            $querydb->whereDate('stock_adj.created_at','>=',date('Y-m-d',strtotime($tgl_start)));
            $querydb->whereDate('stock_adj.created_at','<=',date('Y-m-d',strtotime($tgl_end)));
        }
        $data = $querydb->get();
        foreach($data as $key=> $value)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_produk     = $value->product_name;
            $value->nama_perusahaan = $value->getperusahaan?$value->getperusahaan->name:'-';
            $value->nama_gudang     = $value->getgudang?$value->getgudang->name:'-';
            $value->stock_lama      = $value->qty_product.' '.$value->name;
            $value->stock_adj       = $value->stock_add.' '.$value->name;
            $value->stock_new       = ($value->qty_product + $value->stock_add).' '.$value->name;
            $value->note            = $value->note;
            $value->tgl_adj         = date('d-m-Y H:i',strtotime($value->created_at));
            $value->created_by      = $value->created_by;
        }
        return view('backend/stok/stokadjhistory/print',compact('data','perusahaan','gudang','tgl_start','tgl_end'));
    }
    public function pdf(Request $request){
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $tgl_start         = session('filter_tgl_start');
        $tgl_end           = session('filter_tgl_end');

        $perusahaan        = Perusahaan::find($filter_perusahaan);
        $gudang            = Gudang::find($filter_gudang);

        $querydb = StockAdj::select('stock_adj.*','product.product_name','satuan.name');
        $querydb->join('product','product.id','stock_adj.product_id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->orderBy('id','DESC');

        if($filter_perusahaan != ""){
            $querydb->where('stock_adj.perusahaan_id',$filter_perusahaan);
        }else{
            $querydb->where('stock_adj.perusahaan_id',0);
        }
        if($filter_gudang != ""){
            $querydb->where('stock_adj.gudang_id',$filter_gudang);
        }else{
            $querydb->where('stock_adj.gudang_id',0);
        }
        if($tgl_start != "" && $tgl_end !=""){
            $querydb->whereDate('stock_adj.created_at','>=',date('Y-m-d',strtotime($tgl_start)));
            $querydb->whereDate('stock_adj.created_at','<=',date('Y-m-d',strtotime($tgl_end)));
        }
        $data = $querydb->get();
        foreach($data as $key=> $value)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_produk     = $value->product_name;
            $value->nama_perusahaan = $value->getperusahaan?$value->getperusahaan->name:'-';
            $value->nama_gudang     = $value->getgudang?$value->getgudang->name:'-';
            $value->stock_lama      = $value->qty_product.' '.$value->name;
            $value->stock_adj       = $value->stock_add.' '.$value->name;
            $value->stock_new       = ($value->qty_product + $value->stock_add).' '.$value->name;
            $value->note            = $value->note;
            $value->tgl_adj         = date('d-m-Y H:i',strtotime($value->created_at));
            $value->created_by      = $value->created_by;
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
                'title'                 => 'CETAK HISTORY ADJUSTMENT STOCK',
                'author'                => '',
                'watermark'             => '',
                'show_watermark'        => true,
                'show_watermark_image'  => true,
                'mirrorMargins'         => 1,
                'watermark_font'        => 'sans-serif',
                'display_mode'          => 'default',
            ];
             $pdf = PDF::loadView('backend/stok/stokadjhistory/pdf', ['data'=>$data,'perusahaan'=>$perusahaan,'gudang'=>$gudang, 'tgl_start'   => $tgl_start,'tgl_end'   => $tgl_end ],[],$config);
             ob_get_clean();
             return $pdf->download('History Adjustment Stock "'.date('d_m_Y H_i_s').'".pdf');
    }
    public function excel(Request $request)
    {
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $tgl_start         = session('filter_tgl_start');
        $tgl_end           = session('filter_tgl_end');

        return Excel::download(new HistoryAdjStockExports($filter_perusahaan,$filter_gudang,$tgl_start,$tgl_end),'History Adjustment Stock.xlsx');
    }

}
