<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class StaffController extends Controller
{
      protected $original_column = array(
        1 => "fullname",
        2 => "username",
        3 => "email",
        4 => "flag_user",
        5 => "status",
        6 => "created_at",
        7 => "status",
      );
      public function status()
      {
        $value = array('1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
      }
      public function statusFilter()
      {
         $value = array('99'=>'Semua','1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
      }

      public function index()
      {
          return view('backend/master/staff/index');
      }
   
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          $roles = Role::orWhere('name','LIKE',"sales")->first();

          $admins = User::select('id','fullname','email','username','flag_user','status','created_at');
          // $admins->where('flag_user', != );
          if($request->user()->id !=1){
             $admins->where('id','!=',1);
          }

          if($roles){
            $admins->where('flag_user','!=',$roles->id);
          }
          $admins->where('id','!=',$request->user()->id);
          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $admins->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $admins->orderBy('id','DESC');
          }
           if($search) {
            $admins->where(function ($query) use ($search) {
                    $query->orWhere('fullname','LIKE',"%{$search}%");
                    $query->orWhere('email','LIKE',"%{$search}%");
                    $query->orWhere('username','LIKE',"%{$search}%");
            });
          }
          $totalData = $admins->get()->count();
      
          $totalFiltered = $admins->get()->count();
      
          $admins->limit($limit);
          $admins->offset($start);
          $data = $admins->get();
          foreach ($data as $key=> $admin)
          {
            $enc_id = $this->safe_encode(Crypt::encryptString($admin->id));
            $action = "";
           
            $action.="";
            $action.="<div class='btn-group'>";
           
        
            if($request->user()->can('staff.detail')){
              $action.='<a href="'.route('staff.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            if($request->user()->can('staff.ubah')){
              $action.='<a href="'.route('staff.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('staff.hapus')){
              $action.='<a href="#" onclick="deleteStaff(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }
            $action.="</div>";
            if ($admin->status=='1') {
               $status = '<span class="label label-primary">Aktif</span>';
            }else if($admin->status=='0'){
               $status = '<span class="label label-warning">Tidak Aktif</span>';
            }else if($admin->status=='2'){
               $status = '<span class="label label-danger">Blokir</span>';
            }

            

            $admin->no             = $key+$page;
           
            $admin->id             = $admin->id;
            $admin->name           = $admin->fullname;
            $admin->email          = $admin->email;
            $admin->username       = $admin->username;
            $admin->namaakses      = $admin->namaAkses?$admin->namaAkses->name:'-';
            $admin->tgl            = $admin->created_at==null?'-':date('d-m-Y H:i',strtotime($admin->created_at));
            $admin->status         = $status;
            $admin->action         = $action;
          }
          if ($request->user()->can('staff.index')) {
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
     
    
      private function cekExist($column,$var,$id)
      {
       $cek = User::where('id','!=',$id)->where($column,'=',$var)->first();
       return (!empty($cek) ? false : true);
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
        $roles = Role::where('id','!=',1)->get();
        $roleselected = "";
        return view('backend/master/staff/form',compact('status','selectedstatus','roles','roleselected'));
      }
     
      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
       
        $roles = Role::where('id','!=',1)->get();
        if ($dec_id) {
          $status= $this->status();
          $staff= User::find($dec_id);
          $selectedstatus   =  $staff->status;
          $roleuser = DB::table('roleuser')->where('user_id', $staff->id)->first();
          if($roleuser){
            $roleselected = $roleuser->role_id;
          }else{
            $roleselected = "";
          }
          return view('backend/master/staff/form',compact('status','enc_id','selectedstatus','staff','roles','roleselected'));
        } else {
        	return view('errors/noaccess');
        }
      }

      protected function defaultProfilePhotoUrl($name)
   	  {
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=ffffff&background=54828d&rounded=true&length=2';
      }
      public function detail(Request $request,$enc_id)
      {  
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));  
        if ($dec_id) {
          $status= $this->status();
          $staff= User::find($dec_id);
          $selectedstatus   =  $staff->status;
          $roleuser = DB::table('roleuser')->where('user_id', $staff->id)->first();
          if($roleuser){
            $role = Role::select('name')->where('id',$roleuser->role_id)->first();
            $akses = $role->name;
          }else{
            $akses = "";
          }
          Carbon::setLocale('id');
          if ($staff->status=='1') {
            $status = '<span class="label label-primary">Aktif</span>';
          }else if($staff->status=='0'){
              $status = '<span class="label label-warning">Tidak Aktif</span>';
          }else if($staff->status=='2'){
              $status = '<span class="label label-danger">Blokir</span>';
          }

          $tgl_last_login = $staff->last_login_at==null? '-':Carbon::parse($staff->last_login_at)->format('d/m/Y H:i');
          $tgl_registrasi = $staff->created_at==null? '-':Carbon::parse($staff->created_at)->format('d/m/Y H:i');
        
          return view('backend/master/staff/detail',compact('akses','status','enc_id','selectedstatus','staff','tgl_last_login','tgl_registrasi','status'));
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
          $cek_mail = $this->cekExist('email',$req->email,$dec_id);
          $cek_username = $this->cekExist('username',$req->username,$dec_id);
          if(!$cek_mail)
          {
              $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Email yang Anda masukan sudah terdaftar pada sistem.'
              );
          }else if(!$cek_username)
          {
              $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Username yang Anda masukan sudah terdaftar pada sistem.'
              );
          }
          else {
          if($enc_id){
             $staff = User::find($dec_id);
             $staff->fullname    = $req->name;
             $staff->email       = $req->email;
             if($req->password!=""){
              $staff->password    = bcrypt($req->password);
             }
             $staff->username    = $req->username;
             $staff->address     = $req->alamat;
             $staff->no_hp       = $req->phone;
             $staff->flag_user   = $req->level;
             $staff->npwp        = $req->npwp;
             $staff->ktp         = $req->ktp;
             $staff->jk          = $req->jk;
             $staff->status      = $req->status;
             $staff->save();
            if ($staff) {
              DB::table('roleuser')
              ->where('user_id', $staff->id)
              ->update(['role_id' => $req->level]);
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


            $staff = new User;
            $staff->fullname    = $req->name;
            $staff->email       = $req->email;
            $staff->password    = bcrypt($req->password);
            $staff->username    = $req->username;
            $staff->address     = $req->alamat;

            $staff->no_hp       = $req->phone;
            $staff->flag_user   = $req->level;
            $staff->npwp        = $req->npwp;
            $staff->ktp         = $req->ktp;
            $staff->jk          = $req->jk;
            $staff->status      = $req->status;
            $staff->save();
            if($staff) {
              $roleUser = new RoleUser;
              $roleUser->role_id = $req->level;
              $roleUser->user_id = $staff->id;
              $roleUser->save();
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
        $staff    = User::find($dec_id);
        $cekexist = RoleUser::where('user_id',$dec_id)->first();
        if($staff) {
            if ($cekexist) {
                return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data dikarenakan ID STAFF tersebut masih digunakan oleh role/akses. Silahkan hapus dahulu di Keamanan->Manajemen Akses->Daftar User jika ingin menghapus data ini kembali.']);
            }else{
                $staff->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
            }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
      }

      public function profil()
      {
        $profil = User::find(Auth()->user()->id);
        if ($profil) {
          if ($profil->profil==null) {
            $profile=$this->defaultProfilePhotoUrl($profil->name);
          }else{
            $profile=url($profil->profil);
          }
          return view('backend/profil/index',compact('profil','profile'));
        }else{
          Abort('404');
        }
      }
     
      public function profilSimpan(Request $req)
      {     
          $id         = Auth()->user()->id;
          $cek_mail = $this->cekExist('email',$req->email,$id);

         if(!$cek_mail)
          {
              $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Email yang Anda masukan sudah terdaftar pada sistem.'
              );
          }
          else {
          if($id){
              $profil = User::find($id);
              $profil->fullname    = $req->name;
              $profil->email       = $req->email;
              $profil->username    = $req->username;
              $profil->address     = $req->alamat;
              $profil->no_hp       = $req->phone;
              $profil->npwp        = $req->npwp;
              $profil->ktp         = $req->ktp;
              $profil->jk          = $req->jk;
              $profil->save();
             
            if ($profil) {
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
        }
           return json_encode($json_data); 
      }
      
      public function profilPassword()
      {
        return view('backend/profil/newpassword');
      }

      public function profilNewPassword(Request $req)
      {     
          
          $profil = User::find(auth()->user()->id);
          //cek password lama
          if(Hash::check($req->password_old,$profil->password))  {
              $profil->password   = bcrypt($req->password_new);
              $profil->save();
              if ($profil) {
                $json_data = array(
                      "success"         => TRUE,
                      "message"         => 'Password berhasil diperbarui.'
                   );
              }else{
                 $json_data = array(
                      "success"         => FALSE,
                      "message"         => 'Password gagal diperbarui.'
                   );
              }
          }else {
              $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Password Lama yang Anda masukan salah.'
              );
          }
          return json_encode($json_data); 
      }
}
