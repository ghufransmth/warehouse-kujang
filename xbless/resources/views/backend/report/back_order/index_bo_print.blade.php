<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet"> --}}
	<title>Print BO</title>
  <style type="text/css" media="print">
/* @page { size: landscape; } */
    @page {
        size: auto;  

        /* margin: 0;

        margin-top: 1cm; */
      }
      /* body{

        padding-left: .5cm;

        padding-right: 1cm; 

        padding-top: 1cm;

      } */
  </style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>LAPORAN BACK ORDER {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b></h3>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      {{-- <table class="table-print" style="margin-bottom: 5px!important"> --}}
        <table border="1" cellspacing="0">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align: center;">No</th>
          <th style="text-align: center;">Tanggal</th>
          <th style="text-align: center;">Nama Toko</th>
          <th style="text-align: center;">Kota</th>
          <th style="text-align: center;">Nama Barang</th>
          <th style="text-align: center;">Sub Category</th>
          <th style="text-align: center;">Qty BO</th>
          <th style="text-align: center;">Unit Harga</th>
          <th style="text-align: center;">List Net</th>
          <th style="text-align: center;">No. BO/PO</th>
          <th style="text-align: center;">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($data as $key=>$value)
            
          <tr class="two-strips-bottom">
              <td>{{$value->no}}</td>
              <td>{{date('d M Y', strtotime($value->tgl))}}</td>
              <td style="text-align: left;">{!! $value->nama_toko !!}</td>
              <td style="text-align: left; font-size:12px ">{!! $value->kota_member !!}</td>
              <td style="text-align: left; font-size:13px;">{{$value->nama_produk}}</td>
              <td style="text-align: left; font-size:13px ">{{$value->nama_kategori}}</td>
              <td style="text-align: left; font-size:13px ">{{$value->qtybo}}</td>
              <td style="text-align: right; ">{{$value->harga}}</td>
              <td style="text-align: right; ">{{$value->ttl_harga}}</td>
              <td style="text-align: left; font-size:13px ">{!!$value->no_transaksi!!}</td>
              <td style="text-align: left; font-size: 12px ">{{$value->status}}</td>
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