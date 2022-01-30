<?php

namespace App\Http\Controllers;

use App\Models\Sales;
use App\Models\Toko;
use Illuminate\Http\Request;

class ReturPenjualanController extends Controller
{
    public function form_retur($no_faktur){
        // return $no_faktur;
        $member = array();
        $selectedmember ="";
        $sales = Sales::all();
        // $sales = array();
        $selectedsales ="";
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

        return view('backend/retur/penjualan_form',compact('tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia',
                    'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko'));
    }
}
