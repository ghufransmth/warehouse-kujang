<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\ExpedisiVia;
use App\Models\PurchaseOrder;
use App\Models\Invoice;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class ExpedisiViaController extends Controller
{
      protected $original_column = array(
        1 => "name",
        2 => "telp_no",
        3 => "address",
        4 => "created_at",
        5 => "status",
      );
      public function status()
      {
        $value = array('1'=>'Aktif' ,'0'=>'Tidak Aktif');
        return $value;
      }
      public function index()
      {
          return view('backend/master/expedisivia/index');
      }
      private function cekExist($column,$var,$id){
       $cek = ExpedisiVia::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          

          $dataquery = ExpedisiVia::select('*');
         
          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $dataquery->orderBy('id','DESC');
          }
           if($search) {
            $dataquery->where(function ($query) use ($search) {
                    $query->orWhere('name','LIKE',"%{$search}%");
                    $query->orWhere('telp_no','LIKE',"%{$search}%");
                    $query->orWhere('address','LIKE',"%{$search}%");
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
           
        
            if($request->user()->can('expedisivia.detail')){
              $action.='<a href="'.route('expedisivia.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            if($request->user()->can('expedisivia.ubah')){
              $action.='<a href="'.route('expedisivia.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('expedisivia.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }

            $action.="</div>";
            if ($result->status=='1') {
               $status = '<span class="label label-primary">Aktif</span>';
            }else if($result->status=='0'){
               $status = '<span class="label label-warning">Tidak Aktif</span>';
            }

            

            $result->no             = $key+$page;
           
            $result->id             = $result->id;
            $result->name           = $result->name;
            $result->no_hp          = $result->telp_no==null?'-':$result->telp_no;
            $result->alamat         = $result->address==null?'-':$result->address;
            $result->tgl            = $result->created_at==null?'-':date('d-m-Y H:i',strtotime($result->created_at));
            $result->status         = $status;
            $result->action         = $action;
          }
          if ($request->user()->can('expedisivia.index')) {
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
        $status= $this->status();
        $selectedstatus   = '1';
        return view('backend/master/expedisivia/form',compact('status','selectedstatus'));
      }
     
      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $status= $this->status();
          $expedisivia= ExpedisiVia::find($dec_id);
          $selectedstatus   =  $expedisivia->status;
          
          return view('backend/master/expedisivia/form',compact('status','enc_id','selectedstatus','expedisivia'));
        } else {
        	return view('errors/noaccess');
        }
      }

      public function detail(Request $request,$enc_id)
      {  
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));  
        if($dec_id) {
            $status   = $this->status();
            $expedisivia = ExpedisiVia::find($dec_id);
            $selectedstatus   =$expedisivia->status;
            Carbon::setLocale('id');
            if ($expedisivia->status=='1') {
                $status = '<span class="label label-primary">Aktif</span>';
            }else if($expedisivia->status=='0'){
                $status = '<span class="label label-warning">Tidak Aktif</span>';
            }
            $tgl_buat = $expedisivia->created_at==null? '-':Carbon::parse($expedisivia->created_at)->format('d/m/Y H:i');
            return view('backend/master/expedisivia/detail',compact('status','enc_id','selectedstatus','expedisivia','tgl_buat'));
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
          $cek_name = $this->cekExist('name',$req->name,$dec_id);
          if(!$cek_name){
              $json_data = array(
                  "success"         => FALSE,
                  "message"         => 'Mohon maaf. Expedisi yang Anda masukan sudah terdaftar pada sistem.'
              );
          }else{
            if($enc_id){
              $expedisivia = ExpedisiVia::find($dec_id);
              $expedisivia->name     = $req->name;
              $expedisivia->telp_no  = $req->telp_no;
              $expedisivia->address  = $req->address;
              $expedisivia->status   = $req->status;
              $expedisivia->save();
              if($expedisivia) {
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
             $expedisivia = new ExpedisiVia;
             $expedisivia->name     = $req->name;
             $expedisivia->telp_no  = $req->telp_no;
             $expedisivia->address  = $req->address;
             $expedisivia->status   = $req->status;
             $expedisivia->save();
              if($expedisivia) {
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
        $expedisivia = ExpedisiVia::find($dec_id);
        $cekexistpo     = PurchaseOrder::where('expedisi_via',$dec_id)->where('flag_status',0)->first();
        $cekexistrpo    = PurchaseOrder::where('expedisi_via',$dec_id)->where('flag_status',1)->first();
        $cekexistbo     = PurchaseOrder::where('expedisi_via',$dec_id)->where('flag_status',2)->first();
        if($expedisivia){
          if($cekexistpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi Via sudah direlasikan dengan Transkasi PO, Silahkan hapus dahulu PO yang terkait dengan Expedisi Via ini.']);
          }else if($cekexistrpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi Via sudah direlasikan dengan Transkasi RPO, Silahkan hapus dahulu RPO yang terkait dengan Expedisi Via ini.']);
          }else if($cekexistbo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi Via sudah direlasikan dengan Transkasi BO, Silahkan hapus dahulu BO yang terkait dengan Expedisi Via ini.']);
          }else{
            $expedisivia->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }
}
