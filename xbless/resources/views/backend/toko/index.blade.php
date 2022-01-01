@extends('layouts.layout')
@section('title', 'Toko')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Master Toko</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Master Toko</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        @can('brand.tambah')
          <a href="" class="btn btn-success"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Toko</th>
                                    <th>Nama</th>
                                    <th>Nik</th>
                                    <th>Telepon</th>
                                    <th>Alamat</th>
                                    <th class="text-right">Aksi</th>
                                </tr>
                            </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        {{-- <tr>
                            <td colspan="3">
                                <ul class="pagination float-right"></ul>
                            </td>
                        </tr> --}}
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
    var table,tabledata,table_index;
    $(document).ready(function(){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        table = $('#table1').DataTable({
            "processing": true,
            "serverSide": true,
        //    "stateSave"  : true,
        //    "deferRender": true,
            "pageLength": 25,
            "select" : true,
            "ajax":{
                "url": "{{ route("toko.getdata") }}",
                "dataType": "json",
                "type": "POST",
                data: function(d){
                    d._token="{{ csrf_token() }}";
                },
            },
            "columns":[
                {
                    "data": "no",
                    "orderable": false,
                },
                {
                    "data": "code",
                    "className" : "text-left",
                },
                {
                    "data": "name",
                    "className" : "text-left",
                },
                {
                    "data": "nik",
                    "className" : "text-left",
                },
                {
                    "data": "telp",
                    "className" : "text-left",
                },
                {
                    "data": "alamat",
                    "className": "text-left",
                },
                {
                    "data": "action",
                    "orderable": false,
                    "className": "text-center",
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
        // tabledata = $('#orderData').DataTable({
        //     dom     : 'lrtp',
        //     paging    : false,
        //     columnDefs: [ {
        //             "targets": 'no-sort',
        //             "orderable": false,
        //     } ]
        //     });
        //     $('#filter').click(function(){
        //         table.ajax.reload(null, false);

        //     });
        //     $('#refresh').click(function(){
        //         table.ajax.reload(null, false);
        //     });

        //     table.on('select', function ( e, dt, type, indexes ){
        //     table_index = indexes;
        //     var rowData = table.rows( indexes ).data().toArray();

        //     });
    });
</script>
@endpush
