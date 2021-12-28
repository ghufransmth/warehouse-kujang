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
use App\Exports\ReportBarangKeluarExports;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportBarangKeluarController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        // $produk     = Product::all();
        $perusahaan = Perusahaan::all();
        $gudang     = Gudang::all();
        $produk      = Product::select('id', 'product_code', 'product_name')
            ->offset(0)
            ->limit(10)
            ->get();

        if (session('filter_produk') == "") {
            $selectedproduk = '';
        } else {
            $selectedproduk = session('filter_produk');
        }

        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
        }

        if (session('filter_gudang') == "") {
            $selectedgudang = [];
        } else {
            $selectedgudang = session('filter_gudang');
        }

        if (session('filter_tgl_start') == "") {
            $tgl_start = date('d-m-Y', strtotime(' - 30 days'));
        } else {
            $tgl_start = session('filter_tgl_start');
        }

        if (session('filter_tgl_end') == "") {
            $tgl_end = date('d-m-Y');
        } else {
            $tgl_end = session('filter_tgl_end');
        }
        return view('backend/report/barang_keluar/index_barangkeluar', compact('produk', 'selectedproduk', 'perusahaan', 'selectedperusahaan', 'gudang', 'selectedgudang', 'tgl_start', 'tgl_end'));
    }
    public function getData(Request $request)
    {


        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];
        $masuk = "Keluar";

        $request->session()->put('filter_produk', $request->filter_produk);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $querydb =  ReportStock::whereHas('transaction_order_bm_bl', function ($q) {
            $q->where('flag_status', 0);
        })->with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang', 'transaction_detail', 'transaction_order_bm_bl' => function ($query) {
            $query->with('getmember');
        }]);

        $querydb->where('note', 'like', '%' . $request->ket . '%');

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('report_stok_bm_bl.id', 'DESC');
        }

        if ($request->filter_produk != "") {
            $querydb->where('product_id', $request->filter_produk);
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('updated_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('updated_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        }
        if ($request->filter_gudang != "") {
            $querydb->whereIn('gudang_id', $request->filter_gudang);
        }

        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_name', 'LIKE', "%{$search}%");
                $query->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $data = $querydb->get();

        $temp = 0;
        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + $page;
            $value->id                = $value->id;
            if ($value->note != "Adjusment" || $value->note != "adjusment") {
                $temp = $temp + $value->stock_input;
            }


            $nonota = $value->getinvoice != null ? $value->getinvoice->no_nota : '-';
            $nama_member = $value->transaction_order_bm_bl != null ? ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->name : '-') : '-';
            $kota = $value->transaction_order_bm_bl != null ?  ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->city : '-') : '-';
            $gudang = $value->getgudang != null ? $value->getgudang->name : '-';
            $nama_produk = $value->getproduct != null ? $value->getproduct->product_name : '-';
            $satuan = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $harga = $value->transaction_detail != null ? $value->transaction_detail->price - ($value->transaction_detail->price * ($value->transaction_detail->discount / 100)) : 0;
            $no_purchase = $value->transaction_order_bm_bl != null ? $value->transaction_order_bm_bl->no_nota : '-';


            $value->no_purchase       = $no_purchase;
            $value->nonota            = $nonota;
            $value->nama_member       = $nama_member . ' - ' . $kota;

            $value->nama_gudang       = $gudang;
            $value->nama_produk       = $nama_produk;

            $value->stockinput        = $value->stock_input != null || $value->stock_input != '' ? number_format($value->stock_input, 0, ',', '.') : '-';

            $value->namasatuan        = $satuan;
            $value->catatan           = $value->note;

            $value->harga             = number_format(round($harga), 0, ',', '.');

            $value->tgl               = date('d/m/Y', strtotime($value->updated_at));
            $value->conv_tgl          = date('Y-m-d', strtotime($value->updated_at));
        }

        if ($request->user()->can('reportbarangkeluar.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data->sortByDesc('conv_tgl')->values(),
                "sum_qty"         => $temp,
            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "sum_qty"         => 0,
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


        $filter_produk          = session('filter_produk');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        $masuk = "Keluar";
        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb =  ReportStock::whereHas('transaction_order_bm_bl', function ($q) {
            $q->where('flag_status', 0);
        })->with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang', 'transaction_detail', 'transaction_order_bm_bl' => function ($query) {
            $query->with('getmember');
        }]);

        $querydb->where('note', 'like', '%' . $masuk . '%');

        if ($filter_produk != "") {
            $querydb->where('product_id', $filter_produk);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('updated_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('updated_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('gudang_id', $filter_gudang);
        }



        $data = $querydb->get();


        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;

            $nonota = $value->getinvoice != null ? $value->getinvoice->no_nota : '-';
            $nama_member = $value->transaction_order_bm_bl != null ? ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->name : '-') : '-';
            $kota = $value->transaction_order_bm_bl != null ?  ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->city : '-') : '-';
            $gudang = $value->getgudang != null ? $value->getgudang->name : '-';
            $nama_produk = $value->getproduct != null ? $value->getproduct->product_name : '-';
            $satuan = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $harga = $value->transaction_detail != null ? $value->transaction_detail->price - ($value->transaction_detail->price * ($value->transaction_detail->discount / 100)) : 0;
            $no_purchase = $value->transaction_order_bm_bl != null ? $value->transaction_order_bm_bl->no_nota : '-';


            $value->no_purchase       = $no_purchase;
            $value->nonota            = $nonota;
            $value->nama_member       = $nama_member;
            $value->kota              = $kota;


            $value->nama_gudang       = $gudang;
            $value->nama_produk       = $nama_produk;

            $value->stockinput        = $value->stock_input != null || $value->stock_input != '' ? number_format($value->stock_input, 0, ',', '.') : '-';

            $value->namasatuan        = $satuan;
            $value->catatan           = $value->note;

            $value->harga             = number_format(round($harga), 0, ',', '.');

            $value->tgl               = date('d/m/Y', strtotime($value->updated_at));

            $value->conv_tgl          = date('Y-m-d', strtotime($value->updated_at));
        }

        $data = $data->sortByDesc('conv_tgl')->values();

        return view('backend/report/barang_keluar/index_barangkeluar_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_gudang', 'filter_produk', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {
        $filter_produk          = session('filter_produk');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $masuk = "keluar";


        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb =  ReportStock::whereHas('transaction_order_bm_bl', function ($q) {
            $q->where('flag_status', 0);
        })->with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang', 'transaction_detail', 'transaction_order_bm_bl' => function ($query) {
            $query->with('getmember');
        }]);

        $querydb->where('note', 'like', '%' . $masuk . '%');

        if ($filter_produk != "") {
            $querydb->where('product_id', $filter_produk);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('updated_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('updated_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('gudang_id', $filter_gudang);
        }



        $data = $querydb->get();


        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;

            $nonota = $value->getinvoice != null ? $value->getinvoice->no_nota : '-';
            $nama_member = $value->transaction_order_bm_bl != null ? ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->name : '-') : '-';
            $kota = $value->transaction_order_bm_bl != null ?  ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->city : '-') : '-';
            $gudang = $value->getgudang != null ? $value->getgudang->name : '-';
            $nama_produk = $value->getproduct != null ? $value->getproduct->product_name : '-';
            $satuan = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $harga = $value->transaction_detail != null ? $value->transaction_detail->price - ($value->transaction_detail->price * ($value->transaction_detail->discount / 100)) : 0;
            $no_purchase = $value->transaction_order_bm_bl != null ? $value->transaction_order_bm_bl->no_nota : '-';


            $value->no_purchase       = $no_purchase;
            $value->nonota            = $nonota;
            $value->nama_member       = $nama_member;
            $value->kota              = $kota;


            $value->nama_gudang       = $gudang;
            $value->nama_produk       = $nama_produk;

            $value->stockinput        = $value->stock_input != null || $value->stock_input != '' ? number_format($value->stock_input, 0, ',', '.') : '-';

            $value->namasatuan        = $satuan;
            $value->catatan           = $value->note;

            $value->harga             = number_format(round($harga), 0, ',', '.');

            $value->tgl               = date('d/m/Y', strtotime($value->updated_at));

            $value->conv_tgl          = date('Y-m-d', strtotime($value->updated_at));
        }

        $data = $data->sortByDesc('conv_tgl')->values();
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
        $pdf = PDF::loadView('backend/report/barang_keluar/index_barangkeluar_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_gudang' => $filter_gudang, 'filter_perusahaan' => $filter_perusahaan, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Barang Keluar"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_produk          = session('filter_produk');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $masuk = "Keluar";

        return Excel::download(new ReportBarangKeluarExports($filter_produk, $filter_perusahaan, $filter_gudang, $filter_tgl_start, $filter_tgl_end, $masuk), 'Report Barang Keluar.xlsx');
    }

    public function getDataBackup(Request $request)
    {

        $querydb =  ReportStock::whereHas('transaction_order_bm_bl', function ($q) {
            $q->where('flag_status', 0);
        })
            ->with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
                $query->with('getsatuan:id,name');
            }, 'getgudang', 'transaction_detail', 'transaction_order_bm_bl' => function ($query) {
                $query->with('getmember');
            }]);

        if ($request->filter_produk != "") {
            $querydb->where('product_id', $request->filter_produk);
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        }
        if ($request->filter_gudang != "") {
            $querydb->whereIn('gudang_id', $request->filter_gudang);
        }



        $querydb->where('note', 'like', '%' . $request->ket . '%');

        $data = $querydb->get();


        return response()->json($data);
    }
}
