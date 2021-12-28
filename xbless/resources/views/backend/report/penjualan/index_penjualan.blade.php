@extends('layouts.layout')
@section('title', 'LAPORAN PENJUALAN')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>LAPORAN PENJUALAN</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>LAPORAN PENJUALAN</a>
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
                <form id="submitData" name="submitData">
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Cari Kode / Nama Produk : </label>
                                <div class="col-sm-8 error-text">
                                    {{-- <input type="text" class="form-control" id="filter_keyword" name="filter_keyword" value="{{$selectedfilterkeyword}}"> --}}
                                    <select class="form-control select2 selectProduct" id="filter_keyword" name="filter_keyword">
                                        <option value="0"> Semua Produk</option>
                                        @foreach($product as $key => $row)
                                            <option value="{{$row->id}}" {{$selectedfilterkeyword == $row->id ? 'selected' : ''}}>{{strtoupper($row->product_code)}} | {{strtoupper($row->product_name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Kategori :</label>
                                <div class="col-sm-8 error-text">
                                    <select class="form-control select2" id="kategori" name="kategori" multiple="multiple">
                                        @foreach($kategori as $key => $row)
                                        <option value="{{$row->id}}" @foreach($selectedkategori as $k => $result) {{ $result == $row->id ? 'selected=""' : '' }}  @endforeach>{{ucfirst($row->cat_name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Dari Tanggal : </label>
                                <div class="col-sm-3 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_start" name="tgl_start" value="{{$tgl_start}}">
                                </div>
                                <label class="col-sm-2 col-form-label">Sampai Tanggal : </label>
                                <div class="col-sm-3 error-text">
                                    <input type="text" class="form-control formatTgl" id="tgl_end" name="tgl_end" value="{{$tgl_end}}">
                                </div>


                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Perusahaan : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="">Semua Perusahaan</option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Gudang : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="gudang" name="gudang" multiple="multiple">
                                        @foreach($gudang as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                &nbsp;
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                 </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                @can('reportpenjualan.excel')
                                <div class="col-xs-3">
                                &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="button" id="ExportExcel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                @endcan
                                @can('reportpenjualan.print')
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                                @endcan
                                @can('reportpenjualan.pdf')
                                <div class="col-xs-3">
                                    <button class="btn btn-danger" type="button" id="ExportPdf"><span class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                                </div>
                                @endcan
                            </div>
                            </form>

                            <div class="hr-line-dashed"></div>

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th width="5%" >No</th>
                            <th>Stock Code</th>
                            <th>Sub Category</th>
                            <th>Part No</th>
                            <th>Qty Penjualan</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="5">
                                <ul class="pagination float-right"></ul>
                            </td>
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
    var table,tabledata,table_index,tableproduct;
       $(document).ready(function(){
            $('.select2').select2();
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
           "pageLength": 25,
           "select" : true,
           "bFilter":false,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("reportpenjualan.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                      d._token= "{{csrf_token()}}";
                      d.filter_kategori         = $('#kategori').val();
                      d.filter_perusahaan       = $('#perusahaan').val();
                      d.filter_gudang          = $('#gudang').val();
                      d.filter_keyword          = $('#filter_keyword').val();
                      d.filter_tgl_start        = $('#tgl_start').val();
                      d.filter_tgl_end          = $('#tgl_end').val();
                    }
                  },
           "columns": [
               {
                 "data": "no",
                 "orderable" : false,
               },
               { "data": "code"},
               { "data": "nama_kategori"},
               { "data": "part_no"},
               { "data": "qty"},
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
        $('#cariData').on('click', function() {
            table.ajax.reload(null, false);
        });
        $('#Print').on('click', function() {
            var jumlahdata = table.rows().count();
            if(jumlahdata > 0){
                @cannot('reportpenjualan.print')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportpenjualan.print')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
            }
        });
        $('#ExportExcel').on('click', function() {

            var jumlahdata = table.rows().count();
            if(jumlahdata > 0){
                @cannot('reportpenjualan.excel')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportpenjualan.excel')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
            }
        });
        $('#ExportPdf').on('click', function() {
            var jumlahdata = table.rows().count();

            if(jumlahdata > 0){
                @cannot('reportpenjualan.pdf')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportpenjualan.pdf')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
            }
        });
        $('.selectProduct').select2({
            placeholder: 'Semua Produk',
            ajax: {
                url: '{{ route("order.search") }}',
                dataType: 'JSON',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    }
                },
                processResults: function (data) {
                    let results = [];
                    results.push({
                        id: 0,
                        text: "Semua Produk"
                    })
                    $.each(data, function(index, item){
                        results.push({
                            id: item.id,
                            text : item.product_code+'|'+item.product_name
                        });
                    });

                    return{
                        results: results
                    };

                }
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
