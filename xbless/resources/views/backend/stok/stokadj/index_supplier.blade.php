@extends('layouts.layout')
@section('title', 'Adjustment Stok')
@section('content')
<style>
    .swal2-container {
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Informasi Stok</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Informasi Stok</a>
            </li>
        </ol>
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
                            {{-- <label class="col-sm-2 col-form-label">Supplier : </label>
                            <div class="col-sm-3 error-text">
                                <select class="form-control select2" id="supplier" name="supplier">
                                    <option value="">Pilih Supplier</option>
                                    @foreach($perusahaan as $key => $row)
                                    <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <label class="col-sm-1 col-form-label">Gudang :</label>
                            <div class="col-sm-3 error-text">
                                <select class="form-control select2" id="gudang" name="gudang">
                                    <option value="">Pilih Gudang</option>
                                    @foreach($gudang as $key => $row)
                                    <option value="{{$row->id}}"{{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                    @endforeach
                                </select>
                                </select>
                            </div>
                            <div class="col-sm-1 error-text">
                               <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                        <div class="hr-ine-dashed"></div>
                    </form>
                    <div class="hr-line-dashed"></div>

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                            <thead>
                                <tr>
                                    <th width="5%" rowspan='2' >No</th>
                                    <th rowspan='2'>Kode</th>
                                    <th rowspan='2'>Nama</th>
                                    <th rowspan='2'>Satuan</th>
                                    <th colspan='5' class="text-center">Gudang Stok</th>
                                </tr>
                                <tr>
                                    <th>Gudang Baik</th>
                                    <th>Gudang BS</th>
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
</div>
@endsection
@push('scripts')
<script>
    var table,tabledata,table_index,tableproduct;
    $(document).ready(function(){
        $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
           });
        table= $('#table1').DataTable({
           "processing": true,
           "serverSide": true,
           "pageLength": 25,
           "select" : true,
           "ajax":{
            "url": "{{ route("adjstok.getdatasup") }}",
            "dataType": "json",
            "type": "POST",
            data: function(d){
                d._token= "{{csrf_token()}}";
                // d.filter_supplier_admin         = $('#supplier').val();
                d.filter_gudang_admin           = $('#gudang').val();
            }
           },
           "columns":[
                {
                    "data": "no",
                    "orderable": false,
                },
                {
                    "data": "kode_product"
                },
                {"data": "nama_product"},
                {"data": "satuan"},
                { "data": "stock_baik"},
                { "data": "stock_bs"},
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
            if($('#supplier').val()==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                return false;
            }else if($('#gudang').val()==''){
                Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
                return false;
            }else{
                table.ajax.reload(null, false);
            }

        });
    })
</script>
@endpush
