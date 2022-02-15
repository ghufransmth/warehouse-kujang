<!DOCTYPE html>
<html>
    <head>
        <title>CETAK BARANG MASUK</title>
        <style type="text/css">

        .text-center {
            text-align: center
        }
        .text-right {
            text-align: right
        }

        </style>

    </head>
    <body>
<br/>
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN BARANG MASUK</h3>
{{-- <h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL</h4> --}}

<div>
    <table border="1">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode Product</th>
                <th>Tgl Faktur</th>
                <th>Nama Product</th>
                <th>Qty (PCS)</th>
                <th>Harga Product</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail_pembelian as $key => $value)
                <tr class="two-strips-bottom">
                    <td>{{ $key+1 }}</td>
                    <td class="text-center">{{ $value->product_id }}</td>
                    <td class="text-center">{{ date('d-M-Y',strtotime($pembelian->tgl_faktur)) }}</td>
                    <td class="text-center">{{ $value->getproduct->nama }}</td>
                    <td class="text-center">{{ $value->qty }}</td>
                    <td class="text-right">{{ $value->product_price }}</td>
                    <td class="text-right">{{ $value->total }}</td>
                </tr>
            @endforeach
        </tbody>

    </table>
<i>Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
