@extends('layouts.layout')

@section('title', 'Retur Produk')

@section('content')
<style>
    .swal2-container {
        z-index: 100000 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Retur Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Retur Produk</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />

    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <form id="submitData" name="submitData">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Jenis Transaksi : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control" id="jenis_transaksi" name="jenis_transaksi">
                                    <option value="">Semua Transaksi</option>
                                    <option value="0">Penjualan</option>
                                    <option value="1">Pembelian</option>
                                    {{-- @foreach($perusahaan as $key => $row)
                                    <option value="{{$row->id}}"
                                    {{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}
                                    </option>
                                    @endforeach --}}
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">No. Faktur : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control" id="no_faktur" name="no_faktur">
                                    <option value="">No Faktur</option>
                                    {{-- @foreach($sales as $key => $sles)
                                    <option value="{{ $sles->id }}">{{ $sles->nama }}</option>
                                    @endforeach --}}
                                    {{-- @foreach($member as $key => $row)
                                    <option value="{{$row->id}}" {{ $selectedmember == $row->id ? 'selected=""' : '' }}
                                    >{{ucfirst($row->name)}}-{{ucfirst($row->city)}}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1 error-text">
                                <button class="btn btn-success" id="search-data" type="button"><span
                                        class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                    </form>
                    {{-- <ul class="nav nav-tabs" id="myTab" role="tablist">
                        @if(Gate::check('purchaseorder.liststatuspo') ||
                        Gate::check('purchaseorder.liststatusinvoiceawal') ||
                        Gate::check('purchaseorder.liststatusinvoice'))
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==0?'active':(session('type')==""?'active':'')}}"
                                id="listpo-tab" value="0" onclick="change_type(0)" data-toggle="tab" href="#listpo"
                                role="tab" aria-controls="listpo" aria-selected="true">LIST RETUR PRODUK</a>
                        </li>
                        @endif
                        @can('purchaseorder.liststatuspolisttolak')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==1?'active':''}}" id="listpotolak-tab" value="1"
                                onclick="change_type(1)" data-toggle="tab" href="#listpotolak" role="tab"
                                aria-controls="listpotolak" aria-selected="false">LIST RETUR PENJUALAN</a>
                        </li>
                        @endcan
                        @can('purchaseorder.liststatusinvoiceawal')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==2?'active':''}}" id="listpovalidasi-tab" value="1"
                                onclick="change_type(2)" data-toggle="tab" href="#listpovalidasi" role="tab"
                                aria-controls="listpovalidasi" aria-selected="false">LIST RETUR PEMBELIAN</a>
                        </li>
                        @endcan --}}
                        {{-- @can('purchaseorder.liststatusgudang')
                        @foreach ($gudang as $k=>$itemgudang)
                            <li class="nav-item">
                                <a class="nav-link {{session('type_gudang')==$itemgudang->id?'active':''}}"
                        id="listgudang_{{$itemgudang->id}}-tab" value="1"
                        onclick="change_type_gudang(3,{{$itemgudang->id}})" data-toggle="tab" href="#listpovalidasi"
                        role="tab" aria-controls="listpovalidasi"
                        aria-selected="false">{{strtoupper($itemgudang->name)}}</a>
                        </li>
                        @endforeach
                        @endcan --}}
                    {{-- </ul> --}}
                    <input type="hidden" class="form-control" id="type" value="{{session('type')}}" />
                    <input type="hidden" class="form-control" id="type_gudang" value="{{session('type_gudang')}}" />
                    {{-- <div class="hr-line-dashed"></div> --}}
                    <div class="table-responsive">
                        <table id="table1" class="table display table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="10px;">No</th>
                                    <th>No Faktur</th>
                                    <th>Sales</th>
                                    <th>Toko</th>
                                    <th>Tgl Transaksi</th>
                                    {{-- <th>Tgl Jatuh Tempo</th>
                                    <th>Tgl Lunas</th> --}}
                                    <th>Jenis Transaksi</th>
                                    <th>Total Harga</th>
                                    <th>Created By</th>
                                    <th class="text-center" width="15%">Aksi</th>
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

    <!-- Modal Detail PO -->
    {{-- @include('backend.purchase.detail') --}}




</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.js"
    integrity="sha512-w5udtBztYTK9p9QHQR8R1aq8ke+YVrYoGltOdw9aDt6HvtwqHOdUHluU67lZWv0SddTHReTydoq9Mn+X/bRBcQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
    var table,tabledata,table_index;
        $('.formatTgl').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: "dd-mm-yyyy"
        });
        $(document).ready(function(){
            $(".select2").select2({allowClear: true});

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
                            "url": "{{ route("retur.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                                data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_toko = $('#toko').val();
                                d.filter_sales     = $('#sales').val();
                                d.type              = $('#type').val();
                                d.jenis_transaksi = $('#jenis_transaksi').val();
                                d.no_faktur = $('#no_faktur').val();

                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },
                    { "data": "no_faktur", "orderable" : false, },
                    { "data": "sales", "orderable" : false, },
                    { "data": "toko", "orderable" : false, },
                    { "data": "tgl_transaksi", "orderable" : false, },
                    // { "data": "tgl_jatuh_tempo", "orderable" : false, },
                    // { "data": "tgl_lunas", "orderable" : false, },
                    { "data": "jenis_transaksi", "orderable" : false, },
                    { "data": "total_harga", "orderable" : false, },
                    { "data": "created_by", "orderable" : false, },

                    { "data" : "aksi",
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

            $('#search-data').click(function(){
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


</script>
<script>
    $(document).ready(function(){
        $('#no_faktur').select2({allowClear: false,
            ajax: {
                    url: '{{ route("retur.list_transaksi_retur") }}',
                    dataType: 'JSON',
                    delay: 250,
                    data: function(params) {
                        return {
                        jenis_transaksi: $('#jenis_transaksi').val(),
                        search: params.term
                        }
                    },
                    processResults: function (data) {
                    var results = [];
                    $.each(data, function(index, item){
                        results.push({
                            id: item.no_retur_faktur,
                            text : item.no_retur_faktur,
                        });
                    });
                    return{
                        results: results
                    };
                }
            }
        });
        $('#jenis_transaksi').on('change',function(){
            $("#no_faktur").select2("val", "");
        })
    });
    function change_type(type){
        $('#type').val(type);
        table.ajax.reload(null, false);
    }
function approve(id){
    // console.log(id);
    $.ajax({
        type: 'GET',
        url : "{{route('purchaseorder.approve', [null])}}/"+id,
        dataType: "json",
        beforeSend: function () {
        Swal.showLoading();
        },
        success: function(data){
            if (data.success) {
                Swal.fire('Yes',data.message,'success');
                table.ajax.reload(null, false);
            } else {
                Swal.fire('Ups',data.message,'info');
            }
        },
        complete: function () {
            Swal.hideLoading();
            $('#simpan').removeClass("disabled");
        },
        error: function(data){
            $('#simpan').removeClass("disabled");
            Swal.hideLoading();
            Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
        }
    });
}
function reject(id){
    $.ajax({
        type: 'GET',
        url : "{{route('purchaseorder.reject', [null])}}/"+id,
        dataType: "json",
        beforeSend: function () {
        Swal.showLoading();
        },
        success: function(data){
            if (data.success) {
                Swal.fire('Yes',data.message,'success');
                table.ajax.reload(null, false);
            } else {
                Swal.fire('Ups',data.message,'info');
            }
        },
        complete: function () {
            Swal.hideLoading();
            $('#simpan').removeClass("disabled");
        },
        error: function(data){
            $('#simpan').removeClass("disabled");
            Swal.hideLoading();
            Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
        }
    });
}

</script>
@endpush
