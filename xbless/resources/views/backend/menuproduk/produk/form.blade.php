@extends('layouts.layout')
@section('title', 'Manajemen Produk')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($produk) ? 'Edit' : 'Tambah'}} Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('produk.index')}}">Produk</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($produk) ? 'Edit' : 'Tambah'}}</strong>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($produk)? $enc_id : ''}}">

                            <div class="form-group row">

                                <label class="col-sm-2 col-form-label">Kode Produk *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="kode_produk" name="kode_produk" value="{{isset($produk)? $produk->kode_product : ''}}">
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Produk *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{isset($produk)? $produk->nama : ''}}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Kategori *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="kategori_id" class="form-control select2" id="kategori_id">
                                    <option value="">Pilih Kategori</option>
                                        @foreach($kategori as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedkategori == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->kode_kategori)}} | {{ $row->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Satuan Produk *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="satuan_id" class="form-control select2" id="satuan_id">
                                        <option value="">Pilih Satuan</option>
                                        @foreach($satuan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedsatuan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->nama )}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- <label class="col-sm-2 col-form-label">Isi Produk (Pcs) *</label>
                                <div class="col-sm-3 error-text">
                                    <input type="text" class="form-control" id="satuan_value" name="satuan_value" min="1"  value="{{isset($produk)? $produk->satuan_value : ''}}">
                                </div> --}}
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Harga Beli *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="harga_beli" name="harga_beli" value="{{isset($produk)? number_format($produk->harga_beli,0,',','.') : ''}}">
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Harga Jual *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="harga_jual" name="harga_jual" value="{{isset($produk)? number_format($produk->harga_jual,0,',','.') : ''}}">
                                </div>
                            </div>


                            <div class="form-group row"><label class="col-sm-2 col-form-label">Status *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="status" class="form-control" id="status">
                                        @foreach($status as $key => $row)
                                        <option value="{{$key}}"{{ $selectedstatus == $key ? 'selected=""' : '' }}>{{ucfirst($row)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('produk.index')}}">Batal</a>
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
<script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
<script>
    $(document).ready(function () {
        $('#liner_id').on('change', function() {
            var liner_id = $(this).val();
            if(liner_id=='Y') {
                $('#cekprodukshadow').show();
            }else{
                $('#cekprodukshadow').hide();
            }
        });
        $('#satuan_id').on('change', function() {
            var satuan_id = $(this).find("option:selected").text();
            if(satuan_id.toLowerCase()=='pcs') {
                $('#satuan_value').val('1');
                $('#satuan_value').attr('readonly', 'true');
            }else{
                $('#satuan_value').val('');
                $('#satuan_value').attr('readonly', false);
            }
        });
        $('#submitData').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });
        $('#cover').change(function(){
            var fp = $("#cover");
            var lg = fp[0].files.length; // get length
            var items = fp[0].files;
            var fileSize = 0;

            if (lg > 0) {
                for (var i = 0; i < lg; i++) {
                    fileSize = fileSize+items[i].size; // get file size
                }
                if(fileSize > 2097152) {
                    Swal.fire('Yes','Ukuran File Tidak Boleh Lebih Dari 2 MB','info');
                    $('#cover').val('');
                }
            }
        });
        $('#product_img').change(function(){
            var fp = $("#product_img");
            var lg = fp[0].files.length; // get length
            var items = fp[0].files;
            var fileSize = 0;

            if (lg > 0) {
                for (var i = 0; i < lg; i++) {
                    fileSize = fileSize+items[i].size; // get file size
                }
                if(fileSize > 2097152) {
                    Swal.fire('Yes','Ukuran File Tidak Boleh Lebih Dari 2 MB','info');
                    $('#product_img').val('');
                }
            }
        });
        $(".select2").select2({allowClear: true});

        $('#submitData').validate({
            rules: {
                name:{
                    required: true
                },
                satuan_id:{
                    required: true
                },
                kategori_id:{
                    required: true
                },
                harga_beli:{
                    required: true
                },
                harga_jual:{
                    required: true
                },

            },
            messages: {
                name:{
                    required: "Nama produk wajib di isi."
                },
                satuan_id:{
                    required: "Silahkan pilih salah satu satuan."
                },
                kategori_id:{
                    required: "Silahkan pilih salah satu kategori."
                },
                harga_beli:{
                    required: "Harga beli product tidak boleh kosong."
                },
                harga_jual:{
                    required: "Harga jual produk tidak boleh kosong."
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
        $("#harga_beli" ).keyup(function() {

        var value = Number(this.value.replace(/\./g, ""));
        var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;

        value = formatRupiah(this.value, '');
        var nilai = this.value.replace(/\./g, "");
        if(!numberRegex.test(nilai)){
             $('#harga_beli').val(0);
             Swal.fire('Ups','Harga Beli Produk harus angka','info');
             return false;

        }

        if(value.charAt(0) > 0){
            $('#harga_beli').val(getprice(nilai));
        }else{
            if(value.charAt(1)=='0'){
                $('#harga_beli').val(0);
            }else{
                $('#harga_beli').val(getprice(value));
            }
        }

    });
    $("#harga_jual" ).keyup(function() {
        var value = Number(this.value.replace(/\./g, ""));
        var numberRegex = /^[+-]?\d+(\.\d+)?([eE][+-]?\d+)?$/;
        value = formatRupiah(this.value, '');
        var nilai = this.value.replace(/\./g, "");
         if(!numberRegex.test(nilai)){
             $('#harga_jual').val(0);
             Swal.fire('Ups','Harga Jual Produk harus angka','info');
             return false;

        }

        if(value.charAt(0) > 0){
            $('#harga_jual').val(getprice(nilai));
        }else{
            if(value.charAt(1)=='0'){
                $('#harga_jual').val(0);
            }else{
                $('#harga_jual').val(getprice(value));
            }
        }

    });

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
                url : "{{route('produk.simpan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function(data){
                    console.log(data)
                    if (data.success) {
                        Swal.fire('Yes',data.message,'info');
                        window.location.replace('{{route("produk.index")}}');
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
                    Swal.fire('Ups','Ada kesalahan pada sistem, Silahkan Cek Kembali form yang diisi','info');
                }
            });
        }


    function delete_qrcode(no, qrcode){
        window.addEventListener("click", function(event) {
            event.preventDefault();
        });
        let idpro = $('#enc_id').val()
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Menghapus data ini",
            icon: 'danger',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url : "{{route('produk.delete_qrcode')}}",
                    headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                    dataType: "json",
                    data:{
                        'qrcode': qrcode,
                        'enc_id': idpro
                    },
                    success: function(response){
                        if(response.success){
                            Swal.fire('Yes',`${response.message}`,'info');
                            $('#qr'+no).val('')
                            $('#isi'+no).val('')
                        }else{
                            Swal.fire('Ups',`${response.message}`,'info');
                        }
                    },
                });
            }
        })
    }



    function getprice(nStr){
        nStr+='';
        x=nStr.split('.');
        x1=x[0];
        x2=x.length>1?'.'+x[1]:'';
        var rgx=/(\d+)(\d{3})/;
        while(rgx.test(x1)){
            x1=x1.replace(rgx,'$1'+'.'+'$2');
        }
        return x1+x2;
    }
    function formatRupiah(angka, prefix){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split       = number_string.split(','),
        sisa        = split[0].length % 3,
        nilai_asset        = split[0].substr(0, sisa),
        ribuan      = split[0].substr(sisa).match(/\d{3}/gi);

        // tambahkan titik jika yang di input sudah menjadi angka ribuan
        if(ribuan){
          separator = sisa ? '.' : '';
          nilai_asset += separator + ribuan.join('.');
        }
        nilai_asset = split[1] != undefined ? nilai_asset + ',' + split[1] : nilai_asset;
        return prefix == undefined ? nilai_asset : (nilai_asset ? '' + nilai_asset : '');
    }
</script>
