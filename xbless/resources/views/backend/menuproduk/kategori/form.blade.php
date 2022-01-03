@extends('layouts.layout')
@section('title', 'Manajemen Kategori')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($kategori) ? 'Edit' : 'Tambah'}} Kategori</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('produk.index')}}">Produk</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('kategori.index')}}">Kategori</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($kategori) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <a class="btn btn-success btn-sm" href="{{route('kategori.index')}}"> <span class="fa fa-angle-left"></span>
            &nbsp; Kembali</a>
    </div>
</div>
<<<<<<< HEAD
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($kategori)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Kategori *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="cat_code" name="cat_code" value="{{isset($kategori)? $kategori->kode_kategori : ''}}">
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Kategori *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="cat_name" name="cat_name" value="{{isset($kategori)? $kategori->nama : ''}}">
                                </div>
=======
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox ">
                <div class="ibox-title">
                    @if(session('message'))
                    <div class="alert alert-{{session('message')['status']}}">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        {{ session('message')['desc'] }}
                    </div>
                    @endif

                </div>
                <div class="ibox-content">
                    <form id="submitData">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($kategori)? $enc_id : ''}}">
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Kode Kategori *</label>
                            <div class="col-sm-10 error-text">
                                <input type="text" class="form-control" id="cat_code" name="cat_code"
                                    value="{{isset($kategori)? $kategori->cat_code : ''}}">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Kategori *</label>
                            <div class="col-sm-10 error-text">
                                <input type="text" class="form-control" id="cat_name" name="cat_name"
                                    value="{{isset($kategori)? $kategori->cat_name : ''}}">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Sub Nama Kategori *</label>
                            <div class="col-sm-10 error-text">
                                <input type="text" class="form-control" id="cat_sub_name" name="cat_sub_name"
                                    value="{{isset($kategori)? $kategori->cat_sub_name : ''}}">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Gambar </label>
                            <div class="col-sm-10 error-text">
                                <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*">
                            </div>
                        </div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10 error-text">
                                @if($image ?? '')
                                <img src="{{ isset($kategori)? url($image) :'' }}" alt="" id="photo_preview"
                                    alt="photo_preview" style="max-height: 150px;">
                                @else
                                <img src="" alt="" id="photo_preview" alt="photo_preview" style="max-height: 150px;">
                                @endif
>>>>>>> 8cd37eabf8df7ced6ce6f70c121996619abf0071
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            <div class="col-sm-4 col-sm-offset-2">
                                <a class="btn btn-white btn-sm" href="{{route('kategori.index')}}">Batal</a>
                                <button class="btn btn-primary btn-sm" type="submit" id="simpan">Simpan</button>
                            </div>
<<<<<<< HEAD
                        </form>

                    </div>
=======
                        </div>
                    </form>

>>>>>>> 8cd37eabf8df7ced6ce6f70c121996619abf0071
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD
=======
</div>
>>>>>>> 8cd37eabf8df7ced6ce6f70c121996619abf0071

@endsection
@push('scripts')
<script>
    $(document).ready(function () {

        $('#submitData').validate({
            rules: {
                cat_code:{
                    required: true
                },
                cat_name:{
                    required: true
                },
<<<<<<< HEAD
=======
                cat_sub_name:{
                    required: true
                },
>>>>>>> 8cd37eabf8df7ced6ce6f70c121996619abf0071

            },
            messages: {
                cat_code: {
                    required: "Kode Kategori  tidak boleh kosong"
                },
                cat_name: {
                    required: "Nama Kategori  tidak boleh kosong"
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
                var image = document.querySelector('input[type=file]'),
                file = image.files[0]
                $.each(form, function(idx, val) {
                    dataFile.append(val.name, val.value)
                    dataFile.append("photo", file)
                })
            $.ajax({
                type: 'POST',
                url : "{{route('kategori.simpan')}}",
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
                        window.location.replace('{{route("kategori.index")}}');
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

    $(document).ready(function(){
        $('#photo').change(function(){
            var reader = new FileReader();

            reader.onload = (e) => {
                $('#photo_preview').attr('src', e.target.result)
            }

            reader.readAsDataURL(this.files[0])
        })
    });
</script>
@endpush
