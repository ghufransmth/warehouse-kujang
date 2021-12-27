<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Engine;
use App\Models\Product;
use DB;
use Auth;

class EngineController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    public function index(){
        return view('backend/menuproduk/engine/index');
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
      $cek = Engine::where('id','!=',$id)->where($column,'=',$var)->first();
      return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        
        $dataquery = Engine::select('*');
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
        foreach ($data as $key=> $engine)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($engine->id));
          $action = "";
         
          $action.="";
          $action.="<div class='btn-group'>";
          if($request->user()->can('engine.ubah')){
            $action.='<a href="'.route('engine.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
          }
          if($request->user()->can('engine.hapus')){
            $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
          }
         
          $action.="</div>";
          
          $engine->no             = $key+$page;
          $engine->id             = $engine->id;
          $engine->name           = $engine->name;
          $engine->action         = $action;
        }
        if ($request->user()->can('engine.index')) {
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

    public function tambah(){
        return view('backend/menuproduk/engine/form');
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;
             
        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }
    
        $cek_nama = $this->cekExist('name',$req->name,$dec_id);
        if(!$cek_nama){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Nama Engine sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $engine = Engine::find($dec_id);
            $engine->name        = $req->name;
            $engine->vehicles    = $req->vehicles;
            $engine->cylinder    = $req->cylinder;
            $engine->save();
            if($engine) {
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
            $engine              = new Engine;
            $engine->name        = $req->name;
            $engine->vehicles    = $req->vehicles;
            $engine->cylinder    = $req->cylinder;
            $engine->save();
            if($engine) {
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

    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $engine = Engine::find($dec_id);
          return view('backend/menuproduk/engine/form',compact('enc_id','engine'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function hapus(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $engine    = Engine::find($dec_id);
        $cekexist    = Product::where('engine_id',$dec_id)->first();
        if($engine) {
          if($cekexist) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Engine sudah direlasikan dengan Produk, Silahkan hapus dahulu Produk yang terkait dengan Engine ini.']);
          }else{
            $engine->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}

