<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\PerusahaanGudang;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PurchaseOrder;
use App\Models\Invoice;
use DB;
use Auth;

class PerusahaanController extends Controller
{
  protected $original_column = array(
    1 => "kode",
    2 => "name",
  );
  public function statusFilter(){
     $value = array('99'=>'Semua','1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
    return $value;
  }

  public function status(){
    $value = array('1'=>'Gudang Aktif' ,'2'=>'Gudang Lama');
    return $value;
  }
  public function index(){
      return view('backend/master/perusahaan/index');
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
    $cek = Perusahaan::where('id','!=',$id)->where($column,'=',$var)->first();
    return (!empty($cek) ? false : true);
  }
  public function getData(Request $request){
    $limit = $request->length;
    $start = $request->start;
    $page  = $start +1;
    $search = $request->search['value'];

    $perusahaan = Perusahaan::select('*');
    if(array_key_exists($request->order[0]['column'], $this->original_column)){
       $perusahaan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
    }
     else{
      $perusahaan->orderBy('id','DESC');
    }

     if($search) {
      $perusahaan->where(function ($query) use ($search) {
              $query->orWhere('name','LIKE',"%{$search}%");
      });
    }
    $totalData = $perusahaan->get()->count();

    $totalFiltered = $perusahaan->get()->count();

    $perusahaan->limit($limit);
    $perusahaan->offset($start);
    $data = $perusahaan->get();

    foreach ($data as $key=> $perusahaans)
    {
      $enc_id = $this->safe_encode(Crypt::encryptString($perusahaans->id));
      $action = "";

      $action.="";
      $action.="<div class='btn-group'>";
      if($request->user()->can('perusahaan.detail')){
        $action.='<a href="'.route('perusahaan.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail"><i class="fa fa-eye"></i> Detail</a>&nbsp;';
      }
      if($request->user()->can('perusahaan.gudang')){
        $action.='<a href="#modal_gudang"  id="addgudang" role="button" data-id="'.$perusahaans->id.'" data-toggle="modal" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip gudangdata"><i class="fa fa-file"></i> Gudang</a>&nbsp';
      }
      if($request->user()->can('perusahaan.ubah')){
        $action.='<a href="'.route('perusahaan.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
      }
      if($request->user()->can('perusahaan.delete')){
        $action.='<a href="#" onclick="deletePerusahaan(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
      }
        $action.="</div>";
      if ($perusahaans->flag_perusahaan=='1') {
        $status = '<span class="label label-primary">Gudang Aktif</span>';
      }else if($perusahaans->flag_perusahaan=='2'){
         $status = '<span class="label label-warning">Gudang Lama</span>';
      }
      $perusahaans->no             = $key+$page;
      $perusahaans->id             = $perusahaans->id;
      $perusahaans->name           = $perusahaans->name;
      $perusahaans->status         = $status;
      $perusahaans->action         = $action;
    }

    if ($request->user()->can('perusahaan.index')) {
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
    $status = $this->status();
    $selectedstatus = "";
    return view('backend/master/perusahaan/form',compact('status','selectedstatus'));
  }
  public function simpan(Request $req){
    $enc_id     = $req->enc_id;

    if ($enc_id != null) {
      $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
    }else{
      $dec_id = null;
    }
    $cek_perusahaan = $this->cekExist('name',$req->name,$dec_id);
    $cek_perusahaan_kode = $this->cekExist('kode',$req->kode,$dec_id);
    if(!$cek_perusahaan){
        $json_data = array(
          "success"         => FALSE,
          "message"         => 'Mohon maaf. Nama Perusahaan sudah terdaftar pada sistem.'
        );
    }else if(!$cek_perusahaan_kode){
        $json_data = array(
          "success"         => FALSE,
          "message"         => 'Mohon maaf. Kode Perusahaan sudah terdaftar pada sistem.'
        );
    }else {
      if($enc_id){
        $perusahaan = Perusahaan::find($dec_id);
        $perusahaan->kode                = $req->kode;
        $perusahaan->name                = $req->name;
        $perusahaan->flag_perusahaan     = $req->status;
        $perusahaan->address             = $req->address;
        $perusahaan->city                = $req->city;
        $perusahaan->telephone           = $req->telephone;
        $perusahaan->bank_name           = $req->bank_name;
        $perusahaan->rek_no              = $req->rek_no;
        $perusahaan->save();
        if ($perusahaan) {
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
        $perusahaan                      = new Perusahaan;
        $perusahaan->kode                = $req->kode;
        $perusahaan->name                = $req->name;
        $perusahaan->flag_perusahaan     = $req->status;
        $perusahaan->address             = $req->address;
        $perusahaan->city                = $req->city;
        $perusahaan->telephone           = $req->telephone;
        $perusahaan->bank_name           = $req->bank_name;
        $perusahaan->rek_no              = $req->rek_no;
        $perusahaan->save();
        if($perusahaan) {
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
      $perusahaan = Perusahaan::find($dec_id);
      $status = $this->status();
      $selectedstatus = $perusahaan->flag_perusahaan;
      return view('backend/master/perusahaan/form',compact('enc_id','selectedstatus','perusahaan','status'));
    } else {
    	return view('errors/noaccess');
    }
  }

  public function detail($enc_id){
    $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
    if ($dec_id) {
      $perusahaan = Perusahaan::find($dec_id);
      if ($perusahaan->flag_perusahaan=='1') {
        $status = '<span class="label label-primary">Gudang Aktif</span>';
      }else if($perusahaan->flag_perusahaan=='2'){
         $status = '<span class="label label-warning">Gudang Lama</span>';
      }

      return view('backend/master/perusahaan/detail',compact('enc_id','perusahaan','status'));
    } else {
    	return view('errors/noaccess');
    }
  }

  public function delete(Request $request, $enc_id){
    $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
    $perusahaan    = Perusahaan::find($dec_id);
    $perusahaangudang = PerusahaanGudang::where('perusahaan_id',$dec_id);
    $cekexistgudang = PerusahaanGudang::where('perusahaan_id',$dec_id)->first();
    $cekexistpo     = PurchaseOrder::where('perusahaan_id',$dec_id)->where('flag_status',0)->first();
    $cekexistrpo    = PurchaseOrder::where('perusahaan_id',$dec_id)->where('flag_status',1)->first();
    $cekexistbo     = PurchaseOrder::where('perusahaan_id',$dec_id)->where('flag_status',2)->first();
    $cekexistinvoice= Invoice::where('perusahaan_id',$dec_id)->first();
    if($perusahaan) {
      if($cekexistgudang) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Perusahaan sudah direlasikan dengan Gudang, Silahkan hilangkan dahulu ceklist perusahaan gudang pada yang terkait dengan Perusahaan ini.']);
      }else if($cekexistpo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Perusahaan sudah direlasikan dengan Transkasi PO, Silahkan hapus dahulu PO yang terkait dengan Perusahaan ini.']);
      }else if($cekexistrpo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Perusahaan sudah direlasikan dengan Transkasi RPO, Silahkan hapus dahulu RPO yang terkait dengan Perusahaan ini.']);
      }else if($cekexistbo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Perusahaan sudah direlasikan dengan Transkasi BO, Silahkan hapus dahulu BO yang terkait dengan Perusahaan ini.']);
      }else if($cekexistinvoice) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Perusahaan sudah direlasikan dengan Invoice, Silahkan hapus dahulu Invoice yang terkait dengan Perusahaan ini.']);
      }else{
          $perusahaan->delete();
          PerusahaanGudang::where('perusahaan_id',$dec_id)->delete();
          return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
      }

    }else {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
    }
  }

  public function getGudang(Request $request){
    $dec_id   = $this->safe_decode($request->enc_id);
    return response()->json([
      'datas' => $dec_id
    ]);
  }

  public function perusahaan_gudang(Request $request){
    $dec_id   = $request->enc_id;
    $gudang   = Gudang::all();
    $perusahaan = Perusahaan::where('id', $dec_id)->first();
    foreach ($gudang as $key => $value) {
      $cekgudang = PerusahaanGudang::where('gudang_id', $value->id)->where('perusahaan_id', $dec_id)->first();
      $list = "";
      if($cekgudang){
        $checked = 'checked';
      }else{
        $checked = '';
      }
      $list.='<div><label> <input type="checkbox" '.$checked.' name="gudangid" value="'.$value->id.'"> '.$value->name.'</label></div>';
      $value->aksi = $list;
    }

    return response()->json([
      'datalist' => $gudang,
      'perusahaan' => $perusahaan
    ]);

  }

  public function simpan_perusahaan_gudang(Request $request){
    $perusahaan_id   = $request->perusahaan_id;
    $datagudang = $request->gudang_id;

    foreach ($datagudang as $key => $value) {
      $cek = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->where('gudang_id', $value)->first();
      if($cek == null){
        $simpangudang = new PerusahaanGudang;
        $simpangudang->perusahaan_id = $perusahaan_id;
        $simpangudang->gudang_id     = $value;
        $simpangudang->save();
      }
    }

    $desc = 'data berhasil di update';

    $cek_remove = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->whereNotIn('gudang_id', $datagudang)->delete();

    return response()->json([
      "code" => 200,
      "message" => $desc,
      "messageRmv" => $cek_remove
    ]);
  }
}
