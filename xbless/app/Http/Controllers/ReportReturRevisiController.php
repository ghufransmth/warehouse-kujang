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
use App\Models\InvoiceReturRevisi;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\ReportReturRevisiExports;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportReturRevisiController extends Controller
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

    public function jenisTransaksi()
    {
        $value = array('RET' => 'Retur', 'REV' => 'Revisi');
        return $value;
    }
    public function index()
    {
        $perusahaan = Perusahaan::all();

        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
        }

        $jenistransaksi = $this->jenisTransaksi();
        if (session('filter_jenis_transaksi') == "") {
            $selectedjenistransaksi = '';
        } else {
            $selectedjenistransaksi = session('filter_jenis_transaksi');
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
        if (session('filter_no_retur') == "") {
            $no_returrevisi = "";
        } else {
            $no_returrevisi = session('filter_no_retur');
        }

        return view('backend/report/retur_revisi/index_returrevisi', compact('jenistransaksi', 'selectedjenistransaksi', 'perusahaan', 'selectedperusahaan', 'tgl_start', 'tgl_end', 'no_returrevisi'));
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
        $request->session()->put('filter_no_retur', $request->filter_no_retur);
        $request->session()->put('filter_jenis_transaksi', $request->filter_jenis_transaksi);

        $querydb = InvoiceReturRevisi::select('invoice_retur_revisi.*', 'm.name as nama_member', 'm.city as nama_kota', 'inv.no_nota as no_nota', 'inv_detail.product_name as nama_produk', 'inv_detail.product_code as kode_produk', 'inv_detail.deskripsi as deskripsi', 'inv_detail.qty as qty_ttl', 'inv_detail.price as harga_satuan', 'inv_detail.ttl_price as harga_total', 'inv_detail.satuan as nama_satuan', 'p.name as nama_perusahaan', 'inv.perusahaan_id', 'inv.dateorder');
        $querydb->join('invoice as inv', 'inv.id', 'invoice_retur_revisi.invoice_id');
        $querydb->join('invoice_detail as inv_detail', 'inv_detail.id', 'invoice_retur_revisi.invoice_detail_id');
        $querydb->join('perusahaan as p', 'p.id', 'inv.perusahaan_id');
        $querydb->join('member as m', 'm.id', 'inv.member_id');

        if ($request->filter_perusahaan != "") {
            $querydb->where('inv.perusahaan_id', $request->filter_perusahaan);
        }

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('invoice_retur_revisi.nomor_retur_revisi', 'DESC');
        }

        if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
            $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
            $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
        }


        if ($request->filter_no_retur != "") {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$request->filter_no_retur}%");
        }

        if ($request->filter_jenis_transaksi == "") {
            $querydb->whereRaw('qty_before != qty_change');
            $querydb->orWhereRaw('price_before != price_change');
            if ($request->filter_perusahaan != "") {
                $querydb->where('inv.perusahaan_id', $request->filter_perusahaan);
            }
            if ($request->filter_no_retur != "") {
                $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$request->filter_no_retur}%");
            }
            if ($request->filter_tgl_start != "" && $request->filter_tgl_end != "") {
                $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)));
                $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
            }
        } else {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$request->filter_jenis_transaksi}%");
            if ($request->filter_jenis_transaksi == 'RET') {
                $querydb->whereRaw('qty_before != qty_change');
            } else if ($request->filter_jenis_transaksi == 'REV') {
                $querydb->whereRaw('price_before != price_change');
            }
        }


        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$search}%");
                $query->orWhere('m.name', 'LIKE', "%{$search}%");
                $query->orWhere('inv.no_nota', 'LIKE', "%{$search}%");
            });
        }

        $data = $querydb->groupBy('nomor_retur_revisi')->get();

        $totalData = $querydb->groupBy('nomor_retur_revisi')->get()->count();

        $totalFiltered = $querydb->groupBy('nomor_retur_revisi')->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);


        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $data_produk_info = [];
            $data_keterangan_perubahan = [];
            // untuk mendapatkan data barang apa aja yang di retur/revisi dari spesifik invoice
            $retur_revisi_data = InvoiceReturRevisi::where([
                'nomor_retur_revisi' => $value->nomor_retur_revisi,
                'invoice_id' => $value->invoice_id
            ])->get();

            foreach ($retur_revisi_data as $key => $item_data) {
                if ($item_data->qty_change == $item_data->qty_before && $item_data->price_change == $item_data->price_before) {
                    continue;
                }
                $invoice_detail = InvoiceDetail::find($item_data->invoice_detail_id);
                $j              = explode("/", $item_data->nomor_retur_revisi);
                if ($j[2] == "RET") {
                    if ($item_data->qty_before == $item_data->qty_change) {
                        $status = "Tidak ada Perubahan Qty";
                    } else {
                        $status = "Perubahan Qty : " . $item_data->qty_before . " menjadi " . $item_data->qty_change . "";
                    }
                } else if ($j[2] == "REV") {
                    if ($item_data->price_before == $item_data->price_change) {
                        $status = "Tidak ada Perubahan Harga Satuan";
                    } else {
                        $status = "Perubahan Harga : " . number_format($item_data->price_before, 0, ',', '.') . " menjadi " . number_format($item_data->price_change, 0, ',', '.') . "";
                    }
                } else {
                    $status = "";
                }
                $data_produk = $invoice_detail->product_code . ' - ' . $invoice_detail->product_name;
                array_push($data_produk_info, $data_produk);
                array_push($data_keterangan_perubahan, $status);
            }

            $value->no                = $key + $page;
            $value->id                = $value->id;
            $value->noretur           = $value->nomor_retur_revisi . '<br>' . '<span class="badge badge-info">' . date('d M y', strtotime($value->created_at)) . '</span>';
            $value->no_inv            = $value->no_nota;
            $value->member_name       = $value->nama_member;

            $value->nama_kota         = $value->nama_kota;

            $value->produk_info      = $data_produk_info;
            $pi = "";
            $pi .= '<table class="table" style="margin-top:-10px;">';
            foreach ($data_produk_info as $key => $produk_info) {

                $pi .= '<tr>';
                // $pi .= '<td>' . $produk_info . '</td>';
                $pi .= '<td style="border-top: 1px solid #808080"><div class="card" style="width: auto; border:none; height: 130px"><div class="card-body"><p class="card-text">' . $produk_info . '</p></div></div></td>';

                $pi .= '<tr>';
            }
            $pi .= '</table>';
            $value->produk_info_x      = $pi;

            $value->ket = $data_keterangan_perubahan;
            $ket = "";
            $ket .= '<table class="table" style="margin-top:-10px;">';
            foreach ($data_keterangan_perubahan as $key => $ket_per) {

                $ket .= '<tr>';
                // $ket .= '<td>' . $ket_per . '</td>';
                $ket .= '<td style="border-top: 1px solid #808080"><div class="card" style="width: auto; border:none; height:130px"><div class="card-body"><p class="card-text">' . $ket_per . '</p></div></div></td>';
                $ket .= '<tr>';
            }
            $ket .= '</table>';
            $value->ket_perubahan     = $ket;
            $value->tgl               = date('d/m/Y', strtotime($value->dateorder));
        }

        if ($request->user()->can('reportreturrevisi.index')) {
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

        $filter_no_retur        = session('filter_no_retur');
        $filter_jenis_transaksi = session('filter_jenis_transaksi');

        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb = InvoiceReturRevisi::select('invoice_retur_revisi.*', 'm.name as nama_member', 'm.city as nama_kota', 'inv.no_nota as no_nota', 'inv_detail.product_name as nama_produk', 'inv_detail.product_code as kode_produk', 'inv_detail.deskripsi as deskripsi', 'inv_detail.qty as qty_ttl', 'inv_detail.price as harga_satuan', 'inv_detail.ttl_price as harga_total', 'inv_detail.satuan as nama_satuan', 'p.name as nama_perusahaan');
        $querydb->join('invoice as inv', 'inv.id', 'invoice_retur_revisi.invoice_id');
        $querydb->join('invoice_detail as inv_detail', 'inv_detail.id', 'invoice_retur_revisi.invoice_detail_id');
        $querydb->join('perusahaan as p', 'p.id', 'inv.perusahaan_id');
        $querydb->join('member as m', 'm.id', 'inv.member_id');




        // $querydb->orderBy('invoice_retur_revisi.nomor_retur_revisi', 'DESC');

        if ($filter_perusahaan != "") {
            $querydb->where('inv.perusahaan_id', $filter_perusahaan);
        }

        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }


        if ($filter_no_retur != "") {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_no_retur}%");
        }

        if ($filter_jenis_transaksi == "") {
            $querydb->whereRaw('qty_before != qty_change');
            $querydb->orWhereRaw('price_before != price_change');
            if ($filter_perusahaan != "") {
                $querydb->where('inv.perusahaan_id', $filter_perusahaan);
            }
            if ($filter_no_retur != "") {
                $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_no_retur}%");
            }
            if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
            }
        } else {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_jenis_transaksi}%");
            if ($filter_jenis_transaksi == 'RET') {
                $querydb->whereRaw('qty_before != qty_change');
            } else if ($filter_jenis_transaksi == 'REV') {
                $querydb->whereRaw('price_before != price_change');
            }
        }

        $data = $querydb->groupBy('nomor_retur_revisi')->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $data_produk_info = [];
            $data_keterangan_perubahan = [];
            // untuk mendapatkan data barang apa aja yang di retur/revisi dari spesifik invoice
            $retur_revisi_data = InvoiceReturRevisi::where([
                'nomor_retur_revisi' => $value->nomor_retur_revisi,
                'invoice_id' => $value->invoice_id
            ])->get();

            foreach ($retur_revisi_data as $key => $item_data) {
                if ($item_data->qty_change == $item_data->qty_before && $item_data->price_change == $item_data->price_before) {
                    continue;
                }
                $invoice_detail = InvoiceDetail::find($item_data->invoice_detail_id);
                $j              = explode("/", $item_data->nomor_retur_revisi);
                if ($j[2] == "RET") {
                    if ($item_data->qty_before == $item_data->qty_change) {
                        $status = "Tidak ada Perubahan Qty";
                    } else {
                        $status = "Perubahan Qty : " . $item_data->qty_before . " menjadi " . $item_data->qty_change . "";
                    }
                } else if ($j[2] == "REV") {
                    if ($item_data->price_before == $item_data->price_change) {
                        $status = "Tidak ada Perubahan Harga Satuan";
                    } else {
                        $status = "Perubahan Harga : " . number_format($item_data->price_before, 0, ',', '.') . " menjadi " . number_format($item_data->price_change, 0, ',', '.') . "";
                    }
                } else {
                    $status = "";
                }
                $data_produk = $invoice_detail->product_code . ' - ' . $invoice_detail->product_name;
                array_push($data_produk_info, $data_produk);
                array_push($data_keterangan_perubahan, $status);
            }

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->noretur           = $value->nomor_retur_revisi;
            $value->no_inv            = $value->no_nota;
            $value->member_name       = $value->nama_member;

            $value->nama_kota   = $value->nama_kota;

            $value->produk_info       = $data_produk_info;
            $value->ket               = $data_keterangan_perubahan;
            $value->tgl               = date('d/m/Y', strtotime($value->dateorder));
        }

        return view('backend/report/retur_revisi/index_returrevisi_print', compact('data', 'perusahaan', 'filter_perusahaan', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function pdf(Request $request)
    {

        $filter_perusahaan      = session('filter_perusahaan');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $filter_no_retur        = session('filter_no_retur');
        $filter_jenis_transaksi = session('filter_jenis_transaksi');

        $perusahaan = Perusahaan::find($filter_perusahaan);

        $querydb = InvoiceReturRevisi::select('invoice_retur_revisi.*', 'm.name as nama_member', 'm.city as nama_kota', 'inv.no_nota as no_nota', 'inv_detail.product_name as nama_produk', 'inv_detail.product_code as kode_produk', 'inv_detail.deskripsi as deskripsi', 'inv_detail.qty as qty_ttl', 'inv_detail.price as harga_satuan', 'inv_detail.ttl_price as harga_total', 'inv_detail.satuan as nama_satuan', 'p.name as nama_perusahaan');
        $querydb->join('invoice as inv', 'inv.id', 'invoice_retur_revisi.invoice_id');
        $querydb->join('invoice_detail as inv_detail', 'inv_detail.id', 'invoice_retur_revisi.invoice_detail_id');
        $querydb->join('perusahaan as p', 'p.id', 'inv.perusahaan_id');
        $querydb->join('member as m', 'm.id', 'inv.member_id');

        $querydb->orderBy('invoice_retur_revisi.nomor_retur_revisi', 'DESC');


        if ($filter_tgl_start != "" && $filter_tgl_end != "") {
            $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
            $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
        }

        if ($filter_perusahaan != "") {
            $querydb->where('inv.perusahaan_id', $filter_perusahaan);
        }

        if ($filter_no_retur != "") {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_no_retur}%");
        }

        if ($filter_jenis_transaksi == "") {
            $querydb->whereRaw('qty_before != qty_change');
            $querydb->orWhereRaw('price_before != price_change');
            if ($filter_perusahaan != "") {
                $querydb->where('inv.perusahaan_id', $filter_perusahaan);
            }
            if ($filter_no_retur != "") {
                $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_no_retur}%");
            }
            if ($filter_tgl_start != "" && $filter_tgl_end != "") {
                $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($filter_tgl_start)));
                $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
            }
        } else {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$filter_jenis_transaksi}%");
            if ($filter_jenis_transaksi == 'RET') {
                $querydb->whereRaw('qty_before != qty_change');
            } else if ($filter_jenis_transaksi == 'REV') {
                $querydb->whereRaw('price_before != price_change');
            }
        }

        $data = $querydb->groupBy('nomor_retur_revisi')->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $data_produk_info = [];
            $data_keterangan_perubahan = [];
            // untuk mendapatkan data barang apa aja yang di retur/revisi dari spesifik invoice
            $retur_revisi_data = InvoiceReturRevisi::where([
                'nomor_retur_revisi' => $value->nomor_retur_revisi,
                'invoice_id' => $value->invoice_id
            ])->get();

            foreach ($retur_revisi_data as $key => $item_data) {
                if ($item_data->qty_change == $item_data->qty_before && $item_data->price_change == $item_data->price_before) {
                    continue;
                }

                $invoice_detail = InvoiceDetail::find($item_data->invoice_detail_id);
                $j              = explode("/", $item_data->nomor_retur_revisi);
                if ($j[2] == "RET") {
                    if ($item_data->qty_before == $item_data->qty_change) {
                        $status = "Tidak ada Perubahan Qty";
                    } else {
                        $status = "Perubahan Qty : " . $item_data->qty_before . " menjadi " . $item_data->qty_change . "";
                    }
                } else if ($j[2] == "REV") {
                    if ($item_data->price_before == $item_data->price_change) {
                        $status = "Tidak ada Perubahan Harga Satuan";
                    } else {
                        $status = "Perubahan Harga : " . number_format($item_data->price_before, 0, ',', '.') . " menjadi " . number_format($item_data->price_change, 0, ',', '.') . "";
                    }
                } else {
                    $status = "";
                }
                $data_produk = $invoice_detail->product_code . ' - ' . $invoice_detail->product_name;
                array_push($data_produk_info, $data_produk);
                array_push($data_keterangan_perubahan, $status);
            }

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->noretur           = $value->nomor_retur_revisi;
            $value->no_inv            = $value->no_nota;
            $value->member_name       = $value->nama_member;

            $value->nama_kota         = $value->nama_kota;
            $value->produk_info       = $data_produk_info;
            $value->ket               = $data_keterangan_perubahan;
            $value->tgl               = date('d/m/Y', strtotime($value->dateorder));
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
            'title'                 => 'CETAK REPORT RETUR REVISI',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/retur_revisi/index_returrevisi_pdf', ['data' => $data, 'perusahaan' => $perusahaan, 'filter_perusahaan' => $filter_perusahaan, 'filter_tgl_start'   => $filter_tgl_start, 'filter_tgl_end'   => $filter_tgl_end], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Retur Revisi"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');
        $filter_no_retur        = session('filter_no_retur');
        $filter_jenis_transaksi = session('filter_jenis_transaksi');

        return Excel::download(new ReportReturRevisiExports($filter_perusahaan, $filter_tgl_start, $filter_tgl_end, $filter_no_retur, $filter_jenis_transaksi), 'Report Retur Revisi.xlsx');
    }
}
