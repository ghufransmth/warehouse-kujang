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
<htmlpageheader name="MyHeader1">
<br/>
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN BARANG KELUAR {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</h3>
<h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 1px solid #000;">No</th>
            <th style="text-align: center; border: 1px solid #000;">Tanggal Invoice</th>
            <th style="text-align: center;border: 1px solid #000;">No. Invoice</th>
            <th style="text-align: center;border: 1px solid #000;">No. Sales Order</th>
            <th style="text-align: center;border: 1px solid #000;">Gudang</th>
            <th style="text-align: center;border: 1px solid #000;">Nama Buyer / Member</th>
            <th style="text-align: center;border: 1px solid #000;">Produk</th>
            <th style="text-align: center;border: 1px solid #000;">Qty</th>
            <th style="text-align: center;border: 1px solid #000;">Satuan</th>
            <th style="text-align: center;border: 1px solid #000;">Unit Price</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td width="6%" style="border: 1px solid #000;text-align: center;">{{$no + 1}}</td>
            <td style="border: 1px solid #000;text-align: left;">{!! $value->tgl !!}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nonota}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->no_purchase}}</td>
            <td style='border: 1px solid #000;text-align:center;'>{{$value->nama_gudang}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_member}} - {{$value->kota}}</td>
            <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_produk}}</td>
            <td style='border: 1px solid #000;text-align:center;'>{{$value->stockinput ?? '-'}}</td>
            <td style='border: 1px solid #000;text-align:center;'>{{$value->namasatuan}}</td>
            <td style='border: 1px solid #000;text-align:right;'>{{$value->harga}}</td>
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
