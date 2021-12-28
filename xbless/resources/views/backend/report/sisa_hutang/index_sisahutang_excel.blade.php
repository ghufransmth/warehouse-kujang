<table style="margin-top:20px;">
    <tr>
        <td colspan='9' style="text-align: center;" ><b><h3> LAPORAN Sisa Hutang {{!empty($perusahaan) ? strtoupper($perusahaan->name) : 'Keseluruhan'}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='9' style="text-align: center;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #0a0909;">No</th>
            <th style="text-align: left; border: 1px solid #0a0909;">No Tanda Terima</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Buyer / Member</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Sales</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Total Tagihan</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Sisa Tagihan</th>
            <th style="text-align: left;border: 1px solid #0a0909;">Status</th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td style="text-align:left; border: 1px solid #0a0909;">{{$no+1}}</td>
            <td style="text-align:center; border: 1px solid #0a0909;">{{$value['tanda_terima']}}</td>
            <td style="text-align:left; border: 1px solid #0a0909;">{{$value['member']}}</td>
            <td style="text-align:left; border: 1px solid #0a0909;">{{$value['sales']}}</td>
            <td style="text-align:right; border: 1px solid #0a0909;" data-format="#,##0">{{$value['total_pembayaran']}}</td>
            <td style="text-align:right; border: 1px solid #0a0909;" data-format="#,##0">{{$value['sisa_pembayaran']}}</td>
            <td style="text-align:center; border: 1px solid #0a0909;">{!!$value['status']!!}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr >
            <td colspan="5" style="text-align: left; border: 1px solid #0a0909;">
                <b>Total Tagihan Keseluruhan</b>
            </td>
            <td  colspan="2" style="text-align: center; border: 1px solid #0a0909;" data-format="#,##0">
                <b><span id="total">{{$total_sisa_tagihan}}</span></b>
            </td>
            <td>
                
            </td>
        </tr>
    </tfoot>
</table>
<table>
    <tr>
        <td colspan='9' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


