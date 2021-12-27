<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	<title>Print Penjualan</title>
    <style type="text/css" media="print">
  @page { size: landscape; }
</style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>LAPORAN PENJUALAN {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b></h3>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      <table class="table-print" style="margin-bottom: 5px!important">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align : left; display: table-cell; vertical-align: middle;">No</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Stock Code </th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Sub Category</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Part No</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Qty Penjualan</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
            
          <tr class="two-strips-bottom">
              <td>{!! $value->no !!}</td>
              <td>{!! $value->code !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{{$value->nama_kategori}}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{{$value->part_no}}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{{$value->qty}}</td>
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