<?php

namespace App\Http\Controllers;

use App\Imports\PembelianImport;
use Illuminate\Http\Request;
use App\Models\PembelianDetail;
use App\Models\ImportPembelian;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\StockAdj;
use App\Models\TransaksiStock;
use Maatwebsite\Excel\Facades\Excel;

class ImportPembelianController extends Controller
{
    public function index(Request $request){
        $request->session()->forget('status', 'desc');
        $datas = ImportPembelian::all();
        if(count($datas)> 0){
            foreach($datas as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $datas = array();
        }


        return view('backend/pembelian/import', ['data' => $datas]);
    }

    public function import(Request $request){
        $request->session()->forget('status','desc');
        $file = $request->file('file_pembelian');

        Excel::import(new PembelianImport, $file);

        return redirect()->route('pembelian_import.import');
    }

    public function importbatal(){

        if(ImportPembelian::truncate()){
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
        $alldata = ImportPembelian::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/pembelian/import', ['data' => $alldata]);
    }

    public function importsimpan(Request $req){
        $tgl_transaksi = $req->tgl_transaksi;
        $data_import = ImportPembelian::all();
        if(count($data_import) > 0){
            foreach($data_import as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i> Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $data_import = array();
        }

        // VALIDASI
        foreach($data_import as $key => $data){
            $satuan = Satuan::find($data->satuan_id);
            $product = Product::where('kode_product', $data->kode_product)->first();
            $stockadj = StockAdj::where('id_product', $product->id)->first();
            if(!isset($product)){
                $message = array(
                    'status' =>  'danger',
                    'desc'   =>  'Kode Product tidak ditemukan'
                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/pembelian/import', ['data' => $data_import]);
            }
            if(!isset($stockadj)){
                $message = array(
                    'status' =>  'danger',
                    'desc'   =>  'Stock Product Belum Terdaftar'
                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/pembelian/import', ['data' => $data_import]);
            }
            $cekfaktur = Pembelian::where('no_faktur', $data->no_faktur)->first();
            if(isset($cekfaktur)){
                $message = array(
                    'status' =>  'danger',
                    'desc'   =>  'Nomor Faktur Sudah Pernah Digunakan.'
                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/pembelian/import', ['data' => $data_import]);
            }
        }

        // ENDVALIDASI
        foreach($data_import as $key =>$import){
            $cek_pembelian = Pembelian::where('no_faktur', $import->no_faktur)->first();
            $satuan = Satuan::find($import->satuan_id);
            $total_per_faktur = ImportPembelian::where('no_faktur', $import->no_faktur)->sum('total_harga');
            if(!isset($cek_pembelian)){
                $pembelian = new Pembelian;
                $pembelian->no_faktur = $import->no_faktur;
                $pembelian->tgl_transaksi = date('Y-m-d',strtotime($tgl_transaksi));
                $pembelian->tgl_faktur = date('Y-m-d',strtotime($tgl_transaksi));
                // $pembelian->nominal = $data_import->sum('total_harga');
                $pembelian->nominal = $total_per_faktur;
                $pembelian->keterangan = '-';
                $pembelian->status_pembelian = 1;
                $pembelian->approve_pembelian = 0;
                $pembelian->approved_by = auth()->user()->username;
                $pembelian->created_user = auth()->user()->username;
                if($pembelian->save()){
                    $message = array(
                        'status' =>  'danger',
                        'desc'   =>  'Berhasil menyimpan data pembelian.'
                    );
                }
                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi = $pembelian->no_faktur;
                $transaksi_stock->tgl_transaksi = $pembelian->tgl_faktur;
                $transaksi_stock->flag_transaksi = 4;
                $transaksi_stock->created_by = auth()->user()->username;
                $transaksi_stock->note = '-';
                if(!$transaksi_stock->save()){
                    $message = array(
                        'status' =>  'danger',
                        'desc'   =>  'Gagal menyimpan Transaksi Stock.'
                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);
                }
            }

            $detail_pembelian                   = new PembelianDetail;
            $detail_pembelian->pembelian_id     = $pembelian->id;
            $detail_pembelian->product_id       = Product::where('kode_product',$import->kode_product)->first()->id;
            $detail_pembelian->notransaction    = $import->no_faktur;
            $detail_pembelian->qty              = $import->qty * $satuan->qty;
            $detail_pembelian->product_price    = $import->harga_product;
            $detail_pembelian->total            = $import->total_harga;
            $detail_pembelian->created_user     = auth()->user()->username;
            // return response()->json($detail_pembelian->qty);
            if($detail_pembelian->save()){
                $stockadjust = StockAdj::where('id_product', $detail_pembelian->product_id)->first();
                // return response()->json($stockadjust);
                $stockadjust->stock_pembelian += $detail_pembelian->qty;
                if(!$stockadjust->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal Menambahkan Detail Pembelian'
                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);
                }
            }
        }

        if(ImportPembelian::truncate()){
            $message = array(
                'status' => 'success',
                'desc'   => 'Data berhasil diimport'
            );
        }else{
            $message = array(
                'status' => 'danger',
                'desc'   => 'Gagal menghapus semua data di table import'

            );
        }
        $alldata = ImportPembelian::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/pembelian/import', ['data' => $alldata]);
    }

    public function hapus(Request $req){
        $id = $req->id_detail;
        $data = ImportPembelian::find($id);
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

    public function downloadtemplate(){
        $file = public_path(). "/excel/templateDataPembelian.xlsx";

        return response()->download(public_path('/excel/templateDataPembelian.xlsx'));
    }
}
