<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoiceTandaTerima;
use App\Models\Payment;
use App\Models\Perusahaan;

class ReportSisaHutangExports implements FromView, ShouldAutoSize
{
    protected $filter_perusahaan;
    protected $filter_member;
    protected $filter_tgl_start;
    protected $filter_tgl_end;


    public function __construct($filter_perusahaan, $filter_member, $filter_tgl_start, $filter_tgl_end)
    {
        $this->filter_perusahaan = $filter_perusahaan;
        $this->filter_member    = $filter_member;
        $this->filter_tgl_start = $filter_tgl_start;
        $this->filter_tgl_end   = $filter_tgl_end;
    }

    public function view(): View
    {

        $querydb = Invoice::with(['getTandaTerima' => function ($query) {
            $query->with('invoicePayment');
        }, 'getsales:id,name', 'getMember:id,name,city']);

        if ($this->filter_perusahaan != "") {

            $querydb->where('perusahaan_id', $this->filter_perusahaan);
        }
        if ($this->filter_member != "") {

            $querydb->where('member_id', $this->filter_member);
        }

        $data = $querydb->get();

        $total_tagihan_keseluruhan = 0;
        $filter_tgl_start = $this->filter_tgl_start;
        $filter_tgl_end = $this->filter_tgl_end;
        $data_tanda_terima = [];
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
                    'sisa_pembayaran'         => 'Rp. ' . number_format($item->total, 0, ',', '.'),
                    'total_pembayaran'        => 'Rp. ' . number_format($item->total, 0, ',', '.'),
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
                            'sisa_pembayaran'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                            'total_pembayaran'        => 'Rp. ' . number_format($total, 0, ',', '.'),
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
                                'sisa_pembayaran'         => 'Rp. ' . number_format($sisa, 0, ',', '.'),
                                'total_pembayaran'        => 'Rp. ' . number_format($total, 0, ',', '.'),
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

        $perusahaan = Perusahaan::find($this->filter_perusahaan);
        return view('backend/report/sisa_hutang/index_sisahutang_excel', [
            'data' => $dataArr,
            'filter_perusahaan'  => $this->filter_perusahaan,
            'filter_member'      => $this->filter_member,
            'filter_tgl_start'   => $this->filter_tgl_start,
            'filter_tgl_end'     => $this->filter_tgl_end,
            'perusahaan'         => $perusahaan,
            'total_sisa_tagihan' => $total_tagihan_keseluruhan
        ]);
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
