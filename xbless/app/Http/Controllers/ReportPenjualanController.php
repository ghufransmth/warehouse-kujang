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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportPenjualanExports;
use App\Models\Penjualan;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportPenjualanController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        return view('backend/report/penjualan/index_penjualan');
    }
    public function getData(Request $request)
    {

        $periode_start = $request->tgl_start;
        $periode_end   = $request->tgl_end;
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];


        $querydb = Penjualan::select('*');
        $querydb->orderBy('id', 'DESC');
        if($periode_start){
            $querydb->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        }
        if($periode_end){
            $querydb->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));
        }
        if ($search) {
            $querydb->where(function ($query) use ($search) {
                // $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
                $query->orWhere('product_code', 'LIKE', "%{$search}%");
                // $query->orWhere('category_product.cat_name', 'LIKE', "%{$fiter}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();
        // return $data;


        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + $page;
            $value->id                = $value->id;

            // $value->no              = $value->product_code;
            $value->no_faktur       = $value->no_faktur;
            $value->tgl_transaksi           = $value->tgl_faktur;
            $value->total_harga               = $value->total_harga;
            if($value->status_lunas == 1){
                $status_lunas = "Lunas";
            }else{
                $status_lunas = "Belum Lunas";
            }
            $value->status_lunas    = $status_lunas;
        }
        if ($request->user()->can('reportpenjualan.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => []
            );
        }

        return json_encode($json_data);
    }

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
    public function print(Request $request)
    {


        $filter_kategori        = session('filter_kategori');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');


        $querydb = Invoice::select('invoice_detail.*', 'invoice.dateorder', 'invoice.perusahaan_id', 'product.product_name', 'product.category_id', 'product.product_code', 'product.part_no', 'category_product.cat_name as nama_kategori', DB::raw('SUM(invoice_detail.qty_kirim) as qtykirim'));
        $querydb->join('invoice_detail', 'invoice.id', 'invoice_detail.invoice_id');
        //$querydb->join('product','product.id','invoice_detail.product_id');
        $querydb->join('product', 'product.product_code', 'invoice_detail.product_code');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');



        if ($filter_keyword != 0) {
            $fiter = $filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $fiter);
        }

        if ($filter_kategori != "") {
            $querydb->whereIn('category_id', $filter_kategori);
        }

        if ($filter_tgl_start != "" &&  $filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('invoice_detail.gudang_id', $filter_gudang);
        }

        $querydb->groupBy('invoice_detail.product_code');

        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->code              = $value->product_code;
            $value->nama_kategori     = $value->nama_kategori;
            $value->part_no           = $value->part_no;
            $value->qty               = $value->qtykirim . ' ' . $value->satuan;
        }
        return view('backend/report/penjualan/index_penjualan_print', compact('data', 'filter_perusahaan', 'filter_gudang', 'filter_kategori', 'filter_keyword', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {
        $filter_kategori        = session('filter_kategori');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');


        $querydb = Invoice::select('invoice_detail.*', 'invoice.dateorder', 'invoice.perusahaan_id', 'product.product_name', 'product.category_id', 'product.product_code', 'product.part_no', 'category_product.cat_name as nama_kategori', DB::raw('SUM(invoice_detail.qty_kirim) as qtykirim'));
        $querydb->join('invoice_detail', 'invoice.id', 'invoice_detail.invoice_id');
        //$querydb->join('product','product.id','invoice_detail.product_id');
        $querydb->join('product', 'product.product_code', 'invoice_detail.product_code');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');



        if ($filter_keyword != 0) {
            $fiter = $filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $fiter);
        }

        if ($filter_kategori != "") {
            $querydb->whereIn('category_id', $filter_kategori);
        }

        if ($filter_tgl_start != "" &&  $filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('invoice_detail.gudang_id', $filter_gudang);
        }

        $querydb->groupBy('invoice_detail.product_code');
        $data = $querydb->get();
        foreach ($data as $key => $value) {
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->code              = $value->product_code;
            $value->nama_kategori     = $value->nama_kategori;
            $value->part_no           = $value->part_no;
            $value->qty               = $value->qtykirim . ' ' . $value->satuan;
        }


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
            'title'                 => 'CETAK PENJUALAN',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/penjualan/index_penjualan_pdf', ['data' => $data, 'filter_perusahaan' => $filter_perusahaan, 'filter_gudang' => $filter_gudang, 'filter_kategori' => $filter_kategori, 'filter_keyword' => $filter_keyword, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Penjualan"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_kategori        = session('filter_kategori');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        return Excel::download(new ReportPenjualanExports($filter_kategori, $filter_perusahaan, $filter_gudang, $filter_keyword, $filter_tgl_start, $filter_tgl_end), 'Report Penjualan.xlsx');
    }
}
