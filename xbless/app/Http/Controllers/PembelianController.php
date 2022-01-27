<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\StockAdj;
use App\Models\TransaksiStock;
use DB;
use Illuminate\Support\Facades\Auth;
use Throwable;

class PembelianController extends Controller
{
    protected $original_column = array(
        1 => "nama",
    );

    public function index(){

        return view('backend/pembelian/index');
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
        $cek = Pembelian::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
      }


    public function getData(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $dataquery = Pembelian::select('id', 'no_faktur','tgl_faktur', 'tgl_transaksi', 'keterangan', 'created_user');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
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

        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";
            $action.="";

            if($result->flag_proses == 0){
                $action.='<div>';
                $action.='<a href="'.route('pembelian.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
                $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-trash"></i> Hapus</a>&nbsp;';
                $action.="</div>";
            }else if($result->flag_proses == 1){
                // $action.= '<span class="label label-danger">Data tidak bisa diedit kembali</span>&nbsp;';
                // $action.='<a href="'.route('product.product_beli.detail', $enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-eye"></i> Detail</a>&nbsp;';
            }

            $result->no                   = $key+$page;
            $result->id                   = $result->id;
            $result->no_faktur            = $result->no_faktur;
            $result->tgl_faktur           = date('d M Y', strtotime($result->tgl_faktur));
            $result->tgl_transaksi        = date('d M Y', strtotime($result->tgl_transaksi));
            $result->keterangan           = $result->keterangan;
            $result->created_user         = $result->created_user;
            $result->action               = $action;
        }

        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        // if ($request->user()->can('brand.index')) {
        //     $json_data = array(
        //         "draw"            => intval($request->input('draw')),
        //         "recordsTotal"    => intval($totalData),
        //         "recordsFiltered" => intval($totalFiltered),
        //         "data"            => $data
        //       );
        // }else{
        //     $json_data = array(
        //         "draw"            => intval($request->input('draw')),
        //         "recordsTotal"    => 0,
        //         "recordsFiltered" => 0,
        //         "data"            => []
        //       );

        // }
        return json_encode($json_data);
    }

    public function tambah(){

        return view('backend/pembelian/form');
    }


    public function tambah_product(Request $req)
    {
        $total = $req->total;
        echo "
        <tr id='detail_product_".$total."'>
        <!-- <input type='hidden' id='detail_product' name='detail_product[]'> -->
            <td>
                <select id='product_".$total."' name='produk[]' class='select2_produk_".$total." form-control' onchange='hitung(this.options[this.selectedIndex].value,".$total.")'>
                    <option value='0' selected disabled>Pilih Product</option>
                </select>
            </td>
            <td>
            <select class='select2_satuan_".$total."' id='tipe_satuan_".$total."' name='tipesatuan[]' onchange='satuan(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value='null'>Pilih Tipe Satuan </option>
                </select>
        </td>
        <td>
            <input type='text' class='form-control' id='harga_product_".$total."' name='harga_product[]'>
        </td>
            <td><input type='text' class='form-control touchspin".$total."' id='qty_".$total."' name='qty[]' value='1' onkeyup='hitung_qty(".$total.")' onchange='hitung_qty(".$total.")'>
            </td>

             <td><input type='text' class='form-control total_harga' id='total_".$total."' name='total[]' readonly></td>
            <td><a class='text-white btn btn-danger btn-hemisperich btn-xs' onclick='javascript:deleteObat(".$total.")' data-original-title='Hapus Data' id='deleteModal'><i class='fa fa-trash'></i></a></td>
        </tr>
        <script>
                select_satuan(".$total.");
                select_product(".$total.");
                $('.touchspin".$total."').TouchSpin({
                    min: 1,
                    max: 9999999999999999999999,
                    buttondown_class: 'btn btn-white',
                    buttonup_class: 'btn btn-white'
                });
            </script>
        <script>
            function deleteObat(id){
                $('#detail_product_'+id).remove();
                var total_obat = $('#total_detail').val();
                var total = parseInt(total_obat) - 1;
                $('#total_detail').val(total);
            }

        </script>
        ";
    }

    private function convert_pcs($jumlah, $satuan){
        $satuan = Satuan::where('name', 'LIKE', "%{$satuan}%")->first();

        $count_units    = $jumlah * $satuan->qty;

        return $count_units;
    }

    public function search_satuan(Request $request){
        $satuan = Satuan::select('*')
                    ->orWhere('nama', 'LIKE', "%{$request->search}%")
                    ->orWhere('qty', 'LIKE', "%{$request->search}%")
                    ->orderBy('id', 'DESC')
                    ->limit(10)
                    ->get();

        return json_encode($satuan);
    }


    public function harga_product(Request $request){

        // $memberplus      = Member::select('member.id','type_price.name')->join('type_price','type_price.id','member.operation_price')->where('member.id',$request->member)->first();
        // $tambahan = $memberplus?$memberplus->name:0;
        $product = Product::where('id', $request->produk_id)->with(['getstock'])->first();

        return response()->json([
            'success' => TRUE,
            'data' => $product,
        ]);
    }

    public function coba_simpan(Request $req)
    {
        // return $req->all();
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
        // return $array_product;
        // VALIDASI
        if($nofaktur == null || $nofaktur == ''){
            return response()->json([
                'success' => FALSE,
                'message' => 'Nomor faktur harus diisi'
            ]);
        }
        if(count($array_total_harga) < 1){
            return response()->json([
                'success' => FALSE,
                'message' => 'Product harus diisi'
            ]);
        }

        // for($i=0; $i<$total_product;$i++){
        //     $satuan = Satuan::find($array_id_satuan[$i]);
        //     $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
        //     $total_qty = $array_qty * $satuan->qty;
        //     if($stockadj->stock_pembelian < $total_qty){
        //         return response()->json([
        //             'success' => FALSE,
        //             'message' => 'Stock pembelian harus diisi cukup'
        //         ]);
        //     }
        // }

        // END VALIDASI
        if($total_product > 0){
            $pembelian                    = new Pembelian;
            $pembelian->no_faktur         = $nofaktur;
            $pembelian->tgl_faktur        = $tgl_faktur;
            $pembelian->tgl_transaksi     = $tgl_transaksi;
            $pembelian->nominal           = $nominal;
            $pembelian->tgl_jatuh_tempo   = $tgl_jatuh_tempo;
            $pembelian->keterangan        = $keterangan;
            $pembelian->status_pembelian  = $status_pembelian;
            $pembelian->approve_pembelian = $approve_pembelian;
            $pembelian->approved_by       = auth()->user()->username;
            $pembelian->created_user      = auth()->user()->username;

            if($pembelian->save()){
                for($i=0; $i < $total_product; $i++){
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $detail_pembelian                   = new PembelianDetail;
                    $detail_pembelian->pembelian_id     = $pembelian->id;
                    $detail_pembelian->product_id       = $array_product[$i];
                    $detail_pembelian->notransaction    = $pembelian->no_faktur;
                    $detail_pembelian->qty              = $array_qty[$i] * $satuan->qty;
                    $detail_pembelian->product_price    = $array_harga_product[$i];
                    $detail_pembelian->total            = $array_total_harga[$i];
                    $detail_pembelian->created_user     = auth()->user()->username;
                    if($detail_pembelian->save()){
                        // return $detail_pembelian;
                        if($pembelian->status_pembelian == 1){
                            $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                            if(isset($stockadj)){
                                $stockadj->stock_pembelian += $detail_pembelian->qty;
                            }else{
                                $stockbaru = new StockAdj;
                                $stockbaru->id_product = $detail_pembelian->product_id;
                                $stockbaru->stock_pembelian += $detail_pembelian->qty;
                                $stockbaru->stock_penjualan = 0;
                                $stockbaru->stock_bs = 0;
                                $stockbaru->stock_approve = 0;
                                // return response()->json($stockbaru);
                                $stockbaru->save();
                                if($stockbaru){
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
                }
                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi = $pembelian->no_faktur;
                $transaksi_stock->tgl_transaksi = $pembelian->tgl_faktur;
                $transaksi_stock->flag_transaksi = 4;
                $transaksi_stock->created_by = auth()->user()->username;
                $transaksi_stock->note = '-';
                if($transaksi_stock->save()){
                    return response()->json([
                        'success' => TRUE,
                        'message' => 'Pembelian berhasil disimpan'
                    ]);
                }else{
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Pembelian gagal disimpan'
                    ]);
                }
            }else{
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Gagal menyimpan table Pembelian'
                ]);
            }
        }
    }


    public function search_product(Request $request){
        $product = Product::select('tbl_product.id','tbl_product.nama','tbl_product.kode_product','tbl_product.id_satuan', 'tbl_satuan.nama as satuan_product')
                    ->leftJoin('tbl_satuan','tbl_satuan.id', 'tbl_product.id_satuan')
                    ->orWhere('tbl_product.nama', 'LIKE', "%{$request->search}%")
                    ->orWhere('tbl_product.kode_product', 'LIKE', "%{$request->search}%")
                    ->limit(10)
                    ->get();
        // $product = Product::select('*')
        //             ->orWhere('tbl_product.nama','LIKE',"%{$request->search}%")
        //             ->limit(10)
        //             ->get();
        // return $product;
        return json_encode($product);
    }

    public function get_satuan(Request $request){
        $satuan = Satuan::orWhere('nama', 'LIKE', "%{$request->search}%")
                      ->limit(10)
                      ->get();

        return json_encode($satuan);
      }

    //   public function ubah($enc_id){
    //     $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
    //     $product_beli = Pembelian::select('pembelian.*')->where('pembelian.id', $dec_id)->first();
    //     // $product_beli->transaction = date('d-m-Y', strtotime($product_beli->tgl_pembelian));

    //     $query = PembelianDetail::select('pembelian_detail.id','pembelian_detail.qty','pembelian_detail.product_id','tbl_product.kode_product as product_code','tbl_product.nama as product_name');
    //     $query->leftJoin('tbl_product','tbl_product.id','pembelian_detail.product_id');
    //     $query->where('pembelian_detail.pembelian_id', $dec_id);
    //     $total_beli_detail = $query->count();
    //     $product_beli_detail = $query->get();
    //     foreach ($product_beli_detail as $key => $value) {
    //         $get_satuan_id  = Satuan::where('nama','LIKE',"%{$value->satuan}%")->first();

    //         // $value->expired = date('d-m-Y', strtotime($value->tgl_expired));
    //         $value->satuan_id = $get_satuan_id->id;
    //     }

    //     return view('backend/pembelian/form', compact('enc_id','product_beli','product_beli_detail', 'total_beli_detail'));
    // }
    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        if($dec_id){
            // $pembelian = Pembelian::find($dec_id);
            // $pembelian = Pembelian::select('pembelian.no_faktur','pembelian.nominal','pembelian.keterangan','pembelian.tgl_faktur','pembelian.tgl_jatuh_tempo')->where('id',$dec_id);
            $pembelian = Pembelian::select('*')->where('id',$dec_id)->first();

            // return response()->json($pembelian->getDetailPembelian[0]->getProduct);
            $data = $pembelian->getDetailPembelian[0]->getProduct;
            // return response()->json($data);
            return view('backend/pembelian/form',compact('enc_id','pembelian','data'));
        }else{
            return view('errors/noaccess');
        }
    }

    public function hapus($enc_id){}
    public function import(){
        return view('backend/pembelian/import');
    }

    // public function simpan_lagi(Request $req){

    //     try{
    //         $pembelian = new Pembelian();
    //         $pembelian->no_faktur         = $req->nofaktur;
    //         $pembelian->tgl_faktur        = $req->faktur_date;
    //         $pembelian->nominal           = $req->nominal;
    //         $pembelian->tgl_jatuh_tempo   = date('Y-m-d',strtotime($req->jatuh_tempo));
    //         $pembelian->keterangan        = $req->ket;
    //         $pembelian->status_pembelian  = 1;
    //         $pembelian->approve_pembelian = 1;
    //         $pembelian->approved_by       = auth()->user()->username;
    //         $pembelian->created_user      = auth()->user()->username;
    //         $pembelian->save();
    //         if($pembelian){
    //             foreach($req->)
    //         }

    //     }catch(Throwable $tr){

    //     }


    //         // if($enc_id){
    //         //     $pembelian = Pembelian::find($dec_id);
    //         //     $pembelian->no_faktur         = $req->nofaktur;
    //         //     $pembelian->tgl_faktur        = $req->faktur_date;
    //         //     $pembelian->nominal           = $req->nominal;
    //         //     $pembelian->tgl_jatuh_tempo   = date('Y-m-d',strtotime($req->jatuh_tempo));
    //         //     $pembelian->keterangan        = $req->ket;
    //         //     $pembelian->status_pembelian  = 1;
    //         //     $pembelian->approve_pembelian = 1;
    //         //     $pembelian->approved_by       = auth()->user()->username;
    //         //     $pembelian->created_user      = auth()->user()->username;
    //         //     $pembelian->save();
    //         // }
    // }

}
