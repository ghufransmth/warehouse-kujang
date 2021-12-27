<table style="margin-top:20px;">
    <tr>
        <td colspan='11' style="text-align: center;" ><b><h3> LAPORAN BARANG KELUAR {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='11' style="text-align: center;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #0a0909;">No</th>
            <th style="text-align: center; border: 1px solid #0a0909;">Tanggal Invoice</th>
            <th style="text-align: center;border: 1px solid #0a0909;">No. Invoice</th>
            <th style="text-align: center;border: 1px solid #0a0909;">No. Sales Order</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Gudang</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Nama Buyer / Member</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Produk</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Qty</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Satuan</th>
            <th style="text-align: center;border: 1px solid #0a0909;">Unit Price</th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
            @php
                $nonota = $value->getinvoice != null ? $value->getinvoice->no_nota : '-';
                $nama_member = $value->transaction_order_bm_bl != null ? ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->name : '-') : '-';
                $kota = $value->transaction_order_bm_bl != null ?  ($value->transaction_order_bm_bl->getmember != null ? $value->transaction_order_bm_bl->getmember->city : '-') : '-';
                $nama_gudang = $value->getgudang != null ? $value->getgudang->name : '-';
                $nama_produk = $value->getproduct != null ? $value->getproduct->product_name : '-';
                $namasatuan = $value->getproduct != null ? ($value->getproduct->getsatuan != null ? $value->getproduct->getsatuan->name : '-') : '-';
                $harga = $value->transaction_detail != null ? round($value->transaction_detail->price - ($value->transaction_detail->price * ($value->transaction_detail->discount / 100))) : 0;
                $no_purchase = $value->transaction_order_bm_bl != null ? $value->transaction_order_bm_bl->no_nota : '-';
                $stockinput = $value->stock_input;
                $tgl = date('d/m/Y', strtotime($value->updated_at));
            @endphp
        <tr>
            <td style="border: 1px solid #0a0909;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 1px solid #0a0909;text-align: left;">{{$tgl}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$nonota}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$no_purchase}}</td>
            <td style='border: 1px solid #0a0909;text-align:center;'>{{$nama_gudang}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$nama_member}} - {{$kota}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$nama_produk}}</td>
            <td style='border: 1px solid #0a0909;text-align:center;' data-format="#,##0">{{$stockinput ?? 0}}</td>
            <td style='border: 1px solid #0a0909;text-align:center;'>{{$namasatuan}}</td>
            <td style='border: 1px solid #0a0909;text-align:right;' data-format="#,##0">{{$harga}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='11' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


