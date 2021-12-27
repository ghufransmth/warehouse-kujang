@extends('layouts.layout')

@section('title', 'Backorder')

@section('content')

<style>
    th, td { white-space: nowrap; }
    .text-right-margin{
        margin-right: 200px !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Backorder</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Backorder</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Refresh Data"><span class="fa fa-refresh"></span></button>

    </div>
</div>
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered" >
                        <thead>
                        <tr>
                            <th width="10px;">No</th>
                            <th width="300px;">#</th>
                            <th>Customer</th>
                            <th>Tanggal Dibuat</th>
                            <th>Status</th>
                            <th>Expedisi</th>
                            <th>Sales</th>
                            <th>Total (Rp.)</th>
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
<div class="modal fade" id="modal_cancel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span id="title_modal"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <input type="hidden" id="idpo" value="">
            <div class="modal-body" id="catatan">
              <h4>Apakah Anda Yakin Ingin Membatalkan Order Ini ?</h4>
              <span><h5>Catatan Penolakan </h5>
                <textarea name="note" id="note" cols="50" rows="3"></textarea>
              </span>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                <button class="btn btn-success btn-sm" type="submit" id="simpan_cancel">Ya</button>
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
           "stateSave"  : true,
           "pageLength": 25,
           "select" : true,
           "responsive": true,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("backorder.getdata") }}",
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

               { "data": "rpo", "className":"text-right-margin"},
               { "data": "customer" },
               { "data": "tgl_po" },
               { "data": "status" },
               { "data": "expedisi" },
               { "data": "sales" },
               { "data": "total" },
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
<script>
  function addid(enc_id){
    $('#idpo').val(enc_id)
  }

  $(document).on("click", "#simpan_cancel", function(){
    // alert('ok');
    let enc_id = $('#idpo').val()
    let note = $('#note').val()
    $.ajaxSetup({
          headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
    });
    $.ajax({
        type: 'POST',
        headers:  {'X-CSRF-TOKEN': $('[name="_token"]').val()},
        url: '{{ route("requestpurchaseorder.cancel") }}',
        data: {
            enc_id: enc_id,
            note: note,
            status_po_rpo_bo : 2,

        },
        success: function(response){
          if (response.success) {
            Swal.fire('Yes',response.msg,'info');
            $('#modal_cancel').modal('hide');
            $('#note').val("");
            table.ajax.reload(null, true);
          } else {
            Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
            $('#modal_cancel').modal('hide');
            $('#note').val("");
          }
        }
      })
  })
</script>
@endpush
