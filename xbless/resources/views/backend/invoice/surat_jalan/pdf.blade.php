<!DOCTYPE>
<html>
<head>
	<title>{{$title}}</title>
</head>

<body>
	<p align="center"><font size="12px"><b><U>SURAT JALAN</font></U></b>

	<br>{{$invoice->no_nota}}
	</p>
	<table width="100%">
		<tr>
			<td width="50%">
			<table width="100%">
				<tr>
					<td><b>Nama  </b></td>
					<td><b>:{{$invoice->mname}}</b></td>
				</tr>
				<tr>
					<td valign="top"><b>Alamat  </b></td>
					<td><b>:{{$invoice->malamat}}</td>
				</tr>
				<tr>
					<td></td>
					<td><b>{{$invoice->mcity}}</td>
				</tr>
			</table>
			</td>
			<td width="50%" align="right">
			<table width="100%">
				<tr>
					<td align="right"></td>
					<td align="right">{{$tanggal}}</td>
				</tr>
				<tr>
					<td align="right">Expedisi </td>
					<td align="right">{{$invoice->exname}}</td>
				</tr>
				<tr>
					<td align="right">Via Expedisi </td>
					<td align="right">{{$invoice->vianame}}</td>
				</tr>
			</table>
			</td>
		</tr>
	</table>
	    <table width="100%" border="1" cellspacing="0">
	            <tr>
	                <th align="center">Qty</th>
	                <th>Keterangan</th>
	                <th align="center">Product</th>
            	</tr>
	        <tbody>
	        	@foreach($invoicedetail as $key => $value)
                    <tr>
                        <td align="center">{{$value->qty}} {{$value->satuan}}</td>
						<td>{{$value->deskripsi}}</td>
                        <td align="center">{{$value->product_name}}</td>
                    </tr>
                @endforeach
	        </tbody>
	    </table>
	    <table border='0' width='100%'>
	    	<tr>
	    		<td>
	    			<table>
	    				<tr>
	    					<td><p><b>Diterima Oleh</b></p></td>
	    				</tr>
	    				<tr>
	    					<td><p><b>{{strtoupper($invoice->mname)}}</b></p></td>
	    				</tr>
	    			</table>
	    		</td>
	    		<td align='right'>
	    			<table>
	    				<tr>
	    					<td align='right'><p><b>Hormat Kami</b></p></td>
	    				</tr>
	    				<tr>
	    					<td align='right'><p><b>{{strtoupper($invoice->pname)}}</b></p></td>
	    				</tr>
	    			</table>
	    		</td>
	    	</tr>
	    </table>
</body>
</html>
