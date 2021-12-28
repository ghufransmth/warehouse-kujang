<table style="margin-top:20px;">
    <tr>
        <td colspan='11' style="text-align: center;" ><b><h3> LAPORAN BACK ORDER {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}} </h3> </b></td>
    </tr>
    <tr>
        <td colspan='5' style="text-align: center;" ><b><h4></h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #0a0909;">No</th>
            <th style="text-align: left; border: 1px solid #0a0909;"><b>Tanggal</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Nama Toko</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Kota</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Nama Barang</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Sub Category</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Qty BO</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Unit Harga</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>List Net</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>No. BO/PO</b></th>
            <th style="text-align: left;border: 1px solid #0a0909;"><b>Status</b></th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        
        <tr>
            <td style="border: 1px solid #0a0909;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 1px solid #0a0909;text-align: left;">{!! $value->tgl !!}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->nama_toko}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->kota_member}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->nama_produk}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->nama_kategori}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->qtybo}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->harga}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->ttl_harga}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{!!$value->no_transaksi!!}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->status}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='5' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


