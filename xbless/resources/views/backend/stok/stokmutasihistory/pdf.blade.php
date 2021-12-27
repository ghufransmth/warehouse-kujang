<!DOCTYPE html>
<html>
    <head>
        <title>CETAK HISTORY MUTASI STOCK</title>
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
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN MUTASI STOK {{strtoupper($perusahaan->name)}}</h3>
<h6 style="margin-bottom: 0;margin-top:1px;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</h6>
<table width="100%" cellspacing="0" cellpadding="2" style="margin-top:20px;">
    
    <tr>
        <td width="20%">Dari Gudang </td>
        <td>: <b>{{strtoupper($gudang->name)}} </b></td>
    </tr>
    <tr>
        <td width="20%">Ke Gudang </td>
        <td>: <b>{{strtoupper($gudangtujuan->name)}} </b></td>
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
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Sebelum Mutasi</th>
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Mutasi</th>
            <th width="10%" style="text-align: center;border: 0.5px solid #000;">Stock Setelah Mutasi</th>
            <th width="20%" style="text-align: left;border: 0.5px solid #000;">Tanggal Mutasi</th>
            <th width="20%" style="text-align: left;border: 0.5px solid #000;">Dibuat Oleh</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 0.5px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 0.5px solid #000;text-align: left;">{!! $value->nama_produk !!}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->dari_stock}}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->ke_stock}}</td>
            <td style='border: 0.5px solid #000;text-align:center;'>{{$value->new_stock}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->tgl_mutasi}}</td>
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
        </tr>
        @endfor
    </tbody>
</table>
</div>
</body>
</html>
