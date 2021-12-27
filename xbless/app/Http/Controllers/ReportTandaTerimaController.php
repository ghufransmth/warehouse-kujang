<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\Sales;
use App\Models\City;
use App\Models\Kategori;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\InvoiceTandaTerima;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportTandaTerimaExports;
use App\Models\Member;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;


class ReportTandaTerimaController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        $city     = City::all();
        $perusahaan = Perusahaan::all();
        $sales     = Sales::all();
        $member    = Member::with('getcity:id,name')->get();

        if (session('filter_city') == "") {
            $selectedcity = [];
        } else {
            $selectedcity = session('filter_city');
        }

        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
        }

        if (session('filter_sales') == "") {
            $selectedsales = [];
        } else {
            $selectedsales = session('filter_sales');
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

        if (session('filter_member') == "") {
            $selectedmember = '';
        } else {
            $selectedmember = session('filter_member');
        }
        return view('backend/report/tanda_terima/index_tandaterima', compact('city', 'selectedcity', 'perusahaan', 'selectedperusahaan', 'sales', 'selectedsales', 'tgl_start', 'tgl_end', 'member', 'selectedmember'));
    }
    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];
        $masuk = "Masuk";

        $request->session()->put('filter_kota', $request->filter_kota);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_gudang', $request->filter_gudang);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);
        $request->session()->put('filter_member', $request->filter_member);

        $querydb = ReportStock::select('report_stok_bm_bl.*', 'product_beli.notransaction', 'product_beli.factory_name', 'perusahaan.name as nama_perusahaan', 'gudang.name as nama_gudang', 'product.product_name', 'product.product_code', 'satuan.name as nama_satuan', 'invoice.no_nota as nonota', 'invoice.purchase_no', 'invoice.member_name');
        $querydb->join('perusahaan', 'perusahaan.id', 'report_stok_bm_bl.perusahaan_id');
        $querydb->leftJoin('invoice', 'invoice.id', 'report_stok_bm_bl.invoice_id');
        $querydb->leftJoin('product_beli', 'product_beli.id', 'report_stok_bm_bl.produk_beli_id');
        $querydb->leftJoin('product_beli_detail', 'product_beli.id', 'product_beli_detail.produk_beli_id');

        $querydb->join('product', 'product.id', 'report_stok_bm_bl.product_id');
        $querydb->join('gudang', 'gudang.id', 'report_stok_bm_bl.gudang_id');
        $querydb->join('satuan', 'satuan.id', 'product.satuan_id');
        $querydb->where('report_stok_bm_bl.keterangan', 'LIKE', "%{$masuk}%");

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('report_stok_bm_bl.id', 'DESC');
        }
        // if ($request->filter_produk != "") {
        //     $querydb->where('report_stok_bm_bl.product_id', $request->filter_produk);
        // }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('report_stok_bm_bl.created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('report_stok_bm_bl.created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != "") {
            $querydb->where('report_stok_bm_bl.perusahaan_id', $request->filter_perusahaan);
        }
        if ($request->filter_gudang != "") {
            $querydb->whereIn('report_stok_bm_bl.gudang_id', $request->filter_gudang);
        }


        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
                $query->orWhere('product_code', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        // $querydb->limit($limit);
        // $querydb->offset($start);
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
            $value->transaction_no    = $value->transaction_no;
            $value->nama_produk       = $value->product_name;
            $value->nama_gudang       = $value->nama_gudang;
            $value->stockinput        = $value->stock_input;
            $value->namasatuan        = $value->nama_satuan;
            $value->catatan           = $value->note;
            $value->factoryname       = $value->factory_name == '' ? '-' : $value->factory_name;
            $value->tgl               = date('d/m/Y', strtotime($value->created_at));
        }

        if ($request->user()->can('reporttandaterima.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data,
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
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_sales           = session('filter_sales');
        $filter_kota            = session('filter_kota');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $filter_member          = session('filter_member');
        return response()->json($filter_perusahaan);


        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.uniq_code as kodemember');
        $querydb->join('member', 'member.id', 'invoice.member_id');
        $querydb->where('invoice.flag_tanda_terima', 1);

        if ($filter_sales != "" || $request->has('filter_sales')) {
            $querydb->whereIn('invoice.sales_id', $filter_sales);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }

        if ($filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
        }

        if ($filter_kota != "" || $request->has('filter_kota')) {
            $querydb->whereIn('member.city_id', $filter_kota);
        }

        if ($filter_member != "") {
            $querydb->where('invoice.member_id', $filter_member);
        }

        $querydb->orderBy('member.name', 'asc');
        $querydb->orderBy('invoice.dateorder', 'asc');
        $querydb->orderBy('member.city', 'asc');
        $querydb->groupBy('invoice.member_id');

        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->member_kota       = $value->kota;
            $value->tgl                 = date('F Y', strtotime($value->dateorder));
            $value->member_lengkap      = ($value->kodemember) . ' ' . $value->nama_member . '-' . $value->kota;

            $detail = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.id as id_member');
            $detail->join('member', 'member.id', 'invoice.member_id');
            $detail->where('invoice.flag_tanda_terima', 1);
            $detail->where('invoice.member_id', $value->member_id);

            if ($filter_sales != "" || $request->has('filter_sales')) {
                $detail->whereIn('invoice.sales_id', $filter_sales);
            }

            if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                $detail->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                $detail->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
            }

            if ($filter_perusahaan != "") {
                $detail->where('invoice.perusahaan_id', $filter_perusahaan);
            }

            if ($filter_kota != "" || $request->has('filter_kota')) {
                $detail->whereIn('member.city_id', $filter_kota);
            }

            if ($filter_member != "") {
                $detail->where('member.id', $filter_member);
            }

            $detail->orderBy('invoice.no_nota', 'asc');
            $detail->orderBy('invoice.id', 'desc');
            $detail->orderBy('invoice.perusahaan_id', 'asc');
            $datadetail = $detail->get();

            foreach ($datadetail as $key => $result) {
                $result->invoicett = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as nota', 'invoice.dateorder as tglorder')
                    ->join('invoice', 'invoice.id', 'invoice_tanda_terima.invoice_id')
                    ->where('invoice_id', $result->id)
                    ->first();
            }
            $value->datadetail       = $datadetail;
        }
        return view('backend/report/tanda_terima/index_tandaterima_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_kota', 'filter_sales', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {
        // $filter_perusahaan      = session('filter_perusahaan');
        // $filter_sales           = session('filter_sales');
        // $filter_kota            = session('filter_kota');
        // $filter_tgl_start       = session('filter_tgl_start');
        // $filter_tgl_end         = session('filter_tgl_end');

        $filter_perusahaan      = $request['data']['perusahaan'];
        $filter_sales           = $request['data']['sales'] ?? '';
        $filter_kota            = $request['data']['city'] ?? '';
        $filter_tgl_start       = $request['data']['tgl_start'];
        $filter_tgl_end         = $request['data']['tgl_end'];
        $filter_member          = $request['data']['member'] ?? '';


        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.uniq_code as kodemember');
        $querydb->join('member', 'member.id', 'invoice.member_id');
        $querydb->where('invoice.flag_tanda_terima', 1);

        if ($filter_sales != "") {
            $querydb->whereIn('invoice.sales_id', $filter_sales);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }

        if ($filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
        }

        if ($filter_kota != "") {
            $querydb->whereIn('member.city_id', $filter_kota);
        }

        if ($filter_member != "") {
            $querydb->where('invoice.member_id', $filter_member);
        }

        $querydb->orderBy('nama_member', 'asc');
        $querydb->orderBy('invoice.dateorder', 'asc');
        $querydb->orderBy('member.city', 'asc');
        $querydb->groupBy('invoice.member_id');

        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->member_kota       = $value->kota;
            $value->tgl                 = date('F Y', strtotime($value->dateorder));
            $value->member_lengkap      = ($value->kodemember) . ' ' . $value->nama_member . '-' . $value->kota;

            $detail = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.id as id_member');
            $detail->join('member', 'member.id', 'invoice.member_id');
            $detail->where('invoice.flag_tanda_terima', 1);
            $detail->where('invoice.member_id', $value->member_id);

            if ($filter_sales != "" || $request->has('filter_sales')) {
                $detail->whereIn('invoice.sales_id', $filter_sales);
            }

            if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                $detail->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                $detail->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
            }

            if ($filter_perusahaan != "") {
                $detail->where('invoice.perusahaan_id', $filter_perusahaan);
            }

            if ($filter_kota != "" || $request->has('filter_kota')) {
                $detail->whereIn('member.city_id', $filter_kota);
            }

            if ($filter_member != "") {
                $detail->where('invoice.member_id', $filter_member);
            }

            $detail->orderBy('invoice.no_nota', 'asc');
            $detail->orderBy('invoice.id', 'desc');
            $detail->orderBy('invoice.perusahaan_id', 'asc');
            $datadetail = $detail->get();

            foreach ($datadetail as $key => $result) {
                $result->invoicett = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as nota', 'invoice.dateorder as tglorder')->join('invoice', 'invoice.id', 'invoice_tanda_terima.invoice_id')->where('invoice_id', $result->id)->first();
            }
            $value->datadetail       = $datadetail;
        }

        $config = [
            'mode'                  => '',
            'format'                => 'A4',
            'default_font_size'     => '11',
            'default_font'          => 'sans-serif',
            'margin_left'           => 5,
            'margin_right'          => 5,
            'margin_top'            => 30,
            'margin_bottom'         => 20,
            'margin_header'         => 0,
            'margin_footer'         => 0,
            'orientation'           => 'L',
            'title'                 => 'CETAK TANDA TERIMA',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/tanda_terima/index_tandaterima_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_kota' => $filter_kota, 'filter_sales' => $filter_sales, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Laporan Tanda Terima"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_sales           = session('filter_sales');
        $filter_kota            = session('filter_kota');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $filter_member          = session('filter_member');

        return Excel::download(new ReportTandaTerimaExports($filter_perusahaan, $filter_sales, $filter_kota, $filter_tgl_start, $filter_tgl_end, $filter_member), 'Laporan Tanda Terima.xlsx');
    }

    public function manageExport(Request $request)
    {
        $filter_perusahaan      = $request->perusahaan;
        $filter_sales           = $request->sales;
        $filter_kota            = $request->city;
        $filter_tgl_start       = $request->tgl_start;
        $filter_tgl_end         = $request->tgl_end;
        $filter_member          = $request->member;
        // return response()->json($request->all());
        switch ($request->action) {
            case 'print':

                $perusahaan = Perusahaan::find($filter_perusahaan);

                $querydb = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.uniq_code as kodemember', 'member.city_id');
                $querydb->join('member', 'member.id', 'invoice.member_id');
                $querydb->where('invoice.flag_tanda_terima', 1);

                if (in_array(0, $filter_sales) == false) {

                    $querydb->whereIn('invoice.sales_id', $filter_sales);
                }

                if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                    $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                    $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                }

                if ($filter_perusahaan != "") {
                    $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
                }

                if (in_array(0, $filter_kota) == false) {

                    $querydb->whereIn('member.city_id', $filter_kota);
                }

                if ($filter_member != null) {
                    $querydb->where('invoice.member_id', $filter_member);
                }

                $querydb->orderBy('member.name', 'asc');
                $querydb->orderBy('invoice.dateorder', 'asc');
                $querydb->orderBy('member.city', 'asc');
                $querydb->groupBy('invoice.member_id');

                $data = $querydb->get();



                foreach ($data as $key => $value) {
                    $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
                    $action = "";

                    $value->no                = $key + 1;
                    $value->id                = $value->id;
                    $value->member_kota       = $value->kota;
                    $value->tgl                 = date('F Y', strtotime($value->dateorder));
                    $value->member_lengkap      = '(' . $value->kodemember . ')' . ' ' . $value->nama_member . '-' . $value->kota;

                    $detail = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.id as id_member');
                    $detail->join('member', 'member.id', 'invoice.member_id');
                    $detail->where('invoice.flag_tanda_terima', 1);
                    $detail->where('invoice.member_id', $value->member_id);

                    if (in_array(0, $filter_sales) == false) {
                        $detail->whereIn('invoice.sales_id', $filter_sales);
                    }

                    if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                        $detail->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                        $detail->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                    }

                    if ($filter_perusahaan != "") {
                        $detail->where('invoice.perusahaan_id', $filter_perusahaan);
                    }

                    if (in_array(0, $filter_kota) == false) {
                        $detail->whereIn('member.city_id', $filter_kota);
                    }

                    if ($filter_member != null) {
                        $detail->where('member.id', $filter_member);
                    }

                    $detail->orderBy('invoice.no_nota', 'asc');
                    $detail->orderBy('invoice.id', 'desc');
                    $detail->orderBy('invoice.perusahaan_id', 'asc');
                    $datadetail = $detail->get();

                    foreach ($datadetail as $key => $result) {
                        $result->invoicett = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as nota', 'invoice.dateorder as tglorder')
                            ->join('invoice', 'invoice.id', 'invoice_tanda_terima.invoice_id')
                            ->where('invoice_id', $result->id)
                            ->first();
                    }
                    $value->datadetail       = $datadetail;
                }
                return view('backend/report/tanda_terima/index_tandaterima_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_kota', 'filter_sales', 'filter_tgl_start', 'filter_tgl_end'));
                break;
            case 'pdf':
                $perusahaan = Perusahaan::find($filter_perusahaan);

                $querydb = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.uniq_code as kodemember');
                $querydb->join('member', 'member.id', 'invoice.member_id');
                $querydb->where('invoice.flag_tanda_terima', 1);

                if (in_array(0, $filter_sales) == false) {

                    $querydb->whereIn('invoice.sales_id', $filter_sales);
                }

                if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                    $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                    $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                }

                if ($filter_perusahaan != "") {
                    $querydb->where('invoice.perusahaan_id', $filter_perusahaan);
                }

                if (in_array(0, $filter_kota) == false) {

                    $querydb->whereIn('member.city_id', $filter_kota);
                }

                if ($filter_member != null) {
                    $querydb->where('invoice.member_id', $filter_member);
                }

                $querydb->orderBy('nama_member', 'asc');
                $querydb->orderBy('invoice.dateorder', 'asc');
                $querydb->orderBy('member.city', 'asc');
                $querydb->groupBy('invoice.member_id');

                $data = $querydb->get();

                foreach ($data as $key => $value) {
                    $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
                    $action = "";

                    $value->no                = $key + 1;
                    $value->id                = $value->id;
                    $value->member_kota       = $value->kota;
                    $value->tgl                 = date('F Y', strtotime($value->dateorder));
                    $value->member_lengkap      = '(' . $value->kodemember . ')' . ' ' . $value->nama_member . '-' . $value->kota;

                    $detail = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.id as id_member');
                    $detail->join('member', 'member.id', 'invoice.member_id');
                    $detail->where('invoice.flag_tanda_terima', 1);
                    $detail->where('invoice.member_id', $value->member_id);

                    if (in_array(0, $filter_sales) == false) {
                        $detail->whereIn('invoice.sales_id', $filter_sales);
                    }

                    if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                        $detail->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                        $detail->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                    }

                    if ($filter_perusahaan != "") {
                        $detail->where('invoice.perusahaan_id', $filter_perusahaan);
                    }

                    if (in_array(0, $filter_kota) == false) {
                        $detail->whereIn('member.city_id', $filter_kota);
                    }

                    if ($filter_member != null) {
                        $detail->where('member.id', $filter_member);
                    }

                    $detail->orderBy('invoice.no_nota', 'asc');
                    $detail->orderBy('invoice.id', 'desc');
                    $detail->orderBy('invoice.perusahaan_id', 'asc');
                    $datadetail = $detail->get();

                    foreach ($datadetail as $key => $result) {
                        $result->invoicett = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as nota', 'invoice.dateorder as tglorder')->join('invoice', 'invoice.id', 'invoice_tanda_terima.invoice_id')->where('invoice_id', $result->id)->first();
                    }
                    $value->datadetail       = $datadetail;
                }

                $config = [
                    'mode'                  => '',
                    'format'                => 'A4',
                    'default_font_size'     => '11',
                    'default_font'          => 'sans-serif',
                    'margin_left'           => 5,
                    'margin_right'          => 5,
                    'margin_top'            => 30,
                    'margin_bottom'         => 20,
                    'margin_header'         => 0,
                    'margin_footer'         => 0,
                    'orientation'           => 'L',
                    'title'                 => 'CETAK TANDA TERIMA',
                    'author'                => '',
                    'watermark'             => '',
                    'show_watermark'        => true,
                    'show_watermark_image'  => true,
                    'mirrorMargins'         => 1,
                    'watermark_font'        => 'sans-serif',
                    'display_mode'          => 'default',
                ];
                $pdf = PDF::loadView('backend/report/tanda_terima/index_tandaterima_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_kota' => $filter_kota, 'filter_sales' => $filter_sales, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
                ob_get_clean();
                return $pdf->stream('Laporan Tanda Terima"' . date('d_m_Y H_i_s') . '".pdf');
                break;
            case 'excel':
                return Excel::download(new ReportTandaTerimaExports($filter_perusahaan, $filter_sales, $filter_kota, $filter_tgl_start, $filter_tgl_end, $filter_member), 'Laporan Tanda Terima.xlsx');
                break;
        }
    }
}
