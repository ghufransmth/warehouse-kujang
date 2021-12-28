<table>
    <tr>
    	<td><b>{{strtoupper($invoice->pname)}}</b></td>
        <td></td>
        <td></td>
        <td></td>
        <td>Expedisi : {{$invoice->exname}}</td>
    </tr>
    <tr>
    	<td><p>{{ $invoice->pphone}}</p></td>
        <td></td>
        <td></td>
        <td></td>
        @if($invoice->viaid != '')
        <td>Expedisi Via : {{$invoice->vianame}}</td>
    @endif
    </tr>
    <tr>
        <td><p>{{$invoice->pcity}}</p></td>
        <td></td>
        <td></td>
        <td></td>
        <td>Colly : {{$invoice->colly}}</td>
</table>
<table>
    <tr>
    	<td></td>
        <td></td>
        <td></td>
        <td></td>
    	<td>Kepada Yth :</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    	<td><h3>{{ strtoupper($invoice->mname) }}</h3></td>
    </tr>
    <tr>
    	<td></td>
        <td></td>
        <td></td>
        <td></td>
    	<td><p>{{ strtoupper($invoice->malamat) }}</p></td>
    </tr>
    <tr>
    	<td></td>
        <td></td>
        <td></td>
        <td></td>
    	<td><p>{{ strtoupper($invoice->mcity) }}</p></td>
    </tr>
    <tr>
    	<td></td>
        <td></td>
        <td></td>
        <td></td>
    	<td><p>+62 {{$invoice->area_code}} {{$invoice->mphone}}</p></td>
    </tr>
</table>