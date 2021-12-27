<!DOCTYPE>
<html>
<body>
    <hr style="margin-bottom:0.3mm">
	<table width="100%" cellspacing="0">
	    <tr>
	    	<td width="50%">
	    	<b style="margin-top:0.3mm; font-size: 25px;">{{ strtoupper($invoice->pname) }}</b>
	    	</td>
	    	<td width="50%">
	    	<b style="margin-top:0.3mm; font-size: 21px;">PACKING LIST</b>
	    	</td>
	</tr>
	</table>
	<hr style="margin-top:1px">
	<table width="100%" cellspacing="0">
		<tr>
			<td width="50%" style="height: 2px; vertical-align: top;">
				<table width="100%">
				<tr>
					<td width="50%" style="height: 2px; vertical-align: top;">
						<table width="100%">
								<tr>
									<td style="font-size: 12px; vertical-align: top;"><b>Kepada Yth :</b></td>
								</tr>
								<tr>
									<td style="font-size: 12px; vertical-align: top;"><b>{{strtoupper($invoice->mname)}}</b></td>
								</tr>
								<tr>
									<td style="font-size: 12px; vertical-align: top;"><b>{{strtoupper($invoice->malamat)}}</td>
								</tr>
								<tr>
									<td style="font-size: 12px; vertical-align: top;"><b>{{ strtoupper($invoice->mcity) }}</b></td>
								</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
			<td width="50%" style="padding-left: 100px">
				<table  border="0" >
				<tr>
                	<td>
                    	<b style="font-size: 14px;">Expedisi : {{ $invoice->exname }}</b>
                    </td>
              	</tr>
                <tr>
                    <td>
                      	<b style="font-size: 14px;">Via Expedisi : {{ $invoice->vianame }}</b>
                   	</td>
				</tr>
				</table>
			</td>
		</tr>
    </table>
    <div class="table-responsive">
	    <table width="100%" border="1" cellspacing="0" style="margin-top:4px">
	        <thead>
	            <tr>
	                <th style="height: 25px; font-size: 14px">Colly</th>
	                <th style="height: 25px; font-size: 14px">Weight</th>
	                <th style="height: 25px; font-size: 14px">Quantity</th>
	                <th style="height: 25px; font-size: 14px" class="col-sm-1">Description</th>
	                <th style="height: 25px; font-size: 14px" class="col-sm-1">Product</th>
            	</tr>
	        </thead>
	        <tbody>
                @foreach($invoicedetail as $key => $value)
                    <tr>
                        <td style="text-align: center">{{$value->colly}} - {{$value->colly_to}}</td>
                        <td style="text-align: center">{{$value->weight}} Kg</td>
                        <td style="text-align: center">{{$value->qty}} {{$value->satuan}}</td>
                        <td>{{$value->product_desc}}</td>
                        <td>{{$value->product_name}}</td>
                    </tr>
                @endforeach
	        </tbody>
	    </table>
	    <br><br><br><br><br><br>
		<p style="font-size:10px">Print By : {{$invoice->print_by}}, {{$tanggalskrg}}</p>
	    <p style="font-size:12px; margin-top:0.3mm; padding-left:78%">Jakarta, {{$tanggal}}</p>
	</div>
</body>

</html>
<script>
    window.print()
</script>
