<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SupplierController extends Controller
{
    protected $original_column = array(
        1 => "name",
      );

    function safe_encode($string) {
    $data = str_replace(array('/'),array('_'),$string);
    return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
    return $data;
    }

    public function index()
    {
        return view('backend/master/supplier/index');
    }

    private function cekExist($column,$var,$id){
        $cek = Supplier::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
      }

      public function getData(Request $req){
        $limit = $req->length;
        $start = $req->start;
        $page  = $start +1;
        $search = $req->search['value'];

        $supplier = Supplier::select('*');
        if(array_key_exists($req->order[0]['column'], $this->original_column)){
            $supplier->orderByRaw($this->original_column[$req->order[0]['column']].' '.$req->order[0]['dir']);
         }
          else{
           $supplier->orderBy('id','DESC');
         }
          if($search) {
           $supplier->where(function ($query) use ($search) {
                   $query->orWhere('nama','LIKE',"%{$search}%");
           });
         }
         $totalData = $supplier->get()->count();

         $totalFiltered = $supplier->get()->count();

         $supplier->limit($limit);
         $supplier->offset($start);
         $data = $supplier->get();
        //  return $data;
         foreach($data as $key=> $value)
         {
            $enc_id = $this->safe_encode(Crypt::encryptString($value->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";
            if($req->user()->can('supplier.ubah')){
                $action.='<a href="'.route('supplier.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($req->user()->can('supplier.hapus')){
                $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i>Hapus</a>&nbsp;';
            }
            $action.="</div>";

            $value->no = $key+$page;
            $value->id = $value->id;
            $value->nama = $value->nama;
            $value->action = $action;
         }
         if ($req->user()->can('supplier.index')) {
            $json_data = array(
                      "draw"            => intval($req->input('draw')),
                      "recordsTotal"    => intval($totalData),
                      "recordsFiltered" => intval($totalFiltered),
                      "data"            => $data
                    );

        }else{
             $json_data = array(
                      "draw"            => intval($req->input('draw')),
                      "recordsTotal"    => 0,
                      "recordsFiltered" => 0,
                      "data"            => []
                    );

        }
        return json_encode($json_data);
      }

      public function tambah(){

        return view('backend/master/supplier/form');
      }

      public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_gudang = $this->cekExist('nama',$req->name,$dec_id);
        if(!$cek_gudang){
            $json_data = array(
              "success"         => FALSE,
              "message"         => 'Mohon maaf. Supplier sudah terdaftar pada sistem.'
            );
        }else {
          if($enc_id){
            $supp = Supplier::find($dec_id);
            $supp->nama      = $req->name;
            $supp->save();
            if ($supp) {
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
            $supplier              = new Supplier;
            $supplier->nama        = $req->name;
            $supplier->save();
            if($supplier) {
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
          $supplier = Supplier::find($dec_id);

          return view('backend/master/supplier/form',compact('enc_id','supplier'));
        } else {
            return view('errors/noaccess');
        }
      }

      public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $brand    = Supplier::find($dec_id);
        $brand->delete();
        return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
    }
}
