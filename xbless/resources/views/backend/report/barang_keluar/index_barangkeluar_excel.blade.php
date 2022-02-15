<!DOCTYPE html>
<html>
    <head>
        <title>CETAK BARANG KELUAR</title>
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
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN BARANG KELUAR</h3>
<h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL</h4>

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
            @foreach($detail_penjualan as $key => $value)
            <tr>
                <td width="5%">{{ $key+1 }}</td>
                <td>{{ $value->id_product }}</td>
                <td>{{ $penjualan->tgl_faktur }}</td>
                <td>{{ $value->getproduct->nama }}</td>
                <td>{{ $value->qty }}</td>
                <td>{{ $value->harga_product }}</td>
                <td>{{ $value->total_harga }}</td>
                {{-- <td>Status Bayar</td> --}}
            </tr>
            @endforeach
        </tbody>
        {{-- <tfoot>
            <tr class="text-white text-center bg-primary">
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot> --}}
    </table>
<i>Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
