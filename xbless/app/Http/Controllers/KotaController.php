<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Expedisi;
use App\Models\Country;
use App\Models\Provinsi;
use App\Models\City;
use App\Models\Member;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class KotaController extends Controller
{
      protected $original_column = array(
        1 => "name",
        2 => "area_code",
        3 => "abbreviation",
      );
      public function index()
      {
        return view('backend/master/kota/index');
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];
          $dataquery = City::select('*');

          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $dataquery->orderBy('id','DESC');
          }
           if($search) {
            $dataquery->where(function ($query) use ($search) {
                    $query->orWhere('name','LIKE',"%{$search}%");
                    $query->orWhere('abbreviation','LIKE',"%{$search}%");
                    $query->orWhere('area_code','LIKE',"%{$search}%");
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
            if($request->user()->can('kota.ubah')){
              $action.='<a href="'.route('kota.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('kota.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }

            $action.="</div>";


            $result->no             = $key+$page;

            $result->id             = $result->id;
            $result->name           = $result->name;
            $result->singkatan      = $result->abbreviation==null?'-':$result->abbreviation;
            $result->action         = $action;
          }
          if ($request->user()->can('kota.index')) {
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
        $negara           = Country::all();
        $selectednegara   = '';
        return view('backend/master/kota/form',compact('negara','selectednegara'));
      }

      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $kota= City::find($dec_id);


          $selectedprovinsi   = $kota->provinsi_id;
          $provinsi           = $kota->getprovinsi->name;


          $negara           = Country::all();
          $selectednegara   = Provinsi::find($kota->provinsi_id)->first()->country_id;
          return view('backend/master/kota/form',compact('kota','negara','enc_id','selectednegara','selectedprovinsi','provinsi','selectedprovinsi'));
        } else {
        	return view('errors/noaccess');
        }
      }
      public function provinsi($id,$kecuali=null)
      {
        $prov = Provinsi::where('country_id',$id)->get();
        return json_encode($prov);
      }

      private function cekExist($column,$var,$id)
      {
       $cek = City::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
      }
      public function simpan(Request $req)
      {
          $enc_id     = $req->enc_id;
          if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
          }else{
            $dec_id = null;
          }
          if($req->abbreviation != null){
            if(strlen($req->abbreviation) < 3){
              $json_data = array(
                "success"         => FALSE,
                "message"         => 'Singkatan Kota harus 3 karakter'
              );
              return json_encode($json_data);
            }else if(strlen($req->abbreviation) > 3){
              $json_data = array(
                "success"         => FALSE,
                "message"         => 'Singkatan Kota harus 3 karakter'
              );
              return json_encode($json_data);
            }else{
              $cek_singkatan = $this->cekExist('abbreviation',$req->abbreviation,$dec_id);
              if(!$cek_singkatan)
              {
                  $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Mohon maaf. Singkatan Kota yang Anda masukan sudah terdaftar pada sistem.'
                  );
                  return json_encode($json_data);
              }

            }
          }

          if($enc_id){
             $kota = City::find($dec_id);
             $kota->name         = $req->name;
             $kota->provinsi_id  = $req->provinsi_id;
             $kota->abbreviation = $req->abbreviation;
             $kota->area_code    = $req->area_code;
             $kota->save();
            if($kota) {
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
            $kota = new City;
            $kota->name         = $req->name;
            $kota->provinsi_id  = $req->provinsi_id;
            $kota->abbreviation = $req->abbreviation;
            $kota->area_code    = $req->area_code;
            $kota->save();
            if($kota) {
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
        return json_encode($json_data);
      }
      public function hapus(Request $req,$enc_id)
      {
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $kota = City::find($dec_id);
        $cekexist = Member::where('city_id',$dec_id)->first();
        if($kota){
            if ($cekexist) {
                return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Kota sudah direlasikan dengan Member, Silahkan hapus dahulu Member yang terkait dengan Kota ini.']);
            }else{
                $kota->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
            }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }
}
