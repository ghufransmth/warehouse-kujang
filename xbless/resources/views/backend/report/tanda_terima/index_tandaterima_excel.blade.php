<table style="margin-top:20px;">
    <tr>
        <td colspan='7' style="text-align: center; border: 1px solid #0a0909;" ><b><h3> LAPORAN SO ({{ !empty($perusahaan) ? strtoupper($perusahaan->name) : 'Seluruh Perusahaann'}})</h3> </b></td>
    </tr>
    <tr>
        <td colspan='7' style="text-align: center;border: 1px solid #0a0909;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<?php $grandTotal = 0; ?>
@foreach($data as $key=>$value)
    
    <table class="table" style="margin-bottom: 5px!important;border:none !important">
        <thead>
            <tr class="">
                <th colspan="7" style="border: 1px solid #0a0909;">DAERAH: {{$value->member_kota}}</th>
            </tr>
        </thead>
    </table> 

    <table class="table" style="margin-bottom: 5px!important;border:none !important">
        <thead>
            <tr class="">
                <th colspan="7" style="border: 1px solid #0a0909;">{{$value->tgl}}</th>
            </tr>
        </thead>
    </table> 
    <table class="table-print" style="margin-bottom: 5px!important">
        <thead>
            <tr class="two-strips-top">
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">TANGGAL INVOICE</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">NO TANDA TERIMA</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">NO INVOICE</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">AMOUNT</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">TANGGAL GIRO</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">GIRO</th>
            <th style="text-align:center; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
            @endphp
            <tr class="two-strips-bottom">
                <td colspan="7">{{$value->member_lengkap}}</td>
                
            </tr>
            @foreach($value->datadetail as $k=>$detail)
                @php
                    $totalAmount = $totalAmount + $detail->total;
                @endphp
             
                <tr class="two-strips-bottom">
                    <td style="border: 1px solid #0a0909; text-align: center">{{date('d/m/Y',strtotime($detail->invoicett->tglorder))}}</td>
                    <td style="border: 1px solid #0a0909; text-align: center">{{$detail->invoicett->no_tanda_terima}}</td>
                    <td style="text-align: left; display: table-cell; vertical-align: middle; border: 1px solid #0a0909; text-align: center">{{$detail->invoicett->nota}}</td>
                    <td style="text-align: right; display: table-cell; vertical-align: middle; border: 1px solid #0a0909; text-align: center" data-format="#,##0"><b>{{$detail->total}}</b></td>
                    <td style="text-align: left; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;"></td>
                    <td style="text-align: left; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;"></td>
                    <td style="text-align: left; display: table-cell; vertical-align: middle; border: 1px solid #0a0909;">{{$detail->pay_status==0?'':'Lunas'}}</td>
                </tr>
                
             
            @endforeach
            <tr class="two-strips-bottom">
                <td colspan="3" style="text-align:center;font-weight:bold; border: 1px solid #0a0909;">AMOUNT GIRO</td>
                <td style="text-align: center; font-weight:bold; border: 1px solid #0a0909;" data-format="#,##0"><b>{{$totalAmount}}</b></td> 
                <td colspan="3" style="text-align:center;font-weight:bold; border: 1px solid #0a0909;"></td>
            </tr>
            @php
                $grandTotal += $totalAmount;
            @endphp
            @if ($loop->last)
            <br>
            <br>
            <tr class="two-strips-top two-strips-bottom">
                
                <td style="text-align:center; font-weight:bold; border: 1px solid #0a0909;" colspan="3">GRAND TOTAL</td>
                <td style="text-align:center; display: table-cell; vertical-align: middle;font-weight:bold; border: 1px solid #0a0909;" data-format="#,##0"><b>{{number_format($grandTotal,0,',','.')}}</b></td>
                <td colspan="3" style="text-align:center;font-weight:bold; border: 1px solid #0a0909;"></td>
            </tr>
            @endif
        </tbody>
    </table> 
  @endforeach
  