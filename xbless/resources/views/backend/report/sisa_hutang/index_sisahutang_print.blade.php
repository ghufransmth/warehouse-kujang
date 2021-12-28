<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet"> --}}
	  <title>Print Report Sisa Hutang</title>
    <style type="text/css" media="print">
      /* @page { size: landscape; } */
      @page {
        size: auto;
        margin: 0;
        margin-left: 0.1cm;
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
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>LAPORAN Sisa Hutang {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</b></h3>
            <h4 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h4>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
          {{-- <table class="table-print" style="margin-bottom: 5px!important"> --}}
          <table border='1' width='100%' cellspacing='0'>
            <thead>
              <tr>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">No</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">No Tanda Terima</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">Buyer / Member</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">Sales</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">Total Tagihan</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">Sisa Tagihan</th>
                <th style="text-align : center; display: table-cell; vertical-align: middle;font-size:14px;">Status</th>
              </tr>
            </thead>
            <tbody>

              @foreach($dataArr as $key=>$value)
                <tr style="border: 1px solid black">
                    <td>{{$key+1}}</td>
                    <td style="text-align : center; display: table-cell; vertical-align: middle; font-size:14px">{{$value['tanda_terima']}}</td>
                    <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:10px">{{$value['member']}}</td>
                    <td style="text-align : left; display: table-cell; vertical-align: middle; font-size:14px">{{$value['sales']}}</td>
                    <td style="text-align : right; display: table-cell; vertical-align: middle; font-size:12px">{{$value['total_tagihan']}}</td>
                    <td style="text-align : right; display: table-cell; vertical-align: middle; font-size:12px">{{$value['sisa_tagihan']}}</td>
                    <td style="text-align : center; display: table-cell; vertical-align: middle; font-size:14px">{!!$value['status']!!}</td>
                </tr>
              @endforeach
            </tbody>
            <tfoot style="border: 1px solid black">
              <tr>
                  <td colspan="4" class="text-center" style="font-size: 13px">
                      <b>Total Sisa Tagihan</b>
                  </td>
                  <td class="text-center" style="font-size: 13px; text-align:right" colspan="2">
                      <b><span id="total">{{'Rp. ' . number_format($total_tagihan_keseluruhan, 0, ',', '.') }}</span></b>
                  </td>
                  <td></td>
              </tr>
            </tfoot>
          </table> 
        </div>
      </div>
      <i style="font-size: 10px">Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>