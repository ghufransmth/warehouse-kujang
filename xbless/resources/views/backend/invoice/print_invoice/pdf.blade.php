<!DOCTYPE>
<html>
	<head>
		<title>{{ $title }}</title>
	</head>
	<body>
        {{-- BERKAH JAYA --}}
       @if($kodeperusahaan=='CV.BJ')
            <table width='100%' cellspacing='0,4'>
                <tr>
                    <td><b style='margin-top:0.8mm; font-size: 16px; '>{{strtoupper($invoice->pname)}}</b></td>
                    <td><b style='margin-top:0.6mm; font-size: 14px;'>The Specialist For Engine Parts</b></td>
                    <td align='right'><b style='margin-top:0.6mm; font-size: 14px;'>{{$invoice->no_nota}}</b></td>
                </tr>
            </table>
        {{-- CANDRA JAYA --}}
        @elseif($kodeperusahaan=='PT.CJ')
            <table width='100%' cellspacing='0,4'>
                <tr>
                    <td width='60%'><b style='margin-top:0.8mm; font-size: 16px; '>{{strtoupper($invoice->pname)}}</b></td>
                    <td width='5%'></td>
                    <td width='35%' align='left'><b style='margin-top:0.6mm; font-size: 12px;margin-left:10px;'>Faktur No: {{$invoice->no_nota}}</b> <br/> <b style='margin-top:0.6mm; font-size: 12px;margin-left:10px;'>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                </tr>
            </table>
        {{-- ERTRACO --}}
        @elseif($kodeperusahaan=='PT.ETC')
            <table width='100%' cellspacing='0,4'>
                <tr>
                    <td width='60%'><b style='margin-top:0.8mm; font-size: 16px; '>{{strtoupper($invoice->pname)}}</b></td>
                    <td width='5%'></td>
                    <td width='35%' align='left'><b style='margin-top:0.6mm; font-size: 12px;'>Faktur No: {{$invoice->no_nota}}</b> <br/> <b style='margin-top:0.6mm; font-size: 12px;'>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                </tr>
            </table>
        @elseif($kodeperusahaan=='PT.RA')
            <hr style='margin-top:1px'>
            <table width='100%' cellspacing='0,4'>
                <tr>
                    <td><b style='margin-top:0.8mm; font-size: 16px; '>{{strtoupper($invoice->pname)}}</b></td>
                </tr>
            </table>
        @else
            <hr style='margin-top:1px'>
            <table width='100%' cellspacing='0,4'>
                <tr>
                    <td><b style="font-size: 16px;">{{strtoupper($invoice->pname)}}</b></td>
                </tr>
            </table>
        @endif
		<hr style="margin-top:1px">
        <table width="100%" cellspacing="0">
            {{-- BERKAH JAYA --}}
            @if($kodeperusahaan=='CV.BJ')
                <tr>
                    <td width='65%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px'><b style='margin-left:20px'>{{ strtoupper($invoice->mname) }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px'><b style='margin-left:20px'>{{strtoupper($invoice->malamat)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px'><b style='margin-left:20px;'>{{strtoupper($invoice->mcity)}}</b></td>
                                            </tr>
                                            <br/>
                                            <br/>
                                            <br/>
                                            <tr>
                                                <td><p style="font-size: 12px"><b><i>Note : Barang yang sudah dibeli tidak boleh dikembalikan </i><br> Remark : </b></p></td>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    <td>
                    <td width='5%' style='height: 2px; vertical-align: top;'>
                    </td>
                    <td width='30%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px' align='left'><b><b> {{$invoice->tanggal}}</b></b></td>
                                            </tr>

                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top;font-size: 12px' align='left';><b><b><u>Expedisi : </u><br>{{$invoice->exname}}</b></b></td>
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top;font-size: 12px' align='left';><b><b><u>Via Expedisi : </u><br>{{$invoice->vianame}}</b></b></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <tr>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    </td>
                </tr>
            {{-- CANDRA JAYA --}}
            @elseif($kodeperusahaan=='PT.CJ')
                <tr>
                    <td width='60%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>{{ $perusahaan->address }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>Telp : {{$perusahaan->telephone}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>NPWP : 66.502.535.9-047.000</b></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    <td>
                    <td width='5%' style='height: 2px; vertical-align: top;'></td>
                    <td width='35%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='100%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px;'><b style='margin-left:0px;'>{{ strtoupper($invoice->mname) }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px;'><b style='margin-left:0px;'>{{strtoupper($invoice->malamat)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px;'><b style='margin-left:0px;;'>{{strtoupper($invoice->mcity)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td align='left' style='height: 2px; vertical-align: top;font-size: 12px;' align='left'><b><b> Expedisi : {{$invoice->exname}}</b></b></td>
                                            </tr>
                                            @if($invoice->vianame != null)
                                                <tr>
                                                    <td align='left' style='height: 2px; vertical-align: top;font-size: 12px;' align='left'><b><b> Via Expedisi : {{$invoice->vianame}}</b></b></td>
                                                </tr>
                                            @endif
                                    </table>
                                </td>
                            <tr>
                        </table>
                    </td>
                </tr>
            {{-- ERTRACO --}}
            @elseif($kodeperusahaan=='PT.ETC')
                <tr>
                    <td width='65%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;font-size: 12px;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>{{ strtoupper($invoice->mname) }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>{{strtoupper($invoice->malamat)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;font-size: 12px;'>{{strtoupper($invoice->mcity)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    <td>
                    <td width='35%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='100%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td align='left' style='height: 2px; vertical-align: top; font-size: 12px' align='left'><b><b> Expedisi : {{$invoice->exname}}</b></b></td>
                                            </tr>
                                            @if($invoice->vianame != null)
                                                <tr>
                                                    <td align='left' style='height: 2px; vertical-align: top; font-size: 12px' align='left'><b><b> Via Expedisi : {{$invoice->vianame}}</b></b></td>
                                                </tr>
                                            @endif
                                    </table>
                                </td>
                            <tr>
                        </table>
                    </td>
                </tr>
            {{-- RICK AUTO --}}
            @elseif($kodeperusahaan=='PT.RA')
                <tr>
                    <td width='65%' style='vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{ strtoupper($invoice->mname) }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{strtoupper($invoice->malamat)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{strtoupper($invoice->mcity)}}</b></td>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    <td>
                    <td width='35%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px' align='left'><b>Faktur No: {{$invoice->no_nota}}</b></td>
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 11px' align='left'><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                                                
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px;' align='left';>Expedisi : {{$invoice->exname}}</td>
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px;' align='left';>Via Expedisi : {{$invoice->vianame}}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <tr>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    </td>
                </tr>
            @else
                <tr>
                    <td width='65%' style='vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{ strtoupper($invoice->mname) }}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{strtoupper($invoice->malamat)}}</b></td>
                                            </tr>
                                            <tr>
                                                <td style='vertical-align: top;font-size: 12px;'><b style=''>{{strtoupper($invoice->mcity)}}</b></td>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    <td>
                    <td width='35%' style='height: 2px; vertical-align: top;'>
                        <table width='100%'>
                            <tr>
                                <td width='50%' style='height: 2px; vertical-align: top;'>
                                    <table width='100%'>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px' align='left'><b>Faktur No: {{$invoice->no_nota}}</b></td>
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 11px' align='left'><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                                                
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px;' align='left';>Expedisi : {{$invoice->exname}}</td>
                                            </tr>
                                            <tr>
                                                <td align='right' style='height: 2px; vertical-align: top; font-size: 12px;' align='left';>Via Expedisi : {{$invoice->vianame}}</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                            </tr>
                                            <tr>
                                            </tr>
                                    </table>
                                </td>
                            <tr>
                        </table>
                    </td>
                </tr>
            @endif
        </table>
		<div class="table-responsive">
		    <table width="100%" border="1" cellspacing="0" style="margin-top:4px">
		        <thead>
		            <tr>
		                <th style="height: 14px;font-size: 12px" align="left"><b>Produk</b></th>
		                <th style="height: 14px;font-size: 12px" align="left" class="col-sm-2"><b>QTY</b></th>
		                <th style="height: 14px;font-size: 12px" align="center" class="col-sm-1"><b>Diskon</b></th>
		                <th style="height: 14px;font-size: 12px" align="right" class="col-sm-1"><b>Harga / Unit <br/> (Incl Disc)</b></th>
		                <th style="height: 14px;font-size: 12px" align="right" class="col-sm-1"><b>Harga Total</b></th>
    	        	</tr>
		        </thead>
		        <tbody>
		        	@foreach($invoicedetail as $key => $value)
                        <tr>
                            <td style="font-size: 12px;" align="left">{{ $value->product_name }}</td>
		                    <td style="font-size: 12px;" align="left" class="col-sm-2">{{ $value->qty }} {{ $value->satuan }}</td>
		                    <td style="font-size: 12px;" align="center" class="col-sm-2">{{ $value->discount }} %</td>
		                    <td style="font-size: 12px;" align="right" class="col-sm-1">Rp. {{ number_format($value->hargadiskon, 0, '', '.') }}</td>
		                    <td style="font-size: 12px;" align="right" class="col-sm-1">Rp. {{ number_format($value->ttl_price, 0, '', '.') }}</td>
                        </tr>
                    @endforeach
		        </tbody>
		    </table>
		    <br>
		    <table width="100%" border="0" cellspacing="0">
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: center;" rowspan="9"><b><br/><br/><br/>TOTAL PEMBAYARAN &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>JUMLAH ORDER</b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. {{number_format($invoice->subtotal, 0, '', '.')}}</b></td>
				</tr>
				<tr>
					<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>DISKON {{$invoice->discount}}%</b></td>
					<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. {{number_format($invoice->diskon, 0, '', '.')}}</b></td>
				</tr>
				<tr>
				<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>TOTAL SETELAH DISKON </b></td>
				<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. {{number_format($invoice->harga_diskon, 0, '', '.')}}</b></td>
				</tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>PPN 10% </b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. {{number_format($invoice->pajak, 0, '', '.')}}</b></td>
    	        </tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>EXPEDISI </b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. 0</b></td>
    	        </tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>LAIN-LAIN </b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. 0</b></td>
    	        </tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>BIAYA ASURANSI </b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. 0</b></td>
    	        </tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="4">...................................................................................</td>
    	        	<td></td>
    	        </tr>
    	        <tr>
    	        	<td style="font-size: 12px; vertical-align: top;" colspan="3"><b>TOTAL JUMLAH FAKTUR </b></td>
    	        	<td style="font-size: 12px; vertical-align: top;" align="right"><b>Rp. {{number_format($invoice->grandtotal, 0, '', '.')}}</b></td>
    	        </tr>
		    </table>
		    <br><br><br>
		    <table border="0" width="100%">
		    	<tr>
		    		<td>
		    			<table width="90%">
		    				<tr>
								<td style="font-size: 12px; vertical-align: top;"><b>Nama</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b>:</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b><span class="text-semibold">{{strtoupper($invoice->pname)}}</b></span></td>
							</tr>
							<tr>
								<td style="font-size: 12px; vertical-align: top;"><b>A/C</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b>:</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b><span>{{ $invoice->prekno }}</b></span></td>
							</tr>
							<tr>
								<td style="font-size: 12px; vertical-align: top;"><b>Bank</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b>:</b></td>
								<td style="font-size: 12px; vertical-align: top;"><b><span class="text-semibold">{{ $invoice->pbank }}</span></b></td>
							</tr>
						</table>
		    		</td>
		    		<td align="center">
		    			<table>
		    				<tr>
		    					<td align="center" style="font-size: 12px; vertical-align: top;"><b>Diterima Oleh</b></td>
		    				</tr>
		    				<tr>
		    					<td align="center" style="font-size: 12px; vertical-align: top;"><b>{{ strtoupper($invoice->mname) }}</b></td>
		    				</tr>
		    			</table>
                        <br/>
                        <br/>
                        <br/>
		    			<p style="margin-top:100px"> .......................................................... </p>
		    		</td>
		    	</tr>
		    </table>
		    <table border="0" width="100%">
		    	<tr>
		    		<td>
					    <p size="2" style="font-size: 12px">Memo : {{$invoice->memo}}</p>
					    <p style="font-size:10px">Keterangan</p>
					    <p style="font-size:10px">1. PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{strtoupper($invoice->pname)}}</p>
					    <p style="font-size:10px">2. PEMBAYARAN DAPAT DITRANSFER KE A/C {{$invoice->prekno}} A/N {{strtoupper($invoice->pname)}}</p>
					    <p style="font-size:10px">3. BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN</p>
					</td>
				</tr>
			</table>
			<table border="0" width="100%">
				<tr>
					<td style="height: 10px; vertical-align: bottom;" align="right">
			    		<p style="font-size:10px; padding-left:80%; margin-top:10%;">{{ $tanggal }}</p>
			    	</td>
			    </tr>
		    </table>
		</div>
	</body>
</html>
