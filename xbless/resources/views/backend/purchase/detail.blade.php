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
        <a href="{{ route('purchaseorder.cetak', $enc_id) }}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
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
                                    Jenis Bayar : $0
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
                                        <td class="text-right">{{ $value->harga_product }}</td>
                                        <td class="text-right">{{ $value->qty }}</td>
                                        <td class="text-right">{{ $value->total_harga }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach

                            </tbody>
                            <tfoot>
                                <tr class="m-auto">
                                    <td colspan="3" class="py-3 ">Total Barang : {{ count($detail_penjualan) }}</td>
                                    <td class="text-right py-3">Jumlah Rp</td>
                                    <td class="text-right py-3">
                                        {{ $penjualan->total_harga }}
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
