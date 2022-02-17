@extends('layouts.layout')
@section('title', 'History Delivery Order')
@section('content')
<style>
    .select2-container--open{
        z-index: 99999;
    }
    a.disabled {
        pointer-events: none;
        cursor: default;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>History Delivery Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>History Delivery Order</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="d-flex pl-4 pt-4" style="">
                        <div class="form-group" id="date1">
                            <div class="input-daterange input-group" id="datepicker">
                                <span class="input-group-addon bg-primary">
                                    <i class="fa fa-calendar m-auto px-2"></i> Tgl DO &nbsp;
                                </span>
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_do_history_start" id="filter_tgl_do_history_start"
                                    value="{{$filter_tgl_do_history_start}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <select class="form-control" id="filter_driver" name="filter_driver" width="100%">
                                    <option value="">Semua Driver</option>
                                    @foreach($driver as $key => $row)
                                        <option value="{{$row->id}}" {{ $selecteddriver == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->nama)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group text-right">
                            <div class="col-sm-12">
                                <button class="btn btn-success" id="filter" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class=" text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Tgl DO</th>
                                    <th>No. DO</th>
                                    <th>Salesman</th>
                                    <th>Driver</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="text-white text-center bg-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
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

<div class="modal fade" id="modalNote" role="dialog" aria-labelledby="modal_address" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Catatan Faktur</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="submitDriver">
                <input type="hidden" name="do_id" class="form-control" value="" id="do_id"/>
                <div class="modal-body" id="">
                    <div class="row">
                        <div class="col-md-12">

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <div class="form-group error-text">
                                        <label for="driver">Disclaimer : </label>
                                        <textarea class="form-control" id="note"></textarea>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a  class="" data-dismiss="modal">Close</a>
                    <button type="button" class="btn btn-primary" id="saveNote">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" role="dialog" aria-labelledby="modal_address" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Preview Data</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="submit">
                <div class="modal-body" id="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="error-text">
                                        <label>CV KUJANG MARINAS UTAMA</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="error-text">
                                                <label>Salesman </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_salesman"> </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="error-text">
                                                <label>No. DO </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_no_do"> </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="error-text">
                                                <label>Driver </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_driver"> </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="error-text">
                                                <label>Tgl. DO </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_tgl_do"> </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <table class="table">
                                        <thead>
                                            <tr class="text-white bg-primary">
                                                <th rowspan="2">PCODE</th>
                                                <th rowspan="2">Nama Barang</th>
                                                <th>Assembling</th>
                                                <th colspan="3">Total Assembling</th>
                                            </tr>
                                            <tr class="text-white bg-primary">
                                                <th>KRT. Utuh</th>
                                                <th>KRT</th>
                                                <th>LSN</th>
                                                <th>SAT</th>
                                            </tr>
                                        </thead>
                                        <tbody id="datadetail">

                                        </tbody>

                                    </table>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="error-text">
                                                        <label>JML KRT UTUH </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="error-text">
                                                        : <label id="detail_jml_krt_utuh"> ?</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="error-text">
                                                        <label>JML KRT Assembling </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="error-text">
                                                        : <label id="detail_jml_krt_assembling"> ?</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="error-text">
                                                        <label>Total KRT </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-9">
                                                    <div class="error-text">
                                                        : <label id="detail_total_krt"> ? </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table">
                                        <thead>
                                            <tr class="text-white bg-primary">
                                                <th>Tgl. Faktur</th>
                                                <th>No. Faktur</th>
                                                <th>Outlet ID</th>
                                                <th>Nama Outlet</th>
                                                <th>Nilai Faktur</th>
                                            </tr>
                                        </thead>
                                        <tbody id="datadetailfaktur">

                                        </tbody>
                                        <tfoot>
                                            <tr class="text-white bg-primary">
                                                <th colspan="4" class="text-right">TOTAL</th>
                                                <th class="text-right" id="grandtotal">23.364</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{-- historydeliveryorder.print --}}
                    <a href="#" class="btn btn-primary" id="print">Print</a>
                </div>
            </form>
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

            $('#date1 .input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
                format: "dd-mm-yyyy"
            });

            $('#p_tgl_warkat').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                calendarWeeks: true,
                autoclose: true,
                format: "dd-mm-yyyy"
            });

            $('#modalDriver').on('shown.bs.modal', function () {
                $("#driver").select2({
                    width: '100%',
                });
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
                            "url": "{{ route("historydeliveryorder.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                            data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_tgl_do_history_start = $('#filter_tgl_do_history_start').val();
                                d.filter_do_driver            = $('#driver').val();
                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },

                    { "data": "tgldo"},
                    { "data": "no_do"},
                    { "data": "sales"},
                    { "data": "driver" },
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
                },
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

            $('#saveNote').click(function(){
                var note = $('#note').val();
                if(note==""){
                    Swal.fire('Ups','Catatan wajib diisi.','info');
                }else{
                    $.ajax({
                        type: 'POST',
                        url : "{{route('reportdeliveryorder.updatenote')}}",
                        data: {
                            "_token"         :"{{ csrf_token() }}",
                            "enc_id"         :$('#do_id').val(),
                            "note"           :note,
                        },
                        dataType: "json",
                        success: function(data){
                            console.log(data);
                            if (data.success) {
                                Swal.fire('Yes',data.message,'success');
                                table.ajax.reload(null, true);
                                $('#modalNote').modal('hide');
                                $('#note').val("");
                                $('#do_id').val("");
                            } else {
                                Swal.fire('Ups',data.message,'info');
                                table.ajax.reload(null, true);
                            }
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            });

        });

        function note(e,key){
            var data = table.row(key).data();
            $('#modalNote').modal('show');
            $('#do_id').val(data.enc_id);
            $('#note').val(data.note);
        }

        function detailHistory(e,key){
            var data = table.row(key).data();
            $('#modalDetail').modal('show');
            $('#detail_salesman').html(data.sales);
            $('#detail_no_do').html(data.no_do);
            $('#detail_driver').html(data.driver);
            $('#detail_tgl_do').html(data.tgldo);
            $('#datadetail').html(data.detail[0]);
            $('#detail_jml_krt_utuh').html(data.detail[1]);
            $('#detail_jml_krt_assembling').html(data.detail[2]);
            $('#detail_total_krt').html(data.detail[3]);
            $('#datadetailfaktur').html(data.detailfaktur[0]);
            $('#grandtotal').html(data.detailfaktur[1]);
            var print_url = "{{route('historydeliveryorder.print',[null])}}/"+data.enc_id+"";
            $("#print").attr("href", print_url).attr('target','_blank');;
        }

</script>
@endpush
