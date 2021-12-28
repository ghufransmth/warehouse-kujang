@extends('layouts.layout')
@section('title', 'Manajemen Satuan')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($satuan) ? 'Edit' : 'Tambah'}} Satuan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('produk.index')}}">Produk</a>
              </li>
            <li class="breadcrumb-item">
                <a href="{{route('satuan.index')}}">Satuan</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($satuan) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('satuan.index')}}">Kembali</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($satuan)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Satuan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($satuan)? $satuan->name : ''}}"> 
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Satuan *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="jenis_satuan" class="form-control" id="jenis_satuan">
                                        <option value="">Jenis Satuan</option>
                                        @foreach($jenis as $key => $row)
                                        <option value="{{$key}}"{{ $selectedjenis == $key ? 'selected=""' : '' }}>{{ucfirst($row)}}</option>
                                        @endforeach
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('satuan.index')}}">Batal</a>
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
                jenis_satuan:{
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Nama Satuan tidak boleh kosong"
                },
                jenis_satuan: {
                    required: "Jenis Satuan wajib dipilih salah satu"
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
                url : "{{route('satuan.simpan')}}",
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
                        window.location.replace('{{route("satuan.index")}}');
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