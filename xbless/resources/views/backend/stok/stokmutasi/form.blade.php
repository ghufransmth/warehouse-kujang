@extends('layouts.layout')

@section('title', 'Manajemen Mutasi Stock ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Form Mutasi Stock</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <strong>Stok</strong>
            </li>
            <li class="breadcrumb-item active">
                <strong>Mutasi Stock</strong>
            </li>
        </ol>
    </div>

</div>
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
                    <form id="submitData" name="submitData">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($stokmutasi)? $enc_id : ''}}">

                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">No Transaksi * : </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="no_transaksi" name="no_transaksi" value="">
                            </div>
                            <label class="col-sm-2 col-form-label">Tanggal Mutasi * : </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_mutasi" name="tgl_mutasi"
                                    value="{{date('d-m-Y')}}" />
                            </div>
                        </div>
                        {{-- <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Perusahaan * : </label>
                                <div class="col-sm-10 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan" {{isset($stokmutasi)? ($stokmutasi->flag_proses=='1'?'disabled':'') : ''}}>
                        <option value="">Pilih Perusahaan</option>
                        @foreach($perusahaan as $key => $row)
                        <option value="{{$row->id}}" {{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>
                            {{ucfirst($row->name)}}</option>
                        @endforeach
                        </select>
                </div>

            </div> --}}
            <div class="form-group row">
                {{-- <label class="col-sm-2 col-form-label">Dari Gudang * </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="gudang_from" name="gudang_from">

                                    </select>
                                </div> --}}
                <label class="col-sm-2 col-form-label">Tujuan Gudang * </label>
                <div class="col-sm-10 error-text">
                    <select class="form-control select2" id="gudang_to" name="gudang_to">
                        <option value="0">Pilih Gudang</option>
                        <option value="1">Gudang Penjualan</option>
                        <option value="2">Gudang BS</option>
                    </select>
                </div>
            </div>



            <div class="hr-line-dashed"></div>
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">Pilih Produk </label>
                <div class="col-sm-10 error-text">
                    <select class="form-control selectProduct" id="pilihProduct" name="pilihProduct">
                    </select>
                </div>
            </div>




            <div class="hr-line-dashed"></div>
            <div class="table-responsive">
                <table id="table1" class="table display p-0 table-hover table-striped" style="overflow-x: auto;">
                    <thead>
                        <tr class="text-white text-center bg-primary">
                            <th class="no-sort">Produk</th>
                            <th>Stok Gudang</th>
                            <th>Satuan</th>
                            <th>Qty Mutasi</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="detailData">
                    </tbody>
                </table>
            </div>
            </form>
            <div class="hr-line-dashed"></div>
            <div class="form-group row">
                <div class="col-sm-4 col-sm-offset-2">
                    @can('stokmutasi.tambah')
                    <button class="btn btn-primary btn-sm" type="button" id="simpan">Simpan</button>
                    @endcan
                </div>
            </div>


        </div>
    </div>
</div>
</div>
</div>

@endsection
@push('scripts')

<script>
    $(document).ready(function() {
    $(window).keydown(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            return false;
        }
    });
    });
    var table;
    $(".select2").select2();
    $(document).ready(function () {


        $('.formatTgl').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            calendarWeeks: true,
            autoclose: true,
            format: "dd-mm-yyyy"
        });
        table=$('#table1').DataTable({
            "dom": 'rt',
            "ordering": false,
            "pageLength": 100,
        });
        $("#perusahaan").on('change', function(){

            var perusahaan_id = $(this).val();
            if(perusahaan_id) {
                $.ajax({
                    url: '{{route("stokmutasi.perusahaan_gudang",[null])}}/' + perusahaan_id,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        // $('#gudang').empty();
                        $("#gudang_from").empty().trigger('change')
                        $('#gudang_from').append('<option value="">Pilih Gudang Awal</option>');
                        $.each(data, function(key, value) {
                            $('#gudang_from').append('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                        });
                        $('#table1 tbody > tr').remove();
                    }
                });
            }else{
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                $('#gudang_from').empty();
            }
            loadProduct();
        });
        $("#gudang_from").on('change', function(){
            var gudang_id = $(this).val();
            if(gudang_id=="") {
                Swal.fire('Ups','Silahkan Pilih Gudang Awal terlebih dahulu','info');
            }else{
                $('#table1 tbody > tr').remove();
            }
            loadProduct();
            loadGudangTujuan();
        });
        $('#simpan').on('click', function() {
            if($("#submitData").valid()){
                Swal.showLoading();
                var jumlah=$('#table1 >tbody >tr').length;
                if(jumlah==0){
                    Swal.hideLoading();
                    Swal.fire('Ups','Tidak ada data Mutasi Stok. Silahkan tambahkan produk terlebih dahulu','info');
                    return false;
                }else{
                    SimpanData();
                }
            }
        });
        $('#submitData').validate({
            rules: {
            no_transaksi:{
                required: true
            },
            perusahaan:{
                required: true
            },
            gudang_from:{
                required: true
            },
            tgl_mutasi:{
                required: true,
            },
            gudang_to:{
                required: true,
            }
            },
            messages: {
            no_transaksi: {
                required: "No Transaksi tidak boleh kosong"
            },
            perusahaan: {
                required: "Perusahaan wajib dipilih salah satu."
            },
            gudang_from: {
                required: "Gudang Awal wajib dipilih salah satu."
            },
            tgl_transaksi: {
                required: "Tanggal Mutasi tidak boleh kosong",
            },
            gudang_to: {
                required: "Gudang Tujuan wajib dipilih salah satu."
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
<script>
    // function loadGudangTujuan(){
        //     var perusahaan_id = $('#perusahaan').val();
        //     var gudang_from   = $('#gudang_from').val();
        //     if(perusahaan_id) {
        //         $.ajax({
        //             url: '{{route("stokmutasi.perusahaan_gudang",[null])}}/' + perusahaan_id,
        //             type: "GET",
        //             dataType: "json",
        //             success:function(data) {
        //                 // $('#gudang').empty();
        //                 $("#gudang_to").empty().trigger('change')
        //                 $('#gudang_to').append('<option value="">Pilih Gudang Tujuan</option>');
        //                 $.each(data, function(key, value) {
        //                     if(value['id'] != gudang_from){
        //                         $('#gudang_to').append('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
        //                     }
        //                 });
        //                 $('#table1 tbody > tr').remove();
        //             }
        //         });
        //     }else{
        //         Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
        //         $('#gudang_to').empty();
        //     }
        // }
        function SimpanData(){
            $('#simpan').addClass("disabled");
            var form = $('#submitData').serializeArray()
            var dataFile = new FormData()
            var jumlah = $('#table1 >tbody >tr').length;
            dataFile.append('jumlahdata', jumlah);
            $.each(form, function(idx, val) {
                dataFile.append(val.name, val.value)
            })

            $.ajax({
                type: 'POST',
                url : "{{route('stokmutasi.simpan')}}",
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
                        location.reload();

                    } else {
                        Swal.fire('Ups',data.message,'info');
                    }
                },
                complete: function () {
                        Swal.hideLoading();
                        $('#simpan').removeClass("disabled");
                },
                error: function(data){
                        $('#simpan').removeClass("disabled");
                        Swal.hideLoading();
                        console.log(data);
                }
        });
    }
    $("#table1").on('click', '.remove', function() {
         $(this).closest('tr').remove();
    });
    $('#gudang_to').on('change', function(){
        // console.log('tes');
        loadProduct();
        $('#table1 tbody > tr').remove();
    });
    function loadProduct(){
        // var perusahaan_id = $('#perusahaan').val();
        // console.log('tes');
        var gudang_id = $('#gudang_to').val();
        $('.selectProduct').select2({
        ajax: {
            url: '{{route('stokmutasi.getproduct')}}',
            dataType: 'json',
            data: function (params) {
                var query = {
                    term: params.term,
                    gudang_id : gudang_id,
                }
                return query;
            }
        }
    })
    }

</script>

<script>
    $( "#pilihProduct" ).change(function() {
        var gudang_id = $('#gudang_to').val();
        var val = [];

        $("input[name='product[]']").each(function(i){
            val[i] = $(this).val();
        });
        if(gudang_id==''){
            Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
            return false;
        }else{
            if(jQuery.inArray($(this).val(), val) != -1) {
                Swal.fire('Ups','Produk sudah ada di data keranjang','info');
                $('#pilihProduct').val('');
            }else {
                var id = $(this).val();
                $.ajax({
                    type: 'POST',
                    url: '{{route('stokmutasi.tambahproduk')}}',
                    data: {
                        _token  : '{{csrf_token()}}',
                        id_product    : $(this).val(),
                        gudang_id: gudang_id,
                    },
                    dataType: 'json',
                    success: function(result){
                        $('#detailData').append(result.data);
                        $('.satuan_select'+id).select2();
                        $(".qty").TouchSpin({
                                min:1,
                                max:result.stok,
                                buttondown_class: 'btn btn-white',
                                buttonup_class: 'btn btn-white'
                        });
                    }
                });
                $('#pilihProduct').val('');
            }
        }

    });


</script>
@endpush
