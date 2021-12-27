<style type='text/css' media='print'>
    @page {
      size: auto;
      margin: 0;
    }
</style>
<style>
    body{
        padding-left: 1.3cm;
        padding-right: 1.3cm;
        padding-top: 1.1cm;
    }
</style>
      <p align='center'><font size='6'><b><U>SURAT JALAN</font></U></b>
      <br>{{$invoice->no_nota}}
      </p>
      <table width='100%'>
          <tr>
              <td width='50%'>

              <table width='100%'>

                  <tr>

                      <td><font size='3'><b>Nama  </b></font></td>

                      <td><font size='3'><b>:{{strtoupper($invoice->mname)}}</b></td>

                  </tr>

                  <tr>

                      <td valign="top"><b>Alamat  </b></td>

                      <td><b>:{{$invoice->malamat}}</b></td>

                  </tr>

                  <tr>

                      <td></td>

                      <td><b>{{$invoice->mcity}}</b></td>

                  </tr>

              </table>

              </td>

              <td width='50%' align='right'>

              <table width='100%'>

                  <tr>

                      <td align='right'></td>

                      <td align='right'>{{$tanggal}}</td>

                  </tr>

                  <tr>

                      <td align='right'>Expedisi </td>

                      <td align='right'>{{$invoice->exname}}</td>

                  </tr>

                  <tr>

                      <td align='right'>Via Expedisi </td>

                      <td align='right'>{{$invoice->vianame}}</td>

                  </tr>

              </table>

              </td>

          <tr>

      </table>

      <div class='table-responsive'>

      <br>

          <table width='100%' border='1' cellspacing='0'>

              <thead>

                  <tr>

                      <th style='height: 25px; font-size: 14px;'>Qty</th>

                      <th style='height: 25px;  font-size: 14px; width:10%' class='col-sm-1'>Keterangan</th>

                      <th style='height: 25px; font-size: 14px;' class='col-sm-1'>Product</th>

                  </tr>

              </thead>

              <tbody>
                @foreach($invoicedetail as $key => $value)
                    <tr>

                        <td style='height: 25px; vertical-align: center; font-size: 14px;' align='center'>{{$value->qty}} {{$value->satuan}}</td>

                        <td style='height: 25px; vertical-align: center; font-size: 14px;'>{{$value->deskripsi}}</td>

                        <td align='center' style='height: 25px; vertical-align: center; font-size: 14px;'>{{$value->product_name}}</td>

                    </tr>
                @endforeach
              </tbody>

          </table>

          <br><br>

          <table border='0' width='100%'>

              <tr>

                  <td>

                      <table>

                          <tr>

                              <td><p><b>Diterima Oleh</b></p></td>

                          </tr>

                          <tr>

                              <td><p><b>{{strtoupper($invoice->mname)}}</b></p></td>

                          </tr>

                      </table>

                  </td>

                  <td align='right'>

                      <table>

                          <tr>

                              <td align='center'><p><b>Hormat Kami</b></p></td>

                          </tr>

                          <tr>

                              <td align='center'><p><b>{{strtoupper($invoice->pname)}}<b></p></td>

                          </tr>

                      </table>

                  </td>

          </table>

      </div>

      <div class='panel-body'>

          <div class='row invoice-payment'>

              <div class='col-sm-7'>
              </div>

            </div>
      </div>



          <script>

          window.print();

          window.onfocus=function(){ window.close();}

      </script>

