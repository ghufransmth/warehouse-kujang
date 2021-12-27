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
use App\Models\InvoiceDetail;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\StockAdj;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\ReportStock;
use App\Models\InvoiceReturRevisi;


class ReportReturRevisiExports implements FromView, ShouldAutoSize
{

    protected $filter_perusahaan;
    protected $filter_tgl_start;
    protected $filter_tgl_end;

    protected $filter_no_retur;
    protected $filter_jenis_transaksi;



    public function __construct($filter_perusahaan, $filter_tgl_start, $filter_tgl_end, $filter_no_retur, $filter_jenis_transaksi)
    {

        $this->filter_perusahaan    = $filter_perusahaan;
        $this->filter_tgl_start     = $filter_tgl_start;
        $this->filter_tgl_end       = $filter_tgl_end;
        $this->filter_no_retur      = $filter_no_retur;
        $this->filter_jenis_transaksi  = $filter_jenis_transaksi;
    }

    public function view(): View
    {


        $perusahaan = Perusahaan::find($this->filter_perusahaan);

        $querydb = InvoiceReturRevisi::select('invoice_retur_revisi.*', 'm.name as nama_member', 'm.city as nama_kota', 'inv.no_nota as no_nota', 'inv_detail.product_name as nama_produk', 'inv_detail.product_code as kode_produk', 'inv_detail.deskripsi as deskripsi', 'inv_detail.qty as qty_ttl', 'inv_detail.price as harga_satuan', 'inv_detail.ttl_price as harga_total', 'inv_detail.satuan as nama_satuan', 'p.name as nama_perusahaan');
        $querydb->join('invoice as inv', 'inv.id', 'invoice_retur_revisi.invoice_id');
        $querydb->join('invoice_detail as inv_detail', 'inv_detail.id', 'invoice_retur_revisi.invoice_detail_id');
        $querydb->join('perusahaan as p', 'p.id', 'inv.perusahaan_id');
        $querydb->join('member as m', 'm.id', 'inv.member_id');
        $querydb->orderBy('invoice_retur_revisi.nomor_retur_revisi', 'DESC');




        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }

        if ($this->filter_perusahaan != "") {
            $querydb->where('inv.perusahaan_id', $this->filter_perusahaan);
        }

        if ($this->filter_no_retur != "") {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$this->filter_no_retur}%");
        }

        if ($this->filter_jenis_transaksi == "") {
            $querydb->whereRaw('qty_before != qty_change');
            $querydb->orWhereRaw('price_before != price_change');
            if ($this->filter_perusahaan != "") {
                $querydb->where('inv.perusahaan_id', $this->filter_perusahaan);
            }
            if ($this->filter_no_retur != "") {
                $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$this->filter_no_retur}%");
            }
            if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
                $querydb->whereDate('invoice_retur_revisi.created_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
                $querydb->whereDate('invoice_retur_revisi.created_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
            }
        } else {
            $querydb->where('invoice_retur_revisi.nomor_retur_revisi', 'LIKE', "%{$this->filter_jenis_transaksi}%");
            if ($this->filter_jenis_transaksi == 'RET') {
                $querydb->whereRaw('qty_before != qty_change');
            } else if ($this->filter_jenis_transaksi == 'REV') {
                $querydb->whereRaw('price_before != price_change');
            }
        }

        $data = $querydb->groupBy('nomor_retur_revisi')->get();

        foreach ($data as $key => $value) {
            // $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            // $action = "";
            $data_produk_info = [];
            $data_keterangan_perubahan = [];

            // untuk mendapatkan data barang apa aja yang di retur/revisi dari spesifik invoice
            $retur_revisi_data = InvoiceReturRevisi::where([
                'nomor_retur_revisi' => $value->nomor_retur_revisi,
                'invoice_id' => $value->invoice_id
            ])->get();

            foreach ($retur_revisi_data as $key => $item_data) {
                if ($item_data->qty_before == $item_data->qty_change && $item_data->price_before == $item_data->price_change) {
                    continue;
                }
                $invoice_detail = InvoiceDetail::find($item_data->invoice_detail_id);
                $j              = explode("/", $item_data->nomor_retur_revisi);
                if ($j[2] == "RET") {
                    if ($item_data->qty_before == $item_data->qty_change) {
                        $status = "Tidak ada Perubahan Qty";
                    } else {
                        $status = "Perubahan Qty : " . $item_data->qty_before . " menjadi " . $item_data->qty_change . "";
                    }
                } else if ($j[2] == "REV") {
                    if ($item_data->price_before == $item_data->price_change) {
                        $status = "Tidak ada Perubahan Harga Satuan";
                    } else {
                        $status = "Perubahan Harga : " . number_format($item_data->price_before, 0, ',', '.') . " menjadi " . number_format($item_data->price_change, 0, ',', '.') . "";
                    }
                } else {
                    $status = "";
                }
                $data_produk = $invoice_detail->product_code . ' - ' . $invoice_detail->product_name;
                array_push($data_produk_info, $data_produk);
                array_push($data_keterangan_perubahan, $status);
            }
            $value->no                = $key + 1;
            $value->id                = $value->id;
            $value->noretur           = $value->nomor_retur_revisi;
            $value->no_inv            = $value->no_nota;
            $value->member_name       = $value->nama_member;
            $value->nama_perusahaan   = $value->nama_perusahaan;
            $value->nama_kota         = $value->nama_kota;

            $value->produk_info       = $data_produk_info;
            $value->ket               = $data_keterangan_perubahan;
            $value->tgl               = date('d/m/Y', strtotime($value->dateorder));
        }



        return view('backend/report/retur_revisi/index_returrevisi_excel', [
            'data' => $data,
            'filter_perusahaan'   => $this->filter_perusahaan,
            'perusahaan' => $perusahaan,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end
        ]);
    }
}
