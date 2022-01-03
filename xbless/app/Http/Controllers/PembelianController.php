<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Product;
use App\Models\Pembelian;
use App\Models\PembelianDetail;
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

        $dataquery = Pembelian::select('id', 'no_nota', 'flag_proses', 'tgl_pembelian', 'note', 'created_user');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $dataquery->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $dataquery->orderBy('id','DESC');
        }
         if($search) {
          $dataquery->where(function ($query) use ($search) {
                  $query->orWhere('no_nota','LIKE',"%{$search}%");
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
                $action.='<a href="'.route('product.product_beli.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
                $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-trash"></i> Hapus</a>&nbsp;';
                $action.="</div>";
            }else if($result->flag_proses == 1){
                $action.= '<span class="label label-danger">Data tidak bisa diedit kembali</span>&nbsp;';
                $action.='<a href="'.route('product.product_beli.detail', $enc_id).'" class="btn btn-success btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-eye"></i> Detail</a>&nbsp;';
            }

            $result->no             = $key+$page;
            $result->id             = $result->id;
            $result->no_nota        = $result->no_nota;
            $result->tgl_transaksi  = date('d M Y', strtotime($result->tgl_pembelian));
            $result->note           = $result->note;
            $result->created_user   = $result->created_user;
            $result->action         = $action;
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

    public function tambah_product(Request $req)
    {
        $total = $req->total;
        // $satuan = Satuan::all();

        echo "
        <tr id='detail_product_".$total."'>
        <input type='hidden' id='detail_product' name='detail_product[]'>
            <td>
                <select id='product_".$total."' name='product[]' class='select2_".$total." form-control'>
                    <option value='0' selected disabled>Pilih Product</option>
                </select>
            </td>
            <td><input type='text' class='form-control' id='qty_".$total."' name='qty[]'></td>
            <td>
                <select id='satuan_".$total."' name='satuan[]' class='select2_".$total." form-control'>
                    <option value='0' selected disabled>Pilih Satuan</option>
                </select>
            </td>
            <td><input type='text' class='form-control formatTgl' id='expired_date' name='expired_date[]' value='dd-mm-yyyy'></td>
            <!-- <td>
                <input type='text' class='form-control' id='harga_beli_".$total."' name='harga_beli[]'>
             </td> -->

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
                                text : item.code+' | '+item.name,
                            });
                          });
                          return{
                            results: results
                          };
                      }
                    }
                })
                $('#qty_".$total."').TouchSpin({
                     min: 1,
                     max: 999999,
                     buttondown_class: 'btn btn-white',
                     buttonup_class: 'btn btn-white'
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

    public function simpan(Request $request){
        $enc_id     = $request->enc_id;

        if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
          $dec_id = null;
        }
        $order_product = Pembelian::find($dec_id);

        DB::beginTransaction();
        if($enc_id){
            $result = Pembelian::find($dec_id);
            $result->no_nota        = $request->nota;
            $result->supplier_id    = $request->supplier_product;
            $result->tgl_pembelian  = date('Y-m-d', strtotime($request->tgl_pembelian));
            $result->note           = $request->note;
            $result->flag_proses    = 0;
            $result->created_user   = auth()->user()->username;
            $result->save();

            if($result){
                for ($i=0; $i < $request->total_detail; $i++) {
                    $satuan = Satuan::find($request->satuan[$i]);

                    if(!empty($request->detail_product[$i]) || $request->detail_priduct[$i] != NULL || $request->detail_product[$i] != "" || $request->detail_product[$i] != "null"){
                        $result_detail  = PembelianDetail::where('product_beli_id', $result->id)->where('id', $request->detail_product[$i])->first();
                        $result_detail->product_id      = $request->product[$i];
                        $result_detail->qrcode          = $request->qrcode[$i];
                        $result_detail->qty             = $request->qty[$i];
                        $result_detail->satuan          = $satuan->name;
                        $result_detail->harga_beli      = $request->harga_beli[$i];
                        $result_detail->tgl_expired     = date('Y-m-d', strtotime($request->expired_date[$i]));
                        $result_detail->created_user    = auth()->user()->username;
                        $result_detail->save();
                    }else{
                        $result_detail  = new PembelianDetail();
                        $result_detail->product_beli_id = $result->id;
                        $result_detail->product_id      = $request->product[$i];
                        $result_detail->qrcode          = $request->qrcode[$i];
                        $result_detail->qty             = $request->qty[$i];
                        $result_detail->satuan          = $satuan->name;
                        $result_detail->harga_beli      = $request->harga_beli[$i];
                        $result_detail->tgl_expired     = date('Y-m-d', strtotime($request->expired_date[$i]));
                        $result_detail->created_user    = auth()->user()->username;
                        $result_detail->save();
                    }
                }
                DB::commit();
                $json_data  = array(
                    'success'   => TRUE,
                    'message'   => 'Data berhasil diupdate'
                );
            }else{
                DB::rollback();
                $json_data  = array(
                    'success'   => FALSE,
                    'message'   => 'Data belanja gagal diupdate'
                );
            }
        }else{
            $result = new Pembelian();
            $result->no_nota        = $request->nota;
            $result->supplier_id    = $request->supplier_product;
            $result->tgl_pembelian  = date('Y-m-d', strtotime($request->tgl_pembelian));
            $result->note           = $request->note;
            $result->flag_proses    = 0;
            $result->created_user   = auth()->user()->username;
            $result->save();

            if($result){
                for ($i=0; $i < $request->total_detail; $i++) {
                    $satuan = Satuan::find($request->satuan[$i]);

                    $result_detail  = new PembelianDetail();
                    $result_detail->product_beli_id = $result->id;
                    $result_detail->product_id      = $request->product[$i];
                    $result_detail->qrcode          = $request->qrcode[$i];
                    $result_detail->qty             = $request->qty[$i];
                    $result_detail->satuan          = $satuan->name;
                    $result_detail->harga_beli      = $request->harga_beli[$i];
                    $result_detail->tgl_expired     = date('Y-m-d', strtotime($request->expired_date[$i]));
                    $result_detail->created_user    = auth()->user()->username;
                    $result_detail->save();
                }
                DB::commit();
                $json_data  = array(
                    'success'   => TRUE,
                    'message'   => 'Data berhasil ditambahkan'
                );
            }else{
                DB::rollback();
                $json_data  = array(
                    'success'   => FALSE,
                    'message'   => 'Data belanja gagal ditambahkan'
                );
            }
        }

        return json_encode($json_data);

    }

    public function search_product(Request $request){
        // $product = Product::select('product.id','product.nama','product.code','product.satuan_id', 'satuan.name as satuan_product')
        //             ->leftJoin('satuan','satuan.id', 'product.satuan_id')
        //             ->orWhere('product.name', 'LIKE', "%{$request->search}%")
        //             ->orWhere('product.code', 'LIKE', "%{$request->search}%")
        //             ->limit(10)
        //             ->get();
        $product = Product::select('product.*')
                    ->orWhere('product.nama','LIKE',"%{$request->search}%")
                    ->limit(10)
                    ->get();

        return json_encode($product);
    }

}
