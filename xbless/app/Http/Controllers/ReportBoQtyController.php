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
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportBOQTYExports;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use DB;
use Carbon\Carbon;

class ReportBoQtyController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );
    public function statusFilter()
    {
        $value = array('99' => 'Semua', '1' => 'Belum Terkirim', '2' => 'Terkirim');
        return $value;
    }
    public function index()
    {
        $kategori     = Kategori::all();
        $product      = Product::select('id', 'product_code', 'product_name')
            ->offset(0)
            ->limit(10)
            ->get();
        if (session('filter_kategori') == "") {
            $selectedkategori = [];
        } else {
            $selectedkategori = session('filter_kategori');
        }


        if (session('filter_status') == "") {
            $selectedfilterstatus = '99';
        } else {
            $selectedfilterstatus = session('filter_status');
        }

        if (session('filter_keyword') == "") {
            $selectedfilterkeyword = '';
        } else {
            $selectedfilterkeyword = session('filter_keyword');
        }


        if (session('filter_tgl_start') == "") {
            $tgl_start = date('d-m-Y', strtotime(' - 30 days'));
        } else {
            $tgl_start = session('filter_tgl_start');
        }
        $status = $this->statusFilter();
        if (session('filter_tgl_end') == "") {
            $tgl_end = date('d-m-Y');
        } else {
            $tgl_end = session('filter_tgl_end');
        }
        return view('backend/report/qty_back_order/index_bo_qty', compact('selectedfilterkeyword', 'kategori', 'selectedkategori', 'status', 'selectedfilterstatus', 'tgl_start', 'tgl_end', 'product'));
    }
    public function getData(Request $request)
    {


        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_kategori', $request->filter_kategori);
        $request->session()->put('filter_status', $request->filter_status);
        $request->session()->put('filter_keyword', $request->filter_keyword);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori', 'transaction_purchase.flag_status', 'transaction_purchase_detail.discount');
        $querydb->join('transaction_purchase', 'transaction_purchase.id', 'transaction_purchase_detail.transaction_purchase_id');
        $querydb->join('product', 'product.id', 'transaction_purchase_detail.product_id');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');
        $querydb->join('satuan', 'satuan.id', 'product.satuan_id');
        $querydb->join('member', 'member.id', 'transaction_purchase.member_id');
        $querydb->join('sales', 'sales.id', 'transaction_purchase.sales_id');
        // $querydb->where('transaction_purchase.status','!=',2);
        // $querydb->where('transaction_purchase.flag_status',2);
        $querydb->where('transaction_purchase.status', '!=', 2);
        $querydb->where('transaction_purchase.flag_status', '!=', 1);
        $querydb->where('kode_rpo', 'LIKE', '%BO-%');

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('id', 'DESC');
        }

        if ($request->filter_status != "" || $request->filter_status != "99") {
            // $text = 'BO';
            // $querydb->where('kode_rpo', 'LIKE', "%{$text}%");
            if ($request->filter_status == 1) { // filter status belum terkirim
                $querydb->where('transaction_purchase.flag_status', 2);
            } else if ($request->filter_status == 2) { // filter status terkirim
                $querydb->where('transaction_purchase.flag_status', 0);
            }
        }
        if ($request->filter_keyword != 0) {
            // $fiter = $request->filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $request->filter_keyword);
        }

        if ($request->filter_kategori != "") {
            $querydb->whereIn('category_id', $request->filter_kategori);
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('dataorder', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('dataorder', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }

        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_name', 'LIKE', "%{$search}%");
                $query->orWhere('name', 'LIKE', "%{$search}%");
            });
        }
        $querydb->groupBy('transaction_purchase_detail.product_id', 'dataorder');
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";


            $value->no                = $key + $page;
            $value->id                = $value->id;
            $value->nama_toko         = '(' . $value->uniq_code_member . ')' . ' ' . $value->nama_member;
            $value->kota_member       = $value->kota_member;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->nama_kategori;
            $value->qtybo             = $value->qty . ' ' . $value->nama_satuan;
            $value->qty_sum           = $value->qtysum . ' ' . $value->nama_satuan;

            // $value->harga             = number_format($value->price, 0, ',', '.');
            $value->harga             = number_format(($value->price - ($value->price * ($value->discount / 100))), 0, ',', '.');
            $value->ttl_harga         = number_format($value->ttl_price, 0, ',', '.');
            // $value->no_transaksi      = $value->no_nota == null ? $value->kode_rpo : $value->no_nota;
            // $value->status            = $value->kode_rpo == null ? 'Belum Terkirim' : 'Terkirim';
            $value->no_transaksi      = $value->flag_status == 0 ? $value->no_nota : $value->kode_rpo;
            $value->status            = $value->flag_status == 0 ? 'Terkirim' : 'Belum Terkirim';
            $value->tgl               = date("d-m-Y", strtotime($value->dataorder));
            $value->created_by        = $value->created_by;
        }
        if ($request->user()->can('reportboqty.index')) {
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
        $filter_status          = session('filter_status');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');


        // $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori');
        $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori', 'transaction_purchase.flag_status', 'transaction_purchase_detail.discount');
        $querydb->join('transaction_purchase', 'transaction_purchase.id', 'transaction_purchase_detail.transaction_purchase_id');
        $querydb->join('product', 'product.id', 'transaction_purchase_detail.product_id');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');
        $querydb->join('satuan', 'satuan.id', 'product.satuan_id');
        $querydb->join('member', 'member.id', 'transaction_purchase.member_id');
        $querydb->join('sales', 'sales.id', 'transaction_purchase.sales_id');
        // $querydb->where('transaction_purchase.status', '!=', 2);
        // $querydb->where('transaction_purchase.flag_status', 2);
        $querydb->where('transaction_purchase.status', '!=', 2);
        $querydb->where('transaction_purchase.flag_status', '!=', 1);
        $querydb->where('kode_rpo', 'LIKE', '%BO-%');
        $querydb->orderBy('id', 'DESC');


        if ($filter_status != "" || $filter_status != "99") {
            // $text = 'BO';
            // $querydb->where('kode_rpo', 'LIKE', "%{$text}%");
            if ($filter_status == 1) { // filter status belum terkirim
                $querydb->where('transaction_purchase.flag_status', 2);
            } else if ($filter_status == 2) { // filter status terkirim
                $querydb->where('transaction_purchase.flag_status', 0);
            }
        }
        if ($filter_keyword != 0) {
            // $fiter = $filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $filter_keyword);
        }

        if ($filter_kategori != "") {
            $querydb->whereIn('category_id', $filter_kategori);
        }

        if ($filter_tgl_start != "" &&  $filter_tgl_end != "") {
            $querydb->whereDate('dataorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('dataorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        $querydb->groupBy('transaction_purchase_detail.product_id', 'dataorder');
        $data = $querydb->get();
        foreach ($data as $key => $value) {
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->nama_toko         = '(' . $value->uniq_code_member . ')' . ' ' . $value->nama_member;
            $value->kota_member       = $value->kota_member;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->nama_kategori;
            $value->qtybo             = $value->qty . ' ' . $value->nama_satuan;
            $value->qty_sum           = $value->qtysum . ' ' . $value->nama_satuan;
            // $value->harga             = number_format($value->price, 0, ',', '.');
            $value->harga             = number_format(($value->price - ($value->price * ($value->discount / 100))), 0, ',', '.');
            $value->ttl_harga         = number_format($value->ttl_price, 0, ',', '.');
            // $value->no_transaksi      = $value->no_nota == null ? $value->kode_rpo : $value->no_nota;
            // $value->status            = $value->kode_rpo == null ? 'Belum Terkirim' : 'Terkirim';
            $value->no_transaksi      = $value->flag_status == 0 ? $value->no_nota : $value->kode_rpo;
            $value->status            = $value->flag_status == 0 ? 'Terkirim' : 'Belum Terkirim';
            $value->tgl               = date("d-m-Y", strtotime($value->dataorder));
            $value->created_by        = $value->created_by;
        }
        return view('backend/report/qty_back_order/index_bo_qty_print', compact('data', 'filter_status', 'filter_kategori', 'filter_keyword', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {
        $filter_kategori        = session('filter_kategori');
        $filter_status          = session('filter_status');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');


        // $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori');
        $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori', 'transaction_purchase.flag_status', 'transaction_purchase_detail.discount');
        $querydb->join('transaction_purchase', 'transaction_purchase.id', 'transaction_purchase_detail.transaction_purchase_id');
        $querydb->join('product', 'product.id', 'transaction_purchase_detail.product_id');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');
        $querydb->join('satuan', 'satuan.id', 'product.satuan_id');
        $querydb->join('member', 'member.id', 'transaction_purchase.member_id');
        $querydb->join('sales', 'sales.id', 'transaction_purchase.sales_id');
        // $querydb->where('transaction_purchase.status', '!=', 2);
        // $querydb->where('transaction_purchase.flag_status', 2);
        $querydb->where('transaction_purchase.status', '!=', 2);
        $querydb->where('transaction_purchase.flag_status', '!=', 1);
        $querydb->where('kode_rpo', 'LIKE', '%BO-%');
        $querydb->orderBy('id', 'DESC');


        if ($filter_status != "" || $filter_status != "99") {
            // $text = 'BO';
            // $querydb->where('kode_rpo', 'LIKE', "%{$text}%");
            if ($request->filter_status == 1) { // filter status belum terkirim
                $querydb->where('transaction_purchase.flag_status', 2);
            } else if ($request->filter_status == 2) { // filter status terkirim
                $querydb->where('transaction_purchase.flag_status', 0);
            }
        }
        if ($filter_keyword != 0) {
            // $fiter = $filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $filter_keyword);
        }

        if ($filter_kategori != "") {
            $querydb->whereIn('category_id', $filter_kategori);
        }

        if ($filter_tgl_start != "" &&  $filter_tgl_end != "") {
            $querydb->whereDate('dataorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('dataorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        $querydb->groupBy('transaction_purchase_detail.product_id', 'dataorder');
        $data = $querydb->get();
        foreach ($data as $key => $value) {
            // $value->no                = $key + 1;
            // $value->id                = $value->id;
            // $value->nama_toko         = '(' . $value->uniq_code_member . ')' . ' ' . $value->nama_member;
            // $value->kota_member       = $value->kota_member;
            // $value->nama_produk       = $value->product_name;
            // $value->nama_kategori     = $value->nama_kategori;
            // $value->qtybo             = $value->qty . ' ' . $value->nama_satuan;
            // $value->qty_sum           = $value->qtysum . ' ' . $value->nama_satuan;
            // $value->harga             = number_format($value->price, 0, ',', '.');
            // $value->ttl_harga         = number_format($value->ttl_price, 0, ',', '.');
            // $value->no_transaksi      = $value->no_nota == null ? $value->kode_rpo : $value->no_nota;
            // $value->status            = $value->kode_rpo == null ? 'Belum Terkirim' : 'Terkirim';
            // $value->tgl               = date("d-m-Y", strtotime($value->dataorder));
            // $value->created_by        = $value->created_by;
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->nama_toko         = '(' . $value->uniq_code_member . ')' . ' ' . $value->nama_member;
            $value->kota_member       = $value->kota_member;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->nama_kategori;
            $value->qtybo             = $value->qty . ' ' . $value->nama_satuan;
            $value->qty_sum           = $value->qtysum . ' ' . $value->nama_satuan;
            // $value->harga             = number_format($value->price, 0, ',', '.');
            $value->harga             = number_format(($value->price - ($value->price * ($value->discount / 100))), 0, ',', '.');
            $value->ttl_harga         = number_format($value->ttl_price, 0, ',', '.');
            // $value->no_transaksi      = $value->no_nota == null ? $value->kode_rpo : $value->no_nota;
            // $value->status            = $value->kode_rpo == null ? 'Belum Terkirim' : 'Terkirim';
            $value->no_transaksi      = $value->flag_status == 0 ? $value->no_nota : $value->kode_rpo;
            $value->status            = $value->flag_status == 0 ? 'Terkirim' : 'Belum Terkirim';
            $value->tgl               = date("d-m-Y", strtotime($value->dataorder));
            $value->created_by        = $value->created_by;
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
            'title'                 => 'CETAK BO',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/qty_back_order/index_bo_qty_pdf', ['data' => $data, 'filter_status' => $filter_status, 'filter_kategori' => $filter_kategori, 'filter_keyword' => $filter_keyword, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report BO QTY"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_kategori        = session('filter_kategori');
        $filter_status          = session('filter_status');
        $filter_keyword         = session('filter_keyword');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        return Excel::download(new ReportBOQTYExports($filter_kategori, $filter_status, $filter_keyword, $filter_tgl_start, $filter_tgl_end), 'Report BO QTY.xlsx');
    }
}
