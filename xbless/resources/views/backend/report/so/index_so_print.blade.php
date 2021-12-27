<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet"> --}}
	<title>Print SO</title>
  <style>
    @page {

    size: auto;  

    margin: 0;

    margin-top: 1cm;
    /* margin-left: 0.1cm;  */

    }
    body {
      padding-left: 1.3cm;

      padding-right: 1cm; 

      padding-top: 1cm;
    }
  </style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle; "> <br>
                <b style="border-bottom: 1px solid black">LAPORAN SO ({{strtoupper($perusahaan->name)}})</b></h3>
            <h6 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>{{strtoupper(date('d M Y',strtotime($tgl)))}}</b> </h6>
        </div>
      </div>
     
      <div class="row">
        <div class="col-md-12">
      {{-- <table class="table-print" style="margin-bottom: 5px!important"> --}}
        <table width="100%">
      <thead>
        <tr class="two-strips-top">
          <!-- <th style="text-align : left; display: table-cell; vertical-align: middle;">No</th> -->
          <th style="text-align: left; display: table-cell; vertical-align: middle;" colspan="3">Customer</th>
          <th style="text-align: right; display: table-cell; vertical-align: middle;">No PO</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php $grand_total = 0;?>
        @foreach($data as $key=>$value)
            
          <tr class="two-strips-bottom">
              <!-- <td rowspan="3" valign="top">{!! $value->no !!}</td> -->
              <td style="text-align: left; display: table-cell; vertical-align: middle;">{!! $value->nama_member !!}</td>
              <td style="text-align: left; display: table-cell; vertical-align: middle; font-weight:bold">{!! $value->kota_member !!}</td>
              <td style="text-align: left; display: table-cell; vertical-align: middle; font-weight:bold">Description</td>
              <td style="text-align: right">{!! $value->no_nota !!}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->status}}</td>
          </tr>
          <?php $total = 0;?>

   
          @foreach($value->detailpo as $key=>$result)
          <?php $total = $total + $result->ttl_price;?>
          <tr class="two-strips-bottom">
              <!-- <td style="text-align : left; display: table-cell; vertical-align: middle;"></td> -->
              <td style="text-align: right; display: table-cell; vertical-align: middle;">{{$result->qty}} {{$result->nama_satuan}}</td>
              <td style="text-align: left; display: table-cell; vertical-align: middle;">{{$result->product_name}}</td>
              <td style="text-align: right; display: table-cell; vertical-align: middle;">{{number_format(($result->price - ($result->price * ($result->discount/100))),0,',','.')}} </td>
              <td style="text-align: right" colspan="1">{{number_format(round($result->ttl_price),0,',','.')}}</td>
          </tr>
          @endforeach
          <tr class="two-strips-bottom">
            <td style="text-align: right; display: table-cell; vertical-align: middle;" colspan="2"><b>Total</b></td>
          
            <td style="text-align: right; display: table-cell; vertical-align: middle;" colspan="2"><b>{{ number_format(round($total),0,',','.') }}</b></td>
         </tr>
         
         <?php $grand_total = $grand_total + $total;?>
        @endforeach
        
       
      </tbody>
       <tr class="two-strips-bottom">
            <td style="text-align: left;  vertical-align: middle;" colspan="3"><h4><b>GRAND TOTAL</b></h4></td>
            <td style="text-align: center;  vertical-align: middle;" colspan="2"><h4><b>{{ number_format(round($grand_total),0,',','.')}}</b></h4></td>
        </tr>
      
     
      </table> 
      </div>
      </div>
      <i>Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>