@extends('layouts.layout')
@section('title', 'Adjustment Stok')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Adjustment Stok</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Adjustment Stok</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('produk.tambah')
        <a href="{{ route('adjstok.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
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
                                    <th>Product</th>
                                    <th>Stock Penjualan (PCS)</th>
                                    <th>Stock BS (PCS)</th>
                                    <th>Aksi</th>
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
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal_product" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-header">
                <b><span id="title_modal">Tes</span></b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <table class="table-resonsive">
                <tr>
                    <td>No</td>
                    <td>Product</td>
                    <td>Kategori</td>
                    <td>Satuan.PCS</td>
                </tr>
                <tr>
                    <td>No</td>
                    <td>Product</td>
                    <td>Kategori</td>
                    <td>Satuan.PCS</td>
                </tr>
            </table>
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
                            <th>Kategori</th>
                            <th id='stock_product1' class="text-center">Stock Penjualan <br> Satuan.PCS</th>
                            <th id='stock_product2' class="text-center">Stock BS <br> Satuan.PCS</th>
                        </thead>
                        <tbody id="body_product">

                        </tbody>

                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="modal fade" id="modal_stok"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 2050 !important;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <b><span id="title_modal"></span></b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="ibox-content">
                    <form id="submitData" name="submitData">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="perusahaan_id" id="perusahaan_id">
    <input type="hidden" name="perusahaangudang_id" id="perusahaangudang_id">
    <input type="hidden" name="gudang_id" id="gudang_id">
    <div class="form-group row">
        <div class="col-sm-12">
            <input type="text" class="form-control" value="" id="key_value" name="key_value"
                placeholder="Masukan kode / nama produk">
        </div>
    </div>
    <div class="table-responsive">
        <table id="tableproduct" class="table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Stock Qty</th>
                    <th width='20%'>Tambah Stock</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody id="detailData">
            </tbody>
        </table>
    </div>
    </form>
    @can('adjstok.tambah')
    <div class="modal-footer">
        <button class="btn btn-success btn-sm" type="submit" id="simpanAdj">Simpan</button>
    </div>
    @endcan

</div>

</div>
</div>
</div> --}}

</div>



@endsection
@push('scripts')
<script>

</script>
<script type="text/javascript">
    var table,tabledata,table_index,tableproduct;
       $(document).ready(function(){

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
           "pageLength": 25,
           "select" : true,
           "responsive": true,
           "stateSave"  : true,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("adjstok.getdata") }}",
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
               { "data": "product"},
               { "data": "stock_penjualan"},
               { "data": "stock_bs"},
               { "data": "action"},
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
         $("#key_value").on('change', function(){
            loadProduct();
        });
        // $(".qty").on('click', function(){
        //     alert('ok');
        //     var parent = $(this).closest('tr');
        //     var price  = parseFloat($('.price',parent).text());
        //     var qty = parseFloat($('.qty',parent).val());
        //     alert(qty);

        // });

        $('#key_value').keyup(function(e){
            if(e.keyCode == 13)
            {
                loadProduct();
            }
        });
        $('#simpanAdj').on('click', function() {

                Swal.showLoading();
                var jumlah=$('#tableproduct >tbody >tr').length;
                if(jumlah==0){
                    Swal.hideLoading();
                    Swal.fire('Ups','Tidak ada data Adj Stock. Silahkan tambahkan stok adj terlebih dahulu','info');
                    return false;
                }else{
                    SimpanData();
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

       function showproduct(id){
        //    console.log(id);

           $.ajax({
                type: 'GET',
                url : "{{route('adjstok.getdataproduct', [null])}}/"+id,
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
                        // Swal.fire('Yes',data.message,'success');
                        // $('#modal_stok').modal('hide');
                        // $('#key_value').val("");
                        console.log(data.data)
                        if(data.data.getproduct.getsatuan.id == 1){
                            $('stock_product1').html('Stock Penjualan<br> PCS');
                            $('stock_product2').html('Stock BS <br> PCS');
                            $('#body_product').html('<tr>'
                                        +'<td>1</td>'
                                        +'<td>'+data.data.getproduct.nama+'</td>'
                                        +'<td>'+data.data.getproduct.getkategori.nama+'</td>'
                                        +'<td>'+data.data.stock_penjualan+'</td>'
                                        +'<td>'+data.data.stock_bs+'</td>'
                                    +'</tr>')
                        }else{
                            var satuan_stock_penjualan = (data.data.stock_penjualan / data.data.getproduct.getsatuan.qty);
                            if(satuan_stock_penjualan >= 1){
                                var pcs_stock_penjualan = (data.data.stock_penjualan % data.data.getproduct.getsatuan.qty)
                            }else{
                                var satuan_stock_penjualan = 0;
                                var pcs_stock_penjualan = data.data.stock_penjualan;
                            }
                            var satuan_stock_bs = (data.data.stock_bs / data.data.getproduct.getsatuan.qty);
                            if(satuan_stock_bs >= 1){
                                var pcs_stock_bs = (data.data.stock_bs % data.data.getproduct.getsatuan.qty)
                            }else{
                                var satuan_stock_bs = 0;
                                var pcs_stock_bs = data.data.stock_bs;
                            }
                            $('#body_product').html('<tr>'
                                        +'<td>1</td>'
                                        +'<td>'+data.data.getproduct.nama+'</td>'
                                        +'<td>'+data.data.getproduct.getkategori.nama+'</td>'
                                        +'<td>'+data.data.getproduct.getsatuan.nama+' = '+satuan_stock_penjualan.toFixed(0)+'<br> PCS = '+pcs_stock_penjualan+'</td>'
                                        +'<td>'+data.data.getproduct.getsatuan.nama+' = '+satuan_stock_bs.toFixed(0)+'<br> PCS = '+pcs_stock_bs+'</td>'
                                    +'</tr>')
                        }
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
        //
       }
        // function loadProduct(){
        //     var keyword             = $('#key_value').val();
        //     var perusahaanid        = $('#perusahaan_id').val();
        //     var perusahaangudangid  = $('#perusahaangudang_id').val();
        //     var gudangid            = $('#gudang_id').val();

        //     if(keyword==''){
        //         Swal.fire('Ups','Silahkan masukan kode / nama produk terlebih dahulu','info');
        //         return false;
        //     }else if(perusahaanid==''){
        //         Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
        //         return false;
        //     }
        //     else if(gudangid==''){
        //         Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
        //         return false;
        //     }else{
        //         tableproduct= $('#tableproduct').DataTable({
        //                 "processing": true,
        //                 "serverSide": true,
        //                 "pageLength": 50,
        //                 "select" : true,
        //                 "dom": 'rt',
        //                 "destroy": true,
        //                 "ajax":{
        //                     "url": "{{ route("adjstok.getdataproduct") }}",
        //                     "dataType": "json",
        //                     "type": "POST",
        //                     data: function ( d ) {
        //                         d._token= "{{csrf_token()}}";
        //                         d.perusahaanid = perusahaanid;
        //                         d.perusahaangudangid= perusahaangudangid;
        //                         d.gudangid= gudangid;
        //                         d.keyword = keyword;
        //                     },
        //                 },
        //                 "columns": [
        //                     {
        //                         "data": "no",
        //                         "orderable" : false,
        //                     },
        //                     { "data": "code", "orderable" : false,},
        //                     { "data": "name", "orderable" : false,},
        //                     { "data": "stok", "orderable" : false, "className" : "text-center",},
        //                     { "data": "jumlahadj",  "orderable" : false, "className" : "text-center",},
        //                     { "data": "catatan",  "orderable" : false,}
        //                 ],
        //                 drawCallback: function(settings) {
        //                     $(".qty").TouchSpin({
        //                         min:-1000000,
        //                         max: 1000000,
        //                         buttondown_class: 'btn btn-white',
        //                         buttonup_class: 'btn btn-white'
        //                     });
        //                      $(".qty").on('change', function(){
        //                         var parent = $(this).closest('tr');
        //                         var stok   = parseFloat($('.price',parent).text());
        //                         var qty    = parseFloat($('.qty',parent).val());

        //                         if(isNaN(stok) || stok == 0 ){
        //                             if(qty > 0){
        //                               var jumlah_output = qty;
        //                             }
        //                             else{
        //                               var jumlah_output = 0;
        //                             }
        //                         }else if (qty < "-" + stok) {

        //                             var jumlah_output = "-" + stok;
        //                         } else {
        //                             var jumlah_output = qty;
        //                         }

        //                         $('.qty',parent).val(jumlah_output);
        //                     });
        //                 },
        //                 responsive: true,
        //                 language: {
        //                     search: "_INPUT_",
        //                     searchPlaceholder: "Cari data",
        //                     emptyTable: "Tidak ada data",
        //                     info: "Menampilkan data _START_ sampai _END_ dari _MAX_ data.",
        //                     infoEmpty: "Menampilkan 0 sampai 0 dari 0 data.",
        //                     lengthMenu: "Tampilkan _MENU_ data per halaman",
        //                     loadingRecords: "Loading...",
        //                     processing: "Mencari...",
        //                     paginate: {
        //                         "first": "Pertama",
        //                         "last": "Terakhir",
        //                         "next": "Sesudah",
        //                         "previous": "Sebelum"
        //                     },
        //                 }
        //         });
        //     }
        // }
       function manageStock(e,key,perusahaangudang_id,gudang_id,gudang_name){
           var data = table.row(key).data();
           $('#modal_stok').modal('show');
           $('#modal_stok #perusahaan_id').val(data.id);
           $('#modal_stok #perusahaangudang_id').val(perusahaangudang_id);
           $('#modal_stok #gudang_id').val(gudang_id);
           $('#modal_stok #title_modal').html('Pengaturan Stok Produk di '+data.name+ ' pada Gudang '+gudang_name);
           $('#key_value').val("");
           $('#tableproduct tbody > tr').remove();
       }
       function SimpanData(){
                $('#simpanAdj').addClass("disabled");
                var form = $('#submitData').serializeArray()
                var dataFile = new FormData()
                var jumlah = $('#tableproduct >tbody >tr').length;
                dataFile.append('jumlahdata', jumlah);
                $.each(form, function(idx, val) {
                    dataFile.append(val.name, val.value)
                })

            $.ajax({
                type: 'POST',
                url : "{{route('adjstok.simpan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function(data){
                    if (data.success) {
                        Swal.fire('Yes',data.message,'success');
                        $('#modal_stok').modal('hide');
                        $('#key_value').val("");
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
