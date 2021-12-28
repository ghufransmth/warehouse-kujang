@extends('layouts.layout')

@section('title', 'Detail Perusahaan ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Perusahaan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('perusahaan.index')}}">Master Perusahaan</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
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
                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Kode </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$perusahaan->kode}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Nama </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$perusahaan->name}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$perusahaan->address}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kota </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$perusahaan->city}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Telephone</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$perusahaan->telephone}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Bank Perusahaan</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$perusahaan->bank_name==null?'-':$perusahaan->bank_name}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$perusahaan->rek_no==null?'-':$perusahaan->rek_no}}</p>
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Status </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {!! $status !!}</p>

                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('perusahaan.index')}}">Kembali</a>
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

</script>
@endpush
