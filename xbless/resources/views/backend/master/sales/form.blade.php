@extends('layouts.layout')

@section('title', 'Manajemen Sales ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($sales) ? 'Edit' : 'Tambah'}} Sales</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('sales.index')}}">Master Sales</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($sales) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('sales.index')}}">Batal</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($sales)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="code" name="code" value="{{isset($sales)? $sales->code : ''}}" {{isset($sales)? 'readonly' : ''}}>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Lengkap *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($sales)? $sales->name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Username *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="username" name="username" onblur="this.value=removeSpaces(this.value);" value="{{isset($sales)? $sales->username : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Kelamin</label>
                                <div class="col-sm-10 error-text">
                                    <div class="custom-controls-stacked">
                                        <label class="form-check form-check-inline">
                                           <input class="form-check-input" type="radio" name="jk" id="jk" value="L" {{isset($sales)? $sales->jk=='L' ? 'checked':'' : 'checked'}}>
                                           <span class="form-check-label">
                                             Laki-Laki
                                           </span>
                                         </label>

                                         <label class="form-check form-check-inline">
                                           <input class="form-check-input" type="radio" name="jk" id="jk" value="P" {{isset($sales)? $sales->jk=='P' ? 'checked':'' : ''}}>
                                           <span class="form-check-label">
                                             Perempuan
                                           </span>
                                         </label>
                                     </div>
                                </div>
                            </div>



                            <div class="form-group row"><label class="col-sm-2 col-form-label">Email *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="email" class="form-control" id="email" name="email" value="{{isset($sales)? $sales->email : ''}}">
                                </div>
                            </div>

                            <div class="form-group row" style="">
                                <label class="col-sm-2 col-form-label">Password {{isset($sales)? '' : '*'}}</label>

                                <div class="col-sm-10 error-text">
                                    <input type="password" class="form-control" name="password" id="password">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Phone *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{isset($sales)? $sales->phone : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">KTP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="ktp" name="ktp" value="{{isset($sales)? $sales->ktp : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="npwp" name="npwp" value="{{isset($sales)? $sales->npwp : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat *</label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="alamat" name="alamat">{{isset($sales)? $sales->address : ''}}</textarea>

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="no_rek" name="no_rek" value="{{isset($sales)? $sales->no_rek : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Bank</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{isset($sales)? $sales->bank_name : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Pemilik Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="holder_name" name="holder_name" value="{{isset($sales)? $sales->holder_name : ''}}">

                                </div>
                            </div>


                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('sales.index')}}">Batal</a>
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
            code:{
                required: true
            },
            name:{
                required: true
            },
            email:{
                required: true,
                email:true
            },
            username:{
                required: true,
                minlength: 3,
            },
            @if(!isset($sales))
            password: {
                required: true,
                minlength: 5
            },
            @endif
            alamat:{
                required: true,
            },
            phone:{
                required: true,
            },
            },
            messages: {
            code: {
                required: "Kode Sales tidak boleh kosong"
            },
            name: {
                required: "Nama Lengkap tidak boleh kosong"
            },
            username: {
                required: "Username tidak boleh kosong",
                minlength: "Username minimal 3 karakter",

            },
            email: {
                required: "Email tidak boleh kosong",
                email :"Hanya menerima email contoh demo@gmail.com",
            },
            password: {
                required: "Password wajib diisi.",
                minlength: "Password minimal 5 karakter"
            },
            alamat: {
                required: "Alamat tidak boleh kosong"
            },
            phone: {
                required: "No HP tidak boleh kosong"
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
          url : "{{route('sales.simpan')}}",
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
               window.location.replace('{{route("sales.index")}}');
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
    function removeSpaces(string) {
        return string.split(' ').join('');
    }
</script>
@endpush
