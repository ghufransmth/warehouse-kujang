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
                            
                            {{ session('message')['desc'] }}
                            </div>
                        @endif
                        <a id="tambah" href="#" class="btn btn-primary" style="display: {{ isset($sales)? 'none' : '' }};" data-toggle="tooltip" data-placement="top" title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
                    </div>
                    <div class="ibox-content">
                        <form id="submitData">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            @if(isset($sales))
                                <input type="hidden" name="enc_id" id="enc_id" value="{{isset($sales)? $enc_id : ''}}">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group row"><label class="col-sm-4 col-form-label">Sales *</label>
                                            <div class="col-sm-8 error-text">
                                                <select name="sales" id="sales" class="form-control sales">
                                                    <option value="{{ $sales->sales_id }}">{{ ucfirst($sales->sales) }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group row"><label class="col-sm-4 col-form-label">Toko *</label>
                                            <div class="col-sm-8 error-text">
                                                <select name="toko" id="toko" class="form-control">
                                                    <option value="{{ $sales->toko_id }}">{{ ucfirst($sales->toko) }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group row"><label class="col-sm-4 col-form-label">PJP Day *</label>
                                            <div class="col-sm-8 error-text">
                                                <select name="hari" id="hari" class="select2 form-control toko">
                                                    @foreach($hari as $key => $value)
                                                        <option value="{{ $value->id }}" {{ $selectedHari == $value->id ? 'selected=""' : ''}}>{{ ucfirst($value->name) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group row"><label class="col-sm-4 col-form-label">Skala Kunjungan *</label>
                                            <div class="col-sm-8 error-text">
                                                <select name="skala" id="skala" class="select2 form-control">
                                                    @foreach($skala as $key => $value)
                                                        <option value="{{ $value }}" {{ $selectedSkala == $key ? 'selected=""' : '' }}>{{ ucfirst($value) }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row"><label class="col-sm-2 col-form-label">No Faktur *</label>
                                    <div class="col-sm-10 error-text">
                                        <input type="text" name="faktur" id="faktur" class="form-control" value="{{ $sales->faktur_piutang }}">
                                    </div>
                                </div>
                            @else
                                <input type="hidden" id="total_data" name="total_data" value="0">
                                <table id="table1" class="table p-0 table-hover table-striped" style="overflow-x: auto;">
                                    <thead>
                                        <tr class=" text-white text-center bg-primary">
                                            <th>Salesman</th>
                                            <th>Toko</th>
                                            <th>Hari Kunjungan</th>
                                            <th>Skala Kunjungan</th>
                                            <th>Faktur Piutang</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="data_sales">

                                    </tbody>
                            </table>
                            @endif
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
                url : "{{route('kunjungan.simpan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function(response){
                    if (response.code == 201) {
                        Swal.fire('Yes',response.data.message,'success');
                        window.location.replace('{{route("kunjungan.index")}}');
                    } else {
                        Swal.fire('Ups',response.data.message,'info');
                    }
                },
                complete: function () {
                    Swal.hideLoading();
                    $('#simpan').removeClass("disabled");
                },
                error: function(response){
                    $('#simpan').removeClass("disabled");
                    Swal.hideLoading();
                    Swal.fire('Ups','Ada kesalahan pada sistem','info');
                    console.log(response);
                }
            });
        }

        $('.sales').select2({
            ajax: {
                url: "{{ route('sales.getSales') }}",
                dataType: 'JSON',
                delay: 250,
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

        $('#toko').select2({
            ajax: {
                url: "{{ route('toko.gettoko') }}",
                dataType: 'JSON',
                delay: 250,
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
                            text : item.name,
                        });
                    });
                    return{
                        results: results
                    };
                }
            }
        })
    });
    function removeSpaces(string) {
        return string.split(' ').join('');
    }
</script>
<script>
    $('#table1').DataTable({
        "paging":   false,
        "ordering": false,
        "info":     false,
        "filter": false,
        "language": {
            "zeroRecords": "",
            "emptyTable": ""
        }
    })

    $(document).on('click', '#tambah', function(){
        var total_data = $('#total_data').val();
        var total = 1 + parseInt(total_data);
        $('#total_data').val(total);
        console.log(total)
        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{route("kunjungan.list_data")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
              $('#data_sales').prepend(response);
            }
        });
    })
    function deleteProduk(id){
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
                $('#dataajaxproduk_'+id).remove();
                var total_data = $('#total_data').val();
                console.log(total_data)
                var total = parseInt(total_data) - 1;
                $('#total_data').val(total);
                console.log(total)
                Swal.fire(
                  'Pesan',
                  'Data Berhasil Dihapus.',
                  'success'
                )
            }
        })
    }
</script>
@endpush
