<!DOCTYPE html>
<html>
    <head>
        <title>CETAK PO</title>
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
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN PO ({{strtoupper($perusahaan->name)}})</h3>
<h6 style="margin-bottom: 0;text-align: center;"> {{strtoupper(date('d M Y',strtotime($tgl)))}}</h6>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 0.5px solid #000;">No</th>
            <th width="33%" style="text-align: left; border: 0.5px solid #000;">No PO</th>
            <th width="10%" style="text-align: left;border: 0.5px solid #000;">Nama Customer</th>
            <th width="10%" style="text-align: left;border: 0.5px solid #000;">Kota</th>
            <th width="10%" style="text-align: left;border: 0.5px solid #000;">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 0.5px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 0.5px solid #000;text-align: left;">{!! $value->no_nota !!}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->nama_member}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->kota_member}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->status}}</td>
        </tr>
        @endforeach
        @for($i=1;$i<2;$i++)
        <tr>
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
