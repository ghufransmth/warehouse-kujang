<!DOCTYPE html>
<html>
    <head>
        <title>CETAK SO</title>
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
<h3 style="margin-top: 10px;text-align: center;"> 
    <b style="border-bottom: 3px solid black">LAPORAN SO ({{strtoupper($perusahaan->name)}})</b>
    
</h3>
<h6 style="margin-bottom: 0;text-align: center;"> {{strtoupper(date('d M Y',strtotime($tgl)))}}</h6>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="5" class="table table-bordered">

    <thead>
        <tr>
            <th width="33%" style="text-align: left; " colspan="3">Customer</th>
            <th width="20%" style="text-align: right;">No PO</th>
            <th width="10%" style="text-align: center;">Status</th>

        </tr>
    </thead>
    <tbody>
       
        <?php $grand_total = 0;?>
        @foreach($data as $key=>$value)
        <tr>
            <td style="text-align: left;"><b>{!! strtoupper($value->nama_member) !!}</b></td>
            <td style='text-align:left;'><b>{{strtoupper($value->kota_member)}}</b></td>
            <td style='text-align:left;'><b>Description</b></td>
            <td style='text-align: right;'><b>{{$value->no_nota}}</b></td>
            <td style='text-align: center;'><b>{{$value->status}}</b></td>
        </tr>
        <?php $total = 0;?>
        @foreach($value->detailpo as $key=>$result)
        <?php $total = $total + $result->ttl_price;?>
        <tr>
            <td style="text-align: left;">{{$result->qty}} {{$result->nama_satuan}}</td>
            <td style='text-align:left;'>{{$result->product_name}}</td>
            <td style='text-align: right;'>{{number_format(($result->price - ($result->price * ($result->discount/100))),0,',','.')}}</td>
            <td style='text-align: right;' colspan="1">{{number_format(round($result->ttl_price),0,',','.')}}</td>
        </tr>
        @endforeach
        <tr class="two-strips-bottom">
            <td style="text-align: right;" colspan="2"><b>Total</b></td>
            <td style='text-align: right;' colspan="2"><b>{{ number_format(round($total),0,',','.') }}</b></td>
        </tr>
        <br>
            <?php $grand_total = $grand_total + $total;?>

        @endforeach
        <tr class="two-strips-bottom">
            <td style="text-align: right;  vertical-align: middle;" colspan="2"><h4><b>GRAND TOTAL</b></h4></td>
            <td style="text-align: right;  vertical-align: middle;" colspan="2"><h4><b>{{ number_format(round($grand_total),0,',','.')}}</b></h4></td>
        </tr>
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
