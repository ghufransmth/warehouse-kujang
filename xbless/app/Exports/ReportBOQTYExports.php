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

class ReportBOQTYExports implements FromView, ShouldAutoSize
{

    protected $filter_kategori;
    protected $filter_status;
    protected $filter_keyword;
    protected $filter_tgl_start;
    protected $filter_tgl_end;


    public function __construct($filter_kategori, $filter_status, $filter_keyword, $filter_tgl_start, $filter_tgl_end)
    {
        $this->filter_kategori  = $filter_kategori;
        $this->filter_status    = $filter_status;
        $this->filter_keyword   = $filter_keyword;
        $this->filter_tgl_start = $filter_tgl_start;
        $this->filter_tgl_end   = $filter_tgl_end;
    }

    public function view(): View
    {


        // $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*',DB::raw('SUM(qty) as qtysum'),'member.name as nama_member','member.uniq_code as uniq_code_member','member.city as kota_member','product.product_name','product.product_code','product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan','transaction_purchase.dataorder','transaction_purchase.no_nota','product.category_id','transaction_purchase.kode_rpo','category_product.cat_name as nama_kategori');
        $querydb = PurchaseOrderDetail::select('transaction_purchase_detail.*', DB::raw('SUM(qty) as qtysum'), 'member.name as nama_member', 'member.uniq_code as uniq_code_member', 'member.city as kota_member', 'product.product_name', 'product.product_code', 'product.product_desc', 'product.normal_price as harga_satuan', 'satuan.name as nama_satuan', 'transaction_purchase.dataorder', 'transaction_purchase.no_nota', 'product.category_id', 'transaction_purchase.kode_rpo', 'category_product.cat_name as nama_kategori', 'transaction_purchase.flag_status', 'transaction_purchase_detail.discount');
        $querydb->join('transaction_purchase', 'transaction_purchase.id', 'transaction_purchase_detail.transaction_purchase_id');
        $querydb->join('product', 'product.id', 'transaction_purchase_detail.product_id');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');
        $querydb->join('satuan', 'satuan.id', 'product.satuan_id');
        $querydb->join('member', 'member.id', 'transaction_purchase.member_id');
        $querydb->join('sales', 'sales.id', 'transaction_purchase.sales_id');
        // $querydb->where('transaction_purchase.status','!=',2);
        // $querydb->where('transaction_purchase.flag_status',2);
        $querydb->where('transaction_purchase.status', '!=', 2);
        $querydb->where('transaction_purchase.flag_status', '!=', 1);
        $querydb->where('kode_rpo', 'LIKE', '%BO-%');
        $querydb->orderBy('id', 'DESC');

        if ($this->filter_status != "" || $this->filter_status != "99") {
            // $text = 'BO';
            // $querydb->where('kode_rpo','LIKE',"%{$text}%");
            if ($this->filter_status == 1) { // filter status belum terkirim
                $querydb->where('transaction_purchase.flag_status', 2);
            } else if ($this->filter_status == 2) { // filter status terkirim
                $querydb->where('transaction_purchase.flag_status', 0);
            }
        }
        if ($this->filter_keyword != 0) {
            // $fiter = $this->filter_keyword;
            // $querydb->where(function ($query) use ($fiter) {
            //     $query->orWhere('product_name', 'LIKE', "%{$fiter}%");
            //     $query->orWhere('product_code', 'LIKE', "%{$fiter}%");
            // });
            $querydb->where('product.id', $this->filter_keyword);
        }

        if ($this->filter_kategori != "" || $this->filter_kategori != null) {
            $querydb->whereIn('category_id', $this->filter_kategori);
        }

        if ($this->filter_tgl_start != "" &&  $this->filter_tgl_end != "") {
            $querydb->whereDate('dataorder', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('dataorder', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }
        $querydb->groupBy('transaction_purchase_detail.product_id', 'dataorder');
        $data = $querydb->get();

        foreach ($data as $key => $value) {
            // $value->no                = $key+1;
            // $value->id                = $value->id;
            // $value->nama_toko         = '('.$value->uniq_code_member.')'.' '.$value->nama_member;
            // $value->kota_member       = $value->kota_member;
            // $value->nama_produk       = $value->product_name;
            // $value->nama_kategori     = $value->nama_kategori;
            // $value->qtybo             = $value->qty.' '.$value->nama_satuan;
            // $value->qty_sum           = $value->qtysum.' '.$value->nama_satuan;
            // $value->harga             = number_format($value->price,0,',','.');
            // $value->ttl_harga         = number_format($value->ttl_price,0,',','.');
            // $value->no_transaksi      = $value->no_nota==null?$value->kode_rpo:$value->no_nota;
            // $value->status            = $value->kode_rpo==null?'Belum Terkirim':'Terkirim';
            // $value->tgl               = date("d-m-Y",strtotime($value->dataorder));
            // $value->created_by        = $value->created_by;
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->nama_toko         = '(' . $value->uniq_code_member . ')' . ' ' . $value->nama_member;
            $value->kota_member       = $value->kota_member;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->nama_kategori;
            $value->qtybo             = $value->qty . ' ' . $value->nama_satuan;
            $value->qty_sum           = $value->qtysum . ' ' . $value->nama_satuan;
            // $value->harga             = number_format($value->price, 0, ',', '.');
            $value->harga             = number_format(($value->price - ($value->price * ($value->discount / 100))), 0, ',', '.');
            $value->ttl_harga         = number_format($value->ttl_price, 0, ',', '.');
            // $value->no_transaksi      = $value->no_nota == null ? $value->kode_rpo : $value->no_nota;
            // $value->status            = $value->kode_rpo == null ? 'Belum Terkirim' : 'Terkirim';
            $value->no_transaksi      = $value->flag_status == 0 ? $value->no_nota : $value->kode_rpo;
            $value->status            = $value->flag_status == 0 ? 'Terkirim' : 'Belum Terkirim';
            $value->tgl               = date("d-m-Y", strtotime($value->dataorder));
            $value->created_by        = $value->created_by;
        }

        return view('backend/report/qty_back_order/index_bo_qty_excel', [
            'data' => $data,
            'filter_kategori' => $this->filter_kategori,
            'filter_status'   => $this->filter_status,
            'filter_keyword' => $this->filter_keyword,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
