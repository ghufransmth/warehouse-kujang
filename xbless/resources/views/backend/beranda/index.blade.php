@extends('layouts.layout')

@section('title', 'Beranda')

@section('content')
{{-- <div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="text-center m-t-lg">
                @if(session('message'))
                <div class="alert alert-{{session('message')['status']}}">
<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
        aria-hidden="true">&times;</span></button>
{{ session('message')['desc'] }}
</div>
@endif
<h1>
    Welcome in Bensco Project
</h1>
<small>
    (Version 2.0)
</small>
</div>
</div>
</div>
</div> --}}
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-sm-8">
        <h2>Dashboard</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">This is</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Dashboard</strong>
            </li>
        </ol>
    </div>
    <div class="col-sm-4">
        <div class="title-action">
            <div class="form-group" id="data_5">
                <div class="input-daterange input-group" id="datepicker">
                    <span class="input-group-addon px-3 bg-white border"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control-sm form-control" name="start" id="start" value="{{ $periode_start }}">
                    <span class="input-group-addon px-3 bg-primary">to</span>
                    <input type="text" class="form-control-sm form-control" name="end" id="end" value="{{ $periode_end }}">
                    <span class="input-group-addon px-3 bg-white  border"><i class="fa fa-calendar"></i></span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-4 text-center m-auto">
                            <div class="btn btn-primary  dim btn-large-dim rounded-circle" type="button"><i
                                    class="fa fa-dollar"></i></div>
                        </div>
                        <div class="col-md-8 m-auto text-right">
                            <h1 class="no-margins"><span id="omset">Rp. 0</span></h1>
                            <div class="font-bold text-navy">
                                Total Omset
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-4 text-center m-auto">
                            <div class="btn btn-primary  dim btn-large-dim rounded-circle" type="button"><i
                                    class="fa fa-cart-plus"></i></div>
                        </div>
                        <div class="col-md-8 m-auto text-right">
                            <h1 class="no-margins"><span id="retur">Rp. 0</span></h1>
                            <div class="font-bold text-navy">
                                Total Retur
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-4 text-center m-auto">
                            <div class="btn btn-primary  dim btn-large-dim rounded-circle" type="button"><i
                                    class="fa fa-signal"></i></div>
                        </div>
                        <div class="col-md-8 m-auto text-right">
                            <h1 class="no-margins"><span id="fatur">Rp. 0</span></h1>
                            <div class="font-bold text-navy">
                                Nominal Fatur
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="ibox ">
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-md-4 text-center m-auto">
                            <div class="btn btn-primary  dim btn-large-dim rounded-circle" type="button"><i
                                    class="fa fa-money"></i></div>
                        </div>
                        <div class="col-md-8 m-auto text-right">
                            <h1 class="no-margins"><span id="pajak">Rp. 0</span></h1>
                            <div class="font-bold text-navy">
                                Pajak Pendapatan
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>FAKTUR UNILEVER</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_faktur_unilever" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Total Pembelian</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Total Pembelian</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>PENJUALAN</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_penjualan" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Total Penjualan</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Total Penjualan</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>RETUR</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_retur" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>No</th>
                                    <th>Salesman</th>
                                    <th>Nomor Retur</th>
                                    <th>Tanggal Retur</th>
                                    <th>Alasan Retur</th>
                                    <th>Barang</th>
                                    <th>Jml Retur/KRT.LSN.PCS</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Salesman</th>
                                    <th>Nomor Retur</th>
                                    <th>Tanggal Retur</th>
                                    <th>Alasan Retur</th>
                                    <th>Barang</th>
                                    <th>Jml Retur/KRT.LSN.PCS</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>FAKTUR PIUTANG</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_faktur_piutang" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>No</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Due Date</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="">
                                    <td>1</td>
                                    <td>2021-12-27</td>
                                    <td>234000001237</td>
                                    <td>Berkah Jaya</td>
                                    <td>Abdullah Khifli</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                                <tr class="">
                                    <td>2</td>
                                    <td>2021-12-27</td>
                                    <td>234000001237</td>
                                    <td>Berkah Ghusti</td>
                                    <td>Toriq</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                                <tr class="">
                                    <td>3</td>
                                    <td>2021-12-27</td>
                                    <td>23400231237</td>
                                    <td>Bersyukur</td>
                                    <td>Dulha</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Due Date</th>
                                    <th>Nominal</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>FAKTUR TERTAGIH</h5>
                    <div class="ibox-tools">
                        <a class="collapse-link">
                            <i class="fa fa-chevron-up"></i>
                        </a>
                        <a class="close-link">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table_faktur_tertagih" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>No</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Due Date</th>
                                    <th>Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="">
                                    <td>1</td>
                                    <td>2021-12-27</td>
                                    <td>234000001237</td>
                                    <td>Berkah Jaya</td>
                                    <td>Abdullah Khifli</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                                <tr class="">
                                    <td>2</td>
                                    <td>2021-12-27</td>
                                    <td>234000001237</td>
                                    <td>Berkah Ghusti</td>
                                    <td>Toriq</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                                <tr class="">
                                    <td>3</td>
                                    <td>2021-12-27</td>
                                    <td>23400231237</td>
                                    <td>Bersyukur</td>
                                    <td>Dulha</td>
                                    <td>01/02/2021</td>
                                    <td>50.000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Tanggal Faktur</th>
                                    <th>Nomor Faktur</th>
                                    <th>Toko</th>
                                    <th>Salesman</th>
                                    <th>Due Date</th>
                                    <th>Nominal</th>
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
<script>
    var table_unilever, table_penjualan, table_retur, table_piutang, table_tagihan;
    $(document).ready(function () {
        getdata()
        $('#data_5 .input-daterange').datepicker({
            keyboardNavigation: false,
            forceParse: false,
            autoclose: true,
            format: 'dd-mm-yyyy'
        });
        table_unilever = $('#table_faktur_unilever').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "select" : true,
            "responsive": true,
            "stateSave"  : true,
            "dom": '<"html5">lftip',
            "ajax":{
                        "url": "{{ route("beranda.unilever.getdata") }}",
                        "dataType": "json",
                        "type": "POST",
                        data: function ( d ) {
                        d._token= "{{csrf_token()}}";
                        d.periode_start = $('#start').val()
                        d.periode_end = $('#end').val()
                        }
                    },

            "columns": [
                { "data": "faktur"},
                { "data": "no_faktur"},
                { "data": "nominal" },
                { "data": "status" },
                { "data" : "action",
                    "orderable" : false,
                    "className" : "text-center",
                },
            ],
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
        table_penjualan = $('#table_penjualan').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "select" : true,
            "responsive": true,
            "stateSave"  : true,
            "dom": '<"html5">lftip',
            "ajax":{
                        "url": "{{ route("beranda.penjualan.getdata") }}",
                        "dataType": "json",
                        "type": "POST",
                        data: function ( d ) {
                        d._token= "{{csrf_token()}}";
                        d.periode_start = $('#start').val()
                        d.periode_end = $('#end').val()
                        }
                    },

            "columns": [
                { "data": "faktur"},
                { "data": "no_faktur"},
                { "data": "total_harga" },
                { "data": "name" },
                {"data": "nama"}
            ],
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
        $('#table_retur').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
        $('#table_faktur_piutang').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
        $('#table_faktur_tertagih').DataTable({
            pageLength: 10,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-outline btn-default btn-lg',
                },
                {
                    extend: 'excel',
                    exportOptions: {
                        orthogonal: 'export'
                    },
                    header: true,
                    footer: true,
                    className: 'btn btn-block bg-primary text-white',
                }
            ]
        });
    });

    $(document).on('change', '#start', function(){
        var end = $('#end').val()
        if(end != ''){
            refresh()
            getdata()
        }
    })

    $(document).on('change', '#end', function(){
        var start = $('#start').val()
        if(end != ''){
            refresh()
            getdata()
        }
    })
</script>
<script>
    function refresh(){
        table_unilever.ajax.reload(null, true)
        table_penjualan.ajax.reload(null, true)
        table_retur.ajax.reload(null, true)
        table_piutang.ajax.eload(null, true)
        table_tagihan.ajax.reload(null, true)
    }
    function number_to_price(data){
        if(data==0){return '0,00';}
        data=parseFloat(data);
        data=data.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
        data=data.split('.').join('*').split(',').join('.').split('*').join(',');
        return data;
    }
    function getdata(){
        var start = $('#start').val()
        var end = $('#end').val()
        var token = '{{ csrf_token() }}';
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': token},
            data:{
                periode_start: start,
                periode_end: end
            },
            url: '{{ route("beranda.getdata") }}',
            success: function(response){
                // console.log(response)
                if(response.code == 200){
                    $('#omset').html(`Rp. ${number_to_price(response.detail.omset)}`)
                    $('#pajak').html(`Rp. ${number_to_price(response.detail.pajak)}`)
                }else{
                    Swal.fire(response.code,"Terjadi kesalahan pada sistem.",'Info');
                }
            }
        })
    }
</script>
@endpush
