
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Preview data</title>
</head>
<style>
	#tableList tr th {
		border: 1px solid #eaeaea;
		padding: 5px 8px;
	}

	#tableList tr td {
		/* border: 1px solid #eaeaea; */
		padding: 2px 8px;
	}
    .text-right{
        text-align: right;
    }
    .text-center{
        text-align: center;
    }
    .text-left{
        text-align: left;
    }
	* {
		font-family: 'Courier New', Courier, monospace;
	}
</style>
<body>
<table style="width: 100%;border-collapse: collapse;">
	<tr>
		<td style="width: 70%;">
			<h5>CV KUJANG MARINAS UTAMA</h5>
			<span style="font-size: 10px;">KP. CIKAROYA RT 010 RW 003 KECAMATAN CISAAT SUKABUMI DS. GUNUNG JAYA, KEC
                    CISAAT,
                    KAB
                    SUKABUMI</span>
			<div style="width: 170px;">
				<ul style="font-size: 10px;padding-left: 0px;margin: 0px;">
					<li style="list-style: none;">No. Telpon : <span style="float: right;">0266216166</span></li>
				</ul>
				<ul style="font-size: 10px;padding-left: 0px;margin: 0px;">
					<li style="list-style: none;">NPWP : <span style="float: right;">03.323.032-7.405.000</span>
					</li>
				</ul>
			</div>
		</td>
	</tr>
</table>
<table style="width: 100%;border-collapse: collapse;font-size: 10px;">
	<tr>

		<td>
			<table style="width: 70%;border-collapse: collapse;font-size: 10px;">
				<tr>
					<td>Salesman</td>
					<td>:</td>
					<td>{{$deliveryorder->getsales?$deliveryorder->getsales->nama:'-'}}</td>
				</tr>

				<tr>
					<td>Driver</td>
					<td>:</td>
					<td>{{$deliveryorder->getDriver?$deliveryorder->getDriver->nama:'-'}}</td>
				</tr>

			</table>
		</td>

		<td>
			<span>Kepada YTH.</span><br>
			<span>{{$deliveryorder->gettoko?$deliveryorder->gettoko->code:'-'}}/{{$deliveryorder->gettoko?$deliveryorder->gettoko->name:'-'}}</span><br>
			<span>{{$deliveryorder->gettoko?$deliveryorder->gettoko->alamat:'-'}}</span>
		</td>


		<td>
			<table style="width: 80%;border-collapse: collapse;font-size: 10px;">
				<tr>
					<td>No. Faktur</td>
					<td>:</td>
					<td>{{$deliveryorder->no_faktur}}</td>
				</tr>
				<tr>
					<td>Tgl. Faktur</td>
					<td>:</td>
					<td>{{date("d/m/Y",strtotime($deliveryorder->tgl_faktur))}}</td>
				</tr>
				<tr>
					<td>Tgl. JTempo</td>
					<td>:</td>
					<td>{{$deliveryorder->tgl_jatuh_tempo==null?'-':date("d/m/Y",strtotime($deliveryorder->tgl_jatuh_tempo))}}</td>
				</tr>
				<tr>
					<td>Jenis Bayar</td>
					<td>:</td>
					<td>$0</td>
				</tr>
			</table>
		</td>

	</tr>
</table>

<table style="width: 100%; border-collapse: collapse;padding: 5px;font-size: 10px;" id="tableList">
	<thead>
	<tr>
		<th>PCODE</th>
		<th>Nama Barang</th>
		<th style="text-align: right;">Harga/LSN</th>
		<th style="text-align: center;">KRT.LSN.SAT</th>
		<th style="text-align: right;">Jumlah Rp</th>
		<th>Ket.</th>
	</tr>
	</thead>
	<tbody>
        {!! $detail[0] !!}
	</tbody>
</table>
<div style="border-top: 1px solid #eaeaea;margin-top: 10px;margin-bottom: 10px;"></div>
<table style="width: 100%; font-size: 11px;">
	<tbody>
	<tr>
		<td>Total Karton Utuh : 0</td>
		<td style="text-align: right;">Jumlah Rp</td>
		<td style="text-align: right;width: 100px;">{!! $detail[4] !!}</td>
		<td style="width: 60px;"></td>
	</tr>
		</tbody>
</table>

<div style="border-top: 1px solid #eaeaea;margin-top: 10px;margin-bottom: 10px;"></div>

<table style="width: 100%; font-size: 11px;">
	<tbody>
	<tr>
		<td style="text-align: right;">Discount Rp</td>
		<td style="text-align: right;width: 100px;">- {!! $detail[3] !!}</td>
		{{-- <td style="width: 60px;">- {!! $detail[3] !!}</td> --}}
	</tr>
	<tr>
		<td style="text-align: right;">Nilai Faktur Rp</td>
		<td style="text-align: right;width: 100px;">{!! $detail[1] !!}		</td>
		<td style="width: 60px;"></td>
	</tr>
	</tbody>

</table>

<div style="margin-top: 20px;">
	<p style="font-size: 15px;text-transform: uppercase;">TERBILANG :
		{!! $detail[2] !!} </p>
	<p style="font-size: 11px;">* {!! $deliveryorder->note !!} </p>
</div>


<script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>

<script>
	$(document).ready(function () {


		window.onload = function () {
			window.print();
		}

		window.onmousemove = function () {
			window.close();
		}
	})
</script>
</body>

</html>
