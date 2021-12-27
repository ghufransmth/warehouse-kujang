<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Expedisi;
use App\Models\Country;
use App\Models\Provinsi;
use App\Models\City;

use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class ProvinsiController extends Controller
{
      protected $original_column = array(
        1 => "name"
      );
      private function cekExist($column,$var,$id){
       $cek = Provinsi::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
      }
      public function index()
      {
        return view('backend/master/provinsi/index');
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];
          $dataquery = Provinsi::select('*');
         
          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $dataquery->orderBy('id','DESC');
          }
           if($search) {
            $dataquery->where(function ($query) use ($search) {
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
            if($request->user()->can('provinsi.ubah')){
              $action.='<a href="'.route('provinsi.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('provinsi.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }

            $action.="</div>";
           

            $result->no             = $key+$page;
           
            $result->id             = $result->id;
            $result->name           = $result->name;
            $result->action         = $action;
          }
          if ($request->user()->can('provinsi.index')) {
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
      
      public function tambah()
      {
        $negara           = Country::all();
        $selectednegara   = '';
        return view('backend/master/provinsi/form',compact('negara','selectednegara'));
      }
     
      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $provinsi= Provinsi::find($dec_id);
          $negara           = Country::all();
          $selectednegara   = $provinsi->country_id;
          
          return view('backend/master/provinsi/form',compact('negara','enc_id','selectednegara','provinsi'));
        } else {
        	return view('errors/noaccess');
        }
      }
      public function simpan(Request $req)
      {     
          $enc_id     = $req->enc_id;
          if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
          }else{
            $dec_id = null;
          }
          $cek_povinsi = $this->cekExist('name',$req->name,$dec_id);

         
          if(!$cek_povinsi){   
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Provinsi yang Anda masukan sudah terdaftar pada sistem.'
                );
          }
          else {
            if($enc_id){
              $provinsi = Provinsi::find($dec_id);
              $provinsi->name        = $req->name;
              $provinsi->country_id  = $req->country_id;
              $provinsi->save();
             if($provinsi) {
               $json_data = array(
                     "success"         => TRUE,
                     "message"         => 'Data berhasil diperbarui.'
                  );
             }else{
                $json_data = array(
                     "success"         => FALSE,
                     "message"         => 'Data gagal diperbarui.'
                  );
             }
             
            }else{
             $provinsi = new Provinsi;
             $provinsi->name        = $req->name;
             $provinsi->country_id  = $req->country_id;
             $provinsi->save();
             if($provinsi) {
               $json_data = array(
                     "success"         => TRUE,
                     "message"         => 'Data berhasil ditambahkan.'
               );
              }else{
                $json_data = array(
                     "success"         => FALSE,
                     "message"         => 'Data gagal ditambahkan.'
                );
              }
            }
          }
        return json_encode($json_data); 
      }
      public function hapus(Request $req,$enc_id)
      {
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $provinsi = Provinsi::find($dec_id);
        $cekexist = City::where('provinsi_id',$dec_id)->first();
        if($provinsi){
            if($cekexist) {
              return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Provinsi sudah direlasikan dengan Kota, Silahkan hapus dahulu Kota yang terkait dengan Provinsi ini.']);
            }else{
                $provinsi->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
            }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }
}
