<!DOCTYPE html>
<html>
    <head>
        <title>CETAK REKAP INVOICE</title>
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
<div class="title">
    <h3 style="margin-top: 10px;text-align: center;"> REKAP INVOICE {{!empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</h3>
    <h6 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h6>
</div>

</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 0.5px solid #000;">No</th>
            <th style="text-align: center; border: 0.5px solid #000;">Tgl</th>
            <th style="text-align: center;border: 0.5px solid #000;">No. PO</th>
            <th style="text-align: center;border: 0.5px solid #000;">No Invoice</th>
            <th style="text-align: center;border: 0.5px solid #000;">Customer</th>
            <th style="text-align: center;border: 0.5px solid #000;">Kota</th>
            <th style="text-align: center;border: 0.5px solid #000;">Ekspedisi</th>
            <th style="text-align: center;border: 0.5px solid #000;">Tot Inv (PPN) (Rp.)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sum = 0;
        @endphp
       
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 0.5px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
            <td style="border: 0.5px solid #000;text-align: left;">{!! $value->tgl !!}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->nopo}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->nonota}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->member_name}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->member_kota}}</td>
            <td style='border: 0.5px solid #000;text-align:left;'>{{$value->nama_expedisi}}</td>
            <td style='border: 0.5px solid #000;text-align:right;'>{{$value->totalppn}}</td>
        </tr>
        @php
            $sum += $value->total;
        @endphp
        
        @endforeach
       
        <tr>
            <td style="border: 0.5px solid #000;font-size:20px; font-weight:bold" colspan="7">Grand Total</td>
            <td style="border: 0.5px solid #000;font-weight:bold;text-align:right">{{number_format($sum, 0, ',', '.')}}</td>
        </tr>
        @for($i=1;$i<2;$i++)
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
<i>Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
