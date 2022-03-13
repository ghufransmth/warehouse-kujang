@extends('layouts.layout')
@section('title', 'Pembelian')
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
        <h2>Detail Pembelian</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Nota Pembelian</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        @can('purchaseorder.tambah')
        <a href="#" target="__blank" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Print"><span class="fa fa-pencil-square-o"></span>&nbsp; Print</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="card p-5" style="font-family: 'Cutive Mono', monospace;" id="section-to-print">
                        <p class="font-weight-bold" style="font-size: medium;">CV Kujang Marinas Utama</p>
                        <div>
                            <p>KP. CIKAROYA RT 010 RW 003 KECAMATAN CISAAT SUKABUMI DC. GUNUNG JAYA, KEC
                                CISAAT, KAB SUKABUMI <br> No. Telepon : &nbsp; &nbsp;&nbsp; 0266216166</P>
                        </div>
                        <div class="row">
                            <div class="col-sm-4 m-auto">
                                <p> Supplier : {{ $pembelian->getsupplier->nama }} <br>
                                    Gudang : {{ $pembelian->getgudang->name }}
                                </p>
                            </div>

                            <div class="col-sm-4 m-auto">
                                <p> No. Faktur  : {{ $pembelian->no_faktur }}<br>
                                    Tgl. Faktur :  {{ $pembelian->tgl_faktur }}<br>
                                    Tgl. JTempo :  {{ $pembelian->tgl_jatuh_tempo }}<br>
                                </p>
                            </div>
                        </div>
                        <table class="table">
                            <thead class="thead-white border">
                                <tr class="text-center">
                                    <th class="border">Kode</th>
                                    <th class="border">Nama Barang</th>
                                    <th class="border">Harga Barang</th>
                                    <th class="border">Qty (PCS)</th>
                                    <th class="border">Jumlah Rp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($detail_pembelian as $key=> $item)
                                    <tr>
                                        <td>{{ $item->getproduct->kode_product }}</td>
                                        <td>{{ $item->getproduct->nama }}</td>
                                        <td class="text-right">{{ format_uang($item->product_price) }}</td>
                                        <td class="text-right">{{ $item->qty }}</td>
                                        <td class="text-right">{{ format_uang($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="m-auto">
                                    <td colspan="3" class="py-3">Total Barang : {{ count($detail_pembelian) }}</td>
                                    <td class="text-right py-3">Jumlah </td>
                                    <td class="text-right py-3">{{ format_uang($pembelian->nominal) }}</td>
                                    <td></td>
                                </tr>
                                <tr class="m-auto">
                                    <td colspan="3"></td>
                                    <td class="text-right"> Nilai Faktur Rp</td>
                                    <td class="text-right">{{ format_uang($pembelian->nominal) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                        <p class="" style="font-size: medium;">TERBILANG : {{ strtoupper(terbilang($pembelian->nominal)) }}</p>
                        <div>
                            <p>* Ket satu dua tiga <br>
                                Aut adipisci, saepe alias sequi consequunturdolores, <br>
                                tempora doloribus molestiae sumque, error id aliquam harum sunt option
                                officiis nobis quaerat asperiores possimus corrupti. Repellat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    function cetak(){
        window.print();
    }
</script>
@endpush
