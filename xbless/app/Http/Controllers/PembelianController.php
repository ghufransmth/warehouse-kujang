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

        $dataquery = Pembelian::select('id', 'no_faktur','tgl_faktur', 'keterangan', 'created_user');
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

    // public function tambah(){

    //     $product = Product::offset(0)->limit(10)->get();
    //     $selectedproduct = "";

    //     return view('backend/pembelian/form',compact('product','selectedproduct'));
    // }


    public function tambah_product(Request $req)
    {
        $total = $req->total;
        echo "
        <tr id='detail_product_".$total."'>
        <!-- <input type='hidden' id='detail_product' name='detail_product[]'> -->
            <td>
                <select id='product_".$total."' name='product[]' class='select2_".$total." form-control'>
                    <option value='0' selected disabled>Pilih Product</option>
                </select>
            </td>
            <td>
            <select class='select2_satuan_".$total."' id='tipe_satuan_".$total."' name='tipesatuan[]' onchange='satuan(this.options[this.selectedIndex].value, ".$total.")'>
                    <option value='null'>Pilih Tipe Satuan </option>
                </select>
        </td>
        <td>
            <input type='text' class='form-control' id='harga_beli_".$total."' name='harga_beli[]'>
        </td>
            <td><input type='text' class='form-control' id='qty_".$total."' name='qty[]' value='1'>
            </td>

             <td><input type='text' class='form-control total_harga' id='total_".$total."' name='total[]' readonly></td>
            <td><a class='text-white btn btn-danger btn-hemisperich btn-xs' onclick='javascript:deleteObat(".$total.")' data-original-title='Hapus Data' id='deleteModal'><i class='fa fa-trash'></i></a></td>
        </tr>
        <script>
            $(function () {
                $('.select2_".$total."').select2({
                    width: '200px',
                    placeholder: 'Pilih Product',
                    ajax: {
                        url: '".route('pembelian.search_product')."',
                        dataType: 'JSON',
                        data: function(params) {
                          return {
                            search: params.term
                          }
                        },
                        processResults: function (data) {
                          var results = [];
                          $.each(data, function(index, item){
                            results.push({
                                id: item.id,
                                text : item.nama,
                            });
                          });
                          return{
                            results: results
                          };
                      }
                    }
                })

                select_satuan(".$total.");
                select_product(".$total.");
                $('.touchspin".$total."').TouchSpin({
                    min: 1,
                    max: 9999999999999999999999,
                    buttondown_class: 'btn btn-white',
                    buttonup_class: 'btn btn-white'
                });

                $('#qty_".$total."').TouchSpin({
                     min: 1,
                     max: 999999,
                     buttondown_class: 'btn btn-white',
                     buttonup_class: 'btn btn-white'
                })

                $('#satuan_".$total."').select2({
                    width: '200px',
                    placeholder: 'Pilih Obat',
                    ajax: {
                        url: '".route('pembelian.get_satuan')."',
                        dataType: 'JSON',
                        data: function(params) {
                          return {
                            search: params.term
                          }
                        },
                        processResults: function (data) {
                          var results = [];
                          $.each(data, function(index, item){
                            results.push({
                                id: item.id,
                                text : item.nama,
                            });
                          });
                          return{
                            results: results
                          };
                      }
                    }
                  })
            })
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

    // private function save_stock($res, $detail, $res_detail){
    //     // return $detail;
    //     $cek_stock_awal = StockAdj::where('product_id', $detail->product_id)->where('flag_status_product', 0)->first();
    //     if(!isset($cek_stock_awal)){
    //         //return $detail;
    //         $result                         = new StockOpname;
    //         $result->code                   = $res->no_nota;
    //         $result->product_id             = $detail->product_id;
    //         $result->product_detail_id      = $detail->id;
    //         $result->qty                    = $this->convert_pcs($res_detail->qty, $res_detail->satuan);
    //         $result->flag_status_product    = 0;
    //         $result->flag_stock_opname      = 1;
    //         $result->tgl_transaksi          = date('Y-m-d',strtotime($res->tgl_pembelian));
    //         $result->note                   = 'Data barang masuk dari pembelian';
    //         $result->created_by             = auth()->user()->username;
    //         $result->save();
    //         if($result){
    //             $result_akhir                         = new StockOpname;
    //             $result_akhir->product_id             = $detail->product_id;
    //             $result_akhir->qty                    = $this->convert_pcs($res_detail->qty, $res_detail->satuan);
    //             $result_akhir->flag_status_product    = 7;
    //             $result_akhir->tgl_transaksi          = date('Y-m-d',strtotime($res->tgl_pembelian));
    //             $result_akhir->created_by             = auth()->user()->username;
    //             if($result_akhir->save()){
    //                 return response()->json([
    //                     "success"   => TRUE,
    //                     "code"      => 201,
    //                     "message"   => "Data stock opname berhasil di simpan"
    //                 ]);
    //             }else{
    //                 return response()->json([
    //                     "success"   => FALSE,
    //                     "code"      => 401,
    //                     "message"   => "Data stock opname gagal di simpan"
    //                 ]);
    //             }

    //         }else{
    //             return response()->json([
    //                 "success"   => FALSE,
    //                 "code"      => 401,
    //                 "message"   => "Data stock opname gagal di simpan"
    //             ]);
    //         }

    //     }else{
    //         $result                         = new StockOpname;
    //         $result->code                   = $res->no_nota;
    //         $result->product_id             = $detail->product_id;
    //         $result->product_detail_id      = $detail->id;
    //         $result->qty                    = $this->convert_pcs($res_detail->qty, $res_detail->satuan);
    //         $result->flag_status_product    = 1;
    //         $result->flag_stock_opname      = 1;
    //         $result->tgl_transaksi          = date('Y-m-d',strtotime($res->tgl_pembelian));
    //         $result->note                   = 'Data barang masuk dari pembelian';
    //         $result->created_by             = auth()->user()->username;
    //         if($result->save()){
    //             $result_akhir                         = StockOpname::where('product_id',$detail->product_id)->where('flag_status_product', 7)->first();
    //             $result_akhir->product_id             = $detail->product_id;
    //             $result_akhir->qty                    += $this->convert_pcs($res_detail->qty, $res_detail->satuan);
    //             $result_akhir->tgl_transaksi          = date('Y-m-d',strtotime($res->tgl_pembelian));
    //             $result_akhir->created_by             = auth()->user()->username;
    //             if($result_akhir->save()){
    //                 return response()->json([
    //                     "success"   => TRUE,
    //                     "code"      => 201,
    //                     "message"   => "Data stock opname berhasil di simpan"
    //                 ]);
    //             }else{
    //                 return response()->json([
    //                     "success"   => FALSE,
    //                     "code"      => 401,
    //                     "message"   => "Data stock opname gagal di simpan"
    //                 ]);
    //             }

    //         }else{
    //             return response()->json([
    //                 "success"   => FALSE,
    //                 "code"      => 401,
    //                 "message"   => "Data stock opname gagal di simpan"
    //             ]);
    //         }
    //     }
    // }

    // private function save_detail($product, $detail){
    //     $product_data = Product::find($detail->product_id);
    //     $product_detail                 = new TransaksiStock();
    //     $product_detail->code           = $this->check_code_detail($detail->id, $detail->tgl_expired);
    //     $product_detail->product_id     = $detail->product_id;
    //     $product_detail->supplier_id    = $product->supplier_id;
    //     $product_detail->expired_date   = $detail->tgl_expired;
    //     $product_detail->order_date     = $product->tgl_pembelian;
    //     $product_detail->qrcode         = $detail->qrcode;
    //     $product_detail->save();

    //     if($product_detail){
    //         $this->save_stock_opname($product, $product_detail, $detail);
    //         $stock_product  = new StockProduct;
    //         $stock_product->product_id  = $product_data->id;
    //         $stock_product->product_detail_id   = $product_detail->id;
    //         $stock_product->qty                 = $this->convert_pcs($detail->qty, $detail->satuan);
    //         $stock_product->save();
    //         if($product_detail){
    //             $json_data = array(
    //                 "success"   => TRUE,
    //                 "code"      => 201,
    //                 "message"   => "Data stock Product berhasil di simpan"
    //             );
    //         }else{
    //             $json_data = array(
    //                 "success"   => FALSE,
    //                 "code"      => 401,
    //                 "message"   => "Data stock Product gagal di simpan"
    //             );
    //         }
    //     }else{
    //         $json_data = array(
    //             "success"   => FALSE,
    //             "code"      => 401,
    //             "message"   => "Detail Product gagal di simpan"
    //         );
    //     }

    //     return $product_detail;
    // }

    public function simpan(Request $request){
        // return $request->total_detail;
        $enc_id     = $request->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }
        // $order_product = Pembelian::find($dec_id);

        // DB::beginTransaction();
        if($enc_id){
            $result = Pembelian::find($dec_id);
            $result->no_faktur            = $request->nofaktur;
            $result->tgl_faktur           = $request->faktur_date;
            // $result->tgl_pembelian     = date('Y-m-d', strtotime($request->tgl_pembelian));
            $result->nominal              = $request->nominal;
            $result->tgl_jatuh_tempo      = date('Y-m-d', strtotime($request->jatuh_tempo));
            $result->keterangan           = $request->ket;
            $result->status_pembelian     = 1;
            $result->approve_pembelian    = 1;
            $result->approved_by          = auth()->user()->username;
            $result->created_user         = auth()->user()->username;
            $result->save();

            if($result){
                for ($i=0; $i < $request->total_detail; $i++) {
                    $satuan = Satuan::find($request->satuan[$i]);
                    if(!empty($request->detail_product[$i]) || $request->detail_priduct[$i] != NULL || $request->detail_product[$i] != "" || $request->detail_product[$i] != "null"){
                        $result_detail  = PembelianDetail::where('pembelian_id', $result->id)->where('id', $request->detail_product[$i])->first();
                        $result_detail->product_id       = $request->product[$i];
                        // $result_detail->qrcode        = $request->qrcode[$i];
                        $result->notransaction           =   $result->no_faktur;
                        $result_detail->qty              = $request->qty[$i];
                        $result_detail->product_price    = $request->harga_beli[$i];
                        $result_detail->total            =   $request->nominal;
                        // $result_detail->satuan        = $satuan->name;
                        // $result_detail->tgl_expired   = date('Y-m-d', strtotime($request->expired_date[$i]));
                        $result_detail->created_user     = auth()->user()->username;
                        $result_detail->save();
                    }else{
                        $result_detail  = new PembelianDetail;
                        $result_detail->pembelian_id    = $result->id;
                        $result_detail->product_id      = $request->product[$i];
                        $result_detail->notransaction          =   $result->no_faktur;
                        $result_detail->qty             = $request->qty[$i];
                        $result_detail->product_price   = $request->harga_beli[$i];
                        $result_detail->total           =   $request->nominal;
                        $result_detail->created_user    = auth()->user()->username;
                        $result_detail->save();
                    }
                }
                // DB::commit();
                $json_data  = array(
                    'success'   => TRUE,
                    'message'   => 'Data berhasil diupdate'
                );
            }else{
                // DB::rollback();
                $json_data  = array(
                    'success'   => FALSE,
                    'message'   => 'Data belanja gagal diupdate'
                );
            }
        }else{
            $result = new Pembelian();
            $result->no_faktur            = $request->nofaktur;
            $result->tgl_faktur           = $request->faktur_date;
            // $result->tgl_pembelian     = date('Y-m-d', strtotime($request->tgl_pembelian));
            $result->nominal              = $request->nominal;
            $result->tgl_jatuh_tempo      = date('Y-m-d', strtotime($request->jatuh_tempo));
            $result->keterangan           = $request->ket;
            $result->status_pembelian     = 1;
            $result->approve_pembelian    = 1;
            $result->approved_by          = auth()->user()->username;
            $result->created_user         = auth()->user()->username;
            $result->save();

            if($result){
                for ($i=0; $i < $request->total_detail; $i++) {
                    $satuan = Satuan::find($request->satuan[$i]);

                    $result_detail  = new PembelianDetail();
                    $result_detail->pembelian_id    = $result->id;
                    $result_detail->product_id      = $request->product[$i];
                    $result_detail->notransaction          =   $result->no_faktur;
                    $result_detail->qty             = $request->qty[$i];
                    // $result_detail->product_price   = $request->harga_beli[$i];
                    $result_detail->total           =   $request->nominal;
                    $result_detail->created_user    = auth()->user()->username;
                    $result_detail->save();

                    if($result_detail){
                        if($result->status_pembelian == 1){
                            $stockadj = StockAdj::where('id_product',$result_detail->product_id[$i])->first();
                            $stockadj->stock_pembelian += $result_detail->qty;
                            $stockadj->stock_approve += $result_detail->qty;
                        }else{
                            $stockadj = StockAdj::where('id_product',$result_detail->product_id[$i])->first();
                            $stockadj->stock_pembelian += $result_detail->qty;
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
                }
                $transaksi_stock = new TransaksiStock;
                $transaksi_stock->no_transaksi = $result->no_faktur;
                $transaksi_stock->tgl_transaksi = $result->tgl_faktur;
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
                // DB::commit();
                // $json_data  = array(
                //     'success'   => TRUE,
                //     'message'   => 'Data berhasil ditambahkan'
                // );
            }else{
                // DB::rollback();
                $json_data  = array(
                    'success'   => FALSE,
                    'message'   => 'Data belanja gagal ditambahkan'
                );
            }
        }

        return json_encode($json_data);

    }

    public function coba_simpan(Request $req)
    {
        $nofaktur = $req->nofaktur;
        $tgl_faktur = $req->faktur_date;
        $nominal = $req->nominal;
        $keterangan = $req->ket;
        $status_pembelian = 1;
        $approve_pembelian =  1;
        $array_harga_product = $req->harga_product;
        $array_product = $req->produk;
        $array_qty = $req->qty;
        $tgl_jatuh_tempo = date('Y-m-d',strtotime($req->jatuh_tempo));
        $array_id_satuan = $req->tipesatuan;
        $array_total_harga = $req->total;
        $total_product = $req->total_produk;
        $total_harga_pembelian = $req->total_harga_pembelian;

        // VALIDASI
        if($nofaktur == null){
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
            $pembelian = new Pembelian;
            $pembelian->no_faktur = $nofaktur;
            $pembelian->tgl_faktur = $tgl_faktur;
            $pembelian->nominal = $nominal;
            $pembelian->tgl_jatuh_tempo = $tgl_jatuh_tempo;
            $pembelian->keterangan = $keterangan;
            $pembelian->status_pembelian = $status_pembelian;
            $pembelian->approve_pembelian = $approve_pembelian;
            $pembelian->approved_by          = auth()->user()->username;
            $pembelian->created_user         = auth()->user()->username;
            if($pembelian->save()){
                for($i=0;$i<$total_product;$i++){
                    $satuan = Satuan::find($array_id_satuan[$i]);
                    $detail_pembelian = new PembelianDetail;
                    $detail_pembelian->pembelian_id = $pembelian->id;
                    $detail_pembelian->product_id = $array_product[$i];
                    $detail_pembelian->notransaction = $pembelian->no_faktur;
                    $detail_pembelian->qty = $array_qty[$i] * $satuan->qty;
                    $detail_pembelian->product_price = $array_harga_product[$i];
                    $detail_pembelian->total = $array_total_harga[$i];
                    $detail_pembelian->created_user         = auth()->user()->username;
                    if($detail_pembelian->save()){
                        if($pembelian->status_pembelian == 1){
                            $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                            $stockadj->stock_pembelian += $detail_pembelian->qty;
                        }else{
                            $stockadj = StockAdj::where('id_product',$array_product[$i])->first();
                            $stockadj->stock_pembelian += $detail_pembelian->qty;
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

      public function ubah($enc_id){
        $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
        $product_beli = Pembelian::select('pembelian.*')->where('pembelian.id', $dec_id)->first();
        // $product_beli->transaction = date('d-m-Y', strtotime($product_beli->tgl_pembelian));

        $query = PembelianDetail::select('pembelian_detail.id','pembelian_detail.qty','pembelian_detail.product_id','tbl_product.kode_product as product_code','tbl_product.nama as product_name');
        $query->leftJoin('tbl_product','tbl_product.id','pembelian_detail.product_id');
        $query->where('pembelian_detail.pembelian_id', $dec_id);
        $total_beli_detail = $query->count();
        $product_beli_detail = $query->get();
        foreach ($product_beli_detail as $key => $value) {
            $get_satuan_id  = Satuan::where('nama','LIKE',"%{$value->satuan}%")->first();

            // $value->expired = date('d-m-Y', strtotime($value->tgl_expired));
            $value->satuan_id = $get_satuan_id->id;
        }

        return view('backend/pembelian/form', compact('enc_id','product_beli','product_beli_detail', 'total_beli_detail'));
    }

    public function hapus($enc_id){}
    public function import(){
        return view('backend/pembelian/import');
    }

}
