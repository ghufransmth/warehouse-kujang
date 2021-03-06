<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gudang;
use App\Models\Kategori;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
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

        $dataquery = Pembelian::select('pembelian.id','pembelian.no_faktur','pembelian.tgl_faktur','pembelian.nominal','pembelian.status_pembelian','tbl_product.kode_product','pembelian_detail.qty','tbl_product.nama as nama_product','tbl_product.kode_product','pembelian_detail.total as total_product');
        $dataquery->join('pembelian_detail','pembelian_detail.pembelian_id','pembelian.id');
        $dataquery->join('tbl_product','tbl_product.id','pembelian_detail.product_id');
        $dataquery->where('status_pembelian',1);
        $dataquery->where('approve_pembelian',0);
        $dataquery->groupBy('no_faktur');
        // $cek = $dataquery->get();
        // return response()->json($cek);

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
    //   return response()->json($data);

      foreach($data as $key => $value){
        $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
        $action = "";
        $action.="";

        $action.='<div>';
        $action.='<a href="'.route('reportbarangmasuk.detail',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-eye"></i> Preview</a>&nbsp;';
        $action.="</div>";

        $value->nomor = $key+$page;
        $value->id = $value->id;
        $value->tgl_faktur = date('d M Y',strtotime($value->tgl_faktur));
        $value->no_faktur = $value->no_faktur;
        $value->nama_product = $value->nama_product;
        $value->total_pembelian = "Rp. ".number_format($value->nominal,0,',','.');
        if($value->status_pembelian == 1){
            $value->status_bayar = "Lunas";
        }else{
            $value->status_bayar = "Belum Lunas";
        }
        $value->action = $action;
      }

      $json_data = array(
          "draw"            =>  intval($request->input('draw')),
          "recordsTotal"    => intval($totalData),
          "recordsFiltered" => intval($totalFiltered),
          "data"            => $data
      );

      return response()->json($json_data);
    }

    public function detail($enc_id){

        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // return $dec_id;
        $pembelian = Pembelian::find($dec_id);
        // return $penjualan->getdetailpenjualan;
        return view('backend/report/barang_masuk/detail',[
            'pembelian' => $pembelian,
            'detail_pembelian' => $pembelian->getdetailpembelian,
            'enc_id' => $enc_id
        ]);
    }


    public function pdf($enc_id)
    {
        $config = [
            'mode'                  => '',
            'format'                => 'A4',
            'default_font_size'     => '12',
            'default_font'          => 'sans-serif',
            'margin_left'           => 5,
            'margin_right'          => 5,
            'margin_top'            => 30,
            'margin_bottom'         => 20,
            'margin_header'         => 0,
            'margin_footer'         => 0,
            'orientation'           => 'L',
            'title'                 => 'CETAK BARANG MASUK',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        $pembelian = Pembelian::find($dec_id);

        $pdf = PDF::loadView('backend/report/barang_masuk/index_barangmasuk_pdf', ['pembelian' => $pembelian, 'detail_pembelian' => $pembelian->getdetailpembelian], [], $config);
        ob_get_clean();

        return $pdf->stream('Report Barang Masuk"' . date('d_m_Y H_i_s') . '".pdf');
    }



    public function print($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // return $dec_id;
        $pembelian = Pembelian::find($dec_id);
        // return $penjualan;
        return view('backend/report/barang_masuk/index_barangmasuk_print',[
            'pembelian' => $pembelian,
            'detail_pembelian' => $pembelian->getdetailpembelian,
            'enc_id' => $enc_id
        ]);
    }

    public function excel($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        $pembelian = Pembelian::find($dec_id);

        $view = 'backend/report/barang_masuk/index_barangmasuk_excel';
        $data = $pembelian;
        $detail_pembelian = $pembelian->getdetailpembelian;


        return Excel::download(new ReportBarangMasukExports($view, $data, $detail_pembelian), 'Report Barang Masuk.xlsx');
    }
}
