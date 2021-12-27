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
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\StockAdj;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\ReportStock;

class ReportInvoiceExports implements FromView, ShouldAutoSize
{

    protected $filter_perusahaan;
    protected $filter_tgl_start;
    protected $filter_tgl_end;


    public function __construct($filter_perusahaan, $filter_tgl_start, $filter_tgl_end)
    {

        $this->filter_perusahaan    = $filter_perusahaan;
        $this->filter_tgl_start     = $filter_tgl_start;
        $this->filter_tgl_end       = $filter_tgl_end;
    }

    public function view(): View
    {


        $perusahaan = Perusahaan::find($this->filter_perusahaan);
        $querydb = Invoice::select('*');
        $querydb->orderBy('no_nota', 'ASC');





        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $querydb->whereDate('created_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('created_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }
        if ($this->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $this->filter_perusahaan);
        }

        $data = $querydb->get();

        foreach ($data as $key => $value) {

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
            $value->totalppn          = $value->total;
            $value->tgl               = date('d/m/Y', strtotime($value->created_at));
        }

        return view('backend/report/rekap_invoice/index_invoice_excel', [
            'data' => $data,
            'filter_perusahaan'   => $this->filter_perusahaan,
            'perusahaan' => $perusahaan,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
