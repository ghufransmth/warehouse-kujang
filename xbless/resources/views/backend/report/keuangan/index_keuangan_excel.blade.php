<table style="margin-top:20px;">
    <tr>
        <td colspan='9' style="text-align: center;" ><b><h3> LAPORAN BARANG MASUK {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='9' style="text-align: center;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #0a0909;">No</th>
            <th style="text-align: left; border: 1px solid #0a0909;">Tanggal Masuk</th>
            <th style="text-align: left;border: 1px solid #0a0909;">No. Purchase Order</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Nama Supplier</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Produk</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Gudang</th>
            <th style="text-align: left;border: 1px solid #0a0909;" colspan="2">Qty</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Keterangan</th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data->sortByDesc('conv_tgl')->values() as $no => $value)
        
        <tr>
            <td style="border: 1px solid #0a0909;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 1px solid #0a0909;text-align: left;">{!! $value->tgl !!}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->transaction_no}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->factoryname}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->nama_produk}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->nama_gudang}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;' data-format="#,##0">{{$value->stockinput}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->namasatuan}}</td>
            <td style='border: 1px solid #0a0909;text-align:left;'>{{$value->catatan}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='9' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


