<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
class BerandaController extends Controller
{
    public function index(){
    	return view('backend/beranda/index');
    }
    public function getdataCount(){
        $rpo       = PurchaseOrder::select('*')->where('flag_status', 1)->where('status', 0)->where('draft',0)->get()->count('*');
        $pobatal   = PurchaseOrder::select('*')->where('status', 2)->where('draft',0)->where('flag_status','!=',0)->get()->count('*');
        $backorder = PurchaseOrder::select('*')->where('status','!=',2)->where('flag_status', 2)->where('draft',0)->get()->count('*');

        $json_data = array(
            "success"         => TRUE,
            "rpo"             => $rpo,
            "pobatal"         => $pobatal,
            "backorder"       => $backorder,
        );
        return json_encode($json_data);

    }
}
