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
        return view('backend/report/keuangan/index_keuangan');
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $beli = $this->get_beli($limit, $request->periode_start, $request->periode_end);
        $jual = $this->get_jual($limit, $request->periode_start, $request->periode_end);
        $retur = $this->get_retur($limit, $request->periode_start, $request->periode_end);

        $result = [];
        foreach ($beli as $key => $value) {
            $value->sumber  = 0;
            $result[]   = $value;
        }

        foreach ($jual as $key => $value) {
            $value->sumber  = 1;
            $result[]   = $value;
        }

        foreach ($retur as $key => $value) {
            $value->sumber  = 2;
            $result[]   = $value;
        }

        $data = [];
        $no = 0;
        foreach ($result as $key => $value) {
            $no += 1;
            $data[$key]['no']   = $no;
            if($value->tgl_faktur){
                $data[$key]['tgl_faktur']   = date('d-m-Y', strtotime($value->tgl_faktur));
            }else if($value->tgl_retur){
                $data[$key]['tgl_faktur']   = date('d-m-Y', strtotime($value->tgl_retur));
            }
            $data[$key]['no_faktur']   = $value->no_faktur;
            $data[$key]['tgl_kirim']   = 'menunggu';
            if($value->id_toko){
                $data[$key]['toko']   = $value->toko;
            }else{
                $data[$key]['toko']   = '';
            }
            if($value->id_sales){
                $data[$key]['sales']   = $value->sales;
            }else{
                $data[$key]['sales']   = '';
            }
            if($value->tgl_jatuh_tempo){
                if($value->tgl_jatuh_tempo != NULL){
                    $jatuh = $value->tgl_jatuh_tempo;
                }else{
                    $jatuh = '';
                }
                $data[$key]['jatuh_tempo']   = $jatuh;
            }else{
                $data[$key]['jatuh_tempo']   = '';
            }

            if($value->status_lunas){
                if($value->status_lunas == 1){
                    $lunas = 'Lunas';
                }else{
                    $lunas = 'Belum Lunas';
                }
            }else if($value->status_pembelian){
                if($value->status_pembelian == 1){
                    $lunas = 'Lunas';
                }else{
                    $lunas = 'Belum Lunas';
                }
            }else{
                $lunas = '';
            }
            if($value->sumber == 0){
                $sumber = 'Penjualan';
            }else if($value->sumber == 1){
                $sumber = 'Pembelian';
            }else if($value->sumber == 2){
                if($value->jenis_transaksi == 0){
                    $sumber = 'Retur Penjualan';
                }else if($value->jenis_transaksi == 1){
                    $sumber = 'Retur Pembelian';
                }
            }
            $data[$key]['lunas']    = $lunas;
            $data[$key]['sumber']   = $sumber;
            $data[$key]['total']    = number_format($value->total_harga,2,',','.');
            $data[$key]['action']   = '';
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval(count($data)),
            "recordsFiltered" => intval(count($data)),
            "data"            => $data
        );
  
        return response()->json($json_data);
    }

    private function get_beli($limit, $start, $end){
        $query = Pembelian::select('pembelian.*');
        $query->whereDate('tgl_faktur', '>=',date('Y-m-d', strtotime($start)));
        $query->whereDate('tgl_faktur', '<=',date('Y-m-d', strtotime($end)));

        $query->limit($limit);
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

        $query->limit($limit);
        $jual = $query->get();

        return $jual;
    }

    private function get_retur($limit, $start, $end){
        $query = ReturTransaksi::select('*');
        $query->whereDate('tgl_retur', '>=',date('Y-m-d', strtotime($start)));
        $query->whereDate('tgl_retur', '<=',date('Y-m-d', strtotime($end)));

        $query->limit($limit);
        $retur = $query->get();

        return $retur;
    }
}
