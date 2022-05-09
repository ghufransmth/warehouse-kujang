<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\Finance;
use App\Models\FinanceDetail;
use App\Models\KomponenBiaya;

use DB;
use Auth;

class FinanceController extends Controller
{
    protected $original_column = array(
        1 => "name",
    );

    public function index(){
        return view('backend/pembayaran/finance/index');
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
        $cek = Finance::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $country = Finance::select('finance.*', 'komponen_biaya.name');
        $country->leftJoin('komponen_biaya','komponen_biaya.id','finance.komponen_biaya_id');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $country->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $country->orderBy('id','DESC');
        }
         if($search) {
          $country->where(function ($query) use ($search) {
                  $query->orWhere('name','LIKE',"%{$search}%");
          });
        }
        $totalData = $country->get()->count();

        $totalFiltered = $country->get()->count();

        $country->limit($limit);
        $country->offset($start);
        $data = $country->get();
        foreach ($data as $key=> $result)
        {
          $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
          $action = "";

          $action.="";
          $action.="<div class='btn-group'>";
          if($request->user()->can('transaksi.finance.ubah')) {
                $action.='<a href="'.route('transaksi.finance.ubah',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
          }
          if($request->user()->can('transaksi.finance.delete')) {
            $action.='<a href="#" onclick="deleteNegara(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
          }
          $action.="</div>";

          if($result->kategori == 0){
            $kategori = 'Debit';
          }else if($result->kategori == 1){
            $kategori = 'Kredit';
          }

          $result->no             = $key+$page;
          $result->tgl_transaksi  = date('d F Y', strtotime($result->tgl_transaksi));
          $result->total          = 'Rp. '. number_format($result->total,0,',','.');
          $result->kategori       = $kategori;
          $result->action         = $action;
        }


        if($request->user()->can('transaksi.finance.index')) {
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

    public function tambah(){
        return view('backend/pembayaran/finance/form');
    }

    public function list_data(Request $request){
        $total = $request->total;

        echo "
            <tr id='dataajaxproduk_".$total."' class='data-file'>
                <td>
                    <input type='text' class='form-control' id='name_".$total."' name='name[]' value=''>
                </td>
                <td><div class='input-group'>
                    <span class='input-group-addon'>Rp.</span><input type='text' class='form-control nominal_akun' id='nominal_".$total."' name='nominal[]' value='' autocomplete='off'>
                </div></td>
                <td>
                    <div class='input-group'>
                        <span class='input-group-addon'><i class='fa fa-calendar' aria-hidden='true'></i></span><input type='text' class='form-control' id='tgl_transaksi_".$total."' name='tgl_transaksi[]' value='' autocomplete='off'>
                    </div>
                </td>
                <td><input type='text' class='form-control' id='note_".$total."' name='note[]'></td>
                <td class='text-center'><a href='#!' onclick='javascript:deleteProduk(".$total.")' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
            </tr>
            <script>
                $('#tgl_transaksi_".$total."').datepicker({
                    autoclose: true,
                    format: 'dd-mm-yyyy'
                });
                $(document).on('keyup', '#nominal_".$total."', function(){
                    var value = Number(this.value.replace(/\./g, ''));
                    var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;
                    value = formatRupiah(this.value, '');
                    var nilai = this.value.replace(/\./g, '');
                    loadGrand()
                    if(!numberRegex.test(nilai)){
                        $('#nominal_".$total."').val('');
                        return false;
                    }

                    if(value.charAt(0) > 0){
                        $('#nominal_".$total."').val(getprice(nilai));
                    }else{
                        if(value.charAt(1) == '0'){
                            $('#nominal_".$total."').val(0);
                        }else{
                            $('#nominal_".$total."').val(getprice(value));
                        }
                    }
                })
            </script>
        ";
    }

    public function simpan(Request $request){
        // return response()->json([
        //     'data' => $request->all()
        // ]);
      $enc_id     = $request->enc_id;
      if ($enc_id != null) {
          $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
      }else{
          $dec_id = null;
      }

      DB::beginTransaction();
      if($enc_id){
        $komponen = KomponenBiaya::find($request->komponen);
        $result = Finance::find($dec_id);
        $result->komponen_biaya_id  = $request->komponen;
        $result->total      = str_replace(".","", $request->total_nominal);
        $result->kategori   = $komponen->kategori;
        $result->keterangan    = $request->keterangan;
        $result->save();

        if($result){
          $remove = FinanceDetail::where('finance_id', $result->id)->delete();
          for($i = 0; $i < $request->total_data; $i++){
            $detail = new FinanceDetail;
            $detail->finance_id = $result->id;
            $detail->name       = $request->name[$i];
            $detail->nominal    = str_replace(".","",$request->nominal[$i]);
            $detail->tgl_transaksi = date('Y-m-d', strtotime($request->tgl_transaksi[$i]));
            $detail->keterangan = $request->note[$i];
            $detail->save();
          }

          DB::commit();
          return response()->json([
              'data' => [
                  'code' => 201,
                  'detail'    => [
                      'title'     => 'Finance',
                      'message'   => 'Data Terupdate'
                  ]
              ]
          ]);
        }else{
          DB::rollback();
          return response()->json([
              'data' => [
                  'code' => 404,
                  'detail'    => [
                      'title'     => 'Finance',
                      'message'   => 'Data Gagal Terupdate'
                  ]
              ]
          ]);
        }

      }else{
        $komponen = KomponenBiaya::find($request->komponen);
        $result = new Finance;
        $result->komponen_biaya_id  = $request->komponen;
        $result->total      = str_replace(".","", $request->total_nominal);
        $result->kategori   = $komponen->kategori;
        $result->keterangan    = $request->keterangan;
        $result->save();

        if($result){
          for($i = 0; $i < $request->total_data; $i++){
            $detail = new FinanceDetail;
            $detail->finance_id = $result->id;
            $detail->name       = $request->name[$i];
            $detail->nominal    = str_replace(".","",$request->nominal[$i]);
            $detail->tgl_transaksi = date('Y-m-d', strtotime($request->tgl_transaksi[$i]));
            $detail->keterangan = $request->note[$i];
            $detail->save();
          }
          DB::commit();
          return response()->json([
              'data' => [
                  'code' => 201,
                  'detail'    => [
                      'title'     => 'Finance',
                      'message'   => 'Data tersimpan'
                  ]
              ]
          ]);
        }else{
          DB::rollback();
          return response()->json([
              'data' => [
                  'code' => 404,
                  'detail'    => [
                      'title'     => 'Finance',
                      'message'   => 'Data Gagal Terupdate'
                  ]
              ]
          ]);
        }
      }
    }

    public function ubah($enc_id){
      $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
      $finance = Finance::find($dec_id);
      if($finance){
        $komponen = KomponenBiaya::find($finance->komponen_biaya_id);
        $finance->tgl_transaksi = date('d-m-Y', strtotime($finance->tgl_transaksi));
        $finance->total = number_format($finance->total,0,',','.');

        $detail = FinanceDetail::where('finance_id', $finance->id)->get();
        $total_detail = FinanceDetail::where('finance_id', $finance->id)->count();
        foreach ($detail as $key => $value) {
          $value->nominal = number_format($value->nominal,0,',','.');
        }
      }
      return view('backend/pembayaran/finance/form', compact('enc_id','finance','detail','total_detail','komponen'));
    }

    public function hapus(Request $requet, $enc_id){
      $dec_id   = $this->safe_decode(Crypt::decryptString($enc_id));
      $finance = Finance::find($dec_id);
      if($finance){
          $detail = FinanceDetail::where('finance_id', $finance->id)->delete();
          $finance->delete();
          return response()->json([
              'data' => [
                  'code'  => 201,
                  'detail'    => [
                      'title'     => 'Penyesuaian Keuangan',
                      'message'   => 'Berhasil menghapus data'
                  ]
              ]
          ]);
      }else{
          return response()->json([
              'data' => [
                  'code'  => 201,
                  'detail'    => [
                      'title'     => 'Penyesuaian Keuangan',
                      'message'   => 'Maaf data tidak ditemukan silahkan hubungi administrator'
                  ]
              ]
          ]);
      }
    }
}
