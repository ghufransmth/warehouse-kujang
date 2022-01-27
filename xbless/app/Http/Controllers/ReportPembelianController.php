<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\StockAdj;

class ReportPembelianController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index(){
        $pembelian = Pembelian::all();
        $pembelian_detail = PembelianDetail::all();
        $product = Product::select('id','kode_product','nama')->offset(0)->limit(10)->get();
        return view('backend/report/pembelian/index_pembelian',compact('pembelian','pembelian_detail','product'));
    }

    public function getData(Request $req){
        // $limit = $request->length;
        // $start = $request->start;
        // $page  = $start + 1;
        // $search = $request->search['value'];

        $query = Pembelian::select('*');
        return $query->get();
    }
}
