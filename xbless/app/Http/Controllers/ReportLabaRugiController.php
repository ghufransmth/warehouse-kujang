<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportLabaRugiController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index()
    {
        return view('backend/report/laba_rugi/index_labarugi');
    }
}
