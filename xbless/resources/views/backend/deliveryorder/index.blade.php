@extends('layouts.layout')
@section('title', 'Delivery Order')
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
                <a>Delivery Order</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        {{-- @can('expedisi.tambah') --}}
            <a href="{{ route('deliveryorder.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
                title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        {{-- @endcan --}}
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
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_do_start" id="filter_tgl_faktur_do_start"
                                    value="{{$filter_tgl_faktur_do_start}}" />
                                <span class="input-group-addon bg-primary px-2">Sampai </span>
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_do_end" id="filter_tgl_faktur_do_end" value="{{$filter_tgl_faktur_do_end}}" />
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
                                    <th>Tgl Faktur</th>
                                    <th>No. Faktur</th>
                                    <th>Outlet Code</th>
                                    <th>Nama Outlet</th>
                                    <th>Alamat Outlet</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class=" text-white text-center bg-primary">
                                    <th></th>
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

<div class="modal fade" id="modalDriver" role="dialog" aria-labelledby="modal_address" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">No. Faktur : <span id="no_faktur" class="font-bold"></span></h3>
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
                                        <label for="driver">Driver <sup class="red">*</sup></label>
                                        <select class="form-control driver" name="driver" id="driver">
                                            <option value="">Pilih Driver</option>
                                            @foreach($driver as $key => $row)
                                                <option value="{{$row->id}}">{{ucfirst($row->nama)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a  class="" data-dismiss="modal">Close</a>
                    <button type="button" class="btn btn-primary" id="saveDriver">Save Driver</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="modalPengiriman" role="dialog" aria-labelledby="modal_address" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="header_pengiriman"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="submit">
                <input type="hidden" name="do_id_pengiriman" class="form-control" value="" id="do_id_pengiriman"/>
                <div class="modal-body" id="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_tgl_faktur">Tgl Faktur </label>
                                        <input type="text" class="form-control" id="p_tgl_faktur" readonly/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_no_faktur">No. Faktur </label>
                                        <input type="text" class="form-control" id="p_no_faktur" readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_address_outlet">Alamat </label>
                                        <input type="text" class="form-control" id="p_address_outlet" readonly/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_name_outlet">Nama Outlet </label>
                                        <input type="text" class="form-control" id="p_name_outlet" readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_tgl_do">Tgl DO </label>
                                        <input type="text" class="form-control" id="p_tgl_do" readonly/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_no_do">Nomor DO </label>
                                        <input type="text" class="form-control" id="p_no_do" readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_total">Nilai Faktur </label>
                                        <input type="text" class="form-control" id="p_total" readonly/>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group error-text">
                                        <label for="p_no_do">Tipe Pembayaran</label>
                                        <br/>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="typepayment" id="cash" value="0">
                                            <label class="form-check-label" for="inlineRadio1">Cash</label>
                                          </div>
                                          <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="typepayment" id="cheque" value="1">
                                            <label class="form-check-label" for="inlineRadio2">Cheque</label>
                                          </div>
                                          <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="typepayment" id="nihil" value="2" checked>
                                            <label class="form-check-label" for="inlineRadio3">Nihil</label>
                                          </div>
                                        <input type="number" class="form-control" id="p_titip_bayar" placeholder="Titip Bayar" style="display: none"/>
                                        <div class="form-group" id="tgl_warkat" style="display: none">
                                            <div class="input-daterange input-group" id="xxx">
                                                <span class="input-group-addon bg-primary">
                                                    <i class="fa fa-calendar m-auto px-2"></i> Tgl Warkat &nbsp;
                                                </span>
                                                <input type="text" class="form-control-sm form-control" name="p_tgl_warkat" id="p_tgl_warkat"
                                                    value="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a  class="" data-dismiss="modal">Close</a>
                    <button type="button" class="btn btn-primary" id="terkirim">Terkirim</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" role="dialog" aria-labelledby="modal_address" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Faktur</h3>
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
                                        <label id="detail_title"></label>
                                    </div>
                                    <div class="error-text">
                                        <label id="detail_address"></label>
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
                                                <label>No. Faktur </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_nofaktur"> </label>
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
                                                <label>Tgl. Faktur </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="error-text">
                                                : <label id="detail_tglfaktur"> </label>
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
                                            <tr class=" text-white text-center bg-primary">
                                                <th>PCODE</th>
                                                <th>Nama Barang</th>
                                                <th>Harga/LSN</th>
                                                <th>KRT</th>
                                                <th>LSN</th>
                                                <th>SAT</th>
                                                <th>Jumlah Rp</th>
                                            </tr>
                                        </thead>
                                        <tbody id="datadetail">

                                        </tbody>
                                        <tfoot>
                                            <tr class=" text-white text-center bg-primary">
                                                <th colspan="5"></th>
                                                <th>Jumlah Rp</th>
                                                <th class="text-right" id="grandtotal"></th>
                                            </tr>
                                        </tfoot>


                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a data-dismiss="modal">Close</a>
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
                            "url": "{{ route("deliveryorder.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                            data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_tgl_faktur_do_start    = $('#filter_tgl_faktur_do_start').val();
                                d.filter_tgl_faktur_do_end      = $('#filter_tgl_faktur_do_end').val();
                                d.filter_driver                 = $('#filter_driver').val();
                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },

                    { "data": "tglfaktur","orderable" : false,},
                    { "data": "nofakturpopup","orderable" : false,},
                    { "data": "outletcode","orderable" : false,},
                    { "data": "namecode",
                        "className" : "text-left",
                        "orderable" : false,
                    },
                    { "data" : "addresscode",
                        "className" : "text-left",
                        "orderable" : false,
                    },
                    { "data" : "total",
                        "className" : "text-right",
                        "orderable" : false,
                    },
                    { "data": "status",
                        "className" : "text-left",
                        "orderable" : false,
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

            $('#saveDriver').click(function(){
                var driver = $('#driver').val();
                if(driver==""){
                    Swal.fire('Ups','Silahkan pilih driver terlebih dahulu.','info');
                }else{
                    $.ajax({
                        type: 'POST',
                        url : "{{route('deliveryorder.updatedriver')}}",
                        data: {
                            "_token"         :"{{ csrf_token() }}",
                            "enc_id"         :$('#do_id').val(),
                            "driver"         :driver,
                        },
                        dataType: "json",
                        success: function(data){
                            console.log(data);
                            if (data.success) {
                                Swal.fire('Yes',data.message,'success');
                                table.ajax.reload(null, true);
                                $('#modalDriver').modal('hide');
                                $('#no_faktur').html("");
                                $('#do_id').val("");
                                $("#driver").val("").trigger("change");
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

            $('#terkirim').click(function(){
                var p_titip_bayar = $('#p_titip_bayar').val();
                var p_tgl_warkat  = $('#p_tgl_warkat').val();
                var typepayment   = $("input[name='typepayment']:checked").val();
                var next          = 0;
                if(typepayment==0){
                    if(p_titip_bayar==""){
                        Swal.fire('Ups','Titip Bayar bayar wajib diisi.','info');
                        return false;
                    }else{
                        next = 1;
                    }
                }else if(typepayment==1){
                    if(p_tgl_warkat==""){
                        Swal.fire('Ups','Tgl Warkat wajib diisi.','info');
                    }
                    else{
                        next = 1;
                    }
                }else if(typepayment==2){
                    next = 1;
                }
                if(next==1){
                    $.ajax({
                        type: 'POST',
                        url : "{{route('deliveryorder.pengiriman')}}",
                        data: {
                            "_token"         :"{{ csrf_token() }}",
                            "enc_id"         :$('#do_id_pengiriman').val(),
                            "typepayment"    :typepayment,
                            "p_titip_bayar"  :p_titip_bayar,
                            "p_tgl_warkat"   :p_tgl_warkat,
                        },
                        dataType: "json",
                        success: function(data){
                            console.log(data);
                            if (data.success) {
                                Swal.fire('Yes',data.message,'success');
                                table.ajax.reload(null, true);
                                $('#modalPengiriman').modal('hide');
                                $('#header_pengiriman').html('');
                                $('#do_id_pengiriman').val('');
                                $('#p_tgl_faktur').val('');
                                $('#p_no_faktur').val('');

                                $('#p_address_outlet').val('');
                                $('#p_name_outlet').val('');
                                $('#p_tgl_do').val('');

                                $('#p_no_do').val('');
                                $('#p_total').val('');
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

            $("input[name='typepayment']:radio").change(function(){
                if($(this).val() == '0'){
                    $('#p_titip_bayar').show();
                    $('#tgl_warkat').hide();
                }else if($(this).val() == '1'){
                    $('#p_titip_bayar').hide();
                    $('#tgl_warkat').show();
                }else if($(this).val() == '2'){
                    $('#p_titip_bayar').hide();
                    $('#tgl_warkat').hide();
                }
            });
        });

        function changeDriver(e,key){
            var data = table.row(key).data();
            $('#modalDriver').modal('show');
            $('#no_faktur').html(data.no_faktur);
            $('#do_id').val(data.enc_id);
            $("#driver").val(data.driver_id).trigger("change");
        }

        function pengiriman(e,key){
            var data = table.row(key).data();
            $('#modalPengiriman').modal('show');
            $('#header_pengiriman').html(data.namecode+' / '+data.outletcode);
            $('#do_id_pengiriman').val(data.enc_id);
            $('#p_tgl_faktur').val(data.tglfaktur);
            $('#p_no_faktur').val(data.nofaktur);

            $('#p_address_outlet').val(data.addresscode);
            $('#p_name_outlet').val(data.namecode);
            $('#p_tgl_do').val(data.tgldo);

            $('#p_no_do').val(data.no_do);
            $('#p_total').val(data.total);
            if(data.type_payment!=null){
                $('#terkirim').hide();
                $("input[name='typepayment']:radio").prop('disabled',true);
                if(data.type_payment==0){
                    $('#p_titip_bayar').show();
                    $('#p_titip_bayar').val(data.titipbayar);
                    $('#tgl_warkat').hide();
                    $('#p_titip_bayar').prop('readonly',true);
                    $('#cash').prop('checked',true);
                }else if(data.type_payment==1){
                    $('#tgl_warkat').show();
                    $('#p_tgl_warkat').prop('disabled',true);
                    $('#p_tgl_warkat').val(data.tglwarkat);
                    $('#p_titip_bayar').hide();
                    $('#cheque').prop('checked',true);
                }else if(data.type_payment==2){
                    $('#p_titip_bayar').hide();
                    $('#tgl_warkat').hide();
                    $('#nihil').prop('checked',true);
                }
            }else{
                $('#terkirim').show();
            }

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
