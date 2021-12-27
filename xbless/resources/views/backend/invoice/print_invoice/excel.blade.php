<table>
     {{-- BERKAH JAYA --}}
     @if($kodeperusahaan=='CV.BJ')
        <tr>
            <td><b>{{strtoupper($invoice->pname)}}</b></td>
            <td colspan="3"><b>The Specialist For Engine Parts</b></td>
            <td><b>{{$invoice->no_nota}}</b></td>
        </tr>
    {{-- CANDRA JAYA --}}
    @elseif($kodeperusahaan=='PT.CJ')
        <tr>
            <td rowspan="2" colspan="2" valign="center"><b>{{strtoupper($invoice->pname)}}</b></td>
            <td colspan="3"><b>Faktur No: {{$invoice->no_nota}}</b></td>
        </tr>
        <tr>
            <td colspan="3"><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
        </tr>
    {{-- ERTRACO --}}
    @elseif($kodeperusahaan=='PT.ETC')
        <tr>
            <td rowspan="2" colspan="2" valign="center"><b>{{strtoupper($invoice->pname)}}</b></td>
            <td colspan="3"><b>Faktur No: {{$invoice->no_nota}}</b></td>
        </tr>
        <tr>
            <td colspan="3"><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
        </tr>
    @elseif($kodeperusahaan=='PT.RA')
        <tr>
            <td colspan="5" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;"><b>{{strtoupper($invoice->pname)}}</b></td>
        </tr>
    @else
        <tr>
            <td colspan="5" style="border-top: 1px solid #000000;border-bottom: 1px solid #000000;"><b>{{strtoupper($invoice->pname)}}</b></td>
        </tr>
    @endif
</table>
<table>
   {{-- BERKAH JAYA --}}
   @if($kodeperusahaan=='CV.BJ')
       <tr>
            <td  colspan="2"><b>Kepada Yth : &nbsp; &nbsp; </b></td>
            <td  colspan="3"><b>{{$invoice->tanggal}}</b></td>
        </tr>
        <tr>
            <td  colspan="2"><b>{{ strtoupper($invoice->mname) }}</b></td>
            <td  colspan="3"><b><u>Expedisi : </u><br>{{$invoice->exname}}</b></td>
        </tr>
        <tr>
            <td  colspan="2"><b>{{strtoupper($invoice->malamat)}}</b></td>
            <td  colspan="3"><b><u>Via Expedisi : </u><br>{{$invoice->vianame}}</b></td>
        </tr>
        <tr>
            <td  colspan="2"><b>{{strtoupper($invoice->mcity)}}</b></td>
        </tr>
        <tr></tr>
        <tr></tr>
        <tr></tr>
        <tr>
            <td><p><b><i>Note : Barang yang sudah dibeli tidak boleh dikembalikan </i><br> Remark : </b></p></td>
        </tr>
   {{-- CANDRA JAYA --}}
   @elseif($kodeperusahaan=='PT.CJ')
       <tr>
            <td  colspan="2"><b>{{ $perusahaan->address }}</b></td>
            <td  colspan="3"><b>Kepada Yth : &nbsp; &nbsp; </b></td>
       </tr>
        <tr>
            <td  colspan="2"><b>Telp : {{$perusahaan->telephone}}</b></td>
            <td  colspan="3"><b>{{ strtoupper($invoice->mname) }}</b></td>
        </tr>
        <tr>
            <td  colspan="2"><b>NPWP : 66.502.535.9-047.000</b></td>
            <td  colspan="3"><b>{{strtoupper($invoice->malamat)}}</b></td>
        </tr>
        <tr>
            <td  colspan="2"></td>
            <td  colspan="3"><b>{{strtoupper($invoice->mcity)}}</b></td>
        </tr>
        <tr>
            <td  colspan="2"></td>
            <td  colspan="3"><b>Expedisi : {{$invoice->exname}}</b></td>
        </tr>
        @if($invoice->vianame != null)
            <tr>
                <td  colspan="2"></td>
                <td  colspan="3"><b>Via Expedisi : {{$invoice->vianame}}</b></td>
            </tr>
        @endif
   {{-- ERTRACO --}}
   @elseif($kodeperusahaan=='PT.ETC')

        <tr>
            <td colspan="3"><b>Kepada Yth : &nbsp; &nbsp; </b></td>
            <td colspan="2"><b>Expedisi : {{$invoice->exname}}</b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{ strtoupper($invoice->mname) }}</b></td>
            @if($invoice->vianame != null)
                <td colspan="2"><b>Via Expedisi : {{$invoice->vianame}}</b></td>
            @endif
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->malamat)}}</b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->mcity)}}</b></td>
        </tr>
        <tr>
            <td></td>
        </tr>
   {{-- RICK AUTO --}}
   @elseif($kodeperusahaan=='PT.RA')
        <tr>
           <td colspan="3"><b>Kepada Yth : &nbsp; &nbsp; </b></td>
           <td colspan="2"><b>Faktur No: {{$invoice->no_nota}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{ strtoupper($invoice->mname) }}</b></td>
            <td colspan="2"><b>Hari / Tgl : {{$invoice->tanggal}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->malamat)}}</b></td>
            <td colspan="2"><b>Expedisi : {{$invoice->exname}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->mcity)}}</b></td>
            <td colspan="2"><b>Via Expedisi : {{$invoice->vianame}} </b></td>
        </tr>
   @else
        <tr>
            <td colspan="3"><b>Kepada Yth : &nbsp; &nbsp; </b></td>
            <td colspan="2"><b>Faktur No: {{$invoice->no_nota}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{ strtoupper($invoice->mname) }}</b></td>
            <td colspan="2"><b>Hari / Tgl : {{$invoice->tanggal}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->malamat)}}</b></td>
            <td colspan="2"><b>Expedisi : {{$invoice->exname}} </b></td>
        </tr>
        <tr>
            <td colspan="3"><b>{{strtoupper($invoice->mcity)}}</b></td>
            <td colspan="2"><b>Via Expedisi : {{$invoice->vianame}} </b></td>
        </tr>
   @endif
</table>
<table>
    <thead>
        <tr>
            <th>Produk</th>
            <th>Qty</th>
            <th>Diskon</th>
            <th>Harga/ Unit <br/> (Incl Disc)</th>
            <th>Harga Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($invoicedetail as $key => $value)
            <tr>
                <td>{{ $value->product_name }}</td>
                <td>{{ $value->qty }} {{ $value->satuan }}</td>
                <td>{{ $value->discount }} %</td>
                <td align="right">Rp. {{ number_format($value->hargadiskon, 0, '', '.').',00' }}</td>
                <td align="right">Rp. {{ number_format($value->ttl_price, 0, '', '.').',00' }}</td>
            </tr>
        @endforeach
    </tbody>
 </table>
 <br>
 <table>
    <tr>
        <td>TOTAL PEMBAYARAN</td>
        <td></td>
        <td></td>
        <td>JUMLAH ORDER</td>
        <td align="right">Rp. {{number_format($invoice->subtotal, 0, '', '.').',00'}}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>DISKON {{$invoice->discount}}%</td>
        <td align="right">Rp. {{number_format($invoice->diskon, 0, '', '.').',00'}}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>TOTAL SETELAH DISKON </td>
        <td align="right">Rp. {{number_format($invoice->harga_diskon, 0, '', '.').',00'}}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>PPN 10% </td>
        <td align="right">Rp. {{number_format($invoice->pajak, 0, '', '.').',00'}}</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>EXPEDISI </td>
        <td align="right">Rp. 0</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>LAIN-LAIN </td>
        <td align="right">Rp. 0</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>BIAYA ASURANSI </td>
        <td align="right">Rp. 0</td>
    </tr>
    <tr>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td>TOTAL JUMLAH FAKTUR </td>
        <td align="right">Rp. {{number_format($invoice->grandtotal, 0, '', '.').',00'}}</td>
    </tr>
 </table>
 <br/>
 <table>
    <tr>
        <td>
            <table>
                <tr>
                    <td>Nama</td>
                    <td colspan="3">:<span>{{strtoupper($invoice->pname)}}</span></td>
                </tr>
                <tr>
                    <td>A/C</td>
                    <td colspan="3">:<span>{{ $invoice->prekno }}</span></td>
                </tr>
                <tr>
                    <td>Bank</td>
                    <td colspan="3">:<span>{{ $invoice->pbank }}</span></td>
                </tr>
            </table>
        </td>
        <td>
            <table>
                <tr>
                    <td></td>
                    <td></td>
                    <td>Diterima Oleh</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>{{ strtoupper($invoice->mname)}}</td>
                </tr>
            </table>
        </td>
    </tr>
 </table>
 <table>
    <tr>
        <td>Memo : {{$invoice->memo}}</td>
    </tr>
    <tr>
        <td>Keterangan</td>
    </tr>
    <tr>
        <td colspan="5">1. PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{strtoupper($invoice->pname)}}</td>
    </tr>
    <tr>
        <td colspan="5">2. PEMBAYARAN DAPAT DITRANSFER KE A/C {{$invoice->prekno}} A/N {{strtoupper($invoice->pname)}}</td>
    </tr>
    <tr>
        <td colspan="5">3. BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN</td>
    </tr>
 </table>
 <table>
    <tr>
        <td colspan="5" align="center">{{ $tanggal }}</td>
    </tr>
 </table>
