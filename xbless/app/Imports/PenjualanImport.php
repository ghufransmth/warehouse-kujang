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
