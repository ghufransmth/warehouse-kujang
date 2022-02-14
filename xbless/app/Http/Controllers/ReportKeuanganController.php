<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportKeuanganController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        return view('backend/report/keuangan/index_keuangan');
    }
}
