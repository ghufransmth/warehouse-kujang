@extends('layouts.layout')

@section('title', 'Detail Member ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Member</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('member.index')}}">Master Member</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('member.index')}}">Kembali</a>
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
                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Kode Unik </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$member->uniq_code==null?'-':$member->uniq_code}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Nama Lengkap </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$member->name}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Username </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->username}}</p>
                                </div>
                            </div>


                            {{--
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Email </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->email}}</p>
                                </div>
                            </div> --}}

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Phone</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->phone}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">KTP</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->ktp==null?'-':$member->ktp}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->npwp==null?'-':$member->npwp}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat NPWP</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->address}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Alamat Toko</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->address_toko}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kota</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->city}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Provinsi</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->prov}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Tipe Harga <br/><span style="color:red;font-size:10px">Penambahan harga dari harga jual</span></label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->gettipeharga?$member->gettipeharga->name:'-'}} %</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">No Rekening</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->no_rek}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Bank</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->bank_name}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Pemilik Rekening </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$member->holder_name}}</p>
                                </div>
                            </div>


                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('member.index')}}">Kembali</a>
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
