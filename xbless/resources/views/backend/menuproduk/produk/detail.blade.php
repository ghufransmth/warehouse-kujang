@extends('layouts.layout')

@section('title', 'Detail Produk ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('produk.index')}}">Produk</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('produk.index')}}">Kembali</a>
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
                            <div class="form-group row"><label class="col-sm-2 col-xs-6 col-form-label">Kode Produk </label>
                                <div class="col-sm-10 error-text col-xs-6">
                                    <p class="col-form-label"> {{$produk->product_code}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Produk </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->product_name}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Apakah Produk Liner?  </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->is_liner=='Y'?'Ya':'Tidak'}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Produk Bayangan</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->is_liner=='Y'?$produk->product_code_shadow:'-'}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Gambar Cover</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"><img src="{!! $produk->product_cover != null ? url($produk->product_cover) : '' !!}" class="img-fluid" width="100px"/> </p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Brand</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->getBrand?$produk->getBrand->name:'-'}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Engine Model</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->getEngine?$produk->getEngine->name:'-'}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kategori</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->getkategori?$produk->getkategori->cat_name:'-'}}</p>
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Satuan </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->getsatuan?$produk->getsatuan->name:'-'}}  ( {{$produk->satuan_value}} Pcs )</p>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Harga </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{ number_format($produk->normal_price,0,',','.') }}</p>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Harga Export </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{ number_format($produk->export_price,0,',','.') }}</p>

                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Deskripsi</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label">{{$produk->product_desc==null?'-':$produk->product_desc}}</p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Gambar Detail</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label">
                                        @foreach($produk->getImageDetail as $key=>$value)
                                        <img src="{!! url($value->product_img) !!}" class="img-fluid" width="100px"/>
                                        @endforeach
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">QR Code</label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label">
                                        <div class="row">
                                        @foreach($produk->getQrCode as $key=>$value)
                                        <div class="col-md-3">
                                        {!! QrCode::size(100)->generate($value->barcode) !!}<br/>
                                        {{$value->barcode}}  ({{$value->isi}} Pcs)
                                        </div>
                                        @endforeach
                                    </div>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group row"><label class="col-sm-2 col-form-label">Status  </label>
                                <div class="col-sm-10 error-text">
                                    <p class="col-form-label"> {{$produk->product_status=='1'?'Aktif':'Tidak Aktif'}}</p>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('produk.index')}}">Kembali</a>
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
