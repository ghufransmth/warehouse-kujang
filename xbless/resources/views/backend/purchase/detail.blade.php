@extends('layouts.layout')

@section('title', 'Purchase Order')

@section('content')
<style>
    .swal2-container {
        z-index: 100000 !important;
    }
    @media print {
        body * {
            visibility: hidden;
        }
        #section-to-print, #section-to-print * {
            visibility: visible;
        }
        #section-to-print {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Nota Penjualan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Nota Penjualan </a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        {{-- <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button> --}}
        @can('purchaseorder.tambah')
        <a href="{{ route('purchaseorder.cetak', $enc_id) }}" target="__blank" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Print"><span class="fa fa-pencil-square-o"></span>&nbsp; Print</a>
            {{-- <button onclick="cetak()" class="btn btn-success"> Print</button> --}}
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    {{-- <input type="hidden" name="_token" value="{{ csrf_token() }}"> --}}
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
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
                                    <th class="border" rowspan="2">PCODE</th>
                                    <th class="border" rowspan="2">Nama Barang</th>
                                    <th class="border" rowspan="2">Harga Barang</th>
                                    <th class="border" colspan="3">Qty </th>
                                    <th class="border" rowspan="2">Jumlah Rp</th>
                                    <th class="border" rowspan="2">Ket.</th>
                                </tr>
                                <tr class="text-center">
                                    <th class="border" width="10%">K</th>
                                    <th class="border" width="10%">L</th>
                                    <th class="border" width="10%">P</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php
                                function format_uang($angka){

                                  $hasil_rupiah = "Rp " . number_format($angka,2,',','.');
                                  return $hasil_rupiah;

                                }

                                function penyebut($nilai) {
                              		$nilai = abs($nilai);
                              		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
                              		$temp = "";
                              		if ($nilai < 12) {
                              			$temp = " ". $huruf[$nilai];
                              		} else if ($nilai <20) {
                              			$temp = penyebut($nilai - 10). " belas";
                              		} else if ($nilai < 100) {
                              			$temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
                              		} else if ($nilai < 200) {
                              			$temp = " seratus" . penyebut($nilai - 100);
                              		} else if ($nilai < 1000) {
                              			$temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
                              		} else if ($nilai < 2000) {
                              			$temp = " seribu" . penyebut($nilai - 1000);
                              		} else if ($nilai < 1000000) {
                              			$temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
                              		} else if ($nilai < 1000000000) {
                              			$temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
                              		} else if ($nilai < 1000000000000) {
                              			$temp = penyebut($nilai/1000000000) . " milyar" . penyebut(fmod($nilai,1000000000));
                              		} else if ($nilai < 1000000000000000) {
                              			$temp = penyebut($nilai/1000000000000) . " trilyun" . penyebut(fmod($nilai,1000000000000));
                              		}
                              		return $temp;
                              	}

                              	function terbilang($nilai) {
                              		if($nilai<0) {
                              			$hasil = "minus ". trim(penyebut($nilai));
                              		} else {
                              			$hasil = trim(penyebut($nilai));
                              		}
                              		return $hasil;
                              	}

                                function cek_qty_karton($angka){
                                    $hasil = 0;
                                    //Karton
                                    if($angka > 49){
                                        $hasil = floor($angka / 50);
                                    }

                                    return $hasil;
                                }

                                function cek_qty_lusin($angka){
                                    $hasil = 0;
                                    $sisa = 0;
                                    //Lusin
                                    if($angka > 49){
                                        $sisa = $angka % 50;
                                        if($sisa > 11){
                                            $hasil = floor($sisa / 12);
                                        }
                                    }else{
                                       $hasil = floor($angka / 12);
                                    }

                                    return $hasil;
                                }

                                function cek_qty_pcs($angka){
                                    $hasil = 0;
                                    $sisa = 0;
                                    $sisa2 = 0;
                                    //Pcs
                                    if($angka > 49){
                                        $sisa = $angka % 50;
                                        if($sisa > 11){
                                            $sisa2 = $sisa % 12;
                                            $hasil = $sisa2;
                                        }
                                    }else if($angka > 11 && $angka < 50){
                                        $sisa = $angka % 12;
                                        $hasil = $sisa;
                                    }else{
                                        $hasil = $angka;
                                    }

                                    return $hasil;
                                }
                              ;?>
                                @foreach($detail_penjualan as $key => $value)
                                    <tr>
                                        <td>{{ $value->getproduct->kode_product }}</td>
                                        <td>{{ $value->getproduct->nama }}</td>
                                        <td class="text-right">{{ format_uang($value->harga_product) }} </td>
                                        <td class="text-right">{{ cek_qty_karton($value->qty) }}</td>
                                        <td class="text-right">{{ cek_qty_lusin($value->qty) }}</td>
                                        <td class="text-right">{{ cek_qty_pcs($value->qty) }}</td>
                                        <td class="text-right">{{ format_uang($value->total_harga) }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach

                            </tbody>
                            <tfoot>
                                <tr class="m-auto">
                                    <td colspan="5" class="py-3 ">Total Barang : {{ count($detail_penjualan) }}</td>
                                    <td class="text-right py-3">Jumlah Rp</td>
                                    <td class="text-right py-3">
                                      {{ format_uang($penjualan->total_harga) }}
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="m-auto">
                                    <td colspan="5"></td>
                                    <td class="text-right">Discount Rp <br> Nilai Faktur Rp</td>
                                    <td class="text-right">{{ format_uang($penjualan->total_diskon) }}<br>{{ format_uang($penjualan->total_harga - $penjualan->total_diskon) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <p class="" style="font-size: medium;"> TERBILANG : {{ strtoupper(terbilang($penjualan->total_harga - $penjualan->total_diskon)) }} RUPIAH</p>
                        <div>
                            <p>* Ket satu dua tiga <br>
                                Aut adipisci, saepe alias sequi consequunturdolores, <br>
                                tempora doloribus molestiae sumque, error id aliquam harum sunt option
                                officiis nobis quaerat asperiores possimus corrupti. Repellat.
                            </P>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail PO -->
    {{-- @include('backend.purchase.detail') --}}




</div>
@endsection
@push('scripts')
<script>
    function cetak(){
        window.print();
    }
</script>
@endpush
