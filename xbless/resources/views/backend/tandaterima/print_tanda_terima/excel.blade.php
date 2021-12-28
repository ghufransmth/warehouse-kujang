<table>
	<tr>
		<td>
			<p style="font-size:24px;">{{$tanda_terima->perusahaan_name}}</p>
		</td>
		<td></td>
		<td align="center" colspan="2">
			<p style="font-size:18px;">{{ $tanda_terima->no_tanda_terima }}</p>
		</td>
		<td></td>
		<td>
			<p style="font-size:24px;" align="right">Tanda Terima</p>
		</td>
	</tr>
</table>
<table>
	<tr>
		<th>Nama Customer :</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th>{{ $tanggal }}</th>
	</tr>
	<tr>
		<td>{{ $tanda_terima->member_name }}</td>
	</tr>
	<tr>
		<td>{{ $tanda_terima->member_toko }}</td>
	</tr>
	<tr>
		<td>{{ $tanda_terima->member_kota }}</td>
	</tr>
</table>
<table>
	<thead>
		<tr>
			<th>No</th>
			<th>Tertanggal</th>
			<th>No Invoice</th>
			<th>Nilai</th>
			<th>Jumlah</th>
			<th>Keterangan</th>
		</tr>
	</thead>
	<tbody>
		@foreach($detail_tanda_terima as $key => $value)
			<tr>
				<td style='font-size:11px;'>{{ $value->no }}</td>
				<td style='font-size:11px;'>{{ $value->pertanggal }}</td>
				<td style='font-size:11px;'>{{ $value->no_nota }}</td>
				<td align='right' style='font-size:11px;'>Rp. {{ number_format($value->nilai, 0, '', '.').',00' }}</td>
				<td align='right' style='font-size:11px;'>Rp. {{ number_format($value->nilai, 0, '', '.').',00' }}</td>
				<td></td>
			</tr>
		@endforeach
		<tr>
			<td><p style="font-size:14px;"><b>TOTAL PEMBAYARAN</b></p></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td align="right"><p style="font-size:14px;"><b>Rp. {{ number_format($tanda_terima->grandtotal, 0, '', '.').',00' }}</b></p></td>
		</tr>
	</tbody>
</table>
<table>
	<thead>
		<tr>
			<th>Nama</th>
			<th>{{ $tanda_terima->getPerusahaan->name }}</th>
            <th></th>
            <th></th>
            <th></th>
            <th>Diterima Oleh</th>
        </tr>
		<tr>
			<th>A/C</th>
            <th align='left'>{{ $tanda_terima->getPerusahaan->rek_no }}</th>
            <th></th>
            <th></th>
            <th></th>
            <th>{{$tanda_terima->member_name}}</th>
        <tr>
            <th>Bank</th>
            <th>{{ $tanda_terima->getPerusahaan->bank_name }}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
	</thead>
</table>
<table>
	<tr>
		<td>Memo :</td>
		<td></td>
	</tr>
	<tr>
		<td>NOTE :</td>
		<td colspan="5">PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{ $tanda_terima->perusahaan_name }}</td>
	</tr>
	<tr>
		<td></td>
		<td colspan="5">PEMBAYARAN DAPAT DITRANSFER KE A/C {{ $tanda_terima->perusahaan_rekno }} A/N {{ $tanda_terima->perusahaan_name }}</td>
	</tr>
</table>
