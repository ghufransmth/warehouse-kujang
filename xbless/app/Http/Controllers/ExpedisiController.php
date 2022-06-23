<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Expedisi;
use App\Models\PurchaseOrder;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class ExpedisiController extends Controller
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
          return view('backend/master/expedisi/index');
      }

      private function cekExist($column,$var,$id)
      {
       $cek = Expedisi::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
      }

      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];



          $dataquery = Expedisi::select('*');

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


            if($request->user()->can('expedisi.detail')){
              $action.='<a href="'.route('expedisi.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            if($request->user()->can('expedisi.ubah')){
              $action.='<a href="'.route('expedisi.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('expedisi.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
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
            $result->no_hp          = $result->telp_no==null?'-':$result->telp_no;
            $result->alamat         = $result->address==null?'-':$result->address;
            $result->tgl            = $result->created_at==null?'-':date('d-m-Y H:i',strtotime($result->created_at));
            $result->status         = $status;
            $result->action         = $action;
          }
          if ($request->user()->can('expedisi.index')) {
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
        $status= $this->status();
        $selectedstatus   = '1';
        return view('backend/master/expedisi/form',compact('status','selectedstatus'));
      }

      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $status= $this->status();
          $expedisi= Expedisi::find($dec_id);
          $selectedstatus   =  $expedisi->status;

          return view('backend/master/expedisi/form',compact('status','enc_id','selectedstatus','expedisi'));
        } else {
        	return view('errors/noaccess');
        }
      }

      public function detail(Request $request,$enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id) {
            $status   = $this->status();
            $expedisi = Expedisi::find($dec_id);
            $selectedstatus   =$expedisi->status;
            Carbon::setLocale('id');
            if ($expedisi->status=='1') {
                $status = '<span class="label label-primary">Aktif</span>';
            }else if($expedisi->status=='0'){
                $status = '<span class="label label-warning">Tidak Aktif</span>';
            }
            $tgl_buat = $expedisi->created_at==null? '-':Carbon::parse($expedisi->created_at)->format('d/m/Y H:i');
            return view('backend/master/expedisi/detail',compact('status','enc_id','selectedstatus','expedisi','tgl_buat'));
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

          $cek_expedisi_name = $this->cekExist('name',$req->name,$dec_id);
          if(!$cek_expedisi_name){
              $json_data = array(
                  "success"         => FALSE,
                  "message"         => 'Mohon maaf. Expedisi yang Anda masukan sudah terdaftar pada sistem.'
                  );
          }else{
            if($enc_id){
              $expedisi = Expedisi::find($dec_id);
              $expedisi->name     = $req->name;
              $expedisi->telp_no  = $req->telp_no;
              $expedisi->address  = $req->address;
              $expedisi->status   = $req->status;
              $expedisi->save();
             if($expedisi) {
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
             $expedisi = new Expedisi;
             $expedisi->name     = $req->name;
             $expedisi->telp_no  = $req->telp_no;
             $expedisi->address  = $req->address;
             $expedisi->status   = $req->status;
             $expedisi->save();
             if($expedisi) {
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
        $expedisi = Expedisi::find($dec_id);
        $cekexistpo     = PurchaseOrder::where('expedisi',$dec_id)->where('flag_status',0)->first();
        $cekexistrpo    = PurchaseOrder::where('expedisi',$dec_id)->where('flag_status',1)->first();
        $cekexistbo     = PurchaseOrder::where('expedisi',$dec_id)->where('flag_status',2)->first();
        if($expedisi){
          if($cekexistpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi sudah direlasikan dengan Transkasi PO, Silahkan hapus dahulu PO yang terkait dengan Expedisi ini.']);
          }else if($cekexistrpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi sudah direlasikan dengan Transkasi RPO, Silahkan hapus dahulu RPO yang terkait dengan Expedisi ini.']);
          }else if($cekexistbo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Expedisi sudah direlasikan dengan Transkasi BO, Silahkan hapus dahulu BO yang terkait dengan Expedisi ini.']);
          }else{
                $expedisi->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
          }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }
}
