<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Member;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Models\InvoicePayment;
use App\Models\InvoicePiutang;
use App\Models\ReportStokBmBl;
use App\Models\PerusahaanGudang;
use App\Models\InvoiceReturRevisi;
use App\Models\ProductPerusahaanGudang;

class ReturRevisiController extends Controller
{
    public function index()
    {
        $companies = Perusahaan::orderBy('name', 'asc')->get();
        $members = Member::orderBy('name', 'asc')->get();

        if (\Session::get('dataSearch')) {
            $nomorRetur = \Session::get('dataSearch')['retur_revisi'];
            if ($nomorRetur) {
                $invoices = Invoice::when(\Session::get('dataSearch')['invoice'], function($query) {
                                        $query->where('no_nota', 'LIKE', '%' . \Session::get('dataSearch')['invoice'] . '%');
                                    })
                                    ->when(\Session::get('dataSearch')['perusahaan'], function($query) {
                                        $query->where('perusahaan_id', \Session::get('dataSearch')['perusahaan']);
                                    })
                                    ->when(\Session::get('dataSearch')['customer'], function($query) {
                                        $query->where('member_id', \Session::get('dataSearch')['customer']);
                                    })
                                    ->whereHas('getReturRevisi', function($q) use ($nomorRetur){
                                                            $q->where('nomor_retur_revisi', 'LIKE', '%'.$nomorRetur.'%');
                                    })
                                    ->get();
            } else {
                $invoices = Invoice::when(\Session::get('dataSearch')['invoice'], function($query) {
                                        $query->where('no_nota', 'LIKE', '%' . \Session::get('dataSearch')['invoice'] . '%');
                                    })
                                    ->when(\Session::get('dataSearch')['perusahaan'], function($query) {
                                        $query->where('perusahaan_id', \Session::get('dataSearch')['perusahaan']);
                                    })
                                    ->when(\Session::get('dataSearch')['customer'], function($query) {
                                        $query->where('member_id', \Session::get('dataSearch')['customer']);
                                    })
                                    ->get();
            }


            return view ('backend.returrevisi.index', compact('companies', 'members', 'invoices'));

        } else {

            $invoices = Invoice::orderBy('created_at', 'desc')->get();

            return view ('backend.returrevisi.index', compact('companies', 'members', 'invoices'));
        }
    }

    public function search(Request $request)
    {
        $nomorRetur = $request->retur_revisi;
        if ($nomorRetur) {
            $invoices = Invoice::when(request()->invoice, function($query) {
                                    $query->where('no_nota', 'LIKE', '%' . request()->invoice . '%');
                                })
                                ->when(request()->perusahaan, function($query) {
                                    $query->where('perusahaan_id', request()->perusahaan);
                                })
                                ->when(request()->customer, function($query) {
                                    $query->where('member_id', request()->customer);
                                })
                                ->whereHas('getReturRevisi', function($q) use ($nomorRetur){
                                                        $q->where('nomor_retur_revisi', 'LIKE', '%'.$nomorRetur.'%');
                                })
                                ->get();
        } else {
            $invoices = Invoice::when(request()->invoice, function($query) {
                                    $query->where('no_nota', 'LIKE', '%' . request()->invoice . '%');
                                })
                                ->when(request()->perusahaan, function($query) {
                                    $query->where('perusahaan_id', request()->perusahaan);
                                })
                                ->when(request()->customer, function($query) {
                                    $query->where('member_id', request()->customer);
                                })
                                ->get();
        }

        $companies = Perusahaan::orderBy('name', 'asc')->get();
        $members = Member::orderBy('name', 'asc')->get();
        \Session::put('dataSearch', $request->all());
        if($request->type == 'retur'){
            \Session::put('type', 'retur');
        }else{
            \Session::put('type', 'revisi');
        }
        // $dataSearch = $request->all();

        return view ('backend.returrevisi.index', compact('companies', 'members', 'invoices'));
    }

    public function detail($type, $id)
    {
        $invoice = Invoice::find($id);

        return view ('backend.returrevisi.detail', compact('invoice', 'type'));
    }

    public function store(Request $request, $type, $id)
    {
        $invoice = Invoice::find($id);

        if($type == 'retur'){
            $nama_jenis = "RET";
            \Session::put('type', 'retur');
        }else{
            $nama_jenis = "REV";
            \Session::put('type', 'revisi');
        }

        $namaptsj = $invoice->getPerusahaan->kode;

        $retur_revisi = InvoiceReturRevisi::where('nomor_retur_revisi', 'like', '%' . $nama_jenis . '%')
                                            ->where('nomor_retur_revisi', 'like', '%' . $namaptsj . '%')
                                            ->orderBy('created_at', 'desc')->first();

        if ($retur_revisi) {
            $getno = explode("/",$retur_revisi->nomor_retur_revisi);
            if ($getno[4] == date('y')) {
                if ($getno[0] == $namaptsj) {
                    $getno = $getno[1];
                    $uniqNo = (int)$getno;
                    $no_transaksi = $uniqNo+1;
                } else {
                    $no_transaksi = 1;
                }
            } else {
                $no_transaksi = 1;
            }
        }else{
            $no_transaksi = 1;
        }

        $no_retur_revisi = "".$namaptsj."/".sprintf("%'.05d", $no_transaksi)."/".$nama_jenis."/".date('m')."/".date('y')."";

        //update total invoice
        $invoice->update([
            'subtotal' => explode(",",(str_replace(".","",$request->subtotal)))[0],
            'total' => explode(",",(str_replace(".","",$request->grand_total)))[0],
            'total_before_ppn' => explode(",",(str_replace(".","",$request->total_after_discount)))[0],
            'total_before_diskon' => explode(",",(str_replace(".","",$request->subtotal)))[0],
        ]);

        //update invoice tanda terima
        if ($invoice->getTandaTerima) {
            $invoice->getTandaTerima->update([
                'nilai' => explode(",",(str_replace(".","",$request->grand_total)))[0]
            ]);

            $invoicePiutang = InvoicePiutang::where('no_tt',$invoice->getTandaTerima->no_tanda_terima)->get();
            //update invoice piutang
            if (count($invoicePiutang) > 1) {
                InvoicePiutang::where('no_tt',$invoice->getTandaTerima->no_tanda_terima)->update([
                    'total'     => explode(",",(str_replace(".","",$request->grand_total)))[0],
                    'tanggal'   => date('Y-m-d')
                ]);
            }

            $invoicePayment = InvoicePayment::where('no_tanda_terima',$invoice->getTandaTerima->no_tanda_terima)->get();
            //update invoice payment
            if (count($invoicePayment) > 1) {
                InvoicePayment::where('no_tanda_terima',$invoice->getTandaTerima->no_tanda_terima)->update([
                    'total_pembayaran'     => explode(",",(str_replace(".","",$request->grand_total)))[0],
                ]);
            }
        }

        if($type == 'retur'){
            foreach ($request->invoice_detail_id as $key => $value) {

                $invoice_detail = InvoiceDetail::where('id', $value)->update([
                    'qty'   => $request->qty[$key],
                    'ttl_price'   => str_replace(".","",$request->total[$key]),
                ]);

                $data_retur_revisi = [
                    'nomor_retur_revisi'    => $no_retur_revisi,
                    'invoice_tanda_terima'  => $invoice->getTandaTerima ? $invoice->getTandaTerima->no_tanda_terima : '',
                    'invoice_id'            => $id,
                    'note'                  => $request->note,
                    'invoice_detail_id'     => $request->invoice_detail_id[$key],
                    'qty_before'            => $request->current_qty[$key],
                    'qty_change'            => $request->qty[$key],
                    'price_before'          => explode(",",(str_replace(".","",$request->current_price[$key])))[0],
                    'price_change'          => explode(",",(str_replace(".","",$request->price[$key])))[0],
                    'total_before'          => explode(",",(str_replace(".","",$request->current_total[$key])))[0],
                    'total_change'          => explode(",",(str_replace(".","",$request->total[$key])))[0],
                    'note'                  => $request->note ? $request->note : '',
                    'create_user'           => auth()->user()->username
                ];

                InvoiceReturRevisi::create($data_retur_revisi);
            }

            $returs = InvoiceReturRevisi::where('nomor_retur_revisi', $no_retur_revisi)
                                    ->whereColumn('qty_before', '!=', 'qty_change')
                                    ->get();
            foreach ($returs as $key_retur => $retur) {

                $productx = Product::find($retur->getInvoiceDetail->product->id);
                if($productx->product_code_shadow==null){
                    $inputproduct_id_shadow  = null;
                }else{
                    if($productx->product_code_shadow==$productx->product_code){
                        $inputproduct_id_shadow    = $productx->id;
                    }else{
                        $cekindukbro   = Product::where('product_code',$productx->product_code_shadow)->first();
                        $inputproduct_id_shadow = $cekindukbro->id;
                    }
                }
                $data_retur_qty = [
                    'product_id' => $productx->id,
                    'product_id_shadow' => $inputproduct_id_shadow,
                    'transaction_no' => $no_retur_revisi,
                    'invoice_id' => $retur->invoice_id,
                    'perusahaan_id' => $retur->getInvoice->perusahaan_id,
                    'gudang_id' => $retur->getInvoiceDetail->gudang_id,
                    'stock_input' => $retur->qty_change-$retur->qty_before,
                    'created_by' => $retur->create_user,
                    'note' => 'retur barang masuk',
                    'keterangan' => 'retur barang masuk',
                ];

                ReportStokBmBl::create($data_retur_qty);
            }


            //hitung perubahan qty
            foreach ($request->invoice_detail_id as $invD => $invDetail) {
                $productDetQty['invoice_detail_id'] = $request->invoice_detail_id[$invD];
                $productDetQty['qty_change'] = $request->current_qty[$invD]-$request->qty[$invD];
                $productDetQty['perusahaan_id'] = $request->perusahaan_id;
                $productDetQtys[]            = $productDetQty;
            }

            //get product id
            foreach ($productDetQtys as $d => $dataInvDet) {

                $dataProduct           = InvoiceDetail::where('id', $dataInvDet['invoice_detail_id'])->first()->product;

                if ($dataProduct->is_liner == 'Y') {
                    if ($dataProduct->product_code == $dataProduct->product_code_shadow) {
                         $invoice_detail_product['qty_change']           = $dataInvDet['qty_change']*1;
                    } else {
                        $invoice_detail_product['qty_change']           = $dataInvDet['qty_change']*InvoiceDetail::where('id', $dataInvDet['invoice_detail_id'])->first()->product->satuan_value;
                    }
                } else {
                   $invoice_detail_product['qty_change']           = $dataInvDet['qty_change']*1;
                }

                if ($dataProduct->product_code_shadow == null) {
                    $invoice_detail_product['product_id']           = $dataProduct->id;
                    $invoice_detail_product['perusahaan_gudang_id']           = PerusahaanGudang::where('gudang_id', InvoiceDetail::where('id', $dataInvDet['invoice_detail_id'])
                    ->first()->gudang_id)
                    ->where('perusahaan_id', $dataInvDet['perusahaan_id'])->first()->id;
                } elseif($dataProduct->product_code == $dataProduct->product_code_shadow) {
                    $invoice_detail_product['product_id']           = $dataProduct->id;
                    $invoice_detail_product['perusahaan_gudang_id']           = PerusahaanGudang::where('gudang_id', InvoiceDetail::where('id', $dataInvDet['invoice_detail_id'])
                    ->first()->gudang_id)
                    ->where('perusahaan_id', $dataInvDet['perusahaan_id'])->first()->id;
                } elseif($dataProduct->product_code != $dataProduct->product_code_shadow) {
                    $invoice_detail_product['product_id']           = Product::where('product_code', $dataProduct->product_code_shadow)->first()->id;
                    $invoice_detail_product['perusahaan_gudang_id']           = PerusahaanGudang::where('gudang_id', InvoiceDetail::where('id', $dataInvDet['invoice_detail_id'])
                    ->first()->gudang_id)
                    ->where('perusahaan_id', $dataInvDet['perusahaan_id'])->first()->id;
                }
                $qtyUpdate[]    = $invoice_detail_product;
            }
            //update stok
            foreach ($qtyUpdate as $qtyU => $qtyUp) {
                $product_perusahaan_gudang = ProductPerusahaanGudang::where('product_id', $qtyUp['product_id'])
                                                                        ->where('perusahaan_gudang_id', $qtyUp['perusahaan_gudang_id'])
                                                                        ->first();
                $product_perusahaan_gudang->update([
                    'stok' => $product_perusahaan_gudang->stok+$qtyUp['qty_change']
                ]);
            }

        }else{
            foreach ($request->invoice_detail_id as $key => $value) {

                $price = intval(explode(",",(str_replace(".","",$request->price[$key])))[0]);
                $diskon = $request->diskon[$key];
                $price_round = round(100/(100-$diskon)*$price);
                $price_real = number_format((float)$price_round, 0, '.', '');

                $invoice_detail = InvoiceDetail::where('id', $value)->update([
                    'qty'   => $request->qty[$key],
                    'price'   => $price_real,
                    'ttl_price'   => str_replace(".","",$request->total[$key]),
                ]);

                $data_retur_revisi = [
                    'nomor_retur_revisi'    => $no_retur_revisi,
                    'invoice_tanda_terima'  => $invoice->getTandaTerima ? $invoice->getTandaTerima->no_tanda_terima : '',
                    'invoice_id'            => $id,
                    'note'                  => $request->note,
                    'invoice_detail_id'     => $request->invoice_detail_id[$key],
                    'qty_before'            => $request->current_qty[$key],
                    'qty_change'            => $request->qty[$key],
                    'price_before'          => explode(",",(str_replace(".","",$request->current_price[$key])))[0],
                    'price_change'          => explode(",",(str_replace(".","",$request->price[$key])))[0],
                    'total_before'          => explode(",",(str_replace(".","",$request->current_total[$key])))[0],
                    'total_change'          => explode(",",(str_replace(".","",$request->total[$key])))[0],
                    'note'                  => $request->note ? $request->note : '',
                    'create_user'           => auth()->user()->username
                ];

                InvoiceReturRevisi::create($data_retur_revisi);
            }
        }

        return redirect()->route('returrevisi.index')->with('status', ucfirst($type).' berhasil');
    }

    public function logRetur($type, $id)
    {
        $invoice = invoice::where('id', base64_decode($id))->first();

        return view ('backend.returrevisi.log_retur', compact('invoice', 'type'));
    }

    public function logReturPrint($id)
    {
        $retur_revisi = InvoiceReturRevisi::find(base64_decode($id));
        if (strpos($retur_revisi->nomor_retur_revisi, 'RET') == true) {
            $retur_revisi_print = InvoiceReturRevisi::where('nomor_retur_revisi', $retur_revisi->nomor_retur_revisi)
                                                ->whereColumn('qty_before', '<>', 'qty_change')
                                                ->get();
        } elseif(strpos($retur_revisi->nomor_retur_revisi, 'REV') == true) {
            $retur_revisi_print = InvoiceReturRevisi::where('nomor_retur_revisi', $retur_revisi->nomor_retur_revisi)
                                                ->whereColumn('price_before', '<>', 'price_change')
                                                ->get();
        }


        return view ('backend.returrevisi.print', compact('retur_revisi', 'retur_revisi_print'));
    }
}
