<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Diskon;
use App\Models\DiskonDetail;
use App\Models\Satuan;
use App\Models\Product;

use DB;
use Auth;

class DiskonController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    public function index(){
        return view('backend/master/diskon/index');
    }

    function safe_encode($string) {
      $data = str_replace(array('/'),array('_'),$string);
      return $data;
    }

    function safe_decode($string,$mode=null) {
      $data = str_replace(array('_'),array('/'),$string);
      return $data;
    }

    private function cekExist($column,$var,$id, $parent){
      $cek = DiskonDetail::where('id','!=',$id)->where($column,'=',$var)->where('flag_diskon', $parent)->first();
      return (!empty($cek) ? false : true);
    }

    private function flag_diskon(){
        $result = array(
            0 => 'Distributor',
            1 => 'Principal'
        );

        return $result;
    }

    public function tambah(){
        $parent = $this->flag_diskon();
        $jenis = $this->jenis_diskon();
        $kelipatan = $this->kelipatan();
        $satuan = Satuan::all();

        $selectedParent = '';
        $selectedSatuan = '';
        $selectedJenis = '';
        $selectedKelipatan = '';
        $selectedSatuanBonus = '';

        return view('backend/master/diskon/form', compact('parent', 'jenis', 'kelipatan', 'satuan',
                    'selectedParent','selectedSatuan', 'selectedJenis','selectedKelipatan','selectedSatuanBonus'));
    }

    private function jenis_diskon(){
        $diskon = array(
            0 => 'Diskon Uang',
            1 => 'Diskon Barang'
        );

        return $diskon;
    }

    private function kelipatan(){
        $diskon = array(
            0 => 'Berlaku Kelipatan',
            1 => 'Tidak Berlaku Kelipatan'
        );

        return $diskon;
    }

    private function check_diskon($id){
        $diskon = Diskon::where('id', $id)->first();

        return $diskon;
    }

    private function get_product_name($id){
        $product = Product::find($id);

        return isset($product) ? $product->nama : '';
    }

    private function get_product_satuan($id){
        $product = Satuan::find($id);

        return isset($product) ? $product->nama : '';
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $query = DiskonDetail::select('diskon_detail.*','tbl_product.nama as name_product', 'tbl_satuan.nama as satuan_name');
        $query->leftJoin('tbl_product', 'tbl_product.id', 'diskon_detail.produk');
        $query->leftJoin('tbl_satuan','tbl_satuan.id','diskon_detail.satuan');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $query->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $query->orderBy('diskon_detail.id','DESC');
        }
         if($search) {
          $query->where(function ($query) use ($search) {
                  $query->orWhere('diskon_detail.name','LIKE',"%{$search}%");
          });
        }
        $totalData = $query->get()->count();

        $totalFiltered = $query->get()->count();

        $query->limit($limit);
        $query->offset($start);
        $data = $query->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('diskon.ubah')) {
                $action.='<a href="'.route('diskon.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('diskon.delete')) {
                $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }
            $action.="</div>";

            if($result->flag_diskon == 0){
                $flag = 'Distributor';
            }else if($result->flag_diskon == 1){
                $flag = 'Principal';
            }

            $result->no             = $key+$page;
            $result->category       = $flag;

            if(strtolower($result->flag_diskon) == 0){
                $keterangan = 'Minimal Pembelian '.'Rp. '.number_format($result->min_beli,0,",",".").' - '.'Rp. '.number_format($result->max_beli,0,",",".").' ( sebelum PPN ) Mendapatkan diskon sebesar '.($result->nilai_diskon).' %';
            }else if(strtolower($result->flag_diskon) == 1){
                if($result->kelipatan == 0){
                    $kelipatan = ' Dengan Kelipatan ';
                }else{
                    $kelipatan = '';
                }
                $text = 'Setiap Pembelian '.$kelipatan.' '.$result->jml_produk.' '.strtoupper($result->satuan_name).' '.$result->name_product;
                if($result->nilai_diskon != null){
                    $extra = 'Mendapatkan Diskon '.$result->nilai_diskon.' %';
                }else{
                    $extra = 'Mendapatkan Bonus '.$result->jml_bonus.' '.strtoupper($this->get_product_satuan($result->satuan_bonus)).' '.$this->get_product_name($result->bonus_produk).' ';
                }
                $keterangan = $text.' '.$extra;
            }

            $result->keterangan     = $keterangan;
            $result->action         = $action;
        }

        if($request->user()->can('diskon.index')) {
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

    public function simpan(Request $req){
        $enc_id     = $req->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }

        $cek_negara = $this->cekExist('name',$req->name,$dec_id, $req->parent);
        if($enc_id){
            $detail     = DiskonDetail::where('id', $dec_id)->first();
            $detail->name  = $req->name;
            $detail->flag_diskon    = $req->parent;
            $detail->min_beli   = isset($req->min_beli)? $req->min_beli : null;
            $detail->max_beli   = isset($req->max_beli)? $req->max_beli : null;
            $detail->nilai_diskon   = isset($req->nilai_diskon)? $req->nilai_diskon : null;
            $detail->jenis_diskon   = isset($req->jenis_diskon)? $req->jenis_diskon : null;
            $detail->kelipatan  = isset($req->kelipatan)? $req->kelipatan : null;
            $detail->produk = isset($req->produk)? $req->produk : null;
            $detail->jml_produk = isset($req->jml_produk)? $req->jml_produk : null;
            $detail->satuan = isset($req->satuan)? $req->satuan : null;
            $detail->bonus_produk   = isset($req->bonus_produk)? $req->bonus_produk : null;
            $detail->jml_bonus  = isset($req->jml_bonus)? $req->jml_bonus : null;
            $detail->satuan_bonus = isset($req->satuan_bonus)? $req->satuan_bonus : null;
            $detail->tgl_dari = isset($req->tgl_dari)? date('Y-m-d',strtotime($req->tgl_dari)) : null;
            $detail->tgl_sampai = isset($req->tgl_sampai)? date('Y-m-d',strtotime($req->tgl_sampai)) : null;
            $detail->save();
            if($detail){

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
        }else{
            $detail     = new DiskonDetail;
            $detail->name  = $req->name;
            $detail->flag_diskon    = $req->parent;
            $detail->min_beli   = isset($req->min_beli)? $req->min_beli : null;
            $detail->max_beli   = isset($req->max_beli)? $req->max_beli : null;
            $detail->nilai_diskon   = isset($req->nilai_diskon)? $req->nilai_diskon : null;
            $detail->jenis_diskon   = isset($req->jenis_diskon)? $req->jenis_diskon : null;
            $detail->kelipatan  = isset($req->kelipatan)? $req->kelipatan : null;
            $detail->produk = isset($req->produk)? $req->produk : null;
            $detail->jml_produk = isset($req->jml_produk)? $req->jml_produk : null;
            $detail->satuan = isset($req->satuan)? $req->satuan : null;
            $detail->bonus_produk   = isset($req->bonus_produk)? $req->bonus_produk : null;
            $detail->jml_bonus  = isset($req->jml_bonus)? $req->jml_bonus : null;
            $detail->satuan_bonus = isset($req->satuan_bonus)? $req->satuan_bonus : null;
            $detail->tgl_dari = isset($req->tgl_dari)? date('Y-m-d',strtotime($req->tgl_dari)) : null;
            $detail->tgl_sampai = isset($req->tgl_sampai)? date('Y-m-d',strtotime($req->tgl_sampai)) : null;
            $detail->save();

            if($detail){

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

        return json_encode($json_data);
    }

    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        $data = DiskonDetail::select('diskon_detail.*','tbl_product.nama as nama_product')->leftJoin('tbl_product','tbl_product.id','diskon_detail.produk')->where('diskon_detail.id',$dec_id)->first();
        $bonus = Product::find($data->bonus_produk);
        if($data->bonus_produk){
            $data->produk_bonus = isset($bonus) ? $bonus->nama : '';
        }
        // return response()->json(['data' => $data]);
        $parent = $this->flag_diskon();
        $jenis = $this->jenis_diskon();
        $kelipatan = $this->kelipatan();
        $satuan = Satuan::all();

        $selectedParent = $data->flag_diskon;
        $selectedSatuan = $data->satuan;
        $selectedJenis = $data->jenis_diskon;
        $selectedKelipatan = $data->kelipatan;
        $selectedSatuanBonus = $data->satuan_bonus;

        // return response()->json(['data' => $parent]);
        return view('backend/master/diskon/form',compact('enc_id','data','parent', 'jenis', 'kelipatan', 'satuan',
                    'selectedParent','selectedSatuan', 'selectedJenis','selectedKelipatan','selectedSatuanBonus'));
    }

    public function delete(Request $request, $enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $data   = DiskonDetail::find($dec_id);
        if($data){
            $parent = Diskon::find($data->diskon_id);
            $data->delete();
            $parent->delete();
            return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
        }else{
            return response()->json(['status'=>"failed",'message'=>'Gagal menghapus data. Silahkan ulangi kembali.']);
        }
    }

    public function get_product(Request $request){
        $query = Product::select('id', 'nama');
        $query->orWhere('nama', 'LIKE' , "%{$request->search}%");
        $query->limit(10);
        $result = $query->get();

        return json_encode($result);
    }
}
