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
        <b>LAPORAN STOCK OPNAME</b> </h3>
        </div>
      </div>
      <div class="row" style="margin-top:5mm; margin-bottom: 3mm">
        <div class="col-md-12">
        <div class="row">
            <div class="col-md-3">No. Transaksi</div>
            <div class="col-md-3">: {{$stokopname->notransaction}}</div>
            <div class="col-md-3">Tanggal Stock Opname</div>
            <div class="col-md-3">: {{date('d M Y',strtotime($stokopname->faktur_date))}}</div>
        </div>
        <div class="row">
            <div class="col-md-3">Nama Perusahaan</div>
            <div class="col-md-3">: {{$perusahaan}} </div>
            <div class="col-md-3">Stok Gudang </div>
            <div class="col-md-3">: {{$gudang}}</div>
        </div>

        <div class="row">
          <div class="col-md-3">Penanggung Jawab SO</div>
          <div class="col-md-3">: {{$stokopname->pic}}</div>
          <div class="col-md-3">Officer SO </div>
          <div class="col-md-3">: {{$stokopname->created_by}}</div>
        </div>
        @if($stokopname->flag_proses=='1')
        <div class="row">
            <div class="col-md-3">Tanggal Approval </div>
            <div class="col-md-3">: {{date('d M Y',strtotime($stokopname->approved_at))}} </div>
            <div class="col-md-3">Officer App SO </div>
            <div class="col-md-3">: {{$stokopname->approved_by}}</div>
        </div>
        @endif
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      <table class="table-print" style="margin-bottom: 5px!important">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align : left; display: table-cell; vertical-align: middle;">Nama Produk</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Awal Gudang</th>
          <th style="text-align : center; display: table-cell; vertical-align: middle;">Stock Opname</th>
        </tr>
      </thead>
      <tbody>
        @foreach($stokopname->details as $key=>$value)
          <?php $satuan = $value->product->getsatuan?$value->product->getsatuan->name:'-'; ?>
          <tr class="two-strips-bottom">
              <td>{!! $value->product?$value->product->product_name:'-' !!}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{!! $value->qtyProduk==null?'-':$value->qtyProduk !!} {{$satuan}}</td>
              <td style="text-align : center; display: table-cell; vertical-align: middle;">{{$value->qtySO}} {{$satuan}}</td>
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