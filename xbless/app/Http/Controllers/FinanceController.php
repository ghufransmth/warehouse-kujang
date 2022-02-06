<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Finance;

use DB;
use Auth;

class FinanceController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );   

    public function index(){
        return view('backend/pembayaran/finance/index');
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }
  
    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
  
    private function cekExist($column,$var,$id){
        $cek = KategoriToko::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        
        $country = KategoriToko::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $country->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $country->orderBy('id','DESC');
        }
         if($search) {
          $country->where(function ($query) use ($search) {
                  $query->orWhere('name','LIKE',"%{$search}%");
          });
        }
        $totalData = $country->get()->count();
  
        $totalFiltered = $country->get()->count();
  
        $country->limit($limit);
        $country->offset($start);
        $data = $country->get();
        foreach ($data as $key=> $negara)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($negara->id));
          $action = "";
         
          $action.="";
          $action.="<div class='btn-group'>";
          $action.='<a href="'.route('toko.kategori.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
          $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
          $action.="</div>";
  
          $negara->no             = $key+$page;
          $negara->id             = $negara->id;
          $negara->name           = $negara->name;
          $negara->action         = $action;
        }
  
        $json_data = array(
          "draw"            => intval($request->input('draw')),  
          "recordsTotal"    => intval($totalData),
          "recordsFiltered" => intval($totalFiltered),
          "data"            => $data
        );
        // if($request->user()->can('negara.index')) {
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => intval($totalData),
        //     "recordsFiltered" => intval($totalFiltered),
        //     "data"            => $data
        //   );
        // }else{
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => 0,
        //     "recordsFiltered" => 0,
        //     "data"            => []
        //   );
           
        // }    
        return json_encode($json_data); 
      }

}
