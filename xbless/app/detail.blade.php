@extends('layouts.layout')

@section('title', 'Tanda Terima')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.css" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 33px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding-top: 2px;
        }
        .dataTables_wrapper {
            padding: 0;
        }
    </style>
@endsection

@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Input Pembayaran</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Input Pembayaran</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <p class="h5 mb-4 float-left">Halaman No Tanda Terima <b>{{ $invoice_tanda_terima->no_tanda_terima }}</b> Customer <b>{{ $invoice_tanda_terima->getMember->name }} - {{ $invoice_tanda_terima->getMember->prov }}</b></p>
                    <a href="{{ route('pembayaran.index') }}" class="btn btn-secondary float-right"><i class="fa fa-reply"></i> Kembali</a>
                    <div class="table-responsive">
                        <table class="table display table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>No Invoice</th>
                                    <th>Total Nota (Rp.)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $querydetail = \App\Models\InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder');
                                    $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
                                    $querydetail->where('invoice_tanda_terima.no_tanda_terima', $invoice_tanda_terima->no_tanda_terima);

                                    $detail_tanda_terima = $querydetail->get();

                                    $total = 0;
                                    foreach ($detail_tanda_terima as $invoice) {
                                        $total = $total + $invoice->nilai;
                                    }
                                @endphp
                                @foreach ($detail_tanda_terima as $invoice)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $invoice->no_nota }}</td>
                                        <td class="text-right">{{ number_format($invoice->nilai) }}</td>
                                    </tr>

                                @endforeach
                                    <tr class="text-right">
                                        <td colspan="2">Total Nota</td>
                                        <td>{{ number_format($total) }}</td>
                                    </tr>
                                    <tr class="text-right">
                                        <td colspan="2">Sudah Dibayar</td>
                                        <td>{{ number_format($invoice_tanda_terima->invoicePayment->sum('sudah_dibayar')) }}</td>
                                    </tr>
                                    <tr class="text-right">
                                        <td colspan="2">Sisa Pembayaran</td>
                                        <td>{{ number_format($total-$invoice_tanda_terima->invoicePayment->sum('sudah_dibayar')) }}</td>
                                    </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="ibox">
                @if ($total-$invoice_tanda_terima->invoicePayment->sum('sudah_dibayar') > 6000)
                <div class="ibox-content card-body">
                    <form action="{{ route('pembayaran.store', $invoice_tanda_terima->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-11">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Jenis Pembayaran</label>
                                            <select class="form-control select2 @error('jenis_pembayaran') is-invalid @enderror" id="jenis_pembayaran" name="jenis_pembayaran" required>
                                                <option selected disabled>-- Pilih Jenis Pembayaran --</option>
                                                @foreach ($payments as $payment)
                                                    <option value="{{ $payment->id }}">{{ $payment->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('jenis_pembayaran')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Tanggal</label>
                                            <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control @error('date') is-invalid @enderror" required>
                                            @error('date')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Nilai</label>
                                            <input type="text" name="nilai" class="form-control @error('nilai') is-invalid @enderror numberformat" value="{{ old('nilai') }}" required>
                                            @error('nilai')
                                                <div id="validationServer03Feedback" class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row-giro"></div>
                            </div>
                            <div class="col-md-1" style="margin-top: 3px;">
                                <button type="submit" class="btn btn-info mt-4">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
                <div class="result-search">
                    <div class="ibox mt-3">
                        <div class="ibox-content">
                            <div class="card-header card-body mb-3 border-0">
                                <h4>Data Piutang Customer</h4>
                            </div>
                            <div class="table-responsive">
                                <table id="table-pembayaran" class="table display table-bordered p-0">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Pembayaran</th>
                                            <th>Nama dan No.Giro / Cek</th>
                                            <th>Keterangan</th>
                                            <th>Total Pembayaran</th>
                                            <th>Sudah Dibayar</th>
                                            <th>Sisa Pembayaran</th>
                                            <th>Tanggal Setoran</th>
                                            <th>Tanggal Cair</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice_tanda_terima->invoicePayment as $key => $inv_payment)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $inv_payment->getPayment->name }}</td>
                                                <td>{{ $inv_payment->number }}</td>
                                                <td>{{ $inv_payment->keterangan }}</td>
                                                <td class="text-right">{{ number_format($inv_payment->total_pembayaran) }}</td>
                                                <td class="text-right">{{ number_format($inv_payment->sudah_dibayar) }}</td>
                                                <td class="text-right">{{ number_format($inv_payment->sisa) }}</td>
                                                <td>{{ date('Y-m-d', strtotime($inv_payment->payment_date)) }}</td>
                                                <td>{{ $inv_payment->liquid_date ? date('Y-m-d', strtotime($inv_payment->liquid_date)) : '-' }}</td>
                                                <td>
                                                    @if (count($invoice_tanda_terima->invoicePayment) == $key+1)
                                                        <a href="javascript:" class="btn btn-danger btn-sm deleteHistory" rel="{{ $inv_payment->id }}" rel1="{{ $inv_payment->no_tanda_terima }}" rel2="{{ $inv_payment->sudah_dibayar }}"><i class="fa fa-trash"></i></a>
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

@endsection
@push('scripts')
<script>
    $(document).ready(function () {
        $('.select2').select2({allowClear: true});

        $('#table-pembayaran').dataTable();
    })
</script>
<script type="text/javascript">
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
</script>
<script>
    let rowGiro =   '<div class="row giro">'+
                        '<div class="col-md-4">'+
                            '<div class="form-group">'+
                                '<label>Tanggal Cair</label>'+
                                '<input type="date" name="tanggal_cair" value="{{ date('Y-m-d') }}" class="form-control @error('date') is-invalid @enderror" required>'+
                                '@error('date')'+
                                    '<div id="validationServer03Feedback" class="invalid-feedback">'+
                                        '{{ $message }}'+
                                    '</div>'+
                                '@enderror'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<div class="form-group">'+
                                '<label>Nama dan No Giro / Cek</label>'+
                                '<input type="text" name="no_giro_cek" class="form-control @error('no_giro_cek') is-invalid @enderror" value="{{ old('no_giro_cek') }}" required>'+
                                '@error('no_giro_cek')'+
                                    '<div id="validationServer03Feedback" class="invalid-feedback">'+
                                        '{{ $message }}'+
                                    '</div>'+
                                '@enderror'+
                            '</div>'+
                        '</div>'+
                        '<div class="col-md-4">'+
                            '<div class="form-group">'+
                                '<label>Keterangan</label>'+
                                '<input type="text" name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" value="{{ old('keterangan') }}" required>'+
                                '@error('keterangan')'+
                                    '<div id="validationServer03Feedback" class="invalid-feedback">'+
                                        '{{ $message }}'+
                                    '</div>'+
                                '@enderror'+
                            '</div>'+
                        '</div>'+
                    '</div>';

        let returRevisi = '<div class="row giro">'+
                        '<div class="col-md-4">'+
                            '<div class="form-group">'+
                                '<label>No. Retur/Revisi</label>'+
                                '<input type="text" name="no_giro_cek" class="form-control @error('no_giro_cek') is-invalid @enderror" value="{{ old('no_giro_cek') }}" required>'+
                                '@error('no_giro_cek')'+
                                    '<div id="validationServer03Feedback" class="invalid-feedback">'+
                                        '{{ $message }}'+
                                    '</div>'+
                                '@enderror'+
                            '</div>'+
                        '</div>'+
                    '</div>';

        $('#jenis_pembayaran').change(function () {
            let value = $(this).val()
            if (value == 1 || value == 4) {
                $('.row-giro').html(rowGiro)
            }else if(value == 5 || value == 6) {
                $('.row-giro').html(returRevisi)
            } else {
                $('.giro').remove();
            }
        })

</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-sweetalert/1.0.1/sweetalert.min.js"></script>
<script>
    $(".deleteHistory").click( function(){
        var id = $(this).attr('rel');
        var idPayment = $(this).attr('rel');
        var noTandaTerima = $(this).attr('rel1');
        var bayar = $(this).attr('rel2');
        swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this imaginary file!",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false
        },
        function(){
            window.location.href="/pembayaran/delete/"+idPayment+"/"+noTandaTerima+"/"+bayar;
        });
    });
</script>

@if (session()->has('status'))
    <script>
        swal("Good job!", "{{ session('status') }}", "success")
    </script>
@endif
@if (session()->has('error'))
    <script>
        swal("Oopss", "{{ session('error') }}", "warning")
    </script>
@endif
@endpush
