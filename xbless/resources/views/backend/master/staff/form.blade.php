@extends('layouts.layout')

@section('title', 'Manajemen User ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($staff) ? 'Edit' : 'Tambah'}} User</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('staff.index')}}">Master User</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($staff) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('staff.index')}}">Batal</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($staff)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Lengkap *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($staff)? $staff->fullname : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Username *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="username" name="username" onblur="this.value=removeSpaces(this.value);" value="{{isset($staff)? $staff->username : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Kelamin</label>
                                <div class="col-sm-10 error-text">
                                    <div class="custom-controls-stacked">
                                        <label class="form-check form-check-inline">
                                           <input class="form-check-input" type="radio" name="jk" id="jk" value="L" {{isset($staff)? $staff->jk=='L' ? 'checked':'' : 'checked'}}>
                                           <span class="form-check-label">
                                             Laki-Laki
                                           </span>
                                         </label>

                                         <label class="form-check form-check-inline">
                                           <input class="form-check-input" type="radio" name="jk" id="jk" value="P" {{isset($staff)? $staff->jk=='P' ? 'checked':'' : ''}}>
                                           <span class="form-check-label">
                                             Perempuan
                                           </span>
                                         </label>
                                     </div>
                                </div>
                            </div>



                            <div class="form-group row"><label class="col-sm-2 col-form-label">Email *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="email" class="form-control" id="email" name="email" value="{{isset($staff)? $staff->email : ''}}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Password {{isset($staff)? '' : '*'}}</label>

                                <div class="col-sm-10 error-text">
                                    <input type="password" class="form-control" name="password" id="password">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Phone</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{isset($staff)? $staff->no_hp : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">KTP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="ktp" name="ktp" value="{{isset($staff)? $staff->ktp : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="npwp" name="npwp" value="{{isset($staff)? $staff->npwp : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat *</label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="alamat" name="alamat">{{isset($staff)? $staff->address : ''}}</textarea>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Pilih Level Admin *</label>
                                <div class="col-sm-10 error-text">
                                    <select class="form-control" id="level" name="level">
                                        <option value="">Pilih salah satu</option>
                                        @foreach($roles as $key => $row)
                                        <option value="{{$row->id}}"{{ $roleselected == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
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
                                    <a class="btn btn-white btn-sm" href="{{route('staff.index')}}">Batal</a>
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
<script language="javascript" type="text/javascript">
    $(document).ready(function () {

        $('#submitData').validate({
            rules: {
            name:{
                required: true
            },
            email:{
                required: true,
                email:true
            },
            username:{
                required: true,
                minlength: 3
            },
            @if(!isset($staff))
            password: {
                required: true,
                minlength: 5
            },
            @endif
            alamat:{
                required: true,
            },
            level:{
                required: true,
            },
            },
            messages: {
            name: {
                required: "Nama Lengkap tidak boleh kosong"
            },
            username: {
                required: "Username tidak boleh kosong",
                minlength: "Username minimal 3 karakter"
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
            level: {
                required: "Pilih salah satu"
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
          url : "{{route('staff.simpan')}}",
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
               window.location.replace('{{route("staff.index")}}');
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
