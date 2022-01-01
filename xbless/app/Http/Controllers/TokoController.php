<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Toko;
use DB;
use Auth;

class TokoController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "nik"
    );

    public function index(){
        return view('backend/toko/index');
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
        $cek = Toko::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
      }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $dataquery = Toko::select('*');
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
        foreach ($data as $key=> $toko)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($toko->id));
          $action = "";

          $action.="";
          $action.="<div class='btn-group'>";
        //   if($request->user()->can('brand.ubah')){
            $action.='<a href="#" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
        //   }
        //   if($request->user()->can('toko.hapus')){
            $action.='<a href="#" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
        //   }

          $action.="</div>";

          $toko->no             = $key+$page;
          $toko->id             = $toko->id;
          $toko->code           = $toko->code;
          $toko->name           = $toko->name;
          $toko->nik            = $toko->nik;
          $toko->telp           = $toko->telp;
          $toko->alamat         = $toko->alamat;
          $toko->action         = $action;
        }
        // if ($request->user()->can('toko.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
              );
        // }else{
            // $json_data = array(
            //     "draw"            => intval($request->input('draw')),
            //     "recordsTotal"    => 0,
            //     "recordsFiltered" => 0,
            //     "data"            => []
            //   );

        // }
        return json_encode($json_data);
    }

    public function tambah(){
        return view('backend/toko/form');
    }

    // public function simpan(Request $req){
    //     $enc_id     = $req->enc_id;

    //     if ($enc_id != null) {
    //       $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
    //     }else{
    //       $dec_id = null;
    //     }

    //     $cek_nama = $this->cekExist('name',$req->name,$dec_id);
    //     if(!$cek_nama){
    //         $json_data = array(
    //           "success"         => FALSE,
    //           "message"         => 'Mohon maaf. Nama Brand sudah terdaftar pada sistem.'
    //         );
    //     }else {
    //       if($enc_id){
    //         $brand = Toko::find($dec_id);
    //         $brand->name        = $req->name;
    //         $brand->save();
    //         if($brand) {
    //           $json_data = array(
    //                 "success"         => TRUE,
    //                 "message"         => 'Data berhasil diperbarui.'
    //              );
    //         }else{
    //            $json_data = array(
    //                 "success"         => FALSE,
    //                 "message"         => 'Data gagal diperbarui.'
    //              );
    //         }
    //       }else{
    //         $brand              = new Toko;
    //         $brand->name        = $req->name;
    //         $brand->save();
    //         if($brand) {
    //           $json_data = array(
    //                 "success"         => TRUE,
    //                 "message"         => 'Data berhasil ditambahkan.'
    //           );
    //         }else{
    //           $json_data = array(
    //                 "success"         => FALSE,
    //                 "message"         => 'Data gagal ditambahkan.'
    //           );
    //         }

    //       }
    //     }
    //     return json_encode($json_data);
    // }

    // public function ubah($enc_id){
    //     $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
    //     if ($dec_id) {
    //       $brand = Toko::find($dec_id);
    //       return view('backend/toko/form',compact('enc_id','toko'));
    //     } else {
    //         return view('errors/noaccess');
    //     }
    // }

    // public function hapus(Request $request, $enc_id){
    //     $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
    //     $toko    = Toko::find($dec_id);
    //     $cekexist = Product::where('toko_id',$dec_id)->first();
    //     if($toko) {
    //       if($cekexist) {
    //         return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Brand sudah direlasikan dengan Produk, Silahkan hapus dahulu Produk yang terkait dengan Brand ini.']);
    //       }else{
    //         $toko->delete();
    //         return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
    //       }
    //     }else {
    //         return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
    //     }
    // }

}
