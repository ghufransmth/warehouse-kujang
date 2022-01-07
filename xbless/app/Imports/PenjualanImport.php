<?php

namespace App\Imports;

use App\Models\DetailPenjualanImport;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;




class PenjualanImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        // $anggota = Anggota::orwhere('no_ktp', $row['nik'])->orwhere('no_karyawan',$row['nik'])->first();
        // $plafon = new Plafon();
        // $plafon = $plafon->where('anggota_id', $anggota->id)->first();
        // if(isset($plafon)){
        //     if(!isset($row['plafon_1'])){
        //         $data1 = $plafon->plafon_1;
        //     }else{
        //         $data1 = $row['plafon_1'];
        //     }
        //     if(!isset($row['plafon_2'])){
        //         $data2 = $plafon->plafon_2;
        //     }else{
        //         $data2 = $row['plafon_2'];
        //     }
        //     $plafon->update([
        //         'anggota_id' => $plafon->anggota_id,
        //         'plafon_1' => $data1,
        //         'plafon_2' => $data2,
        //     ]);
        //     return $plafon;
        // }else{
        //     return new Plafon([
        //         'anggota_id' => $anggota->id,
        //         'plafon_1' => $row['plafon_1'],
        //         'plafon_2' => $row['plafon_2'],
        //     ]);
        // }
        // $row['inv_number'], $row['sku_code'], $row['outlet_id'], $row['sales_id'], $row['satuan'], $row['quantity']
        // dd($row);
        $harga_product = Product::where('kode_product', $row["sku_code"])->first()->harga_jual;
        $total_harga = $harga_product * $row['quantity'];
        return new DetailPenjualanImport([
            'id_sales' => $row['sales_id'],
            'id_toko'  => $row['outlet_id'],
            'no_faktur' => $row['inv_number'],
            'kode_product' => $row['sku_code'],
            'id_satuan' => $row['satuan'],
            'qty' => $row['quantity'],
            'harga_product' => $harga_product,
            'total_harga' => $total_harga,
        ]);
    }
}
