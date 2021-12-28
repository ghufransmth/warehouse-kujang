<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
	<title>Tanda Terima</title>
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
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
                <b>{{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Seluruh Perusahaan'}}</b></h3>
            <h6 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h6>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
            @php
                $grandTotal = 0;
            @endphp
            @foreach($data as $key => $value)
                <table class="table" style="margin-bottom: 5px!important;border:none !important">
                    <thead>
                        <tr class="">
                            <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important" colspan="7">DAERAH: {{$value->member_kota}}</th>
                        </tr>
                    </thead>
                </table> 
                <table class="table" style="margin-bottom: 5px!important;border:none !important">
                    <thead>
                        <tr class="">
                            {{-- <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th>
                            <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th>
                            <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th>
                            <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th>
                            <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th> --}}
                            <th style="text-align : center; display: table-cell; vertical-align: middle;border:none !important" colspan="7">{{$value->tgl}}</th>
                            {{-- <th style="text-align : left; display: table-cell; vertical-align: middle;border:none !important"></th> --}}
                        </tr>
                    </thead>
                </table> 
                <table class="table-print" style="margin-bottom: 5px!important">
                    <thead>
                        <tr class="two-strips-top">
                            <th style="text-align:center; display: table-cell; vertical-align: middle;">TANGGAL INVOICE</th>
                            <th style="text-align:center; display: table-cell; vertical-align: middle;">NO TANDA TERIMA</th>
                            <th style="text-align:center; display: table-cell; vertical-align: middle;">NO INVOICE</th>
                            <th style="text-align:center; display: table-cell; vertical-align: middle;">AMOUNT</th>
                            <th style="text-align: center; display: table-cell; vertical-align: middle;">TANGGAL GIRO</th>
                            <th style="text-align: center; display: table-cell; vertical-align: middle;">GIRO</th>
                            <th style="text-align: center; display: table-cell; vertical-align: middle;">KETERANGAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $totalAmount = 0; ?>
                        <tr class="two-strips-bottom">
                            <td colspan="7">{{$value->member_lengkap}}</td>
                            
                        </tr>
                        @foreach($value->datadetail as $k=>$detail)
                            <?php $totalAmount = $totalAmount + $detail->total; ?>
                            <tr class="two-strips-bottom">
                                @if ($detail->invoicett != null)
                                <td style="text-align: center">{{date('d/m/Y',strtotime($detail->invoicett->tglorder))}}</td>
                                <td style="text-align: center">{{$detail->invoicett->no_tanda_terima}}</td>
                                <td style="text-align: center; display: table-cell; vertical-align: middle;">{{$detail->invoicett->nota}}</td>
                            
                                @else
                                <td>-</td>
                                <td>-</td>
                                <td style="text-align : left; display: table-cell; vertical-align: middle;">-</td>
                                
                                @endif
                                <td style="text-align: center; display: table-cell; vertical-align: middle;"><b>{{number_format($detail->total,0,',','.')}}</b></td>
                                <td style="text-align : left; display: table-cell; vertical-align: middle;"></td>
                                <td style="text-align : left; display: table-cell; vertical-align: middle;"></td>
                                <td style="text-align : left; display: table-cell; vertical-align: middle;">{{$detail->pay_status==0?'':'Lunas'}}</td>
                            </tr>
                        @endforeach
                        <tr class="">
                            <td colspan="3" style="border:none !important">AMOUNT GIRO</td>
                            <td style="text-align: center; display: table-cell; vertical-align: middle;border:none !important"><b>{{number_format($totalAmount,0,',','.')}}</b></td>
                            <td style="text-align : left; vertical-align: middle;border:none !important"></td>
                            <td style="text-align : left; vertical-align: middle;border:none !important"></td>
                            <td style="text-align : left; vertical-align: middle;border:none !important"></td>
                        </tr>
                        @php
                             $grandTotal += $totalAmount;
                        @endphp
                        @if ($loop->last)
                       
                        <tr class="two-strips-top">
                            <td style="border:none !important; font-weight:bold" colspan="3">GRAND TOTAL</td>
                            <td style="text-align: center; border:none !important"><b>{{number_format($grandTotal,0,',','.')}}</b></td>
                            <td style="border:none !important;"></td>
                            <td style="border:none !important;"></td>
                            <td style="border:none !important;"></td>
                        </tr>
                        @endif
                       
                    </tbody>
                    
                </table> 

                
                
                
        @endforeach
        {{-- <table class="table">     
            <tbody>
                <tr class="two-strips-top">
                    <td style="border:none !important; font-weight:bold">GRAND TOTAL</td>
                    <td style="text-align: center; border:none !important" colspan="6"><b>{{number_format($grandTotal,0,',','.')}}</b></td>
                    
                </tr>
            </tbody>
        </table> --}}
        
        </div>
      </div>
      <i>Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>