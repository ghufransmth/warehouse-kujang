<?php

namespace App\Http\Controllers;

use App\Imports\ImportProduk;
use App\Models\ProdukImport;
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

        Excel::import(new ImportProduk, $file);
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


    }
    public function importbatal(){
        if(DetailPenjualanImport::truncate()){
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
        $alldata = DetailPenjualanImport::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/purchase/import', ['data' => $alldata]);
    }
    public function downloadtemplate(){
        $file= public_path(). "/excel/Import_penjualan.xlsx";

        return response()->download(public_path('/excel/Import_penjualan.xlsx'));
    }
}
