<?php

namespace App\Http\Controllers;

use App\Imports\PenjualanImport;
use App\Models\DetailPenjualan;
use App\Models\DetailPenjualanImport;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\StockAdj;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenjualanImportController extends Controller
{
    public function index(){
        $datas = DetailPenjualanImport::all();
        if(count($datas)> 0){
            foreach($datas as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $datas = array();
        }

        return view('backend/purchase/import', ['data' => $datas]);
    }
    public function import(Request $request){
        // return $request->all();
        $file = $request->file('file_penjualan');

        // membuat nama file unik
        // $nama_file = rand().$file->getClientOriginalName();

        // upload ke folder file_siswa di dalam folder public
        // $file->move('file_siswa',$nama_file);

        // import data
        Excel::import(new PenjualanImport, $file);

        // notifikasi dengan session
        $datas = DetailPenjualanImport::all();

        foreach($datas as $data){
            $aksi = "";
            $aksi .= '<a class="btn btn-danger">Hapus</a>';
            $data->aksi = $aksi;
        }

        return view('backend/purchase/import', ['data' => $data]);
    }
    public function hapus(Request $request){
        // return $request->all();
        $id = $request->id_detail;
        $data = DetailPenjualanImport::find($id);
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
        $data_import = DetailPenjualanImport::all();
        foreach($data_import as $import){
            $cek_penjualan = Penjualan::where('no_faktur', $import->no_faktur)->first();
            if(!isset($cek_penjualan)){
                $penjualan = new Penjualan;
                $penjualan->no_faktur = $import->no_faktur;
                $penjualan->id_sales = $import->id_sales;
                $penjualan->id_toko = $import->id_toko;
                $penjualan->tgl_faktur = date('Y-m-d');
                $penjualan->total_harga = $data_import->sum('total_harga');
                $penjualan->created_by = auth()->user()->username;
                if(!$penjualan->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Penjualan'

                    );
                    session(['message' => $message]);
                    return view('backend/purchase/import');
                }
            }

            $data = new DetailPenjualan;
            $data->id_penjualan = $penjualan->id;
            $data->no_faktur = $import->no_faktur;
            $data->id_product = Product::where('kode_product', $import->kode_product)->first()->id;
            $data->qty = $import->qty;
            $data->harga_product = $import->harga_product;
            $data->total_harga = $import->total_harga;
            if($data->save()){
                $stockadj = new StockAdj;
            }
        }
    }
    public function importbatal(){

    }
}
