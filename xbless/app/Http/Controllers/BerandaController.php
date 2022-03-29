<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

use DB;
use Auth;


class BerandaController extends Controller
{
    private function terbilang($angka) {
        $angka=abs($angka);
        $baca =array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
     
        $terbilang="";
         if ($angka < 12){
             $terbilang= " " . $baca[$angka];
         }
         else if ($angka < 20){
             $terbilang= $this->terbilang($angka - 10) . " belas";
         }
         else if ($angka < 100){
             $terbilang= $this->terbilang($angka / 10) . " puluh" . $this->terbilang($angka % 10);
         }
         else if ($angka < 200){
             $terbilang= " seratus" . $this->terbilang($angka - 100);
         }
         else if ($angka < 1000){
             $terbilang= $this->terbilang($angka / 100) . " ratus" . $this->terbilang($angka % 100);
         }
         else if ($angka < 2000){
             $terbilang= " seribu" . $this->terbilang($angka - 1000);
         }
         else if ($angka < 1000000){
             $terbilang= $this->terbilang($angka / 1000) . " ribu" . $this->terbilang($angka % 1000);
         }
         else if ($angka < 1000000000){
            $terbilang= $this->terbilang($angka / 1000000) . " juta" . $this->terbilang($angka % 1000000);
         }
         else if ($angka < 1000000000000){
             $terbilang= $this->terbilang($angka / 1000000000) . " miliyar" . $this->terbilang($angka % 1000000000);
         }else if ($angka < 1000000000000000){
             $terbilang= $this->terbilang($angka / 1000000000000) . " triliun" . $this->terbilang($angka % 1000000000000);
         }
            return $terbilang;
    }

    public function index()
    {
        $periode_start = date('d-m-Y', strtotime("-1 Month"));
        $periode_end = date('d-m-Y');

        return view('backend/beranda/index', compact('periode_start', 'periode_end'));
    }

    function safe_encode($string)
    {
        $data = str_replace(array('/'), array('_'), $string);
        return $data;
    }

    function safe_decode($string, $mode = null)
    {
        $data = str_replace(array('_'), array('/'), $string);
        return $data;
    }

    public function getData(Request $request)
    {
        $penjualan = $this->omset($request->periode_start, $request->periode_end);
        $pajak = $this->pajak($request->periode_start, $request->periode_end);
        $pembelian = $this->pembelian($request->periode_start, $request->periode_end);

        return response()->json([
            'code' => 200,
            'message' => 'data berhasil didapat',
            'detail' => [
                'omset' => $penjualan,
                'pajak' => $pajak,
                'pembelian' => $pembelian
            ]
        ]);
    }

    private function omset($periode_start, $periode_end)
    {
        $penjualan = DB::table('tbl_penjualan')
            ->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)))
            ->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)))
            ->get();
        $total_harga = 0;
        foreach ($penjualan as $key => $value) {
            $total_harga += $value->total_harga + $value->total_diskon;
        }

        return $total_harga;
    }

    private function pajak($periode_start, $periode_end)
    {
        $penjualan = $this->omset($periode_start, $periode_end);

        $pembelian = DB::table('pembelian')
            ->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)))
            ->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)))
            ->sum('nominal');

        $total_pajak = round((($penjualan - $pembelian) / 100) * 10);

        return $total_pajak;
    }

    private function pembelian($periode_start, $periode_end)
    {
        $pembelian = DB::table('pembelian')
            ->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)))
            ->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)))
            ->sum('nominal');

        return $pembelian;
    }

    public function getDataUnilever(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        if ($request->periode_start != '' && $request->periode_end != '') {
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        } else {
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $dataquery = DB::table('pembelian')->select('*');
        $dataquery->orderBy('id', 'DESC');
        if ($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('name', 'LIKE', "%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action .= '';
            $action .= '<div class="btn-group">';
            $action .= '<a href="#" onclick="modal('.$result->id.')" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action .= '</div>';

            if ($result->status_pembelian == 0) {
                $status = '<span class="label label-danger">Belum Lunas</span>';
            } else {
                $status = '<span class="label label-primary">Lunas</span>';
            }

            $result->no             = $key + $page;

            $result->nominal        = "Rp. " . number_format($result->nominal, 0, '', '.');
            $result->status         = $status;
            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public function detailUnilever(Request $request){
        $query = DB::table('pembelian')->select('pembelian.*','supplier.nama as supplier')->leftJoin('supplier','supplier.id','supplier_id');
        $query->where('pembelian.id', $request->id);
        $result = $query->first();
        
        if($result){
            $result->total_barang = $query->get()->count();
            $result->terbilang = strtoupper($this->terbilang($result->nominal))." RUPIAH";
    
            $queryDetail = DB::table('pembelian_detail')->select('pembelian_detail.*', 'tbl_product.nama as product')->leftJoin('tbl_product','tbl_product.id','pembelian_detail.product_id')->where('pembelian_id', $result->id);
            $resultDetail = $queryDetail->get();
    
            if($resultDetail){
                $detail = '';
                foreach ($resultDetail as $key => $value) {
                    $detail.='<tr>';
                        $detail.='<td>'.$value->notransaction.'</td>';
                        $detail.='<td>'.$value->product.'</td>';
                        $detail.='<td class="text-right">RP '. number_format($value->product_price, 0, '', '.').'</td>';
                        $detail.='<td class="text-center">'.$value->qty.'Pcs </td>';
                        $detail.='<td class="text-right">RP '. number_format($value->total, 0, '', '.').'</td>';
                    $detail.='</tr>';
                }

                return response()->json([
                    'data' => $result,
                    'detail'    => $detail
                ]);
            }else{
                return response()->json([
                    'code'  => 200,
                    'message' => 'Maaf nomor faktur tersebut tidak mempunyai detail',
                    'data'  => $result,
                    'detail'    => '-'
                ]);
            }
    
        }else{
            return response()->json([
                'code' => 404,
                'message' => 'Maaf Data tersebut tidak ada'
            ]);
        }
    }

    public function getDataPenjualan(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        if ($request->periode_start != '' && $request->periode_end != '') {
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        } else {
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $dataquery = DB::table('tbl_penjualan')->select('tbl_penjualan.*', 'tbl_sales.nama', 'toko.name');
        $dataquery->leftJoin('tbl_sales', 'tbl_sales.id', 'tbl_penjualan.id_sales');
        $dataquery->leftJoin('toko', 'toko.id', 'tbl_penjualan.id_toko');
        $dataquery->orderBy('tbl_penjualan.id', 'DESC');
        if ($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('sales.nama', 'LIKE', "%{$search}%");
                $query->orWhere('toko.name', 'LIKE', "%{$search}%");
                $query->orWhere('tbl_penjualan.no_faktur', 'LIKE', "%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action .= '';
            $action .= '<div class="btn-group">';
            $action .= '<a href="' . route('beranda.unilever.detail', $enc_id) . '" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action .= '</div>';

            $total_harga     = 0;
            $total_harga    += $result->total_harga + $result->total_diskon;
            $result->total_harga = "Rp. " . number_format($total_harga, 0, '', '.');
            $result->no             = $key + $page;

            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            // $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public function getDataRetur(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        if ($request->periode_start != '' && $request->periode_end != '') {
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        } else {
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $dataquery = DB::table('detail_retur_transaksi')->select('detail_retur_transaksi.*','retur_transaksi.*', 'tbl_sales.nama','tbl_product.nama as product');
        $dataquery->leftJoin('retur_transaksi','retur_transaksi.id','detail_retur_transaksi.retur_transaksi_id');
        $dataquery->leftJoin('tbl_sales', 'tbl_sales.id', 'retur_transaksi.id_sales');
        $dataquery->leftJoin('tbl_product', 'tbl_product.id', 'detail_retur_transaksi.product_id');
        $dataquery->orderBy('retur_transaksi.id', 'DESC');
        if ($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('sales.nama', 'LIKE', "%{$search}%");
                $query->orWhere('retur_transaksi.no_faktur', 'LIKE', "%{$search}%");
            });
        }

        $dataquery->whereDate('retur_transaksi.tgl_retur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('retur_transaksi.tgl_retur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action .= '';
            $action .= '<div class="btn-group">';
            $action .= '<a href="' . route('beranda.unilever.detail', $enc_id) . '" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action .= '</div>';

            if($result->qty > 12){
                $lusin = round($result->qty / 12);
                $sisa  = $result->qty - ($lusin * 12);
                $hasil = $result->qty.'/ 0.'.$lusin.'.'.$sisa;
            }else{
                $lusin = 0;
                $hasil = $result->qty.'/ 0.0.'.$result->qty;
            }

            $result->total_harga = "Rp. " . number_format($result->total_harga, 0, '', '.');
            $result->no             = $key + $page;
            $result->faktur         = date('d F Y', strtotime($result->tgl_retur));
            $result->jumlah         = $hasil;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public function getDataPiutang(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        if ($request->periode_start != '' && $request->periode_end != '') {
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        } else {
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $dataquery = DB::table('tbl_penjualan')->select('tbl_penjualan.*', 'tbl_sales.nama', 'toko.name','toko.code');
        $dataquery->leftJoin('tbl_sales', 'tbl_sales.id', 'tbl_penjualan.id_sales');
        $dataquery->leftJoin('toko', 'toko.id', 'tbl_penjualan.id_toko');
        $dataquery->where('tbl_penjualan.status_lunas', 0);
        $dataquery->orderBy('tbl_penjualan.id', 'DESC');
        if ($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('sales.nama', 'LIKE', "%{$search}%");
                $query->orWhere('toko.name', 'LIKE', "%{$search}%");
                $query->orWhere('tbl_penjualan.no_faktur', 'LIKE', "%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action .= '';
            $action .= '<div class="btn-group">';
            $action .= '<a href="' . route('beranda.unilever.detail', $enc_id) . '" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action .= '</div>';

            $total_harga     = 0;
            $total_harga    += $result->total_harga + $result->total_diskon;
            $result->total_harga = "Rp. " . number_format($total_harga, 0, '', '.');
            $result->toko   = $result->name.' - '.$result->code;
            $result->no             = $key + $page;
            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            $result->jatuh_tempo         = date('d F Y', strtotime($result->tgl_jatuh_tempo));
            // $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }

    public function getDataTertagih(Request $request)
    {
        $limit = $request->length;
        $start = $request->start;
        $page  = $start + 1;
        $search = $request->search['value'];

        if ($request->periode_start != '' && $request->periode_end != '') {
            $periode_start = $request->periode_start;
            $periode_end = $request->periode_end;
        } else {
            $periode_start = date('Y-m-d', strtotime("-1 Month"));
            $periode_end = date('Y-m-d');
        }

        $dataquery = DB::table('tbl_penjualan')->select('tbl_penjualan.*', 'tbl_sales.nama', 'toko.name','toko.code');
        $dataquery->leftJoin('tbl_sales', 'tbl_sales.id', 'tbl_penjualan.id_sales');
        $dataquery->leftJoin('toko', 'toko.id', 'tbl_penjualan.id_toko');
        $dataquery->where('tbl_penjualan.status_lunas', 1);
        $dataquery->orderBy('tbl_penjualan.id', 'DESC');
        if ($search) {
            $dataquery->where(function ($query) use ($search) {
                $query->orWhere('sales.nama', 'LIKE', "%{$search}%");
                $query->orWhere('toko.name', 'LIKE', "%{$search}%");
                $query->orWhere('tbl_penjualan.no_faktur', 'LIKE', "%{$search}%");
            });
        }

        $dataquery->whereDate('tgl_faktur', '>=', date('Y-m-d', strtotime($periode_start)));
        $dataquery->whereDate('tgl_faktur', '<=', date('Y-m-d', strtotime($periode_end)));

        $totalData = $dataquery->get()->count();

        $totalFiltered = $dataquery->get()->count();

        $dataquery->limit($limit);
        $dataquery->offset($start);
        $data = $dataquery->get();
        foreach ($data as $key => $result) {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = '';

            $action .= '';
            $action .= '<div class="btn-group">';
            $action .= '<a href="' . route('beranda.unilever.detail', $enc_id) . '" class="btn btn-sm btn-primary rounded"><i class="fa fa-eye"></i>&nbsp; Detail</a>';
            $action .= '</div>';

            $total_harga     = 0;
            $total_harga    += $result->total_harga + $result->total_diskon;
            $result->total_harga = "Rp. " . number_format($total_harga, 0, '', '.');
            $result->toko   = $result->name.' - '.$result->code;
            $result->no             = $key + $page;
            $result->faktur         = date('d F Y', strtotime($result->tgl_faktur));
            $result->jatuh_tempo         = date('d F Y', strtotime($result->tgl_jatuh_tempo));
            // $result->action         = $action;
        }
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        $json_data = array(
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        );
        return json_encode($json_data);
    }
}
