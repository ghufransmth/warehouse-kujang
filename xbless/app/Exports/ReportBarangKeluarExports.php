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
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\StockAdj;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\ReportStock;

class ReportBarangKeluarExports implements FromView, ShouldAutoSize
{

    protected $view;
    protected $data;
    protected $detail;
    public function __construct($view, $data, $detail_penjualan)
    {
        $this->view        = $view;
        $this->data        = $data;
        $this->detail      = $detail_penjualan;
    }

    public function view(): View
    {

        $data = $this->data;
        $view = $this->view;
        $detail_penjualan = $this->detail;

        return view($view, [
            'penjualan' => $data,
            'detail_penjualan' => $detail_penjualan
        ]);
    }
}
