<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet"> --}}
	<title>Print REKAP INVOICE</title>
    {{-- <style type="text/css" media="print">
  @page { size: landscape; }
</style> --}}
<style type='text/css' media='print'>

  @page {

    size: auto;  

    margin: 0;

    margin-top: 1cm;
    /* margin-left: 0.1cm;  */

  }

</style>

<style>

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
            <h5 style="margin-top:3mm;text-align:center; font-size:32px">
                <b>REKAP INVOICE {{!empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</b>
                <hr style="width: 50%; font-weight:bold">
            </h5>
            
            <h6 style="margin-top:-14mm;text-align:center;font-size: 20px"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h6>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
      {{-- <table class="table-print" style="margin-bottom: 5px!important; font-size:19px"> --}}
      <table border="1" width="100%" cellspacing="0">
      <thead>
        <tr class="two-strips-top">
          <th style="text-align : center;">No</th>
          <th style="text-align : center;">Tgl</th>
          <th style="text-align : center;">No PO</th>
          <th style="text-align : center;">No Invoice</th>
          <th style="text-align : center;">Customer</th>
          <th style="text-align : center; width: 15%">Kota</th>
          <th style="text-align : center; ">Ekspedisi</th>
          <th style="text-align : center; ">Tot Inv (PPN) (Rp.)</th>
        </tr>
      </thead>
      <tbody>
        @php
            $sum = 0;
        @endphp
        
        @foreach($data as $key=>$value)
          @if($key == 0)
          <tr class="two-strips-bottom">
            <td style="font-size:14px">{!! $value->no !!}</td>
            <td style="font-size:11px">{!! $value->tgl !!}</td>
            <td style="text-align : left;font-size:11px">{!! $value->nopo !!}</td>
            <td style="text-align : left;font-size:11px">{!! $value->nonota !!}</td>
            <td style="text-align : left;font-size:13px">{{$value->member_name}}</td>
            <td style="text-align : left;font-size:12px">{{$value->member_kota}}</td>
            <td style="text-align : left;font-size:12px">{{$value->nama_expedisi}}</td>
            <td style="text-align : right;font-size:14px ">{{$value->totalppn}}</td>
          </tr>
          @else
          <tr class="two-strips-bottom" style="margin-top: 5rem">
            <td style="font-size:14px">{!! $value->no !!}</td>
            <td style="font-size:11px">{!! $value->tgl !!}</td>
            <td style="text-align : left;font-size:11px">{!! $value->nopo !!}</td>
            <td style="text-align : left;font-size:11px">{!! $value->nonota !!}</td>
            <td style="text-align : left;font-size:13px">{{$value->member_name}}</td>
            <td style="text-align : left;font-size:12px">{{$value->member_kota}}</td>
            <td style="text-align : left;font-size:12px">{{$value->nama_expedisi}}</td>
            <td style="text-align : right;font-size:14px ">{{$value->totalppn}}</td>
          </tr>
        @endif
        @php
            $sum += $value->total;
        @endphp
      @endforeach
      <tr>
        <td class="font-weight-bold" style="font-size:20px; text-align: right; font-weight:bold" colspan="7">Grand Total</td>
        <td style="font-weight:bold;text-align:right" colspan="2">{{number_format($sum, 0, ',', '.')}}</td>
      </tr>
      </tbody>
      </table> 
      {{-- <table border="1" width="100%" cellspacing="0">
        <tfoot>
          <tr>
            <td class="font-weight-bold" style="font-size:20px; text-align:right" colspan="8">Grand Total</td>
            <td style="font-weight:bold;text-align:right" colspan="1">{{number_format($sum, 0, ',', '.')}}</td>
          </tr>
        </tfoot>
      </table> --}}
      </div>
      </div>
      <i>Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>