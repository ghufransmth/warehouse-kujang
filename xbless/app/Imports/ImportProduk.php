<?php

namespace App\Imports;

use App\Models\ProdukImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportProduk implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row);
        return new ProdukImport([
            'kode_product' => $row['sku'],
            'nama' => $row['nama_produk'],
            'harga_beli' => $row['harga_beli'],
            'harga_jual' => $row['harga_jual'],
            'id_satuan' => $row['satuan'],
        ]);
    }
}
