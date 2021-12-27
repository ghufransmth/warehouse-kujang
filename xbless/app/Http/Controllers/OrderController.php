<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\ProductBarcode;
use App\Models\ProductBeli;
use App\Models\ProductBeliDetail;
use App\Models\ProductImg;
use App\Models\Kategori;
use App\Models\Product;
use App\Models\Engine;
use App\Models\Satuan;
use App\Models\Brand;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\ProductPerusahaanGudang;
use App\Models\ReportStock;
use DB;
use Auth;
use QrCode;

class OrderController extends Controller
{
    protected $original_column = array(
        1 => "notransaction",
        2 => "factory_name",
        3 => "perusahaan_id",
        4 => "tanggal",
        5 => "status",
        6 => "normal_price",

    );
    public function statusFilter()
    {
        $value = array('99' => 'Semua', '1' => 'Aktif', '0' => 'Tidak Aktif', '2' => 'Blokir');
        return $value;
    }

    public function isLiner()
    {
        $value = array('N' => "TIDAK", 'Y' => 'YA');
        return $value;
    }

    public function status()
    {
        $value = array('0' => 'Tidak Aktif', '1' => 'Aktif');
        return $value;
    }

    public function index()
    {
        return view('backend/order/index');
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

    private function cekExist($column, $var, $id)
    {
        $cek = Product::where('id', '!=', $id)->where($column, '=', $var)->first();
        return (!empty($cek) ? false : true);
    }
    private function cekExistBarcode($column, $var, $id)
    {

        $cek = ProductBarcode::where('product_id', '!=', $id)->where($column, '=', $var)->first();
        return (!empty($cek) ? false : true);
    }

    private function cekProdukBeli($num_transaction)
    {
        $check = ProductBeli::where('notransaction', $num_transaction)->exists();
        return $check;
    }

    public function tambah()
    {
        $satuan = Satuan::all();
        $selectedsatuan = "";
        $brand = Brand::all();
        $selectedbrand = "";
        $kategori = Kategori::all();
        $selectedkategori = "";
        $engine = Engine::all();
        $selectedengine = "";
        $liner = $this->isLiner();
        $selectedliner = "";

        $perusahaan = Perusahaan::all();
        $selectedperusahaan = "";
        $gudang = Gudang::select('*')->limit(5)->get();
        $selectedgudang = "";
        $product = Product::with('satuans:id,name')->offset(0)->limit(10)->get();
        $selectedproduct = "";

        $status = $this->status();
        $selectedstatus = "1";
        return view('backend/order/form', compact(
            'satuan',
            'selectedsatuan',
            'brand',
            'selectedbrand',
            'kategori',
            'selectedkategori',
            'engine',
            'selectedengine',
            'liner',
            'selectedliner',
            'status',
            'selectedstatus',
            'perusahaan',
            'selectedperusahaan',
            'gudang',
            'selectedgudang',
            'product',
            'selectedproduct'
        ));
    }

    public function getdata(Request $request)
    {

        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $order = ProductBeli::select('*')->with('product_beli_detail');
        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $order->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $order->orderBy('id', 'DESC');
        }
        if ($search) {
            $order->where(function ($query) use ($search) {
                $query->orWhere('notransaction', 'LIKE', "%{$search}%");
                $query->orWhere('factory_name', 'LIKE', "%{$search}%");
            });
        }
        $totalData = $order->get()->count();

        $totalFiltered = $order->get()->count();

        $order->limit($limit);
        $order->offset($start);
        $data = $order->get();

        foreach ($data as $key => $orders) {
            $enc_id = $this->safe_encode(Crypt::encryptString($orders->id));
            $action = "";

            $action .= "";
            $action .= "<div class='btn-group'>";
            if ($request->user()->can('produk.detail')) {
                $action .= '<a href="' . route('order.detail', $enc_id) . '" class="btn btn-success btn-md icon-btn md-btn-flat product-tooltip" title="Detail"><i class="fa fa-eye"></i>&nbsp;Detail Data</a>&nbsp;</div>';
            }
            $action .= "<br><br><div class='btn-group'>";
            if ($request->user()->can('produk.ubah')) {
                $action .= '<a href="' . route('order.print', $enc_id) . '" target="_blank" class="btn btn-primary btn-md icon-btn md-btn-flat product-tooltip" title="Cetak"><i class="fa fa-print"></i>&nbsp;Cetak Data</a>&nbsp;';
            }
            $action .= "</div>";

            $perusahaan = Perusahaan::where('id', $orders->product_beli_detail->perusahaan_id)->first();
            if ($orders->status == 1 && $orders->flag_proses == 1) {
                $state = "<span class='label label-success'>Sudah Diapprove</span>";
            } else {
                $state = "<span class='label label-warning-light float-center'>Belum Diproses</span>";
            }

            $log = "";
            $log.='<div class="row">';
                $log.='<div class="col-6" style="margin-bottom:10px;">';
                    $log.='<small class="display-block text-muted">Buat - '.$orders->create_user.'</small><br>';
                    $log.='<small style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;">'
                .date("d M Y H:i:s",strtotime($orders->create_date)).'</small>';
                $log.='</div>';
                if($orders->approved_at!=null){
                    $log.='<div class="col-6" style="margin-bottom:10px;">';
                    $log.='<small class="display-block text-muted">Approved - '.$orders->approved_user.'</small><br>';
                    $log.='<small style="color:#1c84c6;margin-left:20px;font-size:10px;letter-spacing: -1px;">'
                    .date("d M Y H:i:s",strtotime($orders->approved_at)).'</small>';
                    $log.='</div>';
                }
            $log.='</div>';

            $orders->no             = $key + $page;
            $orders->id             = $orders->id;
            $orders->notransaction  = $orders->notransaction.'<br/>'.$log;
            $orders->factory_name   = $orders->factory_name;
            $orders->perusahaan_name = $perusahaan->name;
            // $orders->tanggal        = date('d M Y', strtotime($orders->faktur_date)) . " <hr> " . date('d M Y', strtotime($orders->warehouse_date));
            $orders->tanggal        = $orders->warehouse_date==null?'-': date('d M Y', strtotime($orders->warehouse_date));
            $orders->status         = $state;
            $orders->buat           = $orders->create_user . "<hr>" . $orders->approved_user;
            $orders->action         = $action;
        }
        if ($request->user()->can('order.index')) {
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

    public function simpan(Request $req)
    {
        try {
            $check = $this->cekProdukBeli($req->nofaktur);
            if ($check) {
                return response()->json([
                    'code' => 409,
                    'success' => false,
                    'message' => 'Data sudah ada di database'
                ], 409);
            }

            if ($req->has('simpan')) { // belum diproses
                $status = 0;
                $approved_by = '-';
            } else if ($req->has('simpanselesai')) { // sudah diproses
                $status = 1;
                $approved_at = date('Y-m-d H:i:s');
                $approved_by = auth()->user()->username;
            } else {
                return response()->json([
                    'code' => 405,
                    'success' => false,
                    'message' => 'Method ini tidak diizinkan',
                ], 405);
            }


            DB::beginTransaction();
            $store_data = ProductBeli::create([
                'notransaction' => $req->nofaktur,
                'status' => $status,
                'factory_name' => $req->pabrik,
                'flag_proses'  => 0,
                'faktur_date'  => date('Y-m-d', strtotime(str_replace('/', '-', $req->faktur_date))),
                'warehouse_date' => date('Y-m-d'),
                'note' => '-',
                'create_date' => date('Y-m-d H:i:s'),
                'create_user' => auth()->user()->username,
                'approved_user' => $approved_by
            ]);
            // looping beli produk detail
            if ($store_data) {
                foreach ($req->pid as $key => $item) {
                    ProductBeliDetail::create([
                        'produk_beli_id' => $store_data->id,
                        'produk_id'     => $req->pid[$key],
                        'qty'           => $req->qty[$key],
                        'qty_receive'   => $req->qty[$key],
                        'perusahaan_id' => $req->perusahaan_id,
                        'gudang_id'     => $req->gudang_id,
                        'create_date'   => date('Y-m-d H:i:s'),
                        'create_user'   => auth()->user()->username
                    ]);
                }
                DB::commit();
            }


            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Berhasil Menyimpan',

            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollback();
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $th->getMessage(),

            ]);
        }
    }



    public function ubah($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $brand = Brand::all();
            $kategori = Kategori::all();
            $engine = Engine::all();
            $satuan = Satuan::all();
            $produk = Product::find($dec_id);
            $selectedbrand = $produk->brand_id;
            $selectedsatuan = $produk->satuan_id;
            $selectedkategori = $produk->category_id;
            $selectedengine = $produk->engine_id;
            $selectedliner = $produk->is_liner;
            $img_qrcode = ProductBarcode::where('product_id', $dec_id)->get();
            $img_produk = ProductImg::where('product_id', $dec_id)->get();
            $liner = $this->isLiner();
            $status = $this->status();
            $selectedstatus = $produk->product_status;
            return view('backend/produk/form', compact(
                'enc_id',
                'produk',
                'img_qrcode',
                'img_produk',
                'kategori',
                'satuan',
                'engine',
                'brand',
                'selectedkategori',
                'selectedbrand',
                'selectedsatuan',
                'selectedsatuan',
                'selectedengine',
                'selectedliner',
                'liner',
                'status',
                'selectedstatus'
            ));
        } else {
            return view('errors/noaccess');
        }
    }

    public function detail($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $order = ProductBeli::with(['product_beli_details' => function ($query) {
                $query->with(['perusahaan:id,name', 'produk' => function ($q) {
                    $q->with('satuans:id,name');
                }]);
            }])->where('id', $dec_id)->first();

            $arr_product_beli_item = [];

            foreach ($order->product_beli_details as $key => $item) {

                array_push($arr_product_beli_item, $item->produk_id);
            }
            $encode_arr_product_beli_item = json_encode($arr_product_beli_item);
            $count_arr_product_beli_item = count($arr_product_beli_item);
            $product_beli_item = $encode_arr_product_beli_item;

            $perusahaan = Perusahaan::all();
            $selectedperusahaan = "";

            $gudang = PerusahaanGudang::select('*')->join('gudang', 'gudang.id', 'perusahaan_gudang.gudang_id')
                ->where([
                    'perusahaan_id' => $order->product_beli_details[0]->perusahaan_id,
                    'active' => '1'
                ])
                ->limit(5)
                ->get();
            $selectedgudang = $order->product_beli_details[0]->gudang_id;

            $product = Product::offset(0)->limit(10)->get();
            $selectedproduct = "";

            $status = $this->status();
            $selectedstatus = "1";

            return view('backend/order/form', compact(
                'enc_id',
                'order',
                'status',
                'selectedstatus',
                'perusahaan',
                'selectedperusahaan',
                'gudang',
                'selectedgudang',
                'product',
                'selectedproduct',
                'product_beli_item',
                'count_arr_product_beli_item'
            ));
        } else {
            return view('errors/noaccess');
        }
    }

    public function getDataGudang(Request $request)
    {
        try {
            $get_perusahaan_gudang = PerusahaanGudang::select('gudang.name', 'gudang.id')
                ->rightJoin('gudang', 'perusahaan_gudang.gudang_id',  'gudang.id')
                ->where(['perusahaan_id' => $request->perusahaan_id, 'active' => '1'])->get();

            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $get_perusahaan_gudang,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => 'Terjadi kesalahan pada sistem'
            ], 500);
        }
        return response()->json($request->all());
    }

    public function search_produk(Request $request)
    {
        $product = Product::with('satuans:id,name')
            ->orWhere('product_code', 'LIKE', "%{$request->search}%")
            ->orWhere('product_name', 'LIKE', "%{$request->search}%")
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->get();

        return json_encode($product);
    }

    public function deleteDetailProduk(Request $request, $id)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($id));
        $product_beli_detail = ProductBeliDetail::find($dec_id);

        if (empty($product_beli_detail)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'message' => 'Produk beli detail tidak ditemukan'
            ], 404);
        }

        $product_beli_detail->delete();
        return response()->json([
            'code' => 200,
            'success' => true,
            'message' => 'Berhasil menghapus data produk beli detail',
            'id'    => $dec_id
        ], 200);
    }

    public function saveDone(Request $request)
    {
        try {
            $dec_id   = $this->safe_decode(Crypt::decryptString($request->enc_id));
            $product_beli = ProductBeli::find($dec_id);
            if (empty($product_beli)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'message' => 'Data tidak ditemukan'
                ], 404);
            }
            DB::beginTransaction();

            $change_date_faktur = date('Y-m-d', strtotime(str_replace('/', '-', $request->faktur_date)));
            $change_date_destination = date('Y-m-d', strtotime(str_replace('/', '-', $request->destination_date)));

            $product_beli->update([
                'status'    => 1,
                'factory_name' => $request->pabrik,
                'faktur_date'    => $change_date_faktur,
                'warehouse_date' => $change_date_destination,
                'note' => $request->note != null ? $request->note : '-'
            ]);

            foreach ($request->pbid as $key => $pbid) {
                if ($request->pbid[$key] != 0) {
                    ProductBeliDetail::where('id', $request->pbid[$key])->update([
                        'qty'         => $request->qty[$key],
                        'qty_receive' => $request->qty_receive[$key],
                        'perusahaan_id' => $request->perusahaan_id,
                        'gudang_id'   => $request->gudang_id,
                    ]);
                } else {
                    ProductBeliDetail::insert([
                        'produk_beli_id' => $dec_id,
                        'produk_id' => $request->pid[$key],
                        'qty' => $request->qty[$key],
                        'qty_receive' => $request->qty_receive[$key],
                        'perusahaan_id' => $request->perusahaan_id,
                        'gudang_id'   => $request->gudang_id,
                        'create_date' => date('Y-m-d H:i:s'),
                        'create_user' => auth()->user()->username
                    ]);
                }
            }
            DB::commit();
            return response()->json([
                'code' => 200,
                'success' => true,
                'message' => 'Berhasil simpan dan selesai produk beli'
            ], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function approve(Request $request)
    {

        // try {
        $dec_id   = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $product_beli = ProductBeli::find($dec_id);
        if (empty($product_beli)) {
            return response()->json([
                'code' => 404,
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        DB::beginTransaction();

        $change_date_destination = date('Y-m-d', strtotime(str_replace('/', '-', $request->destination_date)));

        $product_beli->update([
            'flag_proses'    => 1,
            'warehouse_date' => $change_date_destination,
            'approved_at'    => date('Y-m-d H:i:s'),
            'approved_user'  => auth()->user()->username,
        ]);

        // update stok product beli detail
        foreach ($request->pbdid as $key => $pbdid) {

            // if ($request->qty_receive[$key] <= $request->qty[$key]) {

                $data_product_beli_detail = ProductBeliDetail::find($pbdid);

                $productx = Product::find($request->pid[$key]);
                if ($productx->product_code_shadow == null) {
                    $inputproduct_id_shadow  = null;
                } else {
                    if ($productx->product_code_shadow == $productx->product_code) {
                        $inputproduct_id_shadow    = $productx->id;
                    } else {
                        $cekindukbro   = Product::where('product_code', $productx->product_code_shadow)->first();
                        $inputproduct_id_shadow = $cekindukbro->id;
                    }
                }

                $data_product_beli_detail->update([
                    'gudang_id'     => $request->gudang_id,
                    'qty_receive' => $request->qty_receive[$key],
                    'product_id_shadow' => $inputproduct_id_shadow,
                ]);

                $perusahaan_gudang = PerusahaanGudang::select('id')->where([
                    'perusahaan_id' => $data_product_beli_detail->perusahaan_id,
                    'gudang_id'     => $data_product_beli_detail->gudang_id
                ])->first();

                $product = Product::find($request->pid[$key]);
                if ($product->product_code_shadow == null) {
                    $productid     = $product->id;
                    // $satuan_value  = $product->satuan_value;
                    $satuan_value  = 1;
                } else {
                    if ($product->product_code_shadow == $product->product_code) {
                        $productid    = $product->id;
                        $satuan_value = $product->satuan_value;
                    } else {
                        $cekinduk      = Product::where('product_code', $product->product_code_shadow)->first();
                        $productid     = $cekinduk->id;
                        $satuan_value  = $product->satuan_value;
                    }
                }

                $product_perusahaan_gudang = ProductPerusahaanGudang::where([
                    'product_id'           => $productid,
                    'perusahaan_gudang_id' => $perusahaan_gudang->id
                ])->first();

                if (empty($product_perusahaan_gudang)) { // jika data kosong ditabel product_perusahaan_gudang tambah data
                    ProductPerusahaanGudang::insert([
                        'product_id'           => $productid,
                        'perusahaan_gudang_id' => $perusahaan_gudang->id,
                        'stok' => ($request->qty_receive[$key] * $satuan_value)
                    ]);
                } else { // jika tidak update dengan tambah stok nya
                    ProductPerusahaanGudang::where([
                        'product_id'           => $productid,
                        'perusahaan_gudang_id' => $perusahaan_gudang->id
                    ])->update([
                        'stok' => $product_perusahaan_gudang->stok + ($request->qty_receive[$key] * $satuan_value)
                    ]);
                }

                ReportStock::insert([
                    'product_id' => $data_product_beli_detail->produk_id,
                    'product_id_shadow' => $inputproduct_id_shadow,
                    'gudang_id' => $data_product_beli_detail->gudang_id,
                    'perusahaan_id' => $data_product_beli_detail->perusahaan_id,
                    'stock_input'   => $request->qty_receive[$key],
                    'note'  => 'Order Barang Masuk',
                    'keterangan' => 'Order Barang Masuk',
                    'produk_beli_id' => $product_beli->id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => auth()->user()->username
                ]);
            // } else {
            //     DB::rollback();
            // }
        }

        DB::commit();
        return response()->json([
            'code' => 200,
            'success' => true,
            'message' => 'berhasil memperbarui order produk'
        ]);
        // } catch (\Throwable $th) {
        //     DB::rollback();
        //     return response()->json([
        //         'code' => 500,
        //         'success' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
    }

    public function print($id)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($id));
        $produk_beli = ProductBeli::with(['product_beli_details' => function ($query) {
            $query->with(['gudang:id,name', 'perusahaan:id,name', 'produk' => function ($q) {
                $q->with('satuans:id,name');
            }]);
        }])->where('id', $dec_id)->first();

        return view('/backend/order/print', compact('produk_beli'));
    }

    public function searchProdukBarcode(Request $request)
    {
        try {
            $checkBarcode = ProductBarcode::with(['getProduct' => function ($query) {
                $query->with('satuans:id,name');
            }])->where('barcode', $request->barcode)->first();
            if (empty($checkBarcode)) {
                return response()->json([
                    'code' => 404,
                    'success' => false,
                    'message' => 'Produk barcode tidak ditemukan'
                ], 200);
            }
            return response()->json([
                'code' => 200,
                'success' => true,
                'data' => $checkBarcode
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
