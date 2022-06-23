<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Payment;
use DB;
use Auth;

class JenisBayarController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    public function index(){
        return view('backend/master/payment/index');
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
      $cek = Payment::where('id','!=',$id)->where($column,'=',$var)->first();
      return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
      $limit = $request->length;
      $start = $request->start;
      $page  = $start +1;
      $search = $request->search['value'];

      $country = Payment::select('*');
      if(array_key_exists($request->order[0]['column'], $this->original_column)){
         $country->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
      }
       else{
        $country->orderBy('id','DESC');
      }
       if($search) {
        $country->where(function ($query) use ($search) {
                $query->orWhere('name','LIKE',"%{$search}%");
        });
      }
      $totalData = $country->get()->count();

      $totalFiltered = $country->get()->count();

      $country->limit($limit);
      $country->offset($start);
      $data = $country->get();
      foreach ($data as $key=> $negara)
      {
        $enc_id = $this->safe_encode(Crypt::encryptString($negara->id));
        $action = "";

        $action.="";
        $action.="<div class='btn-group'>";
        if ($request->user()->can('payment.ubah')) {
            $action.='<a href="'.route('payment.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
        }
        if ($request->user()->can('payment.delete')) {
            $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
        }
        $action.="</div>";

        $negara->no             = $key+$page;
        $negara->id             = $negara->id;
        $negara->name           = $negara->name;
        $negara->action         = $action;
      }

      $json_data = array(
        "draw"            => intval($request->input('draw')),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
      );
      if($request->user()->can('payment.index')) {
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
      return view('backend/master/payment/form');
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }
        $cek_negara = $this->cekExist('name',$req->name,$dec_id);
        if(!$cek_negara){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Negara sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $negara = Payment::find($dec_id);
            $negara->name      = $req->name;
            $negara->save();
            if ($negara) {
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
            $negara              = new Payment;
            $negara->name        = $req->name;
            $negara->save();
            if($negara) {
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
          $distrik = Payment::find($dec_id);
          return view('backend/master/payment/form',compact('enc_id', 'distrik'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $negara   = Payment::find($dec_id);
        if($negara) {
          $negara->delete();
          return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
        }else {
          return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}
