<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\ReportStock;
use App\Models\Satuan;
use App\Models\StockAdj;
use App\Models\StockMutasi;
use App\Models\TransaksiStock;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;

class StokOpnameController extends Controller
{
    protected $original_column = array(
        1 => "notransaction",
        2 => "perusahaan.name",
        3 => "gudang.name",
        4 => "faktur_date",
        5 => "flag_proses",
        6 => "created_by",
        7 => "approved_by",
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
        return view('backend/stok/stokopname/index');
    }
    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $querydb = TransaksiStock::select('*')->with(['detail_stockopname']);
        $querydb->where('flag_transaksi', 2);


        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('id', 'DESC');
        }
        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('no_transaksi', 'LIKE', "%{$search}%");
                $query->orWhere('detail_stockopname.gudang_dari', 'LIKE', "%{$search}%");
                $query->orWhere('detail_stockopname.gudang_tujuan', 'LIKE', "%{$search}%");
                $query->orWhere('created_by', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();
        // return $data;
        foreach($data as $key => $value){
            $gudang_dari = $value->detail_stockopname[0]->gudang_dari;
            $gudang_tujuan = $value->detail_stockopname[0]->gudang_tujuan;
            if($gudang_dari == 0){
                $gudang_dari = "Gudang Pembelian";
            }else if($gudang_dari == 1){
                $gudang_dari = "Gudang Penjualan";
            }else if($gudang_dari == 2){
                $gudang_dari = "Gudang BS";
            }

            if($gudang_tujuan == 1){
                $gudang_tujuan = "Gudang Penjualan";
            }else if($gudang_tujuan == 2){
                $gudang_tujuan = "Gudang BS";
            }
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";
            $action .= '<a href="#" onclick="showdetail(\''. $enc_id. '\')" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> Detail Data</a>&nbsp';
            $value->no = $key + $page;
            $value->id = $value->id;
            $value->no_transaksi = $value->no_transaksi;
            $value->gudang_dari = $gudang_dari;
            $value->gudang_tujuan = $gudang_tujuan;
            $value->created_by = $value->created_by;
            $value->tgl_transaksi = date('Y-m-d', strtotime($value->tgl_transaksi));
            $value->action = $action;


        }
        if ($request->user()->can('stokopname.index')) {
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
    public function getdatadetail($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $transaksi = TransaksiStock::find($dec_id);
        $stockopname = StockOpname::where('no_transaksi', $transaksi->no_transaksi)->with(['getproduct', 'getsatuan'])->get();
        // foreach($stockopnamee as $stockopname){
        //     if($stockopname->gudang_dari == 0){
        //         $stockopname->gudang_dari = "Gudang Pembelian";
        //     }else if($stockopname->gudang_dari == 1){
        //         $stockopname->gudang_dari = "Gudang Penjualan";
        //     }else if($stockopname->gudang_dari == 2){
        //         $stockopname->gudang_dari = "Gudang BS";
        //     }

        //     if($stockopname->gudang_tujuan == 1){
        //         $stockopname->gudang_tujuan = "Gudang Penjualan";
        //     }else if($stockopname->gudang_tujuan == 2){
        //         $stockopname->gudang_tujuan = "Gudang BS";
        //     }
        // }

        if(isset($transaksi)){
            return response()->json([
                'success' => true,
                'data' => $stockopname,
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Data Product tidak ditemukan',
            ]);
        }

    }
    public function getData_(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $querydb = TransaksiStock::select('*')->with(['detail_stockopname']);
        $querydb->where('flag_transaksi', 2);


        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $querydb->orderBy('id', 'DESC');
        }
        if ($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('no_transaksi', 'LIKE', "%{$search}%");
                $query->orWhere('detail_stockopname.gudang_dari', 'LIKE', "%{$search}%");
                $query->orWhere('detail_stockopname.gudang_tujuan', 'LIKE', "%{$search}%");
                $query->orWhere('created_by', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();
        return $data;
        foreach ($data as $key => $value) {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $action .= "";
            $action .= "<div class='btn-group'>";


            $action .= '<a href="' . route('stokopname.detail', $enc_id) . '" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> Detail Data</a>&nbsp';

            $action .= "</div>";
            if ($value->flag_proses == '0') {
                $status = '<span class="label label-default">Belum diproses</span>';
            } else if ($value->flag_proses == '1') {
                $status = '<span class="label label-success">Sudah diproses</span>';
            } else if ($value->flag_proses == '2') {
                $status = '<span class="label label-danger">Batal diproses</span>';
            }

            $value->no             = $key + $page;
            $value->id             = $value->id;
            $value->notransaction  = $value->notransaction;
            $value->perusahaan     = $value->name;
            $value->gudang         = $value->namagudang;
            $value->status         = $status;
            $value->created_by     = $value->created_by;
            $value->tgltransaksi   = $value->faktur_date == null ? '-' : date('d-m-Y', strtotime($value->faktur_date));
            $value->approve        = $value->approved_by == null ? '-' : $value->approved_by . '<br/>(' . date('d-m-Y H:i', strtotime($value->approved_at)) . ')';
            $value->action         = $action;
        }
        if ($request->user()->can('stokopname.index')) {
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
    public function tambah(Request $request)
    {
        // $gudang = Gudang::all();
        $selectedgudang = '';
        // $perusahaan = Perusahaan::all();
        $selectedperusahaan = '';
        return view('backend/stok/stokopname/form', compact( 'selectedgudang', 'selectedperusahaan'));
    }
    public function detail($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $stokopname = StockOpname::find($dec_id);
            $gudang     = Gudang::all();
            $selectedgudang = $stokopname->gudang_id;
            $perusahaan = Perusahaan::all();
            $selectedperusahaan = $stokopname->perusahaan_id;
            return view('backend/stok/stokopname/form', compact('enc_id', 'stokopname', 'gudang', 'selectedgudang', 'perusahaan', 'selectedperusahaan'));
        } else {
            return view('errors/noaccess');
        }
    }
    public function detaildataform(Request $request)
    {
        $enc_id = $request->enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $stokopname = StockOpname::find($dec_id);
            $readonly = $stokopname->flag_proses == '1' ? 'readonly' : '';
            $html = '';
            foreach ($stokopname->details as $key => $value) {
                $qtyproduk = $value->qtyProduk == null ? '-' : $value->qtyProduk;
                $satuan = $value->product->getsatuan ? $value->product->getsatuan->name : '-';

                $value->qtyproduk     = $value->qtyProduk == null ? '-' : $value->qtyProduk;
                $value->qtyproduk_min = -$qtyproduk;
                $value->satuan    = $value->product->getsatuan ? $value->product->getsatuan->name : '-';
                $value->readonly = $readonly;
                $value->namaproduk = $value->product ? $value->product->product_name : '-';
                // $html.='<tr>';
                // $html.='<td>';
                //     $html.='<span class="product">'.$value->product?$value->product->product_name:'-'.'</span>';
                //     $html.='<input type="hidden" class="product_value" id="product" name="product[]" value="'.$value->produk_id.'">';
                // $html.='</td>';
                // $html.='<td>';
                //     $html.='<span class="price">'.$qtyproduk.'</span>';
                //     $html.='<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'.$qtyproduk.'">';
                // $html.='</td>';
                // $html.='<td>';
                //     $html.='<span class="choose">'.$satuan.'</span>';
                //     $html.='<input type="hidden" class="satuan_value" min=0 id="satuan" name="satuan[]" value="'.$satuan.'">';
                // $html.='</td>';
                //     $html.='<td width="20%">';
                //     $html.='<input type="text" class="form-control qty" min=-'.$qtyproduk.' id="qty_so" name="qty_so[]" value="'.$value->qtySO.'" '.$readonly.'>';
                // $html.='</td>';
                // if($stokopname->flag_proses !='1'){
                //     $html.='<td class="text-right">';
                //     $html.='<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
                //     $html.='</td>';
                // }
                // $html.='</tr>';
            }
            $json_data = array(
                // "html"         => $html,
                "data"         => $stokopname->details,
                "flag_process" => $stokopname->flag_proses,
            );
            return $json_data;
            // return $html;

        } else {
            return view('errors/noaccess');
        }
    }

    public function print($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $stokopname = StockOpname::find($dec_id);
            $gudang     = $stokopname->getgudang ? $stokopname->getgudang->name : '-';
            $perusahaan = $stokopname->getperusahaan ? $stokopname->getperusahaan->name : '-';
            return view('backend/stok/stokopname/print', compact('enc_id', 'stokopname', 'gudang', 'perusahaan'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function cekStokAkhir($product_id, $filter_perusahaan_admin, $filter_gudang_admin)
    {

        $value = Product::find($product_id);

        $jumlah    = StockAdj::select('stock_add', 'created_at', 'qty_product', 'stock_admin')
            ->where('product_id', $product_id)
            ->where('perusahaan_id', $filter_perusahaan_admin)
            ->where('gudang_id', $filter_gudang_admin)
            ->orderBy('id', 'desc')
            ->first();


        $jumlahopname = StockOpnameDetail::select('stock_opname_detail.qtySO', 'stock_opname_detail.created_at', 'stock_opname_detail.stock_admin')
            ->join('stock_opname', 'stock_opname.id', 'stock_opname_detail.so_id')
            ->where('produk_id', $product_id)
            ->where('perusahaan_id', $filter_perusahaan_admin)
            ->where('gudang_id', $filter_gudang_admin)
            ->where('stock_opname.flag_proses', 1)
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
            if ($jumlah->created_at > $jumlahopname->created_at) {

                $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
            } else {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
            }
            if ($jumlahopname) {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else {
            $datamasukget = $datamasuk->get();
        }


        $masuk = 0;

        if (count($datamasukget) > 0) {
            foreach ($datamasukget as $k => $nilai) {
                $produk = Product::find($nilai->product_id);
                if ($produk->is_liner == 'Y') {
                    $produksatuan = $produk->satuan_value;
                } else {
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
            if ($jumlah->created_at > $jumlahopname->created_at) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
            } else {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
            }
            if ($jumlahopname) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else {
            $datakeluarget = $datakeluar->get();
        }


        $keluar = 0;

        if (count($datakeluarget) > 0) {
            foreach ($datakeluarget as $x => $result) {
                $produk = Product::find($result->product_id);
                if ($produk->is_liner == 'Y') {
                    $produksatuan = $produk->satuan_value;
                } else {
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
            if ($jumlah->created_at > $jumlahopname->created_at) {
                $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                $nilaiadj      =  $stokadj;
                $nilaiopname   =  0;
            } else {
                $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
                $nilaiopname   =  $stokadj;
                $nilaiadj      =  0;
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                $nilaiadj      =  $stokadj;
                $nilaiopname   =  0;
            }
            if ($jumlahopname) {
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

        $from_gudang    = $req->gudang_dari;
        $to_gudang      = $req->gudang;
        $jumlah_data    = $req->jumlahdata;
        $no_transaksi   = $req->no_transaksi;
        $note           = $req->note;
        $product        = $req->product;
        $qty            = $req->qty_so;
        $satuan         = $req->satuan;
        $stok           = $req->stok;
        $tgl_transaksi  = $req->tgl_transaksi;
        $created_by     = $req->pic;

        // VALIDASI
            if($from_gudang == $to_gudang){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Gudang asal dan gudang tujuan tidak boleh sama'
                ]);
            }else if($jumlah_data < 1){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Data yang diinputkan kosong'
                ]);
            }
            $cek_notrans = StockOpname::where('no_transaksi', $no_transaksi)->first();
            if(isset($cek_notrans)){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Nomor transaksi sudah pernah dipakai'
                ]);
            }
        //END VALIDASI

        for($i=0 ; $i<$jumlah_data ; $i++){
            $qty_pcs[$i] = Satuan::find($satuan[$i])->qty * $qty[$i];
            //VALIDASI
                if($stok[$i] < $qty_pcs[$i]){
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Stock gudang tidak mencukupi'
                    ]);
                }
            //END VALIDASI
            $stockopname = new StockOpname;
            $stockopname->gudang_dari   = $from_gudang;
            $stockopname->gudang_tujuan = $to_gudang;
            $stockopname->no_transaksi  = $no_transaksi;
            $stockopname->id_product    = $product[$i];
            $stockopname->qty_so        = $qty_pcs[$i];
            $stockopname->id_satuan_so  = $satuan[$i];
            $stockopname->stock_awal    = $stok[$i];
            $stockopname->stock_akhir   = $stok[$i] - $qty_pcs[$i];
            $stockopname->tgl_transaksi = date('Y-m-d', strtotime($tgl_transaksi));
            if(!$stockopname->save()){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Gagal menyimpan data stockopname'
                ]);
                break;
            }else{
                $stock = StockAdj::where('id_product', $product[$i])->first();
                if($from_gudang == 0){
                    if($to_gudang == 1){
                        $stock->stock_pembelian -= $qty_pcs[$i];
                        $stock->stock_penjualan += $qty_pcs[$i];
                    }else if($to_gudang == 2){
                        $stock->stock_pembelian -= $qty_pcs[$i];
                        $stock->stock_bs        += $qty_pcs[$i];
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Asal atau tujuan gudang salah'
                        ]);
                    }
                }else if($from_gudang == 1){
                    if($to_gudang == 2){
                        $stock->stock_penjualan -= $qty_pcs[$i];
                        $stock->stock_bs        += $qty_pcs[$i];
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Asal atau tujuan gudang salah'
                        ]);
                    }
                }else if($from_gudang == 2){
                    if($to_gudang == 1){
                        $stock->stock_bs        -= $qty_pcs[$i];
                        $stock->stock_penjualan += $qty_pcs[$i];
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Asal atau tujuan gudang salah'
                        ]);
                    }
                }else{
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Asal atau tujuan gudang salah'
                    ]);
                }
            }
        }
        $transaksi_stock = new TransaksiStock;
        $transaksi_stock->no_transaksi      = $no_transaksi;
        $transaksi_stock->tgl_transaksi     = date('Y-m-d', strtotime($tgl_transaksi));
        $transaksi_stock->flag_transaksi    = 2;
        $transaksi_stock->created_by        = $created_by;
        $transaksi_stock->note              = $note;
        if($transaksi_stock->save()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Stockopname berhasil disimpan'
            ]);
        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Asal atau tujuan gudang salah'
            ]);
        }

    }

    public function simpan_(Request $req)
    {
        $enc_id     = $req->enc_id;
        if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        } else {
            $dec_id = null;
        }
        $cek_no_transaksi = $this->cekExist('notransaction', $req->no_transaksi, $dec_id);

        if (!$cek_no_transaksi) {
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. No Transaksi yang Anda masukan sudah terdaftar pada sistem.'
            );
        } else {
            if ($enc_id) {
                $so = StockOpname::find($dec_id);
                $so->notransaction = $req->no_transaksi;
                $so->flag_proses   = 0;
                $so->faktur_date   = date('Y-m-d', strtotime($req->tgl_transaksi));
                $so->perusahaan_id = $req->perusahaan;
                $so->gudang_id     = $req->gudang;
                $so->pic           = $req->pic;
                $so->note          = $req->note;
                $so->updated_by    = $req->user()->username;
                $so->save();
                if ($so) {
                    $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $req->perusahaan)->where('gudang_id', $req->gudang)->first();
                    for ($i = 0; $i < $req->jumlahdata; $i++) {
                        $akhirstok = $this->cekStokAkhir($req->product[$i], $req->perusahaan, $req->gudang);

                        $cekdata = StockOpnameDetail::where('produk_id', $req->product[$i])->where('so_id', $so->id)->first();
                        if ($cekdata) {
                            $cekdata->so_id         = $so->id;
                            $cekdata->produk_id     = $req->product[$i];
                            $cekdata->qtyProduk     = $req->stok[$i] == '-' ? null : $req->stok[$i];
                            $cekdata->qtySO         = $req->qty_so[$i];
                            $cekdata->created_by    = $req->user()->username;
                            $cekdata->save();
                            $stokopnameid =  $cekdata->id;
                        } else {
                            $details = new StockOpnameDetail;
                            $details->so_id         = $so->id;
                            $details->produk_id     = $req->product[$i];
                            $details->qtyProduk     = $req->stok[$i] == '-' ? null : $req->stok[$i];
                            $details->qtySO         = $req->qty_so[$i];
                            $details->created_by    = $req->user()->username;
                            $details->save();
                            $stokopnameid =  $details->id;
                        }
                        if ($req->approve == '1') {
                            if ($cekperusahaangudang) {
                                $akhirstok = $this->cekStokAkhir($req->product[$i], $req->perusahaan, $req->gudang);
                                $cek = ProductPerusahaanGudang::where('product_id', $req->product[$i])->where('perusahaan_gudang_id', $cekperusahaangudang->id)->first();
                                if ($cek) {
                                    $cek->stok += $req->qty_so[$i];
                                    // $cek->stok = $req->qty_so[$i];
                                    $cek->save();
                                } else {
                                    //BUAT BARU
                                    $stokbaru = new ProductPerusahaanGudang;
                                    $stokbaru->product_id           = $req->product[$i];
                                    $stokbaru->perusahaan_gudang_id = $cekperusahaangudang->id;
                                    $stokbaru->stok                 = $req->qty_so[$i];
                                    $stokbaru->save();
                                }
                                $stokadmin                  = StockOpnameDetail::find($stokopnameid);
                                $stokadmin->stock_admin     = $akhirstok + $req->qty_so[$i];
                                // $stokadmin->stock_admin  = 1060+$req->qty_so[$i];
                                $stokadmin->save();

                                // new adj nov 18
                                $reportstock = new ReportStock;
                                $reportstock->product_id        = $req->product[$i];
                                $reportstock->product_id_shadow = $req->product[$i];
                                $reportstock->gudang_id         = $so->gudang_id;
                                $reportstock->perusahaan_id     = $so->perusahaan_id;
                                $reportstock->stock_input    = $akhirstok + $req->qty_so[$i];
                                $reportstock->note           = 'Opname';
                                $reportstock->keterangan     = $req->qty_so[$i] > 0 ? 'Opname Masuk' : 'Opname Keluar';
                                $reportstock->created_at        = date("Y-m-d H:i:s");
                                $reportstock->created_by        = $req->user()->username;
                                $reportstock->save();
                            }
                        }
                    }
                    $dataproduct = $req->product;
                    StockOpnameDetail::where('so_id', $so->id)->whereNotIn('produk_id', $dataproduct)->delete();
                    //jika approve =1
                    if ($req->approve == '1') {
                        $soapprove = StockOpname::find($dec_id);
                        $soapprove->flag_proses = 1;
                        $soapprove->approved_at = date("Y-m-d H:i:s");
                        $soapprove->approved_by = $req->user()->username;
                        $soapprove->save();
                        $json_data = array(
                            "success"         => TRUE,
                            "message"         => 'Data berhasil diperbarui & di Approved'
                        );
                    } else {
                        $json_data = array(
                            "success"         => TRUE,
                            "message"         => 'Data berhasil diperbarui.'
                        );
                    }
                } else {
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Data gagal diperbarui.'
                    );
                }
            } else {
                $so = new StockOpname;
                $so->notransaction = $req->no_transaksi;
                $so->flag_proses   = 0;
                $so->faktur_date   = date('Y-m-d', strtotime($req->tgl_transaksi));
                $so->perusahaan_id = $req->perusahaan;
                $so->gudang_id     = $req->gudang;
                $so->pic           = $req->pic;
                $so->note          = $req->note;
                $so->created_by    = $req->user()->username;
                $so->save();
                if ($so) {
                    for ($i = 0; $i < $req->jumlahdata; $i++) {
                        $details = new StockOpnameDetail;
                        $details->so_id         = $so->id;
                        $details->produk_id     = $req->product[$i];
                        $details->qtyProduk     = $req->stok[$i] == '-' ? null : $req->stok[$i];
                        $details->qtySO         = $req->qty_so[$i];
                        $details->created_by    = $req->user()->username;
                        $details->save();
                    }
                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Data berhasil ditambahkan.'
                    );
                } else {
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Data gagal ditambahkan.'
                    );
                }
            }
        }
        return json_encode($json_data);
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

    public function getProduk(Request $request)
    {
        $term = $request->term;
        $query = Product::take(20);
        if ($term) {
            $query->where('nama', 'LIKE', "%{$term}%");
            $query->orWhere('kode_product', 'LIKE', "%{$term}%");
        }
        $produks = $query->get();
        $out = [
            'results' => [],
            'pagination' => [
                'more' => false
            ]
        ];
        foreach ($produks as $value) {
            array_push($out['results'], [
                'id'   => $value->id,
                'text' => $value->kode_product . '-' . $value->nama
            ]);
        }
        return response()->json($out, 200);
    }

    public function tambahProduk(Request $request)
    {
        $id            = $request->id_product;
        $gudang_dari   = $request->gudang_dari;
        // $perusahaan_id = $request->perusahaan_id;
        // $gudang_id     = $request->gudang_id;

        $product       = StockAdj::where('id_product',$id)->first();

        // if ($product->product_code_shadow == null) {
        //     $productid     = $product->id;
        //     $product_code  = $product->product_code;
        //     $product_name = $product->product_name;
        //     $satuan = $product->getsatuan ? $product->getsatuan->name : '-';
        // } else {
        //     if ($product->product_code_shadow == $product->product_code) {
        //         $productid    = $product->id;
        //         $product_code = $product->product_code;
        //         $product_name = $product->product_name;
        //         $satuan = $product->getsatuan ? $product->getsatuan->name : '-';
        //     } else {
        //         $cekinduk      = Product::where('product_code', $product->product_code_shadow)->first();
        //         $productid     = $cekinduk->id;
        //         $product_code  = $cekinduk ? $cekinduk->product_code : '-';
        //         $product_name  = $cekinduk ? $cekinduk->product_name : '-';
        //         $satuan = $cekinduk->getsatuan ? $cekinduk->getsatuan->name : '-';
        //     }
        // }
        // $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->where('gudang_id', $gudang_id)->first();
        // $cek = ProductPerusahaanGudang::select('stok')->where('product_id',$productid)->where('perusahaan_gudang_id',$cekperusahaangudang->id)->first();
        // if($cek){
        //     $stok = $cek->stok;
        // }else{
        //     $stok = '-';
        // }
        $satuan = Satuan::all();
        // $stok   = $this->cekStokAkhir($productid, $perusahaan_id, $gudang_id);
        if($gudang_dari == 0){
            $stok = $product->stock_pembelian;
        }else if($gudang_dari == 1){
            $stok = $product->stock_penjualan;
        }else if($gudang_dari == 2){
            $stok = $product->stock_bs;
        }else{
            $stok = 'tidak ada';
        }

        $html   = '';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<span class="product">' . $product->getproduct->nama . '</span>';
        $html .= '<input type="hidden" class="product_value" id="product" name="product[]" value="' . $product->getproduct->id . '">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<span class="price">' . $stok . '</span>';
        $html .= '<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'.$stok.'">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<select class="satuan_select'.$id.'" name="satuan[]" style="min-width:100px">';
        foreach($satuan as $tuan){
            $html .= '<option value="'.$tuan->id.'">'.$tuan->nama.'</option>';
        }
        $html .= '</select>';
        $html .= '</td>';
        $html .= '<td width="15%">';
        $html .= '<input type="text" class="form-control qty" min=0 id="qty_so" name="qty_so[]" value="1">';
        $html .= '</td>';
        $html .= '<td class="text-right">';
        $html .= '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
        $html .= '</td>';
        $html .= '</tr>';

        $json_data = array(
            // "stok"         => $stok,
            "html"         => $html
        );
        // return json_encode($json_data);
        return $json_data;
    }

    public function tambahProdukBarcode(Request $request)
    {
        $barcode       = $request->barcode;
        $perusahaan_id = $request->perusahaan_id;
        $gudang_id     = $request->gudang_id;
        $product_id_in_tabel     = $request->product_id;

        $cekadabarcode = ProductBarcode::where('barcode', $barcode)->first();
        if ($cekadabarcode) {
            $product = Product::find($cekadabarcode->product_id);
            if ($product) {

                if ($product->product_code_shadow == null) {
                    $productid     = $product->id;
                    $product_code  = $product->product_code;
                    $product_name = $product->product_name;
                    $satuan = $product->getsatuan ? $product->getsatuan->name : '-';
                } else {
                    if ($product->product_code_shadow == $product->product_code) {
                        $productid    = $product->id;
                        $product_code = $product->product_code;
                        $product_name = $product->product_name;
                        $satuan = $product->getsatuan ? $product->getsatuan->name : '-';
                    } else {
                        $cekinduk      = Product::where('product_code', $product->product_code_shadow)->first();
                        $productid     = $cekinduk->id;
                        $product_code  = $cekinduk ? $product->product_code : '-';
                        $product_name  = $cekinduk ? $product->product_name : '-';
                        $satuan = $cekinduk->getsatuan ? $cekinduk->getsatuan->name : '-';
                    }
                }
                if ($request->product_id != null) {
                    $cekexistdata = in_array($productid, $product_id_in_tabel);
                    if ($cekexistdata) {
                        $json_data = array(
                            "success"         => FALSE,
                            "message"         => 'Produk sudah ada di data keranjang'
                        );
                        return $json_data;
                    }
                }
                $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->where('gudang_id', $gudang_id)->first();
                //cekstok
                $cek = ProductPerusahaanGudang::select('stok')->where('product_id', $productid)->where('perusahaan_gudang_id', $cekperusahaangudang->id)->first();
                if ($cek) {
                    $stok = $cek->stok;
                } else {
                    $stok = '-';
                }

                $html = '';
                $html .= '<tr>';
                $html .= '<td>';
                $html .= '<span class="product">' . $product_name . '</span>';
                $html .= '<input type="hidden" class="product_value" id="product" name="product[]" value="' . $productid . '">';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<span class="price">' . $stok . '</span>';
                $html .= '<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="' . $stok . '">';
                $html .= '</td>';
                $html .= '<td>';
                $html .= '<span class="choose">' . $satuan . '</span>';
                $html .= '<input type="hidden" class="satuan_value" min=0 id="satuan" name="satuan[]" value="' . $satuan . '">';
                $html .= '</td>';
                $html .= '<td width="15%">';
                $html .= '<input type="number" class="form-control qty" min=1 id="qty_so" name="qty_so[]" value="' . $cekadabarcode->isi . '">';
                $html .= '</td>';
                $html .= '<td class="text-right">';
                $html .= '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
                $html .= '</td>';
                $html .= '</tr>';
                $json_data = array(
                    "success"         => TRUE,
                    "message"         => $html
                );
                return json_encode($json_data);
                // return $html;
            } else {
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Mohon maaf. Produk tidak ditemukan.'
                );
                return $json_data;
            }
        } else {
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Barcode tidak ditemukan.'
            );
            return json_encode($json_data);
        }
    }
}
