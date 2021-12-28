<?php

namespace App\Exports;

use DB;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Models\InvoiceDetail;
use App\Models\ProductBeliDetail;
use App\Models\ReportStock;
use App\Models\Perusahaan;
use App\Models\Product;
use App\Models\StockAdj;

class ReportStokExports implements FromView, ShouldAutoSize
{
    protected $filter_perusahaan;
    protected $filter_gudang;
    protected $filter_tgl_start;
    protected $filter_tgl_end;
    protected $filter_keyword;
    protected $filter_kategori;


    public function __construct($filter_perusahaan, $filter_gudang, $filter_keyword, $filter_tgl_start, $filter_tgl_end, $filter_kategori)
    {
        $this->filter_perusahaan = $filter_perusahaan;
        $this->filter_gudang     = $filter_gudang;
        $this->filter_keyword    = $filter_keyword;
        $this->filter_tgl_start  = $filter_tgl_start;
        $this->filter_tgl_end    = $filter_tgl_end;
        $this->filter_kategori   = $filter_kategori;
    }

    public function view(): View
    {
        $perusahaanObj = Perusahaan::find($this->filter_perusahaan);
        $product = Product::find($this->filter_keyword);

        $keyword = $this->filter_keyword;
        $kategori = $this->filter_kategori;

        if ($this->filter_keyword != 0) {
            // cek apakah product pencarian adalah produk liner?
            if ($product->product_code == $product->product_code_shadow && $product->is_liner == 'Y') {
                $is_liner_parent = true;
            } else {
                $is_liner_parent = false;
            }
        } else {
            $is_liner_parent = false;
        }

        // stok barang masuk dan barang keluar
        $report_stok = ReportStock::with(['getinvoice', 'produk_beli', 'getperusahaan' => function ($q) {
            $q->select('id', 'name');
        }, 'getgudang' => function ($q) {
            $q->select('id', 'name');
        }, 'getproduct' => function ($query) {
            $query->with('category_product:id,cat_name', 'satuans:id,name');
        }])->whereIn('note', ['Purchase Barang Keluar', 'Order Barang Masuk', 'Mutasi', 'retur barang masuk', 'Adjusment', 'Opname']);


        if ($this->filter_keyword != 0) {
            if ($is_liner_parent) {
                $report_stok->whereHas('getproduct', function ($query) use ($product) {
                    $query->where('product_code_shadow', $product->product_code);
                });
            } else {
                $report_stok->whereHas('getproduct', function ($query) use ($keyword) {
                    $query->where('id', $keyword);
                });
            }
        }

        if ($this->filter_kategori != "" || $this->filter_kategori != null) {

            $report_stok->whereHas('getproduct', function ($query) use ($kategori) {
                $query->whereIn('category_id', $kategori);
            });
        }

        if ($this->filter_tgl_start != "" && $this->filter_tgl_end != "") {
            $report_stok->whereDate('created_at', '>=', date('Y-m-d', strtotime($this->filter_tgl_start)));
            $report_stok->whereDate('created_at', '<=', date('Y-m-d', strtotime($this->filter_tgl_end)));
        }

        if ($this->filter_perusahaan != "" || $this->filter_perusahaan != null) {
            $report_stok->where('perusahaan_id', $this->filter_perusahaan);
        }

        if ($this->filter_gudang != "") {
            $report_stok->where('gudang_id', $this->filter_gudang);
        }

        $data = $report_stok->get();

        foreach ($data as $key => $value) {

            if ($value->getinvoice != null && $value->keterangan == 'Purchase Keluar') {
                $no_transaction = $value->getinvoice->purchase_no;
                $ket = "Data Penjualan";
                $no_invoice = $value->getinvoice->no_nota;
            } else if ($value->produk_beli != null) {
                $no_transaction = $value->produk_beli->notransaction;
                $ket = "Good Receive";
                $no_invoice = '-';
            } else {
                $no_transaction = $value->transaction_no ?? '-';
                $ket = ucwords($value->keterangan);
                $no_invoice = '-';
            }

            if (!empty($value->invoice->dateorder)) {
                $date_order = date('d M Y', strtotime($value->invoice->dateorder));
            } else if (!empty($value->product_beli->faktur_date)) {
                $date_order = date('d M Y', strtotime($value->product_beli->faktur_date));
            } else {
                $date_order = date('d M Y', strtotime($value->created_at));
            }

            if ($value->note == 'Purchase Barang Keluar') {
                $qty = -$value->stock_input;
            } else if ($value->keterangan == 'retur barang masuk') {
                $qty = abs($value->stock_input);
            } else {
                $qty = $value->stock_input;
            }

            if ($is_liner_parent) {
                if ($value->getproduct->product_code != $value->getproduct->product_code_shadow) {
                    $product_code = $value->getproduct->product_code_shadow   . "<br>" . "(" . $value->getproduct->product_code . ")";
                } else {
                    $product_code = $value->getproduct->product_code;
                }
                $product_parent = Product::with('getsatuan:id,name')->where('product_code', $value->getproduct->product_code_shadow)->first();
                $satuan = $product_parent->getsatuan->name;
                $quantity = $qty * $value->getproduct->satuan_value;
            } else {
                $product_code = $value->getproduct->product_code;
                $satuan = $value->getproduct->getsatuan->name;
                $quantity = $qty;
            }


            $value->id                = $value->id;
            $value->no_transaction    = $no_transaction;
            $value->no_invoice        = $no_invoice;
            $value->dateorder         = $date_order;
            $value->product_code      = $product_code;
            $value->gudang_name       = $value->getgudang != null ? $value->getgudang->name : '-';
            $value->ket               = $ket;
            $value->qty               = $quantity;
            $value->satuan            = $satuan;
        }

        return view('backend/report/stok/index_stok_excel', [
            'data' => $data,
            'filter_perusahaan' => $this->filter_perusahaan,
            'filter_keyword'    => $this->filter_keyword,
            'filter_tgl_start' => $this->filter_tgl_start,
            'filter_tgl_end'  => $this->filter_tgl_end,
            'perusahaan' => $perusahaanObj,
            "sum_qty"         => $this->getSumTotalFilter($this->filter_keyword, $this->filter_perusahaan, $this->filter_gudang, $this->filter_tgl_start, $this->filter_tgl_end),
            "cut_off_qty"     => $this->getTotalCutOffStock($this->filter_keyword, $this->filter_perusahaan, $this->filter_gudang, date('d-m-Y', strtotime($this->filter_tgl_start . "-1 days"))),
            "total"           => $this->getSumTotalFilter($this->filter_keyword, $this->filter_perusahaan, $this->filter_gudang, $this->filter_tgl_start, $this->filter_tgl_end) + $this->getTotalCutOffStock($this->filter_keyword, $this->filter_perusahaan, $this->filter_gudang, date('d-m-Y', strtotime($this->filter_tgl_start . "-1 days"))),
            'product' => $product
        ]);
    }

    private function getSumTotalFilter($product_id, $perusahaan_id, $gudang_id, $tgl_start, $tgl_end)
    {
        $sum = 0;
        $sum_report = 0;
        $product = Product::find($product_id);
        if (!empty($product)) {
            // $dataAdj  = StockAdj::select('stock_add', 'created_at', 'qty_product')
            //     ->where([
            //         'product_id'    => $product_id,
            //         'perusahaan_id' => $perusahaan_id,
            //         'gudang_id'     => $gudang_id
            //     ])->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
            //     ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
            //     ->orderBy('id', 'desc')
            //     ->first();

            // if ($product->is_liner == 'Y') {
            //     if ($product->product_code == $product->product_code_shadow) {
            //         $is_liner_parent = true;
            //     } else {
            //         $is_liner_parent = false;
            //     }
            // } else {
            //     $is_liner_parent = false;
            // }

            // if (!$is_liner_parent) {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id'           => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // } else {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id_shadow'    => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // }

            // $data_report_stock->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
            //     ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)));

            // // ->where('keterangan', '!=', 'Adjusment Masuk ()');




            // if (!empty($dataAdj)) {
            //     $data_sum = $data_report_stock->where('created_at', '>=', $dataAdj->created_at)->get();
            // } else {
            //     $data_sum = $data_report_stock->get();
            // }

            // foreach ($data_sum as $k => $nilai) {
            //     $get_data_product = Product::find($nilai->product_id);

            //     if ($is_liner_parent) {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input * $get_data_product->satuan_value;
            //         } else {
            //             $qty = $nilai->stock_input * $get_data_product->satuan_value;
            //         }
            //     } else {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input);
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input;
            //         } else {
            //             $qty = $nilai->stock_input;
            //         }
            //     }

            //     if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
            //         if ($nilai->produk_beli->flag_proses == 1) {
            //             $satuan_value = $qty;
            //         } else {
            //             $satuan_value = 0;
            //         }
            //     } else {
            //         $satuan_value = $qty;
            //     }

            //     $sum_report += $satuan_value;
            // }

            // return $sum_report;
            if ($product->is_liner == 'Y') {
                if ($product->product_code == $product->product_code_shadow) {
                    $is_liner_parent = true;
                } else {
                    $is_liner_parent = false;
                }
            } else {
                $is_liner_parent = false;
            }

            if (!$is_liner_parent) {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!empty($get_note_report_desc)) {

                $data_report_stock->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)));

                if ($get_note_report_desc->note == 'Opname' || $get_note_report_desc->note == 'Adjusment') {
                    $sum_report =  $get_note_report_desc->stock_input;
                } else {
                    $data_prev_last_record = $data_report_stock->get();
                    if ($data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Opname' || $data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Adjusment') {
                        if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'retur barang masuk') {
                            $qty = abs($data_prev_last_record[count($data_prev_last_record) - 1]->stock_input);
                        } else if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'Purchase Keluar') {
                            $qty = -$data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        } else {
                            $qty = $data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        }
                        $sum_report =  $data_prev_last_record[count($data_prev_last_record) - 2]->stock_input + $qty;
                    } else {
                        if (!$is_liner_parent) {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id'           => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id_shadow'    => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '>=', date('Y-m-d', strtotime($tgl_start)))
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($tgl_end)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                        if (!empty($get_data_last_opname_or_adjustment)) {
                            $data_sum = $data_report_stock
                                ->where('id', '>=', $get_data_last_opname_or_adjustment->id)
                                ->get();
                        } else {
                            $data_sum = $data_report_stock->get();
                        }


                        foreach ($data_sum as $k => $nilai) {
                            $get_data_product = Product::find($nilai->product_id);
                            if ($is_liner_parent) {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input * $get_data_product->satuan_value;
                                } else {
                                    $qty = $nilai->stock_input * $get_data_product->satuan_value;
                                }
                            } else {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input);
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input;
                                } else {
                                    $qty = $nilai->stock_input;
                                }
                            }


                            if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                                if ($nilai->produk_beli->flag_proses == 1) {
                                    $satuan_value = $qty;
                                } else {
                                    $satuan_value = 0;
                                }
                            } else {
                                $satuan_value = $qty;
                            }

                            $sum_report += $satuan_value;
                        }
                    }
                }
            }
        }
        return $sum_report;
    }

    private function getTotalCutOffStock($product_id, $perusahaan_id, $gudang_id, $one_day_prev_tgl_start)
    {
        $sum = 0;
        $sum_report = 0;
        $product = Product::find($product_id);
        if (!empty($product)) {
            // $dataAdj  = StockAdj::select('stock_add', 'created_at', 'qty_product')
            //     ->where([
            //         'product_id'    => $product_id,
            //         'perusahaan_id' => $perusahaan_id,
            //         'gudang_id'     => $gudang_id
            //     ])->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
            //     ->orderBy('id', 'desc')
            //     ->first();

            // if ($product->is_liner == 'Y') {
            //     if ($product->product_code == $product->product_code_shadow) {
            //         $is_liner_parent = true;
            //     } else {
            //         $is_liner_parent = false;
            //     }
            // } else {
            //     $is_liner_parent = false;
            // }

            // if (!$is_liner_parent) {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id'           => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // } else {
            //     $data_report_stock = ReportStock::with('produk_beli')->where([
            //         'product_id_shadow'    => $product_id,
            //         'perusahaan_id'        => $perusahaan_id,
            //         'gudang_id'            => $gudang_id
            //     ]);
            // }


            // if (!empty($dataAdj)) {
            //     $data_sum = $data_report_stock->where('created_at', '>=', $dataAdj->created_at)->get();
            // } else {
            //     $data_sum = $data_report_stock->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))->get();
            // }


            // foreach ($data_sum as $k => $nilai) {
            //     $produk = Product::find($nilai->product_id);

            //     if ($is_liner_parent) {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input) * $produk->satuan_value;
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input * $produk->satuan_value;
            //         } else {
            //             $qty = $nilai->stock_input * $produk->satuan_value;
            //         }
            //     } else {
            //         if ($nilai->keterangan == 'retur barang masuk') {
            //             $qty = abs($nilai->stock_input);
            //         } else if ($nilai->keterangan == 'Purchase Keluar') {
            //             $qty = -$nilai->stock_input;
            //         } else {
            //             $qty = $nilai->stock_input;
            //         }
            //     }

            //     if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
            //         if ($nilai->produk_beli->flag_proses == 1) {
            //             $satuan_value = $qty * $produk->satuan_value;
            //         } else {
            //             $satuan_value = 0;
            //         }
            //     } else {
            //         $satuan_value = $qty * $produk->satuan_value;
            //     }

            //     $sum_report += $satuan_value;
            // }
            if ($product->is_liner == 'Y') {
                if ($product->product_code == $product->product_code_shadow) {
                    $is_liner_parent = true;
                } else {
                    $is_liner_parent = false;
                }
            } else {
                $is_liner_parent = false;
            }

            if (!$is_liner_parent) {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id'           => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $data_report_stock = ReportStock::with('produk_beli')->where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ]);
                $get_note_report_desc = ReportStock::where([
                    'product_id_shadow'    => $product_id,
                    'perusahaan_id'        => $perusahaan_id,
                    'gudang_id'            => $gudang_id
                ])
                    ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                    ->orderBy('id', 'desc')
                    ->first();
            }

            if (!empty($get_note_report_desc)) {

                $data_report_stock->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)));

                if ($get_note_report_desc->note == 'Opname' || $get_note_report_desc->note == 'Adjusment') {
                    $sum_report =  $get_note_report_desc->stock_input;
                } else {
                    $data_prev_last_record = $data_report_stock->get();
                    if ($data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Opname' || $data_prev_last_record[count($data_prev_last_record) - 2]->note == 'Adjusment') {
                        if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'retur barang masuk') {
                            $qty = abs($data_prev_last_record[count($data_prev_last_record) - 1]->stock_input);
                        } else if ($data_prev_last_record[count($data_prev_last_record) - 1]->keterangan == 'Purchase Keluar') {
                            $qty = -$data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        } else {
                            $qty = $data_prev_last_record[count($data_prev_last_record) - 1]->stock_input;
                        }
                        $sum_report =  $data_prev_last_record[count($data_prev_last_record) - 2]->stock_input + $qty;
                    } else {
                        if (!$is_liner_parent) {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id'           => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        } else {
                            $get_data_last_opname_or_adjustment = ReportStock::where([
                                'product_id_shadow'    => $product_id,
                                'perusahaan_id'        => $perusahaan_id,
                                'gudang_id'            => $gudang_id
                            ])
                                ->whereDate('created_at', '<=', date('Y-m-d', strtotime($one_day_prev_tgl_start)))
                                ->whereIn('note', ['Opname', 'Adjusment'])
                                ->orderBy('id', 'desc')
                                ->first();
                        }

                        if (!empty($get_data_last_opname_or_adjustment)) {
                            $data_sum = $data_report_stock
                                ->where('id', '>=', $get_data_last_opname_or_adjustment->id)
                                ->get();
                        } else {
                            $data_sum = $data_report_stock->get();
                        }


                        foreach ($data_sum as $k => $nilai) {
                            $get_data_product = Product::find($nilai->product_id);
                            if ($is_liner_parent) {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input) * $get_data_product->satuan_value;
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input * $get_data_product->satuan_value;
                                } else {
                                    $qty = $nilai->stock_input * $get_data_product->satuan_value;
                                }
                            } else {
                                if ($nilai->keterangan == 'retur barang masuk') {
                                    $qty = abs($nilai->stock_input);
                                } else if ($nilai->keterangan == 'Purchase Keluar') {
                                    $qty = -$nilai->stock_input;
                                } else {
                                    $qty = $nilai->stock_input;
                                }
                            }


                            if ($nilai->produk_beli != null && $nilai->produk_beli_id != null) {
                                if ($nilai->produk_beli->flag_proses == 1) {
                                    $satuan_value = $qty;
                                } else {
                                    $satuan_value = 0;
                                }
                            } else {
                                $satuan_value = $qty;
                            }

                            $sum_report += $satuan_value;
                        }
                    }
                }
            }
        }

        return $sum_report;
    }

    public function itemNotExists()
    {
        return ['Order Barang Masuk', 'Purchase Barang Keluar', 'Adjusment'];
    }
}
