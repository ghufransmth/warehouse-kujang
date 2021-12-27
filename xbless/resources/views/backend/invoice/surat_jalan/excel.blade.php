<table>
    <tr>
        <td colspan="5" align="center"><b><U>SURAT JALAN</U></b></td>
    </tr>
    <tr>
        <td colspan="5" align="center"><b><U>{{$invoice->no_nota}}</U></b></td>
    </tr>
</table>
<table>
    <tr>
		<td><b>Nama  </b></td>
		<td><b>:{{strtoupper($invoice->mname)}}</b></td>
        <td></td>
        <td>{{$tanggal}}</td>
	</tr>
	<tr>
		<td><b>Alamat  </b></td>
		<td><b>:{{$invoice->malamat}}</b></td>
        <td></td>
        <td>Expedisi </td>
        <td>{{$invoice->exname}}</td>
	</tr>
	<tr>
		<td></td>
		<td><b>{{$invoice->mcity}}</b></td>
        <td></td>
        <td>Via Expedisi</td>
        <td>{{$invoice->vianame}}</td>
	</tr>
</table>
<table>
        <tr>
            <th></th>
            <th align="center">Qty</th>
            <th align="center">Keterangan</th>
            <th align="center">Product</th>
            <th></th>
    	</tr>
    <tbody>
    	@foreach($invoicedetail as $key => $value)
            <tr>
                <td></td>
                <td align="center">{{$value->qty}} {{$value->satuan}}</td>
                <td align="center">{{$value->deskripsi}}</td>
                <td align="center">{{$value->product_name}}</td>
                <td></td>
            </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
    	<td><p><b>Diterima Oleh</b></p></td>
        <td></td>
        <td></td>
        <td></td>
        <td><p><b>Hormat Kami</b></p></td>
    </tr>
    <tr>
    	<td><p><b>{{strtoupper($invoice->mname)}}</b></p></td>
        <td></td>
        <td></td>
        <td></td>
        <td><p><b>{{strtoupper($invoice->pname)}}</b></p></td>
    </tr>
</table>
