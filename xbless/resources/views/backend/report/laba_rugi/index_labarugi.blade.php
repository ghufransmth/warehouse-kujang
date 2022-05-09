@extends('layouts.layout')
@section('title', 'Laporan Laba Rugi')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Laporan Laba Rugi</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Laporan Laba Rugi</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-3">
        <div class="title-action">
            <div class="row">
                <div class="col-lg-6">

                </div>
            </div>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="d-flex justify-content-between">
                        <div class="form-group" id="date1">
                            <div class="input-daterange input-group" id="datepicker">
                                <span class="input-group-addon bg-primary">
                                    <i class="fa fa-calendar m-auto px-2"></i>
                                </span>
                                <input type="text" class="form-control-sm form-control" name="start"
                                    value="01-01-2022" />
                                <span class="input-group-addon bg-primary px-2">to </span>
                                <input type="text" class="form-control-sm form-control" name="end" value="01-02-2022" />
                            </div>
                        </div>
                        <form id="submitData" name="submitData" class="text-right">
                            <div class="pr-4">
                                <div class="d-flex flex-row-reverse row">
                                    <div class="col-xs-3">
                                        <button class="btn btn-danger" type="button" id="ExportPdf"><span
                                                class="fa fa-file-pdf-o"></span> Export
                                            PDF</button>&nbsp;
                                    </div>
                                    <div class="col-xs-3">
                                        <button class="btn btn-secondary" type="button" id="Print"><span
                                                class="fa fa-print"></span> Print</button>&nbsp;
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="card">
                        <table class="m-5">
                            {{-- <table class="m-5 table-bordered"> --}}
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center">CV.KUJANG MARINAS UTAMA - GT</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-center">LAPORAN LABA RUGI</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-center">PER - 31 Desember 2021</td>
                                </tr>
                                <tr class="border-bottom">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">31/12/2021</td>
                                </tr>
                                <tr>
                                    <td width="40%" class="pt-3">Penjualan Bruto</td>
                                    <td></td>
                                    <td>Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%">- TPR Penjualan</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%">-Potongan penjualan</td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%"></td>
                                    <td>Rp. -</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                <tr>
                                    <td width="40%">-PPN Penjualan Bruto</td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%">Jumlah Penjualan Netto</td>
                                    <td></td>
                                    <td></td>
                                    <td>Rp. -</td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">Return Barang Bruto</td>
                                    <td class="text-left"></td>
                                    <td class="text-left">Rp. -</td>
                                    <td class="text-left"></td>
                                </tr>
                                <tr>
                                    <td width="40%">- TPR </td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%">-Potongan retur</td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td width="40%"></td>
                                    <td>Rp. -</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                <tr>
                                <tr>
                                    <td class="pt-3" width="40%">- PPN Retur</td>
                                    <td></td>
                                    <td class="border-bottom pt-3">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">Penjualan Bersih</td>
                                    <td></td>
                                    <td></td>
                                    <td>Rp. -</td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%" style="text-decoration: underline;">Harga Pokok
                                        Penjualan</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">Persediaan Awal</td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">Persediaan Bruto</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">PPN Pembelian</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">Return Pembelian</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3" width="40%">PPN Return Pembelian</td>
                                    <td>Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>Rp. </td>
                                    <td></td>
                                    <td></td>
                                <tr>
                                    <td>Pembelian Bersih</td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3">Barang yang tersedia untuk dijual</td>
                                    <td></td>
                                    <td>Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3">Persediaan Akhir</td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-3">Harga Pokok Penjualan</td>
                                    <td></td>
                                    <td></td>
                                    <td class="border-bottom">Rp. -</td>
                                </tr>
                                <tr>
                                    <td class="pt-3">Laba Kotor</td>
                                    <td></td>
                                    <td></td>
                                    <td>Rp. -</td>
                                </tr>
                                @if(isset($result))
                                    @foreach($result as $key => $value)
                                        <tr>
                                            <td class="pt-5" style="text-decoration: underline;"><strong> {{$value->name}}</strong>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        @if(isset($value->detail))
                                            @foreach($value->detail as $key => $detail)
                                                <tr>
                                                    <td class=""><span>01</span> {{$detail->name}}</td>
                                                    <td>Rp. -</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endforeach
                                @endif
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td class="border-top">Rp. -</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="pt-5 m-auto"> Laba Usaha</td>
                                    <td></td>
                                    <td></td>
                                    <td class="pt-5">Rp. -</td>
                                </tr>
                                <tr>
                                    <td class=" m-auto"> Administrasi Bank</td>
                                    <td class="">Rp. </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class=" m-auto"> Pengalihan Dana ke Rek.Bp.Andri</td>
                                    <td class="">Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class=" m-auto"> Pengalihan Dana ke Rek.KMK</td>
                                    <td class="">Rp. -</td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class=" m-auto"> Laba Bersih sebelum Pajak</td>
                                    <td></td>
                                    <td></td>
                                    <td class="">Rp. -</td>
                                </tr>
                                <tr>
                                    <td class=" m-auto"> Pajak Penghasilan pasal 21</td>
                                    <td class="">Rp. </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr class=" border-top border-bottom">
                                    <td class=" m-auto" colspan="4"> <strong>Laba Bersih Setelah Dipotong Pajak</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    $('#table1').DataTable({
        pageLength: 10,
        responsive: true,
        dom: '<"html5buttons"B>lTfgitp',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari data",
            emptyTable: "Belum ada data",
            info: "Menampilkan data _START_ sampai _END_ dari _MAX_ data.",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data.",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            loadingRecords: "Loading...",
            processing: "Mencari...",
            paginate: {
            "first": "Pertama",
            "last": "Terakhir",
            "next": "Sesudah",
            "previous": "Sebelum"
            },
        },
        buttons: [
        ],
        });
    $('#date1 .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: "dd-mm-yyyy"
    });

</script>

@endpush
