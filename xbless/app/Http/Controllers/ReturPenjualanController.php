<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Models\Toko;
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
}
