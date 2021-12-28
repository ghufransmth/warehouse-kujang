<!DOCTYPE>
<html>
	<head>
		<title>{{ $title }}</title>
        <style>
            hr	{
				border-bottom: 2px solid black;
				border-top: 0;
			}
        </style>
	</head>
	<body>
		<hr style="margin-bottom:0.3mm">
		<table width="100%" cellspacing="0">
			<tr>
				<td align="left">
					<p style="font-size:22px;">{{ $tanda_terima->perusahaan_name }}</p>
				</td>
				<td align="center">
					<p style="font-size:20px;">{{ $tanda_terima->no_tanda_terima }}</p>
				</td>
				<td align="right">
					<table>
						<tr>
							<td align="right">
								<p style="font-size:22px;">Data Pengiriman</p>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		<hr style="margin-bottom:0.3mm">
		<table border="0" width="100%">
			<tr>
				<th align="left">
					<pstyle="font-size:14px;">Nama Customer :</p>
				</th>
				<th>
					<p align="right"; style="font-size:15px;">{{ $tanggal }}</p>
				</th>
			</tr>
			<tr>
				<td>
					<p style="font-size:14px;">{{ $tanda_terima->member_name }}</p>
				</td>
			</tr>
			<tr>
				<td>
					<p style="font-size:14px;">{{ $tanda_terima->member_toko }}</p>
				</td>
			</tr>
			<tr>
				<td>
					<p style="font-size:14px;">{{ $tanda_terima->member_kota }}</p>
				</td>
			</tr>
		</table>
		<div class="table-responsive">
			<table width="100%" border="1" cellspacing="0">
				<thead>
					<tr>
						<th style="text-align: center;">No</th>
						<th style="text-align: center;">Tertanggal</th>
						<th style="text-align: center;" class="col-sm-1">No Invoice</th>
						<th style="width: 100px;">Nilai</th>
						<th style="width: 100px;">Jumlah</th>
						<th class="col-sm-1">Keterangan</th>
					</tr>
				</thead>
				<tbody>
                    @php
                        $grandTotal = 0;
                        // dd($detail_tanda_terima);
                    @endphp
					@foreach($detail_tanda_terima as $key => $value)
                        @php
                            $grandTotal += $value->nilai_pengiriman;
                        @endphp
						<tr>
							<td style='font-size:13px; text-align: center;'>{{ $value->no }}</td>
							<td style='font-size:13px; text-align: center;'>{{ $value->pertanggal }}</td>
							<td style='font-size:13px; text-align: center;'>{{ $value->no_nota }}</td>
							<td align='right' style='font-size:13px;'>Rp. {{ number_format($value->nilai_pengiriman, 0, '', '.').'' }}</td>
							<td align='right' style='font-size:13px;'>Rp. {{ number_format($value->nilai_pengiriman, 0, '', '.').'' }}</td>
							<td style='font-size:13px;'>Expedisi(No. Resi) : {{ $value->expedisi }}({{$value->resi_no}})<br>
                            @if($value->resi_no == '' || $value->resi_no==null)
                                Tanggal Kirim : Pesanan Belum Terkirim
                            @else
                                Tanggal Kirim : {{ $value->delivery_date }}
                            @endif
                            </td>
						</tr>
					@endforeach
					<tr height="25px">
						<td rowspan="6" colspan="5"><p style="font-size:14px;"><b>TOTAL PEMBAYARAN</b></p></td>
						<td align="right" style="height: 15px;"><p style="font-size:14px;"><b>Rp. {{ number_format($grandTotal, 0, '', '.').'' }}</b></p></td>
					</tr>
				</tbody>
			</table>
			<table border="0" width="100%">
				<tr>
					<td align="right" width="253px">
						<table width="253px">
							<tr>
								<td align="center">
									<p style="font-size:12px;">Diterima Oleh</p>
								</td>
							</tr>
							<tr>
								<td align="center"><h5>{{ $tanda_terima->member_name }}</h5><br><br><br></td>
							</tr>
							<tr>
								<td align="center"><p style="margin-top:15px"> .......................................................... </p></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<table border="0">
				<tr>
					<td>
						<p>Memo : </p>
					</td>
					<td></td>
				</tr>
                {{-- <table style="border: 1px solid black; padding:10px;">
                    <tr>
                        <td>Nama</td>
                        <td>: {{ $tanda_terima->getPerusahaan->name }}</td>
                    </tr>
                    <tr>
                        <td>A/C</td>
                        <td>: {{ $tanda_terima->getPerusahaan->rek_no }}</td>
                    </tr>
                    <tr>
                        <td>Bank</td>
                        <td>: {{ $tanda_terima->getPerusahaan->bank_name }}</td>
                    </tr>
                </table>
				<tr>
					<td>
						<p style="margin-bottom: 0px;">NOTE : </p>
					</td>
					<td>
						<b><p style="margin-top: 0px;">PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{ $tanda_terima->perusahaan_name }} <br> PEMBAYARAN DAPAT DITRANSFER KE A/C {{ $tanda_terima->getPerusahaan->rek_no }} A/N {{ $tanda_terima->perusahaan_name }}</p></b>
					</td>
				</tr> --}}
			</table>
		</div>
	</body>
</html>
<script>
    window.print()
</script>
