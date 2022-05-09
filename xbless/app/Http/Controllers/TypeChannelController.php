<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\TypeChannel;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class TypeChannelController extends Controller
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
          return view('backend/master/type_channel/index');
      }
      private function cekExist($column,$var,$id){
       $cek = TypeChannel::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          $dataquery = TypeChannel::select('*');

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
            if($request->user()->can('type_channel.ubah')){
                $action.='<a href="'.route('type_channel.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('type_channel.delete')){
                $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
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
            $result->action         = $action;
          }

          if($request->user()->can('type_channel.index')) {
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
        return view('backend/master/type_channel/form');
      }

      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $typeChannel = TypeChannel::find($dec_id);

          return view('backend/master/type_channel/form',compact('typeChannel','enc_id'));
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
                  "message"         => 'Mohon maaf. Type Channel yang Anda masukan sudah terdaftar pada sistem.'
              );
          }else{
            if($enc_id){
              $channel = TypeChannel::find($dec_id);
              $channel->name      = $req->name;
              $channel->save();
              if($channel) {
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
              $channel              = new TypeChannel;
              $channel->name        = $req->name;
              $channel->save();
              if($channel) {
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
        $expedisivia = TypeChannel::find($dec_id);
        if($expedisivia){
          $expedisivia->delete();
          return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }
}
