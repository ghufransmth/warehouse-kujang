<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Sales;
use Illuminate\Support\Facades\Hash;
use Auth;

class LoginController extends Controller
{
	// index:halaman login
    public function index(){
    	return view('login');
    }
    // checkLogin::fungsi cek login
    public function checkLogin(Request $request){
    	$akun    = $request->input("akun");
        $password = $request->input("password");

        $akun = User::whereRaw("BINARY username='".$akun."'")->orWhereRaw("BINARY email='".$akun."'")->first();
        if ($akun) {
           if ($akun->status=='1') {
              if(Hash::check($password,$akun->password))  {
                  $akun->last_login_at = now();
                  $akun->last_login_ip = $request->ip();
                  $akun->save();
                  session(['profile' => $this->defaultProfilePhotoUrl($akun->fullname)]);
                  session(['namaakses' => $akun->namaAkses?$akun->namaAkses->name:'']);
                  if($akun->flag_user==11){
                    $datasales  = Sales::where('user_id',$akun->id)->first();
                    if(!$datasales){
                      Auth::logout();
                      $desc = 'Login gagal. Akun Anda belum direlasikan dengan Sales.';
                      app('App\Http\Controllers\LogActivityController')->create_log(1,null,'gagal',$desc,$akun);
                      return redirect()->route('manage.login')->with('message', ['status'=>'danger','desc'=>$desc]);
                    }
                  }else{
                    $datasales = "";
                  }
                  session(['idsales' => $datasales?$datasales->id:'']);
                  Auth::login($akun);
                  return redirect()->route('manage.beranda');
              } else {
                  $desc = 'Login gagal. Cek kembali email dan password Anda.';
                  return redirect()->route('manage.login')->with('message', ['status'=>'danger','desc'=>$desc]);
              }
           }if ($akun->status=='2'){
              $desc = 'Login gagal. Akun anda telah terblokir. Silahkan hubungi Admin.';
              return redirect()->route('manage.login')->with('message', ['status'=>'danger','desc'=>$desc]);
           }else{
             $desc = 'Login gagal. Akun anda belum terverifikasi. Silahkan melakukan verifikasi terlebih dahulu.';
             return redirect()->route('manage.login')->with('message', ['status'=>'danger','desc'=>$desc]);
           }
        }else{
            $desc = 'Login gagal. Akun tidak ditemukan di sistem kami.';
            return redirect()->route('manage.login')->with('message', ['status'=>'danger','desc'=>$desc]);
        }
    }
    // logout::fungsi logout
    public function logout(Request $request){
    	Auth::logout();
    	return redirect()->route('manage.login');
    }
    protected function defaultProfilePhotoUrl($name)
    {
        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=ffffff&background=54828d&rounded=true&length=2';
    }
}
