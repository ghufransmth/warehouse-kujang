<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="{{ asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/font-awesome/css/font-awesome.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/animate.css')}}" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css')}}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cutive+Mono&display=swap" rel="stylesheet">
    <title>Document</title>
</head>
<body>
    <div class="card p-5" style="font-family: 'Cutive Mono', monospace;" id="section-to-print">
        <p class="font-weight-bold" style="font-size: medium;"> CV KUJANG MARINAS UTAMA</p>
        <div>
            <p>KP. CIKAROYA RT 010 RW 003 KECAMATAN CISAAT SUKABUMI DC. GUNUNG JAYA, KEC
                CISAAT, KAB SUKABUMI <br> No. Telepon : &nbsp; &nbsp;&nbsp; 0266216166</P>
        </div>
        <div class="row">
            <div class="col-sm-4 m-auto">
                <p>Salesman : {{ $penjualan->getsales->code }}-{{ $penjualan->getsales->nama }} <br>
                    Drive :
                </p>
            </div>
            <div class="col-sm-4 m-auto">
                <p>Kepada YTH. <br>
                    {{ $penjualan->gettoko->code }}/{{ $penjualan->name }} <br>
                    {{ $penjualan->gettoko->alamat }}
                </p>
            </div>
            <div class="col-sm-4 m-auto">
                <p> No. Faktur : {{ $penjualan->no_faktur }} <br>
                    Tgl. Faktur : {{ date('d/m/Y', strtotime($penjualan->tgl_faktur)) }} <br>
                    Tgl. JTempo : {{ date('d/m/Y', strtotime($penjualan->tgl_jatuh_tempo)) }} <br>
                    Jenis Bayar : {{ $jenis_pembayaran }}
                </p>
            </div>
        </div>
        <table class="table">
            <thead class="thead-white border">
                <tr class="text-center">
                    <th class="border">PCODE</th>
                    <th class="border">Nama Barang</th>
                    <th class="border">Harga Barang</th>
                    <th class="border">Qty (PCS)</th>
                    <th class="border">Jumlah Rp</th>
                    <th class="border">Ket.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detail_penjualan as $key => $value)
                    <tr>
                        <td>{{ $value->getproduct->kode_product }}</td>
                        <td>{{ $value->getproduct->nama }}</td>
                        <td class="text-right">{{ format_uang($value->harga_product) }}</td>
                        <td class="text-right">{{ $value->qty }}</td>
                        <td class="text-right">{{ format_uang($value->total_harga) }}</td>
                        <td></td>
                    </tr>
                @endforeach

            </tbody>
            <tfoot>
                <tr class="m-auto">
                    <td colspan="3" class="py-3 ">Total Barang : {{ count($detail_penjualan) }}</td>
                    <td class="text-right py-3">Jumlah Rp</td>
                    <td class="text-right py-3">
                        {{ format_uang($penjualan->total_harga) }}
                    </td>
                    <td></td>
                </tr>
                <tr class="m-auto">
                    <td colspan="3"></td>
                    <td class="text-right">Discount Rp <br> Nilai Faktur Rp</td>
                    <td class="text-right">{{ format_uang($penjualan->total_diskon) }} <br> {{ format_uang($penjualan->total_harga - $penjualan->total_diskon) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <p class="" style="font-size: medium;"> TERBILANG :{{strtoupper(terbilang($penjualan->total_harga - $penjualan->total_diskon))}}</p>
        <div>
            <p>* Ket satu dua tiga <br>
                Aut adipisci, saepe alias sequi consequunturdolores, <br>
                tempora doloribus molestiae sumque, error id aliquam harum sunt option
                officiis nobis quaerat asperiores possimus corrupti. Repellat.
            </P>
        </div>


    </div>
</body>
</html>

<script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
<script>
    $(document).ready(function(){
        window.print();
    })
</script>

