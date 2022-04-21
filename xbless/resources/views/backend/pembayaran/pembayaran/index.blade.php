@extends('layouts.layout')
@section('title', 'Pembayaran')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-6">
        <h2>Pembayaran</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Keuangan</a>
            </li>
        </ol>
    </div>
    <div class="col-sm-6">
        <div class="title-action">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group" id="data_5">
                        <label class="font-normal">Range select</label>
                        <div class="input-daterange input-group" id="datepicker">
                            <span class="input-group-addon px-3 bg-white border"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control-sm form-control" name="start" id="start" value="{{ $periode_start }}">
                            <span class="input-group-addon px-3 bg-primary">to</span>
                            <input type="text" class="form-control-sm form-control" name="end" id="end" value="{{ $periode_end }}">
                            <span class="input-group-addon px-3 bg-white  border"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="font-normal">Salesman</label>
                        <div>
                            <select class="select2_salesman form-control" id="sales">
                                <option value="">Select Sales</option>
                                @if(isset($sales))
                                    @foreach($sales as $key => $value)
                                        <option value="{{ $value->id }}">{{ $value->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>Pembayaran</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_pembayaran" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Nomor Faktur</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Outlet</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Nomor Faktur</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Outlet</th>
                                    <th>Sales</th>
                                    <th>Status</th>
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
    var table;
    table = $(document).ready(function () {
        $(".select2_salesman").select2();
        table = $('#table_pembayaran').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "select" : true,
            "responsive": true,
            "stateSave"  : true,
            "dom": '<"html5">lftip',
            "ajax":{
                    "url": "{{ route("transaksi.pembayaran.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                        d._token= "{{csrf_token()}}";
                        d.periode_start = $('#start').val()
                        d.periode_end = $('#end').val()
                        d.sales = $('#sales option:selected').val()
                    }
                },
            "columns": [
                {"data": "no"},
                {"data": "no_faktur"},
                {"data": "tgl_faktur"},
                {"data": "toko_name"},
                {"data": "sales_name"},
                {"data": "status"},
            ],
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
    });

    $(document).on('change', '#sales', function(){
        table.ajax.reload(null, false);
    })

    $(document).on('change', '#start', function(){
        var end = $('#end').val()
        if(end != ''){
            table.ajax.reload(null, false);
        }
    })

    $(document).on('change', '#end', function(){
        var end = $('#start').val()
        if(end != ''){
            table.ajax.reload(null, false);
        }
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
