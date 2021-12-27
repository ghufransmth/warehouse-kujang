<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
    {{-- <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet"> --}}
    {{-- <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet"> --}}
  <title>Print LAPORAN RETUR DAN REVISI</title>
  <style type="text/css" media="print">
    /* @page { size: landscape; } */
    @page {

      size: auto;

      margin: 0;

      margin-left: 0.1cm;

    }
    body{

      padding-left: 1.0cm !important;

      padding-right: 0.7cm !important;

      padding-top: 1.1cm !important;

    }
  </style>
</head>
<body onload="window.print();">
  <div class="wrapper">
    <section class="section_print">
      <div class="row">
        <div class="col-md-12">
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
            <h3 style="margin-top:3mm;text-align:center; vertical-align: middle"> <br>
              <b>LAPORAN RETUR DAN REVISI {{ !empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</b>
            </h3>
            <h6 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
              <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> 
            </h6>
        </div>
      </div>
      <br/>
      <div class="row">
        <div class="col-md-12">
          <table border='1' width='100%' cellspacing='0'>
            <thead>
              <tr class="two-strips-top">
                <th style="text-align : center"><b>No</b></th>
                <th style="text-align : center"><b>No Retur/Revisi</b></th>
                <th style="text-align : center"><b>No Invoice</b></th>
                <th style="text-align : center"><b>Customer</b></th>
                <th style="text-align : center"><b>Kode-Nama (Produk)</b></th>
                <th style="text-align : center"><b>Keterangan</b></th>
              </tr>
            </thead>
            <tbody>
              @foreach($data as $key=>$value)
                <?php $jumlah= count($value->produk_info);?>
                <tr class="two-strips-bottom">
                    <td style='font-size: 10px;' valign="top" rowspan="{{$jumlah}}">{{$key + 1}}</td>
                  
                    <td style='font-size: 12px;' valign="top" rowspan="{{$jumlah}}">{{$value->noretur}}</td>
                    <td style='font-size: 12px;' valign="top" rowspan="{{$jumlah}}">{{$value->no_inv}}</td>
                    <td style='font-size: 10px;' valign="top" rowspan="{{$jumlah}}">{{$value->member_name}} - {{$value->nama_kota}}</td>
                    
                    @foreach ($value->produk_info as $key => $produkinfo)
                        @if($key==0)
                        <td style='font-size: 10px;' valign="top">
                            {{$produkinfo}}
                        </td>
                        @endif
                    @endforeach   
                    @foreach ($value->ket as $key => $ket)
                        @if($key==0)
                        <td style='font-size: 10px;' valign="top">
                            {{$ket}}
                        </td>
                        @endif
                    @endforeach
                </tr>
                @if($jumlah > 1)
                  @foreach ($value->produk_info as $key => $item_data)
                      @if($key != 0)
                          <tr>
                              <td style='font-size: 10px;' valign="top">
                                  {{$item_data}}
                              </td>
                              <td style='font-size: 10px;' valign="top">
                                  {{$value->ket[$key]}}
                              </td>
                          </tr>
                      @endif
                  @endforeach
                @endif
              @endforeach
            </tbody>
          </table> 
        </div>
      </div>
      <i>Printed date : <?php echo date("d M Y") ?> </i>
    </section>
  </div>
</body>
</html>
