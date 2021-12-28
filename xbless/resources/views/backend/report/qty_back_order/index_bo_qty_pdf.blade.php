<!DOCTYPE html>
<html>
    <head>
        <title>CETAK BO QTY</title>
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
<htmlpageheader name="MyHeader1">
<br/>
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN BACK ORDER QTY {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h3>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 1px solid #000;">No</th>
            <th style="text-align: left; border: 1px solid #000;">Tanggal</th>
            <th style="text-align: left;border: 1px solid #000;">Nama Barang</th>
            <th style="text-align: left;border: 1px solid #000;">Sub Category</th>
            <th style="text-align: left;border: 1px solid #000;">Qty BO</th>
            <th style="text-align: left;border: 1px solid #000;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 1px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 1px solid #000;text-align: left;">{!! $value->tgl !!}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_produk}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_kategori}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->qty_sum}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->status}}</td>
        </tr>
        @endforeach
        @for($i=1;$i<2;$i++)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
           
        </tr>
        @endfor
    </tbody>
</table>
<i>Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
