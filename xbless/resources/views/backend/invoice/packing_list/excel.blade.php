<table>
    <tr>
    	<td colspan="5" align="center"><b>{{ strtoupper($invoice->pname) }}</b></td>
    </tr>
    <tr>
        <td colspan="5" align="center"><b>PACKING LIST</b></td>
    </tr>
</table>
<table>
    <tr>
		<td><b>Kepada Yth :</b></td>
	</tr>
	<tr>
		<td><b>{{strtoupper($invoice->mname)}}</b></td>
	</tr>
	<tr>
		<td><b>{{strtoupper($invoice->malamat)}}</b></td>
        <td></td>
        <td></td>
        <td></td>
        <td><b>Expedisi : {{ $invoice->exname }}</b></td>
	</tr>
	<tr>
		<td><b>{{ strtoupper($invoice->mcity) }}</b></td>
        <td></td>
        <td></td>
        <td></td>
        <td><b>Via Expedisi : {{ $invoice->vianame }}</b></td>
	</tr>
</table>
<table>
    <thead>
        <tr>
            <th align="center">Colly</th>
            <th align="center">Weight</th>
            <th align="center">Quantity</th>
            <th align="center">Description</th>
            <th align="center">Product</th>
    	</tr>
    </thead>
    <tbody>
        @foreach($invoicedetail as $key => $value)
            <tr>
                <td align="center">{{$value->colly}} - {{$value->colly_to}}</td>
                <td align="center">{{$value->weight}} Kg</td>
                <td align="center">{{$value->qty}} {{$value->satuan}}</td>
                <td align="center">{{$value->product_desc}}</td>
                <td align="center">{{$value->product_name}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan="5" align="left">Print By : {{$invoice->print_by}}, {{$tanggalskrg}}</td>
    </tr>
    <tr>
        <td colspan="5" align="right">Jakarta, {{$tanggal}}</td>
    </tr>
</table>
