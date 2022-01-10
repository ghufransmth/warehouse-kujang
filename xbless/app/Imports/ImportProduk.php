<?php

namespace App\Imports;

use App\Models\ProdukImport;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;
class ImportProduk implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
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
    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
            ],
            'nama_produk' => [
                'required',
            ],
            'harga_beli' => [
                'required',
            ],
            'harga_jual' => [
                'required',
            ],
            'satuan' => [
                'required',
            ],
        ];
    }
    public function onError(\Throwable $e)
    {
        // Handle the exception how you'd like.
    }
}
