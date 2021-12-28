<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\ProductBeli;
use App\Models\ProductBeliDetail;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Exports\HistoryMutasiStockExports;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Auth;
use PDF;
use Excel;
use Carbon\Carbon;

class StokAdminController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function flag_barang_masuk()
    {
        return ['Order Barang Masuk', 'Mutasi Masuk', 'retur barang masuk'];
    }

    public function flag_barang_keluar()
    {
        return ['Purchase Keluar', 'Mutasi Keluar'];
    }

    public function index()
    {
        $gudang     = Gudang::all();
        $perusahaan = Perusahaan::all();

        if (session('filter_perusahaan_admin') == "") {
            $selectedperusahaan = "";
        } else {
            $selectedperusahaan = session('filter_perusahaan_admin');
        }

        if (session('filter_gudang_admin') == "") {
            $selectedgudang = '';
        } else {
            $selectedgudang = session('filter_gudang_admin');
        }
        return view('backend/stok/stokadmin/index', compact('gudang', 'selectedgudang', 'perusahaan', 'selectedperusahaan'));
    }
    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $request->session()->put('filter_perusahaan_admin', $request->filter_perusahaan_admin);
        $request->session()->put('filter_gudang_admin', $request->filter_gudang_admin);

        if ($request->filter_perusahaan_admin != "" && $request->filter_gudang_admin != "") {
            $cek_perusahaan_gudang_id = PerusahaanGudang::where('perusahaan_id', $request->filter_perusahaan_admin)->where('gudang_id', $request->filter_gudang_admin)->first();
            if ($cek_perusahaan_gudang_id) {
                $perusahaan_gudang_id = $cek_perusahaan_gudang_id->id;
            } else {
                $perusahaan_gudang_id = 0;
            }
        } else {
            $perusahaan_gudang_id = 0;
        }
        $querydb = Product::select('product.*', 'satuan.name as satuan_name', 'product_perusahaan_gudang.stok', 'category_product.cat_name');
        $querydb->join('product_perusahaan_gudang', 'product_perusahaan_gudang.product_id', 'product.id');
        $querydb->join('satuan', 'product.satuan_id', 'satuan.id');
        $querydb->join('category_product', 'category_product.id', 'product.category_id');
        $querydb->where('product_perusahaan_gudang.perusahaan_gudang_id', $perusahaan_gudang_id);

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('id', 'DESC');
        }

        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('product_code', 'LIKE', "%{$search}%");
                $query->orWhere('product_name', 'LIKE', "%{$search}%");
                $query->orWhere('satuan.name', 'LIKE', "%{$search}%");
                $query->orWhere('cat_name', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();

        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $jumlah    = StockAdj::select('stock_add', 'created_at', 'qty_product','stock_admin')
                ->where('product_id', $value->id)
                ->where('perusahaan_id', $request->filter_perusahaan_admin)
                ->where('gudang_id', $request->filter_gudang_admin)
                ->orderBy('id', 'desc')
                ->first();

            $jumlahopname = StockOpnameDetail::select('stock_opname_detail.qtySO', 'stock_opname_detail.created_at','stock_opname_detail.stock_admin')
                ->join('stock_opname','stock_opname.id','stock_opname_detail.so_id')
                ->where('produk_id', $value->id)
                ->where('perusahaan_id', $request->filter_perusahaan_admin)
                ->where('gudang_id', $request->filter_gudang_admin)
                ->where('stock_opname.flag_proses',1)
                ->orderBy('stock_opname_detail.id', 'desc')
                ->first();

            // $nilaiadj    = ReportStock::select('stock_input', 'created_at')
            //     ->where('product_id_shadow', $value->id)
            //     ->where('perusahaan_id', $request->filter_perusahaan_admin)
            //     ->where('gudang_id', $request->filter_gudang_admin)
            //     ->orderBy('id', 'desc')
            //     ->first();


            if ($value->is_liner == 'Y') {

                $datamasuk = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $value->id,
                    'perusahaan_id' => $request->filter_perusahaan_admin,
                    'gudang_id' => $request->filter_gudang_admin
                ])->whereIn('keterangan', $this->flag_barang_masuk());
            } else {

                $datamasuk = ReportStock::with('produk_beli')->where([
                    'product_id' => $value->id,
                    'perusahaan_id' => $request->filter_perusahaan_admin,
                    'gudang_id' => $request->filter_gudang_admin
                ])->whereIn('keterangan', $this->flag_barang_masuk());
            }

            if (!empty($jumlah) && !empty($jumlahopname)) {
                if($jumlah->created_at > $jumlahopname->created_at){
                    $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
                }else{
                    $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
                }
            }else if (!empty($jumlah) || !empty($jumlahopname)) {
                if($jumlah){
                    $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
                }
                if($jumlahopname){
                    $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
                }
                // if($jumlah->created_at > $jumlahopname->created_at){
                //     $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
                // }else{
                //     $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
                // }
            } else {
                $datamasukget = $datamasuk->get();
            }



            $masuk = 0;

            if (count($datamasukget) > 0) {
                foreach ($datamasukget as $k => $nilai) {
                    $produk = Product::find($nilai->product_id);
                    if($produk->is_liner=='Y'){
                        $produksatuan = $produk->satuan_value;
                    }
                    else{
                        $produksatuan = 1;
                    }
                    $qty = $nilai->keterangan == 'retur barang masuk' ? abs($nilai->stock_input) : $nilai->stock_input;
                    if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                        if ($nilai->produk_beli->flag_proses == 1) {
                            $satuan_value_masuk = $qty * $produksatuan;
                        } else {
                            $satuan_value_masuk = 0;
                        }
                    } else {
                        $satuan_value_masuk = $qty * $produksatuan;
                    }

                    $masuk += $satuan_value_masuk;
                }
            }

            if ($value->isLiner = "Y") {

                $datakeluar = ReportStock::with(['transaction_detail' => function ($query) {
                    $query->with('purchase_order');
                }])->where([
                    'product_id_shadow'    => $value->id,
                    'perusahaan_id'        => $request->filter_perusahaan_admin,
                    'gudang_id'            => $request->filter_gudang_admin
                ])->whereIn('keterangan', $this->flag_barang_keluar());
            } else {

                $datakeluar = ReportStock::with(['transaction_detail' => function ($query) {
                    $query->with('purchase_order');
                }])->where([
                    'product_id'    => $value->id,
                    'perusahaan_id'        => $request->filter_perusahaan_admin,
                    'gudang_id'            => $request->filter_gudang_admin
                ])->whereIn('keterangan', $this->flag_barang_keluar());
            }

            // if (!empty($jumlah)|| !empty($jumlahopname)){
            //     if($jumlah->created_at > $jumlahopname->created_at){
            //         $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
            //     }else{
            //         $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
            //     }
            // } else {
            //     $datakeluarget = $datakeluar->get();
            // }


            if (!empty($jumlah) && !empty($jumlahopname)) {
                if($jumlah->created_at > $jumlahopname->created_at){
                    $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
                }else{
                    $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
                }
            }else if (!empty($jumlah) || !empty($jumlahopname)) {
                if($jumlah){
                    $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
                }
                if($jumlahopname){
                    $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
                }
            } else {
                $datakeluarget = $datakeluar->get();
            }

            $keluar = 0;

            if (count($datakeluarget) > 0) {
                foreach ($datakeluarget as $x => $result) {
                    $produk = Product::find($result->product_id);
                    if($produk->is_liner=='Y'){
                        $produksatuan = $produk->satuan_value;
                    }
                    else{
                        $produksatuan = 1;
                    }
                    $qty = $result->keterangan == 'Mutasi Keluar' ? abs($result->stock_input) : $result->stock_input;

                    if ($result->transaction_detail != null) { // skip data untuk flag status tidak sama dengan 0 (po)
                        if ($result->transaction_detail->purchase_order->flag_status == 0) {
                            $satuan_value = $qty * $produksatuan;
                        } else {
                            $satuan_value = 0;
                        }
                    } else {
                        $satuan_value =  $qty * $produksatuan;
                    }
                    $keluar += $satuan_value;
                }
            }

            // $stokadj    =  $jumlah ? ($jumlah->stock_add + $jumlah->qty_product) : 0;
            // if (!empty($jumlah)|| !empty($jumlahopname)){
            //     if($jumlah->created_at > $jumlahopname->created_at){
            //         $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
            //     }else{
            //         $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
            //     }
            // } else {
            //     $stokadj       = 0;
            // }

            if (!empty($jumlah) && !empty($jumlahopname)) {
                if($jumlah->created_at > $jumlahopname->created_at){
                    $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                    $nilaiadj      =  $stokadj;
                    $nilaiopname   =  0;
                }else{
                    $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
                    $nilaiopname   =  $stokadj;
                    $nilaiadj      =  0;
                }
            }else if (!empty($jumlah) || !empty($jumlahopname)) {
                if($jumlah){
                    $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                    $nilaiadj      =  $stokadj;
                    $nilaiopname   =  0;
                }
                if($jumlahopname){
                    $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
                    $nilaiopname   =  $stokadj;
                    $nilaiadj      =  0;
                }
            } else {
                $stokadj       = 0;
                $nilaiopname   = 0;
                $nilaiadj      = 0;
            }

            // $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;

            // $stokadj =  $jumlah ? ($nilaiadj->stock_input + $jumlah->stock_add) : 0;
            // $stokadj =  $jumlah ? ($nilaiadj->stock_input) : 0;

            $akhir                    = $stokadj +  $masuk  - $keluar;
            $value->no                = $key + $page;
            $value->id                = $value->id;
            $value->kode_produk       = $value->product_code;
            $value->nama_produk       = $value->product_name;
            $value->nama_kategori     = $value->cat_name;
            $value->nama_satuan       = $value->satuan_name;
            $value->qty               = $value->stok;
            $value->adj               = $nilaiadj;
            $value->opname            = $nilaiopname;
            $value->masuk             = $masuk;
            $value->keluar            = $keluar;
            $value->akhir             = $akhir;
        }

        if ($request->user()->can('stokadmin.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );
        } else {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => 0,
                "recordsFiltered" => 0,
                "data"            => []
            );
        }
        return json_encode($json_data);
    }

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
}
