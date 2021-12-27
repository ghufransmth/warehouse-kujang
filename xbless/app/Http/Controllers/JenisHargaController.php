<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\JenisHarga;
use App\Models\Member;

use DB;
use Auth;

class JenisHargaController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "operation",
    );
    public function index(){
        return view('backend/menuproduk/jenisharga/index');
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
      $cek = JenisHarga::where('id','!=',$id)->where($column,'=',$var)->first();
      return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $jenisharga = JenisHarga::select('*');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $jenisharga->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $jenisharga->orderBy('name','ASC');
        }
        if($search) {
            $jenisharga->where(function ($query) use ($search) {
                    $query->orWhere('name','LIKE',"%{$search}%");
            });
        }
        $totalData = $jenisharga->get()->count();

        $totalFiltered = $jenisharga->get()->count();

        $jenisharga->limit($limit);
        $jenisharga->offset($start);
        $data = $jenisharga->get();
        foreach ($data as $key=> $jenisharga)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($jenisharga->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('jenisharga.ubah')){
                    $action.='<a href="'.route('jenisharga.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('jenisharga.delete')){
                    $action.='<a href="#" onclick="deletejenisharga(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }

            $action.="</div>";

            $jenisharga->no             = $key+$page;
            $jenisharga->id             = $jenisharga->id;
            $jenisharga->name           = $jenisharga->name;
            $jenisharga->operation      = $jenisharga->operation;
            $jenisharga->action         = $action;
        }
        if ($request->user()->can('jenisharga.index')) {
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
        return view('backend/menuproduk/jenisharga/form');
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_jenisharga = $this->cekExist('name',$req->name,$dec_id);
        if(!$cek_jenisharga){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Jenis Harga sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $jenisharga = JenisHarga::find($dec_id);
            $jenisharga->name        = $req->name;
            $jenisharga->operation   = '$product_price + $product_price * '.$req->name.' / 100';
            $jenisharga->save();
            if ($jenisharga) {
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
            $jenisharga              = new JenisHarga;
            $jenisharga->name        = $req->name;
            $jenisharga->operation   = '$product_price + $product_price * '.$req->name.' / 100';
            $jenisharga->save();
            if($jenisharga) {
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

          $jenisharga = JenisHarga::find($dec_id);
          return view('backend/menuproduk/jenisharga/form',compact('enc_id','jenisharga'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $jenisharga  = JenisHarga::find($dec_id);
        $cekexist    = Member::where('operation_price',$dec_id)->first();
        if($jenisharga) {
          if($cekexist) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Jenis Harga sudah direlasikan dengan Member, Silahkan hapus dahulu Member yang terkait dengan Jenis Harga ini.']);
          }else{
            $jenisharga->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}

