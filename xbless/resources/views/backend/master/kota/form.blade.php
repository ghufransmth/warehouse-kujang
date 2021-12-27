@extends('layouts.layout')

@section('title', 'Manajemen Kota ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($kota) ? 'Edit' : 'Tambah'}} Kota</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('kota.index')}}">Master Kota</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($kota) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('kota.index')}}">Batal</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($kota)? $enc_id : ''}}">

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Negara *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="country_id" class="form-control select2" id="country_id">
                                        <option value="">Pilih Negara</option>
                                        @foreach($negara as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectednegara == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Provinsi *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="provinsi_id" class="form-control select2" id="provinsi_id">
                                        @if(isset($kota))<option value="{{$selectedprovinsi}}">{{$provinsi}}</option>@endif
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Kota *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($kota)? $kota->name : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Area *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="area_code" name="area_code" value="{{isset($kota)? $kota->area_code : ''}}">
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Kota <br/><span style="color:red;font-size:10px;">Minimal & Maksimal 3 Karakter</span></span></label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="abbreviation" name="abbreviation" value="{{isset($kota)? $kota->abbreviation : ''}}">
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('kota.index')}}">Batal</a>
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
            country_id:{
                required: true,
            },
            provinsi_id:{
                required: true,
            },
            area_code:{
                required: true,
            },
            // abbreviation:{
            //     required: true,
            //     min:3,
            //     max:3
            // },
            },
            messages: {
            name: {
                required: "Nama tidak boleh kosong"
            },
            country_id: {
                required: "Silahkan pilih salah satu negara"
            },
            provinsi_id: {
                required: "Silahkan pilih salah satu provinsi"
            },
            area_code: {
                required: "Kode Kota tidak boleh kosong"
            },
            // abbreviation: {
            //     required: "Singkatan Kota tidak boleh kosong",
            //     min:"minimal 3 karakter",
            //     max:"minimal 3 karakter",
            // },
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
        $(".select2").select2({allowClear: true});
        @if(isset($kota))
            $.ajax({
                    url: '{{route("kota.provinsi",[null])}}/' + '{{$selectednegara}}',
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        $.each(data, function(key, value) {
                            var selected="";
                            if(value['id']=='{{$selectedprovinsi}}'){
                                selected ='selected';
                            }else{
                                selected ='';
                            }
                            $('#provinsi_id').append('<option value="'+ value['id'] +'" '+selected+'>'+ value['name'] +'</option>');
                        });
                    }
                });
        @endif
        $('select[name="country_id"]').on('change', function() {
            var country_id = $(this).val();

            if(country_id) {
                $.ajax({
                    url: '{{route("kota.provinsi",[null])}}/' + country_id,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {

                        $('#provinsi_id').empty();
                        $('#provinsi_id').append('<option value="">Pilih Provinsi</option>');
                        $.each(data, function(key, value) {
                            $('#provinsi_id').append('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                        });
                    }
                });
            }else{
                $('#provinsi_id').empty();
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
          url : "{{route('kota.simpan')}}",
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
               window.location.replace('{{route("kota.index")}}');
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
