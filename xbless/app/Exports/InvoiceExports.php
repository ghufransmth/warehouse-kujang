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
use App\Models\InvoiceSuratJalan;
use App\Models\PurchaseOrderLog;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\InvoiceLog;
use App\Models\Perusahaan;
use App\Models\Provinsi;
use App\Models\Expedisi;
use App\Models\Country;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\Member;
use App\Models\Gudang;
use App\Models\Sales;
use App\Models\City;
use Carbon\Carbon;

class InvoiceExports implements FromView,ShouldAutoSize{

    protected $idinv;

    public function __construct($idinv)
    {

        $this->idinv    = $idinv;
    }

    public function view(): View
    {
        $dt = new Carbon();
        $invoicequery = Invoice::select('invoice.id','invoice.no_nota','invoice.memo','invoice.colly','invoice.purchase_no','invoice.member_id','invoice.sales_id','invoice.perusahaan_id','invoice.discount','invoice.subtotal','invoice.total','invoice.note','invoice.expedisi','invoice.via_expedisi','invoice.created_at','member.id as mid','member.name as mname','member.email as memail','member.address as malamat','member.address_toko as mtoko','member.city as mcity','member.city_id as mcity_id','member.phone as mphone','member.ktp as mktp','member.prov as mprov','sales.id as sid','sales.name as sname','perusahaan.id as pid','perusahaan.name as pname','perusahaan.address as palamat','perusahaan.city as pcity','perusahaan.telephone as pphone','perusahaan.rek_no as prekno','perusahaan.bank_name as pbank','expedisi.id as exid','expedisi.name as exname','expedisi_via.id as viaid','expedisi_via.name as vianame','dateorder');
        $invoicequery->join('member','member.id','invoice.member_id');
        $invoicequery->join('sales','sales.id','invoice.sales_id');
        $invoicequery->join('perusahaan','perusahaan.id','invoice.perusahaan_id');
        $invoicequery->join('expedisi','expedisi.id','invoice.expedisi');
        $invoicequery->leftJoin('expedisi_via','expedisi_via.id','invoice.via_expedisi');
        $invoicequery->where('invoice.id', $this->idinv);
        $invoice =  $invoicequery->first();
        $perusahaan = Perusahaan::find($invoice->perusahaan_id);
        $kodeperusahaan = $perusahaan->kode;

        $discount = round($invoice->subtotal*($invoice->discount/100));
        $afterdiscount = round($invoice->subtotal - $discount);
        $ppn = round($afterdiscount*(10/100));
        $total = $afterdiscount + $ppn;


        $invoice->diskon        = $discount;
        $invoice->harga_diskon  = $afterdiscount;
        $invoice->pajak         = $ppn;
        $invoice->grandtotal    = $total;
        // $invoice->tanggal       = $invoice->created_at->isoFormat('dddd, D MMMM Y');
        $invoice->tanggal       =  $dt->parse($invoice->dateorder)->isoFormat('dddd, D MMMM Y');

        $invoicedetailquery = InvoiceDetail::select('*');
        $invoicedetailquery->where('invoice_id', $invoice->id);
        $invoicedetailquery->where('qty','!=',0);
        $invoicedetail = $invoicedetailquery->get();

        foreach ($invoicedetail as $key => $value) {
            $hargadiskon        = round($value->price*($value->discount/100));
            $value->hargadiskon = $value->price - $hargadiskon;
        }

        $tanggal = $dt->now()->isoFormat('dddd, D MMMM Y');

        return view('backend/invoice/print_invoice/excel', [
            'invoice'       => $invoice,
            'invoicedetail' => $invoicedetail,
            'tanggal'       => $tanggal,
            'perusahaan'    => $perusahaan,
            'kodeperusahaan'=> $kodeperusahaan
        ]);
    }
}
