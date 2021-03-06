@extends('layouts.layout')

@section('title', 'Purchase Order')

@section('content')
<style>
    .swal2-container {
        z-index: 100000 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Penjualan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Penjualan</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('purchaseorder.tambah')
        <a href="{{ route('purchaseorder.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <form id="submitData" name="submitData">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Toko : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="toko" name="toko">
                                    <option value="">Semua Toko</option>
                                    @foreach($toko as $key => $tko)
                                    <option value="{{ $tko->id }}">{{ $tko->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">Sales : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="sales" name="sales">
                                    <option value="">Semua Sales</option>
                                    @foreach($sales as $key => $sles)
                                    <option value="{{ $sles->id }}">{{ $sles->nama }}</option>

                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1 error-text">
                                <button class="btn btn-success" id="search-data" type="button"><span
                                        class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </form>

                    <input type="hidden" class="form-control" id="type" value="{{session('type')}}" />
                    <input type="hidden" class="form-control" id="type_gudang" value="{{session('type_gudang')}}" />

                    <div class="table-responsive">
                        <table id="table1" class="table display table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="10px;">No</th>
                                    <th>No Faktur</th>
                                    <th>Sales</th>
                                    <th>Toko</th>
                                    <th>Tgl Transaksi</th>
                                    <th>Tgl Jatuh Tempo</th>
                                    <th>Tgl Lunas</th>
                                    <th>Status Pembayaran</th>
                                    <th>Total Harga</th>
                                    <th>Total Diskon</th>
                                    <th>Created By</th>
                                    <th class="text-center" width="11%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.js"
    integrity="sha512-w5udtBztYTK9p9QHQR8R1aq8ke+YVrYoGltOdw9aDt6HvtwqHOdUHluU67lZWv0SddTHReTydoq9Mn+X/bRBcQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    var table,tabledata,table_index;
        $('.formatTgl').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: "dd-mm-yyyy"
        });
        $(document).ready(function(){
            $(".select2").select2({allowClear: true});

            $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
            });

            table= $('#table1').DataTable({
                "processing": true,
                "serverSide": true,
                "stateSave"  : true,
                "pageLength": 25,
                "select" : true,
                "responsive": true,
                "dom": '<"html5">lftip',
                "ajax":{
                            "url": "{{ route("purchaseorder.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                                data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_toko = $('#toko').val();
                                d.filter_sales     = $('#sales').val();
                                d.type              = $('#type').val();

                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },
                    { "data": "no_faktur", "orderable" : false, },
                    { "data": "sales", "orderable" : false, },
                    { "data": "toko", "orderable" : false, },
                    { "data": "tgl_transaksi", "orderable" : false, },
                    { "data": "tgl_jatuh_tempo", "orderable" : false, },
                    { "data": "tgl_lunas", "orderable" : false, },
                    { "data": "status_pembayaran", "orderable" : false, },
                    { "data": "total_harga", "orderable" : false, },
                    { "data": "total_diskon", "orderable" : false, },
                    { "data": "created_by", "orderable" : false, },

                    { "data" : "aksi",
                        "orderable" : false,
                        "className" : "text-center",
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
                }
            });

            tabledata = $('#orderData').DataTable({
                dom     : 'lrtp',
                paging    : false,
                columnDefs: [ {
                        "targets": 'no-sort',
                        "orderable": false,
                } ]
            });

            $('#search-data').click(function(){
                table.ajax.reload(null, false);
            });

            $('#refresh').click(function(){
                table.ajax.reload(null, false);
            });

            table.on('select', function ( e, dt, type, indexes ){
                table_index = indexes;
                var rowData = table.rows( indexes ).data().toArray();
            });
        });


</script>
<script>
    function change_type(type){
        $('#type').val(type);
        table.ajax.reload(null, false);
    }
function approve(id){
    $.ajax({
        type: 'GET',
        url : "{{route('purchaseorder.approve', [null])}}/"+id,
        dataType: "json",
        beforeSend: function () {
        Swal.showLoading();
        },
        success: function(data){
            if (data.success) {
                Swal.fire('Yes',data.message,'success');
                table.ajax.reload(null, false);
            } else {
                Swal.fire('Ups',data.message,'info');
            }
        },
        complete: function () {
            Swal.hideLoading();
            $('#simpan').removeClass("disabled");
        },
        error: function(data){
            $('#simpan').removeClass("disabled");
            Swal.hideLoading();
            Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
        }
    });
}
function reject(id){
    $.ajax({
        type: 'GET',
        url : "{{route('purchaseorder.reject', [null])}}/"+id,
        dataType: "json",
        beforeSend: function () {
        Swal.showLoading();
        },
        success: function(data){
            if (data.success) {
                Swal.fire('Yes',data.message,'success');
                table.ajax.reload(null, false);
            } else {
                Swal.fire('Ups',data.message,'info');
            }
        },
        complete: function () {
            Swal.hideLoading();
            $('#simpan').removeClass("disabled");
        },
        error: function(data){
            $('#simpan').removeClass("disabled");
            Swal.hideLoading();
            Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
        }
    });
}






</script>
@endpush
