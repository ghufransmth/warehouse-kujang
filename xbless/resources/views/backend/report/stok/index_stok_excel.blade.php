
<table>
    <thead>
        <tr>
            <td colspan='9' style="text-align: center; border: 1px solid #0a0909;"><b><h3>  History STOK {{!empty($perusahaan) ? strtoupper($perusahaan->name) ?? '-' : 'Keseluruhan'}}</h3></b></td>
        </tr>
        <tr>
            <td colspan='9' style="text-align: center; border: 1px solid #0a0909;"><b><h4> PERIODE TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b></td>
        </tr>
        <tr>
            <th style="border: 1px solid #0a0909;"><b>No</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>No Invoice</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>No Transaksi</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Tanggal</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Stok Code</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Gudang</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Keterangan</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Qty</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>Satuan</b></th>
        </tr> 
    </thead>
    <tbody>
        @php
            $temp = 0;
            $satuan;
        @endphp
        @foreach($data as $key=>$value)
            @php
                $temp += $value->qty;
                $satuan = $value->satuan;
            @endphp
            <tr>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$key+1}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->no_invoice}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->no_transaction}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->dateorder}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{!!$value->product_code!!}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->gudang_name}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->ket}}</td>
                <td style="text-align: right; border: 1px solid #0a0909;" data-format="#,##0">{{$value->qty}}</td>
                <td style="text-align: center; border: 1px solid #0a0909;">{{$value->satuan}}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr >
            <td colspan="7" style="text-align: center; border: 1px solid #0a0909;">
               <b>Stok Periode {{!empty($product) ? strtoupper($product->product_name) : ''}} {{date('d M Y', strtotime($filter_tgl_start))}} - {{date('d M Y', strtotime($filter_tgl_end))}}</b>
            </td>
            <td colspan="1" style="text-align: center; border: 1px solid #0a0909;" data-format="#,##0">
                 <b>{{$sum_qty}}</b>
            </td>
            <td colspan="1" style="text-align: center; border: 1px solid #0a0909;">
                 <b>{{$satuan}}</b>
            </td>
        </tr>
        <tr>
            <td colspan="9" style="text-align: center; border: 1px solid #0a0909;">
                <b>SUMMARY</b>
               
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right; border: 1px solid #0a0909;">
                <b> STOCK CUT OFF {{!empty($product) ? strtoupper($product->product_name) : ''}} SAMPAI TANGGAL {{date('d M Y', strtotime($filter_tgl_start . '-1 days'))}}</b>
            </td>
            <td colspan="1" style="text-align: right; border: 1px solid #0a0909;" data-format="#,##0">
               <b>{{$cut_off_qty}}</b>
            </td>
            <td colspan="1" style="text-align: center; border: 1px solid #0a0909;">
               <b>{{$cut_off_qty > 0 ? $satuan : '-'}}</b>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right; border: 1px solid #0a0909;">
               <b>Stok {{!empty($product) ? strtoupper($product->product_name) : ''}} Periode {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b>
            </td>
            <td colspan="1" style="text-align: right; border: 1px solid #0a0909;" data-format="#,##0">
                <b>{{$sum_qty}}</b>
            </td>
            <td colspan="1" style="text-align: center; border: 1px solid #0a0909;">
                <b>{{$satuan}}</b>
            </td>
        </tr>
        <tr>
            <td colspan="7" style="text-align: right; border: 1px solid #0a0909;">
               <b>Total Stok {{!empty($product) ? strtoupper($product->product_name) : ''}} Periode {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b>
            </td>
            <td colspan="1" style="text-align: right; border: 1px solid #0a0909;" data-format="#,##0">
               <b>{{$sum_qty + $cut_off_qty}}</b>
            </td>
            <td colspan="1" style="text-align: center; border: 1px solid #0a0909;">
               <b>{{$satuan}}</b>
            </td>
        </tr>
    </tfoot>
</table>
<table>
    <tr>
        <td colspan='8' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


