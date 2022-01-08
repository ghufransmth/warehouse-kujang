<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembelianDetail;
use App\Models\ImportPembelian;
use App\Models\Pembelian;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\StockAdj;
use App\Models\TransaksiStock;

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
}
