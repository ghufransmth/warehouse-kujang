@extends('layouts.layout')
@section('title', 'Retur Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Retur Pembelian</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('manage.beranda') }}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Retur Pembelian</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped">
                            <thead class="text-white text-center bg-primary">
                                <tr>
                                    <th>No</th>
                                    <th>No Faktur</th>
                                    <th>Tgl Faktur</th>
                                    <th>Nominal Faktur</th>
                                    <th class="text-center">Aksi</th>
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
<script type="text/javascript">
    var table,tabledata,table_index;
    $(document).ready(function(){
        $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
           });
        table = $('#table1').DataTable({
           "processing": true,
           "serverSide": true,
           "pageLength": 10,
           "select" : true,
           "responsive": true,
           "stateSave": true,
        //    "dom": '<html5buttons"B>lTfgitp',
           "ajax":{
                "url": "{{ route("retur_pembelian.getdata") }}",
                "dataType": "json",
                "type": "POST",
                data: function ( d ) {
                    d._token= "{{csrf_token()}}";
                }
           },
           "columns":[
            {
                 "data": "no",
                 "orderable" : false,
               },

               { "data": "no_faktur",
                 "className" : "text-left",
               },
               {
                "data": "tgl_faktur",
                "className": "text-left",
               },
               {
                "data": "nominal",
                "className": "text-right",
               },
               {
                "data": "action",
                "className": "text-center",
               },
            ],
            // buttons: [
            //         {
            //             extend: 'copy',
            //             exportOptions: {
            //                 orthogonal: 'export'
            //             },
            //             header: true,
            //             footer: true,
            //             className: 'btn btn-outline btn-default btn-lg',
            //         },
            //         {
            //             extend: 'excel',
            //             exportOptions: {
            //                 orthogonal: 'export'
            //             },
            //             header: true,
            //             footer: true,
            //             className: 'btn btn-block bg-primary text-white',
            //         }
            //     ],
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
    });

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
</script>
@endpush
