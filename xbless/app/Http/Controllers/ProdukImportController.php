<?php

namespace App\Http\Controllers;

use App\Imports\ImportProduk;
use App\Models\Product;
use App\Models\ProdukImport;
use App\Models\Satuan;
use App\Models\StockAdj;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProdukImportController extends Controller
{
    public function index(Request $request){
        $request->session()->forget('status', 'desc');
        $datas = ProdukImport::all();
        if(count($datas)> 0){
            foreach($datas as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $datas = array();
        }

        return view('backend/menuproduk/produk/import', ['data' => $datas]);
    }
    public function import(Request $request){

        $request->session()->forget('status', 'desc');

        $file = $request->file('file_produk');


        try{
            Excel::import(new ImportProduk, $file);
        }catch(\Maatwebsite\Excel\Validators\ValidationException $e){
            $message = array(
                'status' => 'danger',
                'desc' => 'Template yang dimasukkan salah'

            );
            session(['status' => $message['status'], 'desc' => $message['desc']]);
            return view('backend/menuproduk/produk/import', ['data' => array()]);
        }

        return redirect()->route('produk.import');
    }
    public function hapus(Request $request){
        $id = $request->id_detail;
        $data = ProdukImport::find($id);
        if($data->delete()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Berhasil dihapus'
            ]);
        }else{
            return response()->json([
                'success' => False,
                'message' => 'Gagal dihapus'
            ]);
        }
    }
    public function importsimpan(){
        $datas = ProdukImport::all();
        $data_collect = collect([]);
        foreach($datas as $data){
            $data_collect->push($data);
        }
        $cek_duplicate = $data_collect->duplicates('kode_product');
        if(!$cek_duplicate->isEmpty()){
            foreach($cek_duplicate as $index => $dup){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Terdapat Product yang sama, baris ke - '.($index+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                // $datas = ProdukImport::all();
                return view('backend/menuproduk/produk/import', ['data' => $data_collect]);
                break;
            }

        }
        foreach($datas as $key => $data){
            $produk = Product::where('kode_product', $data->kode_product)->orWhere('nama', $data->nama)->first();
            $satuan = Satuan::find($data->id_satuan);
            if($data->kode_product == null){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Kode product kosong, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }
            if(isset($produk)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Product sudah terdaftar, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }
            if(!isset($satuan)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Satuan tidak terdaftar, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }
        }

        foreach($datas as $key => $data){
            $produk = Product::where('kode_product', $data->kode_product)->orWhere('nama', $data->nama)->first();
            $satuan = Satuan::find($data->id_satuan);
            if($data->kode_product == null){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Kode product kosong, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }
            if(isset($produk)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Product sudah terdaftar, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }
            if(!isset($satuan)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Satuan tidak terdaftar, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }

            $new_product = new Product;
            $new_product->kode_product  = $data->kode_product;
            $new_product->nama          = $data->nama;
            $new_product->id_kategori   = $data->id_kategori;
            $new_product->id_satuan     = $data->id_satuan;
            $new_product->harga_beli    = $data->harga_beli;
            $new_product->harga_jual    = $data->harga_jual;
            if($new_product->save()){
                $stockadj   = new StockAdj;
                $stockadj->id_product = $new_product->id;
                if(!$stockadj->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Stock Product, baris ke - '.($key+1)

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/menuproduk/produk/import', ['data' => $datas]);
                }

            }else{
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Gagal menyimpan Stock Product, baris ke - '.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/menuproduk/produk/import', ['data' => $datas]);
            }

        }
        if(ProdukImport::truncate()){
            $message = array(
                'status' => 'success',
                'desc' => 'Data berhasil diimport'

            );
        }else{
            $message = array(
                'status' => 'danger',
                'desc' => 'Gagal menghapus semua data di table import'

            );
        }
        $alldata = ProdukImport::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/menuproduk/produk/import', ['data' => $alldata]);


    }
    public function importbatal(){
        if(ProdukImport::truncate()){
            $message = array(
                'status' => 'success',
                'desc' => 'Data import berhasil dibatalkan'

            );
        }else{
            $message = array(
                'status' => 'danger',
                'desc' => 'Gagal menghapus semua data di table import'

            );
        }
        $alldata = ProdukImport::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/menuproduk/produk/import', ['data' => $alldata]);
    }
    public function downloadtemplate(){
        $file= public_path(). "/excel/Import_penjualan.xlsx";

        return response()->download(public_path('/excel/Import_penjualan.xlsx'));
    }
}
