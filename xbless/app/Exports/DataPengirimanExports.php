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
use App\Models\InvoiceTandaTerima;
use App\Models\InvoiceSuratJalan;
use App\Models\PurchaseOrderLog;
use App\Models\InvoiceDetail;
use App\Models\PurchaseOrder;
use App\Models\ExpedisiVia;
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

class DataPengirimanExports implements FromView,ShouldAutoSize{
    
    protected $idtt;
    
    public function __construct($idtt)
    {
       
        $this->idtt    = $idtt;
    }
 
    public function view(): View
    {
        $dt = new Carbon();
        $tanggal = $dt->now()->isoFormat('D MMM Y');
        $query = InvoiceTandaTerima::select('invoice_tanda_terima.*','perusahaan.id as perusahan_id','perusahaan.name as perusahaan_name','perusahaan.rek_no as perusahaan_rekno','member.id as member_id','member.name as member_name','member.address_toko as member_toko','member.city as member_kota');
        $query->join('perusahaan','invoice_tanda_terima.perusahaan_id','perusahaan.id');
        $query->join('member','invoice_tanda_terima.member_id','member.id');
        $query->where('invoice_tanda_terima.id', $this->idtt);
        $tanda_terima = $query->first();
        $tanda_terima->print_tanggal = $tanggal;
        $grandtotal = InvoiceTandaTerima::where('no_tanda_terima', $tanda_terima->no_tanda_terima)->sum('nilai');
        $tanda_terima->grandtotal = $grandtotal;

        $querydetail = InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder','invoice.expedisi as invoice_expedisi','invoice.via_expedisi as invoice_expedisi_via','invoice.delivery_date as invoice_deliver','invoice.resi_no as invoice_resi');
        $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
        $querydetail->where('invoice_tanda_terima.no_tanda_terima', $tanda_terima->no_tanda_terima);
        
        $detail_tanda_terima = $querydetail->get();
        
        foreach ($detail_tanda_terima as $key => $value) {
            $expedisi = Expedisi::find($value->invoice_expedisi);
            $expedisi_via = ExpedisiVia::find($value->invoice_expedisi_via);

            $value->no = $key+1;
            $value->pertanggal     = date('d M y', strtotime(date($value->dateorder)));
            $value->expedisi        = $expedisi->name;
            if($expedisi_via){
                $value->expedisi_via    = $expedisi_via->name;
            }
            $value->delivery        = date('d M y', strtotime(date($value->invoice_deliver)));
        }

        return view('backend/tandaterima/print_data_pengiriman/excel', [
            'tanda_terima' => $tanda_terima,
            'detail_tanda_terima' => $detail_tanda_terima,
            'tanggal' => $tanggal,
        ]);
    }
}