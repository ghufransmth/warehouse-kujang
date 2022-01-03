<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\Role;
use App\Models\Sales;
use App\Models\Member;
use App\Models\MemberSales;
use App\Models\City;
use App\Models\TipeHarga;
use App\Models\PurchaseOrder;
use App\Models\Invoice;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class MemberController extends Controller
{
      protected $original_column = array(
            1 => "uniq_code",
            2 => "name",
            3 => "city",
            4 => "npwp",
            5 => "phone",
      );
      public function index()
      {
          return view('backend/master/member/index');
      }
      public function getData(Request $request)
      {
          $limit = $request->length;
          $start = $request->start;
          $page  = $start +1;
          $search = $request->search['value'];

          $querydb = Member::select('*');

          if(array_key_exists($request->order[0]['column'], $this->original_column)){
             $querydb->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
          }
           else{
            $querydb->orderBy('id','DESC');
          }
           if($search) {
            $querydb->where(function ($query) use ($search) {
                    $query->orWhere('uniq_code','LIKE',"%{$search}%");
                    $query->orWhere('name','LIKE',"%{$search}%"); 
                    $query->orWhere('npwp','LIKE',"%{$search}%");
                    $query->orWhere('phone','LIKE',"%{$search}%");
                    $query->orWhere('username','LIKE',"%{$search}%");
            });
          }
          $totalData = $querydb->get()->count();

          $totalFiltered = $querydb->get()->count();

          $querydb->limit($limit);
          $querydb->offset($start);
          $data = $querydb->get();
          foreach ($data as $key=> $member)
          {
            $enc_id = $this->safe_encode(Crypt::encryptString($member->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";

            if($request->user()->can('member.detail')){
              $action.='<a href="'.route('member.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail" data-original-title="Show"><i class="fa fa-eye"></i> View</a>&nbsp';
            }
            // if($request->user()->can('member.simpan_member_sales')){
            //   $action.='<a href="#modal_sales"  id="addsales" role="button" data-id="'.$member->id.'" data-toggle="modal" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip salesdata"><i class="fa fa-users"></i> Sales</a>&nbsp';
            // }
            if($request->user()->can('member.ubah')){
              $action.='<a href="'.route('member.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('member.hapus')){
              $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }
            // if($request->user()->can('member.resetapp')){
            //     $action.='<a href="#" onclick="resetApp(this,\''.$key.'\')" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Reset APP"><i class="fa fa-mobile"></i> Reset APP</a>&nbsp;';
            // }
            $action.="</div>";

            $member->no             = $key+$page;
            $member->id             = $member->id;
            $member->enc_id         = $enc_id;
            $member->code           = $member->uniq_code==null?'-':$member->uniq_code;
            $member->name           = $member->name;
            $member->isinpwp        = $member->npwp==null?'-':$member->npwp;
            $member->username       = $member->username;
            $member->tgl            = $member->created_at==null?'-':date('d-m-Y H:i',strtotime($member->created_at));
            $member->action         = $action;
          }
          if ($request->user()->can('member.index')) {
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
       $cek = Member::where('id','!=',$id)->where($column,'=',$var)->first();
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
        $city      = City::all();
        $selectedcity ="";
        $tipeharga = TipeHarga::all();
        $selectedtipeharga ="9";
        return view('backend/master/member/form',compact('city','tipeharga','selectedcity','selectedtipeharga'));
      }

      public function ubah($enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id) {
          $member= Member::find($dec_id);
          $city      = City::all();
          $selectedcity =$member->city_id;
          $tipeharga = TipeHarga::all();
          $selectedtipeharga =$member->operation_price;
          return view('backend/master/member/form',compact('enc_id','member','city','tipeharga','selectedcity','selectedtipeharga'));
        } else {
          return view('errors/noaccess');
        }
      }

      public function detail(Request $request,$enc_id)
      {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $member= Member::find($dec_id);
          $tgl_registrasi = $member->created_at==null? '-':Carbon::parse($member->created_at)->format('d/m/Y H:i');
          return view('backend/master/member/detail',compact('enc_id','member','tgl_registrasi'));
        } else {
        	return view('errors/noaccess');
        }
      }
      public function getCode($abbreviation)
      {
        $next_no = '';

          $max_code = Member::where('uniq_code','LIKE',"%{$abbreviation}%")->max('id');
          if ($max_code) {
              $data = Member::find($max_code);
              $ambil = substr($data->uniq_code, -4);
          }
          if ($max_code==null) {
            $next_no = '0001';
          }elseif (strlen($ambil)<4) {
            $next_no = '0001';
          }elseif ($ambil == '9999') {
            $next_no = '0001';
          }else {
            $next_no = substr('0000', 0, 4-strlen($ambil+1)).($ambil+1);
          }
          return $abbreviation.'.'.$next_no;

      }
      public function member_sales(Request $request){
        $dec_id   = $request->enc_id;
        $sales    = Sales::all();
        $member   = Member::where('id', $dec_id)->first();
        foreach ($sales as $key => $value) {
          $ceksales = MemberSales::where('sales_id', $value->id)->where('member_id', $dec_id)->first();
          $list = "";
          if($ceksales){
            $checked = 'checked';
          }else{
            $checked = '';
          }
          $list.='<div><label> <input type="checkbox" '.$checked.' name="salesid" value="'.$value->id.'"> '.$value->name.'</label></div>';
          $value->aksi = $list;
        }
        return response()->json([
          'datalist' => $sales,
          'member' => $member
        ]);

      }

      public function simpan_member_sales(Request $request){
        $member_id   = $request->member_id;

        $datasales   = $request->sales_id;

        foreach ($datasales as $key => $value) {
          $cek = MemberSales::where('member_id', $member_id)->where('sales_id', $value)->first();
          if($cek == null){
            $simpan = new MemberSales;
            $simpan->member_id = $member_id;
            $simpan->sales_id  = $value;
            $simpan->save();
          }
        }

        $desc = 'data berhasil diperbarui';

        $cek_remove = MemberSales::where('member_id', $member_id)->whereNotIn('sales_id', $datasales)->delete();

        return response()->json([
          "code" => 200,
          "message" => $desc,
          "messageRmv" => $cek_remove
        ]);
      }
    public function simpan(Request $req)
    {
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
            $dec_id = null;
        }

        // $cek_mail = $this->cekExist('email',$req->email,$dec_id);
        $cek_username = $this->cekExist('username',$req->username,$dec_id);
        if($req->username !=""){
             $cek_username = $this->cekExist('username',$req->username,$dec_id);
        }else{
            $cek_username = 1;
        }

        if(!$cek_username)
        {
            $json_data = array(
            "success"         => FALSE,
            "message"         => 'Mohon maaf. Username yang Anda masukan sudah terdaftar pada sistem.'
            );
        }
        // else if(!$cek_mail)
        // {
        //     $json_data = array(
        //         "success"         => FALSE,
        //         "message"         => 'Mohon maaf. Email yang Anda masukan sudah terdaftar pada sistem.'
        //         );
        // }
        else {
            if($enc_id){
                $member = Member::find($dec_id);
                if($member->city_id!=$req->city){
                  $city         = City::find($req->city);
                  $singkatan    = $city->abbreviation;
                  $namaprovinsi = $city->getprovinsi->name;

                  if($singkatan){
                    $member->uniq_code  = $this->getCode($singkatan);
                  }else{
                    $member->uniq_code  ="";
                  }
                  $member->city_id     = $req->city;
                  $member->city        = $city->name;
                  $member->prov        = $namaprovinsi;
                }
                $member->name        = $req->name;
                $member->username    = $req->username;
                $member->email       = $req->email;
                $member->phone       = $req->phone;
                $member->address     = $req->alamat;
                $member->address_toko= $req->alamat_toko;
                $member->npwp        = $req->npwp;
                $member->ktp         = $req->ktp;
                $member->operation_price= $req->type_price;
                $member->no_rek      = $req->no_rek;
                $member->bank_name   = $req->bank_name;
                $member->holder_name = $req->holder_name;
                $member->updated_by  = Auth()->user()->username;
                if($req->password!=""){
                    $member->password    = bcrypt($req->password);
                }
                $member->save();
                if($member){
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
                $city         = City::find($req->city);
                $singkatan    = $city->abbreviation;
                $namaprovinsi = $city->getprovinsi->name;

                $member = new Member;
                if($singkatan){
                  $member->uniq_code  = $this->getCode($singkatan);
                }else{
                  $member->uniq_code  ="";
                }
                $member->name        = $req->name;
                $member->username    = $req->username;
                $member->email       = $req->email;
                $member->password    = bcrypt($req->password);
                $member->phone       = $req->phone;
                $member->address     = $req->alamat;
                $member->address_toko= $req->alamat_toko;
                $member->npwp        = $req->npwp;
                $member->ktp         = $req->ktp;
                $member->city_id     = $req->city;
                $member->city         = $city->name;
                $member->prov         = $namaprovinsi;
                $member->operation_price= $req->type_price;
                $member->no_rek      = $req->no_rek;
                $member->bank_name   = $req->bank_name;
                $member->holder_name = $req->holder_name;
                $member->created_by  = Auth()->user()->username;
                $member->save();
                if($member) {
                    //input member_sales juga yaa
                    $datasales = Sales::all();
                    foreach($datasales as $key=>$sales){
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
        $member    = Member::find($dec_id);
        $cekexistpo     = PurchaseOrder::where('member_id',$dec_id)->where('flag_status',0)->first();
        $cekexistrpo    = PurchaseOrder::where('member_id',$dec_id)->where('flag_status',1)->first();
        $cekexistbo     = PurchaseOrder::where('member_id',$dec_id)->where('flag_status',2)->first();
        $cekexistinvoice= Invoice::where('member_id',$dec_id)->first();
        if($member) {
        if($cekexistpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Member sudah direlasikan dengan Transkasi PO, Silahkan hapus dahulu PO yang terkait dengan Member ini.']);
        }else if($cekexistrpo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Member sudah direlasikan dengan Transkasi RPO, Silahkan hapus dahulu RPO yang terkait dengan Member ini.']);
        }else if($cekexistbo) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Member sudah direlasikan dengan Transkasi BO, Silahkan hapus dahulu BO yang terkait dengan Member ini.']);
        }else if($cekexistinvoice) {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Member sudah direlasikan dengan Invoice, Silahkan hapus dahulu Invoice yang terkait dengan Member ini.']);
        }else{
                $hapusmembersales = MemberSales::where('member_id',$member->id)->delete();
                $member->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
        }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
    public function resetAPP(Request $req)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($req->enc_id));
        $member    = Member::find($dec_id);
        if($member) {
            $member->token = null;
            $member->save();
            return response()->json(['status'=>"success",'message'=>'Reset APP Berhasil dilakukan.']);
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal Mereset APP. Silahkan ulangi kembali.']);
        }
    }
}
