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
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group row"><label class="col-sm-4 col-form-label">Sales *</label>
                                        <div class="col-sm-8 error-text">
                                            <select name="sales" id="sales" class="form-control">
                                            
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group row"><label class="col-sm-4 col-form-label">Toko *</label>
                                        <div class="col-sm-8 error-text">
                                            <select name="toko" id="toko" class="form-control">
                                            
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group row"><label class="col-sm-4 col-form-label">PJP Day *</label>
                                        <div class="col-sm-8 error-text">
                                            <select name="hari" id="hari" class="select2 form-control">
                                                @foreach($hari as $key => $value)
                                                    <option value="{{ $value->id }}">{{ ucfirst($value->name) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group row"><label class="col-sm-4 col-form-label">Skala Kunjungan *</label>
                                        <div class="col-sm-8 error-text">
                                            <select name="hari" id="hari" class="select2 form-control">
                                                @foreach($skala as $key => $value)
                                                    <option value="{{ $value }}">{{ ucfirst($value) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Faktur *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" name="faktur" id="faktur" class="form-control">
                                </div>
                            </div>
                            <div class="tambah_faktur"></div>
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
        $('.select2').select2()
        $('#submitData').validate({
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
<script>
    $('#sales').select2()
    $('#toko').select2()
</script>
@endpush
