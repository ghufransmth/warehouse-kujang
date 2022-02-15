@extends('layouts.layout')
@section('title','LAPORAN PEMBELIAN')
@section('content')
<style>
    .swal2-container{
        z-index: 99999 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>DETAIL BARANG MASUK</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>DETAIL BARANG MASUK</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <form id="submitData" name="submitData" class="text-right">
                        <div class="pr-4">
                            <div class="d-flex flex-row-reverse row">
                                <div class="col-xs-3">
                                    <a href="{{ route('reportbarangmasuk.pdf', $enc_id) }}" class="btn btn-danger" id="ExportPdf"><span
                                            class="fa fa-file-pdf-o"></span>
                                        Export PDF</a>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <a href="{{ route('reportbarangmasuk.excel', $enc_id) }}" class="btn btn-primary" id="ExportExcel"><span
                                            class="fa fa-file-excel-o"></span> Export Excel </a>&nbsp;
                                </div>
                                <div class="col-xs-3">
                                    <a href="{{ route('reportbarangmasuk.print', $enc_id) }}" class="btn btn-secondary" id="Print"><span
                                            class="fa fa-print"></span>
                                        Print</a>&nbsp;
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="hr-line-dashed"></div>
                    <div class="table-responsive">
                        <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Kode Product</th>
                                    <th>Tgl Faktur</th>
                                    <th>Nama Product</th>
                                    <th>Qty (PCS)</th>
                                    <th>Harga Product</th>
                                    <th>Total Harga</th>
                                    {{-- <th>Status Bayar</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail_pembelian as $key => $value)
                                <tr>
                                    <td width="5%">{{ $key+1 }}</td>
                                    <td>{{ $value->product_id }}</td>
                                    <td>{{ $pembelian->tgl_faktur }}</td>
                                    <td>{{ $value->getproduct->nama }}</td>
                                    <td>{{ $value->qty }}</td>
                                    <td>{{ $value->product_price }}</td>
                                    <td>{{ $value->total }}</td>
                                    {{-- <td>Status Bayar</td> --}}
                                </tr>
                                @endforeach
                            </tbody>
                            {{-- <tfoot>
                                <tr class="text-white text-center bg-primary">
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
@endpush
