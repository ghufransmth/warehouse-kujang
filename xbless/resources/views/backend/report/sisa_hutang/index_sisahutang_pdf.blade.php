<!DOCTYPE html>
<html>
    <head>
        <title>Laporan Sisa Hutang</title>
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
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN Sisa Hutang {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</h3>
<h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 1px solid #000;">No</th>
            <th style="text-align: center; border: 1px solid #000;">No Tanda Terima</th>
            <th style="text-align: center;border: 1px solid #000;">Buyer / Member</th>
            <th style="text-align: center;border: 1px solid #000;">Sales</th>
            <th style="text-align: center;border: 1px solid #000;">Total Tagihan</th>
            <th style="text-align: center;border: 1px solid #000;">Sisa Tagihan</th>
            <th style="text-align: center;border: 1px solid #000;">Status</th>
        </tr>
    </thead>
    <tbody>
      
        @foreach ($data as $no => $value)
        <tr>
              <td style="text-align:left; border: 1px solid #000;">{{$no+1}}</td>
              <td style="text-align:center; border: 1px solid #000;">{{$value['tanda_terima']}}</td>
              <td style="text-align:left; border: 1px solid #000;">{{$value['member']}}</td>
              <td style="text-align:left; border: 1px solid #000;">{{$value['sales']}}</td>
              <td style="text-align:right; border: 1px solid #000;">{{$value['total_tagihan']}}</td>
              <td style="text-align:right; border: 1px solid #000;">{{$value['sisa_tagihan']}}</td>
              <td style="text-align:center; border: 1px solid #000;">{!!$value['status'] !!}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr >
            <td colspan="5" style="text-align: left; border: 1px solid #000;">
                <b>Total Tagihan Keseluruhan</b>
            </td>
            <td  colspan="2" style="text-align: right; border: 1px solid #000;">
                <b><span id="total">{{'Rp. ' . number_format($total_sisa_tagihan, 0, ',', '.') }}</span></b>
            </td>
            <td>
                
            </td>
        </tr>
    </tfoot>
</table>
<i style="font-size: 10px; margin-top: 3rem">Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
