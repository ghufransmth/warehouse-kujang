@extends('layouts.layout')

@section('title', 'Retur Revisi')

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
        <h2>Retur Revisi Invoice</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Retur Revisi Invoice</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content card-body">
                    <form id="formPembayaran" action="{{ route('returrevisi.search') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" class="type">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>No Invoice</label>
                                    <input type="text" name="invoice" class="form-control" value="{{ \Session::get('dataSearch') ? \Session::get('dataSearch')['invoice'] : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputEmail4">Perusahaan</label>
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option selected value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ \Session::get('dataSearch') && \Session::get('dataSearch')['perusahaan'] == $company->id ? 'selected' : '' }}>{{ ucfirst($company->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputEmail4">Customer</label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option selected value="">-- Pilih Customer --</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" {{ \Session::get('dataSearch') && \Session::get('dataSearch')['customer'] == $member->id ? 'selected' : '' }}>{{ ucfirst($member->name) }} - {{ ucfirst($member->prov) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="inputEmail4">No Retur Revisi</label>
                                    <input type="text" name="retur_revisi" class="form-control" value="{{ \Session::get('dataSearch') ? \Session::get('dataSearch')['retur_revisi'] : '' }}">
                                </div>
                            </div>
                            <div class="col-md-1" style="margin-top: 3px;">
                                <button type="submit" class="btn btn-info mt-4"><i class="fa fa-search"></i> Cari</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="result-search">
                    <div class="ibox mt-3">
                        <div class="ibox-content">
                            <ul class="nav nav-tabs myTab" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ \Session::get('type') ? \Session::get('type') == 'retur' ? 'active' : '' : 'active' }}" id="retur-tab" data-toggle="tab" href="#retur" role="tab" aria-controls="retur" aria-selected="true">Retur</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link {{ \Session::get('type') ? \Session::get('type') == 'revisi' ? 'active' : '' : '' }}" id="revisi-tab" data-toggle="tab" href="#revisi" role="tab" aria-controls="revisi" aria-selected="false">Revisi</a>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade {{ \Session::get('type') ? \Session::get('type') == 'retur' ? 'show active' : '' : 'show active' }}" id="retur" role="tabpanel" aria-labelledby="retur-tab">
                                    <div class="result-search pt-5">
                                        <div class="table-responsive">
                                            <table id="table-pembayaran-retur" class="table display table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tanggal</th>
                                                        <th>No Retur</th>
                                                        <th>No PO</th>
                                                        <th>No Invoice</th>
                                                        <th>Perusahaan</th>
                                                        <th>Member - Kota</th>
                                                        <th>No Tanda Terima</th>
                                                        <th>Total Invoice (Rp.)</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($invoices as $inv)
                                                    @php
                                                        $discount = round($inv->subtotal*($inv->discount/100));
                                                        $afterdiscount = round($inv->subtotal - $discount);
                                                        $ppn = round($afterdiscount*(10/100));
                                                        $total = $afterdiscount + $ppn;

                                                        $data_retur = \App\Models\InvoiceReturRevisi::where('invoice_id', $inv->id)
                                                                        ->where('nomor_retur_revisi', 'like', '%RET%')
                                                                        ->whereColumn('qty_before', '<>', 'qty_change')
                                                                        ->orderBy('created_at', 'desc')
                                                                        ->get();
                                                    @endphp
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ count($data_retur) > 0 ? date('Y-m-d', strtotime($data_retur->first()->created_at)) : '-' }}</td>
                                                            <td>
                                                                @if (!$inv->getInvoiceReturRevisi->isEmpty())
                                                                    @if (count($data_retur) > 0)
                                                                        {{ $data_retur->first()->nomor_retur_revisi }}
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td>{{ $inv->purchase_no }}</td>
                                                            <td>
                                                                <a href="{{ route('returrevisi.log.retur', ['RET',base64_encode($inv->id)]) }}">{{ $inv->no_nota }}</a> <br>
                                                                @if (!$inv->getInvoiceReturRevisi->isEmpty())
                                                                    @if (count($data_retur) > 0)
                                                                        <span class="badge badge-info"><i class="fa fa-clipboard-check mr-1"></i> ada data log</span>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td>{{ $inv->getPerusahaan->name }}</td>
                                                            <td>{{ $inv->getMember->name }} - {{ $inv->getMember->prov }}</td>
                                                            <td>{{ $inv->getTandaTerima ? $inv->getTandaTerima->no_tanda_terima : '-' }}</td>
                                                            <td>{{ number_format($total) }}</td>
                                                            <td>
                                                                @if ($inv->pay_status == 0)
                                                                    <a href="{{ route('returrevisi.detail', ['retur', $inv->id]) }}" class="btn btn-primary btn-sm w-100" title="Retur"><i class="fa fa-reply mr-2"></i> RETUR</a>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade {{ \Session::get('type') ? \Session::get('type') == 'revisi' ? 'show active' : '' : '' }}" id="revisi" role="tabpanel" aria-labelledby="revisi-tab">
                                    <div class="result-search pt-5">
                                        <div class="table-responsive">
                                            <table id="table-pembayaran-revisi" class="table display table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Tanggal</th>
                                                        <th>No Invoice</th>
                                                        <th>No PO</th>
                                                        <th>No Revisi</th>
                                                        <th>Perusahaan</th>
                                                        <th style="width: 90px;">Member - Kota</th>
                                                        <th style="width: 90px;">No Tanda Terima</th>
                                                        <th style="width: 90px;">Total Invoice (Rp.)</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($invoices as $inv)
                                                        @php
                                                            $data_revisi = \App\Models\InvoiceReturRevisi::where('invoice_id', $inv->id)
                                                                        ->where('nomor_retur_revisi', 'like', '%REV%')
                                                                        ->whereColumn('price_before', '<>', 'price_change')
                                                                        ->orderBy('created_at', 'desc')
                                                                        ->get();
                                                            // dd($inv->getReturRevisi);
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>{{ count($data_revisi) > 0 ? date('Y-m-d', strtotime($data_revisi->first()->created_at)) : '-' }}</td>
                                                            <td>
                                                                @if (!$inv->getReturRevisi->isEmpty())
                                                                    @if (count($data_revisi) > 0)
                                                                        {{ $data_revisi->first()->nomor_retur_revisi }}
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td>{{ $inv->purchase_no }}</td>
                                                            <td>
                                                                <a href="{{ route('returrevisi.log.retur', ['REV',base64_encode($inv->id)]) }}">{{ $inv->no_nota }}</a> <br>
                                                                @if(!$inv->getReturRevisi->isEmpty())
                                                                    @if (count($data_revisi) > 0)
                                                                        <span class="badge badge-info"><i class="fa fa-clipboard-check mr-1"></i> ada data log</span>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                            <td>{{ $inv->getPerusahaan->name }}</td>
                                                            <td>{{ $inv->getMember->name }} - {{ $inv->getMember->prov }}</td>
                                                            <td>{{ $inv->getTandaTerima ? $inv->getTandaTerima->no_tanda_terima : '-' }}</td>
                                                            <td>{{ number_format($inv->total) }}</td>
                                                            <td>
                                                                @if ($inv->pay_status == 0)
                                                                    <a href="{{ route('returrevisi.detail', ['revisi', $inv->id]) }}" class="btn btn-success btn-sm w-100" title="revisi"><i class="fa fa-edit mr-2"></i> REVISI</a>
                                                                @endif
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
</div>

@endsection
@push('scripts')
<script>
    $(document).ready(function () {
        $('.select2').select2({allowClear: true});

        $('#table-pembayaran-retur').dataTable({
            "dom": '<"html5">lftip',
            "stateSave"  : true,
        });
        $('#table-pembayaran-revisi').dataTable({
            "dom": '<"html5">lftip',
            "stateSave"  : true,
        });
    })
</script>
@if (session()->has('status'))
    <script>
        Swal.fire({
            icon: 'success',
            text: '{{ session('status') }}'
        })
    </script>
@endif
<script>
    $('.myTab a').click(function(e) {
        e.preventDefault();
        $(this).tab('show');
        var id =$(e.target).attr("href").substr(1);
        window.location.hash = id;
        $('.type').val(id);
    });

    // on load of the page: switch to the currently selected tab
    var hash = window.location.hash;
    $('.myTab a[href="' + hash + '"]').tab('show');
</script>
@endpush
