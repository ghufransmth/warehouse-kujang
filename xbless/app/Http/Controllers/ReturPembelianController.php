<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Product;
use App\Models\ReturTransaksi;
use App\Models\ReturTransaksiDetail;
use App\Models\Satuan;
use App\Models\StockAdj;
use App\Models\TransaksiStock;
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
        // return $req->all();
        // return $req->produk;
        $nofaktur = $req->nofaktur;
        $tgl_faktur = date('Y-m-d',strtotime($req->faktur_date));
        $tgl_jatuh_tempo = date('Y-m-d',strtotime($req->jatuh_tempo));
        $tgl_transaksi = date('Y-m-d',strtotime($req->tgl_transaksi));
        $nominal = $req->nominal;
        $keterangan = $req->ket;
        $array_harga_product = $req->harga_product;
        $array_product = $req->produk;
        $array_qty = $req->qty;
        $array_id_satuan = $req->tipesatuan;
        $array_total_harga = $req->total;
        $total_product = $req->total_produk;
        $total_harga_pembelian = $req->total_harga_pembelian;
        // return response()->json($req->all());
        if($total_product > 0){
            $retur = new ReturTransaksi;
            $retur->no_faktur = $nofaktur;
            $retur->no_retur_faktur = $nofaktur.'/retur';
            $retur->catatan = $keterangan;
            $retur->jenis_transaksi = 1;
            $retur->total_harga = $total_harga_pembelian;
            $retur->tgl_retur = $tgl_transaksi;
            $retur->created_user = auth()->user()->username;
            if($retur->save()){
                for($i = 0; $i < $total_product; $i++){
                    // return $array_product[$i];
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $detail_retur = new ReturTransaksiDetail;
                    $detail_retur->retur_transaksi_id = $retur->id;
                    $detail_retur->product_id         = $array_product[$i];
                    $detail_retur->qty                = $array_qty[$i] * $satuan->qty;
                    $detail_retur->price              = $array_harga_product[$i];
                    $detail_retur->total              = $array_total_harga[$i];
                    if($detail_retur->save()){
                        $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                        if(isset($stockadj)){
                            $stockadj->gudang_baik -= $detail_retur->qty;
                            $stockadj->stock_retur_pembelian += $detail_retur->qty;
                            if($stockadj->save()){
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
                }
            }
        $transaksi_stock = new TransaksiStock;
        $transaksi_stock->no_transaksi = $retur->no_retur_faktur;
        $transaksi_stock->tgl_transaksi = $retur->tgl_faktur;
        $transaksi_stock->flag_transaksi = 6;
        $transaksi_stock->created_by = auth()->user()->username;
        $transaksi_stock->note = 'retur pembelian';
        if($transaksi_stock->save()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Retur Pembelian berhasil disimpan'
            ]);
        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Retur Pembelian gagal disimpan'
            ]);
        }
    }else{
        return response()->json([
            'success' => FALSE,
            'message' => 'Gagal menyimpan Retur Pembelian'
        ]);
    }
    }

    public function simpan_edit(Request $req){
        // return $req->all();
        // return $req->total;
        $nofakturretur = $req->nofakturretur;
        $tgl_faktur = date('Y-m-d',strtotime($req->faktur_date));
        $tgl_jatuh_tempo = date('Y-m-d',strtotime($req->jatuh_tempo));
        $tgl_transaksi = date('Y-m-d',strtotime($req->tgl_transaksi));
        $nominal = $req->nominal;
        $keterangan = $req->ket;
        $array_harga_product = $req->harga_product;
        $array_product = $req->produk;
        $array_qty = $req->qty;
        $array_id_satuan = $req->tipesatuan;
        $array_total_harga = $req->total;
        $total_product = $req->total_produk;
        $total_harga_pembelian = $req->total_harga_pembelian;

        $enc_id = $req->enc_id;

        if(isset($enc_id)){
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        }

        if($total_product > 0){
            $retur = ReturTransaksi::find($dec_id);
            // return $retur;
            // $retur->no_faktur = $nofaktur;
            $retur->no_retur_faktur = $nofakturretur;
            $retur->catatan = $keterangan;
            $retur->jenis_transaksi = 1;
            $retur->total_harga = $nominal;
            $retur->tgl_retur = $tgl_transaksi;
            $retur->created_user = auth()->user()->username;
            if($retur->save()){
                for($i = 0; $i < $total_product; $i++){
                    // return $array_product[$i];
                    $satuan = Satuan::find($array_id_satuan[$i]);

                    $detail_retur = ReturTransaksiDetail::where('retur_transaksi_id',$retur->id)->get();
                    // return response()->json($detail_retur);
                    foreach($detail_retur as $key=> $value){
                        $value->retur_transaksi_id = $retur->id;
                        $value->product_id         = $array_product[$i];
                        $value->qty                = $array_qty[$i] * $satuan->qty;
                        $value->price              = $array_harga_product[$i];
                        $value->total              = $array_total_harga[$i];
                        if($value->save()){
                            $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                            if(isset($stockadj)){
                                $stockadj->gudang_baik += $stockadj->stock_retur_pembelian - $array_qty[$i];
                                $stockadj->stock_retur_pembelian = $value->qty;
                                if($stockadj->save()){
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
                    }

                }
            }
        $transaksi_stock = new TransaksiStock;
        $transaksi_stock->no_transaksi = $retur->no_retur_faktur;
        $transaksi_stock->tgl_transaksi = $retur->tgl_faktur;
        $transaksi_stock->flag_transaksi = 6;
        $transaksi_stock->created_by = auth()->user()->username;
        $transaksi_stock->note = 'retur pembelian';
        if($transaksi_stock->save()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Retur Pembelian berhasil disimpan'
            ]);
        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Retur Pembelian gagal disimpan'
            ]);
        }
    }else{
        return response()->json([
            'success' => FALSE,
            'message' => 'Gagal menyimpan Retur Pembelian'
        ]);
    }
    }
}
