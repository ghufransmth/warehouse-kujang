@extends('layouts.layout')
@section('title', 'Laporan Penjualan')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Laporan Penjualan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Laporan Penjualan</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
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
                                            class="fa fa-file-pdf-o"></span>
                                        Export PDF</button>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <button class="btn btn-primary" type="button" id="ExportExcel"><span
                                            class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span
                                            class="fa fa-print"></span>
                                        Print</button>&nbsp;
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="d-flex pl-4 pt-4">
                        {{-- <label class="font-normal">Range Tanggal</label> --}}
                        <div class="form-group" id="date1">
                            <div class="input-daterange input-group" id="datepicker">
                                <span class="input-group-addon bg-primary">
                                    <i class="fa fa-calendar m-auto px-2"></i>
                                </span>
                                <input type="text" class="form-control-sm form-control" name="start"
                                    value="01-01-2022" id="periode_start" />
                                <span class="input-group-addon bg-primary px-2">to </span>
                                <input type="text" class="form-control-sm form-control" name="end" value="01-02-2022" id="periode_end" />
                            </div>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="example" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Product</th>
                                    <th>Stock Product</th>
                                    <th>Harga Product</th>
                                    <th>Tipe Satuan</th>
                                    <th>Qty</th>
                                    <th>Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Sabun</td>
                                    <td>1000</td>
                                    <td>5.000</td>
                                    <td>2</td>
                                    <td>lusin</td>
                                    <td>50000</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Sabun</td>
                                    <td>1000</td>
                                    <td>5.000</td>
                                    <td>2</td>
                                    <td>lusin</td>
                                    <td>50000</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>Sabun</td>
                                    <td>1000</td>
                                    <td>5.000</td>
                                    <td>2</td>
                                    <td>lusin</td>
                                    <td>50000</td>
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
    var table,tabledata,table_index,tableproduct;
    $(document).ready(function(){
            $.ajaxSetup({
                headers:{ "X-CSRF-Token": $("meta[name=csrf-token]").attr("content") }
            });
            table = $('#example').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ordering: true,
                select: true,
                // "bFilter":false,
                "ajax":{
                    "url": "{{ route("reportpenjualan.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function( d ){
                        d._token= "{{ csrf_token() }}";
                        }
                    },
                    "columns": [
                        {
                            "data": 'nomor',
                            "orderable": false,

                        },
                        {
                            "data": "tgl_faktur",
                            "orderable": false,
                        },
                        {
                            "data": "no_faktur",
                            "orderable": false,
                        },
                        {
                            "data": 'keterangan',
                            "orderable": false,
                        },
                    ],
                    responsive: true,
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
                    // }
                }
            });
        })





       $('.formatTgl').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
@endpush
