<?php

namespace App\Http\Controllers;

use App\Imports\PembelianImport;
use Illuminate\Http\Request;
use App\Models\PembelianDetail;
use App\Models\ImportPembelian;
use App\Models\Pembelian;
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

    public function importsimpan(Request $req){
        $data_import = ImportPembelian::all();
        // return response()->json($data_import);
        if(count($data_import) > 0){
            foreach($data_import as $data){
                $aksi = "";
                $aksi .= '<a href="#" onclick="hapus(\''.$data->id.'\')" class="btn btn-danger btn-sx icon-btn md-btn-flat product-tooltip"><i class="fa fa-trash"></i>Hapus</a>';
                $data->aksi = $aksi;
            }
        }else{
            $data_import = array();
        }

        // VALIDASI
        foreach($data_import as $key => $data){
            $satuan = Satuan::find($data->satuan_id);
            // return response()->json($satuan);
            // $total_qty = $data->qty * $satuan->qty;
            $product = Product::where('kode_product', $data->kode_product)->first();
            $stockadj = StockAdj::where('id_product', $product->id)->first();
            if(!isset($product)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'kode product tidak ditemukan, baris-'.($key+1)

                );
                session(['status' => $message['status'], 'desc' =>$message['desc']]);
                return view('backend/pembelian_import/import', ['data' => $data_import]);
            }
            if(!isset($stockadj)){
                $message = array(
                    'status' => 'danger',
                    'desc' => 'Stock product belum terdaftar, baris-'.($key+1)

                );
                session(['status' => $message['status'], 'desc' => $message['desc']]);
                return view('backend/pembelian/import',['data' => $data_import]);
            }
            // if($stockadj->stock_pembelian < $total_qty){
            //     $message = array(
            //         'status' => 'danger',
            //         'desc' => 'Stock product harus diisi, baris-'.($key+1)

            //     );
            //     session(['status' => $message['status'], 'desc' => $message['desc']]);
            //     return view('backend/pembelian/import', ['data' => $data_import]);

            // }

            $cek_faktur = Pembelian::where('no_faktur', $data->no_faktur)->first();

            if(isset($cek_faktur)){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Nomor faktur baris '.($key+1).' sudah pernah digunakan'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);
            }
        }

        // ENDVALIDASI
        foreach($data_import as $import){
            $cek_pembelian = Pembelian::where('no_faktur',$import->no_faktur)->first();
            $satuan = Satuan::find($import->satuan_id);
            if(!isset($cek_pembelian)){
                $pembelian = new Pembelian;
                $pembelian->no_faktur = $import->no_faktur;
                $pembelian->tgl_faktur = date('Y-m-d');
                $pembelian->tgl_jatuh_tempo = date('Y-m-d');
                // $pembelian->tgl_lunas = date('Y-m-d');
                $pembelian->nominal = $import->sum('total_harga');
                $pembelian->keterangan = '-';
                $pembelian->status_pembelian = 1;
                $pembelian->approve_pembelian = 0;
                $pembelian->approved_by = auth()->user()->username;
                $pembelian->created_user = auth()->user()->username;
                if(!$pembelian->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Penjualan'
                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);
                }

                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi = $pembelian->no_faktur;
                $transaksi_stock->tgl_transaksi = $pembelian->tgl_faktur;
                $transaksi_stock->flag_transaksi = 4;
                $transaksi_stock->created_by = auth()->user()->username;
                $transaksi_stock->note = '-';
                if(!$transaksi_stock->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal menyimpan Transaksi Stock'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);
                }
            }
            $detail_pembelian = new PembelianDetail;
            $detail_pembelian->pembelian_id = $pembelian->id;
            $detail_pembelian->product_id = Product::where('kode_product',$import->kode_product)->first()->id();
            $detail_pembelian->notransaction = $pembelian->no_faktur;
            $detail_pembelian->qty = $import->qty * $satuan->qty;
            $detail_pembelian->product_price = $import->harga_product;
            $detail_pembelian->total = $import->total_harga;
            $detail_pembelian->created_user         = auth()->user()->username;
            if($data->save()){
                $stockadj = StockAdj::where('id_product', $data->id_product)->first();
                $stockadj->stock_pembelian += $data->qty;
                if($stockadj->save()){
                    $message = array(
                        'status' => 'danger',
                        'desc' => 'Gagal Detail Pembelian'

                    );
                    session(['status' => $message['status'], 'desc' => $message['desc']]);
                    return view('backend/pembelian/import', ['data' => $data_import]);

                }
            }
        }
        if(PembelianImport::truncate()){
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
        $alldata = PembelianImport::all();
        session(['status' => $message['status'], 'desc' => $message['desc']]);
        return view('backend/pembelian/import', ['data' => $alldata]);

    }

    public function importbatal(){

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
