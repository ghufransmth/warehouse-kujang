<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Toko;
use App\Models\KategoriToko;
use App\Models\Payment;
use App\Models\Distrik;
use App\Models\TypeChannel;
use App\Models\JenisToko;
use DB;
use Auth;

class TokoController extends Controller
{
    protected $original_column = array(
        1 => "name",
        2 => "nik"
    );

    public function index(){
        return view('backend/toko/index');
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
        $cek = Toko::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
      }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $dataquery = Toko::select('*');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $dataquery->orderBy('id','DESC');
        }
         if($search) {
          $dataquery->where(function ($query) use ($search) {
                  $query->orWhere('name','LIKE',"%{$search}%");
          });
        }
        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key=> $toko)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($toko->id));
          $action = "";

          $action.="";
          $action.="<div class='btn-group'>";
        //   if($request->user()->can('brand.ubah')){
            $action.='<a href="'.route('toko.edit',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
        //   }
        //   if($request->user()->can('toko.hapus')){
            $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
        //   }

          $action.="</div>";

          $toko->no             = $key+$page;
          $toko->id             = $toko->id;
          $toko->code           = $toko->code;
          $toko->name           = $toko->name;
          $toko->nik            = $toko->nik;
          $toko->telp           = $toko->telp;
          $toko->alamat         = $toko->alamat;
          $toko->action         = $action;
        }
        // if ($request->user()->can('toko.index')) {
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
              );
        // }else{
            // $json_data = array(
            //     "draw"            => intval($request->input('draw')),
            //     "recordsTotal"    => 0,
            //     "recordsFiltered" => 0,
            //     "data"            => []
            //   );

        // }
        return json_encode($json_data);
    }

    public function getDetail(Request $request)
    {
          $term = $request->value;
          $query = Toko::select('*');

          if($term){
              $query->where('id',$term);
          }
          $toko = $query->get();
          $out = [
              'results' => [],
              'pagination' => [
                  'more' => false
              ]
          ];
          foreach($toko as $value){
              array_push($out['results'], [
                  'id'   =>$value->id,
                  'text' =>$value->alamat
              ]);
          }
          return response()->json($out, 200);
    }

    public function tambah(){

        $distrik = Distrik::all();
        $selecteddistrik = "";
        $tipe_chanel = TypeChannel::all();
        $selectedtipechanel = "";
        $payments = Payment::all();
        $selectedpayment = "";
        $jenis_toko = JenisToko::all();
        $selectedjenistoko = "";
        $kategori_toko = KategoriToko::all();
        $selectedkategoritoko = "";
        return view('backend/toko/form',compact('distrik','selecteddistrik','tipe_chanel','selectedtipechanel','payments','selectedpayment','jenis_toko','selectedjenistoko','kategori_toko','selectedkategoritoko'));
    }

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_nama = $this->cekExist('name',$req->name,$dec_id);
        if(!$cek_nama){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Nama Brand sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $toko = Toko::find($dec_id);
            $toko->code        = $req->kode;
            $toko->name        = $req->name;
            $toko->nik        = $req->nik;
            $toko->alamat        = $req->alamat;
            $toko->telp        = $req->telp;
            $toko->distrik_id        = $req->distrik;
            $toko->tipe_chanel_id        = $req->tipe_chanel;
            $toko->jenis_toko_id        = $req->jenis_toko;
            $toko->payment_id        = $req->payment;
            $toko->kategori_toko_id        = $req->kategori;
            $toko->save();
            if($toko) {
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
            $toko              = new Toko;
            $toko->code        = $req->kode;
            $toko->name        = $req->name;
            $toko->nik        = $req->nik;
            $toko->alamat        = $req->alamat;
            $toko->telp        = $req->telp;
            $toko->distrik_id        = $req->distrik;
            $toko->tipe_chanel_id        = $req->tipe_chanel;
            $toko->jenis_toko_id        = $req->jenis_toko;
            $toko->payment_id        = $req->payment;
            $toko->kategori_toko_id        = $req->kategori;
            $toko->save();
            if($toko) {
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

    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if ($dec_id) {
          $toko = Toko::find($dec_id);
          $distrik = Distrik::all();
          $tipe_chanel = TypeChannel::all();
          $payments = Payment::all();
          $jenis_toko = JenisToko::all();
          $kategori_toko = KategoriToko::all();
          $selecteddistrik = $toko->distrik_id;
          $selectedtipechanel = $toko->tipe_chanel_id;
          $selectedpayment = $toko->payment_id;
          $selectedjenistoko = $toko->jenis_toko_id;
          $selectedkategoritoko = $toko->kategori_toko_id;
          return view('backend/toko/form',compact('enc_id','toko','distrik','selecteddistrik','tipe_chanel','selectedtipechanel','payments','selectedpayment','jenis_toko','selectedjenistoko','kategori_toko','selectedkategoritoko'));
        } else {
            return view('errors/noaccess');
        }
    }

    public function hapus(Request $request, $enc_id){
        try{
            $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
            $toko    = Toko::find($dec_id);
            $toko->delete();

            if($toko){
                $json_data = array(
                    "status"    =>  'success',
                    "message"   =>  'Data Berhasil Dihapus.'
                );
            }else{
                $json_data = array(
                    "status"    =>  'Gagal.',
                    "message"   =>  'Data Gagal Dihapus.'
                );
            }
        }catch(\Throwable $th){
            $json_data = array(
                "success"         => 'gagal',
                "message"         => $th->getMessage()
            );
        }
        return json_encode($json_data);
    }

    public function getToko(Request $request){
      $query = Toko::select('id', 'name');
      $query->orWhere('name', 'LIKE' , "%{$request->search}%");
      $query->limit(10);
      $result = $query->get();

      return json_encode($result);
  }
}
