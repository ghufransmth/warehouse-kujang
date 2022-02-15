@extends('layouts.layout')
@section('title','LAPORAN PEMBELIAN')
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
            <div class="ibox-content">
                <form id="submitData" name="submitData">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="hr-line-dashed"></div>
                        {{-- <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Cari Kode / Nama Produk : </label>
                            <div class="col-sm-8 error-text">
                                <select class="form-control select2 selectProduct" id="filter_keyword" name="filter_keyword">
                                    <option value="0"> Semua Produk</option>
                                    @foreach($product as $key => $row)
                                        <option value="{{$row->id}}" {{$selectedfilterkeyword == $row->id ? 'selected' : ''}}>{{strtoupper($row->product_code)}} | {{strtoupper($row->product_name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Dari Tanggal : </label>
                            <div class="col-sm-3 error-text">
                            <input type="text" class="form-control formatTgl" id="tgl_start" name="tgl_start" value="{{ date('d-m-Y') }}">
                            </div>
                            <label class="col-sm-2 col-form-label">Sampai Tanggal : </label>
                            <div class="col-sm-3 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_end" name="tgl_end" value="{{ date('d-m-Y') }}">
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Toko : </label>
                            <div class="col-sm-3 error-text">
                                <select class="form-control select2" id="perusahaan" name="perusahaan">
                                    <option value="">Semua Perusahaan</option>
                                    @foreach($perusahaan as $key => $row)
                                    <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <div class="col-sm-1 error-text">
                                <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                             </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        {{-- <div class="form-group row">
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
                        </div> --}}
                </form>
                <div class="hr-line-dashed"></div>
                <div class="table-responsive">
                    <table id="example" class="table p-0 table-hover table-striped">
                        <thead>
                            <tr class="text-white text-center bg-primary">
                                <th width="5%">No</th>
                                <th>Tgl Faktur</th>
                                <th>No Faktur</th>
                                <th>Total Harga</th>
                                <th>Status Pembayaran</th>
                                <th>Aksi</th>
                                {{-- <th>Catatan</th> --}}
                            </tr>
                        </thead>

                        <tfoot>
                            {{-- <tr class="text-white text-center bg-primary">
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr> --}}
                        </tfoot>
                    </table>
                    <div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    var table,tabledata,table_index,tableproduct;
    $(document).ready(function(){
            $.ajaxSetup({
                headers:{ "X-CSRF-Token": $("meta[name=csrf-token]").attr("content") }
            });
            table = $('#example').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                ordering: true,
                select: true,
                // "bFilter":false,
                "ajax":{
                    "url": "{{ route("reportbarangkeluar.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function( d ){
                        d._token= "{{ csrf_token() }}";
                        d.tgl_start = $('#tgl_start').val();
                        d.tgl_end = $('#tgl_end').val();
                        }
                    },
                    "columns": [
                        {
                            "data": 'no',
                            "orderable": false,

                        },
                        {
                            "data": "tgl_faktur",
                            "orderable": false,
                        },
                        {
                            "data": "no_faktur",
                            "orderable": false,
                        },
                        // {
                        //     "data": 'keterangan',
                        //     "orderable": false,
                        // },
                        {
                            "data": 'total_harga',
                            "orderable": false,
                        },
                        {
                            "data": 'status_lunas',
                            "orderable": false,
                        },
                        {
                            "data": 'aksi',
                            "orderable": false,
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
                    // }
                }
            });
            $('#tgl_start').on('change', function(){
                table.ajax.reload(null, false);
            });
            $('#tgl_end').on('change', function(){
                table.ajax.reload(null, false);
            });
        })

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

       $('#cariData').on('click', function() {
            if($('#tgl_start').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info');
                return false;
            }else if($('#tgl_end').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info');
                return false;
            }else{
                $.ajax({
                    type: 'POST',
                    url: '{{route('reportpembelian.cekdata')}}',
                    data: {
                        _token: '{{csrf_token()}}',
                        // filter_tgl    : $('#filter_tgl').val(),
                        tgl_start    : $('#tgl_start').val(),
                        tgl_end    : $('#tgl_end').val(),
                    },
                    dataType: "json",
                    success: function(result){
                        if (result) {
                           console.log("sukses");
                        }else {
                            Swal.fire('Ups',result.message,'info');
                            return false;
                        }

                    }
                });
            }

        });

       $('.formatTgl').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
@endpush
