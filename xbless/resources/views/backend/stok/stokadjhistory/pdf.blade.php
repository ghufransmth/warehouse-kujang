<!DOCTYPE html>
<html>
    <head>
        <title>CETAK HISTORY ADJUSTMENT STOCK</title>
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
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN ADJUSTMENT STOK {{strtoupper($perusahaan->name)}}</h3>
<h6 style="margin-bottom: 0;text-align: center;margin-top:1px"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</h6>
<table width="100%" cellspacing="0" cellpadding="2" style="margin-top:20px;">
    
    <tr>
        <td width="20%">Gudang </td>
        <td>: <b>{{strtoupper($gudang->name)}} </b></td>
    </tr>
</table>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 0.5px solid #000;">No</th>
            <th width="33%" style="text-align: left; border: 0.5px solid #000;">Produk</th>
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Lama</th>
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Adjustment</th>
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Baru</th>
            <th width="20%" style="text-align: left;border: 0.5px solid #000;">Catatan</th>
            <th width="20%" style="text-align: left;border: 0.5px solid #000;">Tanggal Adjustment</th>
            <th width="20%" style="text-align: left;border: 0.5px solid #000;">Transaksi dibuat</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 0.5px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 0.5px solid #000;text-align: left;">{!! $value->nama_produk !!}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->stock_lama}}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->stock_adj}}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->stock_new}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->note}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->tgl_adj}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->created_by}}</td>
        </tr>
        @endforeach
        @for($i=1;$i<5;$i++)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
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
</div>
</body>
</html>
