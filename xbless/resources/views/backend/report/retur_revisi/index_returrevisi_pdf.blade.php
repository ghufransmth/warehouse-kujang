<!DOCTYPE html>
<html>
    <head>
        <title>LAPORAN RETUR DAN REVISI</title>
        <style type="text/css">
        
        .text-center {
            text-align: center
        }
        .text-right {
            text-align: right
        }
       
        </style>
       
    </head>
    <body>
<htmlpageheader name="MyHeader1">
<br/>
<h3 style="margin-top: 10px;text-align: center;"> LAPORAN RETUR DAN REVISI {{ !empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</h3>
<h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4>
</htmlpageheader>
<sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
<div>
<table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">

    <thead>
        <tr>
            <th width="6%" style="border: 0.5px solid #000;">No</th>
            <th style="text-align: center; border: 0.5px solid #000;">No Retur/Revisi</th>
            <th style="text-align: center;border: 0.5px solid #000;">No Invoice</th>
            <th style="text-align: center;border: 0.5px solid #000;">Customer</th>
            <th style="text-align: center;border: 0.5px solid #000;">Kode-Nama (Produk)</th>
            <th style="text-align: center;border: 0.5px solid #000;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        @php
            $jumlah = count($value->produk_info)
        @endphp
        <tr>
            <td width="6%" style="border: 0.5px solid #000;text-align: center;" valign="top" rowspan="{{$jumlah}}">{{$no + 1}}</td>
            <td style="border: 0.5px solid #000;text-align: left;" valign="top" rowspan="{{$jumlah}}">{{$value->noretur}}</td>
            <td style='border: 0.5px solid #000;text-align:left;' valign="top" rowspan="{{$jumlah}}">{{$value->no_inv}}</td>
            <td style='border: 0.5px solid #000;text-align:left;' valign="top" rowspan="{{$jumlah}}">{{$value->member_name}} - {{$value->nama_kota}}</td>
            @foreach ($value->produk_info as $key => $produkinfo)
                @if($key==0)
                    <td style='border: 0.5px solid #000;text-align:left' valign="top">
                        {{$produkinfo}}
                    </td>
                @endif
            @endforeach   
            @foreach ($value->ket as $key => $ket)
                @if($key==0)
                    <td style='border: 0.5px solid #000;text-align:left' valign="top">
                        {{$ket}}
                    </td>
                @endif
            @endforeach
           
        </tr>
            @if($jumlah > 1)
                @foreach ($value->produk_info as $key => $item_data)
                    @if($key != 0)
                        <tr>
                            <td style="border: 0.5px solid #000;text-align:left" valign="top">
                                {{$item_data}}
                            </td>
                            <td style="border: 0.5px solid #000;text-align:left" valign="top">
                                {{$value->ket[$key]}}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        @endforeach
        @for($i=1;$i<2;$i++)
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
           
        </tr>
        @endfor
    </tbody>
</table>
<i>Printed date : <?php echo date("d M Y") ?> </i>
</div>
</body>
</html>
