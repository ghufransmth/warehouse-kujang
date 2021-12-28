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
use App\Models\StockMutasi;
use App\Models\StockView;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\HistoryMutasiStockExports;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;

class StokSalesController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );
    public function index()
    {
        return view('backend/stok/stoksales/index');
    }
    public function getData(Request $request)
    {
            $limit = $request->length;
            $start = $request->start;
            $page  = $start +1;
            $search = $request->search['value'];

            $querydb = Product::select('product.*','satuan.name as satuan_name','category_product.cat_name');
            $querydb->join('satuan','product.satuan_id','satuan.id');
            $querydb->join('category_product','category_product.id','product.category_id');

            if(array_key_exists($request->order[0]['column'], $this->original_column)){
                $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
            }
            else{
                $querydb->orderBy('id','DESC');
            }

           if($search) {
            $querydb->where(function ($query) use ($search) {
                    $query->orWhere('product_code','LIKE',"%{$search}%");
                    $query->orWhere('product_name','LIKE',"%{$search}%");
                    $query->orWhere('satuan.name','LIKE',"%{$search}%");
                    $query->orWhere('cat_name','LIKE',"%{$search}%");

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
            $detaildata = "";
            $action.="";

            $action.='<a href="#" onclick="DetailStok(this,\''.$key.'\')" class="btn btn-success btn-block btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-database"></i> Detail Stok</a>';

            if($value->is_liner == 'Y'){
                if($value->product_code==$value->product_code_shadow){
                    $satuanvalue   = $value->satuan_value;
                    $productid     = $value->id;
                }else{
                    $product       = Product::where('product_code', $value->product_code_shadow)->first();
                    $satuanvalue   = $value->satuan_value;
                    $productid     = $product?$product->id:0;
                }
            }else{
                // $satuanvalue    = $value->satuan_value;
                $satuanvalue       = 1;
                $productid         = $value->id;
            }
            $product_gudang = ProductPerusahaanGudang::where('product_id', $productid)->get()->sum('stok');
            $stok = floor($product_gudang/$satuanvalue);
            if($stok > 0){
                $action.='<span class="badge badge-primary btn-block text-center">Stock Available</span>';
            }else{
                $action.='<span class="badge badge-danger btn-block text-center">Out Of Stock</span>';
            }
            $stokview        = StockView::select('name')->where('product_id',$productid)->orderBy('name','asc','namagudang','asc')->groupBy('name')->get();


            $detaildata.="<table class='table'>";
                $detaildata.="<thead>";
                    $detaildata.="<tr>";
                        $detaildata.="<th>PERUSAHAAN</th>";
                        $detaildata.="<th>INFORMASI STOK PER GUDANG</th>";
                    $detaildata.="</tr>";
                $detaildata.="</thead>";
                $detaildata.="<tbody>";
                foreach ($stokview as $per => $result) {
                    $stokviewgudang  = StockView::select('namagudang','stok','name')->where('product_id',$productid)->where('name',$result->name)->orderBy('name','asc','namagudang','asc')->get();
                    $detaildata.="<tr>";
                        $detaildata.="<th><b>".$result->name."</b></th>";
                        $detaildata.="<td>";
                        $detaildata.="<table class='table'>";
                        foreach ($stokviewgudang as $gud => $gudang) {
                            $detaildata.="<tr>";
                            $detaildata.="<td><b>".$gudang->namagudang."</b></td>";
                            $detaildata.="<td><b>".number_format(floor($gudang->stok/$satuanvalue),0,',','.')."</b></td>";
                            $detaildata.="</tr>";
                        }
                        $detaildata.="</table></td>";
                    $detaildata.="</tr>";
                }
                $detaildata.="</tbody>";
            $detaildata.="</table>";

            $value->no                = $key+$page;
            $value->id                = $value->id;
            $value->kode_produk       = $value->product_code;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->cat_name;
            // $value->nama_satuan    = $value->satuan_name.' '.$satuanvalue;
            $value->nama_satuan       = $value->satuan_name;
            $value->detail            = $action;
            $value->detaildata        = $detaildata;

          }
          if ($request->user()->can('stoksales.index')) {
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

    public function getDataX(Request $request)
    {

        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $request->session()->put('filter_perusahaan_sales', $request->filter_perusahaan_sales);
        $request->session()->put('filter_gudang_sales', $request->filter_gudang_sales);
        if($request->filter_perusahaan_sales != "" && $request->filter_gudang_sales != ""){
            $cek_perusahaan_gudang_id = PerusahaanGudang::where('perusahaan_id',$request->filter_perusahaan_sales)->where('gudang_id',$request->filter_gudang_sales)->first();
            if($cek_perusahaan_gudang_id){
                $perusahaan_gudang_id = $cek_perusahaan_gudang_id->id;
            }else{
                $perusahaan_gudang_id = 0;
            }
        }else{
            $perusahaan_gudang_id = 0;
        }

        $querydb = Product::select('product.*','satuan.name as satuan_name','product_perusahaan_gudang.stok','category_product.cat_name');
        $querydb->join('product_perusahaan_gudang','product_perusahaan_gudang.product_id','product.id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->join('category_product','category_product.id','product.category_id');
        $querydb->where('product_perusahaan_gudang.perusahaan_gudang_id',$perusahaan_gudang_id);

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $querydb->orderBy('id','DESC');
        }

       if($search) {
        $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_code','LIKE',"%{$search}%");
                $query->orWhere('product_name','LIKE',"%{$search}%");
                $query->orWhere('satuan.name','LIKE',"%{$search}%");
                $query->orWhere('cat_name','LIKE',"%{$search}%");

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
        $produk_child = Product::where('product_code_shadow',$value->product_code)->where('product_code','!=',$value->product_code)->get();
        $product_child="";
        foreach ($produk_child as $no => $item) {
            $product_child .='<span style="color:#1c84c6">'.$item->product_code.' - '.$item->product_name.'</span><br>';
        }
        $value->no                = $key+$page;
        $value->id                = $value->id;
        $value->kode_produk       = $value->product_code;
        $value->nama_produk       = $value->product_name.'<br> '.$product_child;
        $value->nama_kategori     = $value->cat_name;
        $value->nama_satuan       = $value->satuan_name;
        $value->qty               = $value->stok;

      }
      if ($request->user()->can('stoksales.index')) {
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
}
