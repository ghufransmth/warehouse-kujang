<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
    <table style="margin-top:20px;">
        <tr>
            <td colspan='8' style="text-align: center;" ><b><h3> REKAP INVOICE {{!empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</h3> </b></td>
        </tr>
        <tr>
            <td colspan='8' style="text-align: center;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
        </td>
        </tr>
    </table>
    <table class="table" border="1">
        <thead>
            <tr>
                <th style="border: 1px solid #0a0909; font-weight:bold">No</th>
                <th style="border: 1px solid #0a0909; text-align: center;  font-weight:bold">Tgl </th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">No. PO </th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">No Invoice</th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">Customer</th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">Kota</th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">Ekspedisi</th>
                <th style="border: 1px solid #0a0909; text-align: center; font-weight:bold">Tot Inv (PPN) (Rp.)</th>
            </tr> 
        </thead>
        <tbody>
            @php
                $sum = 0;
            @endphp
            @foreach ($data as $no => $value)
            
            <tr>
                <td style="border: 1px solid #0a0909;text-align: center;" valign="top" >{{$no + 1}}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{!! $value->tgl !!}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{{$value->nopo}}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{{$value->nonota}}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{{$value->member_name}}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{{$value->member_kota}}</td>
                <td style="border: 1px solid #0a0909; text-align: left;">{{$value->nama_expedisi}}</td>
                <td style="border: 1px solid #0a0909; text-align: right;" data-format="#,##0">{{$value->totalppn}}</td>
            </tr>
            @php
                $sum += $value->total;
            @endphp
            @endforeach
        </tbody>
    </table>
    <table class="table" border="1">
        <tr>
            <td style="border: 0.5px solid #0a0909;font-size:15px; font-weight:bold" colspan="6">Grand Total</td>
            <td colspan="2" style="border: 0.5px solid #0a0909;font-weight:bold;text-align:right" data-format="#,##0">{{$sum}}</td>
        </tr>
    </table>
    <table>
        <tr>
            <td colspan='8' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
        </tr>
    </table>
</body>
</html>



