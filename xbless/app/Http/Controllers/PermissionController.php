<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Route;
use File;
use App\Models\Permission;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $dataObj = Permission::orderByRaw("1*SUBSTRING_INDEX(nested, '.', 1)","ASC")->get();
      
        if ($request->user()->can('permission.index')) {
            return view('backend/permission/index',['dataObj' => $dataObj]  );
        }
    }

   

    public function tambah()
    {
       
        return view('backend/permission/form');
    }

    public function ubah($enc_id)
    {
      $dec_id = Crypt::decrypt($enc_id);
    
      if ($dec_id) {
       
        $permission= Permission::find($dec_id);
        return view('backend/permission/form',compact('enc_id','permission'));
      } else {
          return view('errors/noaccess');
      }
    }
    private function cekExist($column,$var,$id)
    {
     $cek = Permission::where('id','!=',$id)->where($column,'=',$var)->first();
     return (!empty($cek) ? false : true);
    }

    public function safe_encode($string) {
      $data = str_replace(array('/'),array('_'),$string);
      return $data;
    }
 
    public function safe_decode($string,$mode=null) {
      $data = str_replace(array('_'),array('/'),$string);
      return $data;
    }

    public function simpan(Request $req,$enc_id=null)
    {     
        if ($enc_id != null) {
          $dec_id = Crypt::decrypt($enc_id);
        }else{
          $dec_id = null;
        }

        $cekslug = $this->cekExist('slug',$req->slug,$dec_id);
        $cekurutan = $this->cekExist('nested',$req->urutan,$dec_id);

        if(!$cekslug)
        {
              $desc = 'Mohon maaf. slug yang Anda masukan sudah terdaftar pada sistem.';
              if ($dec_id) {
                  return redirect()->route('permission.ubah',$enc_id)->with('message', ['status'=>'danger','desc'=>$desc]);
              }else{
                  return redirect()->route('permission.tambah')->with('message', ['status'=>'danger','desc'=>$desc]);
              } 
              
        }elseif(!$cekurutan)
        {
              $desc = 'Mohon maaf. Urutan yang Anda masukan sudah terdaftar pada sistem.';
              if ($dec_id) {
                  return redirect()->route('permission.ubah',$enc_id)->with('message', ['status'=>'danger','desc'=>$desc]);
              }else{
                  return redirect()->route('permission.tambah')->with('message', ['status'=>'danger','desc'=>$desc]);
              } 
        }
        else {
        if($enc_id){
           
           $permission = Permission::find($dec_id);
           $permission->name        = $req->name;
           $permission->slug        = $req->slug;
           $permission->nested      = $req->urutan;
           $permission->save();
           $desc = 'Data berhasil diperbarui.';
        }else{

          $permission = new Permission;
          $permission->name        = $req->name;
          $permission->slug        = $req->slug;
          $permission->nested      = $req->urutan;
          $permission->save();
          
          $desc = 'Data berhasil ditambahkan.';
        }
      }
      return redirect()->route('permission.index')->with('message', ['status'=>'success','desc'=>$desc]);
    }

    public function sidebar()
    {
        
        $permissionObj = Permission::select(
                DB::raw("*, CHAR_LENGTH(REPLACE(nested,'.','')) -1 AS depth,
                    CASE
                        WHEN (CHAR_LENGTH(REPLACE(nested,'.','')) -1) > 0
                        THEN SUBSTRING(nested, 1, CHAR_LENGTH(nested) - 2)
                        ELSE null
                    END AS parent")
                )
            ->where('asmenu', '=', 1)
            ->orderByRaw("1*SUBSTRING_INDEX(nested, '.', 1)","ASC")
            ->get();
        $data = $this->sidebar_loop($permissionObj);
        $dirpath = base_path().'/resources/views/includes/';
        if(!File::isDirectory($dirpath)) File::makeDirectory($dirpath, 0775, true);
        if(File::exists($dirpath.'sidebar-dynamic.blade.php')) File::delete($dirpath.'sidebar-dynamic.blade.php');

        File::put($dirpath.'sidebar-dynamic.blade.php',$data);
        return redirect()->route('permission.index')->with('message', ['status'=>'success','desc'=>"Menu Sidebar telah sukses diperbarui."]);
    }

    private function sidebar_loop($data,$nested=null,$loop=0)
    {
        $parent = array_unique(Arr::flatten(Arr::pluck($data, 'parent')));
        $return = "";

        foreach ($data as $row) {
            $hasChild = array_search($row->nested,$parent);

            if($row->parent == $nested) {
                if($row->parent == null) {
                    $return .= '@can(\''.$row->slug.'\')'."\n";
                    $return .= '<li class="nav-item has-treeview">'."\n";
                    $return .= '<a href="#" class="nav-link">'."\n";
                    $return .= '<i class="nav-icon fas '.$row->icon.'"></i>'."\n";
                    $return .= '<p>'.$row->name.' <i class="right fas fa-angle-left"></i></p>'."\n";
            //         $return .='<span class="pull-right-container">
            //   <i class="fa fa-angle-left pull-right"></i>
            // </span>'."\n";
                    $return .= '</a>'."\n";
                    if($hasChild) {
                       
                        $return .= '<ul class="nav nav-treeview">'."\n";
                        $return .= $this->sidebar_loop($data,$row->nested);
                        $return .= '</ul>'."\n";
                    }
                    $return .= '</li>'."\n";
                    $return .= '@endcan'."\n";
                }else{
                    if(Route::has($row->slug)){
                        $return .= '@can(\''.$row->slug.'\')'."\n";
                        $return .= '<li class="nav-item">'."\n";
                        $return .= '<a href="{{ route(\''.$row->slug.'\') }}" class="nav-link" >'."\n";
                    }else{
                        $return .= '@can(\''.$row->slug.'\')'."\n";
                        $return .= '<li>'."\n";
                        $return .= '<a href="#">'."\n";
                    }
                    if($row->depth == 1) {
                        $return .= ''."\n";
                    }
                    $return .= '<p style="margin-left:30px">'.$row->name.'</p>'."\n";
                    if($hasChild) {
                        $return .= '<i class="right fas fa-angle-left"></i></p>';
                    }
                    $return .= '</a>'."\n";
                    if($hasChild) {
                       
                        $return .= '<ul class="nav nav-treeview">'."\n";
                        $return .= $this->sidebar_loop($data,$row->nested);
                        $return .= '</ul>'."\n";
                    }
                    $return .= '</li>'."\n";
                    $return .= '@endcan'."\n";
                    // if(Route::has($row->slug)){
                    //     $return .= '@endcan'."\n";
                    // }
                }
            }
        }
        return $return;
    }
}
