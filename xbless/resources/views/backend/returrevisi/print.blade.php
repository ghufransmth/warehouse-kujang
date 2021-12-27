<style type='text/css' media='print'>
    @page {
        size: auto;
        margin: 0;
    }
</style>

<style>

    body {
        padding-left: 1.3cm;
        padding-right: 1.3cm;
        padding-top: 0.5cm;
    }
</style>

<table width='100%'>
    @php
        if(strpos($retur_revisi_print->first()->nomor_retur_revisi, 'RET') !== false){
            $labelNo = 'Retur';
        } else{
            $labelNo = 'Revisi';
        }
    @endphp
	<tr>
		<th align='left'><h1>{{ $retur_revisi->getInvoice->getPerusahaan->name }}</h1></th>
		<th align='right'><h4>{{$labelNo}} : {{ $retur_revisi_print->first()->nomor_retur_revisi }}</h4></th>
	</tr>
</table>

<hr style='margin-top:-1%'>

<table width='100%'>
	<tr>
		<th align='left'>
            <table border = '1' cellspacing='0'>
                <tr>
                    <th>Dibuat O/ </th>
                    <th>Diterima O/ </th>
                    <th>Disetujui O/ </th>
                    <th>Dikembalikan O/ </th>
                </tr>
                <tr>
                    <td height='40'></td>
                    <td height='40'></td>
                    <td height='40'></td>
                    <td height='40'></td>
                </tr>
            </table>
		</th>
		<th align='right'>{{ date('d M y', strtotime($retur_revisi_print->first()->created_at)) }} <br> Nama Customer : {{ $retur_revisi->getInvoice->getMember->name }} <br> {{ $retur_revisi->getInvoice->getMember->city }}-{{ $retur_revisi->getInvoice->getMember->prov }} <br> Ex_Inv : {{ $retur_revisi->getInvoice->no_nota }}</th>
	</tr>
</table>

<br>

<table border='1' width='100%' cellspacing='0'>
	<tr>
		<th>Product</th>
		{{-- <th>Keterangan</th> --}}
		<th>Qty</th>
		<th>Harga/Unit</th>
		<th>Jumlah</th>
	</tr>
    @php
        $total = 0;
        // foreach ($retur_revisi_print as $key => $value) {
        //     $total = $total + ($value->qty_change*$value->price_change);
        // }
    @endphp
    @foreach ($retur_revisi_print as $item)
        @php
            if ($item->qty_change == $item->qty_before) {
                $itemQty = $item->qty_change;
            } else {
                $itemQty = abs($item->qty_change-$item->qty_before);
            }

            if ($item->price_change == $item->price_before) {
                $itemPrice = $item->price_change;
            } else {
                $itemPrice = abs($item->price_change-$item->price_before);
            }

            $diskon = $item->getInvoice->discount;
            $total = $total + ($itemQty*$itemPrice);
        @endphp
        <tr>
            <td style='font-size: 13px;'>{{ $item->getInvoiceDetail->product_name }}</td>
            {{-- <td style='font-size: 13px;'>-</td> --}}
            <td style='font-size: 13px;' align='center'>
               {{ $itemQty }} {{ $item->getInvoiceDetail->product->getsatuan->name }}
            </td>
            <td align='right' style='font-size: 13px;'>
                ({{ number_format($itemPrice) }})
            </td>
            <td align='right' style='font-size: 13px;'>
                ({{ number_format($itemPrice*$itemQty) }})
            </td>
        </tr>
    @endforeach
</table>

<table border='0' width='100%'>
	<tr>
		<td align='right'>
			<p style='font-size: 13px;'><b>SUBTOTAL : {{ number_format($total) }}</b></p>
		</td>
	</tr>
	<tr>
		<td align='right'>
			<p style='font-size: 13px;'><b>DiSKON {{ $diskon == null ? 0 : $diskon }} % : {{ number_format(round($total*$diskon/100)) }}</b></p>
		</td>
	</tr>
    <tr>
		<td align='right'>
			<p style='font-size: 13px;'><b>TOTAL SETELAH DISKON : {{ number_format($total-($total*$diskon/100)) }}</b></p>
		</td>
	</tr>
	<tr>
		<td align='right'>
			<p style='font-size: 13px;'><b>PPN 10 % : {{ number_format(round(($total-($total*$diskon/100)) * 10 / 100)) }}</b></p>
		</td>
	</tr>
	<tr>
		<td align='right'>
			<p style='font-size: 13px;'><b>GRAND TOTAL : Rp. {{ number_format(round(($total-($total*$diskon/100))+(($total-($total*$diskon/100))) * 10 / 100)) }}</b></p>
		</td>
	</tr>
</table>

	Note : {{ $retur_revisi_print->first()->note }}<br>
	</p>
	<br>
	<table border='1' cellspacing='0' width='50%'>
		<tr>
			<td height='40'></td>
			<td height='40'></td>
			<td height='40'></td>
		</tr>
		<tr>
			<td align='left'>Sales :</td>
			<td align='left'>Officer :</td>
			<td align='left'>Mengetahui : </td>
		</tr>
	</table>
	<p align='right' style='font-size: 12px;'>Print By : {{ auth()->user()->username }}, {{ date('d M y H:i:s') }}

<script>
	window.print();
    // window.onafterprint = function(event) {
    //     document.location.href = "{{ URL::previous() }}";
    // };
</script>
