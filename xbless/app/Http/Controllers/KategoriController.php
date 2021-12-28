<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Kategori;
use App\Models\Product;
use DB;
use Auth;

class KategoriController extends Controller
{
    protected $original_column = array(
        1 => "parent_id",
        2 => "cat_code",
        3 => "cat_name",
        4 => "cat_image",
        5 => "status"
    );

    public function status(){
        $value = array('1' => 'Aktif', '0' => 'Tidak Aktif');
        return $value;
    }

    public function index(){
        return view('backend/menuproduk/kategori/index');
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
        $cek = Kategori::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $kategori = Kategori::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $kategori->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $kategori->orderBy('id','DESC');
        }
        if($search) {
            $kategori->where(function ($query) use ($search) {
                    $query->orWhere('cat_code','LIKE',"%{$search}%");
                    $query->orWhere('cat_name','LIKE',"%{$search}%");
            });
        }
        $totalData = $kategori->get()->count();

        $totalFiltered = $kategori->get()->count();

        $kategori->limit($limit);
        $kategori->offset($start);
        $data = $kategori->get();
        foreach ($data as $key=> $category)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($category->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('kategori.ubah')){
                $action.='<a href="'.route('kategori.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('kategori.delete')){
                $action.='<a href="#" onclick="deleteKategori(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }

            $action.="</div>";

            if ($category->cat_status=='1') {
                $status = '<span class="label label-primary">Aktif</span>';
            }else if($category->cat_status=='0'){
                $status = '<span class="label label-warning">Tidak Aktif</span>';
            }
                $category->no             = $key+$page;
                $category->id             = $category->id;
                $category->cat_name       = $category->cat_name;
                $category->cat_sub_name   = $category->cat_sub_name;
                $category->cat_image      = url($category->cat_image);
                $category->status         = $status;
                $category->action         = $action;
            }

        if ($request->user()->can('kategori.index')) {
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
        return view('backend/menuproduk/kategori/form');
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;
        $dir = 'web/images/category/';
        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
            chmod($dir, 0777);
        }

        if($req->file('photo') != null){
          $fileName = date('Ymd').'_'.$req->file('photo')->getClientOriginalName();
          $pathName = $dir.$fileName;
        }

        $cek_cat_code = $this->cekExist('cat_code',$req->cat_code,$dec_id);
        $cek_kategori = $this->cekExist('cat_name',$req->cat_name,$dec_id);
        if(!$cek_cat_code){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Kode Kategori sudah terdaftar pada sistem.'
            );
        }else if(!$cek_kategori){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Nama Kategori sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $kategori = Kategori::find($dec_id);

            if($req->file('photo') != null){
              if(file_exists($kategori->cat_image)){
                  unlink($kategori->cat_image);
              }

              $req->file('photo')->move($dir, $fileName);
              chmod($pathName, 0775);
              $kategori->parent_id       = 0;
              $kategori->cat_code        = $req->cat_code;
              $kategori->cat_name        = $req->cat_name;
              $kategori->cat_sub_name    = $req->cat_sub_name;
              $kategori->cat_image       = $pathName;
              $kategori->cat_status      = 1;
              $kategori->save();
              if ($kategori) {
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
              $kategori->parent_id       = 0;
              $kategori->cat_code        = $req->cat_code;
              $kategori->cat_name        = $req->cat_name;
              $kategori->cat_sub_name    = $req->cat_sub_name;
              $kategori->cat_status      = 1;
              $kategori->save();
              if ($kategori) {
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
            }
          }else{

            $kategori                  = new Kategori;
            $kategori->parent_id       = 0;
            $kategori->cat_code        = $req->cat_code;
            $kategori->cat_name        = $req->cat_name;
            $kategori->cat_sub_name    = $req->cat_sub_name;
            if($req->file('photo') != null){
              $req->file('photo')->move($dir, $fileName);
              chmod($pathName, 0775);
              $kategori->cat_image       = $pathName;
            }

            $kategori->cat_status      = 1;
            $kategori->save();
            if($kategori) {
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
        $kategori = Kategori::find($dec_id);
        $image = $kategori->cat_image;

        return view('backend/menuproduk/kategori/form',compact('status','enc_id','image','kategori'));
      } else {
        return view('errors/noaccess');
      }
    }

    public function delete(Request $request, $enc_id){
        $dec_id      = $this->safe_decode(Crypt::decryptString($enc_id));
        $kategori    = Kategori::find($dec_id);
        $cekexist    = Product::where('category_id',$dec_id)->first();
        if($kategori) {
          if($cekexist) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Kategori sudah direlasikan dengan Produk, Silahkan hapus dahulu Produk yang terkait dengan Kategori ini.']);
          }else{
            if(file_exists($kategori->cat_image)){
              unlink($kategori->cat_image);
            }
              $kategori->delete();
              return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else{
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}
