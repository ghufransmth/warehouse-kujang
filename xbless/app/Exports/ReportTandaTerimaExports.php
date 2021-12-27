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
use App\Models\InvoiceDetail;
use App\Models\InvoiceTandaTerima;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\StockAdj;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\ReportStock;
use App\Models\InvoiceReturRevisi;


class ReportTandaTerimaExports implements FromView, ShouldAutoSize
{

    protected $filter_perusahaan;
    protected $filter_sales;
    protected $filter_kota;

    protected $filter_tgl_start;
    protected $filter_tgl_end;
    protected $filter_member;




    public function __construct($filter_perusahaan, $filter_sales, $filter_kota, $filter_tgl_start, $filter_tgl_end, $filter_member)
    {

        $this->filter_perusahaan    = $filter_perusahaan;
        $this->filter_sales         = $filter_sales;
        $this->filter_kota          = $filter_kota;
        $this->filter_tgl_start     = $filter_tgl_start;
        $this->filter_tgl_end       = $filter_tgl_end;
        $this->filter_member        = $filter_member;
    }

    public function view(): View
    {


        $perusahaan = Perusahaan::find($this->filter_perusahaan);

        $querydb = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.uniq_code as kodemember');
        $querydb->join('member', 'member.id', 'invoice.member_id');
        $querydb->where('invoice.flag_tanda_terima', 1);

        if (in_array(0, $this->filter_sales) == false) {

            $querydb->whereIn('invoice.sales_id', $this->filter_sales);
        }

        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }

        if ($this->filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $this->filter_perusahaan);
        }

        if (in_array(0, $this->filter_kota) == false) {

            $querydb->whereIn('member.city_id', $this->filter_kota);
        }

        if ($this->filter_member != null) {
            $querydb->where('invoice.member_id', $this->filter_member);
        }

        $querydb->orderBy('nama_member', 'asc');
        $querydb->orderBy('invoice.dateorder', 'asc');
        $querydb->orderBy('member.city', 'asc');
        $querydb->groupBy('invoice.member_id');

        $data = $querydb->get();

        foreach ($data as $key => $value) {
            // $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            // $action = "";

            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->member_kota       = $value->kota;
            $value->tgl                 = date('F Y', strtotime($value->dateorder));
            $value->member_lengkap      = '(' . $value->kodemember . ')' . ' ' . $value->nama_member . '-' . $value->kota;

            $detail = Invoice::select('invoice.*', 'member.name as nama_member', 'member.city as kota', 'member.id as id_member');
            $detail->join('member', 'member.id', 'invoice.member_id');
            $detail->where('invoice.flag_tanda_terima', 1);
            $detail->where('invoice.member_id', $value->member_id);

            if (in_array(0, $this->filter_sales) == false) {
                $detail->whereIn('invoice.sales_id', $this->filter_sales);
            }

            if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
                $detail->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
                $detail->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
            }

            if ($this->filter_perusahaan != "") {
                $detail->where('invoice.perusahaan_id', $this->filter_perusahaan);
            }

            if (in_array(0, $this->filter_kota) == false) {
                $detail->whereIn('member.city_id', $this->filter_kota);
            }

            if ($this->filter_member != null) {
                $detail->where('invoice.member_id', $this->filter_member);
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



        return view('backend/report/tanda_terima/index_tandaterima_excel', [
            'data' => $data,
            'filter_perusahaan'   => $this->filter_perusahaan,
            'perusahaan' => $perusahaan,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
