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

        $filter_tgl_start = $this->filter_tgl_start;
        $filter_tgl_end = $this->filter_tgl_end;

        $querydb = ReportStock::with(['getperusahaan', 'getinvoice', 'produk_beli', 'getproduct' => function ($query) {
            $query->with('getsatuan');
        }, 'getgudang']);

        $querydb->where('note', 'LIKE', "%{$this->masuk}%");
        $querydb->orderBy('id', 'DESC');

        if ($this->filter_produk != 0) {
            $querydb->where('report_stok_bm_bl.product_id', $this->filter_produk);
        }

        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $querydb->whereDate('report_stok_bm_bl.created_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('report_stok_bm_bl.created_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }
        if ($this->filter_perusahaan != 0) {
            $querydb->where('report_stok_bm_bl.perusahaan_id', $this->filter_perusahaan);
        }
        if ($this->filter_gudang != "") {
            $querydb->whereIn('report_stok_bm_bl.gudang_id', $this->filter_gudang);
        }
        $data = $querydb->get();
        $temp = 0;
        foreach ($data as $key => $value) {

            $action = "";

            if ($value->note == 'Order Barang Masuk') {
                $value->transaction_no = $value->produk_beli->notransaction ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->produk_beli->warehouse_date));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->produk_beli->warehouse_date));
            } else {
                $value->transaction_no    = $value->transaction_no ?? '-';
                $tanggal_sampai = date('d/m/Y', strtotime($value->created_at));
                $convert_tanggal_sampai = date('Y-m-d', strtotime($value->created_at));
            }

            $value->no                = $key + 1;
            $value->id                = $value->id;

            $temp = $temp + $value->stock_input;

            $value->transaction_no    = $value->transaction_no ?? '-';

            $value->nama_produk       = $value->getproduct != null ? $value->getproduct->product_name : '-';

            $value->nama_gudang       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->stockinput        = $value->note == 'retur barang masuk' ? abs($value->stock_input) : $value->stock_input;
            $value->namasatuan        = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
            $value->catatan           = ucfirst($value->note);

            $value->factoryname       = $value->produk_beli != null ? $value->produk_beli->factory_name : '-';
            $value->tgl               = $tanggal_sampai;
            $value->conv_tgl          = $convert_tanggal_sampai;
        }

        $data = $data->filter(function ($item) use ($filter_tgl_start, $filter_tgl_end) {
            return (strtotime($item->conv_tgl) >= strtotime($filter_tgl_start)) && (strtotime($item->conv_tgl) <= strtotime($filter_tgl_end));
        });

        return view('backend/report/barang_masuk/index_barangmasuk_excel', [
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
