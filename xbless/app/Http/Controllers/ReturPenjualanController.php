<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\DetailReturTransaksi;
use App\Models\Penjualan;
use App\Models\ReturTransaksi;
use App\Models\Sales;
use App\Models\StockAdj;
use App\Models\Toko;
use App\Models\TransaksiStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ReturPenjualanController extends Controller
{
    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    public function form_retur($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::where('no_faktur', $dec_id)->first();
        $detail_penjualan = DetailPenjualan::where('id_penjualan', $penjualan->id)->where('no_faktur', $penjualan->no_faktur)->with(['getproduct'])->get();
        $member = array();
            $selectedmember ="";
            $sales = Sales::all();
            // $sales = array();
            $selectedsales = $penjualan->id_sales;
            // $expedisi = Expedisi::all();
            $expedisi = array();
            $selectedexpedisi ="";
            // $expedisivia = ExpedisiVia::all();
            $expedisivia = array();
            $selectedexpedisivia ="";
            $selectedproduct ="";
            // $tipeharga = $this->jenisharga();
            $tipeharga = array();
            $selectedtipeharga ="";
            $toko = Toko::all();
            $selectedtoko = $penjualan->id_toko;
            $selectedstatuslunas = $penjualan->status_lunas;

            return view('backend/retur/penjualan_form',compact('enc_id','tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia', 'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko', 'selectedtoko', 'selectedstatuslunas', 'penjualan', 'detail_penjualan'));
    }
    public function simpan(Request $req){
        // return $req->all();
        $dec_id = $this->safe_decode(Crypt::decryptString($req->enc_id));
        $no_transaksi           = $req->no_transaksi;
        $array_harga_product    = $req->harga_product;
        $array_product          = $req->produk;
        $array_stock_product    = $req->stock_product;
        $array_qty              = $req->qty;
        $id_sales               = $req->sales;
        $status_pembayaran      = $req->status_pembayaran; // 1 = lunas, 0 = belum lunas;
        $tgl_jatuh_tempo        = date('Y-m-d',strtotime($req->tgl_jatuh_tempo));
        $tgl_transaksi          = date('Y-m-d', strtotime($req->tgl_transaksi));
        $array_id_satuan        = $req->tipesatuan;
        $id_toko                = $req->toko;
        $array_total_harga      = $req->total;
        $total_product          = $req->total_produk;
        $total_harga_penjualan  = $req->total_harga_penjualan;

        $penjualan = Penjualan::where('no_faktur', $dec_id)->first();
        //VALIDASI
            if(!isset($penjualan)){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Data penjualan tidak ditemukan',
                ]);
            }
            if($total_product < 1){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Product harus lebih dari 1',
                ]);
            }
        //END VALIDASI
        $retur_transaksi = new ReturTransaksi;
        $retur_transaksi->no_faktur = $dec_id;
        $retur_transaksi->no_retur_faktur = $dec_id.'/retur';
        $retur_transaksi->jenis_transaksi = 0;
        $retur_transaksi->tgl_retur = $tgl_transaksi;
        $retur_transaksi->total_harga = $total_harga_penjualan;
        $retur_transaksi->created_user = auth()->user()->username;
        if($retur_transaksi->save()){
            for($i=0;$i<$total_product;$i++){
                if(isset($array_product[$i])){
                    $detail_retur = new DetailReturTransaksi;
                    $detail_retur->retur_transaksi_id = $retur_transaksi->id;
                    $detail_retur->product_id = $array_product[$i];
                    $detail_retur->qty = $array_qty[$i];
                    $detail_retur->price = $array_total_harga[$i];
                    if(!$detail_retur->save()){
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Gagal menyimpan detail retur produk'
                        ]);
                    }else{
                        $stok = StockAdj::where('id_product', $detail_retur->product_id)->first();
                        $stok->stock_retur_penjualan += $array_qty[$i];
                        if($stok->save()){
                            continue;
                        }else{
                            return response()->json([
                                'success' => FALSE,
                                'message' => 'Gagal menyimpan detail stok retur produk'
                            ]);
                        }

                    }

                }
            }
            $transaksi_stok = new TransaksiStock;
            $transaksi_stok->no_transaksi = $retur_transaksi->no_retur_faktur;
            $transaksi_stok->tgl_transaksi = $retur_transaksi->tgl_retur;
            $transaksi_stok->flag_transaksi = 5;
            $transaksi_stok->created_by = $retur_transaksi->created_user;
            if($transaksi_stok->save()){
                return response()->json([
                    'success' => TRUE,
                    'message' => 'Berhasil menyimpan retur produk'
                ]);
            }else{
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Gagal menyimpan retur produk'
                ]);
            }


        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Gagal menyimpan transaksi retur'
            ]);
        }




    }
}
