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
use App\Models\Invoice;
use App\Models\InvoiceDetail;

class ReportPenjualanExports implements FromView, ShouldAutoSize
{

    protected $filter_kategori;
    protected $filter_perusahaan;
    protected $filter_gudang;
    protected $filter_keyword;
    protected $filter_tgl_start;
    protected $filter_tgl_end;


    public function __construct($filter_kategori, $filter_perusahaan, $filter_gudang, $filter_keyword, $filter_tgl_start, $filter_tgl_end)
    {
        $this->filter_kategori  = $filter_kategori;
        $this->filter_perusahaan = $filter_perusahaan;
        $this->filter_gudang    = $filter_gudang;
        $this->filter_keyword   = $filter_keyword;
        $this->filter_tgl_start = $filter_tgl_start;
        $this->filter_tgl_end   = $filter_tgl_end;
    }

    public function view(): View
    {


        $querydb = Invoice::select('invoice_detail.*', 'invoice.dateorder', 'invoice.perusahaan_id', 'product.product_name', 'product.category_id', 'product.product_code', 'product.part_no', 'category_product.cat_name as nama_kategori', DB::raw('SUM(invoice_detail.qty_kirim) as qtykirim'));
        $querydb->join('invoice_detail', 'invoice.id', 'invoice_detail.invoice_id');
        //$querydb->join('product','product.id','invoice_detail.product_id');
        $querydb->join('product', 'product.product_code', 'invoice_detail.product_code');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');


        if ($this->filter_keyword != 0) {
            $fiter = $this->filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name','LIKE',"%{$fiter}%");
            //     $query->orWhere('product_code','LIKE',"%{$fiter}%");
            // });
            $querydb->where('product.id', $fiter);
        }

        if ($this->filter_kategori != "" || $this->filter_kategori != null) {
            $querydb->whereIn('category_id', $this->filter_kategori);
        }

        if ($this->filter_tgl_start != "" &&  $this->filter_tgl_end != "") {
            $querydb->whereDate('invoice.dateorder', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('invoice.dateorder', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }
        if ($this->filter_perusahaan != "") {
            $querydb->where('invoice.perusahaan_id', $this->filter_perusahaan);
        }
        if ($this->filter_gudang != "") {
            $querydb->whereIn('invoice_detail.gudang_id', $this->filter_gudang);
        }

        $querydb->groupBy('invoice_detail.product_code');
        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->code              = $value->product_code;
            $value->nama_kategori     = $value->nama_kategori;
            $value->part_no           = $value->part_no;
            $value->qty               = $value->qtykirim . ' ' . $value->satuan;
        }

        return view('backend/report/penjualan/index_penjualan_excel', [
            'data' => $data,
            'filter_kategori' => $this->filter_kategori,
            'filter_perusahaan' => $this->filter_perusahaan,
            'filter_gudang'   => $this->filter_gudang,
            'filter_keyword' => $this->filter_keyword,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
