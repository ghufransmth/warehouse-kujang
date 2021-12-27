@extends('layouts.layout')
@section('title', 'Manajemen Perusahaan')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($perusahaan) ? 'Edit' : 'Tambah'}} Perusahaan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('perusahaan.index')}}">Master Perusahaan</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($perusahaan) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('perusahaan.index')}}">Kembali</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($perusahaan)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Perusahaan (untuk Kode PO) *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="kode" name="kode" value="{{isset($perusahaan)? $perusahaan->kode : ''}}" {{isset($perusahaan)? 'readonly':''}}>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($perusahaan)? $perusahaan->name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="address" name="address" value="{{isset($perusahaan)? $perusahaan->address : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kota Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="city" name="city" value="{{isset($perusahaan)? $perusahaan->city : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Telephone Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="telephone" name="telephone" value="{{isset($perusahaan)? $perusahaan->telephone : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Bank Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{isset($perusahaan)? $perusahaan->bank_name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Rekening Perusahaan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="rek_no" name="rek_no" value="{{isset($perusahaan)? $perusahaan->rek_no : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Status *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="status" class="form-control" id="status">
                                        @foreach($status as $key => $row)
                                        <option value="{{$key}}"{{ $selectedstatus == $key ? 'selected=""' : '' }}>{{ucfirst($row)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('perusahaan.index')}}">Batal</a>
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
                kode:{
                    required: true
                },
                name:{
                    required: true
                },
                address:{
                    required: true
                },
                city:{
                    required: true
                },
                telephone:{
                    required: true
                },
                bank_name:{
                    required: true
                },
                rek_no:{
                    required: true
                },
                status:{
                    required: true
                },
            },
            messages: {
                name: {
                    required: "Nama Perusahaan tidak boleh kosong"
                },
                kode: {
                    required: "Kode Perusahaan tidak boleh kosong"
                },
                address: {
                    required: "Address Perusahaan tidak boleh kosong"
                },
                city: {
                    required: "Kota Perusahaan tidak boleh kosong"
                },
                telephone: {
                    required: "Telephone Perusahaan tidak boleh kosong"
                },
                rek_no: {
                    required: "Rekening Perusahaan tidak boleh kosong"
                },
                bank_name: {
                    required: "Bank Perusahaan tidak boleh kosong"
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
                url : "{{route('perusahaan.simpan')}}",
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
                        window.location.replace('{{route("perusahaan.index")}}');
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
