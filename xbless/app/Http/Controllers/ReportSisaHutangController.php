<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
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
use App\Exports\ReportSisaHutangExports;
use App\Models\PurchaseOrderDetail;
use App\Models\InvoicePayment;
use App\Models\InvoiceTandaTerima;
use App\Models\Payment;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;
use Mpdf\Tag\P;

class ReportSisaHutangController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        $member     = Member::with('getcity:id,name')->get();
        $perusahaan = Perusahaan::all();



        if (session('filter_perusahaan') == "") {
            $selectedperusahaan = '';
        } else {
            $selectedperusahaan = session('filter_perusahaan');
        }

        if (session('filter_member') == "") {
            $selectedmember = '';
        } else {
            $selectedmember = session('filter_member');
        }


        if (session('filter_tgl_start') == "") {
            $filter_tgl_start = date('d-m-Y', strtotime(' - 30 days'));
        } else {
            $filter_tgl_start = session('filter_tgl_start');
        }

        if (session('filter_tgl_end') == "") {
            $filter_tgl_end = date('d-m-Y');
        } else {
            $filter_tgl_end = session('filter_tgl_end');
        }


        return view('backend/report/sisa_hutang/index_sisahutang', compact('selectedmember', 'member', 'perusahaan', 'selectedperusahaan', 'filter_tgl_start', 'filter_tgl_end'));
    }
    public function getData(Request $request)
    {


        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_member', $request->filter_member);
        $request->session()->put('filter_perusahaan', $request->filter_perusahaan);
        $request->session()->put('filter_tgl_start', $request->filter_tgl_start);
        $request->session()->put('filter_tgl_end', $request->filter_tgl_end);

        $data_tanda_terima = [];

        $querydb = Invoice::with(['getTandaTerima' => function ($query) {
            $query->with('invoicePayment');
        }, 'getsales:id,name', 'getMember:id,name,city']);

        if ($request->filter_perusahaan != "") {

            $querydb->where('perusahaan_id', $request->filter_perusahaan);
        }
        if ($request->filter_member != "") {

            $querydb->where('member_id', $request->filter_member);
        }

        $data = $querydb->get();
    
        $total_sisa_pembayaran = 0;
        foreach ($data as $item) {
            $item_data = [];
            $sisa = 0;
            $total = 0;
            if ($item->getTandaTerima == null) {
                $data_invoice = Invoice::where('no_nota', $item->no_nota)
                    ->whereDate('dateorder', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)))
                    ->whereDate('dateorder', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)))->first();

                if (empty($data_invoice) || $data_invoice->total <= 6000) {
                    continue;
                }
                $item_data = [
                    'tanda_terima' => $item->no_nota,
                    'id'           => $item->id,
                    'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                    'sales'        => $item->getsales->name,
                    'sisa'         => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'total'        => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'status'       => '-'
                ];
                $sisa = $item->total;
                array_push($data_tanda_terima, $item_data);
            } else {
                if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                    continue;
                } else {
                    $data_invoice_tanda_terima = InvoiceTandaTerima::with(['getinvoicepayment' => function ($query) use ($request) {
                        $query->with('getPayment:id,name')
                            ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)))
                            ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)));
                    }])->where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                        ->whereDate('create_date', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)))
                        ->whereDate('create_date', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)))
                        ->orderBy('create_date', 'desc')
                        ->first();

                    if (empty($data_invoice_tanda_terima) || $data_invoice_tanda_terima->nilai <= 6000) {
                        continue;
                    }

                    if (count($data_invoice_tanda_terima->getinvoicepayment) == 0) {
                        $check_tanda_terima_desc = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                            ->orderBy('create_date', 'desc')->first();
                        if (strtotime(date('Y-m-d', strtotime($data_invoice_tanda_terima->create_date))) < strtotime(date('Y-m-d', strtotime($check_tanda_terima_desc->create_date)))) {
                            continue;
                        }
                        $sisa = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $status = '-';
                    } else {
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total_sisa = 0;
                        $checkSisa = 0;
                        foreach ($data_invoice_tanda_terima->getinvoicepayment as $key => $item_invoice_payment) {
                            $get_data_status = InvoicePayment::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                                ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($request->filter_tgl_start)))
                                ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($request->filter_tgl_end)))
                                ->orderBy('filter_date', 'desc')
                                ->first();
                            $cetak_label_status = Payment::where('id', $get_data_status->payment_id)->first();
                            $total_sisa += $item_invoice_payment->sudah_dibayar;
                            if ($key == count($data_invoice_tanda_terima->getinvoicepayment) - 1) {
                                $status = $cetak_label_status->name;
                                $checkSisa = $item_invoice_payment->sisa;
                            }
                        }
                        if ($checkSisa <= 6000) {
                            continue;
                        }
                        $sisa = $total - $total_sisa;
                    }

                    if (empty($data_tanda_terima)) {
                        $item_data = [
                            'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                            'id'           => $item->id,
                            'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                            'sales'        => $item->getsales->name,
                            'sisa'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                            'total'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                            'status'       => $status
                        ];
                        array_push($data_tanda_terima, $item_data);
                    } else {
                        if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                            continue;
                        } else {
                            $item_data = [
                                'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                                'id'           => $item->id,
                                'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                                'sales'        => $item->getsales->name,
                                'sisa'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                                'total'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                                'status'       => $status
                            ];
                            array_push($data_tanda_terima, $item_data);
                        }
                    }
                }
            }
            $total_sisa_pembayaran += $sisa;
        }

        $totalData = count($data_tanda_terima);
        $totalFiltered = count($data_tanda_terima);

        if ($start == 0) {
            $data_tanda_terima = array_slice($data_tanda_terima, 0, $limit);
        } else {
            $data_tanda_terima = array_slice($data_tanda_terima, $start, $limit);
        }

        usort($data_tanda_terima, function ($item1, $item2) {
            return $item1['tanda_terima'] <=> $item2['tanda_terima'];
        });

        if ($request->user()->can('reportsisahutang.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data_tanda_terima,
                "sum_qty"         => 'Rp. ' .  number_format($total_sisa_pembayaran, 0, ',', '.')
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
        $filter_member          = session('filter_member');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');


        // $dataArr = [];
        $data_tanda_terima = [];

        $querydb = Invoice::with(['getTandaTerima' => function ($query) {
            $query->with('invoicePayment');
        }, 'getsales:id,name', 'getMember:id,name,city']);

        if ($filter_perusahaan != "") {

            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        if ($filter_member != "") {

            $querydb->where('member_id', $filter_member);
        }

        $data = $querydb->get();

        $total_tagihan_keseluruhan = 0;
        foreach ($data as $item) {
            $item_data = [];
            $sisa = 0;
            $total = 0;
            if ($item->getTandaTerima == null) {
                $data_invoice = Invoice::where('no_nota', $item->no_nota)
                    ->whereDate('dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                    ->whereDate('dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)))->first();

                if (empty($data_invoice) || $data_invoice->total <= 6000) {
                    continue;
                }
                $item_data = [
                    'tanda_terima' => $item->no_nota,
                    'id'           => $item->id,
                    'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                    'sales'        => $item->getsales->name,
                    'sisa_tagihan'         => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'total_tagihan'        => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'status'       => '-'
                ];
                $sisa = $item->total;
                array_push($data_tanda_terima, $item_data);
            } else {
                if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                    continue;
                } else {
                    $data_invoice_tanda_terima = InvoiceTandaTerima::with(['getinvoicepayment' => function ($query) use ($filter_tgl_start, $filter_tgl_end) {
                        $query->with('getPayment:id,name')
                            ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                            ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                    }])->where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                        ->whereDate('create_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                        ->whereDate('create_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)))
                        ->orderBy('create_date', 'desc')
                        ->first();

                    if (empty($data_invoice_tanda_terima) || $data_invoice_tanda_terima->nilai <= 6000) {
                        continue;
                    }

                    if (count($data_invoice_tanda_terima->getinvoicepayment) == 0) {
                        $check_tanda_terima_desc = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                            ->orderBy('create_date', 'desc')->first();
                        if (strtotime(date('Y-m-d', strtotime($data_invoice_tanda_terima->create_date))) < strtotime(date('Y-m-d', strtotime($check_tanda_terima_desc->create_date)))) {
                            continue;
                        }
                        $sisa = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $status = '-';
                    } else {
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total_sisa = 0;
                        $checkSisa = 0;
                        foreach ($data_invoice_tanda_terima->getinvoicepayment as $key => $item_invoice_payment) {
                            $get_data_status = InvoicePayment::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                                ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                                ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)))
                                ->orderBy('filter_date', 'desc')
                                ->first();
                            $cetak_label_status = Payment::where('id', $get_data_status->payment_id)->first();
                            $total_sisa += $item_invoice_payment->sudah_dibayar;
                            if ($key == count($data_invoice_tanda_terima->getinvoicepayment) - 1) {
                                $status = $cetak_label_status->name;
                                $checkSisa = $item_invoice_payment->sisa;
                            }
                        }
                        if ($checkSisa <= 6000) {
                            continue;
                        }
                        $sisa = $total - $total_sisa;
                    }

                    if (empty($data_tanda_terima)) {
                        $item_data = [
                            'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                            'id'           => $item->id,
                            'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                            'sales'        => $item->getsales->name,
                            'sisa_tagihan'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                            'total_tagihan'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                            'status'       => $status
                        ];
                        array_push($data_tanda_terima, $item_data);
                    } else {
                        if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                            continue;
                        } else {
                            $item_data = [
                                'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                                'id'           => $item->id,
                                'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                                'sales'        => $item->getsales->name,
                                'sisa_tagihan'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                                'total_tagihan'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                                'status'       => $status
                            ];
                            array_push($data_tanda_terima, $item_data);
                        }
                    }
                }
            }
            $total_tagihan_keseluruhan += $sisa;
        }

        // sortby ascending by tanda terima
        $dataArr = $data_tanda_terima;

        usort($dataArr, function ($item1, $item2) {
            return $item1['tanda_terima'] <=> $item2['tanda_terima'];
        });

        $perusahaan = Perusahaan::find($filter_perusahaan);

        return view('backend/report/sisa_hutang/index_sisahutang_print', compact('dataArr', 'filter_perusahaan', 'filter_member', 'filter_tgl_start', 'filter_tgl_end', 'perusahaan', 'total_tagihan_keseluruhan'));
    }
    public function pdf(Request $request)
    {
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_member          = session('filter_member');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        $data_tanda_terima = [];
        $querydb = Invoice::with(['getTandaTerima' => function ($query) {
            $query->with('invoicePayment');
        }, 'getsales:id,name', 'getMember:id,name,city']);

        if ($filter_perusahaan != "") {

            $querydb->where('perusahaan_id', $filter_perusahaan);
        }
        if ($filter_member != "") {

            $querydb->where('member_id', $filter_member);
        }

        $data = $querydb->get();

        $total_tagihan_keseluruhan = 0;
        foreach ($data as $item) {
            $item_data = [];
            $sisa = 0;
            $total = 0;
            if ($item->getTandaTerima == null) {
                $data_invoice = Invoice::where('no_nota', $item->no_nota)
                    ->whereDate('dateorder', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                    ->whereDate('dateorder', '<=', date('Y-m-d', strtotime($filter_tgl_end)))->first();

                if (empty($data_invoice) || $data_invoice->total <= 6000) {
                    continue;
                }
                $item_data = [
                    'tanda_terima' => $item->no_nota,
                    'id'           => $item->id,
                    'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                    'sales'        => $item->getsales->name,
                    'sisa_tagihan'         => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'total_tagihan'        => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'status'       => '-'
                ];
                $sisa = $item->total;
                array_push($data_tanda_terima, $item_data);
            } else {
                if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                    continue;
                } else {
                    $data_invoice_tanda_terima = InvoiceTandaTerima::with(['getinvoicepayment' => function ($query) use ($filter_tgl_start, $filter_tgl_end) {
                        $query->with('getPayment:id,name')
                            ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                            ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)));
                    }])->where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                        ->whereDate('create_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                        ->whereDate('create_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)))
                        ->orderBy('create_date', 'desc')
                        ->first();

                    if (empty($data_invoice_tanda_terima) || $data_invoice_tanda_terima->nilai <= 6000) {
                        continue;
                    }

                    if (count($data_invoice_tanda_terima->getinvoicepayment) == 0) {
                        $check_tanda_terima_desc = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                            ->orderBy('create_date', 'desc')->first();

                        if (strtotime(date('Y-m-d', strtotime($data_invoice_tanda_terima->create_date))) < strtotime(date('Y-m-d', strtotime($check_tanda_terima_desc->create_date)))) {
                            continue;
                        }

                        $sisa = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $status = '-';
                    } else {
                        $total = InvoiceTandaTerima::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)->sum('nilai');
                        $total_sisa = 0;
                        $checkSisa = 0;
                        foreach ($data_invoice_tanda_terima->getinvoicepayment as $key => $item_invoice_payment) {
                            $get_data_status = InvoicePayment::where('no_tanda_terima', $item->getTandaTerima->no_tanda_terima)
                                ->whereDate('filter_date', '>=', date('Y-m-d', strtotime($filter_tgl_start)))
                                ->whereDate('filter_date', '<=', date('Y-m-d', strtotime($filter_tgl_end)))
                                ->orderBy('filter_date', 'desc')
                                ->first();
                            $cetak_label_status = Payment::where('id', $get_data_status->payment_id)->first();
                            $total_sisa += $item_invoice_payment->sudah_dibayar;
                            if ($key == count($data_invoice_tanda_terima->getinvoicepayment) - 1) {
                                $status = $cetak_label_status->name;
                                $checkSisa = $item_invoice_payment->sisa;
                            }
                        }
                        if ($checkSisa <= 6000) {
                            continue;
                        }
                        $sisa = $total - $total_sisa;
                    }

                    if (empty($data_tanda_terima)) {
                        $item_data = [
                            'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                            'id'           => $item->id,
                            'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                            'sales'        => $item->getsales->name,
                            'sisa_tagihan'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                            'total_tagihan'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                            'status'       => $status
                        ];
                        array_push($data_tanda_terima, $item_data);
                    } else {
                        if ($this->arrayContainsData($data_tanda_terima, $item->getTandaTerima->no_tanda_terima)) {
                            continue;
                        } else {
                            $item_data = [
                                'tanda_terima' => $item->getTandaTerima->no_tanda_terima,
                                'id'           => $item->id,
                                'member'       => $item->getMember->name . ' (' . $item->getMember->city . ')',
                                'sales'        => $item->getsales->name,
                                'sisa_tagihan'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                                'total_tagihan'        => 'Rp. ' . number_format($total, 0, ',', '.'),
                                'status'       => $status
                            ];
                            array_push($data_tanda_terima, $item_data);
                        }
                    }
                }
            }
            $total_tagihan_keseluruhan += $sisa;
        }

        // sortby ascending by tanda terima
        $dataArr = $data_tanda_terima;

        // sortby ascending by tanda terima
        usort($dataArr, function ($item1, $item2) {
            return $item1['tanda_terima'] <=> $item2['tanda_terima'];
        });

        $perusahaan = Perusahaan::find($filter_perusahaan);

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
            'title'                 => 'Laporan Sisa Hutang',
            'author'                => '',
            'watermark'             => '',
            'show_watermark'        => true,
            'show_watermark_image'  => true,
            'mirrorMargins'         => 1,
            'watermark_font'        => 'sans-serif',
            'display_mode'          => 'default',
        ];
        $pdf = PDF::loadView('backend/report/sisa_hutang/index_sisahutang_pdf', ['data' => $dataArr, 'filter_perusahaan' => $filter_perusahaan, 'filter_member' => $filter_member, 'filter_tgl_start' => $filter_tgl_start,  'filter_tgl_end' => $filter_tgl_end, 'perusahaan' => $perusahaan, 'total_sisa_tagihan' => $total_tagihan_keseluruhan], [], $config);
        ob_get_clean();
        return $pdf->stream('Report Penjualan"' . date('d_m_Y H_i_s') . '".pdf');
    }
    public function excel(Request $request)
    {
        $filter_perusahaan      = session('filter_perusahaan');
        $filter_member         = session('filter_member');
        $filter_tgl_start       = session('filter_tgl_start');
        $filter_tgl_end         = session('filter_tgl_end');

        return Excel::download(new ReportSisaHutangExports($filter_perusahaan, $filter_member, $filter_tgl_start, $filter_tgl_end), 'Report Penjualan.xlsx');
    }

    private function arrayContainsData($myArray, $item)
    {
        foreach ($myArray as $element) {
            if ($element['tanda_terima'] == $item) {
                return true;
            }
        }
        return false;
    }
}
