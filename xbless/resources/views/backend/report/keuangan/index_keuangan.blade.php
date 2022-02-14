@extends('layouts.layout')
@section('title', 'LAPORAN KEUANGAN')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>LAPORAN KEUANGAN</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>LAPORAN KEUANGAN</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-4">
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <form id="submitData" name="submitData" class="text-right">
                        <div class="pr-4">
                            <div class="d-flex flex-row-reverse row">
                                <div class="col-xs-3">
                                    <button class="btn btn-danger" type="button" id="ExportPdf"><span
                                            class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <button class="btn btn-primary" type="button" id="ExportExcel"><span
                                            class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span
                                            class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex pl-4 pt-4">
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
                        {{-- <label class="font-normal">Range Tanggal</label> --}}
                        {{-- <div class="form-group">
                            <div>
                                <select class="select2_salesman form-control" id="sales">
                                    <option value="">Select Sales</option>

                                </select>
                            </div>
                        </div> --}}
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Tanggal Kirim</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status Bayar</th>
                                    <th>Cara Bayar</th>
                                    <th>Option</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>01-02-2022</td>
                                    <td>A11</td>
                                    <td>01-02-2022</td>
                                    <td>Jaya Abadi</td>
                                    <td>Yanto</td>
                                    <td>-</td>
                                    <td>Lunas</td>
                                    <td>kontan</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>01-02-2022</td>
                                    <td>A11</td>
                                    <td>01-02-2022</td>
                                    <td>Jaya Abadi</td>
                                    <td>Yanto</td>
                                    <td>-</td>
                                    <td>Lunas</td>
                                    <td>kontan</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>01-02-2022</td>
                                    <td>A11</td>
                                    <td>01-02-2022</td>
                                    <td>Jaya Abadi</td>
                                    <td>Yanto</td>
                                    <td>-</td>
                                    <td>Lunas</td>
                                    <td>kontan</td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
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
