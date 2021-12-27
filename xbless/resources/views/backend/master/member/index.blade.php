@extends('layouts.layout')
@section('title', 'Member')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Master Member</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Master Member</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Refresh Data"><span class="fa fa-refresh"></span></button>

        @can('member.tambah')
        <a href="{{ route('member.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
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
                            <th width="10px;">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kota</th>
                            <th>NPWP</th>
                            <th>No HP</th>
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
    <!-- Modal -->
<div class="modal fade" id="modal_sales" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <span id="title_modal"></span>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <input type="hidden" id="memberdataid" value="">
          <div class="modal-body" id="sales">

          </div>
          <div class="modal-footer">
              <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Cancel</button>
              @can('member.simpan_member_sales')
              <button class="btn btn-success btn-sm" type="submit" id="simpan_sales">Simpan</button>
              @endcan
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
                    "url": "{{ route("member.getdata") }}",
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

               { "data": "code"},
               { "data": "name"},
               { "data": "city" },
               { "data": "isinpwp" },
               { "data": "phone" },
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
         $(document).on("click", "#addsales", function () {

              $('#sales').html('')
              $('#memberdataid').val('')
              var token = '{{ csrf_token() }}';
              var memberid = $(this).data('id');
              $.ajaxSetup({
                  headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
              });
              $.ajax({
                  type: 'POST',
                  headers: {'X-CSRF-TOKEN': token},
                  url: '{{ route("member.member_sales") }}',
                  data: {
                      enc_id: memberid
                  },
                  success: function(response){
                      // console.log(response)
                      var sales = response.datalist
                      var select_sales = ''
                      for (let index = 0; index < sales.length; index++) {
                          select_sales += sales[index].aksi
                      }
                      $('#sales').html(select_sales)
                      $('#title_modal').html(`<h5 class="modal-title" id="exampleModalLabel"> Sales Member ${response.member.name} </h5>`)
                      $('#memberdataid').val(response.member.id)
                  }
              })
          });
          $(document).on('click','#simpan_sales',function(){
            var sales_id = [];
            var token = '{{ csrf_token() }}';
            var member_id = $('#memberdataid').val()
            $("input:checkbox[name=salesid]:checked").each(function(){
               sales_id.push($(this).val())
            })


            $.ajax({
                type: 'POST',
                headers: {'X-CSRF-TOKEN': token},
                url: '{{ route("member.simpan_member_sales") }}',
                data: {
                    member_id: member_id,
                    sales_id: sales_id
                },
                success: function(response){
                    if(response.code == 200){
                        Swal.fire('Yes',response.message,'success');
                        $('#modal_sales').modal('hide')
                    }else{
                        Swal.fire(response.code,"Terjadi kesalahan pada sistem.",'Info');
                    }
                }
            })
         });
       });


         function deleteData(e,enc_id){
           @cannot('member.hapus')
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
               url: '{{route("member.hapus",[null])}}/' + enc_id,
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
           @cannot('member.resetapp')
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
               url: '{{route("member.resetapp")}}',
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
