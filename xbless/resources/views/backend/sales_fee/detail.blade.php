@extends('layouts.layout')

@section('title', 'Detail Fee Sales ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Fee Sales</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('feesales.index')}}">Fee Sales</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('feesales.index')}}">Kembali</a>
    </div>
</div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        @if(session('message'))
                            <div class="alert alert-{{session('message')['status']}}">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            {{ session('message')['desc'] }}
                            </div>
                        @endif

                    </div>
                    <div class="ibox-content">
                        <form id="submitData">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($expedisi)? $enc_id : ''}}">
                            <div class="form-group row" style="margin-left: 5px;">
                                <label class="col-sm-2 col-xs-6 col-form-label">Kode Sales </label>
                                <div class="col-sm-4 error-text col-xs-6">
                                    <p class="col-form-label"> {{$sales->code}}</p>
                                </div>
                                <label class="col-sm-2 col-xs-6 col-form-label">Nama Sales </label>
                                <div class="col-sm-4 error-text col-xs-6">
                                    <p class="col-form-label"> {{$sales->name}}</p>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                                <table id="table1" class="table display table-bordered">
                                <thead>
                                    <tr>
                                        <th width='5%'>No</th>
                                        <th>No Nota</th>
                                        <th>Total Nota (Rp.)</th>
                                        <th>Fee (Rp.)</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('feesales.index')}}">Kembali</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
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
           table= $('#table1').DataTable({
           "processing": true,
           "serverSide": true,
           "stateSave"  : true,
           "deferRender": true,
           "pageLength": 50,
           "select" : true,
           "responsive": true,
           "stateSave"  : true,
           "dom": '<"html5">lftip',
           "ajax":{
                "url": "{{route("feesales.detailgetdata")}}",
                "dataType": "json",
                "type": "POST",
                data: function ( d ) {
                    d._token= "{{csrf_token()}}";
                    d.id= "{{$sales->id}}";
                }
            },
           "columns": [

               { "data": "no","orderable" : false,},
               { "data": "no_nota","orderable" : false, },
               { "data": "ttl_nota", "orderable" : false, "className" : "text-right", },
               { "data": "fee", "orderable" : false, "className" : "text-right",},
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
    });
</script>
@endpush
