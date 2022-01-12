<?php

namespace App\Imports;

use Illuminate\Support\Collection;
// use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\ImportPembelian;
use App\Models\Product;

class PembelianImport implements ToModel,WithHeadingRow
{
   public function model(array $row)
   {
    $harga_product = Product::where('kode_product', $row["kode_product"])->first()->harga_beli;
    $total_harga = $harga_product * $row['quantity'];
    return new ImportPembelian([
        'no_faktur' => $row['inv_number'],
        'kode_product' => $row['kode_product'],
        'satuan_id' => $row['satuan'],
        'qty' => $row['quantity'],
        'harga_product' => $harga_product,
        'total_harga' => $total_harga,
    ]);
   }
}
