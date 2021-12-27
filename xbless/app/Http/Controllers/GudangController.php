<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\PurchaseOrderDetail;
use DB;
use Auth;

class GudangController extends Controller
{
  protected $original_column = array(
    1 => "name",
    2 => "status",
  );

  public function status(){
      $value = array('1' => 'Aktif', '0' => 'Tidak Aktif');
      return $value;
  }

  public function index(){
      return view('backend/master/gudang/index');
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
    $cek = Gudang::where('id','!=',$id)->where($column,'=',$var)->first();
    return (!empty($cek) ? false : true);
  }

  public function getData(Request $request){
    $limit = $request->length;
    $start = $request->start;
    $page  = $start +1;
    $search = $request->search['value'];
    
    $gudangs = Gudang::select('*');
    if(array_key_exists($request->order[0]['column'], $this->original_column)){
       $gudangs->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
    }
     else{
      $gudangs->orderBy('id','DESC');
    }
     if($search) {
      $gudangs->where(function ($query) use ($search) {
              $query->orWhere('name','LIKE',"%{$search}%");
      });
    }
    $totalData = $gudangs->get()->count();

    $totalFiltered = $gudangs->get()->count();

    $gudangs->limit($limit);
    $gudangs->offset($start);
    $data = $gudangs->get();
    foreach ($data as $key=> $gudang)
    {
      $enc_id = $this->safe_encode(Crypt::encryptString($gudang->id));
      $action = "";
     
      $action.="";
      $action.="<div class='btn-group'>";
      if($request->user()->can('gudang.ubah')){
          $action.='<a href="'.route('gudang.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
      }
      if($request->user()->can('gudang.delete')){
        $action.='<a href="#" onclick="deleteGudang(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
      }
      $action.="</div>";
      if ($gudang->status=='1') {
        $status = '<span class="label label-primary">Aktif</span>';
     }else if($gudang->status=='0'){
        $status = '<span class="label label-warning">Tidak Aktif</span>';
     }
      $gudang->no             = $key+$page;
      $gudang->id             = $gudang->id;
      $gudang->name           = $gudang->name;
      $gudang->status         = $status;
      $gudang->action         = $action;
    }
    if ($request->user()->can('gudang.index')) {
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

    $status= $this->status();
    $selectedstatus   = '1';
    return view('backend/master/gudang/form',compact('status','selectedstatus'));
  }

  public function simpan(Request $req){
    $enc_id     = $req->enc_id;
         
    if ($enc_id != null) {
      $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
    }else{
      $dec_id = null;
    }

    $cek_gudang = $this->cekExist('name',$req->name,$dec_id);
    if(!$cek_gudang){
        $json_data = array(
          "success"         => FALSE,
          "message"         => 'Mohon maaf. Gudang sudah terdaftar pada sistem.'
        );
    }else {
      if($enc_id){
        $staff = Gudang::find($dec_id);
        $staff->name      = $req->name;
        $staff->status      = $req->status;
        $staff->save();
        if ($staff) {
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
        $staff              = new Gudang;
        $staff->name        = $req->name;
        $staff->status      = $req->status;
        $staff->save();
        if($staff) {
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
      $status= $this->status();
      $gudang = Gudang::find($dec_id);
      $selectedstatus = $gudang->status;

      return view('backend/master/gudang/form',compact('status','enc_id','selectedstatus','gudang'));
    } else {
    	return view('errors/noaccess');
    }
  }

  public function delete(Request $request, $enc_id){
    $dec_id       = $this->safe_decode(Crypt::decryptString($enc_id));
    $gudang       = Gudang::find($dec_id);
    $cekexist     = PerusahaanGudang::where('gudang_id',$dec_id)->first();
    $cekexistgd   = PurchaseOrderDetail::where('gudang_id',$dec_id)->first();
    if($gudang){
      if($cekexist) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Gudang sudah direlasikan dengan Perusahaan Gudang, Silahkan hapus checklist gudang terkait pada perusahaan']);
      }else if($cekexistgd) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Gudang sudah digunakan untuk Transaksi, Silahkan hapus Transaksi yang terkait pada Gudang ini']);
      }else{
        $gudang->delete();
        return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
      }
    }else {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
    }
  }
}
