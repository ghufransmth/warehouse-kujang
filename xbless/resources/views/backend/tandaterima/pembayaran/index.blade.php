@extends('layouts.layout')

@section('title', 'Tanda Terima')

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                <div class="ibox-content card-body">
                    <form id="formPembayaran" action="{{ route("pembayaran.search") }}" method="POST">
                        @csrf
                    {{-- <form id="formSearchPembayaran"> --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>No Invoice</label>
                                    <input type="text" name="invoice" class="form-control p-1" value="{{ $dataSearch ? $dataSearch['invoice'] : '' }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="inputEmail4">No Tanda Terima</label>
                                    <input type="text" name="tanda_terima" class="form-control p-1" value="{{ $dataSearch ? $dataSearch['tanda_terima'] : '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputEmail4">Customer</label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option selected value="">-- Pilih Customer --</option>
                                        @foreach ($members as $member)
                                            <option value="{{ $member->id }}" {{ $dataSearch && $dataSearch['customer'] == $member->id ? 'selected' : '' }}>{{ ucfirst($member->name) }} - {{ ucfirst($member->city) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="inputEmail4">Perusahaan</label>
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option selected value="">-- Pilih Perusahaan --</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}" {{ $dataSearch && $dataSearch['perusahaan'] == $company->id ? 'selected' : '' }}>{{ ucfirst($company->name) }}</option>
                                        @endforeach
                                    </select>
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
                            <div class="table-responsive">
                                <table id="table-pembayaran" class="table display table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>No Tanda Terima</th>
                                            <th>No Invoice</th>
                                            <th>Member - Kota</th>
                                            <th>Perusahaan</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice_tanda_terima as $inv)
                                        @php
                                            $querydetail = \App\Models\InvoiceTandaTerima::select('invoice_tanda_terima.*', 'invoice.no_nota as no_nota', 'invoice.id as invoid','invoice.dateorder as dateorder');
                                            $querydetail->join('invoice','invoice_tanda_terima.invoice_id','invoice.id');
                                            $querydetail->where('invoice_tanda_terima.no_tanda_terima', $inv->no_tanda_terima)->orderBy('invoice_date', 'asc');

                                            $detail_tanda_terima = $querydetail->get();
                                            $total = 0;
                                            foreach ($detail_tanda_terima as $invoice) {
                                                $total = $total + $invoice->nilai;
                                            }
                                        @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    {{ $inv->no_tanda_terima }}
                                                </td>
                                                <td>
                                                    @foreach ($detail_tanda_terima as $no_nota)
                                                        {{ $no_nota ->no_nota }} <br>
                                                    @endforeach
                                                </td>
                                                <td>{{ $inv->getMember->name }} - {{ $inv->getMember->city }}</td>
                                                <td>{{ $inv->getPerusahaan->name }}</td>
                                                <td>
                                                    {{ date('d M y', strtotime($inv->created_at)) }} <br>
                                                    {{ date('H:i', strtotime($inv->created_at)) }}
                                                </td>
                                                <td>
                                                    @if ($total-$inv->invoicePayment->sum('sudah_dibayar') <= 6000)
                                                        @can('pembayaran.input')
                                                            <a href="{{ route('pembayaran.detail', Crypt::encrypt($inv->id)) }}" class="btn btn-danger btn-sm" title="Input Pembayaran"><i class="fa fa-money-bill-alt"></i></a>
                                                        @endcan
                                                    @else
                                                        @can('pembayaran.input')
                                                            <a href="{{ route('pembayaran.detail', Crypt::encrypt($inv->id)) }}" class="btn btn-danger btn-sm" title="Input Pembayaran"><i class="fa fa-money-bill-alt"></i></a>
                                                        @endcan
                                                        @can('pembayaran.printtandaterima')
                                                            <a href="javascript:" class="btn btn-warning btn-sm" onclick="pilih_menu({{$inv->id}}, this.name)" name="menu_input_pembayaran" data-toggle="modal" data-target="#modal_pilihan" title="Print Tanda Terima"><i class="fa fa-print"></i></a>
                                                        @endcan
                                                        @can('pembayaran.inputdatapengiriman')
                                                            <a href="javascript:" onclick="input_pengiriman(this.name)" name="{{$inv->no_tanda_terima}}" id="input_pengiriman" role="button" data-toggle="modal" data-target="#modal_pengiriman" class="btn btn-success btn-sm" title="Input Data Pengiriman"><i class="fa fa-truck"></i></a>
                                                        @endcan
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

@endsection
@push('scripts')
<script>
    $(document).ready(function () {
        $('.select2').select2({allowClear: true});

        $('#table-pembayaran').dataTable({
            "pageLength": 30,
            "dom": '<"html5">lftip',
            "stateSave"  : true,
            "lengthMenu": [[30, 60, 100, -1], [30, 60, 100, "All"]]
        });
    })

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
                console.log(data);
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
            url: '{{ route("pembayaran.menu_data_list") }}',
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
        // console.log(idinv)
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
                console.log(response)
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
</script>
<script>
    function menu_input_pembayaran(idinv, menu){
        window.open('{{route("pembayaran.data_pembayaran",[null])}}/'+menu+'/'+idinv,'_blank');
        // console.log(idinv)
    }
    function menu_pengiriman(idinv, menu){
        window.open('{{route("tandaterima.pengiriman",[null])}}/'+menu+'/'+idinv,'_blank');
    }
</script>
<script>
    $(document).on('submit', '#formSearchPembayaran', function(e){
        e.preventDefault()
        let formSearchPembayaran = $('#formSearchPembayaran').serialize()
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url : "{{ route('pembayaran.search') }}",
            data: formSearchPembayaran,
            beforeSend: function () {
                Swal.showLoading();
            },
            success: function(response){
                // console.log(response)
                $('.result-search').html(response);
                Swal.hideLoading();
            },
            error: function(data){
                // $('#simpan').removeClass("disabled");
                Swal.hideLoading();
                Swal.fire('Ups','Ada kesalahan pada sistem','info');
                console.log(data);
            }
        });
    })
</script>
@endpush
