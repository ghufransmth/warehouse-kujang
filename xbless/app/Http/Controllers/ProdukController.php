<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\ProductBarcode;
use App\Models\ProductImg;
use App\Models\Kategori;
use App\Models\Product;
use App\Models\Engine;
use App\Models\Satuan;
use App\Models\Brand;
use App\Models\InvoiceDetail;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\PurchaseOrderDetail;
use App\Models\StockAdj;
use DB;
use Auth;
use QrCode;

class ProdukController extends Controller
{
    protected $original_column = array(
        1 => "product_code",
        2 => "product_name",
        3 => "product_cover",
        4 => "category_id",
        5 => "normal_price",

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
        return view('backend/menuproduk/produk/index');
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
    public function tambah()
    {
        $satuan = Satuan::all();
        $selectedsatuan = "";
        $kategori = Kategori::all();
        $selectedkategori = "";
        $liner = $this->isLiner();
        $selectedliner = "";

        $status = $this->status();
        $selectedstatus="1";
        return view('backend/menuproduk/produk/form',compact('satuan', 'selectedsatuan', 'kategori',
        'selectedkategori','liner','selectedliner','status','selectedstatus'));
    }

    public function getdata(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        $product = Product::select('*');
        if (array_key_exists($request->order[0]['column'], $this->original_column)) {
            $product->orderByRaw($this->original_column[$request->order[0]['column']] . ' ' . $request->order[0]['dir']);
        } else {
            $product->orderBy('id', 'DESC');
        }
         if($search) {
          $product->where(function ($query) use ($search) {
                  $query->orWhere('kode_product','LIKE',"%{$search}%");
                  $query->orWhere('nama','LIKE',"%{$search}%");
          });
        }
        $totalData = $product->get()->count();

        $totalFiltered = $product->get()->count();

        $product->limit($limit);
        $product->offset($start);
        $data = $product->get();

        foreach ($data as $key => $products) {
            $enc_id = $this->safe_encode(Crypt::encryptString($products->id));
            $action = "";

            $action .= "";
            $action .= "<div class='btn-group'>";
            // if ($request->user()->can('produk.detail')) {
            //     $action .= '<a href="' . route('produk.detail', $enc_id) . '" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail"><i class="fa fa-eye"></i></a>&nbsp;';
            // }
            if ($request->user()->can('produk.ubah')) {
                $action .= '<a href="' . route('produk.ubah', $enc_id) . '" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i></a>&nbsp;';
            }
            if ($request->user()->can('produk.delete')) {
                $action .= '<a href="#" onclick="deleteProduct(this,\'' . $enc_id . '\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i></a>&nbsp;';
            }
            $action.="</div>";
            // $qrcode = ProductBarcode::where('product_id', $products->id)->get();
            // if(count($qrcode) > 0){
            //     $qr1 ='<p>'.QrCode::size(50)->generate($qrcode[0]->barcode).'</p>';
            // }else{
            //     $qr1 ='-';
            // }
            // if(count($qrcode) == 2){
            //     $qr2 ='<p>'.QrCode::size(50)->generate($qrcode[1]->barcode).'</p>';
            // }else{
            //     $qr2 ='-';
            // }
            $products->no             = $key+$page;
            $products->id             = $products->id;
            $products->code           = $products->kode_product;
            $products->name           = $products->nama;
            $products->satuan         = $products->getsatuan?$products->getsatuan->nama:'-';
            $products->kategori       = $products->getkategori?$products->getkategori->nama:'-';
            $products->harga_beli          = number_format($products->harga_beli,0,',','.');
            $products->harga_jual          = number_format($products->harga_jual,0,',','.');
            $products->action         = $action;
        }

        if ($request->user()->can('produk.index')) {
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


    public function simpan(Request $req){
        $enc_id     = $req->enc_id;
        if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        } else {
            $dec_id = null;
        }
        $cek_produk = $this->cekExist('kode_product',$req->kode_produk,$dec_id);
        if(!$cek_produk)
        {
            return response()->json([
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Kode Produk yang Anda masukan sudah terdaftar pada sistem.'
            ]);
        } else{
            if($enc_id){
                $product = Product::find($dec_id);
                $product->kode_product  = $req->kode_produk;
                $product->nama          = $req->name;
                $product->id_kategori   = $req->kategori_id;
                $product->id_satuan     = $req->satuan_id;
                $product->harga_beli    = str_replace(".", "", $req->harga_beli);
                $product->harga_jual    = str_replace(".", "", $req->harga_jual);
                //VALIDASI
                $cek_kode = Product::where('kode_product', $req->kode_product)->where('id', '!=', $dec_id)->first();
                $cek_nama = Product::where('nama', $req->name)->where('id', '!=', $dec_id)->first();
                if(isset($cek_kode)){
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Mohon maaf. Kode Produk yang Anda masukan sudah terdaftar pada sistem.'
                    ]);
                }
                if(isset($cek_nama)){
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Mohon maaf. Nama Produk yang Anda masukan sudah terdaftar pada sistem.'
                    ]);
                }
                //END VALIDASI
                if($product->save()){
                    return response()->json([
                        "success"         => TRUE,
                        "message"         => 'Data berhasil diperbarui.'
                    ]);
                }else{
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Data gagal diperbarui.'
                    ]);
                }
            }else{
                $product = new Product;
                $product->kode_product  = $req->kode_produk;
                $product->nama          = $req->name;
                $product->id_kategori   = $req->kategori_id;
                $product->id_satuan     = $req->satuan_id;
                $product->harga_beli    = str_replace(".", "", $req->harga_beli);
                $product->harga_jual    = str_replace(".", "", $req->harga_jual);
                //VALIDASI
                $cek_kode = Product::where('kode_product', $req->kode_product)->first();
                $cek_nama = Product::where('nama', $req->name)->first();
                if(isset($cek_kode)){
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Mohon maaf. Kode Produk yang Anda masukan sudah terdaftar pada sistem.'
                    ]);
                }
                if(isset($cek_nama)){
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Mohon maaf. Nama Produk yang Anda masukan sudah terdaftar pada sistem.'
                    ]);
                }
                //END VALIDASI
                if($product->save()){
                    $cek_stock = StockAdj::where('id_product', $product->id)->first();
                    if(!isset($cek_stock)){
                        $stock = new StockAdj;
                        $stock->id_product = $product->id;
                        $stock->save();
                    }
                    return response()->json([
                        "success"         => TRUE,
                        "message"         => 'Data berhasil ditambahkan.'
                    ]);
                }else{
                    return response()->json([
                        "success"         => FALSE,
                        "message"         => 'Data gagal ditambahkan.'
                    ]);
                }
            }
        }
    }


    public function ubah($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id) {
            // $brand = Brand::all();
            $kategori = Kategori::all();

            $satuan = Satuan::all();
            $produk = Product::find($dec_id);
            // $selectedbrand = $produk->brand_id;
            $selectedsatuan = $produk->id_satuan;
            $selectedkategori = $produk->id_kategori;

            // $selectedliner = $produk->is_liner;
            // $img_qrcode = ProductBarcode::where('product_id',$dec_id)->get();
            // $img_produk = ProductImg::where('id_product', $dec_id)->get();
            // $liner = $this->isLiner();
            $status = $this->status();
            $selectedstatus=$produk->status;
            return view('backend/menuproduk/produk/form',compact('enc_id','produk','kategori', 'satuan'
            ,'selectedkategori','selectedsatuan','selectedsatuan','status','selectedstatus'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function detail($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
            $produk = Product::find($dec_id);
            return view('backend/menuproduk/produk/detail', compact('enc_id', 'produk'));
        } else {
            return view('errors/noaccess');
        }
    }

    // public function ProdukImage(Request $request)
    // {
    //     $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
    //     $product = Product::where('id', $dec_id)->first();
    //     $image = ProductImg::where('id_product', $dec_id)->get();

    //     if ($image) {
    //         foreach ($image as $key => $img) {
    //             $image_data = '';
    //             $image_data .= '<div class="col-3" id="detail-image">';
    //             $image_data .= '<img class="img-fluid" src="' . url($img->product_img) . '" style="width: 150px;">';
    //             $image_data .= '</div>';

    //             $img->aksi = $image_data;
    //         }
    //     } else {
    //         $image_data = '';
    //         $image_data .= '<h2>Produk Tidak Mempunyai Image</h2>';
    //         $img->aksi = $image_data;
    //     }

    //     return response()->json(['image_list' => $image, 'name' => $product->product_name]);
    // }


    public function delete_qrcode(Request $request)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $product = ProductBarcode::where('product_id', $dec_id)->where('id', $request->qrcode)->delete();
        if ($product) {
            return response()->json([
                'success' => TRUE,
                'message' => 'QR Code Berhasil Dihapus.'
            ]);
        } else {
            return response()->json([
                'success' => FALSE,
                'message' => 'QR Code Gagal Dihapus.'
            ]);
        }
    }

    public function delete(Request $request, $enc_id)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $produk    = Product::find($dec_id);
        $produk_img = ProductImg::where('id_product', $dec_id)->get();
        $produk_qrcode = ProductBarcode::where('product_id', $dec_id)->get();
        $cekexist    = PurchaseOrderDetail::where('product_id', $dec_id)->first();
        if ($produk) {
            if ($cekexist) {
                return response()->json(['status' => "failed", 'message' => 'Gagal menghapus data. Produk sudah digunakan untuk Transaksi, Silahkan hapus dahulu Transaksi yang terkait dengan Produk ini.']);
            } else {
                if ($produk->product_cover != 'web/images/no_img.png') {
                    if (file_exists($produk->product_cover)) {
                        unlink($produk->product_cover);
                    }
                }
                foreach ($produk_img as $key => $img) {
                    if (file_exists($img->product_img)) {
                        unlink($img->product_img);
                    }
                }
                foreach ($produk_qrcode as $key => $qrcode) {
                    if (file_exists($qrcode->barcode)) {
                        unlink($qrcode->barcode);
                    }
                }
                $detele_img = ProductImg::where('id_product', $dec_id)->delete();
                $detele_qrcode = ProductBarcode::where('product_id', $dec_id)->delete();
                $produk->delete();
                return response()->json(['status' => "success", 'message' => 'Data Berhasil dihapus.']);
            }
        } else {
            return response()->json(['status' => "failed", 'message' => 'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }

}
