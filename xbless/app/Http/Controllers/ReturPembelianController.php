<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Product;
use App\Models\ReturTransaksi;
use App\Models\ReturTransaksiDetail;
use App\Models\Satuan;
use App\Models\StockAdj;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ReturPembelianController extends Controller
{

    protected $original_column = array(
        1 => "nama",
    );

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
      }

      function safe_decode($string,$mode=null) {
          $data = str_replace(array('_'),array('/'),$string);
          return $data;
      }

      private function cekExist($column,$var,$id){
        $cek = Pembelian::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
      }

    public function index()
    {
        return view('backend/returpembelian/index');
    }

    public function getData(Request $req)
    {
        $limit = $req->length;
        $start = $req->start;
        $page  = $start +1;
        $search = $req->search['value'];

        $dataquery = Pembelian::select('id', 'no_faktur','tgl_faktur','nominal', 'tgl_transaksi', 'keterangan', 'created_user');
        if(array_key_exists($req->order[0]['column'], $this->original_column)){
            $dataquery->orderByRaw($this->original_column[$req->order[0]['column']].' '.$req->order[0]['dir']);
         }
          else{
           $dataquery->orderBy('id','DESC');
         }
         if($search) {
            $dataquery->where(function ($query) use ($search) {
                    $query->orWhere('no_faktur','LIKE',"%{$search}%");
            });
          }
            $totalData = $dataquery->get()->count();

            $totalFiltered = $dataquery->get()->count();

            $dataquery->limit($limit);
            $dataquery->offset($start);
            $data = $dataquery->get();

            foreach($data as $key=> $result){
                $enc_id = $this->safe_encode(Crypt::encryptString($result->id));

                $action = "";
                $action.="";
                $action.='<div>';
                $action.='<a href="'.route('retur_pembelian.detail_retur',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Retur"><i class="fa fa-pencil"></i> Retur</a>&nbsp;';

                $result->no = $key+$page;
                $result->id = $result->id;
                $result->no_faktur = $result->no_faktur;
                $result->tgl_faktur           = date('d M Y', strtotime($result->tgl_faktur));
                // $result->nominal = $result->nominal;
                $result->nominal = number_format($result->nominal,0,',','.');
                $result->action = $action;
            }
            $json_data = array(
                "draw"            => intval($req->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $data
            );

            return response()->json($json_data);
    }

    public function detail_retur($enc_id){

        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $retur = Pembelian::find($dec_id);

        if(isset($retur)){
            $detail = Pembelian::where('id',$retur->id)->where('no_faktur',$retur->no_faktur)->get();

            return view('backend/returpembelian/form',compact('enc_id','retur'));
        }else{
            return response()->json([
                'success' => false,
                'code' => 201,
                'message' => 'No faktur tidak ditemukan'
            ]);
        }
        return $retur;
    }

    // public function tambah($enc_id){

    //     $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

    //     $selectedProduct = "";
    //     $product = Product::all();
    //     $pembelian = Pembelian::find($enc_id);

    //     return view('backend/returpembelian/form',compact('selectedProduct','product','pembelian','detail_pembelian'));
    // }

    public function tambah($enc_id){
        // return $enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // return $dec_id;
        $pembelian = Pembelian::where('no_faktur',$dec_id)->first();
        $pembelian_detail = PembelianDetail::where('pembelian_id',$pembelian->id)->where('notransaction',$pembelian->no_faktur)->with(['getproduct'])->get();
        // return $pembelian_detail;

        return view('backend/returpembelian/form_retur',compact('enc_id','pembelian','pembelian_detail'));
    }

    public function simpan(Request $req)
    {
        return $req->all();
        $nofaktur = $req->nofaktur;
        $tgl_faktur = date('Y-m-d',strtotime($req->faktur_date));
        $tgl_jatuh_tempo = date('Y-m-d',strtotime($req->jatuh_tempo));
        $tgl_transaksi = date('Y-m-d',strtotime($req->tgl_transaksi));
        $nominal = $req->nominal;
        $keterangan = $req->ket;
        $status_pembelian = 1;
        $approve_pembelian =  1;
        $array_harga_product = $req->harga_product;
        $array_product = $req->produk;
        $array_qty = $req->qty;
        $array_id_satuan = $req->tipesatuan;
        $array_total_harga = $req->total;
        $total_product = $req->total_produk;
        $total_harga_pembelian = $req->total_harga_pembelian;

        if($total_product > 0){
            $retur = new ReturTransaksi;
            $retur->no_faktur = $nofaktur;
            $retur->no_retur_faktur = 1111;
            $retur->catatan = $keterangan;
            $retur->jenis_transaksi = 1;
            $retur->total_harga = $total_harga_pembelian;
            $retur->tgl_transaksi = $tgl_transaksi;
            $retur->created_user = auth()->user()->username;
            if($retur->save()){
                for($i = 0; $i < $total_product; $i++){
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $detail_retur = new ReturTransaksiDetail;
                    $detail_retur->retur_transaksi_id = $retur->id;
                    $detail_retur->product_id         = $array_product[$i];
                    $detail_retur->qty                = $array_qty[$i] * $satuan->qty;
                    $detail_retur->price              = $array_harga_product[$i];
                    $detail_retur->total              = $array_total_harga[$i];
                   $detail_retur->save();
                   if($detail_retur){
                    $json_data = array(
                        "success"         => TRUE,
                        "message"         => 'Data berhasil ditambahkan.'
                );
                }else {
                    $json_data = array(
                        "success"         => FALSE,
                        "message"         => 'Data gagal ditambahkan.'
                        );
                   }
                }
            }
        }
        // return json_encode($json_data);
    }
}
