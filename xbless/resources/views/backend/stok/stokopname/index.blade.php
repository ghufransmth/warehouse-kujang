@extends('layouts.layout')
@section('title', 'Stok Opname')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Stok Opname</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Stok Opname</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('stokopname.tambah')
        <a href="{{ route('stokopname.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
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
                        <table id="table1" class="table display p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>No Transaksi</th>
                                    {{-- <th>Perusahaan</th> --}}
                                    <th>Gudang Dari</th>
                                    <th>Gudang Tujuan</th>
                                    <th>Tanggal Transaksi</th>

                                    <th>Dibuat Oleh</th>
                                    {{-- <th>Diapprove Oleh</th> --}}
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
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
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table display table-bordered">
                        <thead>
                            <th>No</th>
                            <th>Product</th>
                            <th>Gudang Dari</th>
                            <th>Gudang Tujuan</th>
                            <th>Satuan</th>
                            <th>QTY</th>
                            <th>Stock Awal</th>
                            <th>Stock Akhir</th>
                            <th>Tgl Transaksi</th>
                        </thead>
                        <tbody id="body_product">
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>

                        </tbody>

                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>

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
                    "url": "{{ route("stokopname.getdata") }}",
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

               { "data": "no_transaksi"},
               { "data": "gudang_dari" },
               { "data": "gudang_tujuan" },
               { "data": "tgl_transaksi" },
               { "data": "created_by" },
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
    function showdetail(id){
        // console.log(id);
        $.ajax({
                type: 'GET',
                url : "{{route('stockopname.getdatadetail', [null])}}/"+id,
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },

                success: function(data){
                    if (data.success) {
                        Swal.close();
                        console.log(data.data.length);
                        var html = "";
                        for(i=0 ; i< data.data.length; i++){
                            if(data.data[i].gudang_dari == 0){
                                var gudang_dari = "Gudang Pembelian";
                            }else if(data.data[i].gudang_dari == 1){
                                var gudang_dari = "Gudang Penjualan";
                            }else if(data.data[i].gudang_dari == 2){
                                var gudang_dari = "Gudang BS";
                            }

                            if(data.data[i].gudang_tujuan == 1){
                                var gudang_tujuan = "Gudang Penjualan";
                            }else if(data.data[i].gudang_tujuan == 2){
                                var gudang_tujuan = "Gudang BS";
                            }
                            html += '<tr>'
                                    +'<td>1</td>'
                                    +'<td>'+data.data[i].getproduct.nama+'</td>'
                                    +'<td>'+gudang_dari+'</td>'
                                    +'<td>'+gudang_tujuan+'</td>'
                                    +'<td>'+data.data[i].getsatuan.nama+'</td>'
                                    +'<td>'+data.data[i].qty_so+'</td>'
                                    +'<td>'+data.data[i].stock_awal+'</td>'
                                    +'<td>'+data.data[i].stock_akhir+'</td>'
                                    +'<td>'+data.data[i].tgl_transaksi+'</td>'
                                +'</tr>'

                        }
                        $('#body_product').html(html)
                        $('#exampleModal').modal('show');
                    } else {
                        Swal.fire('Ups',data.message,'error');
                        return false;
                    }

                },
                complete: function () {
                        Swal.hideLoading();
                        $('#simpanAdj').removeClass("disabled");

                },
                error: function(data){
                        $('#simpanAdj').removeClass("disabled");
                        Swal.hideLoading();
                        console.log(data);
                }
        });
    }
</script>
@endpush
