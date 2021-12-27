<table style="margin-top:20px;">
    <tr>
        <td colspan='9' style="text-align: center;border: 1px solid #f5f2f2" ><b><h3> LAPORAN SO ({{strtoupper($perusahaan->name)}})</h3> </b></td>
    </tr>
    <tr>
        <td colspan='9' style="text-align: center;border: 1px solid #f5f2f2" ><b><h4>{{strtoupper(date('d M Y',strtotime($tgl)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
           
            <th style="border: 1px solid #f5f2f2;" colspan="5"><b>Customer</b></th>
            <th style="border: 1px solid #f5f2f2; text-align:right" colspan="3"><b>No PO</b></th>
            <th style="border: 1px solid #f5f2f2; text-align:center"><b>Status</b></th>
        </tr>
    </thead>
    <tbody>
        <?php $grand_total = 0;?>
        @foreach($data as $key=>$value)
        <tr>
            <td style="border: 1px solid #f5f2f2;" colspan="4"><b>{{strtoupper($value->nama_member)}}</b></td>
            <td style="border: 1px solid #f5f2f2;" colspan="2"><b>{{ strtoupper($value->kota_member)}}</b></td>
            <td style="border: 1px solid #f5f2f2;"><b>Description</b></td>
            <td style="border: 1px solid #f5f2f2; text-align:right"><b>{{$value->no_nota}}</b></td>
            <td style="border: 1px solid #f5f2f2; text-align:center"><b>{{$value->status}}</b></td>
        </tr>
        <?php $total = 0;?>
        @foreach($value->detailpo as $key=>$result)
        <?php $total = $total + $result->ttl_price;?>
        <tr>
            <td style="border: 1px solid #f5f2f2;text-align: right;" colspan="2"></td>
            <td style="border: 1px solid #f5f2f2;text-align: right;" colspan="2">{{$result->qty}}</td>
            <td style="border: 1px solid #f5f2f2;text-align: left;" colspan="1">{{$result->nama_satuan}}</td>
            <td style="border: 1px solid #f5f2f2;text-align:left;">{{$result->product_name}}</td>
            <td data-format="#,##0_-"  style="border: 1px solid #f5f2f2;text-align:right;">{{round($result->price - ($result->price * ($result->discount/100)))}}</td>
            <td data-format="#,##0_-"  style="border: 1px solid #f5f2f2;text-align:right;">{{round($result->ttl_price)}}</td>
        </tr>
        @endforeach
         <tr>
            <td style="border: 1px solid #f5f2f2;text-align: right;" colspan="6"><b>Total</b></td>
            <td data-format="#,##0_-"  style="border: 1px solid #f5f2f2;text-align:right;" colspan="2"><b>{{$total}}</b></td>
        </tr>
        <?php $grand_total = $grand_total + $total;?>
        @endforeach
        <tr>
            <td style="border: 1px solid #f5f2f2;text-align: right; " colspan="5"><h4><b>GRAND TOTAL</b></h4></td>
            <td  data-format="#,##0_-"  style="border: 1px solid #f5f2f2;text-align: right;" colspan="2"><h4><b>{{round($grand_total)}}</b></h4></td>
            <td style="border: 1px solid #f5f2f2;text-align: right; "></td>
        </tr>
    </tbody>
</table>
<table>
    <tr>
        <td colspan='8' style='text-align: left; border: 1px solid #f5f2f2;' ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


