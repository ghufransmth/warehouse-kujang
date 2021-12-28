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
use App\Models\ProductBeliDetail;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use App\Exports\ReportStokExports;

class ReportStokController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        $kategori     = Kategori::all();
        $perusahaan     = Perusahaan::all();
        $gudang     = Gudang::all();
        $product      = Product::select('id', 'product_code', 'product_name')
            ->offset(0)
            ->limit(10)
            ->get();

        if (session('filter_kategori') == "") {
            $selectedkategori = '';
        } else {
            $selectedkategori = session('filter_kategori');
        }

        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
        }

        if (session('filter_gudang') == "") {
            $selectedgudang = '';
        } else {
            $selectedgudang = session('filter_gudang');
        }


        if (session('filter_keyword') == "") {
            $selectedfilterkeyword = '';
        } else {
            $find_product = Product::find(session('filter_keyword'));
            $selectedfilterkeyword = !empty($find_product) ? strtoupper($find_product->product_name) : '';
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
        return view('backend/report/stok/index_stok', compact('selectedfilterkeyword', 'kategori', 'selectedkategori', 'perusahaan', 'selectedperusahaan', 'gudang', 'selectedgudang', 'tgl_start', 'tgl_end', 'product'));
    }
    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;

        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_kategori', $request->filter_kategori);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        $request->session()->put('filter_keyword', $request->filter_keyword);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);



        if ($request->filter_keyword != 0) {
            // cek apakah product pencarian adalah produk liner?
            $checkProductIsLiner = Product::find($request->filter_keyword);
            if ($checkProductIsLiner->product_code == $checkProductIsLiner->product_code_shadow && $checkProductIsLiner->is_liner == 'Y') {
                $is_liner_parent = true;
            } else {
                $is_liner_parent = false;
            }
        } else {
            $is_liner_parent = false;
        }

        // data value
        $data_value = ReportStock::with(['getinvoice', 'produk_beli', 'getgudang' => function ($q) {
            $q->select('id', 'name');
        }, 'getproduct' => function ($query) {
            $query->with('category_product:id,cat_name', 'satuans:id,name');
        }])->whereIn('note', ['Purchase Barang Keluar', 'Order Barang Masuk', 'Mutasi', 'retur barang masuk', 'Adjusment', 'Opname']);
        if ($is_liner_parent) {
            $data_value->whereHas('getproduct', function ($query) use ($checkProductIsLiner) {
                $query->where('product_code_shadow', $checkProductIsLiner->product_code);
            });
        } else {
            $data_value->whereHas('getproduct', function ($query) use ($request) {
                $query->where('id', $request->filter_keyword);
            });
        }

        $data_value->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)))
            ->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)))
            ->where('perusahaan_id', $request->filter_perusahaan)
            ->where('gudang_id', $request->filter_gudang);

        if ($request->filter_kategori != "" || $request->filter_kategori != null) {
            if ($request->filter_kategori_length > 0) {
                $data_value->whereHas('getproduct', function ($query) use ($request) {
                    $query->whereIn('category_id', $request->filter_kategori);
                });
            }
        }

        $totalData = $data_value->get()->count();

        $totalFiltered = $data_value->get()->count();

        $data_value->limit($limit);
        $data_value->offset($start);

        $data = $data_value->get();

        $temp = 0;
        foreach ($data as $key => $value) {

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            if ($value->getinvoice != null && $value->keterangan == 'Purchase Keluar') {
                $no_transaction = $value->getinvoice->purchase_no;
                $ket = "Data Penjualan";
                $no_invoice = $value->getinvoice->no_nota;
            } else if ($value->produk_beli != null) {
                $no_transaction = $value->produk_beli->notransaction;
                $ket = "Good Receive";
                $no_invoice = '-';
            } else {
                $no_transaction = $value->transaction_no ?? '-';
                $ket = ucwords($value->keterangan);
                $no_invoice = '-';
            }

            if (!empty($value->invoice->dateorder)) {
                $date_order = date('d M Y', strtotime($value->invoice->dateorder));
            } else if (!empty($value->product_beli->faktur_date)) {
                $date_order = date('d M Y', strtotime($value->product_beli->faktur_date));
            } else {
                $date_order = date('d M Y', strtotime($value->created_at));
            }

            if ($value->note == 'Purchase Barang Keluar') {
                $qty = -$value->stock_input;
            } else if ($value->keterangan == 'retur barang masuk') {
                $qty = abs($value->stock_input);
            } else {
                $qty = $value->stock_input;
            }

            if ($is_liner_parent) {
                if ($value->getproduct->product_code != $value->getproduct->product_code_shadow) {
                    $product_code = $value->getproduct->product_code_shadow . '<br>' . '(' . $value->getproduct->product_code . ')';
                } else {
                    $product_code = $value->getproduct->product_code;
                }
                $product_parent = Product::with('getsatuan:id,name')->where('product_code', $value->getproduct->product_code_shadow)->first();
                $satuan = $product_parent->getsatuan->name;
                $quantity = $qty * $value->getproduct->satuan_value;
            } else {
                $product_code = $value->getproduct->product_code;
                $satuan = $value->getproduct->getsatuan->name;
                $quantity = $qty;
            }



            $value->no                = $key + $page;
            $value->id                = $value->id;
            $value->no_transaction    = $no_transaction;
            $value->no_invoice        = $no_invoice;
            $value->dateorder         = $date_order;
            $value->product_code      = $value->getproduct != null ? $product_code : '-';

            $value->gudang_name       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->ket               = $ket;
            $value->qty               = number_format($quantity, 0, ',', '.');
            $value->satuan            = $satuan;
        }


        $total_stock = $this->getSumTotalFilter($request->filter_keyword, $request->filter_perusahaan, $request->filter_gudang, $request->filter_tgl_start, $request->filter_tgl_end) + $this->getTotalCutOffStock($request->filter_keyword, $request->filter_perusahaan, $request->filter_gudang, date('d-m-Y', strtotime($request->filter_tgl_start . "-1 days")));

        if ($request->user()->can('reportstok.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data,
                "sum_qty"         => number_format($this->getSumTotalFilter($request->filter_keyword, $request->filter_perusahaan, $request->filter_gudang, $request->filter_tgl_start, $request->filter_tgl_end), 0, ',', '.'),
                "cut_off_qty"     => number_format($this->getTotalCutOffStock($request->filter_keyword, $request->filter_perusahaan, $request->filter_gudang, date('d-m-Y', strtotime($request->filter_tgl_start . "-1 days"))), 0, ',', '.'),
                "total"           => number_format($total_stock, 0, ',', '.')

            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "sum_qty"         => $temp
            );
        }

        return json_encode($json_data);
    }
    public function getData_(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;

        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_kategori', $request->filter_kategori);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        $request->session()->put('filter_keyword', $request->filter_keyword);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $invoice_detail = InvoiceDetail::with(['invoice', 'product' => function ($query) {
            $query->with(['category_product:id,cat_name', 'satuans:id,name']);
        }, 'gudang:id,name']);



        if ($request->filter_keyword != 0) {
            // $invoice_detail->where(function ($query) use ($request) {
            //     $query->orWhere('product_code', 'LIKE', "%{$request->filter_keyword}%");
            //     $query->orWhere('product_name', 'LIKE', "%{$request->filter_keyword}%");
            // });
            $invoice_detail->whereHas('product', function ($q) use ($request) {
                $q->where('id', $request->filter_keyword);
            });
        }



        if ($request->filter_kategori != "" || $request->filter_kategori != null) {
            if ($request->filter_kategori_length > 0) {
                $invoice_detail->whereHas('product', function ($query) use ($request) {
                    $query->whereIn('category_id', $request->filter_kategori);
                });
            }
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $invoice_detail->whereHas('invoice', function ($q) use ($request) {
                $q->whereDate('dateorder', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
                $q->whereDate('dateorder', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
            });
        }

        if ($request->filter_perusahaan != "" || $request->filter_perusahaan != null) {
            $invoice_detail->whereHas('invoice', function ($q) use ($request) {
                $q->where('perusahaan_id', $request->filter_perusahaan);
            });
        }

        if ($request->filter_gudang != "") {
            if ($request->filter_gudang_length > 0) {
                $invoice_detail->whereIn('gudang_id', $request->filter_gudang);
            }
        }

        $data_sale = $invoice_detail->get();

        // produk beli
        $pbd = ProductBeliDetail::whereHas('product_beli', function ($q) {
            $q->where('flag_proses', 1);
        })->with(['product_beli', 'produk' => function ($query) {
            $query->select('id', 'product_name', 'product_code', 'category_id', 'satuan_id')->with('satuans:id,name', 'category_product:id,cat_name');
        }, 'gudang:id,name', 'perusahaan:id,name']);


        if ($request->filter_keyword != 0) {
            $pbd->whereHas('produk', function ($query) use ($request) {
                // $query->where(function ($q) use ($request) {
                //     $q->orWhere('product_code', 'LIKE', "%{$request->filter_keyword}%");
                //     $q->orWhere('product_name', 'LIKE', "%{$request->filter_keyword}%");
                // });

                $query->where('id', $request->filter_keyword);
            });
        }

        if ($request->filter_kategori != "" || $request->filter_kategori != null) {
            if ($request->filter_kategori_length > 0) {
                $pbd->whereHas('produk', function ($query) use ($request) {
                    $query->whereIn('category_id', $request->filter_kategori);
                });
            }
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $pbd->whereHas('product_beli', function ($q) use ($request) {
                $q->whereDate('faktur_date', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
                $q->whereDate('faktur_date', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
            });
        }

        if ($request->filter_perusahaan != "" || $request->filter_perusahaan != null) {
            $pbd->where('perusahaan_id', $request->filter_perusahaan);
        }

        if ($request->filter_gudang != "") {
            if ($request->filter_gudang_length > 0) {
                $pbd->whereIn('gudang_id', $request->filter_gudang);
            }
        }

        // stok barang masuk dan barang keluar
        $report_stok = ReportStock::with(['getperusahaan' => function ($q) {
            $q->select('id', 'name');
        }, 'getgudang' => function ($q) {
            $q->select('id', 'name');
        }, 'getproduct' => function ($query) {
            $query->with('category_product:id,cat_name', 'satuans:id,name');
        }]);


        if ($request->filter_keyword != 0) {
            $report_stok->whereHas('getproduct', function ($query) use ($request) {
                $query->where('id', $request->filter_keyword);
            });
        }

        if ($request->filter_kategori != "" || $request->filter_kategori != null) {
            if ($request->filter_kategori_length > 0) {
                $report_stok->whereHas('getproduct', function ($query) use ($request) {
                    $query->whereIn('category_id', $request->filter_kategori);
                });
            }
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $report_stok->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $report_stok->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }

        if ($request->filter_perusahaan != "" || $request->filter_perusahaan != null) {
            $report_stok->where('perusahaan_id', $request->filter_perusahaan);
        }

        if ($request->filter_gudang != "") {
            if ($request->filter_gudang_length > 0) {
                $report_stok->whereIn('gudang_id', $request->filter_gudang);
            }
        }

        $report_stok->whereNotIn('note', $this->itemNotExist());



        $data_produk_beli = $pbd->get();

        $data_bm_bl = $report_stok->get();


        $data = $data_sale->merge($data_produk_beli)->merge($data_bm_bl);


        $temp = 0;
        foreach ($data->values()->all() as $key => $value) {

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            // mendapatkan nomor nota dari gabungan beberapa data
            if (!empty($value->invoice->no_nota)) {
                $no_nota = $value->invoice->no_nota ?? '-';
                $ket = "Data Penjualan";
            } else if (!empty($value->product_beli->notransaction)) {
                $no_nota = $value->product_beli->notransaction ?? '-';
                $ket = "Good Receive";
            } else {
                $no_nota = $value->notransaction ?? '-';
                $ket = $value->keterangan;
            }

            if (!empty($value->invoice->purchase_no)) {
                $no_invoice = $value->invoice->purchase_no ?? '-';
            } else {
                $no_invoice = '-';
            }

            if (!empty($value->invoice->dateorder)) {
                $date_order = date('d M Y', strtotime($value->invoice->dateorder));
            } else if (!empty($value->product_beli->faktur_date)) {
                $date_order = date('d M Y', strtotime($value->product_beli->faktur_date));
            } else {
                $date_order = date('d M Y', strtotime($value->created_at));
            }

            if (!empty($value->product_code)) {
                $product_code = $value->product_code;
            } else if (!empty($value->produk->product_code)) {
                $product_code = $value->produk->product_code;
            } else if (!empty($value->getproduct->product_code)) {
                $product_code = $value->getproduct->product_code;
            } else {
                $product_code = '-';
            }

            if (!empty($value->gudang->name)) {
                $gudang = $value->gudang->name;
            } else if (!empty($value->getgudang->name)) {
                $gudang = $value->getgudang->name;
            } else {
                $gudang = '-';
            }

            if (!empty($value->qty_kirim)) {
                $qty = '-' . $value->qty_kirim;
            } else if (!empty($value->qty_receive)) {
                $qty = $value->qty_receive;
            } else if (!empty($value->stock_input)) {
                $qty = $value->note == 'retur barang masuk' ? abs($value->stock_input) : $value->stock_input;
            } else {
                $qty = 0;
            }

            if (!empty($value->product->satuans->name)) {
                $satuan = $value->product->satuans->name;
            } else if (!empty($value->produk->satuans->name)) {
                $satuan = $value->produk->satuans->name;
            } else if (!empty($value->getproduct->satuans->name)) {
                $satuan = $value->getproduct->satuans->name;
            } else {
                $satuan = '-';
            }

            $value->no                = $key + $page;
            $value->id                = $value->id;
            $value->no_nota           = $no_nota;
            $value->no_invoice        = $no_invoice;
            $value->dateorder         = $date_order;
            $value->product_code      = $product_code;
            $value->gudang_name       = $gudang;
            $value->ket               = $ket;
            $value->qty               = $qty;
            $value->satuan            = $satuan;

            $temp += $qty;
        }

        $totalData = count($data->sortBy('dateorder')->values());
        $totalFiltered = count($data->sortBy('dateorder')->values());

        if ($start == 0) {
            // $data = $data->slice(0, $limit);
            $data = $data->sortBy('dateorder')->values()->slice(0, $limit);
        } else {
            $data = $data->sortBy('dateorder')->values()->slice($start, $limit);
        }

        $stock_invoice_detail_one_year_before = $this->getStockInvoiceDetailOneYearBefore($request->filter_keyword);
        $stock_receive_one_year_before = $this->getStockProductBeliDetailOneYearBefore($request->filter_keyword);
        $stock_input_report_bm_bl_one_year_before = $this->getStokReportOneYearBefore($request->filter_keyword);

        $qty_total_cut_off = intval($stock_invoice_detail_one_year_before) + intval($stock_receive_one_year_before) + intval($stock_input_report_bm_bl_one_year_before);


        if ($request->user()->can('reportstok.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data->sortBy('dateorder')->values(),
                "sum_qty"         => $temp,
                "cut_off_qty"     => $qty_total_cut_off,
                "total"           => $temp + $qty_total_cut_off

            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
                "sum_qty"         => $temp
            );
        }

        return json_encode($json_data);
    }

    public function print(Request $request)
    {
        $filter_kategori   = session('filter_kategori');
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $filter_keyword    = session('filter_keyword');
        $filter_tgl_start  = session('filter_tgl_start');
        $filter_tgl_end    = session('filter_tgl_end');


        $perusahaan = Perusahaan::find($filter_perusahaan);
        $product = Product::find($filter_keyword);

        if ($filter_keyword != 0) {
            // cek apakah product pencarian adalah produk liner?
            if ($product->product_code == $product->product_code_shadow && $product->is_liner == 'Y') {
                $is_liner_parent = true;
            } else {
                $is_liner_parent = false;
            }
        } else {
            $is_liner_parent = false;
        }

        $data_value = ReportStock::with(['getinvoice', 'produk_beli', 'getgudang' => function ($q) {
            $q->select('id', 'name');
        }, 'getproduct' => function ($query) {
            $query->with('category_product:id,cat_name', 'satuans:id,name');
        }])->whereIn('note', ['Purchase Barang Keluar', 'Order Barang Masuk', 'Mutasi', 'retur barang masuk', 'Adjusment', 'Opname']);


        if ($filter_keyword != 0) {
            if ($is_liner_parent) {
                $data_value->whereHas('getproduct', function ($query) use ($product) {
                    $query->where('product_code_shadow', $product->product_code);
                });
            } else {
                $data_value->whereHas('getproduct', function ($query) use ($filter_keyword) {
                    $query->where('id', $filter_keyword);
                });
            }
        }

        if ($filter_kategori != "" || $filter_kategori != null) {

            $data_value->whereHas('getproduct', function ($query) use ($filter_kategori) {
                $query->whereIn('category_id', $filter_kategori);
            });
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $data_value->whereDate('created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $data_value->whereDate('created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }

        if ($filter_perusahaan != "" || $filter_perusahaan != null) {
            $data_value->where('perusahaan_id', $filter_perusahaan);
        }

        if ($filter_gudang != "") {

            $data_value->where('gudang_id', $filter_gudang);
        }

        $data = $data_value->get();

        $temp = 0;

        foreach ($data as $key => $value) {

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            if ($value->getinvoice != null && $value->keterangan == 'Purchase Keluar') {
                $no_transaction = $value->getinvoice->purchase_no;
                $ket = "Data Penjualan";
                $no_invoice = $value->getinvoice->no_nota;
            } else if ($value->produk_beli != null) {
                $no_transaction = $value->produk_beli->notransaction;
                $ket = "Good Receive";
                $no_invoice = '-';
            } else {
                $no_transaction = $value->transaction_no ?? '-';
                $ket = ucwords($value->keterangan);
                $no_invoice = '-';
            }


            // if ($value->getinvoice != null) {
            //     $no_invoice = $value->getinvoice->purchase_no;
            // } else {
            //     $no_invoice = '-';
            // }

            if (!empty($value->invoice->dateorder)) {
                $date_order = date('d M Y', strtotime($value->invoice->dateorder));
            } else if (!empty($value->product_beli->faktur_date)) {
                $date_order = date('d M Y', strtotime($value->product_beli->faktur_date));
            } else {
                $date_order = date('d M Y', strtotime($value->created_at));
            }

            if ($value->note == 'Purchase Barang Keluar') {
                $qty = -$value->stock_input;
            } else if ($value->keterangan == 'retur barang masuk') {
                $qty = abs($value->stock_input);
            } else {
                $qty = $value->stock_input;
            }

            if ($is_liner_parent) {
                if ($value->getproduct->product_code != $value->getproduct->product_code_shadow) {
                    $product_code = $value->getproduct->product_code_shadow   . "<br>" . "(" . $value->getproduct->product_code . ")";
                } else {
                    $product_code = $value->getproduct->product_code;
                }
                $product_parent = Product::with('getsatuan:id,name')->where('product_code', $value->getproduct->product_code_shadow)->first();
                $satuan = $product_parent->getsatuan->name;
                $quantity = $qty * $value->getproduct->satuan_value;
            } else {
                $product_code = $value->getproduct->product_code;
                $satuan = $value->getproduct->getsatuan->name;
                $quantity = $qty;
            }


            $value->id                = $value->id;
            $value->no_transaction    = $no_transaction;
            $value->no_invoice        = $no_invoice;
            $value->dateorder         = $date_order;
            $value->product_code      = $product_code;
            $value->gudang_name       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->ket               = $ket;
            $value->qty               = number_format($quantity, 0, ',', '.');
            $value->satuan            = $satuan;
        }

        $sum_total_filter = $this->getSumTotalFilter($filter_keyword, $filter_perusahaan, $filter_gudang, $filter_tgl_start, $filter_tgl_end);
        $sum_total_cutoff = $this->getTotalCutOffStock($filter_keyword, $filter_perusahaan, $filter_gudang, date('d-m-Y', strtotime($filter_tgl_start . "-1 days")));

        return view('backend/report/stok/index_stok_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_tgl_start', 'filter_tgl_end', 'filter_keyword', 'sum_total_filter', 'sum_total_cutoff', 'product'));
    }

    public function pdf(Request $request)
    {
        $filter_kategori   = session('filter_kategori');
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $filter_keyword    = session('filter_keyword');
        $filter_tgl_start  = session('filter_tgl_start');
        $filter_tgl_end    = session('filter_tgl_end');


        $perusahaan = Perusahaan::find($filter_perusahaan);
        $product = Product::find($filter_keyword);

        if ($filter_keyword != 0) {
            // cek apakah product pencarian adalah produk liner?
            if ($product->product_code == $product->product_code_shadow && $product->is_liner == 'Y') {
                $is_liner_parent = true;
            } else {
                $is_liner_parent = false;
            }
        } else {
            $is_liner_parent = false;
        }

        $data_value = ReportStock::with(['getinvoice', 'produk_beli', 'getgudang' => function ($q) {
            $q->select('id', 'name');
        }, 'getproduct' => function ($query) {
            $query->with('category_product:id,cat_name', 'satuans:id,name');
        }])->whereIn('note', ['Purchase Barang Keluar', 'Order Barang Masuk', 'Mutasi', 'retur barang masuk', 'Adjusment', 'Opname']);


        if ($filter_keyword != 0) {
            if ($is_liner_parent) {
                $data_value->whereHas('getproduct', function ($query) use ($product) {
                    $query->where('product_code_shadow', $product->product_code);
                });
            } else {
                $data_value->whereHas('getproduct', function ($query) use ($filter_keyword) {
                    $query->where('id', $filter_keyword);
                });
            }
        }

        if ($filter_kategori != "" || $filter_kategori != null) {

            $data_value->whereHas('getproduct', function ($query) use ($filter_kategori) {
                $query->whereIn('category_id', $filter_kategori);
            });
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $data_value->whereDate('created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $data_value->whereDate('created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }

        if ($filter_perusahaan != "" || $filter_perusahaan != null) {
            $data_value->where('perusahaan_id', $filter_perusahaan);
        }

        if ($filter_gudang != "") {

            $data_value->where('gudang_id', $filter_gudang);
        }

        $data = $data_value->get();

        $temp = 0;

        foreach ($data as $key => $value) {

            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            if ($value->getinvoice != null && $value->keterangan == 'Purchase Keluar') {
                $no_transaction = $value->getinvoice->purchase_no;
                $ket = "Data Penjualan";
                $no_invoice = $value->getinvoice->no_nota;
            } else if ($value->produk_beli != null) {
                $no_transaction = $value->produk_beli->notransaction;
                $ket = "Good Receive";
                $no_invoice = '-';
            } else {
                $no_transaction = $value->transaction_no ?? '-';
                $ket = ucwords($value->keterangan);
                $no_invoice = '-';
            }


            // if ($value->getinvoice != null) {
            //     $no_invoice = $value->getinvoice->purchase_no;
            // } else {
            //     $no_invoice = '-';
            // }

            if (!empty($value->invoice->dateorder)) {
                $date_order = date('d M Y', strtotime($value->invoice->dateorder));
            } else if (!empty($value->product_beli->faktur_date)) {
                $date_order = date('d M Y', strtotime($value->product_beli->faktur_date));
            } else {
                $date_order = date('d M Y', strtotime($value->created_at));
            }

            if ($value->note == 'Purchase Barang Keluar') {
                $qty = -$value->stock_input;
            } else if ($value->keterangan == 'retur barang masuk') {
                $qty = abs($value->stock_input);
            } else {
                $qty = $value->stock_input;
            }

            if ($is_liner_parent) {
                if ($value->getproduct->product_code != $value->getproduct->product_code_shadow) {
                    $product_code = $value->getproduct->product_code_shadow   . "<br>" . "(" . $value->getproduct->product_code . ")";
                } else {
                    $product_code = $value->getproduct->product_code;
                }
                $product_parent = Product::with('getsatuan:id,name')->where('product_code', $value->getproduct->product_code_shadow)->first();
                $satuan = $product_parent->getsatuan->name;
                $quantity = $qty * $value->getproduct->satuan_value;
            } else {
                $product_code = $value->getproduct->product_code;
                $satuan = $value->getproduct->getsatuan->name;
                $quantity = $qty;
            }

            $value->id                = $value->id;
            $value->no_transaction    = $no_transaction;
            $value->no_invoice        = $no_invoice;
            $value->dateorder         = $date_order;
            $value->product_code      = $product_code;
            $value->gudang_name       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->ket               = $ket;
            $value->qty               = $quantity;
            $value->satuan            = $satuan;
        }

        $sum_total_filter = $this->getSumTotalFilter($filter_keyword, $filter_perusahaan, $filter_gudang, $filter_tgl_start, $filter_tgl_end);
        $sum_total_cutoff = $this->getTotalCutOffStock($filter_keyword, $filter_perusahaan, $filter_gudang, date('d-m-Y', strtotime($filter_tgl_start . "-1 days")));



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
            'title'                 => 'Laporan History Stok',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/stok/index_stok_pdf', ['data' => $data, 'filter_perusahaan' => $filter_perusahaan,  'filter_tgl_start' => $filter_tgl_start,  'filter_tgl_end' => $filter_tgl_end, 'perusahaan' => $perusahaan, 'filter_keyword' => $filter_keyword, 'sum_total_filter' => $sum_total_filter, 'sum_total_cutoff' => $sum_total_cutoff, 'product' => $product], [], $config);
        ob_get_clean();
        return $pdf->stream('Report History Stok"' . date('d_m_Y H_i_s') . '".pdf');
    }

    public function excel(Request $request)
    {
        $filter_kategori   = session('filter_kategori');
        $filter_perusahaan = session('filter_perusahaan');
        $filter_gudang     = session('filter_gudang');
        $filter_keyword    = session('filter_keyword');
        $filter_tgl_start  = session('filter_tgl_start');
        $filter_tgl_end    = session('filter_tgl_end');

        return Excel::download(new ReportStokExports($filter_perusahaan, $filter_gudang, $filter_keyword, $filter_tgl_start, $filter_tgl_end, $filter_kategori), 'Report History Stok.xlsx');
    }

    private function getSumTotalFilter($product_id, $perusahaan_id, $gudang_id, $tgl_start, $tgl_end)
    {
        $sum = 0;
        $sum_report = 0;
        $product = Product::find($product_id);
        if (!empty($product)) {
            // $dataAdj  = StockAdj::select('stock_add', 'created_at', 'qty_product')
            //     ->where([
            //         'product_id'    => $product_id,
            //         'perusahaan_id' => $perusahaan_id,
            //         'gudang_id'     => $gudang_id
            //     ])->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
            //     ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
            //     ->orderBy('id', 'desc')
            //     ->first();

            if ($product->is_liner == 'Y') {
                if ($product->product_code == $product->product_code_shadow) {
                    $is_liner_parent = true;
                } else {
                    $is_liner_parent = false;
                }
            } else {
                $is_liner_parent = false;
            }

            if (!$is_liner_parent) {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!empty($get_note_report_desc)) {

                $data_report_stock->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)));

                if ($get_note_report_desc->note == 'Opname' || $get_note_report_desc->note == 'Adjusment') {
                    $sum_report =  $get_note_report_desc->stock_input;
                } else {
                    $data_prev_last_record = $data_report_stock->get();
                    if ($data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Opname' || $data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Adjusment') {
                        if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'retur barang masuk') {
                            $qty = abs($data_prev_last_record[count($data_prev_last_record) - 1]->stock_input);
                        } else if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'Purchase Keluar') {
                            $qty = -$data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        } else {
                            $qty = $data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        }
                        $sum_report =  $data_prev_last_record[count($data_prev_last_record) - 2]->stock_input + $qty;
                    } else {
                        if (!$is_liner_parent) {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id'           => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id_shadow'    => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                        if (!empty($get_data_last_opname_or_adjustment)) {
                            $data_sum = $data_report_stock
                                ->where('id', '>=', $get_data_last_opname_or_adjustment->id)
                                ->get();
                        } else {
                            $data_sum = $data_report_stock->get();
                        }


                        foreach ($data_sum as $k => $nilai) {
                            $get_data_product = Product::find($nilai->product_id);
                            if ($is_liner_parent) {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input * $get_data_product->satuan_value;
                                } else {
                                    $qty = $nilai->stock_input * $get_data_product->satuan_value;
                                }
                            } else {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input);
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input;
                                } else {
                                    $qty = $nilai->stock_input;
                                }
                            }


                            if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                                if ($nilai->produk_beli->flag_proses == 1) {
                                    $satuan_value = $qty;
                                } else {
                                    $satuan_value = 0;
                                }
                            } else {
                                $satuan_value = $qty;
                            }

                            $sum_report += $satuan_value;
                        }
                    }
                }
            }


            // if (!empty($dataAdj)) {
            //     $data_sum = $data_report_stock->where('created_at', '>=', $dataAdj->created_at)->get();
            // } else {
            //     $data_sum = $data_report_stock->get();
            // }

            // return $data_sum;

            // foreach ($data_sum as $k => $nilai) {
            //     $get_data_product = Product::find($nilai->product_id);
            //     if ($is_liner_parent) {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input * $get_data_product->satuan_value;
            //         } else {
            //             $qty = $nilai->stock_input * $get_data_product->satuan_value;
            //         }
            //     } else {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input);
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input;
            //         } else {
            //             $qty = $nilai->stock_input;
            //         }
            //     }


            //     if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
            //         if ($nilai->produk_beli->flag_proses == 1) {
            //             $satuan_value = $qty;
            //         } else {
            //             $satuan_value = 0;
            //         }
            //     } else {
            //         $satuan_value = $qty;
            //     }

            //     $sum_report += $satuan_value;
            // }
        }
        return $sum_report;
    }

    private function getTotalCutOffStock($product_id, $perusahaan_id, $gudang_id, $one_day_prev_tgl_start)
    {
        $sum = 0;
        $sum_report = 0;
        $product = Product::find($product_id);
        if (!empty($product)) {
            // $dataAdj  = StockAdj::select('stock_add', 'created_at', 'qty_product')
            //     ->where([
            //         'product_id'    => $product_id,
            //         'perusahaan_id' => $perusahaan_id,
            //         'gudang_id'     => $gudang_id
            //     ])->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
            //     ->orderBy('id', 'desc')
            //     ->first();

            if ($product->is_liner == 'Y') {
                if ($product->product_code == $product->product_code_shadow) {
                    $is_liner_parent = true;
                } else {
                    $is_liner_parent = false;
                }
            } else {
                $is_liner_parent = false;
            }

            if (!$is_liner_parent) {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!empty($get_note_report_desc)) {

                $data_report_stock->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)));

                if ($get_note_report_desc->note == 'Opname' || $get_note_report_desc->note == 'Adjusment') {
                    $sum_report =  $get_note_report_desc->stock_input;
                } else {
                    $data_prev_last_record = $data_report_stock->get();
                    if ($data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Opname' || $data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Adjusment') {
                        if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'retur barang masuk') {
                            $qty = abs($data_prev_last_record[count($data_prev_last_record) - 1]->stock_input);
                        } else if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'Purchase Keluar') {
                            $qty = -$data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        } else {
                            $qty = $data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        }
                        $sum_report =  $data_prev_last_record[count($data_prev_last_record) - 2]->stock_input + $qty;
                    } else {
                        if (!$is_liner_parent) {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id'           => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id_shadow'    => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                        if (!empty($get_data_last_opname_or_adjustment)) {
                            $data_sum = $data_report_stock
                                ->where('id', '>=', $get_data_last_opname_or_adjustment->id)
                                ->get();
                        } else {
                            $data_sum = $data_report_stock->get();
                        }


                        foreach ($data_sum as $k => $nilai) {
                            $get_data_product = Product::find($nilai->product_id);
                            if ($is_liner_parent) {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input * $get_data_product->satuan_value;
                                } else {
                                    $qty = $nilai->stock_input * $get_data_product->satuan_value;
                                }
                            } else {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input);
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input;
                                } else {
                                    $qty = $nilai->stock_input;
                                }
                            }


                            if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                                if ($nilai->produk_beli->flag_proses == 1) {
                                    $satuan_value = $qty;
                                } else {
                                    $satuan_value = 0;
                                }
                            } else {
                                $satuan_value = $qty;
                            }

                            $sum_report += $satuan_value;
                        }
                    }
                }
            }


            // if (!$is_liner_parent) {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id'           => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // } else {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id_shadow'    => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // }



            // if (!empty($dataAdj)) {
            //     $data_sum = $data_report_stock->where('created_at', '>=', $dataAdj->created_at)->get();
            // } else {
            //     $data_sum = $data_report_stock->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))->get();
            // }


            // foreach ($data_sum as $k => $nilai) {
            //     $produk = Product::find($nilai->product_id);

            //     if ($is_liner_parent) {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input) * $produk->satuan_value;
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input * $produk->satuan_value;
            //         } else {
            //             $qty = $nilai->stock_input * $produk->satuan_value;
            //         }
            //     } else {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input);
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input;
            //         } else {
            //             $qty = $nilai->stock_input;
            //         }
            //     }
            //     if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
            //         if ($nilai->produk_beli->flag_proses == 1) {
            //             $satuan_value = $qty * $produk->satuan_value;
            //         } else {
            //             $satuan_value = 0;
            //         }
            //     } else {
            //         $satuan_value = $qty * $produk->satuan_value;
            //     }

            //     $sum_report += $satuan_value;
            // }
        }
        return $sum_report;
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

    public function itemNotExist()
    {
        return ['Order Barang Masuk', 'Purchase Barang Keluar', 'Adjusment'];
    }
}
