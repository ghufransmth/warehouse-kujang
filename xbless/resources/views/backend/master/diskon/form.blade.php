@extends('layouts.layout')
@section('title', 'Manajemen Diskon')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($diskon) ? 'Edit' : 'Tambah'}} Diskon</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('distrik.index')}}">Master Diskon</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($diskon) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('diskon.index')}}">Kembali</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($diskon)? $enc_id : ''}}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Diskon Dari *</label>
                                <div class="col-sm-10 error-text">
                                    <select name="parent" class="form-control select2" id="parent">
                                        <option value="">Select Diskon ...</option>
                                            @foreach($parent as $key => $value)
                                                <option value="{{ $key }}" name="{{ $value }}" {{ $selectedParent == $key? 'selected=""' : ''}}>{{ $value }}</option>
                                            @endforeach
                                    </select> 
                                </div>
                            </div>
                            <div id="diskon_detail">
                                <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Diskon *</label>
                                    <div class="col-sm-10 error-text">
                                        <input type="text" class="form-control" id="name" name="name" value="{{isset($data)? $data->name : ''}}"> 
                                    </div>
                                </div>
                                <div id="diskon_distributor">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Minimal Pembelian</label>
                                                <div class="col-sm-8">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">Rp.</span><input type="text" class="form-control" id="min_beli" name="min_beli" value="{{isset($data)? $data->min_beli : ''}}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row">
                                                <label class="col-sm-4 col-form-label">Maximal Pembelian</label>
                                                <div class="col-sm-8">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">Rp.</span><input type="text" class="form-control" id="max_beli" name="max_beli" value="{{isset($data)? $data->max_beli : ''}}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="diskon_principal" class="hide">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group row"><label class="col-sm-4 col-form-label">Jenis Diskon *</label>
                                                <div class="col-sm-8 error-text">
                                                    <select name="jenis_diskon" class="form-control select2" id="jenis">
                                                        @foreach($jenis as $key => $value)
                                                            <option value="{{ $key }}" {{ $selectedJenis == $key? 'selected=""' : ''}}>{{ $value }}</option>
                                                        @endforeach
                                                    </select> 
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group row"><label class="col-sm-4 col-form-label">Kelipatan *</label>
                                                <div class="col-sm-8 error-text">
                                                    <select name="kelipatan" class="form-control select2" id="kelipatan">
                                                        @foreach($kelipatan as $key => $value)
                                                            <option value="{{ $key }}" {{ $selectedKelipatan == $key? 'selected=""' : ''}}>{{ $value }}</option>
                                                        @endforeach
                                                    </select> 
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row"><label class="col-sm-2 col-form-label">Product *</label>
                                        <div class="col-sm-10 error-text">
                                            <select name="produk" class="form-control select2_product" id="produk">
                                                @if(isset($data))
                                                    <option value="{{ $data->produk }}">{{ $data->nama_product }}</option> 
                                                @endif
                                            </select> 
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group row"><label class="col-sm-4 col-form-label">Jml Pembelian *</label>
                                                <div class="col-sm-8 error-text">
                                                    <input type="text" class="form-control" name="jml_produk" id="jml_produk" value="{{ isset($data)? $data->jml_produk : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-2">
                                            <div class="col-sm-12 error-text">
                                                <select name="satuan" class="form-control select2_satuan" id="satuan">
                                                    @foreach($satuan as $key => $value)
                                                        <option value="{{ $value->id }}" {{ $selectedSatuan == $value->id? 'selected=""' : ''}}>{{ $value->nama }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="diskon_barang">
                                        <div class="form-group row"><label class="col-sm-2 col-form-label">Bonus Product *</label>
                                            <div class="col-sm-10 error-text">
                                                <select name="bonus_produk" class="form-control select2_product" id="bonus_produk">
                                                @if(isset($data))
                                                    <option value="{{ $data->bonus_produk }}">{{ $data->produk_bonus }}</option> 
                                                @endif
                                                </select> 
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group row"><label class="col-sm-4 col-form-label">Jml Bonus *</label>
                                                    <div class="col-sm-8 error-text">
                                                        <input type="text" class="form-control" name="jml_bonus" id="jml_bonus" value="{{ isset($data)? $data->jml_bonus : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-2">
                                                <div class="col-sm-12 error-text">
                                                    <select name="satuan_bonus" class="form-control select2_satuan" id="satuan_bonus">
                                                        @foreach($satuan as $key => $value)
                                                            <option value="{{ $value->id }}" {{ $selectedSatuanBonus == $value->id? 'selected=""' : ''}}>{{ $value->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="diskon_uang">
                                    <div class="col-sm-6">
                                        <div class="form-group row">
                                            <label class="col-sm-4 col-form-label">Nilai Diskon</label>
                                            <div class="col-sm-8">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="nilai_diskon" name="nilai_diskon" value="{{isset($data)? $data->nilai_diskon : ''}}"><span class="input-group-addon">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('diskon.index')}}">Batal</a>
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
        head_diskon()

        $('#submitData').validate({
            rules: {
                name:{
                    required: true
                },
            },
            messages: {
                name: {
                    required: "Nama Diskon tidak boleh kosong"
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
                url : "{{route('diskon.simpan')}}",
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
                        window.location.replace('{{route("diskon.index")}}');
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

    $('.select2').select2()
    $('.select2_satuan').select2({
        placeholder: 'Satuan ...'
    })
    
    $('.select2_product').select2({
        placeholder: 'Pilih Product ...',
        ajax: {
            url: "{{ route('diskon.product') }}",
            dataType: 'JSON',
            data: function(params) {
              return {
                search: params.term
              }
            },
            processResults: function (data) {
                var results = [];
                $.each(data, function(index, item){
                    results.push({
                        id: item.id,
                        text : item.nama,
                    });
                });
                return{
                    results: results
                };
            }
        }
    })

    $("#jml_product").TouchSpin({
        min: 0,
        max: 9999999,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    })

    $(document).on('change', '#parent', function(){
        head_diskon()
    })

    $(document).on('change', '#jenis', function(){
        var jenis = $('#jenis option:selected').val()
        if(jenis == 0){
            $('#diskon_uang').show()
            $('#diskon_barang').hide()
            $('#bonus_produk').attr('disabled', true)
            $('#jml_bonus').val('')
            $('#satuan_bonus').attr('disabled', true)
        }else if(jenis == 1){
            $('#diskon_uang').hide()
            $('#diskon_uang').val('')
            $('#diskon_barang').show()
            $('#bonus_produk').attr('disabled', false)
            $('#satuan_bonus').attr('disabled', false)
        }
    })
</script>
<script>
    function diskon_jenis(){
        var jenis = $('#jenis option:selected').val()
        if(jenis == 0){
            $('#diskon_uang').show()
            $('#diskon_barang').hide()
        }else if(jenis == 1){
            $('#diskon_uang').hide()
            $('#diskon_barang').show()
        }
    }

    function disabled(){
        $('#jenis').attr('disabled', true)
        $('#kelipatan').attr('disabled', true)
        $('#produk').attr('disabled', true)
        $('#satuan').attr('disabled', true)
        $('#jml_produk').val('')
    }

    function enabled(){
        $('#jenis').attr('disabled', false)
        $('#kelipatan').attr('disabled', false)
        $('#produk').attr('disabled', false)
        $('#satuan').attr('disabled', false)
    }

    function head_diskon(){
        var parent = $('#parent option:selected').attr('name')
        if(parent == 'Distributor'){
            $('#diskon_detail').show()
            $('#diskon_distributor').show()
            $('#diskon_principal').hide()
            $('#diskon_uang').show()
            $('#diskon_barang').hide()
            disabled()
        }else if(parent == 'Principal'){
            $('#diskon_detail').show()
            $('#diskon_distributor').hide()
            $('#diskon_principal').show()
            enabled()
            diskon_jenis()
        }else{
            $('#diskon_detail').hide()
        }
    }
</script>
@endpush