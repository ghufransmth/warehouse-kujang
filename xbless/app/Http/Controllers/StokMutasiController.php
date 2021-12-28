<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\Gudang;
use App\Models\PerusahaanGudang;
use App\Models\Product;
use App\Models\ProductPerusahaanGudang;
use App\Models\ProductBarcode;
use App\Models\ReportStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMutasi;
use App\Models\StockAdj;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Auth;

class StokMutasiController extends Controller
{
    public function flag_barang_masuk()
    {
        return ['Order Barang Masuk', 'Mutasi Masuk', 'retur barang masuk'];
    }

    public function flag_barang_keluar()
    {
        return ['Purchase Keluar', 'Mutasi Keluar'];
    }

    public function tambah(Request $request)
    {
        $gudang = Gudang::all();
        $selectedgudang = '';
        $perusahaan = Perusahaan::all();
        $selectedperusahaan = '';
        return view('backend/stok/stokmutasi/form', compact('gudang', 'selectedgudang', 'perusahaan', 'selectedperusahaan'));
    }
    public function simpan(Request $req)
    {
        $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $req->perusahaan)->where('gudang_id', $req->gudang_from)->first();
        if ($cekperusahaangudang) {
            for ($i = 0; $i < $req->jumlahdata; $i++) {
                //product_perusahaan_gudang
                $cek = ProductPerusahaanGudang::where('product_id', $req->product[$i])->where('perusahaan_gudang_id', $cekperusahaangudang->id)->first();
                if ($cek) {
                    $sm = new StockMutasi;
                    $sm->no_transaction = $req->no_transaksi;

                    $sm->product_perusahaan_gudang_id = $cek->id;
                    $sm->from_perusahaan_id   = $req->perusahaan;
                    $sm->from_gudang_id       = $req->gudang_from;
                    $sm->to_perusahaan_id     = $req->perusahaan;
                    $sm->to_gudang_id         = $req->gudang_to;
                    $sm->from_stock           = $req->stok[$i];
                    $sm->to_stock             = $req->qty_mutasi[$i];
                    $sm->created_at           = date("Y-m-d H:i:s", strtotime($req->tgl_mutasi . date("H:i:s")));
                    $sm->created_by           = $req->user()->username;
                    $sm->save();

                    if ($sm) {
                        //pengurangan stok gudang awal
                        $cek->stok -= $req->qty_mutasi[$i];
                        $cek->save();

                        $reportstockawal = new ReportStock;
                        $reportstockawal->product_id        = $req->product[$i];
                        $reportstockawal->product_id_shadow = $req->product[$i];
                        $reportstockawal->gudang_id      = $req->gudang_from;
                        $reportstockawal->perusahaan_id  = $req->perusahaan;
                        $reportstockawal->stock_input    = "-" . $req->qty_mutasi[$i];
                        $reportstockawal->note           = 'Mutasi';
                        $reportstockawal->keterangan     = 'Mutasi Keluar';
                        $reportstockawal->created_at     = date("Y-m-d H:i:s");
                        $reportstockawal->created_by     = $req->user()->username;
                        $reportstockawal->save();

                        //penambahan stok gudang tujuan
                        $cekperusahaangudangtujuan = PerusahaanGudang::where('perusahaan_id', $req->perusahaan)->where('gudang_id', $req->gudang_to)->first();
                        if ($cekperusahaangudangtujuan) {
                            $cektujuan = ProductPerusahaanGudang::where('product_id', $req->product[$i])->where('perusahaan_gudang_id', $cekperusahaangudangtujuan->id)->first();
                            if ($cektujuan) {
                                $cektujuan->stok  += $req->qty_mutasi[$i];
                                $cektujuan->save();
                            } else {
                                $stokbaru = new ProductPerusahaanGudang;
                                $stokbaru->product_id           = $req->product[$i];
                                $stokbaru->perusahaan_gudang_id = $cekperusahaangudangtujuan->id;
                                $stokbaru->stok                 = $req->qty_mutasi[$i];
                                $stokbaru->save();
                            }
                            $reportstocktujuan = new ReportStock;
                            $reportstocktujuan->product_id     = $req->product[$i];
                            $reportstocktujuan->product_id_shadow = $req->product[$i];
                            $reportstocktujuan->gudang_id      = $req->gudang_to;
                            $reportstocktujuan->perusahaan_id  = $req->perusahaan;
                            $reportstocktujuan->stock_input    = $req->qty_mutasi[$i];
                            $reportstocktujuan->note           = 'Mutasi';
                            $reportstocktujuan->keterangan     = 'Mutasi Masuk';
                            $reportstocktujuan->created_at     = date("Y-m-d H:i:s");
                            $reportstocktujuan->created_by     = $req->user()->username;
                            $reportstocktujuan->save();
                        }
                    }
                }
            }
            $json_data = array(
                "success"         => TRUE,
                "message"         => 'Data berhasil ditambahkan.'
            );
        } else {
            $json_data = array(
                "success"         => FALSE,
                "message"         => 'Data gagal ditambahkan.'
            );
        }
        return json_encode($json_data);
    }

    public function perusahaan_gudang($id)
    {
        $perusahaan_gudang = PerusahaanGudang::select('gudang.id', 'gudang.name')->join('gudang', 'gudang.id', 'perusahaan_gudang.gudang_id')->where('perusahaan_id', $id)->get();
        return json_encode($perusahaan_gudang);
    }

    private function cekExist($column, $var, $id)
    {
        $cek = StockMutasi::where('id', '!=', $id)->where($column, '=', $var)->first();
        return (!empty($cek) ? false : true);
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

    public function getProduk(Request $request)
    {
        $perusahaan_id = $request->perusahaan_id;
        $gudang_id     = $request->gudang_id;
        $term          = $request->term;
        $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->where('gudang_id', $gudang_id)->first();

        $query = Product::select('product.id', 'product.product_code', 'product.product_name')->join('product_perusahaan_gudang', 'product.id', 'product_perusahaan_gudang.product_id')->where('product_perusahaan_gudang.perusahaan_gudang_id', $cekperusahaangudang->id)->take(20);

        if ($term) {
            $query->where('product_name', 'LIKE', "%{$term}%");
            $query->orWhere('product_code', 'LIKE', "%{$term}%");
        }
        $produks = $query->get();
        $out = [
            'results' => [],
            'pagination' => [
                'more' => false
            ]
        ];
        foreach ($produks as $value) {
            array_push($out['results'], [
                'id'   => $value->id,
                'text' => $value->product_code . '-' . $value->product_name
            ]);
        }
        return response()->json($out, 200);
    }

    public function cekStokAkhir($product_id, $filter_perusahaan_admin, $filter_gudang_admin)
    {

        $value = Product::find($product_id);

        $jumlah    = StockAdj::select('stock_add', 'created_at', 'qty_product', 'stock_admin')
            ->where('product_id', $product_id)
            ->where('perusahaan_id', $filter_perusahaan_admin)
            ->where('gudang_id', $filter_gudang_admin)
            ->orderBy('id', 'desc')
            ->first();


        $jumlahopname = StockOpnameDetail::select('stock_opname_detail.qtySO', 'stock_opname_detail.created_at', 'stock_opname_detail.stock_admin')
            ->join('stock_opname', 'stock_opname.id', 'stock_opname_detail.so_id')
            ->where('produk_id', $product_id)
            ->where('perusahaan_id', $filter_perusahaan_admin)
            ->where('gudang_id', $filter_gudang_admin)
            ->where('stock_opname.flag_proses', 1)
            ->orderBy('stock_opname_detail.id', 'desc')
            ->first();



        if ($value->is_liner == 'Y') {
            $datamasuk = ReportStock::with('produk_beli')->where([
                'product_id_shadow'    => $product_id,
                'perusahaan_id' => $filter_perusahaan_admin,
                'gudang_id' => $filter_gudang_admin,
            ])->whereIn('keterangan', $this->flag_barang_masuk());
        } else {
            $datamasuk = ReportStock::with('produk_beli')->where([
                'product_id' => $product_id,
                'perusahaan_id' => $filter_perusahaan_admin,
                'gudang_id' => $filter_gudang_admin,
            ])->whereIn('keterangan', $this->flag_barang_masuk());
        }

        if (!empty($jumlah) && !empty($jumlahopname)) {
            if ($jumlah->created_at > $jumlahopname->created_at) {

                $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
            } else {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlah->created_at)->get();
            }
            if ($jumlahopname) {
                $datamasukget = $datamasuk->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else {
            $datamasukget = $datamasuk->get();
        }


        $masuk = 0;

        if (count($datamasukget) > 0) {
            foreach ($datamasukget as $k => $nilai) {
                $produk = Product::find($nilai->product_id);
                if ($produk->is_liner == 'Y') {
                    $produksatuan = $produk->satuan_value;
                } else {
                    $produksatuan = 1;
                }
                $qty = $nilai->keterangan == 'retur barang masuk' ? abs($nilai->stock_input) : $nilai->stock_input;
                if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                    if ($nilai->produk_beli->flag_proses == 1) {
                        $satuan_value_masuk = $qty * $produksatuan;
                    } else {
                        $satuan_value_masuk = 0;
                    }
                } else {
                    $satuan_value_masuk = $qty * $produksatuan;
                }

                $masuk += $satuan_value_masuk;
            }
        }

        if ($value->isLiner = "Y") {

            $datakeluar = ReportStock::with(['transaction_detail' => function ($query) {
                $query->with('purchase_order');
            }])->where([
                'product_id_shadow'    => $product_id,
                'perusahaan_id'        => $filter_perusahaan_admin,
                'gudang_id'            => $filter_gudang_admin,
            ])->whereIn('keterangan', $this->flag_barang_keluar());
        } else {

            $datakeluar = ReportStock::with(['transaction_detail' => function ($query) {
                $query->with('purchase_order');
            }])->where([
                'product_id'           => $product_id,
                'perusahaan_id'        => $filter_perusahaan_admin,
                'gudang_id'            => $filter_gudang_admin,
            ])->whereIn('keterangan', $this->flag_barang_keluar());
        }

        if (!empty($jumlah) && !empty($jumlahopname)) {
            if ($jumlah->created_at > $jumlahopname->created_at) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
            } else {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlah->created_at)->get();
            }
            if ($jumlahopname) {
                $datakeluarget = $datakeluar->where('created_at', '>=', $jumlahopname->created_at)->get();
            }
        } else {
            $datakeluarget = $datakeluar->get();
        }


        $keluar = 0;

        if (count($datakeluarget) > 0) {
            foreach ($datakeluarget as $x => $result) {
                $produk = Product::find($result->product_id);
                if ($produk->is_liner == 'Y') {
                    $produksatuan = $produk->satuan_value;
                } else {
                    $produksatuan = 1;
                }
                $qty = $result->keterangan == 'Mutasi Keluar' ? abs($result->stock_input) : $result->stock_input;

                if ($result->transaction_detail != null) { // skip data untuk flag status tidak sama dengan 0 (po)
                    if ($result->transaction_detail->purchase_order->flag_status == 0) {
                        $satuan_value = $qty * $produksatuan;
                    } else {
                        $satuan_value = 0;
                    }
                } else {
                    $satuan_value =  $qty * $produksatuan;
                }
                $keluar += $satuan_value;
            }
        }

        if (!empty($jumlah) && !empty($jumlahopname)) {
            if ($jumlah->created_at > $jumlahopname->created_at) {
                $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                $nilaiadj      =  $stokadj;
                $nilaiopname   =  0;
            } else {
                $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
                $nilaiopname   =  $stokadj;
                $nilaiadj      =  0;
            }
        } else if (!empty($jumlah) || !empty($jumlahopname)) {
            if ($jumlah) {
                $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;
                $nilaiadj      =  $stokadj;
                $nilaiopname   =  0;
            }
            if ($jumlahopname) {
                $stokadj       =  $jumlahopname ? ($jumlahopname->stock_admin) : 0;
                $nilaiopname   =  $stokadj;
                $nilaiadj      =  0;
            }
        } else {
            $stokadj       = 0;
            $nilaiopname   = 0;
            $nilaiadj      = 0;
        }

        // $stokadj       =  $jumlah ? ($jumlah->stock_admin) : 0;

        $akhir          = $stokadj +  $masuk  - $keluar;

        return $akhir;
    }

    public function tambahProduk(Request $request)
    {
        $id            = $request->id_product;
        $perusahaan_id = $request->perusahaan_id;
        $gudang_id     = $request->gudang_id;
        $cekperusahaangudang = PerusahaanGudang::where('perusahaan_id', $perusahaan_id)->where('gudang_id', $gudang_id)->first();
        // $cek = ProductPerusahaanGudang::select('stok')->where('product_id', $id)->where('perusahaan_gudang_id', $cekperusahaangudang->id)->first();
        // if ($cek) {
        //     $stok = $cek->stok;
        // } else {
        //     $stok = '-';
        // }
        $stok   = $this->cekStokAkhir($id, $perusahaan_id, $gudang_id);
        $product = Product::find($id);

        $satuan = $product->getsatuan ? $product->getsatuan->name : '-';
        $html = '';
        $html .= '<tr>';
        $html .= '<td>';
        $html .= '<span class="product">' . $product->product_name . '</span>';
        $html .= '<input type="hidden" class="product_value"  name="product[]" value="' . $product->id . '">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<span class="price">' . $stok . '</span>';
        $html .= '<input type="hidden" class="stok_value" min=0  name="stok[]" value="' . $stok . '">';
        $html .= '</td>';
        $html .= '<td>';
        $html .= '<span class="choose">' . $satuan . '</span>';
        $html .= '<input type="hidden" class="satuan_value" min=0 name="satuan[]" value="' . $satuan . '">';
        $html .= '</td>';
        $html .= '<td width="13%">';
        $html .= '<input type="text" class="form-control qty" min=1  name="qty_mutasi[]" value="1">';
        $html .= '</td>';
        $html .= '<td class="text-right">';
        $html .= '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
        $html .= '</td>';
        $html .= '</tr>';
        $json_data = array(
            "success"         => true,
            "message"         => 'Data ditambahkan.',
            "data"            => $html,
            "stok"            => $stok,
        );
        return json_encode($json_data);
    }
}
