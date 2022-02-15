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

class ReportBarangMasukExports implements FromView, ShouldAutoSize
{

    protected $filter_produk;
    protected $filter_perusahaan;
    protected $filter_gudang;
    protected $filter_tgl_start;
    protected $filter_tgl_end;
    protected $masuk;

    public function __construct($view, $data, $detail_pembelian)
    {
        $this->view        = $view;
        $this->data        = $data;
        $this->detail      = $detail_pembelian;
    }

    public function view(): View
   {
    $data = $this->data;
    $view = $this->view;
    $detail_pembelian = $this->detail;

    return view($view, [
        'pembelian' => $data,
        'detail_pembelian' => $detail_pembelian
    ]);
   }
}
