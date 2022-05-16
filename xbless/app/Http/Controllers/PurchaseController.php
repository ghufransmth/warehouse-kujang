<?php

namespace App\Http\Controllers;

use App\Models\DetailPenjualan;
use App\Models\DiskonDetail;
use App\Models\DiskonPenjualan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrder;
use App\Models\ReportStock;
use App\Models\TipeHarga;
use App\Models\Invoice;
use App\Models\Satuan;
use App\Models\Expedisi;
use App\Models\Product;
use App\Models\Gudang;
use App\Models\Penjualan;
use App\Models\Sales;
use App\Models\StockAdj;
use App\Models\Toko;
use App\Models\TransaksiStock;
use Illuminate\Support\Facades\Gate;

use Carbon\Carbon;

use DB;
use PDF;
use Auth;

class PurchaseController extends Controller
{
    protected $original_column = array(
        1 => "product_code",
    );

    public function statusFilter(){
        $value = array('99'=>'Semua','1'=>'Aktif' ,'0'=>'Tidak Aktif','2'=>'Blokir');
        return $value;
    }

    public function jenisharga(){
        $value = array('1'=>'Harga Normal', '2'=>'Harga Expor');
        return $value;
    }

    public function actionstatus(){
        $value = array('0'=>'BARU', '1'=>'PROSESS', '2' => 'DITOLAK');
        return $value;
    }

    public function statuspo(){
        $value = array('1' => 'PROSES INVOICE', '2' => 'DITOLAK');
        return $value;
    }

    public function statusgudang(){
        $value = array('0'=>'DIPROSES', '1'=>'SELESAI', '2' => 'DITOLAK');
        return $value;
    }

    public function statusinvoiceawal(){
        $value = array('0'=>'DIPROSES', '1'=>'PROSES KE GUDANG', '2' => 'DITOLAK');
        return $value;
    }

    public function index(){

        if(session('filter_perusahaan')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_perusahaan');
        }

        if(session('filter_member')==""){
            $selectedmember = '';
        }else{
            $selectedmember = session('filter_member');
        }
        $member = array();
        $perusahaan = array();
        $gudang = array();
        $toko = Toko::all();
        $sales = Sales::all();
        return view('backend/purchase/index',compact('member','perusahaan','gudang','selectedmember','selectedperusahaan', 'sales', 'toko'));
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
        $cek = PurchaseOrder::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function cut_text($string){
        $value = substr($string, 0, 3);
        return $value;
    }


    public function gennoinvo($tanggal, $data)
    {
            $next_no     ='';
            $max_data_id =Invoice::where('perusahaan_id',$data->perusahaan_id)->orderBy('id', 'DESC')->first();

            if ($max_data_id) {
                $datainvoice  = Invoice::find($max_data_id->id);
                $nota         = $datainvoice->no_nota;
                $tahunnota     =explode('/',$nota)[3];
                if($tahunnota == date('y')){
                    $ambil = substr(explode('/',$nota)[0], -5);
                }else{
                    $ambil = '99999';
                }
            }

            if ($max_data_id==null) {
                $next_no = '00001';
            }elseif (strlen($ambil)<5) {
                $next_no = '00001';
            }elseif ($ambil == '99999') {
                $next_no = '00001';
            }else {
                $next_no = substr('00000', 0, 5-strlen($ambil+1)).($ambil+1);
            }

            $nmperusahaan      = Perusahaan::find($data->perusahaan_id);
            $kode_perusahaan   = $nmperusahaan?$nmperusahaan->kode:'-';

            return $next_no.'/'.$kode_perusahaan.'/'.date('m').'/'.date('y');
    }

    public function edit($enc_id){

        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        return $dec_id;
        $penjualan = Penjualan::find($dec_id);

        if(isset($penjualan)){
            $detail_penjualan = DetailPenjualan::where('id_penjualan', $penjualan->id)->where('no_faktur', $penjualan->no_faktur)->with(['getproduct'])->get();
            $member = array();
            $selectedmember ="";
            $sales = Sales::all();

            $selectedsales = $penjualan->id_sales;
            // $expedisi = Expedisi::all();
            $expedisi = array();
            $selectedexpedisi ="";
            // $expedisivia = ExpedisiVia::all();
            $expedisivia = array();
            $selectedexpedisivia ="";
            $selectedproduct ="";
            // $tipeharga = $this->jenisharga();
            $tipeharga = array();
            $selectedtipeharga ="";
            $toko = Toko::all();
            $selectedtoko = $penjualan->id_toko;
            $selectedstatuslunas = $penjualan->status_lunas;
            $selectedjenispembayaran = $penjualan->jenis_pembayaran;

            return view('backend/purchase/form',compact('enc_id','tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia', 'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko', 'selectedtoko', 'selectedstatuslunas', 'penjualan', 'detail_penjualan', 'selectedjenispembayaran'));
        }else{

        }
        return $penjualan;
    }
    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        $type = $request->type;
        $filter_toko = $request->filter_toko;
        $filter_sales = $request->filter_sales;
        $request->session()->put('filter_toko', $request->filter_toko);
        $request->session()->put('filter_sales', $request->filter_sales);
        $request->session()->put('type', $request->type);
        $penjualan = Penjualan::select('*');
        $penjualan->whereHas('gettransaksi');
        // return $penjualan->get();

        $penjualan->orderBy('tgl_faktur', 'DESC');
        if($filter_toko != null || $filter_toko != ""){
            $penjualan->where('id_toko', $filter_toko);
        }
        if($filter_sales != null || $filter_sales != ""){
            $penjualan->where('id_sales', $filter_sales);
        }
        if($type == 1){
            $penjualan->where('status_lunas', 0);
        }else if($type == 2){
            $penjualan->where('status_lunas', 1);
        }

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $penjualan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }

        if($search) {
            $penjualan->where(function ($query) use ($search) {
                    $query->orWhere('no_nota','LIKE',"%{$search}%");
                    $query->orWhere('kode_rpo','LIKE',"%{$search}%");
            });
        }
        $totalData = $penjualan->get()->count();

        $totalFiltered = $penjualan->get()->count();
        $penjualan->limit($limit);
        $penjualan->offset($start);
        $data = $penjualan->get();

        foreach($data as $key => $result){
            $aksi = "";
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $result->id             = $result->id;
            $result->no             = $key+$page;
            $result->no_faktur = $result->no_faktur;
            $result->sales = $result->getsales->nama;
            $result->toko = $result->gettoko->name;
            $result->tgl_jatuh_tempo = $result->tgl_jatuh_tempo;
            $result->tgl_transaksi = $result->tgl_faktur;
            $result->total_harga = $this->format_uang($result->total_harga);
            $result->total_diskon = $this->format_uang($result->total_diskon);
            $result->tgl_lunas = $result->tgl_lunas;
            $aksi .= '<a href="'.route('purchaseorder.detail', $enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip">Detail </a> <br>';

            if($result->flag_proses != '1'){
                $aksi .= '<a href="'.route('purchaseorder.edit', $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px; margin-top:5px">Edit </a> <br>';
            }

            if($result->status_lunas == 0 && $result->flag_proses == '1'){
                $result->status_pembayaran = '<span class="badge badge-success">Belum Lunas</span>';
                $aksi .= '<a href="#" onclick="approve(\''.$enc_id.'\')" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Aprrove</a> <a href="#" onclick="reject(\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Reject</a>';
            }else if($result->status_lunas == 2){
                $result->status_pembayaran = '<span class="badge badge-danger">Ditolak</span>';
            }else{
                $result->status_pembayaran = '<span class="badge badge-primary">Lunas</span>';
            }

            if($result->flag_proses != '1' && $result->status_lunas != 2){
                $aksi .= '<a href="'.route('purchaseorder.proses', $enc_id).'" class ="btn btn-info btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px; min-width:100px">Proses</a>';
            }
            $result->created_by = $result->created_by;
            $result->aksi = $aksi;
        }
        if ($request->user()->can('purchaseorder.index')) {
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

    public function format_uang($angka){

    	$hasil_rupiah = "Rp " . number_format($angka,2,',','.');
    	return $hasil_rupiah;

    }

    public function approve($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::find($dec_id);
        $penjualan->status_lunas = 1;
        $penjualan->tgl_lunas = date('Y-m-d');
        if($penjualan->save()){
            return response()->json([
                'success' => true,
                'message' => 'Status Lunas berhasil diubah'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Status Lunas gagal diubah'
            ]);
        }

    }
    public function reject($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::find($dec_id);
        $penjualan->status_lunas = 2;
        $penjualan->tgl_lunas = date('Y-m-d');
        if($penjualan->save()){
            return response()->json([
                'success' => true,
                'message' => 'Status berhasil diubah'
            ]);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Status gagal diubah'
            ]);
        }
    }

    public function tambah(){
        // $member = Member::all();
        $member = array();
        $selectedmember ="";
        $sales = Sales::all();
        // $sales = array();
        $selectedsales = session('idsales') != '' ?  session('idsales') : '';
        // $expedisi = Expedisi::all();
        $expedisi = array();
        $selectedexpedisi ="";
        // $expedisivia = ExpedisiVia::all();
        $expedisivia = array();
        $selectedexpedisivia ="";
        $selectedproduct ="";
        // $tipeharga = $this->jenisharga();
        $tipeharga = array();
        $selectedtipeharga ="";
        $toko = Toko::all();

        $selectednotransaksi = $this->generateKode();

        // return 'tes';
        return view('backend/purchase/form',compact('tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia',
                    'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko','selectednotransaksi'));
    }

    public function cekInvoiceBelumLunas(Request $req){
        $inv = Invoice::selectRaw('no_nota,pay_status,DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY),DATE_FORMAT(now(), "%Y-%m-%d")')->whereRaw('DATE_ADD(DATE_FORMAT(dateorder, "%Y-%m-%d"), INTERVAL +2 DAY) <= DATE_FORMAT(now(), "%Y-%m-%d")')->where('pay_status',1)->where('member_id',$req->member_id)->get();
        if(count($inv) > 0){
            $json_data = array(
                "success"         => TRUE,
                "info"            => 1,
            );
        }else{
            $json_data = array(
                "success"         => TRUE,
                "info"            => 0,
            );
        }
        return json_encode($json_data);

    }
    public function addproduk(Request $request){
        $total = $request->total;
        echo "
            <tr id='dataajaxproduk_".$total."'>
                <td>
                <select class='select2_produk_".$total."' id='produk_".$total."' name='produk[]' onchange='hitung(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value=''>Pilih Produk </option>
                </select>

                <td>
                <input type='text' class='form-control' name='stock_product[]' id='stock_product_".$total."' readonly>
                </td>
                <td><input type='text' class='form-control' id='harga_product_".$total."' name='harga_product[]' readonly></td>
                <td>
                <select class='select2_satuan_".$total."' id='tipe_satuan_".$total."' name='tipesatuan[]' onchange='satuan(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value='null'>Pilih Tipe Satuan </option>
                </select>
                </td>
                <td><input type='text' class='form-control touchspin".$total."' id='qty_".$total."' name='qty[]' value='1' onkeyup='hitung_qty(".$total.")' onchange='hitung_qty(".$total.")'> </td>
                <td><input type='text' class='form-control total_harga' id='total_".$total."' name='total[]' readonly></td>
                <td><a href='#!' onclick='javascript:deleteProduk(".$total.")' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
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
        ";
    }

    public function search_produk(Request $request){

        // return "tes";
        $search = $request->search;
        $stockadj = StockAdj::where('gudang_baik', '>', 0)->pluck('id_product');
        $query = Product::select('*')
                    ->whereIn('id', $stockadj)
                    ->orderBy('id', 'DESC');
        if($search){
            $query->where(function($q) use ($search){
                $q->orwhere('kode_product', 'LIKE', "%{$search}%");
                $q->orwhere('nama', 'LIKE', "%{$search}%");
            });
        }
        $product = $query->limit(10)->get();
        return json_encode($product);
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


    public function getTahun($string){
        $value = substr($string, 0, 3);
        if($value=='BO-'){
            $tahun = substr($string, 7, 2);
        }else{
            $tahun = substr($string, 4, 2);
        }
        return $tahun;
    }

    public function genRPO($sales_id)
    {
            $next_no     ='';
            $max_data_id =PurchaseOrder::where('sales_id',$sales_id)->where('kode_rpo','!=',null)->orderBy('id', 'DESC')->first();
            if ($max_data_id) {
                $datarpo      = PurchaseOrder::find($max_data_id->id);
                $koderpo      = $datarpo->kode_rpo;
                $tahunkode    = $this->getTahun($koderpo);
                if($tahunkode == date('y')){
                    $ambil = substr($koderpo, -5);
                }else{
                    $ambil = '99999';
                }
            }

            if ($max_data_id==null) {
                $next_no = '00001';
            }elseif (strlen($ambil)<5) {
                $next_no = '00001';
            }elseif ($ambil == '99999') {
                $next_no = '00001';
            }else {
                $next_no = substr('00000', 0, 5-strlen($ambil+1)).($ambil+1);
            }
            $sales             = Sales::select('code')->where('id',$sales_id)->first();

            $salescode         = $sales?$sales->code:'-';

            // {{tgl}{bln}{thn}{Kode sales}{5 digit Nomor}
            return date('dmy').$salescode.$next_no;
    }
    public function simpan(Request $req){
        // return $req->all();
        $enc_id                 = $req->enc_id;
        if(isset($enc_id)){
            $dec_id                 = $this->safe_decode(Crypt::decryptString($enc_id));

        }
        $no_transaksi           = $req->no_transaksi;
        $array_harga_product    = $req->harga_product;
        $array_product          = $req->produk;
        $array_stock_product    = $req->stock_product;
        $array_qty              = $req->qty;
        $id_sales               = $req->sales;
        $status_pembayaran      = 0; // 1 = lunas, 0 = belum lunas;
        $tgl_jatuh_tempo        = date('Y-m-d',strtotime($req->tgl_jatuh_tempo));
        $tgl_transaksi          = date('Y-m-d', strtotime($req->tgl_transaksi));
        $array_id_satuan        = $req->tipesatuan;
        $id_toko                = $req->toko;
        $array_total_harga      = $req->total;
        $total_product          = $req->total_produk;
        $total_harga_penjualan  = $req->total_harga_penjualan;
        $nilai_diskon           = $req->nilai_diskon;
        $diskon_penjualan       = $req->total_diskon;
        $jenis_pembayaran       = $req->jenis_pembayaran;
        $jumlah_penjualan       = $req->jumlah_penjualan;
        // return $req->all();
        //VALIDASI
        // return $req->all();
            if($no_transaksi == null){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Nomor transaksi harus diisi'
                ]);
            }
            if($jenis_pembayaran == 0){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Jenis pembayaran harus diisi'
                ]);
            }
            if(count($array_total_harga) < 1){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Product harus diisi'
                ]);
            }
            if($id_toko == 0){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Toko harus diisi'
                ]);
            }

            for($i=0;$i<$total_product;$i++){
                if(isset($array_id_satuan[$i])){
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $stockadj = StockAdj::where('id_product', $array_product[$i])->orderBy('gudang_baik', 'DESC')->first();
                    // return $stockadj;
                    $total_qty = $array_qty[$i] * $satuan->qty;
                    if($stockadj->gudang_baik < $total_qty){
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Stock penjualan tidak cukup'
                        ]);
                    }
                }
            }

        //END VALIDASI
        if($enc_id != null || isset($enc_id)){

            // return $dec_id;
            $penjualan = Penjualan::find($dec_id);
            $detail_penjualan = DetailPenjualan::where('id_penjualan', $penjualan->id)->where('no_faktur', $penjualan->no_faktur);
            $penjualan->no_faktur   = $no_transaksi;
            $penjualan->id_sales    = $id_sales;
            $penjualan->id_toko     = $id_toko;
            $penjualan->tgl_jatuh_tempo = $tgl_jatuh_tempo;
            $penjualan->tgl_faktur  = $tgl_transaksi;
            $penjualan->total_harga = $total_harga_penjualan;
            $penjualan->status_lunas = $status_pembayaran;
            $penjualan->total_diskon = $diskon_penjualan;
            $penjualan->jenis_pembayaran = $jenis_pembayaran;
            $penjualan->created_by  = auth()->user()->username;
            if($penjualan->save()){
                $diskon = DiskonPenjualan::where('id_penjualan', $penjualan->id)->first();
                $diskon->tipe_diskon = 0;
                $diskon->jenis_diskon = 0;
                $diskon->nilai_diskon = $nilai_diskon;
                $diskon->jumlah_diskon = $diskon_penjualan;
                if(!$diskon->save()){
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Gagal memasukkan ke table diskon'
                    ]);
                }
                foreach($detail_penjualan->get() as $detail){
                    if($penjualan->status_lunas == 0){
                        $stockadj = StockAdj::where('id_product', $detail->id_product)->first();
                        $stockadj->stock_penjualan  += $detail->qty;
                        $stockadj->stock_approve    -= $detail->qty;
                    }else{
                        $stockadj = StockAdj::where('id_product', $detail->id_product)->first();
                        $stockadj->stock_penjualan  += $detail->qty;
                    }
                    if(!$stockadj->save()){
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Gagal mengupdate stock product'
                        ]);
                        break;
                    }
                }
                if($detail_penjualan->delete()){
                    // return $req->all();
                    for($i=0;$i<$total_product;$i++){
                        if(isset($array_id_satuan[$i])){
                            $satuan = Satuan::find($array_id_satuan[$i]);
                            $detail_penjualan = new DetailPenjualan;
                            $detail_penjualan->id_penjualan = $penjualan->id;
                            $detail_penjualan->no_faktur = $penjualan->no_faktur;
                            $detail_penjualan->id_product = $array_product[$i];
                            $detail_penjualan->qty = $array_qty[$i] * $satuan->qty;
                            $detail_penjualan->harga_product = $array_harga_product[$i];
                            $detail_penjualan->total_harga = $array_total_harga[$i];
                            if($detail_penjualan->save()){
                                if($penjualan->status_lunas == 0){
                                    $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                                    $stockadj->stock_penjualan  -= $detail_penjualan->qty;
                                    $stockadj->stock_approve    += $detail_penjualan->qty;
                                }else{
                                    $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                                    $stockadj->stock_penjualan  -= $detail_penjualan->qty;
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
                                    'message' => 'Gagal menyimpan detail penjualan'
                                ]);
                            }
                        }else{
                            continue;
                        }
                    }
                    $transaksi_stock = TransaksiStock::where('no_transaksi', $penjualan->no_faktur)->first();
                    $transaksi_stock->total_harga = $penjualan->total_harga;
                    if($transaksi_stock->save()){
                        return response()->json([
                            'success' => TRUE,
                            'message' => 'Data penjualan berhasil disimpan'
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
                        'message' => 'Gagal menghapus detail penjualan'
                    ]);
                }
            }
            // return $detail_penjualan;
        }else{

            if($total_product > 0){
                $penjualan = new Penjualan;
                $penjualan->no_faktur   = $no_transaksi;
                $penjualan->id_sales    = $id_sales;
                $penjualan->id_toko     = $id_toko;
                $penjualan->tgl_jatuh_tempo = $tgl_jatuh_tempo;
                $penjualan->tgl_faktur  = $tgl_transaksi;
                $penjualan->total_harga = $total_harga_penjualan;
                $penjualan->status_lunas = $status_pembayaran;
                $penjualan->total_diskon = $diskon_penjualan;
                $penjualan->jenis_pembayaran = $jenis_pembayaran;
                $penjualan->flag_proses = 0;
                $penjualan->created_by  = auth()->user()->username;
                // return $penjualan;
                if($penjualan->save()){
                    $diskon = new DiskonPenjualan;
                    $diskon->id_penjualan = $penjualan->id;
                    $diskon->tipe_diskon = 0;
                    $diskon->jenis_diskon = 0;
                    $diskon->nilai_diskon = $nilai_diskon;
                    $diskon->jumlah_diskon = $diskon_penjualan;
                    if(!$diskon->save()){
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Gagal memasukkan ke table diskon'
                        ]);
                    }
                    for($i=0;$i<$total_product;$i++){
                        $satuan = Satuan::find($array_id_satuan[$i]);
                        $detail_penjualan = new DetailPenjualan;
                        $detail_penjualan->id_penjualan = $penjualan->id;
                        $detail_penjualan->no_faktur = $penjualan->no_faktur;
                        $detail_penjualan->id_product = $array_product[$i];
                        $detail_penjualan->qty = $array_qty[$i] * $satuan->qty;
                        $detail_penjualan->harga_product = $array_harga_product[$i];
                        $detail_penjualan->total_harga = $array_total_harga[$i];
                        if($detail_penjualan->save()){
                            continue;
                            // if($penjualan->status_lunas == 0){
                            //     $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                            //     $stockadj->stock_penjualan  -= $detail_penjualan->qty;
                            //     $stockadj->stock_approve    += $detail_penjualan->qty;
                            // }else{
                            //     $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                            //     $stockadj->stock_penjualan  -= $detail_penjualan->qty;
                            // }
                            // if(!$stockadj->save()){
                            //     return response()->json([
                            //         'success' => FALSE,
                            //         'message' => 'Gagal mengupdate stock product'
                            //     ]);
                            //     break;
                            // }
                        }else{
                            return response()->json([
                                'success' => FALSE,
                                'message' => 'Gagal menyimpan detail penjualan'
                            ]);
                        }
                    }
                    $transaksi_stock = new TransaksiStock;
                    $transaksi_stock->no_transaksi  = $penjualan->no_faktur;
                    $transaksi_stock->tgl_transaksi = $tgl_transaksi;
                    $transaksi_stock->flag_transaksi = 3;
                    $transaksi_stock->total_harga = $penjualan->total_harga;
                    $transaksi_stock->created_by = auth()->user()->username;
                    $transaksi_stock->note = '-';
                    if($transaksi_stock->save()){
                        return response()->json([
                            'success' => TRUE,
                            'message' => 'Penjualan berhasil disimpan'
                        ]);
                    }else{
                        return response()->json([
                            'success' => FALSE,
                            'message' => 'Penjualan gagal disimpan'
                        ]);
                    }
                }else{
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Gagal menyimpan table Penjualan'
                    ]);
                }
            }
        }
    }
    public function proses($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::find($dec_id);
        $gudang = Gudang::all();
        $all_gudang = array();
        foreach($penjualan->getdetailpenjualan as $detail){
            $all = StockAdj::where('id_product', $detail->id_product)->where('gudang_baik', '>=', $detail->qty)->pluck('id_gudang');
            $all_gudang[] = Gudang::whereIn('id', $all)->get();
            // return $all;
        }
        // return $all_gudang[0];
        // return $penjualan->getdetailpenjualan;
        return view('backend/purchase/proses', ['penjualan' => $penjualan, 'gudang' => $all_gudang, 'enc_id' => $enc_id]);
    }
    public function simpan_proses(Request $req){
        $enc_id = $req->enc_id;
        $gudang = $req->gudang;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::find($dec_id);
        // return $req->all();
        foreach($penjualan->getdetailpenjualan as $key => $detail){
            $stockadj = StockAdj::where('id_product', $detail->id_product)->where('id_gudang', $gudang[$key])->first();
            if($penjualan->status_lunas == 0){
                $stockadj->gudang_baik  -= $detail->qty;
                $stockadj->stock_approve    += $detail->qty;
            }else{
                $stockadj->gudang_baik  -= $detail->qty;
            }

            if(!$stockadj->save()){
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupdate stock!'
                ]);
                break;
            }
        }
        $penjualan->flag_proses = 1;
        if($penjualan->save()){
            return response()->json([
                'success' => TRUE,
                'message' => 'Penjualan berhasil diproses'
            ]);
        }else{
            return response()->json([
                'success' => FALSE,
                'message' => 'Penjualan gagal diproses'
            ]);

        }

    }
    public function getsupplier(Request $req){
        // return $req->all();
        $id_gudang = $req->id_gudang;
        $enc_id = $req->enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::find($dec_id);
        $detail_penjualan = DetailPenjualan::where('id_penjualan', $penjualan->id)->pluck('id_product');
        $stockadj = StockAdj::where('id_gudang', $id_gudang)->pluck('id_supplier');

        $cek_stok = StockAdj::with(['getsupplier'])->whereIn('id_product', $detail_penjualan)->where('id_gudang', $id_gudang)->whereIn('id_supplier', $stockadj)->distinct('id_supplier')->get();
        return response()->json([
            'success' => true,
            'data' => $cek_stok
        ]);

    }

    // To Be Continue
    public function harga_product(Request $request){

        // $memberplus      = Member::select('member.id','type_price.name')->join('type_price','type_price.id','member.operation_price')->where('member.id',$request->member)->first();
        // $tambahan = $memberplus?$memberplus->name:0;
        $product = Product::where('id', $request->produk_id)->with(['getstock'])->first();
        // return $product;
        // $diskon  = DiskonDetail::where('produk', $product->id)->where('flag_diskon', 0)->first();
        return response()->json([
            'success' => TRUE,
            'data' => $product,
        ]);
    }
    public function total_harga(Request $request){
        // return $request->all();

        $satuan = Satuan::find($request->satuan_id);

        return response()->json([
            'success' => TRUE,
            'data'  => $satuan,

        ]);
    }
    public function total_diskon(Request $request){
        $harga_penjualan = $request->harga_penjualan;
        $diskon = DiskonDetail::where('flag_diskon', 0)->where('min_beli', '<=', $harga_penjualan)->where('max_beli', '>=', $harga_penjualan)->first();
        if(isset($diskon)){
            $total_diskon = ($harga_penjualan * $diskon->nilai_diskon)/100;
            $nilai_diskon = $diskon->nilai_diskon;
        }else{
            $total_diskon = 0;
            $nilai_diskon = 0;
        }
        return response()->json([
            'success' => true,
            'total_diskon' => round($total_diskon),
            'jumlah_total' => $harga_penjualan - round($total_diskon),
            'nilai_diskon' => $nilai_diskon
        ]);
        // return $total_diskon;

    }
    public function expedisi(Request $request){
        $expedisi = Expedisi::select('id', 'name')->where('status', 1)->get();
        return response()->json([
            'data' => $expedisi
        ]);
    }

    public function simpan_expedisi(Request $request){
        $dec_id = $this->safe_decode(Crypt::decryptString($request->idpo));
        $purchase           = PurchaseOrder::find($dec_id);
        $purchase->expedisi = $request->idexp;
        $purchase->save();
        $dt = new Carbon();
        if($purchase){
            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $purchase->id;
            $polog->keterangan    = auth()->user()->username." Telah Mengupdate Expedisi PO ".$purchase->no_nota;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();
        }

        return response()->json([
            'success'   => TRUE,
            'code'      => 200,
            'msg'       => 'Expedisi Berhasil Diupdate'
        ]);
    }

    public function note(Request $request){
        $poawal             = PurchaseOrder::find($request->idpo);
        $poawal->note       = $request->note;
        $poawal->save();
        $dt = new Carbon();
        if($poawal){
            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = auth()->user()->username." Telah Mengupdate Note PO ".$poawal->no_nota;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();
        }

        return response()->json([
            'success'   => TRUE,
            'code'      => 200,
            'msg'       => 'Note Berhasil Diupdate'
        ]);
    }

    public function updatepo(Request $request){
        $field = $request->field;
        $purchasedetail = PurchaseOrderDetail::find($request->id_po);
        $checkproduct = Product::find($purchasedetail->product_id);
        if($checkproduct->is_liner == 'Y'){
            //TAMBAHAN
            if($checkproduct->product_code==$checkproduct->product_code_shadow){
                //MASTER
                $satuanvalue   = $checkproduct->satuan_value;
                $productid     = $checkproduct->id;
            }else{
                //SUB
                $product       = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                $satuanvalue   = $checkproduct->satuan_value;
                $productid     = $product->id;
            }
            $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $purchasedetail->perusahaan_id)->where('gudang_id',$purchasedetail->gudang_id)->first();
            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
        }else{
            // $satuanvalue     = $checkproduct->satuan_value;
            $satuanvalue        = 1;
            $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $purchasedetail->perusahaan_id)->where('gudang_id',$purchasedetail->gudang_id)->first();
            $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $purchasedetail->product_id)->first();
        }
        if($field == 'qty_kirim'){
            if($purchasedetail->qty < $request->value){
                $reqsult = 'qty more';
                if($purchasedetail->qty_kirim == NULL){
                    $qty_kirim = $purchasedetail->qty;
                    $qtykirim    = array(
                        'success'  => TRUE,
                        'description' => 'quantity pengiriman disamakan dengan permintaan',
                        'alternate'   => 'karena melebihi quantity permintaan',
                        'value'       => $purchasedetail->qty
                    );
                }else{
                    $qty_kirim = $purchasedetail->qty;
                    // $qty_sisa = $purchasedetail->qty - $purchasedetail->qty_kirim;
                    // $product_gudang->stok = $product_gudang->stok - ($qty_sisa * $satuanvalue);
                    // $product_gudang->save();
                    $qtykirim    = array(
                        'success'  => TRUE,
                        'description' => 'quantity pengiriman disamakan dengan permintaan',
                        'alternate'   => 'karena melebihi quantity permintaan',
                        'value'       => $purchasedetail->qty
                    );
                }
            }else if($purchasedetail->qty >= $request->value){
                $qty_kirim = $request->value;
                $qty_sisa = $purchasedetail->qty - $request->value;
                // if($purchasedetail->qty_kirim == NULL){
                //     $product_gudang->stok = $product_gudang->stok + ($qty_sisa * $satuanvalue);
                //     $product_gudang->save();
                // }else{
                //     if($purchasedetail->qty_kirim > $request->value){
                //         $qty_check = (($purchasedetail->qty_kirim - $request->value) * $satuanvalue);
                //         $product_gudang->stok += $qty_check;
                //         $product_gudang->save();
                //     }else{
                //         $qty_check = (($request->value - $purchasedetail->qty_kirim) * $satuanvalue);
                //         $product_gudang->stok -= $qty_check;
                //         $product_gudang->save();
                //     }
                // }
                $qtykirim    = array(
                    'success'  => FALSE,
                    'value'       => $request->value
                );
            }else{
                $qty_kirim = $request->value;
                $qtykirim    = array(
                    'success'  => FALSE,
                    'value'       => $request->value
                );
            }
            $purchasedetail->$field = $qty_kirim;
            $purchasedetail->save();

            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Berhasil diperbarui',
                'qtykirim'  => $qtykirim
            ]);
        }else{
            $qty_kirim = $request->value;
            $qtykirim = array(
                'success'  => FALSE
            );
            // $product_gudang->save();
            $purchasedetail->$field = $qty_kirim;
            $purchasedetail->save();

            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Berhasil diupdate',
                'qtykirim'  => $qtykirim
            ]);
        }

    }

    public function updatepokrnditolak(Request $request){
        $field = $request->field;
        //dd($field);
        $purchasedetail = PurchaseOrderDetail::find($request->id_po);
        if($field == 'price'){
            $purchasedetail->price     = str_replace(".", "",$request->value);
            $unitsetelahdiskon =  str_replace(".", "",$request->value) - round(($purchasedetail->discount/100 *  str_replace(".", "",$request->value)));
            $purchasedetail->ttl_price = $purchasedetail->qty * $unitsetelahdiskon;
            $purchasedetail->save();
            $updatedata = PurchaseOrderDetail::where('transaction_purchase_id',$purchasedetail->transaction_purchase_id)->get();
            $subtotal =0;
            foreach ($updatedata as $key => $value) {
                $subtotal   += round($value->ttl_price);
            }
            $fixpo	= PurchaseOrder::find($purchasedetail->transaction_purchase_id);
            $fixpo->sub_total = $subtotal;
            $fixpo->total     = $subtotal;
            $fixpo->save();
            $total_price      =  $fixpo->total;
            $subTotal         =  $fixpo->sub_total;
            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Harga Berhasil diperbarui',
                'totalsebelumdiskon'  => number_format(round(str_replace(".", "",$request->value) *  $purchasedetail->qty), 0, ',', '.'),
                'unitsetelahdiskon'   => number_format(round($unitsetelahdiskon),0, ',', '.'),
                'hargasetelahdiskon'  => number_format($purchasedetail->ttl_price),
                'total_price'         => number_format(round($total_price),2, ',', '.'),
                'subTotal'         => number_format(round($subTotal),2, ',', '.'),
            ]);
        }
        if($field == 'diskon'){
            $purchasedetail->discount  = $request->value;
            $unitsetelahdiskon         = $purchasedetail->price - round(($request->value/100 *  $purchasedetail->price));
            $purchasedetail->ttl_price = $purchasedetail->qty * $unitsetelahdiskon;
            $purchasedetail->save();
            $updatedata = PurchaseOrderDetail::where('transaction_purchase_id',$purchasedetail->transaction_purchase_id)->get();
            $subtotal =0;
            foreach ($updatedata as $key => $value) {
                $subtotal   += round($value->ttl_price);
            }
            $fixpo	= PurchaseOrder::find($purchasedetail->transaction_purchase_id);
            $fixpo->sub_total = $subtotal;
            $fixpo->total     = $subtotal;
            $fixpo->save();
            $total_price      =  $fixpo->total;
            $subTotal         =  $fixpo->sub_total;
            return response()->json([
                'success'   => TRUE,
                'code'      => 200,
                'msg'       => 'PO Diskon Berhasil diperbarui',
                'totalsebelumdiskon'  => number_format(round($purchasedetail->price *  $purchasedetail->qty), 0, ',', '.'),
                'unitsetelahdiskon'   => number_format(round($unitsetelahdiskon),0, ',', '.'),
                'hargasetelahdiskon'  => number_format($purchasedetail->ttl_price),
                'total_price'         => number_format(round($total_price),2, ',', '.'),
                'subTotal'            => number_format(round($subTotal),2, ',', '.'),
            ]);
        }

    }
    public function detail($enc_id){
        // return $req->all();
        // return $enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::where('id', $dec_id)->with(['getsales', 'gettoko', 'getdetailpenjualan'])->first();
        $detail_penjualan = $penjualan->getdetailpenjualan;
        if($penjualan->jenis_pembayaran == 1){
            $jenis_pembayaran = "Cash";
        }else if($penjualan->jenis_pembayaran == 2){
            $jenis_pembayaran = "Cek / Giro";
        }else{
            $jenis_pembayaran = "Transfer";
        }
        // return $penjualan;
        return view('backend/purchase/detail',
        [
            'enc_id' => $enc_id,
            'penjualan' => $penjualan,
            'detail_penjualan' => $detail_penjualan,
            'jenis_pembayaran' => $jenis_pembayaran
        ]);
        // return $penjualan;
    }
    public function cetak($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = Penjualan::where('id', $dec_id)->with(['getsales', 'gettoko', 'getdetailpenjualan'])->first();
        $detail_penjualan = $penjualan->getdetailpenjualan;
        if($penjualan->jenis_pembayaran == 1){
            $jenis_pembayaran = "Cash";
        }else if($penjualan->jenis_pembayaran == 2){
            $jenis_pembayaran = "Cek / Giro";
        }else{
            $jenis_pembayaran = "Transfer";
        }
        // return $penjualan;
        return view('backend/purchase/cetak',
        [
            'enc_id' => $enc_id,
            'penjualan' => $penjualan,
            'detail_penjualan' => $detail_penjualan,
            'jenis_pembayaran' => $jenis_pembayaran
        ]);
    }

    public function detail_(Request $request){
        $dec_id = $this->safe_decode(Crypt::decryptString($request->enc_id));
        $purchase = PurchaseOrder::find($dec_id);

        if($purchase->status=='2'){
            $dis='readonly';
            $buttonsimpan = "disable";
        }else{
            $dis='';
            $buttonsimpan = "";
        }

        $detail = PurchaseOrderDetail::select('id','transaction_purchase_id','gudang_id','product_id','qty','ttl_price','perusahaan_id','discount','satuan','price', 'qty_kirim','colly','colly_to','weight')->where('transaction_purchase_id', $dec_id);
        $purchasedetail = $detail->get();
        $gudang = PurchaseOrderDetail::where('transaction_purchase_id', $dec_id)->first();
        $member = Member::find($purchase->member_id);
        $perusahaan = Perusahaan::find($purchase->perusahaan_id);
        $gudangname = Gudang::find($gudang->gudang_id);
        $header = '';
        $header.= '<tr>';
            $header.='<th>Produk</th>';
            $header.='<th>Qty Order</th>';
            if($request->user()->can('purchaseorder.liststatusgudang')){
                $header.='<th>Qty Kirim</th>';
                $header.='<th colspan="3" class="col-sm-2 text-center">Colly</th>';
                $header.='<th>Berat (Kg)</th>';
            }
            if($request->user()->can('purchaseorder.liststatuspo') || $request->user()->can('purchaseorder.liststatusinvoice')){
                $header.='<th>Diskon (%)</th>';
                $header.='<th>Unit Sebelum Diskon</th>';
                $header.='<th>Harga Total Sebelum Diskon</th>';
                $header.='<th>Unit Setelah Diskon</th>';
                $header.='<th>Harga Setelah Diskon</th>';
            }
        $header.= '</tr>';

        $html='';
        $subtotal = 0;

        foreach ($purchasedetail as $key => $value) {
            // dd();
            $total  =  round($value->ttl_price);

            $totaldiscount = ($value->discount/100)*($value->price * $value->qty);
            $unitdiskon    = round($value->price*($value->discount/100));

            $product = Product::find($value->product_id);
            $value->product_name = $product->product_name;
            $value->product_code = $product->product_code;
            $value->total_discount = round($value->ttl_price - $totaldiscount);
            $subtotal   += round($value->ttl_price);
            $value->unit_discount = $value->price - $unitdiskon;

            if($value->qty_kirim == NULL){
                $value->qty_kirim = 0;
            }else{
                $value->qty_kirim = $value->qty_kirim;
            }

            if($value->colly == NULL){
                $value->colly = 0;
            }else{
                $value->colly = $value->colly;
            }

            if($value->colly == NULL){
                $value->colly_to = 0;
            }else{
                $value->colly_to = $value->colly_to;
            }

            if($value->weight == NULL){
                $value->weight = 0;
            }else{
                $value->weight = $value->weight;
            }

            $html.='<tr>';
                $html.='<td><b>'.$value->product_name.'</b><br/><p style="color:#1c84c6">'.$value->product_code.'</p></td>';
                $html.='<td>'.$value->qty.' '.$value->satuan.'</td>';
                if($request->user()->can('purchaseorder.liststatusgudang')){
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" min="1" '.$dis.' id="qty_kirim_'.$value->id.'" name="qty_kirim" value="'.$value->qty_kirim.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td>';
                    $html.='<input class="form-control col-xs-3" type="text" id="colly_'.$value->id.'" '.$dis.'  name="colly" value="'.$value->colly.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td> - </td>';
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" id="colly_to_'.$value->id.'" '.$dis.'  name="colly_to" value="'.$value->colly_to.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                    $html.='<td>';
                        $html.='<input class="form-control col-xs-3" type="text" id="weight_'.$value->id.'"  '.$dis.'  name="weight" value="'.$value->weight.'" onchange="updatepo('.$value->id.', this.name,)">';
                    $html.='</td>';
                }

                if($request->user()->can('purchaseorder.liststatuspo') || $request->user()->can('purchaseorder.liststatusinvoice')){

                    if($request->user()->can('purchaseorder.updatepokrnditolak')){
                        if($purchase->status_invoice=='2'  && $purchase->status_gudang=='2' && $purchase->flag_status=='0' && $purchase->type=='2'){
                            $html.='<td class="text-left"><input class="form-control col-xs-3 editdiskon" type="text" min="0" id="diskon_'.$value->id.'" '.$dis.'  name="diskon" value="'.$value->discount.'" onchange="updatepokrntolak('.$value->id.', this.name,)"></td>';
                            $html.='<td class="text-left"><input class="form-control col-xs-3 editprice" type="text" min="0" id="price_'.$value->id.'" '.$dis.'  name="price" value="'.$value->price.'" onchange="updatepokrntolak('.$value->id.', this.name,)"></td>';
                        }else{
                            $html.='<td>'.$value->discount.'</td>';
                            $html.='<td class="text-left">'.number_format($value->price, 2, ',', '.').'</td>';
                        }

                    }else{
                        $html.='<td>'.$value->discount.'</td>';
                        $html.='<td class="text-left">'.number_format($value->price, 2, ',', '.').'</td>';
                    }
                    $html.='<td class="text-left" id="hargatotalsebelumdiskon_'.$value->id.'">'.number_format($value->price * $value->qty, 2, ',', '.').'</td>';
                    $html.='<td class="text-left" id="unitsetelahdiskon_'.$value->id.'">'.number_format($value->unit_discount, 2, ',', '.').'</td>';
                    $html.='<td class="text-left" id="hargasetelahdiskon_'.$value->id.'">'.number_format($total, 2, ',', '.').'</td>';

                }
            $html.='</tr>';


        }
        $detailtable[] = $html;
        $purchase->sub_total = $subtotal;

        // you can move this bottom code when the project done on testing by vendor and client
        // fix subtotal when error counting on RPO or BO
        // can move
        $fixpo	= PurchaseOrder::find($dec_id);
        $fixpo->sub_total = $subtotal;
        $fixpo->total     = $subtotal;
        $fixpo->save();
        // end can move

        return response()->json([
            'po'    => [
                'member' => [
                    'name'      => $member->name,
                    'phone'     => $member->phone,
                    'address'   => $member->address
                ],
                'perusahaan' => [
                    'name'      => $perusahaan->name,
                    'alamat'    => $perusahaan->address,
                    'contact'   => $perusahaan->telephone,
                    'kota'      => $perusahaan->city,
                ],
                'purchase'   => [
                    'id'        => $dec_id,
                    'no_nota'   => $purchase->no_nota,
                    'note'      => $purchase->note,
                    'gudang'    => $gudangname->name,
                    'sub_total' => number_format($purchase->sub_total, 2, ',', '.'),
                    'total'     => number_format($purchase->total, 2, ',', '.'),
                    'ttl_pesan' => date("d M Y",strtotime($purchase->created_at))
                ],

            ],
            // 'data' => $perusahaan,
            'po_detail' => $html,
            'header'    => $header,
            'detailpo'  => $purchasedetail,
            'dec_id'      => $dec_id,
            "buttonsimpan"=> $buttonsimpan,
            "dis"       => $dis
        ]);
    }
    // proses status PO
    // proses status PO
    public function status_po(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt                 = new Carbon();
        if($request->status == 1){
            $description            = auth()->user()->username." Telah Mengupdate PO untuk di proses INVOICE";
            $poawal->status         = $request->status;
            $poawal->status_gudang  = 0;
            $poawal->status_invoice = 0;
            $poawal->type           = 0;
            $poawal->updated_at    = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);


        }else if($request->status == 2){
            // DITOLAK PO LAIN-LAIN
            $poawal->note  = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);
                    if($checkproduct->is_liner == 'Y'){
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $product_shadow->id;
                        }
                        $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($value->qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $satuanvalue    = $checkproduct->satuan_value;
                        $satuanvalue       = 1;
                        $perusahaan_gudang      = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang         = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok   = $product_gudang->stok + ($value->qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "PO-".ucfirst(auth()->user()->username)." Menolak PO catatan : " .$request->note;
                //TAMBAHAN
                $poawal->flag_status    =0; //0:PO 1:RPO
                $poawal->status_rpo     =1;
                $poawal->status         =2;
                $poawal->status_gudang  =2;
                $poawal->status_invoice =2;
                $poawal->type           =1;

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $poawal->updated_at     =now();
                $poawal->save();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();

                // TAMBAH KE RPO

                $newPOtoRPO = new PurchaseOrder();
                $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                $newPOtoRPO->dataorder     = $poawal->dataorder;
                $newPOtoRPO->member_id     = $poawal->member_id;
                $newPOtoRPO->sales_id      = $poawal->sales_id;
                $newPOtoRPO->sub_total     = $poawal->sub_total;
                $newPOtoRPO->discount      = $poawal->discount;
                $newPOtoRPO->total         = $poawal->total;
                $newPOtoRPO->note          = $poawal->note;
                $newPOtoRPO->duedate       = $poawal->duedate;
                $newPOtoRPO->status        = 0;
                $newPOtoRPO->pay_status    = $poawal->pay_status;
                $newPOtoRPO->access        = $poawal->access;
                $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                $newPOtoRPO->status_gudang = $poawal->status_gudang;
                $newPOtoRPO->status_po     = $poawal->status_po;
                $newPOtoRPO->status_invoice= $poawal->status_invoice;
                $newPOtoRPO->type          = $poawal->type;
                $newPOtoRPO->flag_status   = 1;
                $newPOtoRPO->read          = $poawal->read;
                $newPOtoRPO->createdby     = $poawal->createdby;
                $newPOtoRPO->createdon     = $poawal->createdon;
                $newPOtoRPO->updatedby     = $poawal->updatedby;
                $newPOtoRPO->updatedon     = $poawal->updatedon;
                $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                $newPOtoRPO->updated_po    = $poawal->updated_po;
                $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                $newPOtoRPO->expedisi      = $poawal->expedisi;
                $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                $newPOtoRPO->update_date   = $poawal->update_date;
                $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                $newPOtoRPO->draft         = $poawal->draft;
                $newPOtoRPO->created_at    = $poawal->created_at;
                $newPOtoRPO->updated_at    = $poawal->updated_at;
                $newPOtoRPO->save();

                // DETAIL
                foreach ($podetail as $key => $detail) {
                        $newdetail = new PurchaseOrderDetail();
                        $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                        $newdetail->perusahaan_id           = $detail->perusahaan_id;
                        $newdetail->gudang_id               = $detail->gudang_id;
                        $newdetail->product_id              = $detail->product_id;
                        $newdetail->product_id_shadow       = $detail->product_id_shadow;
                        $newdetail->type                    = $detail->type;
                        $newdetail->qty                     = $detail->qty;
                        $newdetail->qty_kirim               = $detail->qty_kirim;
                        $newdetail->price                   = $detail->price;
                        $newdetail->discount                = $detail->discount;
                        $newdetail->ttl_price               = $detail->ttl_price;
                        $newdetail->weight                  = $detail->weight;
                        $newdetail->colly                   = $detail->colly;
                        $newdetail->colly_to                = $detail->colly_to;
                        $newdetail->satuan                  = $detail->satuan;
                        $newdetail->created_at              = $detail->created_at;
                        $newdetail->updated_at              = $detail->updated_at;
                        $newdetail->save();
                }

                // COPY LOG PO SEBELUMNYA
                $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                foreach ($recordlogs as $keyr => $recordlog) {
                    $pologold  = new PurchaseOrderLog();
                    $pologold->user_id       = $recordlog->user_id;
                    $pologold->purchase_id   = $newPOtoRPO->id;
                    $pologold->keterangan    = $recordlog->keterangan;
                    $pologold->create_date   = $recordlog->create_date;
                    $pologold->create_user   = $recordlog->create_user;
                    $pologold->save();
                }

                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);

            }
        }

    }

    // proses status
    public function status_gudang(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt = new Carbon();
        if($request->status == 1){
            $cek_approved_gudang = PurchaseOrder::where('status_gudang',1)->where('status_po',2)->where('id',$request->id_po)->first();
            if(!$cek_approved_gudang){
                $description = auth()->user()->username." Telah Mengupdate PO untuk diproses INVOICE AKHIR";
                $detaildata  = 0;
                $adabo       = 0;
                foreach ($podetail as $k => $item) {
                    if($item->qty != $item->qty_kirim){
                        $detaildata += 1;
                    }
                }
                if($detaildata > 0){
                    $purchaseOrderBo                  = new PurchaseOrder();
                    $purchaseOrderBo->kode_rpo        = 'BO-'.$poawal->no_nota;
                    $purchaseOrderBo->dataorder       = now();
                    $purchaseOrderBo->member_id       = $poawal->member_id;
                    $purchaseOrderBo->sales_id        = $poawal->sales_id;
                    $purchaseOrderBo->note            = null;
                    $purchaseOrderBo->duedate         = now();
                    $purchaseOrderBo->access          = 1;
                    $purchaseOrderBo->flag_status     = 2;
                    $purchaseOrderBo->sub_total       = 0;
                    $purchaseOrderBo->total           = 0;
                    $purchaseOrderBo->createdby       = auth()->user()->fullname;
                    $purchaseOrderBo->createdon       = auth()->user()->username;
                    $purchaseOrderBo->expedisi        = $poawal->expedisi;
                    $purchaseOrderBo->expedisi_via    = $poawal->expedisi_via;
                    $purchaseOrderBo->draft           = 0;
                    $purchaseOrderBo->save();
                    $adabo = 1;
                }
                foreach ($podetail as $key => $value) {
                    $product = Product::find($value->product_id);
                    if($product->product_code_shadow==null){
                        $productid     = $product->id;
                        $satuan_value  = 1;
                    }else{
                        if($product->product_code_shadow==$product->product_code){
                            $productid    = $product->id;
                            $satuan_value = $product->satuan_value;
                        }else{
                            $cekinduk      = Product::where('product_code',$product->product_code_shadow)->first();
                            $productid     = $cekinduk->id;
                            $satuan_value  = $product->satuan_value;
                        }
                    }

                    $report = new ReportStock();
                    $report->product_id             = $value->product_id;
                    $report->product_id_shadow      = $productid;
                    $report->transaction_no         = $poawal->no_nota;
                    $report->gudang_id              = $value->gudang_id;
                    $report->perusahaan_id          = $value->perusahaan_id;
                    $report->stock_input            = $value->qty_kirim;
                    $report->purchase_detail_id     = $value->id;
                    $report->note                   = 'Purchase Barang Keluar';
                    $report->keterangan             = 'Purchase Keluar';
                    $report->created_by             = auth()->user()->username;
                    $report->save();
                    // jika qty order != qty_kirim
                    $totalprice = 0;
                    if($value->qty != $value->qty_kirim){
                        $qty                               = $value->qty - $value->qty_kirim;
                        $podetail                          = new PurchaseOrderDetail();
                        $podetail->transaction_purchase_id = $purchaseOrderBo->id;
                        $podetail->product_id              = $value->product_id;
                        $podetail->product_id_shadow       = $value->product_id_shadow;
                        $podetail->discount                = $value->discount;
                        $podetail->type                    = $value->type;
                        $podetail->qty                     = $qty;
                        $podetail->price                   = $value->price;
                        $totalprice                        = $value->price-round($value->discount/100 * $value->price);
                        $podetail->ttl_price               = $totalprice * $qty;
                        $podetail->satuan                  = $value->satuan;
                        $podetail->save();

                        //update podetail
                        $updatepodetail = PurchaseOrderDetail::find($value->id);
                        // $updatepodetail->qty        = $value->qty_kirim;
                        $updatepodetail->ttl_price  = $totalprice * $value->qty_kirim;
                        $updatepodetail->save();

                        //pengembalian stok ;
                        $perusahaan_gudang     = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang        = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $qty_check             = $qty * $satuan_value;
                        $product_gudang->stok += $qty_check;
                        $product_gudang->save();
                    }
                }
                //update total
                if($adabo==1){
                    $purchaseOrderBoUpdate                  = PurchaseOrder::find($purchaseOrderBo->id);
                    $totalprice                             = PurchaseOrderDetail::where('transaction_purchase_id', $purchaseOrderBo->id)->sum('ttl_price');
                    $purchaseOrderBoUpdate->sub_total       = $totalprice;
                    $purchaseOrderBoUpdate->total           = $totalprice;
                    $purchaseOrderBoUpdate->save();

                    $dt = new Carbon();
                    $pologbo  = new PurchaseOrderLog();
                    $pologbo->user_id        = auth()->user()->id;
                    $pologbo->purchase_id    = $purchaseOrderBoUpdate->id;
                    $pologbo->keterangan     = auth()->user()->username." Telah Mengupdate PO ke Backorder ".$purchaseOrderBoUpdate->kode_rpo;
                    $pologbo->create_date    = $dt->toDateTimeString();
                    $pologbo->create_user    = auth()->user()->username;
                    $pologbo->save();

                    $total_sub_total = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->sum('ttl_price');
                    $poawal->sub_total     = $total_sub_total;
                    $poawal->total         = $total_sub_total;

                }
                $poawal->status        = $request->status;
                $poawal->status_gudang = 1;
                $poawal->status_po     = 2;
                $poawal->updated_at    = now();
                $poawal->save();


                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }else{
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }

        }else if($request->status == 2){
            // DITOLAK GUDANG
            $poawal->note     = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);
                    ReportStock::where('transaction_no',$poawal->no_nota)->where('product_id', $value->product_id)->delete();
                    if($checkproduct->is_liner == 'Y'){
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue    = $checkproduct->satuan_value;
                            $productid      = $product_shadow->id;
                        }
                        $qty                  = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue            = 1;
                        $qty                    = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $perusahaan_gudang      = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang         = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok   = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "GDG-".ucfirst(auth()->user()->username)." Menolak PO catatan : " .$request->note;
                $poawal->status_gudang  = 2;
                //TAMBAHAN TETEP JADI PO
                $poawal->flag_status    = 0; //0: PO 1: RPO 2:BO
                $poawal->status_rpo     = 1;
                $poawal->status         = 2;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->updated_at     = now();
                $poawal->type           = $request->type;
                $poawal->save();

                if($poawal){
                    $polog  = new PurchaseOrderLog();
                    $polog->user_id        = auth()->user()->id;
                    $polog->purchase_id    = $poawal->id;
                    $polog->keterangan     = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO

                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();
                }
                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);


            }else if($request->status == 2){
                $description = "GDG-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                $poawal->status_gudang = 2;
                $poawal->status_invoice =2;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status   = 0;
                $poawal->status_rpo    = 0;
                $poawal->status        = 0;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);
            }
        }
    }

    public function status_invoice_awal(Request $request){
        $poawal       = PurchaseOrder::find($request->id_po);
        $podetail     = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt           = new Carbon();
        if($request->status == 1){

            $description            = auth()->user()->username." Telah Mengupdate PO untuk diproses GUDANG";
            $poawal->status         = $request->status;
            $poawal->status_invoice = 1;
            $poawal->updated_at     = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id        = auth()->user()->id;
            $polog->purchase_id    = $poawal->id;
            $polog->keterangan     = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);

        }else if($request->status == 2){
            // DITOLAK INVOICE AWAL
            $poawal->note     = $request->note;
            if($request->type=='1'){
                foreach ($podetail as $key => $value) {
                    $checkproduct   = Product::find($value->product_id);
                    if($checkproduct->is_liner == 'Y'){
                        //TAMBAHAN
                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow= Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $product_shadow->id;
                        }
                        // $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $qty    = $value->qty;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        // $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $qty               = $value->qty;
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue       = 1;
                        $perusahaan_gudang = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang    = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }

                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO dengan catatan : " .$request->note;
                $poawal->status_invoice = 2;
                //TAMBAHAN KEMBALI KE RPO
                $poawal->flag_status    = 0; // 0 PO; 1 RPO
                $poawal->status_rpo     = 1;
                $poawal->status         = 2;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->type           = $request->type;
                $poawal->updated_at     = now();
                $poawal->save();

                if($poawal){

                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                    $polog  = new PurchaseOrderLog();
                    $polog->user_id        = auth()->user()->id;
                    $polog->purchase_id    = $poawal->id;
                    $polog->keterangan     = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO
                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                }
                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);


            }else if($request->type=='2'){

                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status   = 0;
                $poawal->status_rpo    = 0;
                $poawal->status        = 0;
                $poawal->status_gudang = 2;
                $poawal->status_invoice= 2;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id        = auth()->user()->id;
                $polog->purchase_id    = $poawal->id;
                $polog->keterangan     = $description;
                $polog->create_date    = $dt->toDateTimeString();
                $polog->create_user    = auth()->user()->username;
                $polog->save();


                return response()->json([
                    'code' => 200,
                    'msg'  => 'status berhasil diubah'
                ]);
            }


        }
    }

    public function status_invoice(Request $request){
        $poawal             = PurchaseOrder::find($request->id_po);
        $podetail           = PurchaseOrderDetail::where('transaction_purchase_id', $request->id_po)->get();
        $dt = new Carbon();
        //dd($request->type);
        if($request->status == 1){
            $description = auth()->user()->username." Telah Mengupdate PO menjadi INVOICE";
            $poawal->status_gudang = 1;
            $poawal->status_po     = 2;
            $poawal->status        = $request->status;
            $poawal->updated_at    = now();
            $poawal->save();

            $polog  = new PurchaseOrderLog();
            $polog->user_id       = auth()->user()->id;
            $polog->purchase_id   = $poawal->id;
            $polog->keterangan    = $description;
            $polog->create_date    = $dt->toDateTimeString();
            $polog->create_user    = auth()->user()->username;
            $polog->save();

            return response()->json([
                'code' => 200,
                'msg'  => 'status berhasil diubah'
            ]);
        }else if($request->status == 2){
            $poawal->note     = $request->note;
            if($request->type=='1'){

                foreach ($podetail as $key => $value) {
                    $checkproduct = Product::find($value->product_id);

                    if($checkproduct->is_liner == 'Y'){
                        $perusahaan_gudang  = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();

                        if($checkproduct->product_code==$checkproduct->product_code_shadow){
                            //MASTER
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $checkproduct->id;
                        }else{
                            //SUB
                            $product_shadow = Product::where('product_code', $checkproduct->product_code_shadow)->first();
                            $satuanvalue   = $checkproduct->satuan_value;
                            $productid     = $product_shadow->id;
                        }
                        $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $productid)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }else{
                        $qty = $value->qty_kirim!=null?$value->qty_kirim:$value->qty;
                        // $satuanvalue   = $checkproduct->satuan_value;
                        $satuanvalue   = 1;
                        $perusahaan_gudang    = PerusahaanGudang::where('perusahaan_id', $value->perusahaan_id)->where('gudang_id',$value->gudang_id)->first();
                        $product_gudang       = ProductPerusahaanGudang::where('perusahaan_gudang_id', $perusahaan_gudang->id)->where('product_id', $value->product_id)->first();
                        $product_gudang->stok = $product_gudang->stok + ($qty * $satuanvalue);
                        $product_gudang->save();
                    }
                }
                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO dengan catatan : ".$request->note;
                $poawal->status_gudang = 2;
                //TAMBAHAN
                $poawal->flag_status   = 0; //0:PO 1:RPO
                $poawal->status        = 2;
                $poawal->status_rpo    = 1;
                $poawal->status_gudang = 2;
                $poawal->status_invoice= 2;
                $poawal->status_po     = 0;
                $poawal->type          = $request->type;
                $poawal->updated_at    = now();
                $poawal->save();

                if($poawal){

                    ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                    $polog  = new PurchaseOrderLog();
                    $polog->user_id       = auth()->user()->id;
                    $polog->purchase_id   = $poawal->id;
                    $polog->keterangan    = $description;
                    $polog->create_date    = $dt->toDateTimeString();
                    $polog->create_user    = auth()->user()->username;
                    $polog->save();

                    // TAMBAH KE RPO
                    $newPOtoRPO = new PurchaseOrder();
                    $newPOtoRPO->perusahaan_id = $poawal->perusahaan_id;
                    $newPOtoRPO->kode_rpo      = $this->genRPO($poawal->sales_id);
                    $newPOtoRPO->dataorder     = $poawal->dataorder;
                    $newPOtoRPO->member_id     = $poawal->member_id;
                    $newPOtoRPO->sales_id      = $poawal->sales_id;
                    $newPOtoRPO->sub_total     = $poawal->sub_total;
                    $newPOtoRPO->discount      = $poawal->discount;
                    $newPOtoRPO->total         = $poawal->total;
                    $newPOtoRPO->note          = $poawal->note;
                    $newPOtoRPO->duedate       = $poawal->duedate;
                    $newPOtoRPO->status        = 0;
                    $newPOtoRPO->pay_status    = $poawal->pay_status;
                    $newPOtoRPO->access        = $poawal->access;
                    $newPOtoRPO->status_rpo    = $poawal->status_rpo;
                    $newPOtoRPO->status_gudang = $poawal->status_gudang;
                    $newPOtoRPO->status_po     = $poawal->status_po;
                    $newPOtoRPO->status_invoice= $poawal->status_invoice;
                    $newPOtoRPO->type          = $poawal->type;
                    $newPOtoRPO->flag_status   = 1;
                    $newPOtoRPO->read          = $poawal->read;
                    $newPOtoRPO->createdby     = $poawal->createdby;
                    $newPOtoRPO->createdon     = $poawal->createdon;
                    $newPOtoRPO->updatedby     = $poawal->updatedby;
                    $newPOtoRPO->updatedon     = $poawal->updatedon;
                    $newPOtoRPO->count_cetak   = $poawal->count_cetak;
                    $newPOtoRPO->updated_po    = $poawal->updated_po;
                    $newPOtoRPO->updated_gudang= $poawal->updated_gudang;
                    $newPOtoRPO->expedisi      = $poawal->expedisi;
                    $newPOtoRPO->expedisi_via  = $poawal->expedisi_via;
                    $newPOtoRPO->update_date   = $poawal->update_date;
                    $newPOtoRPO->update_date_gudang   = $poawal->update_date_gudang;
                    $newPOtoRPO->update_date_invoice  = $poawal->update_date_invoice;
                    $newPOtoRPO->draft         = $poawal->draft;
                    $newPOtoRPO->created_at    = $poawal->created_at;
                    $newPOtoRPO->updated_at    = $poawal->updated_at;
                    $newPOtoRPO->save();

                    // DETAIL
                    foreach ($podetail as $key => $detail) {
                            $newdetail = new PurchaseOrderDetail();
                            $newdetail->transaction_purchase_id = $newPOtoRPO->id;
                            $newdetail->perusahaan_id           = $detail->perusahaan_id;
                            $newdetail->gudang_id               = $detail->gudang_id;
                            $newdetail->product_id              = $detail->product_id;
                            $newdetail->product_id_shadow       = $detail->product_id_shadow;
                            $newdetail->type                    = $detail->type;
                            $newdetail->qty                     = $detail->qty;
                            $newdetail->qty_kirim               = $detail->qty_kirim;
                            $newdetail->price                   = $detail->price;
                            $newdetail->discount                = $detail->discount;
                            $newdetail->ttl_price               = $detail->ttl_price;
                            $newdetail->weight                  = $detail->weight;
                            $newdetail->colly                   = $detail->colly;
                            $newdetail->colly_to                = $detail->colly_to;
                            $newdetail->satuan                  = $detail->satuan;
                            $newdetail->created_at              = $detail->created_at;
                            $newdetail->updated_at              = $detail->updated_at;
                            $newdetail->save();
                    }

                    // COPY LOG PO SEBELUMNYA
                    $recordlogs = PurchaseOrderLog::where('purchase_id',$poawal->id)->get();
                    foreach ($recordlogs as $keyr => $recordlog) {
                        $pologold  = new PurchaseOrderLog();
                        $pologold->user_id       = $recordlog->user_id;
                        $pologold->purchase_id   = $newPOtoRPO->id;
                        $pologold->keterangan    = $recordlog->keterangan;
                        $pologold->create_date   = $recordlog->create_date;
                        $pologold->create_user   = $recordlog->create_user;
                        $pologold->save();
                    }
                }

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);

            }else if($request->type=='2'){
                $description = "INV-".ucfirst(auth()->user()->username)." Menolak PO karena salah harga dengan catatan : ".$request->note;
                $poawal->status_invoice = 0;
                //TAMBAHAN KEMBALI KE PO AWAL
                $poawal->flag_status    = 0;
                $poawal->status_rpo     = 0;
                $poawal->status         = 0;
                $poawal->status_gudang  = 2;
                $poawal->status_invoice = 2;
                $poawal->status_po      = 0;
                $poawal->type           = $request->type;
                $poawal->updated_at     = now();
                $poawal->save();

                ReportStock::where('transaction_no',$poawal->no_nota)->delete();

                $polog  = new PurchaseOrderLog();
                $polog->user_id       = auth()->user()->id;
                $polog->purchase_id   = $poawal->id;
                $polog->keterangan    = $description;
                $polog->create_date   = $dt->toDateTimeString();
                $polog->create_user   = auth()->user()->username;
                $polog->save();

                return response()->json([
                    'code' => 200,
                    // 'data' => $product_gudang
                    'msg'  => 'status berhasil diubah'
                ]);

            }
        }
    }

    public function check_gudang($idpo){
        $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $idpo)->get();
        $detaildata = 0;
        foreach ($purchasedetail as $key => $value) {
            if($value->qty != $value->qty_kirim){
                $detaildata += 1;
            }
        }
        if($detaildata > 0){
            return response()->json([
                'success'   => FALSE,
                'message'   => 'Terdapat data pada PO ini QTY Kirim berbeda dengan QTY Order, apakah anda ingin memproses?',
                'record'    => $detaildata
            ]);
        }else{
            return response()->json([
                'success'   => TRUE,
                'message'   => 'Apakah anda yakin ingin memproses data ini?',
            ]);
        }
    }

    public function showpo($idpo){
        $purchase = PurchaseOrder::find($idpo);
        $dt = new Carbon();
        return response()->json([
            'date'      => $dt->now()->format('d-m-Y'),
            'nota'      => $purchase->no_nota,
            'sub_total' => $purchase->sub_total,
            'total'     => $purchase->total,

        ]);
    }

    public function process_nota(Request $request){
        // update purchase

        try {
            $purchase = PurchaseOrder::find($request->id_po);
            $purchase->status    = 3;
            $purchase->status_po = 1;
            $purchase->save();

            // Generate Nota
            $no_nota = $this->gennoinvo($request->tanggal, $purchase);

            $invo_nota      = Invoice::where('no_nota')->first();
            $sales          = Sales::where('code',$purchase->sales_id)->first();
            $diskon         = round($purchase->total * ($request->diskon / 100));
            $grandtotal     = $purchase->total - $diskon;
            $ppn            = round($grandtotal * (10 / 100));
            $grandtotalPpn  = $grandtotal+$ppn;
            // check invoice
            if(!$invo_nota){
                // Save Invoice
                $invoice                        = new Invoice();
                $invoice->no_nota               = $no_nota;
                $invoice->purchase_no           = $purchase->no_nota;
                $invoice->dateorder             = date('Y-m-d', strtotime($request->tanggal));
                $invoice->invoice_date_tt       = date('Y-m-d', strtotime($request->tanggal));
                $invoice->member_id             = $purchase->member_id;
                $invoice->sales_id              = $purchase->sales_id;
                $invoice->perusahaan_id         = $purchase->perusahaan_id;
                $invoice->subtotal              = $purchase->sub_total;
                $invoice->discount              = $request->diskon;
                // $invoice->ppn                = 10;
                $invoice->total_before_ppn      = $grandtotal;
                $invoice->total_before_diskon   = $purchase->total;
                $invoice->total                 = $grandtotalPpn;
                $invoice->note                  = "";
                $invoice->memo                  = $request->memo;
                $invoice->expedisi              = $purchase->expedisi;
                if($purchase->expedisi_via != null){
                    $invoice->via_expedisi      = $purchase->expedisi_via;
                }
                $invoice->duedate               = date('Y-m-d', strtotime("+120 day", strtotime($request->tanggal)));
                $invoice->min_duedate           = date('Y-m-d', strtotime("+90 day", strtotime($request->tanggal)));
                $invoice->pay_status            = 0;
                $invoice->dateprint             = date('Y-m-d', strtotime($request->tanggal));
                $invoice->count_print           = 0;
                $invoice->create_user           = auth()->user()->username;
                $invoice->save();

                // make invoice detail
                if($invoice){
                    $purchasedetail = PurchaseOrderDetail::where('transaction_purchase_id', $purchase->id)->get();
                    foreach ($purchasedetail as $key => $value) {
                        if($value->qty_kirim != 0){
                            $productdetail = Product::where('id', $value->product_id)->first();

                            $report = ReportStock::where('transaction_no',$purchase->no_nota)->where('product_id', $value->product_id)->first();
                            if($report){
                                $report->invoice_id             = $invoice->id;
                                $report->save();
                            }

                            $invoicedetail                  = new InvoiceDetail();
                            $invoicedetail->invoice_id      = $invoice->id;
                            $invoicedetail->gudang_id       = $value->gudang_id;
                            $invoicedetail->product_code    = $productdetail->product_code;
                            $invoicedetail->product_name    = $productdetail->product_name;
                            $invoicedetail->product_img     = $productdetail->product_cover;
                            $invoicedetail->qty             = $value->qty_kirim;
                            $invoicedetail->qty_kirim       = $value->qty_kirim;
                            $invoicedetail->satuan          = $value->satuan;
                            $invoicedetail->discount        = $value->discount;
                            $invoicedetail->colly           = $value->colly;
                            $invoicedetail->colly_to        = $value->colly_to;
                            $invoicedetail->weight          = $value->weight;
                            $invoicedetail->price           = $value->price;
                            $invoicedetail->ttl_price       = $value->ttl_price;
                            $invoicedetail->save();
                        }
                    }
                }
            }

            $dt = new Carbon();
            $purchaselog = new PurchaseOrderLog;
            $purchaselog->user_id        = auth()->user()->id;
            $purchaselog->purchase_id    = $purchase->id;
            $purchaselog->keterangan     = auth()->user()->username." telah memproses PO ".$purchase->no_nota." ke pembuatan NOTA";
            $purchaselog->create_date    = $dt->toDateTimeString();
            $purchaselog->create_user    = auth()->user()->username;
            $purchaselog->save();

            $invoicedetailcolly = InvoiceDetail::where('invoice_id', $invoice->id)->orderBy('id', 'DESC')->first();

            $simpancolly = Invoice::find($invoice->id);
            $simpancolly->colly = $invoicedetailcolly->colly_to;
            $simpancolly->save();

            if($purchaselog) {
                $json_data = array(
                    "success"         => TRUE,
                    "message"         => 'Data berhasil diproses ke Invoice.'
                );
            }else{
                $json_data = array(
                    "success"         => FALSE,
                    "message"         => 'Data gagal diproses ke Invoice.'
                );
            }
        }
        catch (\Throwable $th) {
            $json_data = array(
                "success"         => FALSE,
                "message"         => $th->getMessage(),
            );
        }

        return json_encode($json_data);
    }

    public function print($idpo){
        $purchase = PurchaseOrder::select('transaction_purchase.id as id', 'transaction_purchase.member_id','transaction_purchase.no_nota', 'transaction_purchase.note', 'transaction_purchase.created_at',
                                            'transaction_purchase.expedisi','transaction_purchase.expedisi_via','transaction_purchase.perusahaan_id', 'transaction_purchase.no_nota', 'transaction_purchase.count_cetak',
                                            'member.id as mid', 'member.name as mname', 'member.city as mcity', 'member.address as maddress',
                                            'expedisi.id as exid', 'expedisi.name as exname',
                                            'expedisi_via.id as viaid','expedisi_via.name as vianame',
                                            'perusahaan.id as perid','perusahaan.name as pername', 'perusahaan.address as peraddress','perusahaan.city as percity',)
                                    ->leftjoin('member','member.id','transaction_purchase.member_id')
                                    ->leftjoin('expedisi', 'expedisi.id', 'transaction_purchase.expedisi')
                                    ->leftjoin('perusahaan','perusahaan.id','transaction_purchase.perusahaan_id')
                                    ->leftJoin('expedisi_via', 'expedisi_via.id','transaction_purchase.expedisi_via')
                                    ->where('transaction_purchase.id', $idpo)
                                    ->first();
        $purchasedetail = PurchaseOrderDetail::select('transaction_purchase_detail.id as id','transaction_purchase_detail.qty as qty','transaction_purchase_detail.qty_kirim as qtykirim',
                                                    'transaction_purchase_detail.satuan as satuan','transaction_purchase_detail.discount as discount','transaction_purchase_detail.price as price',
                                                    'transaction_purchase_detail.ttl_price as total','transaction_purchase_detail.perusahaan_id','transaction_purchase_detail.gudang_id',
                                                    'transaction_purchase_detail.product_id', 'transaction_purchase_detail.colly','transaction_purchase_detail.colly_to', 'transaction_purchase_detail.weight',
                                                    'product.id as prid', 'product.product_name as prname', 'product.product_desc as prdesc',
                                                    'perusahaan.id as pid', 'perusahaan.name as pname',
                                                    'gudang.id as gid','gudang.name','transaction_purchase_detail.type')
                                                ->leftjoin('product', 'product.id','transaction_purchase_detail.product_id')
                                                ->leftjoin('gudang','gudang.id', 'transaction_purchase_detail.gudang_id')
                                                ->leftjoin('perusahaan','perusahaan.id','transaction_purchase_detail.perusahaan_id')
                                                ->where('transaction_purchase_detail.transaction_purchase_id', $idpo)
                                                ->get();
        foreach ($purchasedetail as $key => $value) {
            $gudang = Gudang::select('name')->where('id', $value->gid)->first();
            $ttlnon	= $value->total * ($value->discount/100);
            $unitnon = $value->price * ($value->discount/100);
            $hargaprodukasli = Product::select('normal_price','export_price')->find($value->prid);
            //$memberplus      = Member::select('member.id','type_price.name')->join('type_price','type_price.id','member.operation_price')->where('member.id',$purchase->mid)->first();
            if($value->type=='2'){
                $harga = $hargaprodukasli->export_price;
                $hargaasli = $harga;
            }else{
                $harga = $hargaprodukasli->normal_price;
                // $tambahan = $memberplus?$memberplus->name:0;
                // $hargaasli = $tambahan==0?$harga: round($harga + ($tambahan/100 * $harga));
                $hargaasli = $harga;
            }
            $value->hargaasli  = $hargaasli;
            $value->hargarpo   = $value->price;
            $value->hargatotal = $value->total;
            $value->hargaunitsetelahdiskon = '<p style="">'.number_format($value->price - $unitnon, 0, '', '.').'</p>';
            // $value->price = $value->price - $unitnon;
            // $value->total = $value->total;
        }

        if(auth()->user()->can('purchaseorder.liststatuspo')){
            $userakses = 'po';
        }else if(auth()->user()->can('purchaseorder.liststatusgudang')){
            $userakses = 'gudang';
        }else if(auth()->user()->can('purchaseorder.liststatusinvoice')){
            $userakses = 'invoice';
        }

        if($userakses == 'gudang'){
            if($purchase->count_cetak){
                $purchase->count_cetak = $purchase->count_cetak + 1;
            }else{
                $purchase->count_cetak = 1;
            }
        }
        $purchase->save();

        $dt = new Carbon();
        $printoleh = auth()->user()->username.", ".$dt->toDateTimeString();
        if($userakses == 'gudang'){
            $purchaselog = new PurchaseOrderLog;
            $purchaselog->user_id       = auth()->user()->id;
            $purchaselog->purchase_id   = $purchase->id;
            $purchaselog->keterangan    = auth()->user()->username." telah melakukan print pada PO ".$purchase->no_nota;
            $purchaselog->create_date    = $dt->toDateTimeString();
            $purchaselog->create_user    = auth()->user()->username;
            $purchaselog->save();
        }
        return view('backend/purchase/print', compact('idpo','purchase','purchasedetail','printoleh', 'gudang','userakses'));
    }

    public function generateKode()
    {
          $next_no = '';
          $kodesales = '';
          if(strlen(session('idsales'))>1){
              $kodesales = session('idsales');
          }else{
              $kodesales = '0'.session('idsales');
          }
          $tahun = date('y');
          $bulan = date('m');
          $format = $kodesales.$tahun;
          $last_row = Penjualan::whereYear('tgl_faktur','=',date('Y'))->orderBy('no_faktur','DESC')->first();
          $max_value = $last_row->id;
          if ($max_value) {
              $data  = Penjualan::find($max_value);
              $ambil = substr($data->no_faktur, -6);
          }
          if ($max_value==null) {
              $next_no = '000001';
          }elseif (strlen($ambil)<6) {
              $next_no = '000001';
          }elseif ($ambil == '999999') {
              $next_no = '000001';
          }else {
              $next_no = substr('000000', 0, 6-strlen($ambil+1)).($ambil+1);
          }
          return $format.''.$next_no;
    }

}
