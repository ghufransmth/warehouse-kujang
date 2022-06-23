<?php

namespace App\Http\Controllers;

use App\Models\DetailReturTransaksi;
use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ReturTransaksi;
use App\Models\ReturTransaksiDetail;
use App\Models\Sales;
use App\Models\StockAdj;
use App\Models\Toko;
use App\Models\TransaksiStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use function App\Http\Helpers\format_uang;

class ReturController extends Controller
{
    protected $original_column = array(
        1 => "product_code",
    );
    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

    function safe_decode($string,$mode=null) {
        $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }
    public function index(){
        if(session('filter_toko')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_toko');
        }

        if(session('filter_sales')==""){
            $selectedmember = '';
        }else{
            $selectedmember = session('filter_sales');
        }
        $member = array();
        $perusahaan = array();
        $gudang = array();
        $toko = Toko::all();
        $sales = Sales::all();
        return view('backend/retur/index',compact('member','perusahaan','gudang','selectedmember','selectedperusahaan', 'sales', 'toko'));
    }
    public function index_retur(){
        if(session('filter_toko')==""){
            $selectedperusahaan = "";
        }else{
            $selectedperusahaan = session('filter_toko');
        }

        if(session('filter_sales')==""){
            $selectedmember = '';
        }else{
            $selectedmember = session('filter_sales');
        }
        $member = array();
        $perusahaan = array();
        $gudang = array();
        $toko = Toko::all();
        $sales = Sales::all();
        // $transaksi = TransaksiStock::find(1);
        // return $transaksi->gettransaksi(2);
        return view('backend/retur/index_retur',compact('member','perusahaan','gudang','selectedmember','selectedperusahaan', 'sales', 'toko'));
    }
    public function edit($enc_id){
        // return $enc_id;
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = ReturTransaksi::select('*')->where('id',$dec_id)->with(['getdetailtransaksi', 'getdetailtransaksi.getproduct'])->first();
        $pembelian = ReturTransaksi::select('*')->where('id',$dec_id)->with(['getdetailtransaksi','getdetailtransaksi.getproduct'])->first();
        // return $penjualan;
        // return $pembelian;
        //VALIDASI
            if(!isset($dec_id)){
                return response()->json([
                    'success' => false,
                    'code' => 404,
                    'message' => 'Nomor retur tidak ditemukan'
                ]);
            }
        //END VALIDASI
        // return $retur_transaksi->getdetailtransaksi;

            if($penjualan->jenis_transaksi == 0){
                $detail_penjualan = $penjualan->getdetailtransaksi;
                // return $detail_penjualan;
                // return $detail_penjualan;
                $member = array();
                $selectedmember ="";
                $sales = Sales::all();
                // $sales = array();
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
                $selectedstatuslunas = "";

                return view('backend/retur/penjualan_edit_form',compact('enc_id','tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia', 'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko', 'selectedtoko', 'selectedstatuslunas', 'penjualan', 'detail_penjualan'));
            }elseif($penjualan->jenis_transaksi == 1){
                $detail_pembelian = $pembelian->getdetailtransaksi;
                // return $detail_pembelian;
                return view('backend/retur/pembelian_edit_form',compact('enc_id','pembelian','detail_pembelian'));
            }

    }
    public function simpan(Request $req){
        // return $req->all();
        $dec_id = $this->safe_decode(Crypt::decryptString($req->enc_id));
        $no_transaksi           = $req->no_transaksi;
        // return $dec_id;
        $array_harga_product    = $req->harga_product;
        $array_product          = $req->produk;
        $array_stock_product    = $req->stock_product;
        $array_qty              = $req->qty;
        $id_sales               = $req->sales;
        $status_pembayaran      = $req->status_pembayaran; // 1 = lunas, 0 = belum lunas;
        $tgl_jatuh_tempo        = date('Y-m-d',strtotime($req->tgl_jatuh_tempo));
        $tgl_transaksi          = date('Y-m-d', strtotime($req->tgl_transaksi));
        $array_id_satuan        = $req->tipesatuan;
        $id_toko                = $req->toko;
        $array_total_harga      = $req->total;
        $total_product          = $req->total_produk;
        $total_harga_penjualan  = $req->total_harga_penjualan;
        $retur = ReturTransaksi::where('id', $dec_id)->first();
        // return $
        //VALIDASI
            if(!isset($retur)){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Data penjualan tidak ditemukan',
                ]);
            }
            if($total_product < 1){
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Product harus lebih dari 1',
                ]);
            }
        //END VALIDASI
        $retur->no_retur_faktur = $no_transaksi;
        $retur->tgl_retur      = $tgl_transaksi;
        $retur->total_harga           = $total_harga_penjualan;
        $detail_retur           = DetailReturTransaksi::where('retur_transaksi_id', $retur->id);
        if($retur->save()){
            foreach($detail_retur->get() as $detail){
                $stockadj = StockAdj::where('id_product', $detail->product_id)->first();
                $stockadj->stock_retur_penjualan -= $detail->qty;
                $stockadj->stock_penjualan       += $detail->qty;
                if(!$stockadj->save()){
                    return response()->json([
                        'success' => FALSE,
                        'message' => 'Gagal mengupdate stock product'
                    ]);
                    break;
                }
            }
            if($detail_retur->delete()){
                for($i=0;$i<$total_product;$i++){
                    if(isset($array_product[$i])){
                        $detail_retur = new DetailReturTransaksi;
                        $detail_retur->retur_transaksi_id = $retur->id;
                        $detail_retur->product_id = $array_product[$i];
                        $detail_retur->qty = $array_qty[$i];
                        $detail_retur->price = $array_harga_product[$i];
                        $detail_retur->total = $array_total_harga[$i];
                        if($detail_retur->save()){
                            $stockadj = StockAdj::where('id_product', $array_product[$i])->first();
                            $stockadj->stock_retur_penjualan += $array_qty[$i];
                            $stockadj->stock_penjualan       -= $array_qty[$i];
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
                                'message' => 'Gagal menyimpan edit retur penjualan'
                            ]);
                        }
                    }else{
                        continue;
                    }
                }
                $transaksi_stock = TransaksiStock::where('no_transaksi', $retur->no_retur_faktur)->first();
                $transaksi_stock->total_harga = $retur->total_harga;
                if($transaksi_stock->save()){
                    return response()->json([
                        'success' => TRUE,
                        'message' => 'Data edit retur penjualan berhasil disimpan'
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
                    'message' => 'Gagal menghapus detail retur transaksi'
                ]);
            }
        }
        return $retur;

    }
    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        $type = $request->type;
        $filter_toko = $request->filter_toko;
        $filter_sales = $request->filter_sales;
        $jenis_transaksi = $request->jenis_transaksi;
        $no_faktur = $request->no_faktur;
        $request->session()->put('filter_toko', $request->filter_toko);
        $request->session()->put('filter_sales', $request->filter_sales);
        $request->session()->put('type', $request->type);
        $dataquery = ReturTransaksi::select('*');
        // $dataquery->where('flag_transaksi',5);
        // $dataquery->orwhere('flag_transaksi', 6);
        if($jenis_transaksi != ""){
            // return $jenis_transaksi;
            $dataquery->where('jenis_transaksi', $jenis_transaksi);
        }
        if($no_faktur != ""){
            $dataquery->where('no_retur_faktur', $no_faktur);
        }
        if($search){
            $dataquery->orwhere('no_retur_faktur', 'LIKE', "{$search}%");
            // $dataquery->with(['getsales' => function($query) use ($search) {
            //         $query->orWhere('nama', 'LIKE', "{$search}%");
            //     }]);
            // $dataquery->whereHas('getsales', function($query) use ($search){
            //     $query->orwhere('nama', 'LIKE', 'Adin%');
            // });
        }
        $dataquery->orderBy('tgl_retur', 'DESC');
        // if($filter_toko != null || $filter_toko != ""){
        //     $penjualan->where('id_toko', $filter_toko);
        // }
        // if($filter_sales != null || $filter_sales != ""){
        //     $penjualan->where('id_sales', $filter_sales);
        // }
        // if($type == 1){
        //     $penjualan->where('status_lunas', 0);
        // }else if($type == 2){
        //     $penjualan->where('status_lunas', 1);
        // }

        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }

        if($search) {
           $dataquery->orwhere('no_retur_faktur', 'like', "{$search}%");
           $sales = Sales::where('nama', 'like', "{$search}%")->pluck('id');
           $toko = Toko::where('name', 'like', "{$search}%")->pluck('id');
           $dataquery->orwhereIn('id_sales', $sales);
           $dataquery->orwhereIn('id_toko', $toko);
        }
        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();
        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        // return $data;

        foreach($data as $key => $result){
            $aksi = "";
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $result->id             = $result->id;
            $result->no             = $key+$page;
            $result->no_faktur = $result->no_retur_faktur;
            if($result->jenis_transaksi == 0){
                $sales = $result->getsales->nama;
                $toko = $result->gettoko->name;
                $jenis_transaksi = '<span class="badge badge-success">PENJUALAN</span>';
            }else{
                $sales = "-";
                $toko = "-";
                $jenis_transaksi = '<span class="badge badge-warning">PEMBELIAN</span>';
            }
            $result->sales = $sales;
            $result->toko = $toko;

            $result->tgl_transaksi = $result->tgl_retur;
            $result->total_harga = $result->total_harga;
            // $result->tgl_lunas = $result->tgl_lunas;
            $result->jenis_transaksi = $jenis_transaksi;

            $aksi .= '<a href="#" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip">Detail </a>';
            if($result->jenis_transaksi == 0){
                $aksi .= '<a href="'.route('retur.edit', $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px">Edit </a>';
            }else if($result->jenis_transaksi == 1){
                $aksi .= '<a href="'.route('retur.edit', $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px">Edit </a>';
            }
            $aksi .= '<a href="'.route('retur.delete', $enc_id).'" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px">Hapus </a> <br>';


            // if($result->status_lunas == 0){
            //     $result->status_pembayaran = '<span class="badge badge-success">Belum Lunas</span>';
            //     $aksi .= '<a href="#" onclick="approve(\''.$enc_id.'\')" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Aprrove</a> <a href="#" onclick="reject(\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Reject</a>';
            // }else if($result->status_lunas == 2){
            //     $result->status_pembayaran = '<span class="badge badge-danger">Ditolak</span>';
            // }else{
            //     $result->status_pembayaran = '<span class="badge badge-primary">Lunas</span>';
            // }
            $result->created_by = $result->created_user;
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
    public function getData_retur(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];
        $type = $request->type;
        $filter_toko = $request->filter_toko;
        $filter_sales = $request->filter_sales;
        $jenis_transaksi = $request->jenis_transaksi;
        $no_faktur  = $request->no_faktur;
        $request->session()->put('filter_toko', $request->filter_toko);
        $request->session()->put('filter_sales', $request->filter_sales);
        $request->session()->put('type', $request->type);
        $dataquery = TransaksiStock::select('*');
        $all_retur = ReturTransaksi::all();



        if(array_key_exists($request->order[0]['column'], $this->original_column)){
            $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }

        if($search) {
            $dataquery->where(function ($query) use ($search) {
                    $query->orWhere('no_transaksi','LIKE',"%{$search}%");
                    $query->orWhere('created_by','LIKE',"%{$search}%");
            });
        }
        if($jenis_transaksi != ""){
            $dataquery->where('flag_transaksi', $jenis_transaksi);
        }else{
            $dataquery->orwhere('flag_transaksi', 3);
            $dataquery->orwhere('flag_transaksi', 4);
        }
        if($no_faktur != ""){
            $dataquery->where('no_transaksi', 'LIKE', "%{$no_faktur}%");
        }
        $totalData = $dataquery->count();

        $totalFiltered = $dataquery->count();
        $dataquery->limit($limit);
        // $alldata->offset($start);
        $data = $dataquery->get();
        // $data = [];
        // return $data;
        foreach($data as $key => $result){

                $enc_id = $this->safe_encode(Crypt::encryptString($result->no_transaksi));
                // $result->id             = $result->id;
                $result->no             = $key+$page;
                $result->no_faktur = $result->no_transaksi;
                $result->tgl_transaksi = $result->tgl_transaksi;
                $result->total_harga = format_uang($result->total_harga);
                $cek_retur = collect($all_retur)->where('no_faktur', $result->no_transaksi)->first();
                $aksi = '';
                if(!isset($cek_retur)){
                    if($result->flag_transaksi == 3){
                        $result->jenis_transaksi = '<span class="badge badge-success">Penjualan</span>';
                        $aksi .= '<a href="'.route('retur.retur_penjualan', $enc_id).'" class="btn btn-success"><i class="fa fa-check"></i> Retur</a> ';
                    }elseif($result->flag_transaksi == 4){
                        $result->jenis_transaksi = '<span class="badge badge-warning">Pembelian</span>';
                        $aksi .= '<a href="'.route('retur_pembelian.form-retur', $enc_id).'" class="btn btn-success"><i class="fa fa-check"></i> Retur</a> ';
                    }
                }else{
                    if($result->flag_transaksi == 3){
                        $result->jenis_transaksi = '<span class="badge badge-success">Penjualan</span>';
                        $aksi .= '<a href="#" class="btn btn-secondary disabled">Barang pernah di retur</a> ';
                    }elseif($result->flag_transaksi == 4){
                        $result->jenis_transaksi = '<span class="badge badge-warning">Pembelian</span>';
                        $aksi .= '<a href="#" class="btn btn-secondary disabled">Barang pernah di retur</a> ';
                    }
                }

                $result->aksi = $aksi;

                // return $data;

        }

        // return $data;
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
    public function retur_penjualan(){
        $member = array();
        $selectedmember ="";
        $sales = Sales::all();
        // $sales = array();
        $selectedsales ="";
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

        return view('backend/retur/penjualan_form',compact('tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia',
                    'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko'));
    }
    public function list_transaksi(Request $request){
        if($request->jenis_transaksi != ""){
            if($request->jenis_transaksi == 3){
                $transaksi = Penjualan::whereHas('getdetailpenjualan')->whereHas('gettransaksi')->limit(10)->orderBy('id', 'DESC')->get();
            }else{
                $transaksi = Pembelian::whereHas('getdetailpembelian')->whereHas('gettransaksi')->limit(10)->orderBy('id', 'DESC')->get();
            }
            // $transaksi = TransaksiStock::select('*')
            //         ->orWhere('no_transaksi', 'LIKE', "%{$request->search}%")
            //         ->where('flag_transaksi', $request->jenis_transaksi)
            //         ->orderBy('id', 'DESC')
            //         ->limit(10)
            //         ->get();
        }else{
            $penjualan = Penjualan::whereHas('getdetailpenjualan')->whereHas('gettransaksi')->limit(10)->orderBy('id', 'DESC')->get();
            $pembelian = Pembelian::whereHas('getdetailpembelian')->whereHas('gettransaksi')->limit(10)->orderBy('id', 'DESC')->get();
            $transaksi = array_merge($penjualan->toArray(), $pembelian->toArray());
            // $transaksi = TransaksiStock::select('*')
            //         ->orWhere('no_transaksi', 'LIKE', "%{$request->search}%")
            //         ->whereHas('penjualan.getdetailpenjualan')
            //         ->whereHas('pembelian.getdetailpembelian')
            //         ->orderBy('id', 'DESC')
            //         ->limit(10)
            //         ->get();
        }

        return json_encode($transaksi);
    }
    public function list_transaksi_retur(Request $request){
        if($request->jenis_transaksi != ""){
            $transaksi = ReturTransaksi::where('jenis_transaksi', $request->jenis_transaksi)->limit(10)->orderBy('id', 'DESC')->get();
        }else{
            $transaksi = ReturTransaksi::limit(10)->orderBy('id', 'DESC')->get();
        }
        return json_encode($transaksi);

    }

    public function hapus($enc_id)
    {
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        // $dataretur = ReturTransaksi::where('jenis_transaksi',1)->first();
        $returback = ReturTransaksi::where('id',$dec_id)->first();
        // return $returback;
        $detail = ReturTransaksiDetail::where('retur_transaksi_id',$returback->id)->get();
        // return $detail;

        foreach($detail as $key=> $value){
            // return $value->qty;
            $stockadj = StockAdj::where('id_product',$value->product_id)->first();
            if(isset($stockadj)){
                $stockadj->stock_pembelian += $value->qty;
                $stockadj->stock_retur_pembelian = 0;
                if($stockadj->save()){
                    $value->delete();
                }
            }
        }
        $transaksi_stock = TransaksiStock::where('no_transaksi',$returback->no_retur_faktur)->first();
        $transaksi_stock->delete();
        $returback->delete();

        return "Berhasil";
    }
}
