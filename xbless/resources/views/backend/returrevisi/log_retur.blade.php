@extends('layouts.layout')

@section('title', 'Detail Perubahan Data Invoice')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 33px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-top: 2px;
        }
    </style>
@endsection

@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Perubahan Data Invoice</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Detail Perubahan Data Invoice</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="result-search">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $type == 'RET' ? 'active' : '' }}" id="retur-tab" data-toggle="tab" href="#retur" role="tab" aria-controls="retur" aria-selected="true">Retur</a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ $type == 'REV' ? 'active' : '' }}" id="revisi-tab" data-toggle="tab" href="#revisi" role="tab" aria-controls="revisi" aria-selected="false">Revisi</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade  {{ $type == 'RET' ? 'show active' : '' }}" id="retur" role="tabpanel" aria-labelledby="retur-tab">
                            <div class="ibox mt-3">
                                <div class="ibox-content">
                                    <div class="table-responsive">
                                        <table id="table-pembayaran" class="table display table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tanggal</th>
                                                    <th>No Retur/Revisi</th>
                                                    <th>No Invoice</th>
                                                    <th>Qty</th>
                                                    <th>Harga/Unit</th>
                                                    <th>Harga Total</th>
                                                    <th>Catatan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $data_retur = \App\Models\InvoiceReturRevisi::where('invoice_id', $invoice->id)->where('nomor_retur_revisi', 'like', '%RET%')
                                                    ->whereColumn('qty_before', '<>', 'qty_change')
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();
                                                @endphp
                                                @foreach ($data_retur as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ date('d M Y', strtotime($item->created_at)) }}</td>
                                                        <td>{{ $item->nomor_retur_revisi }}</td>
                                                        <td>{{ $invoice->no_nota }}</td>
                                                        <td>
                                                            @if ($item->qty_before == $item->qty_change)
                                                                Tidak ada perubahan Qty
                                                            @else
                                                                Perubahan Qty : {{$item->qty_before}} {{ $item->getInvoiceDetail->product->getsatuan->name }} <br>
                                                                menjadi {{$item->qty_change}} {{ $item->getInvoiceDetail->product->getsatuan->name }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->price_before == $item->price_change)
                                                                Tidak ada perubahan Harga
                                                            @else
                                                                Perubahan Harga : {{$item->price_before}}  <br>
                                                                menjadi {{$item->price_change}}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->total_before == $item->total_change)
                                                                Tidak ada perubahan total harga
                                                            @else
                                                                Perubahan total harga : {{$item->total_before}}  <br>
                                                                menjadi {{$item->total_change}}
                                                            @endif
                                                        </td>
                                                        <td>-</td>
                                                        <td>
                                                            <a href="{{ route('returrevisi.log.retur.print', base64_encode($item->id)) }}" class="btn btn-info" target="_blank"><i class="fa fa-print mr-2"></i> Print</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ $type == 'REV' ? 'show active' : '' }}" id="revisi" role="tabpanel" aria-labelledby="revisi-tab">
                            <div class="ibox mt-3">
                                <div class="ibox-content">
                                    <div class="table-responsive">
                                        <table id="table-pembayaran" class="table display table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Tanggal</th>
                                                    <th>No Retur/Revisi</th>
                                                    <th>No Invoice</th>
                                                    <th>Qty</th>
                                                    <th>Harga/Unit</th>
                                                    <th>Harga Total</th>
                                                    <th>Catatan</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $data_revisi = \App\Models\InvoiceReturRevisi::where('invoice_id', $invoice->id)->where('nomor_retur_revisi', 'like', '%REV%')
                                                    ->whereColumn('price_before', '<>', 'price_change')
                                                    ->orderBy('created_at', 'desc')
                                                    ->get();
                                                @endphp
                                                @foreach ($data_revisi as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ date('d M Y', strtotime($item->created_at)) }}</td>
                                                        <td>{{ $item->nomor_retur_revisi }}</td>
                                                        <td>{{ $invoice->no_nota }}</td>
                                                        <td>
                                                            @if ($item->qty_before == $item->qty_change)
                                                                Tidak ada perubahan Qty
                                                            @else
                                                                Perubahan Qty : {{$item->qty_before}} {{ $item->getInvoiceDetail->product->getsatuan->name }}  <br>
                                                                menjadi {{$item->qty_change}} {{ $item->getInvoiceDetail->product->getsatuan->name }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->price_before == $item->price_change)
                                                                Tidak ada perubahan Harga
                                                            @else
                                                                Perubahan Harga : {{$item->price_before}}  <br>
                                                                menjadi {{$item->price_change}}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($item->total_before == $item->total_change)
                                                                Tidak ada perubahan total harga
                                                            @else
                                                                Perubahan total harga : {{$item->total_before}}  <br>
                                                                menjadi {{$item->total_change}}
                                                            @endif
                                                        </td>
                                                        <td>{{ $item->note }}</td>
                                                        <td>
                                                            <a href="{{ route('returrevisi.log.retur.print', base64_encode($item->id)) }}" class="btn btn-info" target="_blank"><i class="fa fa-print mr-2"></i> Print</a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
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
        $('.select2').select2({allowClear: true});

        $('#table-pembayaran').dataTable({
            "dom": '<"html5">lftip',
        });
    })
</script>
@endpush
