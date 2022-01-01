<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PembelianController extends Controller
{
    public function index(){

        return view('backend/pembelian/index');
    }

    public function tambah(){

        return view('backend/pembelian/form');
    }
}
