@extends('layouts.layout')
@section('title', 'Report Retur Revisi')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
.dataTables_length {
    float: right !important;
}
#table1_filter {
    display: none;
}
.style-product {
    padding-left: 0 !important;
    padding-right: 0 !important;
}
.style-note{
    padding-left: 0 !important;
    padding-right: 0 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Report Retur Revisi</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Report Retur Revisi</a>
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
                                <label class="col-sm-2 col-form-label">Perusahaan : </label>
                                <div class="col-sm-8 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Transaksi : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control" id="jenistransaksi" name="jenistransaksi">
                                        <option value="">Pilih Jenis Transaksi</option>
                                        @foreach($jenistransaksi as $key => $row)
                                        <option value="{{$key}}"{{ $selectedjenistransaksi == $key ? 'selected=""' : '' }}>{{ucfirst($row)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">No Retur/Revisi : </label>
                                <div class="col-sm-3 error-text">
                                    <input type="text" class="form-control" id="no_returrevisi" name="no_returrevisi" value="{{$no_returrevisi}}">
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
                                &nbsp;
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                </div>
                            </div>


                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                @can('reportreturrevisi.excel')
                                <div class="col-xs-3">
                                &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="button" id="ExportExcel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                @endcan
                                @can('reportreturrevisi.print')
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                                @endcan
                                @can('reportreturrevisi.pdf')
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
                            <th width="5%">No</th>
                            <th>No Retur/Revisi</th>
                            <th>No Invoice</th>
                            <th>Customer</th>
                            <th>Kota</th>
                            <th>Kode-Nama (Produk)</th>
                            <th>Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
           "pageLength": 100,
           "select" : true,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("reportreturrevisi.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                      d._token= "{{csrf_token()}}";
                      d.filter_perusahaan       = $('#perusahaan').val();
                      d.filter_tgl_start        = $('#tgl_start').val();
                      d.filter_tgl_end          = $('#tgl_end').val();
                      d.filter_no_retur         = $('#no_returrevisi').val();
                      d.filter_jenis_transaksi  = $('#jenistransaksi').val();
                    },

            "dataSrc": function ( json ) {
                return json.data;
            },
            },
           "columns": [
               {
                 "data": "no",
                 "orderable" : false,
                 render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                 }
               },
               { "data": "noretur"},
               { "data": "no_inv"},
               { "data": "member_name"},
            //    { "data": "nama_perusahaan"},
               { "data": "nama_kota"},
               { "data": "produk_info_x", "className": "style-product"},
               { "data": "ket_perubahan", "className": "style-note"},
               
               // { "data": "produk_info", 
               //   "render": function (data) {
               //      let merge_produk = ''
               //      data.forEach((item, idx, array) => {
               //          if(idx === array.length - 1) {
               //              merge_produk += item 
               //          }else {
               //              merge_produk += item + '<hr>'
               //          }
                      
               //      })

               //      return merge_produk;
               //  },
               // },
               // { 
               //     "data": "ket",
               //     "render" : function(data) {
               //         let merge_keterangan = ''
               //         data.forEach((item, idx, array) => {
               //          if(idx === array.length - 1) {
               //              merge_keterangan += item 
               //          }else {
               //              merge_keterangan += item + '<hr>'
               //          }
                       
               //        })
               //        return merge_keterangan;
               //     }
               // },
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
                @cannot('reportreturrevisi.print')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportreturrevisi.print')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
            }
        });
        $('#ExportExcel').on('click', function() {

            var jumlahdata = table.rows().count();
            if(jumlahdata > 0){
                @cannot('reportreturrevisi.excel')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportreturrevisi.excel')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
            }
        });
        $('#ExportPdf').on('click', function() {
            var jumlahdata = table.rows().count();
            window.open('{{route('reportreturrevisi.pdf')}}', '_blank');
            if(jumlahdata > 0){
                @cannot('reportreturrevisi.pdf')
                    Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                @else
                    window.open('{{route('reportreturrevisi.pdf')}}', '_blank');
                @endcannot
            }else{
                Swal.fire('Ups','Tidak ada data','info');
                return false;
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
