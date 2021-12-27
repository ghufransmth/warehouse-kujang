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

class ReportPOExports implements FromView,ShouldAutoSize
{
    
    protected $perusahaan_id;
    protected $tgl;
    

    public function __construct($perusahaan_id,$tgl)
    {
        $this->perusahaan_id = $perusahaan_id;
        $this->tgl           = $tgl;
    }
 
    public function view(): View
    {
       
        $perusahaan        = Perusahaan::find($this->perusahaan_id);

        $query = PurchaseOrder::select('*');
        $query->where('perusahaan_id',$this->perusahaan_id);
        $query->whereDate('dataorder',$this->tgl);
        $query->where('flag_status',0);
        $data=$query->get();
        foreach($data as $key=> $value)
        {
            if($value->status == 0){
                $status ='BARU';
            }elseif($value->status_gudang == 0){
                $status ='DIPROSES';
            }elseif($value->status_gudang == 1){
                $status ='SELESAI';
            }elseif ($value->status_gudang == 2){
                $status ='DITOLAK';
            }else{
                $status='DIBATALKAN';
            }
            $action = "";
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_member     = $value->getmember?$value->getmember->name:'-';
            $value->kota_member     = $value->getmember?$value->getmember->city:'-';
            $value->status          = $status;
            
        }
        
        return view('backend/report/po/index_po_excel', [
                'data' => $data,
                'perusahaan' => $perusahaan,
                'tgl'  => $this->tgl
        ]);
    }
}
