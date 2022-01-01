@extends('layouts.layout')
@section('title', 'Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Order Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Order Produk</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        @can('produk.tambah')
          <a href="{{ route('pembelian.tambah')}}" class="btn btn-success"><span class="fa fa-pencil-square-o"></span>&nbsp; Order Barang Baru</a>
        @endcan

    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>No Faktur</th>
                            <th>Perusahaan</th>
                            <th>Pabrik</th>
                            <th>Tgl Faktur <br> Tgl Transaksi</th>
                            <th>Status</th>
                            <th>Dibuat<br>Diapprove</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="8">
                                <ul class="pagination float-right"></ul>
                            </td>
                        </tr>
                        </tfoot>
                    </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modal_image_produk" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="title_modal"></span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" id="imageprodukdataid" value="">
                <div class="modal-body" id="image_produk">
                    <div class="row" id="img-data">

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
