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
use App\Models\ReportStock;

class HistoryAdjStockExports implements FromView,ShouldAutoSize
{
    
    protected $filter_perusahaan;
    protected $filter_gudang;
    protected $tgl_start;
    protected $tgl_end;

    public function __construct($filter_perusahaan,$filter_gudang,$tgl_start,$tgl_end)
    {
        $this->filter_perusahaan = $filter_perusahaan;
        $this->filter_gudang     = $filter_gudang;
        $this->tgl_start         = $tgl_start;
        $this->tgl_end           = $tgl_end;
    }
 
    public function view(): View
    {
       
        $perusahaan        = Perusahaan::find($this->filter_perusahaan);
        $gudang            = Gudang::find($this->filter_gudang);

        $querydb = StockAdj::select('stock_adj.*','product.product_name','satuan.name');
        $querydb->join('product','product.id','stock_adj.product_id');
        $querydb->join('satuan','product.satuan_id','satuan.id');
        $querydb->orderBy('id','DESC');
        
        if($this->filter_perusahaan != ""){
            $querydb->where('stock_adj.perusahaan_id',$this->filter_perusahaan);
        }else{
            $querydb->where('stock_adj.perusahaan_id',0);
        }
        if($this->filter_gudang != ""){
            $querydb->where('stock_adj.gudang_id',$this->filter_gudang);
        }else{
            $querydb->where('stock_adj.gudang_id',0);
        }
        if($this->tgl_start != "" && $this->tgl_end !=""){
            $querydb->whereDate('stock_adj.created_at','>=',date('Y-m-d',strtotime($this->tgl_start)));
            $querydb->whereDate('stock_adj.created_at','<=',date('Y-m-d',strtotime($this->tgl_end)));
        }
        $data = $querydb->get();
        foreach($data as $key=> $value)
        {
            $value->no              = $key+1;
            $value->id              = $value->id;
            $value->nama_produk     = $value->product_name;
            $value->nama_perusahaan = $value->getperusahaan?$value->getperusahaan->name:'-';
            $value->nama_gudang     = $value->getgudang?$value->getgudang->name:'-';
            $value->stock_lama      = $value->qty_product.' '.$value->name;
            $value->stock_adj       = $value->stock_add.' '.$value->name;
            $value->stock_new       = ($value->qty_product + $value->stock_add).' '.$value->name;
            $value->note            = $value->note;
            $value->tgl_adj         = date('d-m-Y H:i',strtotime($value->created_at));
            $value->created_by      = $value->created_by;
        }
        
        return view('backend/stok/stokadjhistory/excel', [
                'data' => $data,
                'perusahaan' => $perusahaan,
                'gudang' => $gudang,
                'tgl_start' => $this->tgl_start,
                'tgl_end' => $this->tgl_end,
                'tgl' => date('d/m/Y H:s')
        ]);
    }
}
