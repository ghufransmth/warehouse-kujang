<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\KomponenBiaya;
use DB;
use Auth;

class KomponenController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    public function index(){
        return view('backend/master/komponen/index');
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }

    private function cekExist($column,$var,$id){
        $cek = KomponenBiaya::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    private function kategori(){
        $result = array(
            0 => 'Debit',
            1 => 'Kredit'
        );

        return $result;
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        
        $country = KomponenBiaya::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $country->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
        else{
            $country->orderBy('id','DESC');
        }
        if($search) {
            $country->where(function ($query) use ($search) {
                    $query->orWhere('name','LIKE',"%{$search}%");
            });
        }
        $totalData = $country->get()->count();

        $totalFiltered = $country->get()->count();

        $country->limit($limit);
        $country->offset($start);
        $data = $country->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";
        
            $action.="";
            $action.="<div class='btn-group'>";
            $action.='<a href="'.route('komponen.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            $action.="</div>";

            if($result->kategori == 0){
                $kategori = 'DEBIT';
            }else if($result->kategori == 1){
                $kategori = 'KREDIT';
            }

            $result->no             = $key+$page;
            $result->jenis          = $kategori;
            $result->action         = $action;
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),  
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        // if($request->user()->can('negara.index')) {
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => intval($totalData),
        //     "recordsFiltered" => intval($totalFiltered),
        //     "data"            => $data
        //   );
        // }else{
        //   $json_data = array(
        //     "draw"            => intval($request->input('draw')),  
        //     "recordsTotal"    => 0,
        //     "recordsFiltered" => 0,
        //     "data"            => []
        //   );
            
        // }    
        return json_encode($json_data); 
    }

    public function tambah(){
        $kategori = $this->kategori();
        $selectedKategori = '';

        return view('backend/master/komponen/form', compact('kategori','selectedKategori'));
    }

    public function simpan(Request $request){
        $enc_id     = $request->enc_id;
        
        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_data = $this->cekExist('name',$request->name,$dec_id);
        if(!$cek_data){
            return response()->json([
                'code'  => 409,
                'detail'    => [
                    'title' => 'Komponen',
                    'message'   => 'Data SUdah terdaftar'
                ]
            ]);
        }else{
            DB::beginTransaction();
            if($enc_id){
                $result = KomponenBiaya::find($dec_id);
                $result->name   = $request->name;
                $result->kategori   = $request->kategori;
                $result->save();

                $data = 'Terupdate';
                
            }else{
                $result = new KomponenBiaya;
                $result->name   = $request->name;
                $result->kategori   = $request->kategori;
                $result->save();

                $data = 'Tersimpan';
            }

            if($result){
                DB::commit();
                return response()->json([
                    'code'  => 201,
                    'detail'    => [
                        'title' => 'Komponen',
                        'message'   => 'Data Berhasil '.$data
                    ]
                ]);
            }else{
                DB::rollback();
                return response()->json([
                    'code'  => 409,
                    'detail'    => [
                        'title' => 'Komponen',
                        'message'   => 'Maaf Data Gagal disimpan silahlan tunggu beberapa saat lagi'
                    ]
                ]);
            }
        }

        return response()->json([
            'data'  => $request->all()
        ]);
    }

    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $komponen = KomponenBiaya::find($dec_id);
        if($komponen){
            $kategori = $this->kategori();
            $selectedKategori = $komponen->kategori;

            return view('backend/master/komponen/form', compact('enc_id','komponen','kategori','selectedKategori'));
        }else{
            return view('errors/noaccess');
        }
    }

    public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $komponen = KomponenBiaya::find($dec_id);
        if($komponen){
            $komponen->delete();
            return response()->json([
                'code'  => 202,
                'detail'    => [
                    'title' => 'Komponen',
                    'message'   => 'Data Berhasil Terhapus'
                ]
            ]);
        }else{
            return response()->json([
                'code'  => 404,
                'detail'    => [
                    'title' => 'Komponen',
                    'message'   => 'Maaf Data Tidak dapat ditemukan cobalah beberapa saat lagi'
                ]
            ]);
        }
    }

    public function get_komponen(Request $request){
        $query = KomponenBiaya::select('*')
            ->orWhere('code', 'LIKE', "%{$request->search}%")
            ->orWhere('name', 'LIKE', "%{$request->search}%")
            ->limit(10);

        $result = $query->get();

        return json_encode($result);
    }
}
