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
        return view('backend/report/barang_masuk/index_barangmasuk', compact('produk', 'selectedproduk', 'perusahaan', 'selectedperusahaan', 'gudang', 'selectedgudang', 'tgl_start', 'tgl_end'));
    }
    public function getData(Request $request)
    {

        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];
        $masuk = "Masuk";

        $request->session()->put('filter_produk', $request->filter_produk);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('report_stok_bm_bl.note', 'LIKE', "%" . $masuk . "%");

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('id', 'DESC');
        }
        if ($request->filter_produk != 0) {

            $querydb->where('product_id', $request->filter_produk);
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != 0) {
            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        }

        if ($request->filter_gudang != "") {
            $querydb->whereIn('gudang_id', $request->filter_gudang);
        }


        $data = $querydb->get();

        $temp = 0;

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key;
            $value->id                = $value->id;

            if ($value->note == 'Order Barang Masuk') {
                $value->transaction_no = $value->produk_beli->notransaction ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->produk_beli->warehouse_date));
                $conv_tanggal_sampai = date('Y-m-d', strtotime($value->produk_beli->warehouse_date));
            } else {
                $value->transaction_no    = $value->transaction_no ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->created_at));
                $conv_tanggal_sampai = date('Y-m-d', strtotime($value->created_at));
            }

            $value->nama_produk       = $value->getproduct != null ? $value->getproduct->product_name : '-';
            $value->nama_gudang       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->stockinput        = $value->note == 'retur barang masuk' ? number_format(abs($value->stock_input), 0, ',', '.') : number_format($value->stock_input, 0, ',', '.');
            $value->namasatuan        = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $value->catatan           = ucfirst($value->note);

            $value->factoryname       = $value->produk_beli != null ? $value->produk_beli->factory_name : '-';
            $value->tgl               = $tanggal_sampai;
            $value->convert_tgl       = $conv_tanggal_sampai;
        }


        $data = $data->filter(function ($item) use ($request) {
            return (strtotime($item->convert_tgl) >= strtotime($request->filter_tgl_start)) && (strtotime($item->convert_tgl) <= strtotime($request->filter_tgl_end));
        });

        $totalData = $data->values()->count();

        $totalFiltered = $data->values()->count();

        if ($start == 0) {
            $data = $data->slice(0, $limit);
        } else {

            $data = $data->slice($start, $limit);
        }

        if ($request->user()->can('reportbarangmasuk.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data->sortByDesc('tgl')->values(),
                "sum_qty"         => $this->sumDataReport($request->filter_produk, $request->filter_tgl_start, $request->filter_tgl_end, $request->filter_perusahaan, $request->filter_gudang)
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
        $masuk = "Masuk";
        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('note', 'LIKE', "%{$masuk}%");
        $querydb->orderBy('id', 'DESC');

        if ($filter_produk != 0) {
            $querydb->where('product_id', $filter_produk);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != 0) {
            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('gudang_id', $filter_gudang);
        }

        $data = $querydb->get();
        $temp = 0;
        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            if ($value->note == 'Order Barang Masuk') {
                $value->transaction_no = $value->produk_beli->notransaction ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->produk_beli->warehouse_date));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->produk_beli->warehouse_date));
            } else {
                $value->transaction_no    = $value->transaction_no ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->created_at));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->created_at));
            }

            $value->no                = $key + 1;
            $value->id                = $value->id;

            $temp = $temp + $value->stock_input;

            $value->transaction_no    = $value->transaction_no ?? '-';

            $value->nama_produk       = $value->getproduct != null ? $value->getproduct->product_name : '-';

            $value->nama_gudang       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->stockinput        = $value->note == 'retur barang masuk' ? number_format(abs($value->stock_input), 0, ',', '.') : number_format($value->stock_input, 0, ',', '.');
            $value->namasatuan        = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $value->catatan           = ucfirst($value->note);

            $value->factoryname       = $value->produk_beli != null ? $value->produk_beli->factory_name : '-';
            $value->tgl               = $tanggal_sampai;
            $value->conv_tgl          = $convert_tanggal_sampai;
        }

        $data = $data->filter(function ($item) use ($filter_tgl_start, $filter_tgl_end) {
            return (strtotime($item->conv_tgl) >= strtotime($filter_tgl_start)) && (strtotime($item->conv_tgl) <= strtotime($filter_tgl_end));
        });

        return view('backend/report/barang_masuk/index_barangmasuk_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_gudang', 'filter_produk', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {
        $filter_produk          = session('filter_produk');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $masuk = "Masuk";


        $perusahaan = Perusahaan::find($filter_perusahaan);
        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('note', 'LIKE', "%{$masuk}%");
        $querydb->orderBy('id', 'DESC');

        if ($filter_produk != 0) {
            $querydb->where('report_stok_bm_bl.product_id', $filter_produk);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('report_stok_bm_bl.created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('report_stok_bm_bl.created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != 0) {
            $querydb->where('report_stok_bm_bl.perusahaan_id', $filter_perusahaan);
        }
        if ($filter_gudang != "") {
            $querydb->whereIn('report_stok_bm_bl.gudang_id', $filter_gudang);
        }

        $data = $querydb->get();
        $temp = 0;
        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            if ($value->note == 'Order Barang Masuk') {
                $value->transaction_no = $value->produk_beli->notransaction ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->produk_beli->warehouse_date));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->produk_beli->warehouse_date));
            } else {
                $value->transaction_no    = $value->transaction_no ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->created_at));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->created_at));
            }

            $value->no                = $key + 1;
            $value->id                = $value->id;

            $temp = $temp + $value->stock_input;

            $value->transaction_no    = $value->transaction_no ?? '-';

            $value->nama_produk       = $value->getproduct != null ? $value->getproduct->product_name : '-';

            $value->nama_gudang       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->stockinput        = $value->note == 'retur barang masuk' ? number_format(abs($value->stock_input), 0, ',', '.') : number_format($value->stock_input, 0, ',', '.');
            $value->namasatuan        = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $value->catatan           = ucfirst($value->note);
            $value->factoryname       = $value->produk_beli != null ? $value->produk_beli->factory_name : '-';
            $value->tgl               = $tanggal_sampai;
            $value->conv_tgl          = $convert_tanggal_sampai;
        }

        $data = $data->filter(function ($item) use ($filter_tgl_start, $filter_tgl_end) {
            return (strtotime($item->conv_tgl) >= strtotime($filter_tgl_start)) && (strtotime($item->conv_tgl) <= strtotime($filter_tgl_end));
        });

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
        $pdf = PDF::loadView('backend/report/barang_masuk/index_barangmasuk_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_gudang' => $filter_gudang, 'filter_perusahaan' => $filter_perusahaan, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Barang Masuk"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_produk          = session('filter_produk');
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_gudang          = session('filter_gudang');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $masuk = "Masuk";

        return Excel::download(new ReportBarangMasukExports($filter_produk, $filter_perusahaan, $filter_gudang, $filter_tgl_start, $filter_tgl_end, $masuk), 'Report Barang Masuk.xlsx');
    }

    private function sumDataReport($produk_id, $tgl_start, $tgl_end, $perusahaan_id, $gudang_id)
    {
        $sum_qty = 0;
        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('report_stok_bm_bl.note', 'LIKE', "%Masuk%");

        if ($produk_id != 0) {

            $querydb->where('product_id', $produk_id);
        }

        if ($tgl_start != "" && $tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)));
        }
        if ($perusahaan_id != 0) {
            $querydb->where('perusahaan_id', $perusahaan_id);
        }

        if ($gudang_id != "") {
            $querydb->whereIn('gudang_id', $gudang_id);
        }
        $data = $querydb->get();
        foreach ($data as $key => $value) {
            if ($value->note == 'Order Barang Masuk') {
                $conv_tanggal_sampai = date('Y-m-d', strtotime($value->produk_beli->warehouse_date));
            } else {
                $conv_tanggal_sampai = date('Y-m-d', strtotime($value->created_at));
            }
            $value->convert_tgl       = $conv_tanggal_sampai;
        }


        $data = $data->filter(function ($item) use ($tgl_start, $tgl_end) {
            return (strtotime($item->convert_tgl) >= strtotime($tgl_start)) && (strtotime($item->convert_tgl) <= strtotime($tgl_end));
        });


        foreach ($data as $key => $value) {
            if ($value->note == 'retur barang masuk') {
                $stok = abs($value->stock_input);
            } else {
                $stok = $value->stock_input;
            }
            $sum_qty += $stok;
        }

        return number_format($sum_qty, 0, ',', '.');
    }

    public function getDataBackup(Request $request)
    {

        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('report_stok_bm_bl.note', 'LIKE', "%Masuk%");


        if ($request->filter_produk != "") {
            $querydb->where('product_id', $request->filter_produk);
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        } else {
            $querydb->where('perusahaan_id', 0);
        }
        if ($request->filter_gudang != "") {
            $querydb->whereIn('gudang_id', $request->filter_gudang);
        }

        $data = $querydb->get();
        return response()->json($data);
    }
}
