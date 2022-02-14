<!DOCTYPE html>
<html>
    <head>
        <title>CETAK BARANG MASUK</title>
        <style type="text/css">

        .text-center {
            text-align: center
        }
        .text-right {
            text-align: right
        }

        .title {
            page-break-after: always !important;
        }

        </style>

    </head>
    <body>
        <htmlpageheader name="MyHeader1">
        <br/>
        <div class="title">
            <h3 style="margin-top: 10px;text-align: center;"> LAPORAN BARANG MASUK</h3>
            <h4 style="margin-bottom: 0;text-align: center;"> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4>
        </div>

        </htmlpageheader>
        <sethtmlpageheader name="MyHeader1" value="on" show-this-page="1"/>
        <div>
            <table width="100%" cellspacing="0" cellpadding="3" class="table table-bordered">
                <thead>
                    <tr>
                        <th width="6%" style="border: 1px solid #000;">No</th>
                        <th style="text-align: left; border: 1px solid #000;">Tanggal Faktur</th>
                        <th style="text-align: left;border: 1px solid #000;">No. Faktur</th>
                        <th style="text-align: left;border: 1px solid #000;">Total Pembelian</th>
                        <th style="text-align: left;border: 1px solid #000;">Status Bayar</th>
                        {{-- <th style="text-align: left;border: 1px solid #000;">Gudang</th>
                        <th style="text-align: center;border: 1px solid #000;" colspan="2">Qty</th>
                        <th style="text-align: left;border: 1px solid #000;">Keterangan</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->sortByDesc('conv_tgl')->values() as $no => $value)
                        <tr>
                            <td width="6%" style="border: 1px solid #000;text-align: center;" valign="top" >{{$no + 1}}</td>
                            <td style="border: 1px solid #000;text-align: left;">{!! $value->tgl_faktur !!}</td>
                            <td style='border: 1px solid #000;text-align:left;'>{{$value->no_faktur}}</td>
                            <td style='border: 1px solid #000;text-align:left;'>{{$value->nominal}}</td>
                            <td style='border: 1px solid #000;text-align:left;'>{{$value->status_pembelian}}</td>
                            {{-- <td style='border: 1px solid #000;text-align:left;'>{{$value->nama_gudang}}</td> --}}
                            {{-- <td style='border: 1px solid #000;text-align:center;'>{{$value->stockinput}}</td>
                            <td style='border: 1px solid #000;text-align:left;'>{{$value->namasatuan}}</td>
                            <td style='border: 1px solid #000;text-align:left;'>{{$value->catatan}}</td> --}}
                        </tr>
                    @endforeach
                    @for($i=1;$i<2;$i++)
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            {{-- <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td> --}}

                        </tr>
                    @endfor
                </tbody>
            </table>
            <i>Printed date : <?php echo date("d M Y") ?> </i>
        </div>
    </body>
</html>
