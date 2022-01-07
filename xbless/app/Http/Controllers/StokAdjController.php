<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\StockAdj;
use App\Models\ReportStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrder;
use App\Models\Satuan;
use DB;

use Auth;

class StokAdjController extends Controller
{

    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        return view('backend/stok/stokadj/index');
    }
    public function satuan($id_satuan){
        $satuan = Satuan::find($id_satuan);
        return $satuan->qty;
    }
    public function tambah(){
        $product = Product::all();
        $satuan = Satuan::all();
        return view('backend/stok/stokadj/form', ['product' => $product, 'satuan' => $satuan]);
    }
    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $querydb = StockAdj::select('*');

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
        $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
        $querydb->orderBy('id', 'DESC');
        }
        if ($search) {
        $querydb->where(function ($query) use ($search) {
            $query->orWhere('nama', 'LIKE', "%{$search}%");
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
            $value->no                  = $key + $page;
            // $value->id             = $value->id;
            $value->product             = $value->getproduct->nama;
            $value->stock_penjualan     = $value->stock_penjualan;
            $value->stock_bs            = $value->stock_bs;
            $value->action = '<a href="#" onclick="showproduct(\''. $enc_id. '\')" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-file"></i> Detail</a> ';
        }
        if ($request->user()->can('adjstok.index')) {
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
    public function getData_(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $querydb = Perusahaan::select('*');

        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
        $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
        $querydb->orderBy('id', 'DESC');
        }
        if ($search) {
        $querydb->where(function ($query) use ($search) {
            $query->orWhere('name', 'LIKE', "%{$search}%");
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
        $perusahaangudang = PerusahaanGudang::select('perusahaan_gudang.id', 'perusahaan_gudang.gudang_id', 'gudang.name');
        $perusahaangudang->join('gudang', 'gudang.id', 'perusahaan_gudang.gudang_id');
        $perusahaangudang->where('perusahaan_gudang.perusahaan_id', $value->id);
        $datagudang = $perusahaangudang->get();
        $gudang = "";
        foreach ($datagudang as $x => $result) {
            $nama = $result->name;
            $gudang .= '<a href="#" onclick="manageStock(this,\'' . $key . '\',\'' . $result->id . '\',\'' . $result->gudang_id . '\',\'' . $result->name . '\')" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" title="Gudang ' . $nama . '"><i class="fa fa-file"></i> ' . $nama . '</a>&nbsp;<br/><br/>';
        }


        $value->no             = $key + $page;
        $value->id             = $value->id;
        $value->name           = $value->name;
        $value->gudang         = $gudang;
        $value->action         = $action;
        }
        if ($request->user()->can('adjstok.index')) {
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

    public function flag_barang_masuk()
    {
        return ['Order Barang Masuk', 'Mutasi Masuk', 'retur barang masuk'];
    }

    public function flag_barang_keluar()
    {
            return ['Purchase Keluar', 'Mutasi Keluar'];
    }

    public function cekStokAkhir($product_id,$filter_perusahaan_admin,$filter_gudang_admin)
    {
            $value = Product::find($product_id);

            $jumlah    = StockAdj::select('stock_add', 'created_at', 'qty_product','stock_admin')
                ->where('product_id', $product_id)
                ->where('perusahaan_id', $filter_perusahaan_admin)
                ->where('gudang_id', $filter_gudang_admin)
                ->orderBy('id', 'desc')
                ->first();

            $jumlahopname = StockOpnameDetail::select('stock_opname_detail.qtySO', 'stock_opname_detail.created_at','stock_opname_detail.stock_admin')
                ->join('stock_opname','stock_opname.id','stock_opname_detail.so_id')
                ->where('produk_id', $product_id)
                ->where('perusahaan_id', $filter_perusahaan_admin)
                ->where('gudang_id', $filter_gudang_admin)
                ->where('stock_opname.flag_proses',1)
                ->orderBy('stock_opname_detail.id', 'desc')
                ->first();


            if ($value->is_liner == 'Y') {
                $datamasuk = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id' => $filter_perusahaan_admin,
                    'gudang_id' => $filter_gudang_admin,
                ])->whereIn('keterangan', $this->flag_barang_masuk());
            } else {
                $datamasuk = ReportStock::with('produk_beli')->where([
                    'product_id' => $product_id,
                    'perusahaan_id' => $filter_perusahaan_admin,
                    'gudang_id' => $filter_gudang_admin,
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
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $filter_perusahaan_admin,
                    'gudang_id'            => $filter_gudang_admin,
                ])->whereIn('keterangan', $this->flag_barang_keluar());
            } else {

                $datakeluar = ReportStock::with(['transaction_detail' => function ($query) {
                    $query->with('purchase_order');
                }])->where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $filter_perusahaan_admin,
                    'gudang_id'            => $filter_gudang_admin,
                ])->whereIn('keterangan', $this->flag_barang_keluar());
            }

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

            $akhir          = $stokadj +  $masuk  - $keluar;
            return $akhir;


    }
    public function simpan(Request $req){
        $cek_product = StockAdj::where('id_product', $req->id_product)->first();
        // return $req->all();
        // VALIDASI
        if(isset($cek_product)){
            return response()->json([
                'success' => FALSE,
                'message' => 'Product sudah pernah diinputkan'
            ]);
        }
        // END VALIDASI
        $stok = new StockAdj;
        $stok->id_product       = $req->id_product;
        $stok->stock_penjualan  = $this->satuan($req->satuan_id) * $req->stock_penjualan;
        $stok->stock_bs         = $this->satuan($req->satuan_id) * $req->stock_bs;
        if($stok->save()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Stock berhasil ditambahkan'
            ]);
        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Stock gagal ditambahkan'
            ]);
        }
    }
    public function simpan_(Request $req)
    {

            if (!array_filter($req->qty_adj)) {
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Tidak ada data yang diproses.'
                );

            } else {
                try {
                    DB::beginTransaction();
                    for ($i = 0; $i < $req->jumlahdata; $i++) {
                        if ($req->qty_adj[$i] != 0) {
                            $akhirstok = $this->cekStokAkhir($req->product[$i],$req->perusahaan_id,$req->gudang_id);
                            $adj = new StockAdj;
                            $adj->product_id    = $req->product[$i];
                            $adj->perusahaan_id = $req->perusahaan_id;
                            $adj->gudang_id     = $req->gudang_id;
                            $adj->qty_product   = $req->stok[$i] == '-' ? null : $req->stok[$i];
                            $adj->stock_add     = $req->qty_adj[$i];
                            $adj->note          = $req->catatan[$i];
                            $adj->created_by    = $req->user()->username;
                            $adj->updated_by    = $req->user()->username;
                            $adj->save();
                            if ($adj) {
                                //tambah stok product perusahaan gudang
                                $cek = ProductPerusahaanGudang::select('*')->where('perusahaan_gudang_id', $req->perusahaangudang_id)->where('product_id', $req->product[$i])->first();

                                if($cek) {
                                    $cek->stok += $adj->stock_add;
                                    $cek->save();
                                } else {
                                    //BUAT BARU
                                    $stokbaru = new ProductPerusahaanGudang;
                                    $stokbaru->product_id           = $adj->product_id;
                                    $stokbaru->perusahaan_gudang_id = $req->perusahaangudang_id;
                                    $stokbaru->stok                 = $adj->stock_add;
                                    $stokbaru->save();
                                }

                                if ($req->qty_adj[$i] > 0) {
                                    $ket = 'Adjusment Masuk (' . $adj->note . ')';
                                } else {
                                    $ket = 'Adjusment Keluar (' . $adj->note . ')';
                                }
                                $get_satuan_value_product = Product::find($adj->product_id);

                                if($get_satuan_value_product->is_liner == 'Y'){
                                    if($get_satuan_value_product->product_code==$get_satuan_value_product->product_code_shadow){
                                        //MASTER
                                        $satuanvalue   = $get_satuan_value_product->satuan_value;
                                    }else{
                                        //SUB
                                        $satuanvalue   = $get_satuan_value_product->satuan_value;
                                    }
                                }else{
                                    $satuanvalue    = 1;
                                }


                                //update stok admin
                                $stokadmin = StockAdj::find($adj->id);
                                $stokadmin->stock_admin = $akhirstok+$req->qty_adj[$i];
                                $stokadmin->save();

                                $reportstock = new ReportStock;
                                $reportstock->product_id     = $adj->product_id;
                                $reportstock->product_id_shadow = $adj->product_id;
                                $reportstock->gudang_id      = $req->gudang_id;
                                $reportstock->perusahaan_id  = $req->perusahaan_id;
                                $reportstock->stock_input    = round(($adj->stock_add + $req->stok[$i]) / $satuanvalue);
                                $reportstock->note           = 'Adjusment';
                                $reportstock->keterangan     = $ket;
                                $reportstock->created_at     = date("Y-m-d H:i:s");
                                $reportstock->created_by     = $req->user()->username;
                                $reportstock->save();

                            }
                        }
                    }
                    DB::commit();
                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Data berhasil diproses'
                    );
                    return json_encode($json_data);
                    } catch (\Throwable $th) {
                        DB::rollback();
                        $json_data = array(
                            'code' => 500,
                            'success' => FALSE,
                            'msg' => $th->getMessage(),
                        );
                        return json_encode($json_data);
                    }
            }
    }

    public function perusahaan_gudang($id)
    {
        $perusahaan_gudang = PerusahaanGudang::select('gudang.id', 'gudang.name')->join('gudang', 'gudang.id', 'perusahaan_gudang.gudang_id')->where('perusahaan_id', $id)->get();
        return json_encode($perusahaan_gudang);
    }

    private function cekExist($column, $var, $id)
    {
        $cek = StockOpname::where('id', '!=', $id)->where($column, '=', $var)->first();
        return (!empty($cek) ? false : true);
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

    public function getDataProduct($enc_id)
    {
        // return $enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // $this->safe_encode(Crypt::encryptString($value->id));
        // return $dec_id;
        $stock = StockAdj::where('id',$dec_id)->with(['getproduct', 'getproduct.getkategori', 'getproduct.getsatuan'])->first();
        if(isset($stock)){
            return response()->json([
                'success' => true,
                'data' => $stock,
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Data Product tidak ditemukan',
            ]);
        }


    }

}
