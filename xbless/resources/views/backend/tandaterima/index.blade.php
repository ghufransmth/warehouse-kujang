@extends('layouts.layout')
@section('title', 'Tanda Terima')
@section('content')

<style>
    .swal2-container{
        z-index: 1000000;
    }

    .select2-container--open{
        z-index: 1000000;
    }
</style>

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Invoice Tanda Terima</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Invoice Tanda Terima</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
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
                        <label class="col-sm-2 col-form-label">Customer : </label>
                        <div class="col-sm-3 error-text">
                            <select class="form-control select2" id="customer" name="customer">
                                <option value="">Pilih Customer</option>
                                @foreach($members as $key => $member)
                                    <option value="{{$member->id}}">{{ucfirst($member->name)}} - {{ucfirst($member->city)}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-2 error-text">
                           <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>No Tanda Terima</th>
                            <th>No Invoice</th>
                            <th>Customer</th>
                            <th>Kota</th>
                            <th>Perusahaan</th>
                            <th>Tanggal Dibuat</th>
                            <th style="width: 140px;">Aksi</th>
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
    <div class="modal fade" id="modal_pilihan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="title_modal_nota">Pilih Menu</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <!-- <input type="hidden" id="id_invoice_menu"> -->
                <div class="modal-body">
                    <div class='form-group has-feedback text-center' id="list_data_detail">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_pengiriman" data-focus="false" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="title_modal_nota">Input Pengiriman</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="submitData">
                    <input type="hidden" id="id_tanter" name="id_tanter">
                    <div class="modal-body">
                        <div class='form-group has-feedback' id="content_pengiriman">

                        </div>
                    </div>
                </form>
                <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="save_pengiriman">Ya</button>
              </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script type="text/javascript">
    $.fn.modal.Constructor.prototype.enforceFocus = function() {};
    var table,tabledata,table_index;
        $('.select2').select2({allowClear: true});
    $(document).ready(function(){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        table= $('#table1').DataTable({
            "processing": true,
            "serverSide": true,
            "stateSave"  : true,
            "pageLength": 30,
            "dom": '<"html5">lftip',
            "lengthMenu": [[30, 60, 100, -1], [30, 60, 100, "All"]],
            "select" : true,
            "ajax":{
                    "url": "{{ route("tandaterima.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                        d._token= "{{csrf_token()}}";
                        d.filter_perusahaan = $('#perusahaan').val();
                        d.filter_customer = $('#customer').val();
                    }
                },

            "columns": [
                {
                    "data": "no",
                    "orderable" : false,
                },
                { "data": "no_tanda_terima"},
                { "data": "no_nota"},
                { "data": "member_name"},
                { "data": "kota"},
                { "data": "perusahaan"},
                { "data": "tanggal_dibuat"},
                { "data" : "action",
                    "orderable" : false,
                    "className" : "text-center",
                },
            ],
            responsive: true,
            language: {
                search: "<span>Cari No Tanda Terima / No Invoice :</span> _INPUT_",
                searchPlaceholder: "Cari Data",
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
            columnDefs: [{
                    "targets": 'no-sort',
                    "orderable": false,
            }]
        });
        $('#cariData').click(function(){
            table.ajax.reload(null, false);
        });
        table.on('select', function ( e, dt, type, indexes ){
            table_index = indexes;
            var rowData = table.rows( indexes ).data().toArray();
        });
    });
    $(document).on('click', '#save_pengiriman', function(){
        var form = $('#submitData').serialize()
        // console.log(form)
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url : "{{route('pembayaran.simpan_pengiriman')}}",
            data: form,
            beforeSend: function () {
                Swal.showLoading();
            },
            success: function(response){
                // console.log(response)
                if (response.success) {
                    Swal.fire('Yes',response.message,'info');
                    $('#modal_pengiriman').modal('hide')
                } else {
                    Swal.fire('Ups',response.message,'info');
                }
                Swal.hideLoading();
            },
            complete: function () {
                Swal.hideLoading();
                $('#simpan').removeClass("disabled");
            },
            error: function(data){
                $('#simpan').removeClass("disabled");
                Swal.hideLoading();
                Swal.fire('Ups','Ada kesalahan pada sistem','info');
                // console.log(data);
            }
        });
    })
</script>
<script>
    function pilih_menu(idinv, menu){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("tandaterima.menu_data_list") }}',
            data: {
                enc_id: idinv,
                menu: menu
            },
            success: function(response){
                $('#list_data_detail').html(response.list)
            }
        })
    }

    function input_pengiriman(idinv){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("pembayaran.input_pengiriman") }}',
            data: {
                enc_id: idinv
            },
            success: function(response){
                // console.log(response)
                $('#id_tanter').val(idinv)
                $('#content_pengiriman').html(response.html)
                let coba = document.createElement('script')
                coba.text = response.js
                document.body.appendChild(coba)

                $('input.numberformat').keyup(function(event) {

                // skip for arrow keys
                if(event.which >= 37 && event.which <= 40) return;

                // format number
                $(this).val(function(index, value) {
                        return value
                        .replace(/\D/g, "")
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
                        ;
                    });
                });
            }
        })
    }

    function menu_tanda_terima(idinv, menu){
        window.open('{{route("tandaterima.tanda_terima",[null])}}/'+menu+'/'+idinv,'_blank');
    }
    function menu_pengiriman(idinv, menu){
        window.open('{{route("tandaterima.pengiriman",[null])}}/'+menu+'/'+idinv,'_blank');
    }
</script>
@endpush
