<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style-print.css')}}" rel="stylesheet">
    <title>Print Barang Masuk</title>
    <style type="text/css" media="print">
        /* @page { size: landscape; } */

        /* @page { size: landscape; } */
        @page {

            size: auto;

            margin: 0;

            margin-left: 0.1cm;
            margin-top: 1cm;

        }

        body {

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
                            <b>LAPORAN BARANG MASUK </b>
                        </h3>
                        <h4 style="margin-top:-4mm;text-align:center; vertical-align: middle"> <br>
                            {{-- <b>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} -
                            {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</b> </h4> --}}
                </div>
            </div>
            <br />
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-print" style="font-size: 15px !important" width="100%" cellspacing='0'
                        border="1">
                        {{-- <table border='1' width='100%' cellspacing='0'> --}}
                        <thead>
                            <tr class="two-strips-top">
                                <th style="text-align : center; display: table-cell; vertical-align: middle;">No</th>
                                <th style="text-align : center; display: table-cell; vertical-align: middle;">Tanggal
                                    Faktur</th>
                                <th style="text-align : center; display: table-cell; vertical-align: middle;">No.
                                    Faktur</th>
                                <th style="text-align : center; display: table-cell; vertical-align: middle;">Total
                                    Pembelian</th>
                                <th style="text-align : center; display: table-cell; vertical-align: middle;">Status Bayar
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data as $key => $value)
                                <tr class="two-strips-bottom">
                                    <td>{{ $value->no }}</td>
                                    <td class="text-center">{{ $value->tgl_faktur }}</td>
                                    <td class="text-center">{{ $value->no_faktur }}</td>
                                    <td class="text-right">{{ $value->total_pembelian }}</td>
                                    <td class="text-center">{{ $value->status_bayar }}</td>
                                </tr>
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
