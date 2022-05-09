@extends('layouts.layout')
@section('title', 'Manajemen Toko')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($toko) ? 'Edit' : 'Tambah'}} Toko</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('toko.index')}}">Toko</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($toko) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('toko.index')}}">Kembali</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($toko)? $enc_id : ''}}">

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Kode Toko *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="kode" name="kode" value="{{isset($toko)? $toko->code : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($toko)? $toko->name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nik *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="nik" name="nik" value="{{isset($toko)? $toko->nik : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="alamat" name="alamat" value="{{isset($toko)? $toko->alamat : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Telp *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="telp" name="telp" value="{{isset($toko)? $toko->telp : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Distrik *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="distrik" id="distrik" class="form-control select2">
                                        <option value="">Pilih Distrik</option>
                                        @foreach($distrik as $key => $value)
                                            <option value="{{ $value->id }}"{{ $selecteddistrik == $value->id ? 'selected=""' : '' }}>{{ ucfirst($value->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Tipe Channel *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="tipe_chanel" id="tipe_chanel" class="form-control select2">
                                        <option value="">Pilih Tipe Channel</option>
                                        @foreach($tipe_chanel as $key => $value)
                                            <option value="{{ $value->id }}"{{ $selectedtipechanel == $value->id ? 'selected=""' : '' }}>{{ ucfirst($value->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Toko *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="jenis_toko" id="jenis_toko" class="form-control select2">
                                        <option value="">Pilih Jenis Toko</option>
                                        @foreach($jenis_toko as $key => $value)
                                            <option value="{{ $value->id }}"{{ $selectedjenistoko == $value->id ? 'selected=""' : '' }}>{{ ucfirst($value->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Bayar *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="payment" id="payment" class="form-control select2">
                                        <option value="">Pilih Jenis Pembayaran</option>
                                        @foreach($payments as $key => $value)
                                            <option value="{{ $value->id }}"{{ $selectedpayment == $value->id ? 'selected=""' : '' }}>{{ ucfirst($value->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kategori *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="kategori" id="kategori" class="form-control select2">
                                        <option value="">Pilih Kategori Toko</option>
                                        @foreach($kategori_toko as $key => $value)
                                            <option value="{{ $value->id }}"{{ $selectedkategoritoko == $value->id ? 'selected=""' : '' }}>{{ ucfirst($value->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('toko.index')}}">Batal</a>
                                    <button class="btn btn-primary btn-sm" type="submit" id="simpan">Simpan</button>
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
<script>
    $(document).ready(function () {
        $('#submitData').validate({
            rules: {
                name:{
                    required: true
                },
            },
            messages: {
                name: {
                    required: "Nama Brand tidak boleh kosong"
                },
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');

            element.closest('.error-text').append(error);

            },
            highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
            Swal.showLoading();
            SimpanData();
            }
        });
        function SimpanData(){
            $('#simpan').addClass("disabled");
                var form = $('#submitData').serializeArray()
                var dataFile = new FormData()
                $.each(form, function(idx, val) {
                    dataFile.append(val.name, val.value)
                })
            $.ajax({
                type: 'POST',
                url : "{{route('toko.simpan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function(data){
                    if (data.success) {
                        Swal.fire('Yes',data.message,'info');
                        window.location.replace('{{route("toko.index")}}');
                    } else {
                        Swal.fire('Ups',data.message,'info');
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
        }
    });
</script>
@endpush
