<!DOCTYPE>
<html>
<head>
	
</head>
<html>
	<table width="100%" cellspacing="0">
		<tr>
			<td width="50%">
				<p style="margin-left:30%"><table width="100%">
				<tr>
					<td width="50%">
						<table width="100%">
								<tr>
									<td><b style="font-size: 25px;">{{strtoupper($invoice->pname)}}</b></td>
								</tr>
								<tr>
									<td align="left" style="font-size: 12px;"><p>{{ $invoice->pphone}}</p></td>
								</tr>
								<tr>
									<td align="left" style="font-size: 12px;"><p>{{$invoice->pcity}}</p></td>
								</tr>
						</table>
					</td>
				</tr>
				</table></p>
			</td>
			<td width="50%">
				<table width="100%">
				<tr>
					<td width="50%">
						<table width="75%">
								<tr>
									<td align="right"><h4>Expedisi : {{$invoice->exname}}</h4><br></td>
								</tr>
                                @if($invoice->viaid != '')
                                    <tr>
									    <td><h4>Expedisi Via : {{$invoice->vianame}}</h4><br></td>
								    </tr>
                                
                                @endif
								<tr>
									<td align="right"><h4>Colly : {{$invoice->colly}}</h4></td>
								</tr>
						</table>
					</td>
				</tr>
				</table>
			</td>
		</tr>
	</table>
    <table width="100%" cellspacing="0">
		<tr>
			<td width="50%">
				<p style="margin-left:30%"><table width="100%">
				<tr>
					<td width="50%">
						<table width="100%">
						</table>
					</td>
				</tr>
				</table></p>
	        		</td>
	        		<td width="50%">
                    <table >
	        	<tr>
	        		<td></td>
	        		<td>Kepada Yth :</td>
	        	</tr>
	        	<tr>
	        		<td></td>
	        		<td><h3>{{ strtoupper($invoice->mname) }}</h3></td>
	        	</tr>
	        	<tr>
	        		<td></td>
	        		<td><p style="font-size: 20px;">{{ strtoupper($invoice->malamat) }}</p></td>
	        	</tr>
	        	<tr>
	        		<td></td>
	        		<td><p style="font-size: 20px;">{{ strtoupper($invoice->mcity) }}</p></td>
	        	</tr>
	        	<tr>
	        		<td></td>
	        		<td><p style="font-size: 20px;">+62 {{$invoice->area_code}} {{$invoice->mphone}}<br>{{$invoice->provinsi}} - {{strtoupper($invoice->negara)}}</p></td>
	        	</tr>
	        </table>
			</td>
		</tr>
	</table>
</body>
</html>
<script>
    window.print()
</script>