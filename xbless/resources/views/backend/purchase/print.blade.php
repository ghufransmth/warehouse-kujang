<style type='text/css' media='print'>
    @page {
      size: auto;
      margin: 0;
    }
  </style>
  <style>
    body{
      padding-left: 0.8cm;
      padding-right: 0.8cm;
      padding-top: 0.3cm;
    }
  </style>
      <table width='100%'>

          <tr>

              <td><b style='font-size: 25px;font-family:arial'>No : {{ $purchase->no_nota }}</b></td>

              <td align='Right'><h4><b style='font-size: 15px;font-family:arial'>{{ date("d M Y",strtotime($purchase->created_at)) }}</b></h4></td>



          <tr>

      </table>

      <table width='100%'>

          <tr>

              <td width='30%'>

                  <table width='100%' border='2' cellspacing='0'>

                      <tr>

                          <td style='width: 50%; vertical-align: top;font-family:arial;font-size: 12px;' align='center'>Diterima O/</td>

                          <td style='width: 50%; vertical-align: top;font-family:arial;font-size: 12px;' align='center'>Diserahkan O/</td>

                      </tr>

                      <tr>

                          <td height='40'></td>

                          <td></td>

                      </tr>

                  </table>

              </td>

              <td width='20%'>



              </td>

              <td width='50%' align='right'>

                  <table width='100%'>

                      <tr>

                          <td align='right'><b style='font-size: 12px;font-family:arial'>{{ $purchase->mname }}</b></td>

                      </tr>

                      <tr>

                          <td align='right' style='font-size: 12px;font-family:arial'>{{ $purchase->maddress }}</td>

                      </tr>

                      <tr>

                          <td align='right' style='font-size: 12px;font-family:arial'>{{ $purchase->mcity }}</td>

                      </tr>

                      <tr>

                          <td align='right' style='font-size: 12px;font-family:arial'>Expedisi : {{$purchase->exname}} </td>

                      </tr>
                      @if($purchase->vianame != '')
                            <tr>
                                <td align='right' style='font-size: 12px;font-family:arial'>Via Expedisi : {{$purchase->vianame}}</td>
                            </tr>
                      @endif
                      <tr>
                          <td align='right' style='font-size: 12px;font-family:arial'></td>


                      </tr>

                  </table>

              </td>

          <tr>

      </table>

      <b style='font-family:arial'>GUDANG : {{ $purchase->pername }} ( {{ $gudang->name }} ) </b>

      <div class='table-responsive'>

          <br>

          <table width='100%' border='2' cellspacing='0' cellpadding='0'>
            <thead>
                <tr>
                    <th style='font-size: 14px;font-family:arial'>Produk</th>
                    <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Qty Order</th>
                    <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Qty Kirim</th>
                    @if($userakses == 'gudang')
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Colly</th>
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Berat</th>
                    @elseif($userakses == 'po' || $userakses == 'invoice')
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Harga Net</th>
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Harga Cust</th>
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Disc</th>
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Harga Cust <br/> (Incl Disc)</th>
                        <th class='col-sm-1' style='font-size: 14px;font-family:arial'>Harga Total</th>
                    @endif
                </tr>
            </thead>
            <tbody align="center">
                @foreach($purchasedetail as $key => $value)
                    <tr>
                        <td style='font-size: 12px;font-family:arial' align="left">{{ $value->prname }} </td>
                        <td style='font-size: 12px;font-family:arial'>{{ $value->qty }} {{ $value->satuan }}</td>
                        <td style='font-size: 12px;font-family:arial'>{{ $value->qtykirim }} {{ $value->satuan }}</td>
                        @if($userakses == 'gudang')
                            <td style='font-size: 12px;font-family:arial'>{{$value->colly}} - {{$value->colly_to}}</td>
                            <td style='font-size: 12px;font-family:arial'>{{$value->weight}} Kg</td>
                        @elseif($userakses == 'po' || $userakses == 'invoice')

                            <td style='font-size: 12px;font-family:arial' align="right">{{ number_format($value->hargaasli, 0, '', '.') }}</td>
                            <td style='font-size: 12px;font-family:arial' align="right">{{ number_format($value->hargarpo, 0, '', '.') }}</td>
                            <td style='font-size: 12px;font-family:arial' >{{ $value->discount }} %</td>
                            <td style='font-size: 12px;font-family:arial' align="right">{!! $value->hargaunitsetelahdiskon !!}</span></td>
                            <td style='font-size: 12px;font-family:arial' align="right">{{ number_format($value->hargatotal, 0, '', '.') }}</span></td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>

          <br>

          <table border='2' width='40%' cellspacing='0'>

                  <tr>

                      <td width='80' align='center' style='font-size: 13px;font-family:arial'><b>No Inv</b></td>

                      <td width='80' style='font-size: 13px;font-family:arial'></td>

                  </tr>

          </table>

      </div>



      <div class='panel-body'>

          <div class='row invoice-payment'>

              <div class='col-sm-7'>



              </div>

          </div>



          <br>

          <h3>Note : {{ $purchase->note }}</h3>

          <p style='font-size: 12px; padding-left:430px; font-family:arial'>Print By : {{ $printoleh }} </p>

      </div>



      <script>

          window.print();

      </script>

