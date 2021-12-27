@extends('layouts.layout')
@section('title', 'Informasi Stok Sales')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Informasi Stok Sales</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Informasi Stok Produk</a>
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
                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th width="5%" >No</th>
                            <th>Kode Produk</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Informasi Stok</th>
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
 <div class="modal fade" id="modalStok" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span id="title_modal">INFORMASI STOK PRODUK <span id="namaproduk">XXX</span></span>
                <input type="hidden" id="id_product">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="data_stok">

            </div>

        </div>
    </div>
  </div>
@endsection
@push('scripts')
<script type="text/javascript">
    var table,tabledata,table_index,tableproduct;
       $(document).ready(function(){
            $('.formatTgl').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                format: "dd-mm-yyyy"
            });
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
           $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
           });
           table= $('#table1').DataTable({
           "processing": true,
           "serverSide": true,
           "pageLength": 50,
           "select" : true,
           "responsive": true,
           "stateSave"  : true,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("stoksales.getdata") }}",
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
               { "data": "kode_produk"},
               { "data": "nama_produk"},
               { "data": "nama_kategori"},
               { "data": "nama_satuan"},
               { "data": "detail"},
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

       function DetailStok(e,key){
            var data = table.row(key).data();
            $('#modalStok').modal("show");
            $('#modalStok #namaproduk').html(data.product_name);
            $('#modalStok #data_stok').html(data.detaildata);
       }

 </script>
@endpush
