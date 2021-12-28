<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	<title>Laporan Stok</title>
    <style type="text/css" media="print">
  @page { size: landscape; }
</style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      {{-- <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>History STOK {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</b></h3>
            <h4 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>PERIODE TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h4>
        </div>
      </div> --}}
      <br/>
      <div class="row">
        <div class="col-md-12">
      <table class="table-print" style="margin-bottom: 5px!important">
      <thead>
       
        <tr class="two-strips-top">
          <th style="text-align:center ; display: table-cell; vertical-align: middle;" colspan="9">
            <h6 class="font-weight-bold">
                History STOK {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}
                PERIODE TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> 
            </h6>
            
          </th>
        </tr>
        <tr class="two-strips-top">
          <th style="text-align: center; display: table-cell; vertical-align: middle;">No</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">No Invoice</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">No Transaksi</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Tanggal</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Stok Code</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Gudang</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Keterangan</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Qty</th>
          <th style="text-align: center; display: table-cell; vertical-align: middle;">Satuan</th>
        </tr>
      </thead>
      <tbody>
        @php
            $temp = 0;
        @endphp
        @foreach($data as $key=>$value)
        @php
            $temp += $value->qty;
            $satuan = $value->satuan;
        @endphp
          <tr class="two-strips-bottom">
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$key+1}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->no_invoice}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->no_transaction}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->dateorder}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{!!$value->product_code!!}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->gudang_name}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->ket}}</td>
              <td style="text-align: right; display: table-cell; vertical-align: middle;">{{$value->qty}}</td>
              <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$value->satuan}}</td>
          </tr>
        @endforeach
        <tr>
          <th colspan="7" style="text-align: center">
              <span id="stock-periode">Stok Periode {{!empty($product) ? strtoupper($product->product_name) : ''}} {{date('d M Y', strtotime($filter_tgl_start))}} - {{date('d M Y', strtotime($filter_tgl_end))}}</span>
          </th>
          <th colspan="1" style="text-align: right">
               <span id="sum-stock-periode">{{number_format($sum_total_filter,0,',','.')}}</span>
          </th>
          <th colspan="1" style="text-align: center">
               <span id="sum-stock-periode">{{$satuan}}</span>
          </th>
      </tr>
      <tr>
          <th colspan="9" style="text-align: center">
              SUMMARY
          </th>
      </tr>
      <tr>
          <th colspan="7" style="text-align: right">
              STOCK CUT OFF {{!empty($product) ? strtoupper($product->product_name) : ''}} SAMPAI TANGGAL {{date('d M Y', strtotime($filter_tgl_start . '-1 days'))}}
          </th>
          <th colspan="1" style="text-align: right">
              <span id="stock-cut-off-one-year">{{number_format($sum_total_cutoff,0,',','.')}}</span>
          </th>
          <th colspan="1" style="text-align: center">
              <span id="stock-cut-off-one-year">{{$sum_total_cutoff > 0 ? $satuan : '-'}}</span>
          </th>
      </tr>
      <tr>
          <th colspan="7" style="text-align: right">
              <span id="stock-periode-summary">Stok {{!empty($product) ? strtoupper($product->product_name) : ''}} Periode {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</span>
              
          </th>
          <th colspan="1" style="text-align: right">
              <span id="sum-stock-periode-summary">{{number_format($sum_total_filter,0,',','.')}}</span>
          </th>
          <th colspan="1" style="text-align: center">
              <span id="sum-stock-periode-summary">{{$satuan}}</span>
          </th>
      </tr>
      <tr>
          <th colspan="7" style="text-align: right">
              <span id="total-stock-periode">Total Stok {{!empty($product) ? strtoupper($product->product_name) : ''}} Periode {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</span>
          </th>
          <th colspan="1" style="text-align: right">
              <span id="total-summary-stock-periode">{{number_format($sum_total_filter + $sum_total_cutoff,0,',','.')}}</span>
          </th>
          <th colspan="1" style="text-align: center">
              <span id="total-summary-stock-periode">{{$satuan}}</span>
          </th>
      </tr>
      </tbody>
      </table> 
      </div>
      </div>
      <div style="margin-top: 3rem">
        <i style="font-size: 10px">Printed date : <?php echo date("d M Y") ?> </i>
      </div>
      
    </section>
  </div>
</body>
</html>