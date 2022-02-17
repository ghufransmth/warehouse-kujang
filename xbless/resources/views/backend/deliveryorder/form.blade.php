@extends('layouts.layout')
@section('title', 'Delivery Order')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Delivery Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('deliveryorder.index')}}">Delivery Order</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Tambah Delivery Order</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-4">
        <br/>
        <div class="d-flex pt-4" style="padding-left: 0.4rem!important;">
            <div class="form-group">
                <div class="col-sm-12">
                    <select class="form-control select2" id="driver" name="driver" width="100%">
                        <option value="">Pilih Driver</option>
                        @foreach($driver as $key => $row)
                            <option value="{{$row->id}}">{{ucfirst($row->nama)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <button type="button" id="createDO" class="btn btn-success" data-toggle="tooltip" data-placement="top"
                        title="Create DO"><span class="fa fa-pencil-square-o"></span>&nbsp; Proses DO</button>
                </div>
            </div>
        </div>
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
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_start" id="filter_tgl_faktur_start"
                                    value="{{$filter_tgl_faktur_start}}" />
                                <span class="input-group-addon bg-primary px-2">Sampai </span>
                                <input type="text" class="form-control-sm form-control" name="filter_tgl_faktur_end" id="filter_tgl_faktur_end" value="{{$filter_tgl_faktur_end}}" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <select class="form-control select2" id="sales" name="sales" width="100%">
                                    <option value="">Semua Salesman</option>
                                    @foreach($sales as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedsales == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->nama)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-success" id="filter" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class=" text-white text-center bg-primary">
                                    <th class=""></th>
                                    <th>Tgl Faktur</th>
                                    <th>No. Faktur</th>
                                    <th>Outlet Code</th>
                                    <th>Nama Outlet</th>
                                    <th>Alamat Outlet</th>
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
@endsection
@push('scripts')
<link rel="stylesheet" type="text/css"  href="https://cdn.datatables.net/select/1.3.4/css/select.dataTables.min.css">
<script type="text/javascript" src="https://cdn.datatables.net/select/1.3.4/js/dataTables.select.min.js"></script>

<script type="text/javascript">
    var table,tabledata,table_index;
        $(document).ready(function(){
            $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
            });
            var rows_selected = [];
            $(".select2").select2();
            $('#date1 .input-daterange').datepicker({
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
                format: "dd-mm-yyyy"
            });
            table= $('#table1').DataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 100,
                "responsive": true,
                "stateSave"  : true,
                "select" : true,
                "dom": '<"html5">lftip',
                "ajax":{
                    "url": "{{ route("deliveryorder.getdatapenjualan") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                        d._token= "{{csrf_token()}}";
                        d.filter_tgl_faktur_start= $('#filter_tgl_faktur_start').val();
                        d.filter_tgl_faktur_end= $('#filter_tgl_faktur_end').val();
                        d.filter_sales= $('#sales').val();
                    }
                },
                "select": {
                    "style": 'multi',
                    "selector": 'td:first-child'
                },
                "columns": [

                    {
                        "data": "check",
                        "orderable" : false,
                    },
                    { "data": "tglfaktur"},
                    { "data": "nofaktur"},
                    { "data": "outletcode" },
                    { "data": "namecode",
                        "className" : "text-left",
                    },
                    { "data" : "addresscode",
                        "className" : "text-left",
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
            $('#table1 tbody').on('click', 'input[type="checkbox"]', function(e){
                var $row = $(this).closest('tr');
                var data = table.row($row).data();
                var rowId = data.id;
                var index = $.inArray(rowId, rows_selected);
                if(this.checked && index === -1){
                    rows_selected.push(rowId);
                } else if (!this.checked && index !== -1){
                    rows_selected.splice([index, 1]);
                    console.log(rows_selected);
                }
                if(this.checked){
                    $row.addClass('selected');
                } else {
                    $row.removeClass('selected');
                }
                e.stopPropagation();
            });

            $('#createDO').on("click", function (e){
                e.preventDefault();
                var driver  = $('#driver').val();
                var count   = 0;
                var insert  = [];
                if(driver==""){
                    Swal.fire('Ups','Silahkan pilih driver terlebih dahulu.','info');
                }else{
                    $('#table1').find('input[type="checkbox"]:checked:not(:disabled)').each(function () {
                        if($(this).is(":checked"))
                        {
                            count += 1;
                            insert.push([$(this).attr('data-id')]);
                        }
                    });
                    if (count > 0) {
                        Swal.fire({
                            title: "Konfirmasi?",
                            text: "Yakin akan menambahkan No Faktur ke Delivery Order",
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "Ya",
                            cancelButtonText:"Close",
                            confirmButtonColor: "#20b393",
                            closeOnConfirm: false,
                            closeOnCancel: false
                        }).then(function(result) {
                            if(result.isConfirmed){
                                $.ajax({
                                type: 'POST',
                                url : "{{route('deliveryorder.simpan')}}",
                                data: {
                                    "_token"         :"{{ csrf_token() }}",
                                    "insert"         :insert,
                                    "driver"         :$('#driver').val(),
                                },
                                dataType: "json",
                                success: function(data){
                                    console.log(data);
                                    if (data.success) {
                                        Swal.fire('Yes',data.message,'success');
                                        table.ajax.reload(null, true);

                                    } else {
                                        Swal.fire('Ups',data.message,'info');
                                    }
                                },
                                error: function(data){
                                    console.log(data);
                                }
                            });
                            }
                        });
                    }else {
                        Swal.fire('Ups','Tidak ada data faktur yang dipilih','info');
                    }
                }

            });
            table.on('select', function ( e, dt, type, indexes ){
                table_index = indexes;
                var rowData = table.rows( indexes ).data().toArray();
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

       });

</script>
@endpush
