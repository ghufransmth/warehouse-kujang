<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Sales;
use App\Models\Member;
use App\Models\MemberSales;
use App\Models\PurchaseOrder;
use App\Models\Invoice;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class SalesController extends Controller
{
      protected $original_column = array(
            1 => "name",
            2 => "username",
            3 => "email",
            4 => "phone",
            5 => "created_at",
      );
      public function index()
      {
          return view('backend/master/sales/index');
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          $querydb = Sales::select('*');

          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $querydb->orderBy('id','DESC');
          }
           if($search) {
            $querydb->where(function ($query) use ($search) {
                    $query->orWhere('name','LIKE',"%{$search}%");
                    $query->orWhere('email','LIKE',"%{$search}%");
                    $query->orWhere('username','LIKE',"%{$search}%");
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

            if($request->user()->can('sales.detail')){
              $action.='<a href="'.route('sales.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            if($request->user()->can('sales.ubah')){
              $action.='<a href="'.route('sales.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('sales.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }
            if($request->user()->can('sales.resetapp')){
                $action.='<a href="#" onclick="resetApp(this,\''.$key.'\')" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Reset APP"><i class="fa fa-mobile"></i> Reset APP</a>&nbsp;';
            }
            $action.="</div>";

            $sales->no             = $key+$page;
            $sales->id             = $sales->id;
            $sales->enc_id         = $enc_id;
            $sales->name           = $sales->name;
            $sales->email          = $sales->email;
            $sales->username       = $sales->username;
            $sales->tgl            = $sales->created_at==null?'-':date('d-m-Y H:i',strtotime($sales->created_at));
            $sales->action         = $action;
          }
          if ($request->user()->can('sales.index')) {
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
       $cek = Sales::where('id','!=',$id)->where($column,'=',$var)->first();
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
        return view('backend/master/sales/form');
      }

      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id) {
          $sales= Sales::find($dec_id);
          return view('backend/master/sales/form',compact('enc_id','sales'));
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
          $sales= Sales::find($dec_id);
          $tgl_registrasi = $sales->created_at==null? '-':Carbon::parse($sales->created_at)->format('d/m/Y H:i');
          return view('backend/master/sales/detail',compact('enc_id','sales','tgl_registrasi'));
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
        $cek_kode = $this->cekExist('code',$req->code,$dec_id);
        $cek_mail = $this->cekExist('email',$req->email,$dec_id);
        $cek_username = $this->cekExist('username',$req->username,$dec_id);

        if(!$cek_kode)
        {
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Kode Sales yang Anda masukan sudah terdaftar pada sistem.'
                );

        }else if(!$cek_username)
        {
            $json_data = array(
            "success"         => FALSE,
            "message"         => 'Mohon maaf. Username yang Anda masukan sudah terdaftar pada sistem.'
            );
        }else if(!$cek_mail)
        {
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Mohon maaf. Email yang Anda masukan sudah terdaftar pada sistem.'
                );
        }
        else {
            if($enc_id){
                $sales = Sales::find($dec_id);
                // $sales->code        = $req->code;
                $sales->name        = $req->name;
                $sales->username    = $req->username;
                $sales->email       = $req->email;
                $sales->password    = bcrypt($req->password);
                $sales->phone       = $req->phone;
                $sales->address     = $req->alamat;
                $sales->flag_sales  = 0;
                $sales->npwp        = $req->npwp;
                $sales->ktp         = $req->ktp;
                $sales->jk          = $req->jk;
                $sales->no_rek      = $req->no_rek;
                $sales->bank_name   = $req->bank_name;
                $sales->holder_name = $req->holder_name;
                if($req->password!=""){
                    $sales->password    = bcrypt($req->password);
                }
                $sales->save();
                if($sales){
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
                $sales = new Sales;
                $sales->code        = $req->code;
                $sales->name        = $req->name;
                $sales->username    = $req->username;
                $sales->email       = $req->email;
                $sales->password    = bcrypt($req->password);
                $sales->phone       = $req->phone;
                $sales->address     = $req->alamat;
                $sales->flag_sales  = 0;
                $sales->npwp        = $req->npwp;
                $sales->ktp         = $req->ktp;
                $sales->jk          = $req->jk;
                $sales->no_rek      = $req->no_rek;
                $sales->bank_name   = $req->bank_name;
                $sales->holder_name = $req->holder_name;
                $sales->save();
                if($sales) {
                    //input member_sales juga yaa
                    $datamember = Member::all();
                    foreach($datamember as $key=>$member){
                        $member_sales = new MemberSales;
                        $member_sales->member_id = $member->id;
                        $member_sales->sales_id  = $sales->id;
                        $member_sales->active    =1;
                        $member_sales->save();
                    }
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
    $sales    = Sales::find($dec_id);
    $cekexistpo     = PurchaseOrder::where('sales_id',$dec_id)->where('flag_status',0)->first();
    $cekexistrpo    = PurchaseOrder::where('sales_id',$dec_id)->where('flag_status',1)->first();
    $cekexistbo     = PurchaseOrder::where('sales_id',$dec_id)->where('flag_status',2)->first();
    $cekexistinvoice= Invoice::where('sales_id',$dec_id)->first();
    if($sales) {
      if($cekexistpo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Sales sudah direlasikan dengan Transkasi PO, Silahkan hapus dahulu PO yang terkait dengan Sales ini.']);
      }else if($cekexistrpo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Sales sudah direlasikan dengan Transkasi RPO, Silahkan hapus dahulu RPO yang terkait dengan Sales ini.']);
      }else if($cekexistbo) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Sales sudah direlasikan dengan Transkasi BO, Silahkan hapus dahulu BO yang terkait dengan Sales ini.']);
      }else if($cekexistinvoice) {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Sales sudah direlasikan dengan Invoice, Silahkan hapus dahulu Invoice yang terkait dengan Sales ini.']);
      }else{
            $hapusmembersales = MemberSales::where('sales_id',$sales->id)->delete();
            $sales->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
      }
    }else {
        return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
    }
    }
    public function resetAPP(Request $req)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($req->enc_id));
        $sales    = Sales::find($dec_id);
        if($sales) {
            $sales->token = null;
            $sales->save();
            return response()->json(['status'=>"success",'message'=>'Reset APP Berhasil dilakukan.']);
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal Mereset APP. Silahkan ulangi kembali.']);
        }
    }
}
