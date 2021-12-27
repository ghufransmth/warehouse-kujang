<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	<title>Print SO</title>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>LAPORAN ADJUSTMENT STOK {{strtoupper($perusahaan->name)}}</b></h3>
            <h6 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</b> </h6>
        </div>
      </div>
      <div class="row" style="margin-top:5mm; margin-bottom: 3mm">
        <div class="col-md-12">
        <div class="row">
            <div class="col-md-1">Gudang</div>
            <div class="col-md-3">:<b>{{strtoupper($gudang->name)}} </b></div>
           
        </div>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      <table class="table-print" style="margin-bottom: 5px!important">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align : left; display: table-cell; vertical-align: middle;">No</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Produk</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Lama</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Adjustment</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Baru</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Catatan</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Tanggal Adjustment</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Transaksi dibuat</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
        
          <tr class="two-strips-bottom">
              <td>{!! $value->no !!}</td>
              <td>{!! $value->nama_produk !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->stock_lama !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->stock_adj !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->stock_new !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{!! $value->note !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{!! $value->tgl_adj !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{!! $value->created_by !!}</td>
          </tr>
        @endforeach
      </tbody>
      </table> 
      </div>
      </div>
    </section>
  </div>
</body>
</html>