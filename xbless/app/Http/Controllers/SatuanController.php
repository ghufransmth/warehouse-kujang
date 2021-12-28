<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Satuan;
use App\Models\Product;
use DB;
use Auth;

class SatuanController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "flag_jenis",
    );

    public function jenis(){
        $value = array('1' => 'Ecer', '2' => 'Grosir');
        return $value;
    }

    public function index(){
        return view('backend/menuproduk/satuan/index');
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
      $cek = Satuan::where('id','!=',$id)->where($column,'=',$var)->first();
      return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $satuan = Satuan::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $satuan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $satuan->orderBy('id','DESC');
        }
         if($search) {
          $satuan->where(function ($query) use ($search) {
                  $query->orWhere('name','LIKE',"%{$search}%");
          });
        }
        $totalData = $satuan->get()->count();

        $totalFiltered = $satuan->get()->count();

        $satuan->limit($limit);
        $satuan->offset($start);
        $data = $satuan->get();
        foreach ($data as $key=> $satuan)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($satuan->id));
          $action = "";

          $action.="";
          $action.="<div class='btn-group'>";
          if($request->user()->can('satuan.ubah')){
                $action.='<a href="'.route('satuan.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
          }
          if($request->user()->can('satuan.delete')){
                $action.='<a href="#" onclick="deleteSatuan(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
          }

          $action.="</div>";

          if ($satuan->flag_jenis=='2') {
                $status = '<span class="label label-primary">Grosir</span>';
          }else if($satuan->flag_jenis=='1'){
                $status = '<span class="label label-warning">Ecer</span>';
          }

          $satuan->no             = $key+$page;
          $satuan->id             = $satuan->id;
          $satuan->name           = $satuan->name;
          $satuan->action         = $action;
        }

        if ($request->user()->can('satuan.index')) {
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
        $jenis = $this->jenis();
        $selectedjenis = "";
        return view('backend/menuproduk/satuan/form',compact('jenis','selectedjenis'));
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_satuan = $this->cekExist('name',$req->name,$dec_id);
        if(!$cek_satuan){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Satuan sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $satuan = Satuan::find($dec_id);
            $satuan->name        = $req->name;
            $satuan->flag_jenis  = $req->jenis_satuan;
            $satuan->save();
            if ($satuan) {
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
            $satuan              = new Satuan;
            $satuan->name        = $req->name;
            $satuan->flag_jenis  = $req->jenis_satuan;
            $satuan->save();
            if($satuan) {
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
          $jenis = $this->jenis();
          $satuan = Satuan::find($dec_id);
          $selectedjenis = $satuan->flag_jenis;

          return view('backend/menuproduk/satuan/form',compact('jenis','enc_id','selectedjenis','satuan'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $satuan    = Satuan::find($dec_id);
        $cekexist    = Product::where('satuan_id',$dec_id)->first();
        if($satuan) {
          if($cekexist) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Satuan sudah direlasikan dengan Produk, Silahkan hapus dahulu Produk yang terkait dengan Satuan ini.']);
          }else{
            $satuan->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}

