<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

use App\Models\User;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Permission;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;

class RoleController extends Controller
{
    protected $original_column = array(
        1 => "name"
    );

    public function index(Request $request)
    {
        return view('backend/role/index');
    }
    
    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $querydata = Role::select('*');

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $querydata->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $querydata->orderBy('id','DESC');
        }
        if($request->user()->id !=1){
          $querydata->where('id','!=',1);
        }
        if($search) {
          $querydata->where(function ($query) use ($search) {
                  $query->orWhere('name','LIKE',"%{$search}%");
          });
        }
        $totalData = $querydata->get()->count();
    
        $totalFiltered = $querydata->get()->count();
  
        $querydata->limit($limit);
        $querydata->offset($start);
        $data = $querydata->get();
        foreach ($data as $key=> $result)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
          $action = "";
         
          $action.="";
          $action.="<div class='btn-group'>";
          if($request->user()->can('role.user')){
            $action.='<a href="'.route('role.user',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Daftar User" data-original-title="Show"><i class="fa fa-user"></i> User</a>&nbsp';
          }
          if($request->user()->can('role.ubah')){
            $action.='<a href="'.route('role.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
          }
          if($request->user()->can('role.hapus')){
            $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
          }
          $action.="</div>";

               
          $result->no             = $key+$page;
          $result->id             = $result->id;
          $result->name           = $result->name;
          $result->action         = $action;
        }
        if ($request->user()->can('role.index')) {
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

    public function form($enc_id = null)
    {
      if ($enc_id==null) {
            $dataSet ="";
      }else{
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
          $dataSet = Role::find($dec_id);
      }
      $permissionObj = Permission::select(
                DB::raw("*, CHAR_LENGTH(REPLACE(nested,'.','')) -1 AS depth,
                    CASE
                        WHEN (CHAR_LENGTH(REPLACE(nested,'.','')) -1) > 0
                        THEN SUBSTRING(nested, 1, CHAR_LENGTH(nested) - 2)
                        ELSE null
                    END AS parent")
                )
            ->orderByRaw("1*SUBSTRING_INDEX(nested, '.', 1)","ASC")
            ->get();

      if($enc_id){
        foreach ($permissionObj as $row) {
           $access = $row->role()->where('roles.id',$dec_id)->exists();
           if($access) {
                $row->access = 1;
           }else{
                $row->access = 0;
           }
        }
      }
      $checkbox_loop = $this->checkbox_loop($permissionObj);
      return view('backend/role/form',['dataSet' => $dataSet, 'permissionObj' => $permissionObj, 'checkbox_loop' => $checkbox_loop]);
    }

    private function checkbox_loop($data,$nested=null,$loop=0)
    {
        $parent = array_unique(Arr::flatten(Arr::pluck($data, 'parent')));
        $return = '<ul class="no-style" style="list-style: none">'."\n";
        foreach ($data as $row) {
            $hasChild = array_search($row->nested,$parent);

            if($row->parent == $nested) {
                $return .= '<li data-id="'.$row->nested.'">'."\n";
                $return .= '    <div class="checkbox">'."\n";
                $return .= '        <label>'."\n";
                $return .= '            <input type="checkbox" class="flat-red check_tree" data-id="'.$row->slug.'" name="permission['.$row->id.']" '.($row->access ? "checked" : "").'> '.$row->name."\n";
                $return .= '        </label>'."\n";
                $return .= '    </div>'."\n";
                if($hasChild) {
                    $return .= $this->checkbox_loop($data,$row->nested);
                }
                $return .= '</li>'."\n";
            }
        }
        $return .= '</ul>'."\n";

        return $return;
    }

    public function formuser($enc_id = null)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $dataSet = Role::find($dec_id);

        $userObj = DB::table('users')
            ->leftJoin('roleuser', 'users.id', '=', 'roleuser.user_id')
            ->whereNull('roleuser.role_id')
            ->get();
       

        return view('backend/role/formuser',['dataSet' => $dataSet,'userObj' => $userObj]);
    }

    public function save(Request $request, $id = null)
    {
         
        if ($id) {
            $role = Role::find($id);
            $role->name      = $request->name;
            $role->save();

            $desc = "Akses telah sukses diubah.";
        }else {
            $role = new Role;
            $role->name  = $request->name;
            $role->save();

            $desc = "Akses baru telah sukses ditambah.";
        }

        $id = $role->id;
        $permission = $request->permission;
        $role->permission()->detach();
        if($permission) {
            foreach ($permission as $rkey => $rval) {
                $menu[]  = ['permission_id'=>$rkey];
            }
            $role->permission()->attach($menu);
        }

        return redirect()->route('role.index')->with('message', ['status'=>'success','desc'=>$desc]);
    }

    public function saveuser(Request $request, $id = null)
    {
        $enc =$this->safe_encode(Crypt::encryptString($id));
        $role = Role::find($id);
        if ($request->tambah_user) {
            $role->user()->syncWithoutDetaching([$request->tambah_user]);
            $desc = "User telah sukses ditambah.";
            $ubah = User::find($request->tambah_user);
            $ubah->flag_user = $id;
            $ubah->save();
        }elseif ($request->hapus_user) {
            $role->user()->detach($request->hapus_user);
            $desc = "User telah sukses dihapus.";
            $ubah = User::find($request->hapus_user);
            $ubah->flag_user = null;
            $ubah->save();
        }else{
            $desc = null;
        }

        return redirect()->route('role.user',$enc)->with('message', ['status'=>'success','desc'=>$desc]);
    }

    public function delete(Request $request, $id)
    {
        $dec_id   = $this->safe_decode(Crypt::decryptString($id));
        $role     = Role::find($dec_id);
        $cekexist = RoleUser::where('role_id',$dec_id)->first();
        if($role) {
            if ($cekexist) {
                return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data dikarenakan Akses tersebut masih digunakan oleh user. Silahkan hapus dahulu di keamanan->Manajemen Akses->Daftar User jika ingin menghapus data ini kembali.']);
            }else{
                $role->delete();
                return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
            }
        }else {
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }
}
