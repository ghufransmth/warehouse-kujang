@extends('layouts.layout')
@section('title', 'LAPORAN PENJUALAN')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>DETAIL BARANG MASUK</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>DETAIL BARANG MASUK</a>
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

                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Kode Product</th>
                                    <th>Tgl Faktur</th>
                                    <th>Nama Product</th>
                                    <th>Total Harga</th>
                                    <th>Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            {{-- <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot> --}}
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
        table = $('#table1').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ordering: true,
            select: true,
            "ajax":{
                "url": "{{ route("reportbarangmasuk.getdata") }}",
                "dataType": "json",
                "type": "POST",
                data: function( d ){
                    d._token= "{{ csrf_token() }}";
                    }
                },
                "columns":[
                    {
                        "data": 'nomor',
                        "orderable": false,
                    },
                    {
                        "data": "kode_product",
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
                        "data": 'total_pembelian',
                        "orderable": false,
                    },
                    {
                        "data": 'status_bayar',
                        "orderable": false,
                    },
                    // {
                    //     "data": "action",
                    //     "orderable": false,
                    // },
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
                }
            });
    })

    $(document.body).on("keydown", function(e){
         ele = document.activeElement;
           if(e.keyCode==38){
             table.row(table_index).deselect();
             table.row(table_index-1).select();
           }
           else if(e.keyCode==40){

             table.row(table_index).deselect();
             table.rows(parseInt(table_index)+1).select();
             console.log(parseInt(table_index)+1);

           }
           else if(e.keyCode==13){

           }
       });


    $('#date1 .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
@endpush
