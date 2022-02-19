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
use App\Models\Penjualan;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
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
        $perusahaan = [];
        $gudang     = [];
        $produk      = [];
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

        // return $request->all();
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $tgl_start = $request->tgl_start;
        $tgl_end = $request->tgl_end;

        $search = $request->search['value'];


        $querydb = Penjualan::select('*');
        $querydb->whereHas('getdetailpenjualan');
        if($tgl_start){
            $querydb->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($tgl_start)));
        }
        if($tgl_end){
            $querydb->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($tgl_end)));
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
        // return $data;

        // $temp = 0;
        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + $page;
            $value->id                = $value->id;

            // $value->no              = $value->product_code;
            $value->no_faktur       = $value->no_faktur;
            $value->tgl_transaksi           = $value->tgl_faktur;
            $value->total_harga               = format_uang($value->total_harga);
            if($value->status_lunas == 1){
                $status_lunas = "Lunas";
            }else{
                $status_lunas = "Belum Lunas";
            }
            $aksi = '<a href="'.route("reportbarangkeluar.detail", $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-eye"></i> Preview </a>';
            $value->status_lunas    = $status_lunas;
            $value->aksi = $aksi;
        }

        if ($request->user()->can('reportbarangkeluar.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data->sortByDesc('conv_tgl')->values(),
                // "sum_qty"         => $temp,
            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                // "sum_qty"         => 0,
            );
        }

        return json_encode($json_data);
    }
    public function detail($id){
        $dec_id = $this->safe_decode(Crypt::decryptString($id));
        // return $dec_id;
        $penjualan = Penjualan::find($dec_id);
        // return $penjualan->getdetailpenjualan;
        return view('backend/report/barang_keluar/index_barangkeluar_detail',[
            'penjualan' => $penjualan,
            'detail_penjualan' => $penjualan->getdetailpenjualan,
            'enc_id' => $id
        ]);
    }
    public function print($id){
        $dec_id = $this->safe_decode(Crypt::decryptString($id));
        // return $dec_id;
        $penjualan = Penjualan::find($dec_id);
        // return $penjualan;
        return view('backend/report/barang_keluar/index_barangkeluar_print',[
            'penjualan' => $penjualan,
            'detail_penjualan' => $penjualan->getdetailpenjualan,
            'enc_id' => $id
        ]);
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
        // return $dec_id;
        $penjualan = Penjualan::find($dec_id);
        // return $penjualan;
        // return view('backend/report/barang_keluar/index_barangkeluar_print',[
        //     'penjualan' => $penjualan,
        //     'detail_penjualan' => $penjualan->getdetailpenjualan,
        //     'enc_id' => $id
        // ]);
        $pdf = PDF::loadView('backend/report/barang_keluar/index_barangkeluar_pdf', ['penjualan' => $penjualan, 'detail_penjualan' => $penjualan->getdetailpenjualan], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Barang Keluar"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        $penjualan = Penjualan::find($dec_id);
        // $filter_produk          = session('filter_produk');
        // $filter_perusahaan      = session('filter_perusahaan');
        // $filter_gudang          = session('filter_gudang');
        // $filter_tgl_start       = session('filter_tgl_start');
        // $filter_tgl_end         = session('filter_tgl_end');
        // $masuk = "Keluar";
        $view = 'backend/report/barang_keluar/index_barangkeluar_excel';
        $data = $penjualan;
        $detail_penjualan = $penjualan->getdetailpenjualan;


        return Excel::download(new ReportBarangKeluarExports($view, $data, $detail_penjualan), 'Report Barang Keluar.xlsx');
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
