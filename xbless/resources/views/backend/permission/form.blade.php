@extends('layouts.layout')

@section('title', 'Manajemen Modul ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($permission) ? 'Edit' : 'Tambah'}} Modul</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('permission.index')}}">Modul</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($permission) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        
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
                        <form id="submitData" method="POST" action="{{isset($permission)? route('permission.simpan',$enc_id) : route('permission.simpan')}}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                           
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Modul *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($permission)? $permission->name : ''}}"> 
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Slug *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="slug" name="slug" value="{{isset($permission)? $permission->slug : ''}}"> 
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Urutan *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="urutan" name="urutan" value="{{isset($permission)? $permission->nested : ''}}"> 
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('permission.index')}}">Batal</a>
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
            focusInvalid: false,
            rules: {
            name:{
                required: true
            },
            slug:{
                required: true
            },
            urutan:{
                required: true
            }
            },
            messages: {
            name: {
                required: "Nama Modul tidak boleh kosong"
            },
            slug: {
                required: "Nama slug / route tidak boleh kosong"
            },
            urutan: {
                required: "Urutan tidak boleh kosong"
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
        });
        
    });
</script>
@endpush