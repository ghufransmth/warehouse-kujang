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
        return view('backend/report/laba_rugi/index_labarugi');
    }

    private function getParent($parent){
        $finance = Finance::select('finance.id','finance.total','komponen_biaya.name')->leftJoin('komponen_biaya','komponen_biaya.id','finance.komponen_biaya_id')->where('finance.id',$parent)->first();

        return $finance;
    }

    public function getDataFinance(Request $request){
        $queryFinanceDetail = FinanceDetail::whereDate('tgl_transaksi','>=', date('Y-m-d', strtotime($request->start)))->whereDate('tgl_transaksi', '<=', date('Y-m-d', strtotime($request->end)));
        $detail = $queryFinanceDetail->groupBy('finance_id')->get();

        $other = [];
        foreach ($detail as $key => $value) {
            $other[]   = $this->getParent($value->finance_id);
        }

        foreach ($other as $key => $value) {
            $value->total   = number_format($value->total,0,',','.');
            $detail         = $this->getDetail($value);
            $value->detail  = $detail;
        }

        return response()->json([
            'code'      => 200,
            'data'    => [
                'title'  => 'Report Laba Rugi',
                'detail'    => [
                    'other'     => $other
                ]
            ]
            
        ]);
    }

    private function getDetail($data){
        $detail = FinanceDetail::select('name','nominal')->where('finance_id', $data->id)->get();
        $html = '';
        $html.='<tr>';
            $html.='<td class="pt-5" style="text-decoration: underline;"><strong>'.$data->name.'</strong></td>';
            $html.='<td></td>';
            $html.='<td></td>';
            $html.='<td></td>';
        $html.='</tr>';
        $child = '';
        $detail_result = [];
        foreach ($detail as $key => $result) {
            $child.='<tr>';
                $child.='<td class="pt-5" style="text-decoration: underline;"><strong>'.$result->name.'</strong></td>';
                $child.='<td>Rp. '.$result->nominal.'</td>';
                $child.='<td></td>';
                $child.='<td></td>';
            $child.='</tr>';

            $detail_result[] = $child;
        }

        $all = array(
            'parent'    => $html,
            'child'     => $detail_result
        );
        
        return $all;
    }
}