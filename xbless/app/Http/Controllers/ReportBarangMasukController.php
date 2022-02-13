<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\Kategori;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use App\Models\Member;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportBarangMasukExports;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportBarangMasukController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    function safe_encode($string)
    {
        $data = str_replace(array('/'), array('_'), $string);
        return $data;
    }
    function safe_decode($string, $mode = null)
    {
        $data = str_replace(array('_'), array('/'), $string);
        return $data;
    }

    public function index()
    {
        return view('backend/report/barang_masuk/index_barangmasuk');
    }

    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

    //    $dataquery = PembelianDetail::select('pembelian_detail.id','tbl_product.kode_product','pembelian_detail.qty','tbl_product.nama','pembelian.status_pembelian');
    //    $dataquery->join('pembelian','pembelian.id','pembelian_detail.pembelian_id');
    //    $dataquery->join('tbl_product','tbl_product.id','pembelian_detail.product_id');
    //    $dataquery->where('status_pembelian',1);

        $dataquery = Pembelian::select('pembelian.id','pembelian.no_faktur','pembelian.tgl_faktur','pembelian.nominal','pembelian.status_pembelian')->where('status_pembelian',1);

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

      foreach($data as $key=> $value){
        $enc_id = $this->safe_encode(Crypt::encryptString($value->id));

        $value->nomor = $key+$page;
        $value->id = $value->id;
        $value->tgl_faktur = date('d M Y',strtotime($value->tgl_faktur));
        $value->no_faktur = $value->no_faktur;
        $value->total_pembelian = "Rp. ".number_format($value->nominal,2,',','.');
        if($value->status_pembelian == 1){
            $value->status_bayar = "Lunas";
        }else{
            $value->status_bayar = "Belum Lunas";
        }
      }

      $json_data = array(
          "draw"            =>  intval($request->input('draw')),
          "recordsTotal"    => intval($totalData),
          "recordsFiltered" => intval($totalFiltered),
          "data"            => $data
      );

      return response()->json($json_data);
    }
}
