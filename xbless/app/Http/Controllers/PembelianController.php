<?php

namespace App\Http\Controllers;

use App\Models\Gudang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Satuan;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\StockAdj;
use App\Models\Supplier;
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
                $action.='<a href="'.route('pembelian.detail',$enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Detail"><i class="fa fa-eye"></i> Detail</a>&nbsp;';
                $action.='<a href="#" onclick="hapus(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i fa fa-trash-o></i>Hapus</a>&nbsp;';
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

        $supplier = Supplier::all();
        $gudang = Gudang::all();
        $selectedsupplier ="";
        $selectedgudang ="";

        return view('backend/pembelian/form',compact('supplier','gudang','selectedsupplier','selectedgudang'));
    }


    public function tambah_product(Request $req)
    {
        $total = $req->total;
        echo "
        <tr id='detail_product_".$total."'>
        <!-- <input type='hidden' id='detail_product' name='detail_product[]'> -->
            <td>
                <select id='product_".$total."' name='produk[]' class='select2_produk_".$total." form-control' onchange='hitung(this.options[this.selectedIndex].value,".$total.")'>
                    <option value='0' selected>Pilih Product</option>
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
            <td><input type='text' class='form-control touchspin".$total."' id='qty_".$total."' name='qty[]' value='1' onkeyup='hitung_qty(".$total.")' onchange='hitung_qty(".$total.",".$total.")'>
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

    public function total_harga(Request $request){
        $satuan = Satuan::find($request->satuan_id);
        return response()->json([
            'success' => TRUE,
            'data'  => $satuan,
        ]);
    }


    public function coba_simpan(Request $req)
    {
        $enc_id = $req->enc_id;

        if(isset($enc_id)){
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        }

        $supplier = $req->supplier;
        $gudang = $req->gudang;
        $nofaktur = $req->nofaktur;
        $tgl_faktur = date('Y-m-d',strtotime($req->faktur_date));
        $tgl_jatuh_tempo = date('Y-m-d',strtotime($req->jatuh_tempo));
        $tgl_transaksi = date('Y-m-d',strtotime($req->tgl_transaksi));
        $nominal = $req->nominal;
        $keterangan = $req->ket;
        $status_pembelian = 1;
        $approve_pembelian =  0;
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

        if($enc_id != null || isset($enc_id)){
            $pembelian = Pembelian::find($dec_id);
            $pembelian_detail = PembelianDetail::where('pembelian_id',$pembelian->id)->where('notransaction',$pembelian->no_faktur)->first();
            $pembelian->supplier_id       = $supplier;
            $pembelian->id_gudang         = $gudang;
            $pembelian->no_faktur         = $nofaktur;
            $pembelian->tgl_faktur        = $tgl_faktur;
            $pembelian->tgl_transaksi     = $tgl_transaksi;
            $pembelian->nominal           = str_replace('.','',$nominal);
            $pembelian->tgl_jatuh_tempo   = $tgl_jatuh_tempo;
            $pembelian->keterangan        = $keterangan;
            $pembelian->status_pembelian  = $status_pembelian;
            $pembelian->approve_pembelian = $approve_pembelian;
            $pembelian->approved_by       = auth()->user()->username;
            $pembelian->created_user      = auth()->user()->username;
            if($pembelian->save()){
                foreach($pembelian_detail->get() as $detail){
                    if($pembelian->status_pembelian == 1){
                        $stockadj = StockAdj::where('id_product',$detail->product_id)->first();
                        // return response()->json($stockadj);
                        $stockadj->id_gudang = $gudang;
                        $stockadj->id_supplier = $supplier;
                        $stockadj->gudang_baik = $detail->qty;
                        if(!$stockadj->save()){
                            return response()->json([
                                'success' => FALSE,
                                'message' => 'Gagal mengupdate stock product'
                            ]);
                            break;
                        }
                    }
                    if($pembelian_detail->delete()){

                        for($i=0;$i<$total_product;$i++){
                            if(isset($array_id_satuan[$i])){
                                $satuan = Satuan::find($array_id_satuan[$i]);
                                $pembelian_detail = new PembelianDetail;
                                $pembelian_detail->pembelian_id     = $pembelian->id;
                                $pembelian_detail->product_id       = $array_product[$i];
                                $pembelian_detail->notransaction    = $pembelian->no_faktur;
                                $pembelian_detail->qty              = $array_qty[$i] * $satuan->qty;
                                $pembelian_detail->product_price    = $array_harga_product[$i];
                                $pembelian_detail->total            = $array_total_harga[$i];
                                $pembelian_detail->created_user     = auth()->user()->username;
                                if($pembelian_detail->save()){
                                    if($pembelian->status_pembelian == 0){
                                        $stockadj = StockAdj::where('id_product',$detail->id_product)->where('id_gudang',$gudang)->first();
                                        $stockadj->id_gudang = $gudang;
                                        $stockadj->id_supplier = $supplier;
                                        $stockadj->gudang_baik += $pembelian_detail->qty;
                                    }
                                    if(!$stockadj->save()){
                                        return response()->json([
                                            'success' => FALSE,
                                            'message' => 'Gagal mengupdate stock product'
                                        ]);
                                        break;
                                    }
                                }else{
                                    return response()->json([
                                        'success' => FALSE,
                                        'message' => 'Gagal menyimpan detail pembelian'
                                    ]);
                                }
                            }else{
                                continue;
                            }
                        }
                        $transaksi_stock = TransaksiStock::where('no_transaksi',$pembelian->no_faktur)->first();
                        $transaksi_stock->total_harga = $pembelian->total_harga;
                        // $transaksi_stock->total_harga = str_replace('.','',$nominal);
                        if($transaksi_stock->save()){
                            return response()->json([
                                'success' => TRUE,
                                'message' => 'Data pembelian berhasil disimpan'
                            ]);
                        }else{
                            return response()->json([
                                'success' => FALSE,
                                'message' => 'Gagal mengupdate total harga transaksi stock'
                            ]);
                        }
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Gagal menghapus detail pembelian'
                        ]);
                    }
                }
            }
            }else{
                // return $req->all();
                if($total_product > 0){
                    $pembelian                    = new Pembelian;
                    $pembelian->supplier_id         = $supplier;
                    $pembelian->id_gudang         = $gudang;
                    $pembelian->no_faktur         = $nofaktur;
                    $pembelian->tgl_faktur        = $tgl_faktur;
                    $pembelian->tgl_transaksi     = $tgl_transaksi;
                    $pembelian->nominal           = str_replace('.','',$nominal);
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
                                        $stockadj = StockAdj::where('id_product',$array_product[$i])->where('id_gudang',$gudang)->first();
                                        if(isset($stockadj)){
                                            $stockadj->gudang_baik += $detail_pembelian->qty;
                                            $stockadj->id_gudang = $gudang;
                                            $stockadj->id_supplier = $supplier;
                                            $stockadj->save();
                                        }else{
                                            $stockbaru = new StockAdj;
                                            $stockbaru->id_product = $detail_pembelian->product_id;
                                            $stockbaru->id_supplier = $supplier;
                                            $stockbaru->id_gudang = $gudang;
                                            $stockbaru->gudang_baik += $detail_pembelian->qty;
                                            // $stockbaru->gudang_bs = 0;
                                            // $stockbaru->stock_approve = 0;
                                            $stockbaru->save();
                                            if($stockbaru){
                                                $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                                                $stockadj->gudang_baik += $detail_pembelian->qty;
                                                $stockadj->id_gudang = $gudang;
                                                $stockadj->id_supplier = $supplier;
                                                $detail_pembelian->save();
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


                                    // return response()->json($array_product[$i]);
                                    }else{
                                        $stockbaru = new StockAdj;
                                        $stockbaru->id_product = $detail_pembelian->product_id;
                                        $stockbaru->gudang_baik += $detail_pembelian->qty;
                                        $stockbaru->id_gudang = $pembelian->id_gudang;
                                        $stockbaru->id_supplier = $pembelian->id_supplier;
                                        $stockbaru->gudang_bs = 0;
                                        $stockbaru->stock_approve = 0;
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
                                // }

                            }
                        }
                        $transaksi_stock = new TransaksiStock;
                        $transaksi_stock->no_transaksi = $pembelian->no_faktur;
                        $transaksi_stock->tgl_transaksi = $pembelian->tgl_faktur;
                        $transaksi_stock->flag_transaksi = 4;
                        $transaksi_stock->total_harga = $nominal;
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

        // }
    }


    public function search_product(Request $request){
        $product = Product::select('tbl_product.id','tbl_product.nama','tbl_product.kode_product','tbl_product.id_satuan', 'tbl_satuan.nama as satuan_product')
                    ->leftJoin('tbl_satuan','tbl_satuan.id', 'tbl_product.id_satuan')
                    ->orWhere('tbl_product.nama', 'LIKE', "%{$request->search}%")
                    ->orWhere('tbl_product.kode_product', 'LIKE', "%{$request->search}%")
                    ->limit(10)
                    ->get();

        return json_encode($product);
    }

    public function get_satuan(Request $request){
        $satuan = Satuan::orWhere('nama', 'LIKE', "%{$request->search}%")
                      ->limit(10)
                      ->get();

        return json_encode($satuan);
      }

    public function ubah($enc_id){

        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));

        $pembelian = Pembelian::find($dec_id);
        // $pembelian = Pembelian::select('*','gudang.name','supplier.nama');
        // $pembelian->join('gudang','pembelian.id_gudang','gudang.id');
        // $pembelian->join('supplier','pembelian.supplier_id','supplier.id');
        // $data = $pembelian->get();
        // return response()->json($data);

        if(isset($pembelian)){
            $pembelian_detail = PembelianDetail::where('pembelian_id',$pembelian->id)->where('notransaction',$pembelian->no_faktur)->with(['getproduct'])->get();
            // return response()->json($pembelian_detail);
            $supplier = Supplier::all();
            $gudang = Gudang::all();
            $selectedProduct = "";
            $selectedsupplier = $pembelian->supplier_id;
            $selectedgudang = $pembelian->id_gudang;

            return view('backend/pembelian/form',compact('enc_id','pembelian','pembelian_detail','supplier','selectedsupplier','gudang','selectedgudang'));
        }else{
            return view('errors/noaccess');
        }
        return $pembelian;
    }

    public function detail($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $pembelian = Pembelian::where('id', $dec_id)->with(['getdetailpembelian'])->first();
        $detail_pembelian = $pembelian->getdetailpembelian;

        return view('backend/pembelian/detail',compact('enc_id','pembelian','detail_pembelian'));
    }

    public function hapus($enc_id)
    {
        // return "oke";
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // $dataretur = ReturTransaksi::where('jenis_transaksi',1)->first();
        $returback = Pembelian::where('id',$dec_id)->first();
        // return $returback;
        $detail = PembelianDetail::where('pembelian_id',$returback->id)->get();
        // return $detail;

        foreach($detail as $key=> $value){
            // return $value->qty;
            $stockadj = StockAdj::where('id_product',$value->product_id)->first();
            // return $stockadj;
            if(isset($stockadj)){
                $stockadj->gudang_baik -= $value->qty;
                // $stockadj->stock_retur_pembelian = 0;
                if($stockadj->save()){
                    $value->delete();
                }
            }
        }
        $transaksi_stock = TransaksiStock::where('no_transaksi',$returback->no_faktur)->first();
        // return $transaksi_stock;
        $transaksi_stock->delete();
        $returback->delete();

        return response()->json(['status'=>"success",'message'=>'Data Berhasil dihapus.']);
    }

    public function import(){
        return view('backend/pembelian/import');
    }

}
