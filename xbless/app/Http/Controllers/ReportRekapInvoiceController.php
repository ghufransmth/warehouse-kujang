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
use App\Exports\ReportInvoiceExports;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportRekapInvoiceController extends Controller
{
    protected $original_column = array(
        1 => "created_at",
        2 => "purchase_no",
        3 => "purchase_no",
        4 => "no_nota",
        5 => "member_name",
        7 => "expedisi",
        8 => "total"
    );

    public function index()
    {
        $perusahaan = Perusahaan::all();

        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
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
        return view('backend/report/rekap_invoice/index_invoice', compact('perusahaan', 'selectedperusahaan', 'tgl_start', 'tgl_end'));
    }
    public function getData(Request $request)
    {


        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $querydb = Invoice::select('*');

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('no_nota', 'DESC');
        }
        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }
        if ($request->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        }


        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('no_nota', 'LIKE', "%{$fiter}%");
                $query->orWhere('member_name', 'LIKE', "%{$search}%");
                $query->orWhere('expedisi_name', 'LIKE', "%{$search}%");
            });
        }
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
            $value->nopo              = $value->purchase_no;
            $cekso                    = explode("/", $value->purchase_no);
            $value->so                = $cekso[1];
            $value->nonota            = $value->no_nota;
            $value->member_name       = $value->getMember ? $value->getMember->name : '-';
            $value->member_kota       = $value->getMember ? $value->getMember->city : '-';
            $value->nama_expedisi     = $value->getExpedisi ? $value->getExpedisi->name : '-';
            $value->totalppn          = number_format($value->total, 0, ',', '.');
            $value->tgl               = date('d/m/Y', strtotime($value->created_at));
        }

        if ($request->user()->can('reportinvoice.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data,
            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => [],
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

        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        $perusahaan = Perusahaan::find($filter_perusahaan);
        $querydb = Invoice::select('*');
        $querydb->orderBy('no_nota', 'ASC');

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->nopo              = $value->purchase_no;
            $cekso                    = explode("/", $value->purchase_no);
            $value->so                = $cekso[1];
            $value->nonota            = $value->no_nota;
            $value->member_name       = $value->getMember ? $value->getMember->name : '-';
            $value->member_kota       = $value->getMember ? $value->getMember->city : '-';
            $value->nama_expedisi     =  $value->getExpedisi ? $value->getExpedisi->name : '-';
            $value->totalppn          = number_format($value->total, 0, ',', '.');
            $value->tgl               = date('d/m/Y', strtotime($value->created_at));
        }
        return view('backend/report/rekap_invoice/index_invoice_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {

        $filter_perusahaan      = session('filter_perusahaan');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');



        $perusahaan = Perusahaan::find($filter_perusahaan);
        $querydb = Invoice::select('*');
        $querydb->orderBy('no_nota', 'ASC');


        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }
        if ($filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $filter_perusahaan);
        }


        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->nopo              = $value->purchase_no;
            $cekso                    = explode("/", $value->purchase_no);
            $value->so                = $cekso[1];
            $value->nonota            = $value->no_nota;
            $value->member_name       = $value->getMember ? $value->getMember->name : '-';
            $value->member_kota       = $value->getMember ? $value->getMember->city : '-';
            $value->nama_expedisi     = $value->getExpedisi ? $value->getExpedisi->name : '-';
            $value->totalppn          = number_format($value->total, 0, ',', '.');
            $value->tgl               = date('d/m/Y', strtotime($value->created_at));
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
            'title'                 => 'CETAK REPORT INVOICE',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/rekap_invoice/index_invoice_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Invoice"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {

        $filter_perusahaan      = session('filter_perusahaan');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        return Excel::download(new ReportInvoiceExports($filter_perusahaan, $filter_tgl_start, $filter_tgl_end), 'Report Invoice.xlsx');
    }
}
