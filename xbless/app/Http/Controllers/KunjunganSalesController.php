<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\KunjunganSales;
use App\Models\Hari;

use DB;
use Auth;

class KunjunganSalesController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "username",
        3 => "email",
        4 => "phone",
        5 => "created_at",
    );

    private function skala(){
        $result = array(
            0 => 'Weekly',
            1 => 'Biweekly'
        );

        return $result;
    }

    public function index(){
        return view('backend/kunjungan/index');
    }

    private function cekExist($column,$var,$id){
        $cek = Sales::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

	function safe_decode($string,$mode=null) {
	    $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    
    public function tambah(){
        $hari = Hari::all();
        $skala = $this->skala();

        $selectedHari = '';
        $selectedSkala = '';

        return view('backend/kunjungan/form', compact('hari', 'skala', 'selectedHari','selectedSkala'));
    }

}
