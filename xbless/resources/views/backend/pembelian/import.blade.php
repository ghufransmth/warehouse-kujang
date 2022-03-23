@extends('layouts.layout')
@section('title', 'Manajemen Pembelian ')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Upload Pembelian</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('pembelian.index')}}">Pembelian</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Upload</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        {{-- <a class="btn btn-danger btn-sm" href="{{route('pembelian.index')}}">Batal</a> --}}
        <a href="{{ asset('/assets/excel/templateDataPembelian.xlsx') }}" class="btn btn-primary" style="margin-left: 10px"><i class="fa fa-file-excel-o"></i>  Download Template</a>
    </div>
</div>
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox ">
                    <div class="ibox-title">
                        @if(session('status'))
                            <div class="alert alert-{{session('status')}}">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            {{ session('desc') }}
                            </div>
                        @endif
                        <form action="{{ route('pembelian_import.uploadimport') }}" method="POST" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="custom-file mb-3">
                                <input type="file" class="custom-file-input" id="customFile" name="file_pembelian">
                                <label class="custom-file-label" for="customFile">Choose file</label>
                            </div>
                            <div class="d-flex flex-row-reverse">
                                <input type="submit" class="btn btn-primary" style="display:none" id="upload" value="Upload">
                            </div>
                        </form>

                    </div>
                    <div class="ibox-content">
                        <div class="alert alert-danger" id="showAlert" style="display: none">
                            MEMBER INI BELUM MELAKUKAN PEMBAYARAN PADA INVOICE
                          </div>

                    @if(isset($data) && count($data) > 0)
                    <div class="col">
                        <form action="{{ route('pembelian_import.importsimpan') }}" method="POST">
                            {{ csrf_field() }}
                            <label for="">Tanggal Transaksi</label>
                            <input type="text" name="tgl_transaksi" class="formatTgl transaksi" id="" autocomplete="off" value="{{ date('d-m-Y') }}">
                            &nbsp;
                            <select name="supplier" id="supplier" class="select2 col-sm-2" required>
                                <option disabled selected value="">Pilih Supplier</option>
                                @foreach($supplier as $key => $value)
                                    <option value="{{ $value->id }}" {{ $selectedsupplier==$value->id?'selected':'' }}>{{ $value->nama }}</option>
                                @endforeach
                            </select>

                            &nbsp;
                            <select name="gudang" id="gudang" class="select2 col-sm-2" required>
                                <option disabled selected value="">Pilih Gudang</option>
                                @foreach($gudang as $key => $value)
                                    <option value="{{ $value->id }}" {{ $selectedgudang==$value->id?'selected':'' }}>{{ $value->name }}</option>
                                @endforeach
                            </select>

                            <br>
                            <div class="text-right">
                                <button type="submit" class="btn btn-primary" ><i class="fa fa-save mr-1"></i>Simpan</button>
                                <a href="{{ route('pembelian_import.importbatal') }}" class="btn btn-danger" style="margin-right: 10px"><i class="fa fa-close"></i> Batal</a>
                            </div>
                            </form>
                        </div>
                    <br>
                        <div class="table-responsive">
                            <table id="table1" class="table display table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nomor Faktur</th>
                                        <th>Kode Product</th>
                                        <th>Satuan</th>
                                        <th>QTY</th>
                                        <th>Harga Beli</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $key => $value)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $value->no_faktur }}</td>
                                            <td>{{ $value->kode_product }}</td>
                                            <td>{{ $value->satuan_id }}</td>
                                            <td>{{ $value->qty }}</td>
                                            <td>{{ $value->harga_product }}</td>
                                            <td>{!! $value->aksi !!}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>

                                </tfoot>
                            </table>

                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<script>
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        $('#upload').show();
    });

    function hapus(id){
        // console.log(id);
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Menghapus data ini",
            icon: 'danger',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url : "{{route('pembelian_import.deleteimport')}}",
                    headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                    data:{
                        id_detail : id
                    },

                    dataType: "json",
                    beforeSend: function () {
                    Swal.showLoading();
                    },
                    success: function(data){
                        // console.log(data);
                        // return false;
                        if (data.success) {
                            Swal.fire('Yes',data.message,'info');
                            window.location.replace('{{route("pembelian_import.import")}}');


                        } else {
                            Swal.fire('Ups',data.message,'info');
                        }
                    },
                    complete: function () {
                        Swal.hideLoading();
                        $('#simpan').removeClass("disabled");
                    },
                    error: function(data){
                        $('#simpan').removeClass("disabled");
                        Swal.hideLoading();
                        Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
                    }
                });
            }
        })

    }

    $('.transaksi').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
@endpush
