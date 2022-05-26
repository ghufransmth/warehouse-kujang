<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use App\Models\KunjunganSales;
use App\Models\Hari;

use DB;
use Auth;

class KunjunganSalesController extends Controller
{

    protected $original_column = array(
        1 => "name",
        2 => "username",
        3 => "email",
        4 => "phone",
        5 => "created_at",
    );

    private function skala(){
        $result = array(
            0 => 'Weekly',
            1 => 'Biweekly',
            2 => 'Monthly'
        );

        return $result;
    }

    public function index(){
        return view('backend/kunjungan/index');
    }

    private function cekExist($column,$var,$id){
        $cek = Sales::where('id','!=',$id)->where($column,'=',$var)->first();
        return (!empty($cek) ? false : true);
    }

    function safe_encode($string) {
        $data = str_replace(array('/'),array('_'),$string);
        return $data;
    }

	function safe_decode($string,$mode=null) {
	    $data = str_replace(array('_'),array('/'),$string);
        return $data;
    }

    public function tambah(){
        $hari = Hari::all();
        $skala = $this->skala();

        $selectedHari = '';
        $selectedSkala = '';

        return view('backend/kunjungan/form', compact('hari', 'skala', 'selectedHari','selectedSkala'));
    }

    public function getData(Request $request){
        $limit = $request->length;
        $start = $request->start;
        $page  = $start +1;
        $search = $request->search['value'];

        $query = KunjunganSales::select('kunjungan_sales.id','kunjungan_sales.skala','kunjungan_sales.faktur_piutang', 'toko.name as toko','tbl_sales.nama as sales','hari.name as hari');
        $query->leftJoin('tbl_sales','tbl_sales.id','kunjungan_sales.sales_id');
        $query->leftJoin('toko','toko.id','kunjungan_sales.toko_id');
        $query->leftJoin('hari','hari.id','kunjungan_sales.hari_id');
        if(array_key_exists($request->order[0]['column'], $this->original_column)){
           $query->orderByRaw($this->original_column[$request->order[0]['column']].' '.$request->order[0]['dir']);
        }
         else{
          $query->orderBy('id','DESC');
        }
         if($search) {
          $query->where(function ($query) use ($search) {
                $query->orWhere('kunjungan_sales.faktur_piutang','LIKE',"%{$search}%");
                $query->orWhere('tbl_sales.nama','LIKE',"%{$search}%");
                $query->orWhere('toko.name','LIKE',"%{$search}%");
                $query->orWhere('hari.name','LIKE',"%{$search}%");
          });
        }
        $totalData = $query->get()->count();

        $totalFiltered = $query->get()->count();

        $query->limit($limit);
        $query->offset($start);
        $data = $query->get();
        foreach ($data as $key=> $result)
        {
            $enc_id = $this->safe_encode(Crypt::encryptString($result->id));
            $action = "";

            $action.="";
            $action.="<div class='btn-group'>";
            if($request->user()->can('kunjungan.ubah')) {
                $action.='<a href="'.route('kunjungan.edit',$enc_id).'" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Edit</a>&nbsp;';
            }
            if($request->user()->can('kunjungan.delete')) {
                $action.='<a href="#" onclick="deleteData(this,\''.$enc_id.'\')" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</a>&nbsp;';
            }
            $action.="</div>";

            if($result->skala == 0){
                $skala = 'Weekly';
            }else if($result->skala == 1){
                $skala = 'Biweekly';
            }else if($result->skala == 2){
                $skala = 'Monthly';
            }

            $result->no             = $key+$page;
            $result->skala          = $skala;
            $result->hari           = ucfirst($result->hari);
            $result->action         = $action;
        }


        if($request->user()->can('kunjungan.index')) {
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

    public function list_data(Request $request){
        $hari = Hari::all();
        $skala = $this->skala();
        $total = $request->total;

        echo "
            <tr id='dataajaxproduk_".$total."'>
                <td>
                    <select class='select2_".$total." form-control' id='sales_".$total."' name='sales[]'>
                        <option value=''>Pilih Sales </option>
                    </select>
                </td>
                <td>
                    <select class='select2_".$total." form-control' id='toko_".$total."' name='toko[]'>
                        <option value=''>Pilih Toko </option>
                    </select>
                </td>
                <td>
                    <select class='form-control select2' id='hari_".$total."' name='hari[]'>
                        <option value=''>Pilih Hari </option>
                    echo ";
                        foreach($hari as $key => $row){
                        echo"
                            <option value=".$row->id.">".ucfirst($row->name)."</option>";
                        }echo"
                    </select>
                </td>
                <td>
                    <select class='form-control select2' id='skala_".$total."' name='skala[]'>
                        <option value=''>Pilih Skala Kunjungan </option>
                    echo ";
                        foreach($skala as $key => $row){
                        echo"
                            <option value=".$key.">".$row."</option>";
                        }echo"
                    </select>
                </td>
                <td><input type='text' class='form-control' id='faktur_".$total."' name='faktur[]'></td>
                <td><a href='#!' onclick='javascript:deleteProduk(".$total.")' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
            </tr>

            <script>
                $('#hari_".$total."').select2()
                $('#skala_".$total."').select2()
                $('#sales_".$total."').select2({allowClear: false, width: '200px',
                    ajax: {
                        url: '".route('sales.getSales')."',
                        dataType: 'JSON',
                        delay: 250,
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
                });
                $('#toko_".$total."').select2({allowClear: false, width: '200px',
                    ajax: {
                        url: '".route('toko.gettoko')."',
                        dataType: 'JSON',
                        delay: 250,
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
                                    text : item.name,
                                });
                            });
                            return{
                                results: results
                            };
                        }
                    }
                });
            </script>
        ";
    }

    public function simpan(Request $request){
        $enc_id = $request->enc_id;
        if ($enc_id != null) {
            $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        }else{
            $dec_id = null;
        }

        if($dec_id){
            $check_data = KunjunganSales::where('sales_id', $request->sales)->where('toko_id', $request->toko)->where('hari_id', $request->hari)->where('faktur_piutang', $request->faktur)->first();
            if(!$check_data){
                $result = KunjunganSales::find($dec_id);
                $result->sales_id   = $request->sales;
                $result->hari_id    = $request->hari;
                $result->skala      = $request->skala;
                $result->toko_id    = $request->toko;
                $result->faktur_piutang = $request->faktur;
                $result->save();

                if($result){
                    return response()->json([
                        'code' => 201,
                        'data' => [
                            'title' => 'List Kunjungan',
                            'message' => 'success melakukan update kunjungan sales'
                        ]
                    ]);
                }else{
                    return response()->json([
                        'code' => 401,
                        'data' => [
                            'title' => 'List Kunjungan',
                            'message' => 'gagal melakukan update kunjungan sales'
                        ]
                    ]);
                }
            }else{
                return response()->json([
                    'code' => 401,
                    'data' => [
                        'title' => 'List Kunjungan',
                        'message' => 'maaf sales tersebut sudah mempunyai jadwal faktur yang diinginkan'
                    ]
                ]);
            }

        }else{
            try {
                for ($i=0; $i < $request->total_data ; $i++) {
                    $check_data = KunjunganSales::where('sales_id', $request->sales[$i])->where('toko_id', $request->toko[$i])->where('hari_id', $request->hari[$i])->where('faktur_piutang', $request->faktur[$i])->first();
                    if(!$check_data){
                        $result = new KunjunganSales;
                        $result->sales_id   = $request->sales[$i];
                        $result->hari_id    = $request->hari[$i];
                        $result->skala      = $request->skala[$i];
                        $result->toko_id    = $request->toko[$i];
                        $result->faktur_piutang = $request->faktur[$i];
                        $result->save();
                    }
                }

                return response()->json([
                    'code' => 201,
                    'data' => [
                        'title' => 'List Kunjungan',
                        'message' => 'success input data'
                    ]
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'code' => 401,
                    'data' => [
                        'title' => 'List Kunjungan',
                        'message' => $th->getMessage()
                    ]
                ]);
            }
        }
    }

    public function ubah($enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $query = KunjunganSales::select('kunjungan_sales.*', 'toko.name as toko','tbl_sales.nama as sales');
        $query->leftJoin('tbl_sales','tbl_sales.id','kunjungan_sales.sales_id');
        $query->leftJoin('toko','toko.id','kunjungan_sales.toko_id');
        $sales = $query->where('kunjungan_sales.id', $dec_id)->first();

        if($sales){
            $hari = Hari::all();
            $skala = $this->skala();

            $selectedHari = $sales->hari_id;
            $selectedSkala = $sales->skala;

            return view('backend/kunjungan/form', compact('enc_id','sales','hari','skala','selectedHari','selectedSkala'));
        }
    }

    public function hapus(Request $request, $enc_id){
        $dec_id = $this->safe_decode(Crypt::decryptString($enc_id));
        $data   = KunjunganSales::find($dec_id);
        if($data){
            $data->delete();
            return response()->json([
                    'code'  => 202,
                    'data'  => [
                        'title'     => 'List Kunjungan',
                        'message'   => 'data kunjungan sales berhasil dihapus'
                    ]
                ]);
        }else{
            return response()->json([
                'code'  => 406,
                'data'  => [
                    'title'     => 'List Kunjungan',
                    'message'   => 'data kunjungan sales gagal dihapus'
                ]
            ]);
        }
    }

}
