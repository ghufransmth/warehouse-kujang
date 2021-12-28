<style type='text/css' media='print'>
    @page {

      size: auto;

      margin: 0;

      margin-left: 0.1cm;

    }
    

  </style>
  <style>
    body{
      padding-left: 1.3cm;

      padding-right: 1.3cm;

      padding-top: 1.1cm;

    }
  </style>
       {{-- BERKAH JAYA --}}
       @if($kodeperusahaan=='CV.BJ')
            <table width='100%' cellspacing='0,4'>
                <td><b style='margin-top:0.8mm; font-size: 28px; '>{{strtoupper($invoice->pname)}}</b></td>
                <td><b style='margin-top:0.6mm; font-size: 16px;'>The Specialist For Engine Parts</b></td>
                <td align='right'><b style='margin-top:0.6mm; font-size: 20px;'>{{$invoice->no_nota}}</b></td>
            </table>
       {{-- CANDRA JAYA --}}
       @elseif($kodeperusahaan=='PT.CJ')
            <table width='100%' cellspacing='0,4'>
                <td width='60%'><b style='margin-top:0.8mm; height: 2px; font-size: 28px;'>{{strtoupper($invoice->pname)}}</b></td>
                <td width='2%'></td>
                <td width='38%' align='left'><b style='margin-top:0.6mm;height: 2px; font-size: 15px;margin-left:10px;'>Faktur No: {{$invoice->no_nota}}</b> <br/> <b style='margin-top:0.6mm; font-size: 15px;margin-left:10px;'>Hari / Tgl : {{$invoice->tanggal}}</b></td>
            </table>
       {{-- ERTRACO --}}
       @elseif($kodeperusahaan=='PT.ETC')
            <table width='100%' cellspacing='0,4'>
                <td width='60%'><b style='margin-top:0.8mm; font-size: 28px; '>{{strtoupper($invoice->pname)}}</b></td>
                <td width='1%'></td>
                <td width='39%' align='left'><b style='margin-top:0.6mm; font-size: 15px;margin-left:10px;'>Faktur No: {{$invoice->no_nota}}</b> <br/> <b style='margin-top:0.6mm; font-size: 15px;margin-left:10px;'>Hari / Tgl : {{$invoice->tanggal}}</b></td>
            </table>
       @elseif($kodeperusahaan=='PT.RA')
            <hr style='margin-top:1px'>
            <table width='100%' cellspacing='0,4'>
                <td><b style='margin-top:0.8mm; font-size: 28px; '>{{strtoupper($invoice->pname)}}</b></td>
            </table>
       @else
            <hr style='margin-top:1px'>
            <table width='100%' cellspacing='0,4'>
                <td><b style='margin-top:0.8mm; font-size: 28px; '>{{strtoupper($invoice->pname)}}</b></td>
            </table>
       @endif


      <hr style='margin-top:1px'>

      <table width='100%' cellspacing='0,4'>
        {{-- BERKAH JAYA --}}
        @if($kodeperusahaan=='CV.BJ')
            <tr>
                <td width='65%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><div style="width: 300px;margin-left:20px;"><b>{{ strtoupper($invoice->mname) }}</b></div></td>
                                        </tr>
                                       
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><div style="width: 400px;margin-left:20px;"><b>{{strtoupper($invoice->malamat)}}</b></div></td>
                                        </tr>
                                       

                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><div style="width: 300px;margin-left:20px;"><b>{{strtoupper($invoice->mcity)}}</b></div></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><p><b><i>Note : Barang yang sudah dibeli tidak boleh dikembalikan </i><br> Remark : </b></p></td>
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
                                            <td align='right' style='height: 2px; vertical-align: top; font-size: 16px' align='left'><b><b> {{$invoice->tanggal}}</b></b></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td align='right' style='height: 2px; vertical-align: top;' align='left';><b><b><u>Expedisi : </u><br>{{$invoice->exname}}</b></b></td>
                                        </tr>
                                        <tr>
                                            <td align='right' style='height: 2px; vertical-align: top;' align='left';><b><b><u>Via Expedisi : </u><br>{{$invoice->vianame}}</b></b></td>
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
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>{{ $perusahaan->address }}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>Telp : {{$perusahaan->telephone}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;;'>NPWP : 66.502.535.9-047.000</b></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                </table>
                            </td>
                        <tr>
                    </table>
                <td>
                <td width='2%' style='height: 2px; vertical-align: top;'></td>
                <td width='38%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='100%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>{{ strtoupper($invoice->mname) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>{{$invoice->malamat}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;;'>{{strtoupper($invoice->mcity)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top;' align='left'><b><b> Expedisi : {{$invoice->exname}}</b></b></td>
                                        </tr>
                                        @if($invoice->vianame != null)
                                            <tr>
                                                <td align='left' style='height: 2px; vertical-align: top;' align='left'><b><b> Via Expedisi : {{$invoice->vianame}}</b></b></td>
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
                <td width='60%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>{{ strtoupper($invoice->mname) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;'>{{strtoupper($invoice->malamat)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style='margin-left:0px;;'>{{strtoupper($invoice->mcity)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                        </tr>
                                </table>
                            </td>
                        <tr>
                    </table>
                <td>
                 <td width='1%'></td>
                <td width='39%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='100%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top; font-size: 13px' align='left'><b><b> Expedisi : {{$invoice->exname}}</b></b></td>
                                        </tr>
                                        @if($invoice->vianame != null)
                                            <tr>
                                                <td align='left' style='height: 2px; vertical-align: top; font-size: 13px' align='left'><b><b> Via Expedisi : {{$invoice->vianame}}</b></b></td>
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
                <td width='65%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{ strtoupper($invoice->mname) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{strtoupper($invoice->malamat)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{strtoupper($invoice->mcity)}}</b></td>
                                        </tr>
                                </table>
                            </td>
                        <tr>
                    </table>
                <td>
                <!-- <td width='2%' style='height: 2px; vertical-align: top;'>
                </td> -->
                <td width='35%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top; font-size: 16px' align='left'><b>Faktur No: {{$invoice->no_nota}}</b></td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top; font-size: 14px' align='left'><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top;' align='left';>Expedisi : {{$invoice->exname}}</td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top;' align='left';>Via Expedisi : {{$invoice->vianame}}</td>
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
                <td width='65%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b>Kepada Yth : &nbsp; &nbsp; </b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{ strtoupper($invoice->mname) }}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{strtoupper($invoice->malamat)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='height: 2px; vertical-align: top;'><b style=''>{{strtoupper($invoice->mcity)}}</b></td>
                                        </tr>
                                </table>
                            </td>
                        <tr>
                    </table>
                <td>
                <!-- <td width='2%' style='height: 2px; vertical-align: top;'>
                </td> -->
                <td width='35%' style='height: 2px; vertical-align: top;'>
                    <table width='100%'>
                        <tr>
                            <td width='50%' style='height: 2px; vertical-align: top;'>
                                <table width='100%'>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top; font-size: 16px' align='left'><b>Faktur No: {{$invoice->no_nota}}</b></td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top; font-size: 14px' align='left'><b>Hari / Tgl : {{$invoice->tanggal}}</b></td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top;' align='left';>Expedisi : {{$invoice->exname}}</td>
                                        </tr>
                                        <tr>
                                            <td align='left' style='height: 2px; vertical-align: top;' align='left';>Via Expedisi : {{$invoice->vianame}}</td>
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
      <div class='table-responsive'>

          <table width='100%' border='2' cellspacing='0,5' style='margin-top:2px'>

              <thead>

                  <tr>
                      <th style="height: 10px;width: 330px;font-size: 14px;">Produk</th>
                      <th style='height: 10px; height: 10px; width: 70px;font-size: 14px;'>Qty</th>
                      <th style='height: 10px; font-size: 14px;width: 50px;' class='col-sm-2'>Diskon</th>
                      <th style='height: 10px; font-size: 14px;width: 100px;' class='col-sm-2'>Harga / Unit <br/>(Incl Disc)</th>
                      <th style='height: 10px; font-size: 14px;' class='col-sm-2' width="100px;">Harga Total</th>
                  </tr>

              </thead>
              <tbody>
                    @foreach($invoicedetail as $key => $value)
                        <tr>
                            <td style="font-size: 14px;font-weight: bold;" align="left">{{ $value->product_name }}</td>
		                    <td style="font-size: 14px;" align="center" class="col-sm-1">{{ $value->qty }} {{ $value->satuan }}</td>
		                    <td style="font-size: 14px;" align="center" class="col-sm-1">{{ $value->discount }} %</td>
		                    <td style="font-size: 14px;" align="right" class="col-sm-1">Rp. {{ number_format($value->hargadiskon, 0, '', '.') }}</td>
		                    <td style="font-size: 14px;" align="right" class="col-sm-1">Rp. {{ number_format($value->ttl_price, 0, '', '.') }}</td>
                        </tr>
                    @endforeach
              </tbody>

          </table>

          <br>

          <table width='100%' border='0' cellspacing='0'>

              <tr>

                  <td style='height: 25px; vertical-align: center;' rowspan='9'><b><b>TOTAL PEMBAYARAN</b></b></td>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>JUMLAH ORDER</b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. {{number_format($invoice->subtotal, 0, '', '.')}}</b></td>

              </tr>

          <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>DISKON {{$invoice->discount}}%</b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. {{number_format($invoice->diskon, 0, '', '.')}}</b></td>

              </tr>
          <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>TOTAL SETELAH DISKON </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. {{number_format($invoice->harga_diskon, 0, '', '.')}}</b></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>PPN 10% </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. {{number_format($invoice->pajak, 0, '', '.')}}</b></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>EXPEDISI </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. 0</b></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>LAIN-LAIN </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. 0</b></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>BIAYA ASURANSI </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. 0</b></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='4'><hr width='100%' style='border-top: 3px dashed black;'></td>

                  <td></td>

              </tr>

              <tr>

                  <td style='height: 15px; vertical-align: top;' colspan='3'><b>TOTAL JUMLAH FAKTUR </b></td>

                  <td style='height: 15px; vertical-align: top;' align='right'><b>Rp. {{number_format($invoice->grandtotal, 0, '', '.')}}</b></td>

              </tr>

          </table>

          <br><br><br>

          <table border='0' width='100%'>

              <tr>

                  <td  width='70%'>

                      <table width='90%'>

                          <tr>

                              <td style='height: 2px; vertical-align: top;'><b>Nama</b></td>

                              <td style='height: 2px; vertical-align: top;'><b>:</b></td>

                              <td style='height: 2px; vertical-align: top;'><b><span class='text-semibold'>{{strtoupper($invoice->pname)}}</b></span></td>

                          </tr>

                          <tr>

                              <td style='height: 2px; vertical-align: top;'><b>A/C</b></td>

                              <td style='height: 2px; vertical-align: top;'><b>:</b></td>

                              <td style='height: 2px; vertical-align: top;'><b><span>{{ $invoice->prekno }}</b></span></td>

                          </tr>

                          <tr>

                              <td style='height: 2px; vertical-align: top;'><b>Bank</b></td>

                              <td style='height: 2px; vertical-align: top;'><b>:</b></td>

                              <td style='height: 2px; vertical-align: top;'><b><span class='text-semibold'>{{ $invoice->pbank }}</span></b></td>

                          </tr>



                      </table>

                  </td>

                  <td align='center'>
                      <table>
                          <tr>
                              <td align='center' style='height: 2px; vertical-align: top;'><b>Diterima Oleh</b></td>
                          </tr>
                          <tr>
                              <td align='center' style='height: 2px; vertical-align: top;font-size: 14px'><b>{{ strtoupper($invoice->mname) }}</b></td>
                          </tr>
                      </table>
                      <p style='margin-top:100px'> .......................................................... </p>
                  </td>

          </table>

          <table border='0' width='100%'>

              <tr>

                  <td>

                      <p size='2' style="font-size: 12px">

                      Memo : {{$invoice->memo}}</p>

                      <p style='font-size:10px'>Keterangan</p>

                      <p style='font-size:10px'>1. PEMBAYARAN DENGAN CEK/GIRO HARUS MENCANTUMKAN NAMA {{strtoupper($invoice->pname)}}</p>

                      <p style='font-size:10px'>2. PEMBAYARAN DAPAT DITRANSFER KE A/C {{$invoice->prekno}} A/N {{strtoupper($invoice->pname)}}</p>

                      <p style='font-size:10px'>3. BARANG YANG SUDAH DIBELI TIDAK DAPAT DIKEMBALIKAN</p>

                  </td>



                  </tr>

                  </table>



                  <table border='0' width='100%'>

                      <tr>

                          <td style='height: 15px; vertical-align: bottom;'>

                              <p style='font-size:10px; padding-left:80%; margin-top:10%;'>{{ $tanggal }}</p>

                          </td>

                      </tr>

                  </table>

      </div>
      <div class='panel-body'>

          <div class='row invoice-payment'>

              <div class='col-sm-7'>

              </div>

          </div>
      </div>



