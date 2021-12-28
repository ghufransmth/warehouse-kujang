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
use App\Models\ProductPerusahaanGudang;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderLog;
use App\Models\PerusahaanGudang;
use App\Models\PurchaseOrder;
use App\Models\ExpedisiVia;
use App\Models\Expedisi;
use App\Models\Perusahaan;
use App\Models\Product;
use App\Models\Member;
use App\Models\Sales;
use Carbon\Carbon;

class RequestPurchaseOrderExports implements FromView,ShouldAutoSize
{

    protected $dec_id;

    public function __construct($dec_id){
        $this->dec_id     = $dec_id;
    }

    public function view(): View{
        $purchase = PurchaseOrder::select('transaction_purchase.id as id', 'transaction_purchase.member_id','transaction_purchase.kode_rpo', 'transaction_purchase.note', 'transaction_purchase.created_at',
                            'transaction_purchase.expedisi','transaction_purchase.expedisi_via','transaction_purchase.perusahaan_id','transaction_purchase.total','transaction_purchase.dataorder',
                            'member.id as mid', 'member.name as mname', 'member.city as mcity', 'member.address as maddress','member.prov',
                            'expedisi.id as exid', 'expedisi.name as exname',
                            'expedisi_via.id as viaid','expedisi_via.name as vianame',
                            'perusahaan.id as perid','perusahaan.name as pername', 'perusahaan.address as peraddress','perusahaan.city as percity',
                            'sales.id as sid','sales.name as sname','sales.code as scode')
                    ->leftJoin('member','member.id','transaction_purchase.member_id')
                    ->leftJoin('sales','sales.id','transaction_purchase.sales_id')
                    ->leftJoin('expedisi', 'expedisi.id', 'transaction_purchase.expedisi')
                    ->leftJoin('perusahaan','perusahaan.id','transaction_purchase.perusahaan_id')
                    ->leftJoin('expedisi_via', 'expedisi_via.id','transaction_purchase.expedisi_via')
                    ->where('transaction_purchase.id', $this->dec_id)
                    ->first();
        $purchasedetail = PurchaseOrderDetail::select('transaction_purchase_detail.id as id','transaction_purchase_detail.qty as qty','transaction_purchase_detail.qty_kirim as qtykirim',
                    'transaction_purchase_detail.satuan as satuan','transaction_purchase_detail.discount as discount','transaction_purchase_detail.price as price',
                    'transaction_purchase_detail.ttl_price as total','transaction_purchase_detail.perusahaan_id','transaction_purchase_detail.gudang_id',
                    'transaction_purchase_detail.product_id', 'transaction_purchase_detail.colly', 'transaction_purchase_detail.weight',
                    'product.id as prid', 'product.product_name as prname', 'product.product_desc as prdesc',
                    'perusahaan.id as pid', 'perusahaan.name as pname',
                    'gudang.id as gid','gudang.name')
                ->leftjoin('product', 'product.id','transaction_purchase_detail.product_id')
                ->leftjoin('gudang','gudang.id', 'transaction_purchase_detail.gudang_id')
                ->leftjoin('perusahaan','perusahaan.id','transaction_purchase_detail.perusahaan_id')
                ->where('transaction_purchase_detail.transaction_purchase_id', $this->dec_id)
                ->get();
        $totalharga = 0;
        foreach ($purchasedetail as $key => $value) {
            $value->no  = $key+1;
            $hargadiskon    = ($value->qty * $value->price) * ($value->discount/100);
            $harganon       = $value->qty * $value->price;
            $unitnon        = $value->price * ($value->discount/100);
            $value->totalsebelum     = round($harganon);
            $value->totalsesudah     = round($harganon - $hargadiskon);
            $value->unitsesudah      = round($value->price - $unitnon);
            $totalharga += $value->totalsesudah;
        }
        
        $dt = new Carbon();
        $printoleh = auth()->user()->username.", ".$dt->toDateTimeString();
        
        return view('backend/requestpurchase/excel', [
                'idpo' => $this->dec_id,
                'purchase' => $purchase,
                'purchasedetail' => $purchasedetail,
                'printoleh' => $printoleh,
                'totalharga' => $totalharga
        ]);
    }
}
