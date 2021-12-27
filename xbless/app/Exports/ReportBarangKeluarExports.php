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

    protected $filter_produk;
    protected $filter_perusahaan;
    protected $filter_gudang;
    protected $filter_tgl_start;
    protected $filter_tgl_end;
    protected $masuk;

    public function __construct($filter_produk, $filter_perusahaan, $filter_gudang, $filter_tgl_start, $filter_tgl_end, $masuk)
    {
        $this->filter_produk        = $filter_produk;
        $this->filter_perusahaan    = $filter_perusahaan;
        $this->filter_gudang        = $filter_gudang;
        $this->filter_tgl_start     = $filter_tgl_start;
        $this->filter_tgl_end       = $filter_tgl_end;
        $this->masuk                = $masuk;
    }

    public function view(): View
    {


        $perusahaan = Perusahaan::find($this->filter_perusahaan);

        $querydb =  ReportStock::whereHas('transaction_order_bm_bl', function ($q) {
            $q->where('flag_status', 0);
        })->with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang', 'transaction_detail', 'transaction_order_bm_bl' => function ($query) {
            $query->with('getmember');
        }]);

        $querydb->where('note', 'like', '%' . $this->masuk . '%');

        if ($this->filter_produk != "") {
            $querydb->where('product_id', $this->filter_produk);
        }

        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $querydb->whereDate('updated_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('updated_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }
        if ($this->filter_perusahaan != "") {
            $querydb->where('perusahaan_id', $this->filter_perusahaan);
        }
        if ($this->filter_gudang != "") {
            $querydb->whereIn('gudang_id', $this->filter_gudang);
        }



        $data = $querydb->get();


        foreach ($data as $key => $value) {
            $value->conv_tgl          = date('Y-m-d', strtotime($value->updated_at));
        }

        $data = $data->sortByDesc('conv_tgl')->values();
        return view('backend/report/barang_keluar/index_barangkeluar_excel', [
            'data' => $data,
            'filter_produk' => $this->filter_produk,
            'filter_perusahaan'   => $this->filter_perusahaan,
            'filter_gudang' => $this->filter_gudang,
            'perusahaan' => $perusahaan,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
