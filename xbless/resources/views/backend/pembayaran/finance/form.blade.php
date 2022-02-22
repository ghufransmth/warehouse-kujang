@extends('layouts.layout')
@section('title', 'Penyesuaian Keuangan ')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Penyesuaian Keuangan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="#">Penyesuaian Keuangan</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Penyesuaian Keuangan</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('transaksi.finance.index')}}">Batal</a>
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
                    <input type="hidden" name="enc_id" id="enc_id" value="{{isset($finance)? $enc_id : ''}}">
                        <div class="row">
                            <div class="col-sm">
                                <div class="form-group  row"><label class="col-sm-4 col-form-label">Komponen *</label>
                                    <div class="col-sm-8">
                                        <select id="akun_base" class="form-control chosen-select select2-komponen" name="komponen">
                                            @if(isset($finance))
                                                <option value="{{ $komponen->id }}">{{ $komponen->name }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="form-group row"><label class="col-sm-4 col-form-label">Nominal</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-addon">Rp.</span><input type="text" class="form-control" id="total_nominal" name="total_nominal" autocomplete="off" value="{{ isset($finance) ? $finance->total :''}}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group row"><label class="col-sm-4 col-form-label">Tgl Transaksi</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span><input type="text" class="form-control" id="tgl_transaksi" name="tgl_transaksi" value="{{ isset($finance) ? $finance->tgl_transaksi :''}}" autocomplete="off">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Keterangan</label>
                            <div class="col-sm-10">
                                <textarea name="keterangan" class="form-control" id="keterangan">{{ isset($finance) ?$finance->keterangan:''}}</textarea>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                            <input type="hidden" class="form-control mb-1" name="total_data" id="total_data" value="{{ isset($finance) ? $total_detail : 0 }}">
                            <a id="tambah_detail" class="btn btn-success text-white"><span class="fa fa-plus"></span> Tambah Detail</a>
                        <div class="hr-line-dashed"></div>
                            <table class="table" style="width:100%;" id="example" >
                                <thead class="text-center">
                                    <tr>
                                        <th>Name</th>
                                        <th>Nominal</th>
                                        <th>Tgl Transaksi</th>
                                        <th>Keterangan</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_form">
                                    @if(isset($finance))
                                        @foreach($detail as $kunci => $value)
                                            <tr id='dataajaxproduk_{{$value->id}}' class="data_file">
                                                <td>
                                                    <input type="text" class="form-control" id="name" name="name[]" value="{{$value->name}}">
                                                </td>
                                                <td><div class='input-group'>
                                                    <span class='input-group-addon'>Rp.</span><input type='text' class='form-control nominal_akun' id='nominal' name='nominal[]' value="{{ $value->nominal }}" autocomplete='off'>
                                                </div></td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span><input type="text" class="form-control" id="tgl_transaksi" name="tgl_transaksi[]" value="{{ isset($finance) ? $finance->tgl_transaksi :''}}" autocomplete="off">
                                                    </div>
                                                </td>
                                                <td><input type='text' class='form-control' id='note' value="{{$value->keterangan}}" name='note[]'></td>
                                                <td class='text-center'><a href='#!' onclick='javascript:deleteProduk({{$value->id}})' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            <div class="col-sm-4 col-sm-offset-2">
                                <a class="btn btn-white btn-sm" href="{{route('transaksi.finance.index')}}">Batal</a>
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
        $('#table1').DataTable()
        $('#submitData').validate({
            rules: {
            },
            messages: {
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
                url : "{{route('transaksi.finance.simpan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                success: function(response){
                    if (response.data.code == 201) {
                        Swal.fire('Yes',response.data.message,'success');
                        window.location.replace('{{route("transaksi.finance.index")}}');
                    } else if(response.data.code == 417){
                        Swal
                            .fire({
                                title: response.data.detail.title,
                                text: response.data.detail.message,
                                icon: 'info'
                            }).then((value) => {
                                Swal.fire({
                                    title: response.data.detail.title,
                                    text: response.data.detail.detailMessage,
                                    icon: 'info'
                                })
                            })
                    }else{
                        Swal.fire('Ups',response.data.message,'info');
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
        $('#tgl_transaksi').datepicker({
            autoclose: true,
            format: "dd-mm-yyyy",
            endDate: '+0d',
        });
    });
</script>
<script>
    $('.select2').select2()
    $('.select2_jenis_detail').select2({
        width: '300px',
    })
    $("#example").on('keyup', '.nominal_akun', function() {
        var parent = $(this).closest('tr');
        var value = Number(this.value.replace(/\./g, ""));
        value = formatRupiah(this.value, '');
        var nilai = this.value.replace(/\./g, "");
        loadGrand();
        if(value.charAt(0) > 0){
            $('.nominal_akun',parent).val(getprice(nilai));
        }else{
            if(value.charAt(1)=='0'){
                $('.nominal_akun',parent).val(0);
            }else{
                $('.nominal_akun',parent).val(getprice(value));
            }
        }
    });

    $('.select2-komponen').select2({
        placeholder: 'Pilih Komponen ...',
        ajax: {
            url: "{{ route('komponen.get_komponen') }}",
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
                        text : item.name,
                    });
                });
                return{
                    results: results
                };
            }
        }
    })
   
    $(document).on('click', '#tambah_detail', function(){
        var total_data = $('#total_data').val();
        var total = 1 + parseInt(total_data);
        $('#total_data').val(total);
        console.log(total)
        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{route("transaksi.finance.list_data")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
              $('#detail_form').prepend(response);
            }
        })
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
                loadGrand();
                console.log(total)
                Swal.fire(
                  'Pesan',
                  'Data Berhasil Dihapus.',
                  'success'
                )
            }
        })
    }
    function loadGrand(){
        var total = 0;
        $(".nominal_akun").each(function(){
            total += parseInt($(this).val().replace(/\./g, ''));
        });
        
        console.log(total)
        $('#total_nominal').val(getprice(total));
    }
</script>
<script>
    function getprice(nStr){
        nStr+='';
        x=nStr.split('.');
        x1=x[0];
        x2=x.length>1?'.'+x[1]:'';
        var rgx = /(\d+)(\d{3})/;
        while(rgx.test(x1)){
            x1=x1.replace(rgx,'$1'+'.'+'$2');
        }
        return x1+x2;
    }
    function formatRupiah(angka, prefix){
        var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa  = split[0].length % 3,
        nilai_asset = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);
        if(ribuan){
            separator = sisa ? '.' : '';
            nilai_asset += separator + ribuan.join('.');
        }
        nilai_asset = split[1] != undefined ? nilai_asset + ',' + split[1] : nilai_asset;
        return prefix == undefined ? nilai_asset : (nilai_asset ? '' + nilai_asset : '');
    }
</script>
@endpush