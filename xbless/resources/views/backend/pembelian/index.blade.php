@extends('layouts.layout')
@section('title', 'Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-9">
        <h2>Pembelian</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('pembelian.index')}}">Pembelian</a>
            </li>

        </ol>
    </div>
    <div class="col-lg-3">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('pembelian.tambah')
        <a href="{{ route('pembelian.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Tambah Data"><span class="fa fa-pencil-square-o"></span> &nbsp; Tambah</a>
        @endcan
        @can('pembelian_import.import')
        <a href="{{ route('pembelian_import.import')}}" class="btn btn-warning" data-toggle="tooltip" data-placement="top"
            title="Import Data">Import &nbsp; <span class="fa fa-file-excel-o"></span></a>
        @endcan
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
                                    <th width="10px;">No</th>
                                    <th>No Faktur</th>
                                    <th>Tgl Faktur</th>
                                    <th>Keterangan</th>
                                    <th>Created_user</th>
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
           table= $('#table1').DataTable({
           "processing": true,
           "serverSide": true,
           "pageLength": 25,
           "select" : true,
           "responsive": true,
           "stateSave"  : true,
           "dom": '<"html5buttons"B>lTfgitp',
           "ajax":{
                    "url": "{{ route("pembelian.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                      d._token= "{{csrf_token()}}";
                    }
                  },

           "columns": [

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
                "data": "keterangan",
                "className": "text-left",
               },
               { "data": "created_user",
                 "className" : "text-left",
               },
               { "data" : "action",
                 "orderable" : false,
                 "className" : "text-center",
               },
           ],
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
         $('#filter').click(function(){
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

      function deleteSatuan(e,enc_id){
        var token = '{{ csrf_token() }}';
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data akan terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya",
            cancelButtonText:"Batal",
            confirmButtonColor: "#ec6c62",
            closeOnConfirm: false
        }).then(function(result) {
            console.log(result)
            if (result.value) {
                $.ajaxSetup({
                    headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
                });
                $.ajax({
                    type: 'delete',
                    url: '{{route("pembelian.hapus",[null])}}/' + enc_id,
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data){
                    console.log(data)
                    if (data.status == 'success') {
                        Swal.fire('Yes',data.message,'success');
                        table.ajax.reload(null, true);
                     }else{
                       Swal.fire('Ups',data.message,'info');
                     }
                },
                error: function(data){
                    console.log(data);
                    Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                }});
            }
        });
      }

      function hapus(e,enc_id){
        var token = '{{ csrf_token() }}';
        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data akan terhapus!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Ya",
            cancelButtonText:"Batal",
            confirmButtonColor: "#ec6c62",
            closeOnConfirm: false
        }).then(function(result) {
            console.log(result)
            if (result.value) {
                $.ajaxSetup({
                    headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
                });
                $.ajax({
                    type: 'get',
                    url: '{{route("pembelian.hapus",[null])}}/' + enc_id,
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data){
                    console.log(data)
                    if (data.status == 'success') {
                        Swal.fire('Yes',data.message,'success');
                        table.ajax.reload(null, true);
                     }else{
                       Swal.fire('Ups',data.message,'info');
                     }
                },
                error: function(data){
                    console.log(data);
                    Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                }});
            }
        });
      }

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
