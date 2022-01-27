@extends('layouts.layout')
@section('title', 'Form Retur Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Faktur Retur Pembelian</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Detail Faktur Retur</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="card mb-4">
                    <div class="card-header d-flex flex-row align-items-center justify-content-between" style="border-bottom: 1px solid rgba(0,0,0,.1);">
                        <h5 class="m-0 font-weight-bold text-primary">Nomor Faktur #</h5>
                    </div>
                    <div class="row p-3">
                        <div class="col-md-6">
                            <div class="col-md-6 text-right">
                                <h4>FAKTUR #</h4>
                                <p>Date : <b></b></p>
                            </div>
                        </div>
                    </div>
                    <div class="ibox mt-3">
                        <div class="ibox-content">
                            <form action="">
                                {{ csrf_field() }}
                                <div class="table-responsive">
                                    <table class="table p-0 table-hover table-striped" id="table1">
                                        <thead class="text-white text-center bg-primary">
                                            <tr>
                                                <th>Produk</th>
                                                <th>Harga</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <label for="">Catatan</label>
                                        <textarea name="note" id="" class="form-control" cols="30" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="{{ route('retur_pembelian.index') }}" class="btn btn-secondary"><i class="fa fa-reply mr-1"></i> Kembali</a>
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save mr-1"></i> Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection
