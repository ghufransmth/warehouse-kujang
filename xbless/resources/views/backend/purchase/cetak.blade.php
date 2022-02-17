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
                <p>Salesman : 25-sales2 <br>
                    Drive :
                </p>
            </div>
            <div class="col-sm-4 m-auto">
                <p>Kepada YTH. <br>
                    15163312320226001101083070/4 BERSAUDARA <br>
                    CIUTARA
                </p>
            </div>
            <div class="col-sm-4 m-auto">
                <p> No. Faktur : 210000000934 <br>
                    Tgl. Faktur : 27/12/2021 <br>
                    Tgl. JTempo : 01/01/2021 <br>
                    Jenis Bayar : $0
                </p>
            </div>
        </div>
        <table class="table">
            <thead class="thead-white border">
                <tr class="text-center">
                    <th class="border">PCODE</th>
                    <th class="border">Nama Barang</th>
                    <th class="border">Harga/LSN</th>
                    <th class="border">KRT.LSN.SAT</th>
                    <th class="border">Jumlah Rp</th>
                    <th class="border">Ket.</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>2345</td>
                    <td>testingtesting</td>
                    <td class="text-right">240.000</td>
                    <td class="text-right">1.0.0</td>
                    <td class="text-right">20.000</td>
                    <td></td>
                </tr>
                <tr>
                    <td>2345</td>
                    <td>testingtesting</td>
                    <td class="text-right">240.000</td>
                    <td class="text-right">1.0.0</td>
                    <td class="text-right">20.000</td>
                    <td></td>
                </tr>
                <tr>
                    <td>2345</td>
                    <td>testingtesting</td>
                    <td class="text-right">240.000</td>
                    <td class="text-right">1.0.0</td>
                    <td class="text-right">20.000</td>
                    <td></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="m-auto">
                    <td colspan="3" class="py-3 ">Total Karton Utuh : 1</td>
                    <td class="text-right py-3">Jumlah Rp</td>
                    <td class="text-right py-3">
                        20.000
                    </td>
                    <td></td>
                </tr>
                <tr class="m-auto">
                    <td colspan="3"></td>
                    <td class="text-right">Discount Rp <br> Nilai Faktur Rp</td>
                    <td class="text-right">0 <br> 20.000</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <p class="font-weight-bold" style="font-size: medium;"> TERBILANG : DUA PULUH RIBU
            RUPIAH</p>
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

