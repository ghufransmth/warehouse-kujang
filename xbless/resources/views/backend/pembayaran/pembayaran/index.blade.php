@extends('layouts.layout')
@section('title', 'Pembayaran')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-6">
        <h2>Pembayaran</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Keuangan</a>
            </li>
        </ol>
    </div>
    <div class="col-sm-6">
        <div class="title-action">
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group" id="data_5">
                        <label class="font-normal">Range select</label>
                        <div class="input-daterange input-group" id="datepicker">
                            <span class="input-group-addon px-3 bg-white border"><i class="fa fa-calendar"></i></span>
                            <input type="text" class="form-control-sm form-control" name="start" value="05/14/2014">
                            <span class="input-group-addon px-3 bg-primary">to</span>
                            <input type="text" class="form-control-sm form-control" name="end" value="05/22/2014">
                            <span class="input-group-addon px-3 bg-white  border"><i class="fa fa-calendar"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label class="font-normal">Salesman</label>
                        <div>
                            <select class="select2_salesman form-control">
                                <option></option>
                                <option value="Sales1">Sales 1</option>
                                <option value="Sales2">Sales 2</option>
                                <option value="Sales3">Sales 3</option>
                                <option value="Sales4">Sales 4</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    <h5>P embayaran</h5>
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
                        <table id="table_pembayaran" class="table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Nomor Faktur</th>
                                    <th>Outlet</th>
                                    <th>Sales</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th>#</th>
                                    <th>Nomor Faktur</th>
                                    <th>Outlet</th>
                                    <th>Sales</th>
                                    <th>Status</th>
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
    $(document).ready(function () {
            $(".select2_salesman").select2();
            $('#table_pembayaran').DataTable({
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
</script>
@endpush
