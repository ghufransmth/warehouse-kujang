<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	<title>Print Barang Keluar</title>
    <style type="text/css" media="print">
  /* @page { size: landscape; } */
  @page {
    size: auto;  

    margin: 0;

    margin-top: 1cm;
  }
  body{

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
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>LAPORAN BARANG KELUAR {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</b></h3>
            <h4 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h4>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      <table class="table-print" style="margin-bottom: 5px!important">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align : left; display: table-cell; vertical-align: middle;">No</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Tanggal Invoice</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">No. Invoice</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">No. Sales Order</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Gudang</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Nama Buyer / Member</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Produk</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Qty</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Satuan</th>
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Unit Price</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
            
          <tr class="two-strips-bottom">
              <td style="font-size:14px">{!! $key + 1 !!}</td>
              <td style="font-size:14px">{!! $value->tgl !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:11px">{!! $value->nonota !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:11px">{!! $value->no_purchase !!}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:13px">{{$value->nama_gudang}}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:13px">{{$value->nama_member}} - {{$value->kota}}</td>
              <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:12px">{{$value->nama_produk}}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle; font-size:12px">{{$value->stockinput ?? '-'}}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle; font-size:11px">{{$value->namasatuan}}</td>
              <td style="text-align : right; display: table-cell; vertical-align: middle; font-size:12px">{{$value->harga}}</td>
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