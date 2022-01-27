<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\Penjualan;
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
        $penjualan->whereHas('getsales');

        $penjualan->orderBy('id', 'ASC');
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
            $result->total_harga = $result->total_harga;
            $result->tgl_lunas = $result->tgl_lunas;
            $aksi .= '<a href="#" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip">Detail </a>';
            $aksi .= '<a href="'.route('purchaseorder.edit', $enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" style="margin-left:4px">Edit </a> <br>';

            if($result->status_lunas == 0){
                $result->status_pembayaran = '<span class="badge badge-success">Belum Lunas</span>';
                $aksi .= '<a href="#" onclick="approve(\''.$enc_id.'\')" class="btn btn-primary btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Aprrove</a> <a href="#" onclick="reject(\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" style="margin-top:5px">Reject</a>';
            }else if($result->status_lunas == 2){
                $result->status_pembayaran = '<span class="badge badge-danger">Ditolak</span>';
            }else{
                $result->status_pembayaran = '<span class="badge badge-primary">Lunas</span>';
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
    public function getData_retur(Request $request){
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
        $penjualan = Penjualan::select('*')->whereHas('getdetailpenjualan')->whereHas('gettransaksi')->get();
        $collect_penjualan = collect([]);
        foreach($penjualan as $jualan){
            $colect_jualan = collect([
                'no_faktur' => $jualan->no_faktur,
                'total_harga' => $jualan->total_harga,
                'flag_transaksi' => 3,
                'tgl_transaksi' => $jualan->tgl_faktur,
                'created_by' => $jualan->gettransaksi->created_by,
            ]);
            $collect_penjualan->push($colect_jualan);
        }
        $pembelian = Pembelian::select('*')->whereHas('getdetailpembelian')->whereHas('gettransaksi')->get();
        $collect_pembelian = collect([]);
        foreach($pembelian as $belian){
            $colect_belian = collect([
                'no_faktur' => $belian->no_faktur,
                'total_harga' => $belian->nominal,
                'flag_transaksi' => 4,
                'tgl_transaksi' => $belian->tgl_faktur,
                // 'created_by' => $belian->gettransaksi->created_by,
            ]);
            $collect_pembelian->push($colect_belian);
        }
        // return $collect_pembelian;
        $alldata = $collect_penjualan->merge($collect_pembelian);
        // return $alldata;
        // $penjualan->orwhere('flag_transaksi',3);
        // $penjualan->orwhere('flag_transaksi',4);

        // $penjualan->orderBy('id', 'ASC');
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
            $penjualan->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }

        if($search) {
            $penjualan->where(function ($query) use ($search) {
                    $query->orWhere('no_nota','LIKE',"%{$search}%");
                    $query->orWhere('kode_rpo','LIKE',"%{$search}%");
            });
        }
        $totalData = $alldata->count();

        $totalFiltered = $alldata->count();
        $alldata->take($limit);
        // $alldata->offset($start);
        // $data = $penjualan->get();
        $data = [];
        // return $data;
        foreach($alldata as $key => $result){
            $results = [];
            // return $result['no_faktur'];

                $enc_id = $this->safe_encode(Crypt::encryptString($result['no_faktur']));
                // $result->id             = $result->id;
                $results['no']             = $key+$page;
                $results['no_faktur'] = $result['no_faktur'];
                $results['tgl_transaksi'] = $result['tgl_transaksi'];
                $results['total_harga'] = $result['total_harga'];
                if($result['flag_transaksi'] == 3){
                    $results['jenis_transaksi'] = "Penjualan";
                    $aksi = '<a href="'.route('retur.retur_penjualan', $result['no_faktur']).'" class="btn btn-success"><i class="fa fa-trash"></i> Retur</a> ';
                }elseif($result['flag_transaksi'] == 4){
                    $results['jenis_transaksi'] = "Pembelian";
                }

                $results['aksi'] = $aksi;
                $data[] = $results;
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
}
