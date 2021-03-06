<?php

namespace App\Http\Controllers;

use App\Imports\PenjualanImport;
use App\Models\DetailPenjualan;
use App\Models\DetailPenjualanImport;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\StockAdj;
use App\Models\TransaksiStock;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PenjualanImportController extends Controller
{
    public function index(Request $request){
        $request->session()->forget('status', 'desc');
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
        $request->session()->forget('status', 'desc');
        // return $request->all();
        $file = $request->file('file_penjualan');
        // membuat nama file unik
        // $nama_file = rand().$file->getClientOriginalName();

        // upload ke folder file_siswa di dalam folder public
        // $file->move('file_siswa',$nama_file);

        // import data
        try{
            Excel::import(new PenjualanImport, $file);
        }catch(\Maatwebsite\Excel\Validators\ValidationException $e){
            $message = array(
                'status' => 'danger',
                'desc' => 'Template yang dimasukkan salah'

            );
            session(['status' => $message['status'], 'desc' => $message['desc']]);
            return view('backend/purchase/import', ['data' => array()]);
        }
        return redirect()->route('purchaseorder.import');

        // notifikasi dengan session
        // $datas = DetailPenjualanImport::all();
        // if(count($datas)>0){
        //     foreach($datas as $data){
        //         $aksi = "";
        //         $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
        //         $data->aksi = $aksi;
        //     }
        // }else{
        //     $datas = array();
        // }


        // return view('backend/purchase/import', ['data' => $datas]);
        return redirect()->route('purchaseorder.import');
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
    public function importsimpan(Request $request){
       // return $request->all();
        // return date('Y-m-d', strtotime($request->tgl_transaksi));
        $tgl_transaksi = $request->tgl_transaksi;
        $data_import = DetailPenjualanImport::all();
        if(count($data_import)> 0){
            foreach($data_import as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $data_import = array();
        }
        //VALIDASI
            foreach($data_import as $key => $data){
                $satuan        = Satuan::find($data->id_satuan);
                $total_qty     = $data->qty * $satuan->qty;
                $product = Product::where('kode_product', $data->kode_product)->first();
                $stockadj_product = StockAdj::where('id_product', $product->id)->first();
                if(!isset($product)){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'SKU product tidak ditemukan, baris-'.($key+1)

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }
                if(!isset($stockadj_product)){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Stock product belum terdaftar, baris-'.($key+1)

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }

                if($stockadj_product->stock_penjualan < $total_qty){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Stock product tidak cukup, baris-'.($key+1)

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }

                $cek_faktur = Penjualan::where('no_faktur', $data->no_faktur)->first();
                if(isset($cek_faktur)){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Nomor faktur baris '.($key+1).' sudah pernah digunakan'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }
            }

        //ENDVALIDASI
        foreach($data_import as $import){
            $cek_penjualan = Penjualan::where('no_faktur', $import->no_faktur)->first();
            $satuan        = Satuan::find($import->id_satuan);
            if(!isset($cek_penjualan)){
                $penjualan = new Penjualan;
                $penjualan->no_faktur = $import->no_faktur;
                $penjualan->id_sales = $import->id_sales;
                $penjualan->id_toko = $import->id_toko;
                $penjualan->tgl_faktur = date('Y-m-d', strtotime($tgl_transaksi));
                $penjualan->tgl_jatuh_tempo = date('Y-m-d', strtotime($tgl_transaksi));
                $penjualan->tgl_lunas   = date('Y-m-d', strtotime($tgl_transaksi));
                $total_inv = DetailPenjualanImport::where('no_faktur', $import->no_faktur)->sum('total_harga');
                $diskon_inv = DetailPenjualanImport::where('no_faktur', $import->no_faktur)->sum('diskon');
                $penjualan->total_harga = $total_inv;
                $penjualan->total_diskon      = $diskon_inv;
                $penjualan->status_lunas = 1;
                $penjualan->created_by = auth()->user()->username;
                if(!$penjualan->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Penjualan'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }
                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi  = $penjualan->no_faktur;
                $transaksi_stock->tgl_transaksi = $penjualan->tgl_faktur;
                $transaksi_stock->flag_transaksi = 3;
                $transaksi_stock->created_by = auth()->user()->username;
                $transaksi_stock->note = '-';
                if(!$transaksi_stock->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Transaksi Stock'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);
                    // return view('backend/purchase/import')->with('message_alert', $message['status'])->with('message_desc', $message['desc']);
                }
            }

            $data = new DetailPenjualan;
            $data->id_penjualan = $penjualan->id;
            $data->no_faktur = $import->no_faktur;
            $data->id_product = Product::where('kode_product', $import->kode_product)->first()->id;
            $data->qty = $import->qty * $satuan->qty;
            $data->harga_product = $import->harga_product;
            $data->total_harga = $import->total_harga;
            $data->diskon       = $import->diskon;
            if($data->save()){
                $stockadj = StockAdj::where('id_product', $data->id_product)->first();
                $stockadj->stock_penjualan -= $data->qty;
                if(!$stockadj->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal Detail Penjualan'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/purchase/import', ['data' => $data_import]);

                }
            }
        }
        if(DetailPenjualanImport::truncate()){
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
        $alldata = DetailPenjualanImport::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/purchase/import', ['data' => $alldata]);

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
