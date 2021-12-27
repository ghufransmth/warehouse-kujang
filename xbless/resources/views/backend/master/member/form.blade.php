@extends('layouts.layout')

@section('title', 'Manajemen Member ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($member) ? 'Edit' : 'Tambah'}} Member</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('member.index')}}">Master Member</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($member) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('member.index')}}">Batal</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($member)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="code" name="code" placeholder="generate by sistem" value="{{isset($member)? $member->uniq_code : ''}}" readonly }}>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Lengkap *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($member)? $member->name : ''}}">
                                </div>
                            </div>



                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat Toko *</label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="alamat_toko" name="alamat_toko">{{isset($member)? $member->address_toko : ''}}</textarea>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat NPWP </label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="alamat" name="alamat">{{isset($member)? $member->address : ''}}</textarea>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kota * </label>
                                <div class="col-sm-10 error-text">
                                    <select class="form-control select2" id="city" name="city">
                                        <option value="">Pilih salah satu kota</option>
                                        @foreach($city as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedcity == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Phone </label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{isset($member)? $member->phone : ''}}" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Jenis Harga * <br/>
                                    <span style="color:red;font-size:10px">Penambahan harga dari harga jual</span>
                                </label>

                                <div class="col-sm-10 error-text">
                                    <select class="form-control select2" id="type_price" name="type_price">
                                        <option value="">Pilih jenis harga</option>
                                        @foreach($tipeharga as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedtipeharga == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}} %</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Username </label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="username" name="username" onblur="this.value=removeSpaces(this.value);" value="{{isset($member)? $member->username : ''}}">
                                </div>
                            </div>

                            {{--
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Email </label>
                                <div class="col-sm-10 error-text">
                                    <input type="email" class="form-control" id="email" name="email" value="{{isset($member)? $member->email : ''}}">
                                </div>
                            </div> --}}

                            <div class="form-group row">
                                <!-- <label class="col-sm-2 col-form-label">Password {{isset($member)? '' : '*'}}</label> -->
                                <label class="col-sm-2 col-form-label">Password </label>

                                <div class="col-sm-10 error-text">
                                    <input type="password" class="form-control" name="password" id="password">
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">KTP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="ktp" name="ktp" value="{{isset($member)? $member->ktp : ''}}" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="npwp" name="npwp" value="{{isset($member)? $member->npwp : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="no_rek" name="no_rek" value="{{isset($member)? $member->no_rek : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Bank</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="{{isset($member)? $member->bank_name : ''}}">

                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Pemilik Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="holder_name" name="holder_name" value="{{isset($member)? $member->holder_name : ''}}">

                                </div>
                            </div>


                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('member.index')}}">Batal</a>
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
        $(".select2").select2();
        $('#submitData').validate({
            rules: {

            name:{
                required: true
            },
            // email:{
            //     required: true,
            //     email:true
            // },
            // username:{
            //     required: true,
            //     minlength: 3
            // },
            @if(!isset($member))
            // password: {
            //     required: true,
            //     minlength: 5
            // },
            @endif
            // alamat:{
            //     required: true,
            // },
            alamat_toko:{
                required: true,
            },
            // phone:{
            //     required: true,
            // },
            city:{
                required: true,
            },
            type_price:{
                required: true,
            },

            },
            messages: {

            name: {
                required: "Nama Lengkap tidak boleh kosong"
            },
            // username: {
            //     required: "Username tidak boleh kosong",
            //     minlength: "Username minimal 3 karakter"
            // },
            // email: {
            //     required: "Email tidak boleh kosong",
            //     email :"Hanya menerima email contoh demo@gmail.com",
            // },
            // password: {
            //     required: "Password wajib diisi.",
            //     minlength: "Password minimal 5 karakter"
            // },
            // alamat: {
            //     required: "Alamat tidak boleh kosong"
            // },
            alamat_toko: {
                required: "Alamat Toko tidak boleh kosong"
            },
            // phone: {
            //     required: "No HP tidak boleh kosong"
            // },
            city: {
                required: "Kota wajib dipilih salah satu"
            },
            type_price: {
                required: "Jenis Harga wajib dipilih salah satu"
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
          url : "{{route('member.simpan')}}",
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
               window.location.replace('{{route("member.index")}}');
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
