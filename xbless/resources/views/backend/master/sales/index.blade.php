@extends('layouts.layout')
@section('title', 'Sales')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Master Sales</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Master Sales</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('sales.tambah')
        <a href="{{ route('sales.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class=" text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>No HP</th>
                                    <th>Tgl</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class=" text-white text-center bg-primary">
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
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("sales.getdata") }}",
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

               { "data": "name"},
               { "data": "username"},
               { "data": "email" },
               { "data": "phone" },
               { "data": "tgl" },
               { "data" : "action",
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


         function deleteData(e,enc_id){
           @cannot('sales.hapus')
               Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
           @else
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
           if (result.value) {
             $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
             });
              $.ajax({
               type: 'DELETE',
               url: '{{route("sales.hapus",[null])}}/' + enc_id,
               headers: {'X-CSRF-TOKEN': token},
               success: function(data){
                 if (data.status=='success') {
                     Swal.fire('Yes',data.message,'success');
                     table.ajax.reload(null, true);
                  }else{
                    Swal.fire('Ups',data.message,'info');
                  }
               },
               error: function(data){
                 console.log(data);
                 Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
               }
             });


           } else {

           }
          });
           @endcannot
       }
       function resetApp(e,key){
           var data = table.row(key).data();
           @cannot('sales.resetapp')
               Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
           @else
           var token = '{{ csrf_token() }}';
           Swal.fire({
             title: "Apakah Anda yakin",
             text: "Mereset Akun APP "+data.name+" ?",
             icon: 'warning',
             showCancelButton: true,
             confirmButtonClass: "btn-danger",
             confirmButtonText: "Ya",
             cancelButtonText:"Batal",
             confirmButtonColor: "#ec6c62",
             closeOnConfirm: false
           }).then(function(result) {
           if (result.value) {
             $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
             });
              $.ajax({
               type: 'POST',
               url: '{{route("sales.resetapp")}}',
               headers: {'X-CSRF-TOKEN': token},
               data: {
                    enc_id: data.enc_id
                },
               dataType: "json",
               success: function(data){
                 if (data.status=='success') {
                     Swal.fire('Yes',data.message,'success');
                     table.ajax.reload(null, true);
                  }else{
                    Swal.fire('Ups',data.message,'info');
                  }
               },
               error: function(data){
                 console.log(data);
                 Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
               }
             });


           } else {

           }
          });
           @endcannot
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
