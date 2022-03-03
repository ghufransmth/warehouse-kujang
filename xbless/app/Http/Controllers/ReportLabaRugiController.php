<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Finance;
use App\Models\FinanceDetail;

use Auth;
use PDF;
use Excel;
use Carbon\Carbon;
use DB;

class ReportLabaRugiController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    function safe_encode($string)
    {
        $data = str_replace(array('/'), array('_'), $string);
        return $data;
    }

    function safe_decode($string, $mode = null)
    {
        $data = str_replace(array('_'), array('/'), $string);
        return $data;
    }

    public function index()
    {
        $queryFinanceDetail = FinanceDetail::whereDate('tgl_transaksi','>=', date('Y-m-d', strtotime("-1Month")))->whereDate('tgl_transaksi', '<=', date('Y-m-d'));
        $detail = $queryFinanceDetail->groupBy('finance_id')->get();

        $result = [];
        foreach ($detail as $key => $value) {
            $result[]   = $this->getParent($value->finance_id);
        }

        foreach ($result as $key => $value) {
            $value->total   = number_format($value->total,0,',','.');
            $detail         = $this->getDetail($value);
            $value->detail  = $detail;
        }

        // return response()->json([
        //     'data' => $result
        // ]);
        return view('backend/report/laba_rugi/index_labarugi', compact('result'));
    }

    private function getParent($parent){
        $finance = Finance::select('finance.id','finance.total','komponen_biaya.name')->leftJoin('komponen_biaya','komponen_biaya.id','finance.komponen_biaya_id')->where('finance.id',$parent)->first();

        return $finance;
    }

    private function getDetail($data){
        $detail = FinanceDetail::select('name','nominal')->where('finance_id', $data->id)->get();

        return $detail;
    }
}