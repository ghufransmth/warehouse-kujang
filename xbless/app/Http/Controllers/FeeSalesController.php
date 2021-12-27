<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Expedisi;
use App\Models\Sales;
use App\Models\TransactionSalesFee;
use App\Models\xxx;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class FeeSalesController extends Controller
{
      protected $original_column = array(
        1 => "code",
        2 => "name"
      );
      public function index()
      {
          return view('backend/sales_fee/index');
      }
      public function xxx()
      {
          $xxx = new xxx;
          $number = 120000;
          $string_number = (string)$number;
          $xxx->sub_total = $string_number;
          $xxx->total = (string) 3000450000000;
          $xxx->save();
      }
   
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          

          $dataquery = Sales::select('*');
          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $dataquery->orderBy('id','DESC');
          }
           if($search) {
            $dataquery->where(function ($query) use ($search) {
                    $query->orWhere('code','LIKE',"%{$search}%");
                    $query->orWhere('name','LIKE',"%{$search}%");
            });
          }
          $totalData = $dataquery->get()->count();
      
          $totalFiltered = $dataquery->get()->count();
      
          $dataquery->limit($limit);
          $dataquery->offset($start);
          $data = $dataquery->get();
          foreach ($data as $key=> $result)
          {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";
            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('feesales.detail')){
              $action.='<a href="'.route('feesales.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            $action.="</div>";
            
            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->name           = $result->name;
            $result->nominal        = 'Rp. '. number_format($result->nominal_fee(),0,',','.');
            $result->action         = $action;
          }
          if ($request->user()->can('feesales.index')) {
            $json_data = array(
                      "draw"            => intval($request->input('draw')),  
                      "recordsTotal"    => intval($totalData),  
                      "recordsFiltered" => intval($totalFiltered), 
                      "data"            => $data
                      );
          }else{
             $json_data = array(
                      "draw"            => intval($request->input('draw')),  
                      "recordsTotal"    => 0,  
                      "recordsFiltered" => 0, 
                      "data"            => []  
                      );
          }    
          return json_encode($json_data); 
      }
    
      function safe_encode($string) {
	
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
      }
 
	  function safe_decode($string,$mode=null) {
		
		   $data = str_replace(array('_'),array('/'),$string);
        return $data;
      }
      
      public function detail(Request $request,$enc_id)
      {  
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));  
        if($dec_id) {
            $sales = Sales::find($dec_id);
            return view('backend/sales_fee/detail',compact('enc_id','sales'));
        } else {
        	return view('errors/noaccess');
        }
      }
      public function getDataDetail(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $id    = $request->id;
          $page  = $start +1;
          $search = $request->search['value'];
          $dataquery = TransactionSalesFee::select('*')->where('sales_id',$id);
          $dataquery->orderBy('id','ASC');
          if($search) {
            $dataquery->where(function ($query) use ($search) {
                    // $query->orWhere('code','LIKE',"%{$search}%");
                    // $query->orWhere('name','LIKE',"%{$search}%");
            });
          }
          $totalData = $dataquery->get()->count();
      
          $totalFiltered = $dataquery->get()->count();
      
          $dataquery->limit($limit);
          $dataquery->offset($start);
          $data = $dataquery->get();
          foreach ($data as $key=> $result)
          {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";        
            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->no_nota        = $result->getInvoice?$result->getInvoice->no_nota:'-';
            $result->ttl_nota       = $result->getInvoice?number_format($result->getInvoice->total,0,',','.'):'-';
            $result->fee            = number_format($result->fee,0,',','.');
          }
          if ($request->user()->can('feesales.detail')) {
            $json_data = array(
                      "draw"            => intval($request->input('draw')),  
                      "recordsTotal"    => intval($totalData),  
                      "recordsFiltered" => intval($totalFiltered), 
                      "data"            => $data
                      );
          }else{
             $json_data = array(
                      "draw"            => intval($request->input('draw')),  
                      "recordsTotal"    => 0,  
                      "recordsFiltered" => 0, 
                      "data"            => []  
                      );
          }    
          return json_encode($json_data); 
      }
}
