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
			<p style="font-size:24px;" align="right">Data Pengiriman</p>
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
		@php
            $grandTotal = 0;
        @endphp
        @foreach($detail_tanda_terima as $key => $value)
            @php
                $grandTotal += $value->nilai_pengiriman;
            @endphp
			<tr>
				<td style='font-size:10px;'>{{ $value->no }}</td>
				<td style='font-size:10px;'>{{ $value->pertanggal }}</td>
				<td style='font-size:10px;'>{{ $value->no_nota }}</td>
				<td align='right' style='font-size:10px;'>Rp. {{ number_format($value->nilai_pengiriman, 0, '', '.').',00' }}</td>
				<td align='right' style='font-size:10px;'>Rp. {{ number_format($value->nilai_pengiriman, 0, '', '.').',00' }}</td>
				<td style='font-size:10px;'>Expedisi(No. Resi) : {{ $value->expedisi }}({{$value->resi_no}})<br>
                @if($value->resi_no == '' || $value->resi_no==null)
                    Tanggal Kirim : Pesanan Belum Terkirim
                @else
                    Tanggal Kirim : {{ $value->delivery_date }}
                @endif
                </td>
			</tr>
		@endforeach
		<tr>
			<td><p style="font-size:14px;"><b>TOTAL PEMBAYARAN</b></p></td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
			<td align="right"><p style="font-size:14px;"><b>Rp. {{ number_format($grandTotal, 0, '', '.').',00' }}</b></p></td>
		</tr>
	</tbody>
</table>
<table>
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>Diterima Oleh</td>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>{{$tanda_terima->member_name}}</td>
	</tr>
</table>
<table>
	<tr>
		<td>Memo :</td>
		<td></td>
	</tr>
    {{-- <tr>
        <td>Nama</td>
        <td>{{ $tanda_terima->getPerusahaan->name }}</td>
    </tr>
    <tr>
        <td>A/C</td>
        <td align='left'>{{ $tanda_terima->getPerusahaan->rek_no }}</td>
    </tr>
    <tr>
        <td>Bank</td>
        <td>{{ $tanda_terima->getPerusahaan->bank_name }}</td>
    </tr>
	<tr>
		<td>Note</td>
		<td colspan="5">PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{ $tanda_terima->perusahaan_name }} <br> PEMBAYARAN DAPAT DITRANSFER KE A/C {{ $tanda_terima->getPerusahaan->rek_no }} A/N {{ $tanda_terima->perusahaan_name }}</td>
	</tr> --}}
</table>
