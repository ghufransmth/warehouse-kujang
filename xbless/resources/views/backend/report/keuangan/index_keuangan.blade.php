@extends('layouts.layout')
@section('title', 'Laporan Keuangan')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
    .pagination{
        display: none;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Laporan Keuangan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Laporan Keuangan</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-4">
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="d-flex pl-4 pt-4">
                        <div class="form-group" id="date1">
                        <div class="input-daterange input-group" id="datepicker">
                            <span class="input-group-addon px-3 bg-white border"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control-sm form-control" name="start" id="start" value="{{ $periode_start }}">
                            <span class="input-group-addon px-3 bg-primary">to</span>
                            <input type="text" class="form-control-sm form-control" name="end" id="end" value="{{ $periode_end }}">
                            <span class="input-group-addon px-3 bg-white  border"><i class="fa fa-calendar"></i></span>
                        </div>
                        </div>

                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="table1" class="table" style="overflow-x: auto;">
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
                                    <th>Total</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Tanggal Kirim</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Jatuh Tempo</th>
                                    <th>Status Bayar</th>
                                    <th>Total</th>
                                    <th>Keterangan</th>
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
    var table
    table = $('#table1').DataTable({
        "processing": true,
        "serverSide": true,
        "pageLength": 25,
        "select" : true,
        "responsive": true,
        "stateSave"  : true,
        "dom": '<"html5">lftip',
        "ajax":{
                "url": "{{ route("reportkeuangan.getdata") }}",
                "dataType": "json",
                "type": "POST",
                data: function ( d ) {
                    d._token= "{{csrf_token()}}";
                    d.periode_start = $('#start').val()
                    d.periode_end = $('#end').val()
                }
            },
        "columns": [
            { "data": "no"},
            { "data": "tanggal_faktur"},
            { "data": "no_faktur"},
            { "data": "tanggal_kirim"},
            { "data": "toko_name"},
            { "data": "sales_name"},
            { "data": "tanggal_tempo"},
            { "data": "status"},
            { "data": "total",
                "className": "text-right"
            },
            {"data":"note"}
        ],
        buttons: [
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Cari data",
            emptyTable: "Belum ada data",
            info: "",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data.",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            loadingRecords: "Loading...",
            processing: "Mencari...",
        },
    });
    $('#date1 .input-daterange').datepicker({
        keyboardNavigation: false,
        forceParse: false,
        autoclose: true,
        format: "dd-mm-yyyy"
    });

    $(document).on('change','#start', function(){
        if($('#end').val() != null){
            table.ajax.reload(null, false);
        }
    })

    $(document).on('change','#end', function(){
        if($('#start').val() != null){
            table.ajax.reload(null, false);
        }
    })
</script>
<script>
    $(document).on('click','#Print', function(){
        window.open('{{route('reportkeuangan.print',[null,null])}}/'+$('#start').val()+'/'+$('#end').val(), '_blank');
    })
</script>
@endpush
