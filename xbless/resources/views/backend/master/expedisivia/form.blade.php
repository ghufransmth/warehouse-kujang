@extends('layouts.layout')

@section('title', 'Manajemen Expedisi Via ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($expedisivia) ? 'Edit' : 'Tambah'}} Expedisi Via</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('expedisivia.index')}}">Master Expedisi Via</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($expedisivia) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('expedisivia.index')}}">Batal</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($expedisivia)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($expedisivia)? $expedisivia->name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Telp *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="telp_no" name="telp_no" value="{{isset($expedisivia)? $expedisivia->telp_no : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat *</label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="address" name="address">{{isset($expedisivia)? $expedisivia->address : ''}}</textarea>

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
                                    <a class="btn btn-white btn-sm" href="{{route('expedisivia.index')}}">Batal</a>
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
            telp_no:{
                required: true,
            },
            address:{
                required: true,
            }
            },
            messages: {
            name: {
                required: "Nama tidak boleh kosong"
            },
            telp_no: {
                required: "No Telp tidak boleh kosong"
            },
            address: {
                required: "Alamat tidak boleh kosong"
            }
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
          url : "{{route('expedisivia.simpan')}}",
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
               window.location.replace('{{route("expedisivia.index")}}');
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
             Swal.fire('Ups','Ada kesalahan pada sistem','info');
            console.log(data);
          }
        });
    }
    });
</script>
@endpush
