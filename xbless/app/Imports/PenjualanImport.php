<?php

namespace App\Imports;

use App\Models\DetailPenjualanImport;
use App\Models\Diskon;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithValidation;



class PenjualanImport implements ToModel,WithHeadingRow, WithValidation, SkipsOnError
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
        $diskon_all = Diskon::all();
        foreach($diskon_all as $diskon){
            if($total_harga >= $diskon->min && $total_harga <= $diskon->max){
                $persen_diskon = $diskon->nilai_diskon;
                break;
            }
        }
        return new DetailPenjualanImport([
            'id_sales' => $row['sales_id'],
            'id_toko'  => $row['outlet_id'],
            'no_faktur' => $row['inv_number'],
            'kode_product' => $row['sku_code'],
            'id_satuan' => $row['satuan'],
            'qty' => $row['quantity'],
            'harga_product' => $harga_product,
            'total_harga' => $total_harga,
            'diskon'    => ($total_harga * $persen_diskon)/100,
        ]);
    }
    public function rules(): array
    {
        return [
            'sku_code' => [
                'required',
                'string',
            ],
            'sales_id' => [
                'required',
            ],
            'outlet_id' => [
                'required',
            ],
            'inv_number' => [
                'required',
            ],
            'satuan' => [
                'required',
            ],
            'quantity' => [
                'required',
            ],
        ];
    }
    public function onError(\Throwable $e)
    {
        // Handle the exception how you'd like.
    }
}
