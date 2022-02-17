<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
use App\Models\ReturTransaksi;
use App\Models\Sales;
use App\Models\Toko;
use App\Models\TransaksiStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $penjualan = ReturTransaksi::select('*','price as harga_product')->where('id',$dec_id)->with(['getdetailtransaksi', 'getdetailtransaksi.getproduct'])->first();
        return $penjualan;
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

            }

            return view('backend/retur/penjualan_form',compact('enc_id','tipeharga','selectedtipeharga','sales','selectedsales','expedisi','expedisivia', 'selectedexpedisi','selectedexpedisivia','selectedproduct','member','selectedmember', 'toko', 'selectedtoko', 'selectedstatuslunas', 'penjualan', 'detail_penjualan'));

        return $retur_transaksi;
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
            $aksi .= '<a href="'.route('retur.edit', $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px">Edit </a> <br>';

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
        $dataquery->orwhere('flag_transaksi', 3);
        $dataquery->orwhere('flag_transaksi', 4);


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
                $result->total_harga = $result->total_harga;
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

                    }elseif($result->flag_transaksi == 4){
                        $result->jenis_transaksi = '<span class="badge badge-warning">Pembelian</span>';

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
}
