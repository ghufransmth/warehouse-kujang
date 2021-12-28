<!DOCTYPE html>
<html>
<head>
  	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	  <title>Print PO</title>
    <style>
      @page {

        size: auto;

        margin: 0;

        margin-left: 0.1cm;
        margin-top: 1cm;

      }
      body{

        padding-left: 1.0cm !important;

        padding-right: 0.7cm !important;

        padding-top: 1.1cm !important;

      }
    </style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b style="border-bottom: 2px solid black">LAPORAN PO ({{strtoupper($perusahaan->name)}})</b>
            </h3>
            <h6 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>{{strtoupper(date('d M Y',strtotime($tgl)))}}</b> </h6>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      {{-- <table class="table-print" style="margin-bottom: 5px!important"> --}}
        <table border='1' width='100%' cellspacing='0'>
      <thead>
        <tr class="two-strips-top">
          <th style="text-align: center; display: table-cell; vertical-align: middle; font-size: 14px">No</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle; font-size: 14px">No PO</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle; font-size: 14px">Nama Customer</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle; font-size: 14px">Kota</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle; font-size: 14px">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
            
          <tr class="two-strips-bottom">
              <td style="text-align: center; font-size: 13px">{!! $value->no !!}</td>
              <td style="text-align: center; font-size: 13px">{!! $value->no_nota !!}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle; font-size: 13px">{!! $value->nama_member !!}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle; font-size: 13px">{!! $value->kota_member !!}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle; font-size: 13px">{{$value->status}}</td>
          </tr>
        @endforeach
      </tbody>
      </table> 
      </div>
      </div>
      <i>Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>