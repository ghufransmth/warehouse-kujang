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
                <b>LAPORAN MUTASI STOK {{strtoupper($perusahaan->name)}}</b></h3>
            <h4 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</b> </h4>
        </div>
      </div>
      <div class="row" style="margin-top:5mm; margin-bottom: 3mm">
        <div class="col-md-12">
        <div class="row">
            <div class="col-md-2">Dari Gudang</div>
            <div class="col-md-3">:<b>{{strtoupper($gudang->name)}} </b></div>
        </div>
        <div class="row">
            <div class="col-md-2">Ke Gudang</div>
            <div class="col-md-3">:<b>{{strtoupper($gudangtujuan->name)}} </b></div>
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
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Sebelum Mutasi</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Mutasi</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Setelah Mutasi</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Tanggal Mutasi</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Dibuat Oleh</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
        
          <tr class="two-strips-bottom">
              <td>{!! $value->no !!}</td>
              <td>{!! $value->nama_produk !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->dari_stock !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->ke_stock !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->new_stock !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle;">{!! $value->tgl_mutasi !!}</td>
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