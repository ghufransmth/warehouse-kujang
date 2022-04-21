<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Driver;

use DB;
use Auth;

class DriverController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "username",
        3 => "email",
        4 => "phone",
        5 => "created_at",
    );

    private function cekExist($column,$var,$id){
        $cek = Driver::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    private function cekExistUser($column,$var,$id){
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

    public function index(){
        return view('backend/master/driver/index');
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $querydb = Driver::select('tbl_driver.id','tbl_driver.nama as name','users.username','users.email','users.no_hp','users.no_hp','users.created_at');
        $querydb->leftJoin('users','users.id','tbl_driver.user_id');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }else{
            $querydb->orderBy('tbl_driver.id','DESC');
        }
        if($search) {
            $querydb->where(function ($query) use ($search) {
                $query->orWhere('tbl_driver.nama','LIKE',"%{$search}%");
                $query->orWhere('users.email','LIKE',"%{$search}%");
                $query->orWhere('users.username','LIKE',"%{$search}%");
            });
        }

        $totalData = $querydb->get()->count();

        $totalFiltered = $querydb->get()->count();

        $querydb->limit($limit);
        $querydb->offset($start);
        $data = $querydb->get();

        foreach ($data as $key=> $sales)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($sales->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";

            $action.='<a href="'.route('driver.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            $action.="</div>";

            $sales->no             = $key+$page;
            $sales->enc_id         = $enc_id;
            $sales->phone          = $sales->no_hp;
            $sales->tgl            = $sales->created_at==null?'-':date('d F Y H:i',strtotime($sales->created_at));
            $sales->action         = $action;
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );

        return json_encode($json_data);
    }

    public function status(){
        $value = array('1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
    }

    public function tambah(){
        $status= $this->status();
        $selectedstatus   = '1';
        $roles = Role::orWhere('name','LIKE',"driver")->first();
        $roleselected = "";

        if($roles){
            return view('backend/master/driver/form', compact('roles','roleselected','status','selectedstatus'));
        }else{
            return view('backend/master/driver/index');
        }
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
            $dec_id = null;
        }

        $cek_mail = $this->cekExistUser('email',$req->email,$dec_id);
        $cek_username = $this->cekExistUser('username',$req->username,$dec_id);

        if(!$cek_username){
            $json_data = array(
            "success"         => FALSE,
            "message"         => 'Mohon maaf. Username yang Anda masukan sudah terdaftar pada sistem.'
            );
        }else if(!$cek_mail){
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Email yang Anda masukan sudah terdaftar pada sistem.'
            );
        }else{
          DB::BeginTransaction();
            if($enc_id){
                $driver = Driver::find($dec_id);

                $staff = User::find($driver->user_id);
                if($staff){
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
                }
                
                if($staff){
                    $driver->user_id     = $staff->id;
                    $driver->nama        = $req->name;
                    $driver->save();
                    DB::commit();

                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Data berhasil diperbarui.'
                    );
                }else{
                  DB::rollback();
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
                if($staff){
                    $driver = new Driver;
                    $driver->user_id     = $staff->id;
                    $driver->nama        = $req->name;
                    $driver->save();
                    
                    DB::commit();
                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Data berhasil ditambahkan.'
                    );
                }else{
                    DB::rollback();
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
        $driver= Driver::select('tbl_driver.*','users.*')->leftJoin('users','users.id','tbl_driver.user_id')->where('tbl_driver.id',$dec_id)->first();
        if($driver) {
            $status= $this->status();
            $selectedstatus   = $driver->status;
            $roles = Role::orWhere('name','LIKE',"driver")->first();
            $roleselected = $driver->flag_user;
            
            // return response()->json(['data' => $roles]);
            return view('backend/master/driver/form',compact('enc_id','driver','roles','roleselected','status','selectedstatus'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function hapus(Request $req, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $sales    = Driver::find($dec_id);

        if($sales) {
            $userdelete = User::find($sales->user_id);
            $userdelete->delete();
            $sales->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}
