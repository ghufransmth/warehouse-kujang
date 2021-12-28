<!DOCTYPE html>
<html>
    <head>
        <title>LAPORAN TANDA TERIMA</title>
        <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
        <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
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

<br/>
<h6 style="margin-top: 10px;text-align: center;">{{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Seluruh Perusahaan'}}</h6>
<h6 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h6>
<?php $grandTotal = 0; ?>
<div>
    @foreach($data as $key=>$value)
    
    <table class="table" style="margin-bottom: 5px!important;border:none !important">
        <thead>
            <tr class="">
                <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important" colspan="7">DAERAH: {{$value->member_kota}}</th>
            </tr>
        </thead>
    </table> 

    <table class="table" style="margin-bottom: 5px!important;border:none !important">
        <thead>
            <tr class="">
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important" >{{$value->tgl}}</th>
                <th style="text-align:left; display: table-cell; vertical-align: middle;border:none !important"></th>
            </tr>
        </thead>
    </table> 
    <table class="table-print" style="margin-bottom: 5px!important">
    <thead>
        <tr class="two-strips-top">
        <th style="text-align:center; display: table-cell; vertical-align: middle;">TANGGAL INVOICE</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">NO TANDA TERIMA</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">NO INVOICE</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">AMOUNT</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">TANGGAL GIRO</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">GIRO</th>
        <th style="text-align:center; display: table-cell; vertical-align: middle;">KETERANGAN</th>
        </tr>
    </thead>
    <tbody>
       
        @php
             $totalAmount = 0;
             
        @endphp
        <tr class="two-strips-bottom">
            <td colspan="7">{{$value->member_lengkap}}</td>
        </tr>
        @foreach($value->datadetail as $k=>$detail)
        @php
            $totalAmount = $totalAmount + $detail->total;
        @endphp
        
        <tr class="two-strips-bottom">
            <td style="text-align: center">{{date('d/m/Y',strtotime($detail->invoicett->tglorder))}}</td>
            <td style="text-align: center">{{$detail->invoicett->no_tanda_terima}}</td>
            <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$detail->invoicett->nota}}</td>
            <td style="text-align: center; display: table-cell; vertical-align: middle;"><b>{{number_format($detail->total,0,',','.')}}</b></td>
            <td style="text-align: left; display: table-cell; vertical-align: middle;"></td>
            <td style="text-align: left; display: table-cell; vertical-align: middle;"></td>
            <td style="text-align: left; display: table-cell; vertical-align: middle;">{{$detail->pay_status==0?'':'Lunas'}}</td>
        </tr>
        @endforeach
        <tr class="two-strips-bottom">
            <td colspan="3" style="text-align:center;font-weight:bold">AMOUNT GIRO</td>
         
            <td style="text-align:center;font-weight:bold; border-right:none !important"><b>{{number_format($totalAmount,0,',','.')}}</b></td>
            <td style="text-align:left;font-weight:bold; border-left:none !important" colspan="3"></td>
        </tr>
        @php
            $grandTotal += $totalAmount;
        @endphp
        @if ($loop->last)
        
        <tr class="two-strips-top two-strips-bottom">
            <td style="text-align:center; font-weight:bold" colspan="3">GRAND TOTAL</td>
            <td style="text-align:center; display: table-cell; vertical-align: middle;font-weight:bold; border-right:none !important"><b>{{number_format($grandTotal,0,',','.')}}</b></td>
            <td style="text-align:left;font-weight:bold; border-left:none !important" colspan="3"></td>
        </tr>
        @endif
    </tbody>
  </table> 
  @endforeach
  <br>
<i style="font-size:10px">Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
