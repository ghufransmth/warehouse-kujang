@extends('layouts.layout')

@section('title', 'Detail User ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail User</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('staff.index')}}">Master User</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('staff.index')}}">Kembali</a>
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
                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Nama Lengkap </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$staff->fullname}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Username </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->username}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Jenis Kelamin</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->jk=='L'?'Laki-laki':'Perempuan'}}</p>

                                </div>
                            </div>



                            <div class="form-group row"><label class="col-sm-2 col-form-label">Email </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->email}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Phone</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->no_hp}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">KTP</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->ktp==null?'-':$staff->ktp}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->npwp==null?'-':$staff->npwp}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$staff->address}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Level Admin</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$akses}}</p>
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
                                    <a class="btn btn-white btn-sm" href="{{route('staff.index')}}">Kembali</a>
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
