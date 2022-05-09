<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Facades\LengthAwarePaginator;

use App\Models\Gudang;
use App\Models\Kategori;
use App\Models\Product;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Penjualan;
use App\Models\DetailPenjualan;
use App\Models\ReturTransaksi;

use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportKeuanganController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    function safe_encode($string)
    {
        $data = str_replace(array('/'), array('_'), $string);
        return $data;
    }

    function safe_decode($string, $mode = null)
    {
        $data = str_replace(array('_'), array('/'), $string);
        return $data;
    }

    public function index()
    {
        $periode_start = date('d-m-Y', strtotime("-1 Month"));
        $periode_end = date('d-m-Y');

        return view('backend/report/keuangan/index_keuangan', compact('periode_start', 'periode_end'));
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        if($request->periode_start != ''&& $request->periode_end != ''){
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        }else{
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $query = Penjualan::select('tbl_penjualan.id','tbl_penjualan.no_faktur','tbl_penjualan.tgl_jatuh_tempo','tbl_penjualan.tgl_faktur','tbl_penjualan.jenis_pembayaran','tbl_penjualan.status_lunas','tbl_penjualan.total_harga','tbl_penjualan.total_diskon','tbl_sales.nama as sales_name','toko.name as toko_name');
        $query->leftJoin('tbl_sales','tbl_sales.id','tbl_penjualan.id_sales');
        $query->leftJoin('toko','toko.id','tbl_penjualan.id_toko');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $query->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
          $query->orderBy('id','DESC');
        }
        if($search) {
            $query->where(function ($query) use ($search) {
                $query->orWhere('tbl_penjualan.no_faktur','LIKE',"%{$search}%");
                $query->orWhere('sales.nama','LIKE',"%{$search}%");
                $query->orWhere('toko.name','LIKE',"%{$search}%");
            });
        }

        if($request->sales != ''){
            $query->where('id_sales', $request->sales);
        }

        $query->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $query->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $query->get()->count();

        $totalFiltered = $query->get()->count();

        $query->limit($limit);
        $query->offset($start);
        $data = $query->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";

            $action.="</div>";

            if($result->status_lunas == 0){
                $status = '<span class="label label-danger">Belum Lunas</span>';
            }else if($result->status_lunas == 1){
                $status = '<span class="label label-primary">Lunas</span>';
            }

            if($result->jenis_pembayaran == 1){
                $bayar = '<span class="label label-success">Cash</span>';
            }else if($result->jenis_pembayaran == 2){
                $bayar = '<span class="label label-success">Ceck / Giro</span>';
            }else if($result->jenis_pembayaran == 3){
                $bayar = '<span class="label label-success">Transfer</span>';
            }else{
                $bayar = '<span class="label label-danger">Pending</span>';
            }

            $retur  = $this->check_retur($result->no_faktur);
            if($retur){
                $total_retur = ($retur->total_harga != NULL) ? $retur->total_harga : 0;
                $total  = ($result->total_harga + $result->total_diskon) - $total_retur;
                $note   = 'Transaksi dengan nomor Faktur '.$result->no_faktur.' mengalami retur transaksi dengan nilai Rp. '.number_format($total_retur, 2,',','.');
            }else{
                $total  = $result->total_harga + $result->total_diskon;
                $note   = '';
            }

            $result->no             = $key+$page;
            $result->tanggal_faktur = date('d F Y', strtotime($result->tgl_faktur));
            $result->tanggal_tempo  = date('d F Y', strtotime($result->tgl_jatuh_tempo));
            $result->tanggal_kirim  = date('d F Y');
            $result->status         = $status;
            $result->cara_bayar     = $bayar;
            $result->total          = number_format($total,2,',','.');
            $result->note           = $note;
            $result->action         = $action;
        }


        if($request->user()->can('reportkeuangan.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
        }else{
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => []
            );
        }
        return json_encode($json_data);
    }

    private function check_retur($no_faktur){
        $retur  = ReturTransaksi::where('no_faktur', $no_faktur)->first();

        return $retur;
    }

    private function get_beli($limit, $start, $end){
        $query = Pembelian::select('pembelian.*');
        $query->whereDate('tgl_faktur', '>=',date('Y-m-d', strtotime($start)));
        $query->whereDate('tgl_faktur', '<=',date('Y-m-d', strtotime($end)));

        if($limit != null){
            $query->limit($limit);
        }
        $beli = $query->get();

        foreach ($beli as $key => $value) {
            $count = PembelianDetail::where('pembelian_id', $value->id)->sum('total');
            $value->total_harga = $count;
        }

        return $beli;
    }

    private function get_jual($limit, $start, $end){
        $query = Penjualan::select('tbl_penjualan.*', 'toko.name as toko', 'tbl_sales.nama as sales')
        ->leftJoin('toko','toko.id','tbl_penjualan.id_toko')->leftJoin('tbl_sales','tbl_sales.id','tbl_penjualan.id_sales');
        $query->whereDate('tgl_faktur', '>=',date('Y-m-d', strtotime($start)));
        $query->whereDate('tgl_faktur', '<=',date('Y-m-d', strtotime($end)));

        if($limit != null){
            $query->limit($limit);
        }
        $jual = $query->get();

        return $jual;
    }

    private function get_retur($limit, $start, $end){
        $query = ReturTransaksi::select('*');
        $query->whereDate('tgl_retur', '>=',date('Y-m-d', strtotime($start)));
        $query->whereDate('tgl_retur', '<=',date('Y-m-d', strtotime($end)));

        if($limit != null){
            $query->limit($limit);
        }
        $retur = $query->get();

        return $retur;
    }

    public function print(Request $request, $start, $end){
        $beli = $this->get_beli(null, $start, $end);
        $jual = $this->get_jual(null, $start, $end);
        $retur = $this->get_retur(null, $start, $end);

        // return view('backend/report/keuangan/index_keuangan_print');
        return response()->json([
            'beli'  => $beli,
            'jual'  => $jual,
            'retur' => $retur
        ]);

        // $result = [];
        // foreach ($beli as $key => $value) {
        //     $value->sumber  = 0;
        //     $result[]   = $value;
        // }

        // foreach ($jual as $key => $value) {
        //     $value->sumber  = 1;
        //     $result[]   = $value;
        // }

        // foreach ($retur as $key => $value) {
        //     $value->sumber  = 2;
        //     $result[]   = $value;
        // }

        // $data = [];
        // $no = 0;
        // foreach ($result as $key => $value) {
        //     $no += 1;
        //     $data[$key]['no']   = $no;
        //     if($value->tgl_faktur){
        //         $data[$key]['tgl_faktur']   = date('d-m-Y', strtotime($value->tgl_faktur));
        //     }else if($value->tgl_retur){
        //         $data[$key]['tgl_faktur']   = date('d-m-Y', strtotime($value->tgl_retur));
        //     }
        //     $data[$key]['no_faktur']   = $value->no_faktur;
        //     $data[$key]['tgl_kirim']   = 'menunggu';
        //     if($value->id_toko){
        //         $data[$key]['toko']   = $value->toko;
        //     }else{
        //         $data[$key]['toko']   = '';
        //     }
        //     if($value->id_sales){
        //         $data[$key]['sales']   = $value->sales;
        //     }else{
        //         $data[$key]['sales']   = '';
        //     }
        //     if($value->tgl_jatuh_tempo){
        //         if($value->tgl_jatuh_tempo != NULL){
        //             $jatuh = $value->tgl_jatuh_tempo;
        //         }else{
        //             $jatuh = '';
        //         }
        //         $data[$key]['jatuh_tempo']   = $jatuh;
        //     }else{
        //         $data[$key]['jatuh_tempo']   = '';
        //     }

        //     if($value->status_lunas){
        //         if($value->status_lunas == 1){
        //             $lunas = 'Lunas';
        //         }else{
        //             $lunas = 'Belum Lunas';
        //         }
        //     }else if($value->status_pembelian){
        //         if($value->status_pembelian == 1){
        //             $lunas = 'Lunas';
        //         }else{
        //             $lunas = 'Belum Lunas';
        //         }
        //     }else{
        //         $lunas = '';
        //     }
        //     if($value->sumber == 0){
        //         $sumber = 'Penjualan';
        //     }else if($value->sumber == 1){
        //         $sumber = 'Pembelian';
        //     }else if($value->sumber == 2){
        //         if($value->jenis_transaksi == 0){
        //             $sumber = 'Retur Penjualan';
        //         }else if($value->jenis_transaksi == 1){
        //             $sumber = 'Retur Pembelian';
        //         }
        //     }
        //     $data[$key]['lunas']    = $lunas;
        //     $data[$key]['sumber']   = $sumber;
        //     $data[$key]['total']    = number_format($value->total_harga,2,',','.');
        // }


    }
}
