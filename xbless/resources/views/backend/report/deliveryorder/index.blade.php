@extends('layouts.layout')
@section('title', 'Report Delivery Order')
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
        <h2>Delivery Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Report Delivery Order</a>
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
                                    <i class="fa fa-calendar m-auto px-2"></i> Tgl Faktur Dari &nbsp;
                                </span>
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_report_start" id="filter_tgl_faktur_report_start"
                                    value="{{$filter_tgl_faktur_report_start}}" />
                                <span class="input-group-addon bg-primary px-2">Sampai </span>
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_report_end" id="filter_tgl_faktur_report_end" value="{{$filter_tgl_faktur_report_end}}" />
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
                                    <th>Toko</th>
                                    <th>Driver</th>
                                    <th>Catatan</th>
                                    <th>Status Kirim</th>
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
                            "url": "{{ route("reportdeliveryorder.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                            data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_tgl_faktur_report_start = $('#filter_tgl_faktur_report_start').val();
                                d.filter_tgl_faktur_report_end   = $('#filter_tgl_faktur_report_end').val();
                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },

                    { "data": "tgldo"},
                    { "data": "no_do"},
                    { "data": "namecode"},
                    { "data": "driver" },
                    { "data" : "catatan",
                        "className" : "text-center",
                    },
                    { "data": "status",
                        "className" : "text-left",
                    },
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

        function detail(e,key){
            var data = table.row(key).data();
            $('#modalDetail').modal('show');
            $('#detail_title').html(data.namecode+' / Kode: '+data.outletcode);
            $('#detail_address').html(data.addresscode);
            $('#detail_salesman').html(data.sales);
            $('#detail_nofaktur').html(data.nofaktur);
            $('#detail_driver').html(data.driver);
            $('#detail_tglfaktur').html(data.tglfaktur);
            $('#datadetail').html(data.detail[0]);
            $('#grandtotal').html(data.detail[1]);
            // sales
        }

</script>
@endpush
