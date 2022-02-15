@extends('layouts.layout')
@section('title', 'LAPORAN BARANG KELUAR')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>LAPORAN BARANG KELUAR</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>LAPORAN BARANG KELUAR</a>
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
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Gudang : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="gudang" name="gudang" multiple="multiple">
                                        @foreach($gudang as $key => $row)
                                        {{-- <option value="{{$row->id}}" @foreach($selectedgudang as $k => $result) {{ $result == $row->id ? 'selected=""' : '' }}  @endforeach >{{ucfirst($row->name)}}</option> --}}
                                        <option value="{{$row->id}}"  {{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>


                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Produk :</label>
                                <div class="col-sm-8 error-text">
                                    <select class="form-control select2 selectProduct" id="produk" name="produk">
                                        <option value="">Pilih Semua</option>
                                        @foreach($produk as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedproduk == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->product_name)}}</option>
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
                                &nbsp;
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                 </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                @can('reportbarangkeluar.excel')
                                <div class="col-xs-3">
                                &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="button" id="ExportExcel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                @endcan
                                @can('reportbarangkeluar.print')
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                                @endcan
                                @can('reportbarangkeluar.pdf')
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
                            <th>No</th>
                            <th>Tanggal Invoice</th>
                            <th>No. Invoice</th>
                            <th>No. Sales Order</th>
                            <th>Gudang</th>
                            <th>Nama Buyer / Member</th>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Satuan</th>
                            <th>Unit Price</th>

                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="7" class="text-right">
                                <b>Total</b>
                            </td>
                            <td colspan="3"  style="border-right:none; text-align: right">
                                <b><span id="total">0</span></b>
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
                "pageLength": -1,
                "select" : true,
                "bFilter":false,
                "dom": '<"html5">lftip',
                "ajax":{
                            "url": "{{ route("reportbarangkeluar.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                            data: function ( d ) {
                            d._token= "{{csrf_token()}}";
                            d.filter_gudang           = $('#gudang').val();
                            d.filter_perusahaan       = $('#perusahaan').val();
                            d.filter_produk           = $('#produk').val();
                            d.filter_tgl_start        = $('#tgl_start').val();
                            d.filter_tgl_end          = $('#tgl_end').val();
                            },

                    "dataSrc": function ( json ) {
                        $('#total').html(json.sum_qty);
                        return json.data;
                    },
                    },
                "columns": [
                    {
                        "data": "no",
                        "orderable" : false,
                    },
                    { "data": "tgl",  "orderable" : false,},
                    { "data": "nonota",  "orderable" : false,},
                    { "data": "no_purchase",  "orderable" : false,},
                    { "data": "nama_gudang",  "orderable" : false,},
                    { "data": "nama_member",  "orderable" : false,},
                    { "data": "nama_produk", "orderable" : false,},
                    { "data": "stockinput","className" : "text-center", "orderable" : false,},
                    { "data": "namasatuan", "className": "text-center",  "orderable" : false,},
                    { "data": "harga","className": "text-right",  "orderable" : false,},
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
            table.on( 'order.dt search.dt', function () {
                table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();
            $('#cariData').on('click', function() {
                if($('#perusahaan').val()!=""){
                    table.ajax.reload(null, false);
                }else{
                    Swal.fire('Ups','Perusahaan wajib dipilih salah satu','info');
                    return false;
                }

            });
            $('#Print').on('click', function() {

                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){
                    @cannot('reportbarangkeluar.print')
                        Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                    @else
                        window.open('{{route('reportbarangkeluar.print')}}', '_blank');
                    @endcannot
                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });
            $('#ExportExcel').on('click', function() {

                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){
                    @cannot('reportbarangkeluar.excel')
                        Swal.fire('Ups!', "Anda tidak memiliki HAK AKSES! Hubungi ADMIN Anda.",'error'); return false;
                    @else
                        window.open('{{route('reportbarangkeluar.excel')}}', '_blank');
                    @endcannot
                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });
            $('#ExportPdf').on('click', function() {

                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){
                    window.open('{{route('reportbarangkeluar.pdf')}}', '_blank');
                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });

            $('.selectProduct').select2({
                placeholder: 'Pilih Semua',
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
                            text: "Pilih Semua"
                        })
                        $.each(data, function(index, item){
                            results.push({
                                id: item.id,
                                text : item.product_name
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
