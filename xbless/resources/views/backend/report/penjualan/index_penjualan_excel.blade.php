<table style="margin-top:20px;">
    <tr>
        <td colspan='5' style="text-align: center;" ><b><h3> LAPORAN PENJUALAN {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}} </h3> </b></td>
    </tr>
    <tr>
        <td colspan='5' style="text-align: center;" ><b><h4></h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #000;"><b>No</b></th>
            <th style="text-align: left; border: 1px solid #000;"><b>Stock Code</b></th>
            <th style="text-align: left;border: 1px solid #000;"><b>Sub Category</b></th>
            <th style="text-align: left;border: 1px solid #000;"><b>Part No</b></th>
            <th style="text-align: left;border: 1px solid #000;"><b>Qty Penjualan</b></th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td style="border: 1px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 1px solid #000;text-align: left;">{!! $value->code !!}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_kategori}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->part_no}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->qty}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='5' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


