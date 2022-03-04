@extends('layouts.layout')

@section('title', 'Manajemen Stock Opname ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($stokopname) ? 'Manage' : 'Tambah'}} Stock Opname</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('stokopname.index')}}">Stock Opname</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($stokopname) ? 'Manage' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('stokopname.index')}}">Batal</a>
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
                        <form id="submitData" name="submitData">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($stokopname)? $enc_id : ''}}">

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">No Transaksi * : </label>
                                <div class="col-sm-4 error-text">
                                    <input type="text" class="form-control"  id="no_transaksi" name="no_transaksi" value="{{isset($stokopname)? $stokopname->notransaction : ''}}" {{isset($stokopname)? ($stokopname->flag_proses=='1'?'readonly':'') : ''}}>
                                </div>
                                <label class="col-sm-2 col-form-label">Tanggal Transaksi * : </label>
                                <div class="col-sm-4 error-text">
                                    <input type="text" class="form-control formatTgl" id="tgl_transaksi" name="tgl_transaksi" value="{{isset($stokopname)? date('d-m-Y',strtotime($stokopname->faktur_date)) : date('d-m-Y')}}" {{isset($stokopname)? ($stokopname->flag_proses=='1'?'readonly':'') : ''}}>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Dari Gudang * : </label>
                                <div class="col-sm-10 error-text">
                                    <select class="form-control select2" id="gudang_dari" name="gudang_dari" {{isset($stokopname)? ($stokopname->flag_proses=='1'?'disabled':'') : ''}}>
                                        <option value="">Pilih Gudang</option>
                                        @foreach($gudang as $key => $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>

                                        @endforeach
                                    </select>
                                </div>
                                {{-- <label class="col-sm-2 col-form-label">Gudang Tujuan * </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="gudang" name="gudang">
                                        <option value="">Pilih Gudang Tujuan</option>
                                        <option value="1">Gudang Penjualan</option>
                                        <option value="2">Gudang BS</option>
                                    </select>
                                </div> --}}
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">PIC * : </label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" value="{{isset($stokopname)? $stokopname->pic : auth()->user()->username}}" id="pic" name="pic" {{isset($stokopname)? ($stokopname->flag_proses=='1'?'readonly':'') : ''}}>
                                </div>
                                {{-- <label class="col-sm-2 col-form-label">Suplier * : </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="gudang_to" name="gudang_to">
                                        <option value="0">Pilih Suplier</option>
                                        @foreach($suplier as $key => $value)
                                            <option value="{{ $value->id }}">{{ $value->nama }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Catatan : </label>
                                <div class="col-sm-10 error-text">
                                    <textarea class="form-control" id="note" name="note" {{isset($stokopname)? ($stokopname->flag_proses=='1'?'readonly':'') : ''}}>{{isset($stokopname)? $stokopname->note : '-'}}</textarea>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row" style="{{isset($stokopname)? ($stokopname->flag_proses=='1'?'display:none':'') : ''}}">
                                <label class="col-sm-2 col-form-label">Pilih Produk </label>
                                <div class="col-sm-9 error-text">
                                    <select class="form-control selectProduct" id="pilihProduct" name="pilihProduct">
                                    </select>
                                </div>
                                <label class="col-sm-1">
                                <a class="btn btn-primary btn-sm" style="color: white" id="clearproduct">Clear</a>
                                </label>
                            </div>
                            <div class="form-group row" style="margin-top:-30px;margin-bottom:-10px">
                                <label class="col-sm-2 col-form-label" style="{{isset($stokopname)? ($stokopname->flag_proses=='1'?'display:none':'') : ''}}">Atau </label>
                            </div>
                            <div class="form-group row" style="{{isset($stokopname)? ($stokopname->flag_proses=='1'?'display:none':'') : ''}}">
                                <label class="col-sm-2 col-form-label">Scan Barcode </label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" value="" id="scanbarcode" name="scanbarcode">
                                </div>
                            </div>



                            <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                                <table id="table1" class="table" >
                                <thead>
                                <tr>
                                    <th>Produk</th>
                                    <th>Stok Gudang</th>
                                    <th>Satuan</th>
                                    <th>Stok Opname</th>
                                    @if(isset($stokopname))
                                       @if($stokopname->flag_proses=='1')
                                       @else
                                       <th class="text-right">Aksi</th>
                                       @endif
                                    @else
                                    <th class="text-right">Aksi</th>
                                    @endif

                                </tr>
                                </thead>
                                <tbody id="detailData">
                                </tbody>
                            </table>
                            </div>
                        </form>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-10 col-sm-offset-2">
                                    @if(isset($stokopname))
                                       @if($stokopname->flag_proses=='1')
                                         <a href="{{route('stokopname.print',$enc_id)}}" class="btn btn-success btn-sm"  id="print" target='_blank'><i class="fa fa-print"></i> Print</a>
                                       @else
                                         <a class="btn btn-white btn-sm" href="{{route('stokopname.index')}}">Kembali</a>
                                         @can('stokopname.ubah')
                                         <button class="btn btn-primary btn-sm" type="button" id="simpan">Simpan</button>
                                         @endcan
                                         @can('stokopname.approve')
                                         <button class="btn btn-success btn-sm" type="button" id="approve">Simpan & Approve</button>
                                         @endcan
                                         <a href="{{route('stokopname.print',$enc_id)}}" class="btn btn-success btn-sm"  id="print" target='_blank'><i class="fa fa-print"></i> Print</a>
                                       @endif
                                    @else
                                    <a class="btn btn-white btn-sm" href="{{route('stokopname.index')}}">Kembali</a>
                                        @can('stokopname.tambah')
                                        <button class="btn btn-primary btn-sm" type="button" id="simpan">Simpan</button>
                                        @endcan
                                    @endif
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

        @if(isset($stokopname))
            var perusahaan_id = $('#perusahaan').val();
            var selectedgudang = '{{$selectedgudang}}';
            if(perusahaan_id) {
                $.ajax({
                    url: '{{route("stokopname.perusahaan_gudang",[null])}}/' + perusahaan_id,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        $("#gudang").empty().trigger('change')
                        $.each(data, function(key, value) {
                            $('#gudang').append('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                            $("#gudang").val(selectedgudang).trigger('change');
                        });
                        $('#table1 tbody > tr').remove();
                    }
                });
            }else{
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                $('#gudang').empty();
            }
            loadProduct();
            loadDataDetail();

        @endif
        $("#scanbarcode").on('change', function(){
            var perusahaan = $('#perusahaan').val();
            var gudang_id = $('#gudang').val();
            var val = [];
            $("input[name='product[]']").each(function(i){
                val[i] = $(this).val();
            });
            if(perusahaan==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                $('#scanbarcode').val('');
                return false;
            }
            else if(gudang_id==''){
                Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
                $('#scanbarcode').val('');
                return false;
            }else{
                $.ajax({
                    type: 'POST',
                    url: '{{route('stokopname.tambahprodukbarcode')}}',
                    data: {
                        _token: '{{csrf_token()}}',
                        barcode : $(this).val(),
                        perusahaan_id : perusahaan,
                        gudang_id: gudang_id,
                        product_id: val,
                    },
                    dataType: "json",
                    success: function(result){
                        if (result.success) {
                            $('#detailData').append(result.message);
                            $(".qty").TouchSpin({
                                min:-1000000,
                                max : 1000000,
                                buttondown_class: 'btn btn-white',
                                buttonup_class: 'btn btn-white'
                            });
                        } else {
                            Swal.fire('Ups',result.message,'info');
                            return false;
                        }

                    }
                });
                $('#scanbarcode').val('');
            }
        });
        $('#scanbarcode').keyup(function(e){
            if(e.keyCode == 13)
            {
                var perusahaan = $('#perusahaan').val();
                var gudang_id = $('#gudang').val();
                var val = [];
                $("input[name='product[]']").each(function(i){
                    val[i] = $(this).val();
                });

                if(perusahaan==''){
                    Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                    $('#scanbarcode').val('');
                    return false;
                }
                else if(gudang_id==''){
                    Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
                    $('#scanbarcode').val('');
                    return false;
                }else{
                    $.ajax({
                        type: 'POST',
                        url: '{{route('stokopname.tambahprodukbarcode')}}',
                        data: {
                            _token: '{{csrf_token()}}',
                            barcode : $(this).val(),
                            perusahaan_id : perusahaan,
                            gudang_id: gudang_id,
                            product_id: val,
                        },
                        dataType: "json",
                        success: function(result){
                            if (result.success) {
                                $('#detailData').append(result.message);
                                $(".qty").TouchSpin({
                                    min:-1000000,
                                    max : 1000000,
                                    buttondown_class: 'btn btn-white',
                                    buttonup_class: 'btn btn-white'
                                });
                            } else {
                                Swal.fire('Ups',result.message,'info');
                                return false;
                            }

                        }
                    });
                    $('#scanbarcode').val('');
                }
            }
        });

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
                    url: '{{route("stokopname.perusahaan_gudang",[null])}}/' + perusahaan_id,
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        // $('#gudang').empty();
                        $("#gudang").empty().trigger('change')
                        $('#gudang').append('<option value="">Pilih Gudang</option>');
                        $.each(data, function(key, value) {
                            $('#gudang').append('<option value="'+ value['id'] +'">'+ value['name'] +'</option>');
                        });
                        $('#table1 tbody > tr').remove();
                    }
                });
            }else{
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info');
                $('#gudang').empty();
            }
            loadProduct();
        });
        $("#gudang").on('change', function(){
            // var gudang_id = $(this).val();
            // if(gudang_id=="") {
            //     Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
            // }else{
            //     $('#table1 tbody > tr').remove();
            // }
            loadProduct();
            deleterow();
        });
        $("#gudang_dari").on('change', function(){
            // var gudang_id = $(this).val();
            // if(gudang_id=="") {
            //     Swal.fire('Ups','Silahkan Pilih Gudang terlebih dahulu','info');
            // }else{
            //     $('#table1 tbody > tr').remove();
            // }
            loadProduct();
            deleterow();
            showallproduct();
        });
        $('#simpan').on('click', function() {
            if($('#pilihProduct').val() != null){
                Swal.fire('Ups','Silahkan tekan tombol clear produk terlebih dahulu','info');
                return false;
            }
            if($("#submitData").valid()){
                Swal.showLoading();
                var jumlah=$('#table1 >tbody >tr').length;
                if(jumlah==0){
                    Swal.hideLoading();
                    Swal.fire('Ups','Tidak ada data Stock Opname. Silahkan tambahkan produk terlebih dahulu','info');
                    return false;
                }else{
                    SimpanData(0);
                }
            }
        });
        $('#approve').on('click', function() {
            if($("#submitData").valid()){
                Swal.showLoading();
                var jumlah=$('#table1 >tbody >tr').length;
                if(jumlah==0){
                    Swal.hideLoading();
                    Swal.fire('Ups','Tidak ada data Stock Opname. Silahkan tambahkan produk terlebih dahulu','info');
                    return false;
                }else{
                    SimpanData(1);
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
            gudang:{
                required: true
            },
            tgl_transaksi:{
                required: true,
            },
            pic:{
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
            gudang: {
                required: "Gudang wajib dipilih salah satu."
            },
            tgl_transaksi: {
                required: "Tanggal Transaksi tidak boleh kosong",
            },
            pic: {
                required: "PIC tidak boleh kosong"
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
        function SimpanData(approve){
            $('#simpan').addClass("disabled");
            var form = $('#submitData').serializeArray()
            var dataFile = new FormData()
            var jumlah = $('#table1 >tbody >tr').length;
            dataFile.append('jumlahdata', jumlah);
            dataFile.append('approve', approve);
            $.each(form, function(idx, val) {
                dataFile.append(val.name, val.value)
            })

            $.ajax({
                type: 'POST',
                url : "{{route('stokopname.simpan')}}",
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
                        window.location.replace('{{route("stokopname.index")}}');
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

    function loadProduct(){
        $('.selectProduct').select2({
        ajax: {
            url: '{{route('stokopname.getproduct')}}',
            dataType: 'json',
            data: function (params) {
                return {
                    term: params.term,
                    gudang: $('#gudang_dari').val(),
                }
            }
        }
    });
    }

    function deleterow(){
        $('#table1 tbody > tr').remove();

    }
    function loadDataDetail(){
        var enc_id = $('#enc_id').val();

        $.ajax({
            type: 'POST',
            url: '{{route('stokopname.detaildataform')}}',
            data: {
                _token  : '{{csrf_token()}}',
                enc_id: enc_id,
            },
            success: function(result){
                // $('#detailData').append(result.html);
                    $.each(result.data, function(key, value) {
                        $html='';
                        $html+='<tr>';
                        $html+='<td>';
                            $html+='<span class="product">'+ value['namaproduk'] +'</span>';
                            $html+='<input type="hidden" class="product_value" id="product" name="product[]" value="'+ value['produk_id'] +'">';
                        $html+='</td>';
                        $html+='<td>';
                            $html+='<span class="price">'+ value['qtyproduk'] +'</span>';
                            $html+='<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'+ value['qtyproduk'] +'">';
                        $html+='</td>';
                        $html+='<td>';
                            $html+='<span class="choose">'+ value['satuan'] +'</span>';
                            $html+='<input type="hidden" class="satuan_value" min=0 id="satuan" name="satuan[]" value="'+value['satuan']+'">';
                        $html+='</td>';
                            $html+='<td width="20%">';
                            $html+='<input type="text" class="form-control qty" min=-'+ value['qtyproduk'] +' id="qty_so" name="qty_so[]" value="'+ value['qtySO'] +'" '+ value['readonly'] +'>';
                        $html+='</td>';
                        if(result.flag_process !='1'){
                            $html+='<td class="text-right">';
                            $html+='<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
                            $html+='</td>';
                        }
                        $html+='</tr>';
                        $('#detailData').append($html);
                        $(".qty").TouchSpin({
                            min :value['qtyproduk_min'],
                            max : 1000000,
                            buttondown_class: 'btn btn-white',
                            buttonup_class: 'btn btn-white'
                    });
                });

            }
        });
    }
</script>

<script>
var allproduct = [];
var allnamaproduct = [];
var allstok = [];
var allsatuan = [];
var allqty = [];
var satuan = [];
function showallproduct(){
        $.ajax({
            type: 'POST',
            url: '{{route('stokopname.showallproduct')}}',
            data: {
                _token  : '{{csrf_token()}}',
                gudang_dari: $('#gudang_dari').val(),
            },
            beforeSend: function(){
                Swal.showLoading();
            },
            success: function(result){
                html   = '';
                for(let i=0; i<result.data.length; i++){

                    html += '<tr>';
                    html += '<td>';
                    html += '<span class="product">' + result.data[i].getproduct.nama + '</span>';
                    html += '<input type="hidden" class="product_value" id="product" name="product[]" value="'+result.data[i].getproduct.id+'">';
                    html += '</td>';
                    html += '<td>';
                    html += '<span class="price">' + result.data[i].gudang_baik + '</span>';
                    html += '<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'+result.data[i].gudang_baik+'">';
                    html += '</td>';
                    html += '<td>';
                    html += '<select class="satuan_select'+i+'" name="satuan[]" style="min-width:100px">';
                    for(let x = 0; x<result.satuan.length; x++){
                        html += '<option value="'+result.satuan[x].id+'">'+result.satuan[x].nama+'</option>';
                    }
                    html += '</select>';
                    html += '</td>';
                    html += '<td width="15%">';
                    html += '<input type="text" class="form-control qty" min=0 id="qty_so'+i+'" name="qty_so[]" value="'+result.data[i].gudang_baik+'">';
                    html += '</td>';
                    html += '<td class="text-right">';
                    html += '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
                    html += '</td>';
                    html += '</tr>';
                    $('#detailData').append(html);
                    html = '';
                    $(".qty").TouchSpin({
                            min :0,
                            max : 1000000,
                            buttondown_class: 'btn btn-white',
                            buttonup_class: 'btn btn-white'
                    });
                    $('#qty_so'+i).on('change', function(){
                        allqty[i] = $(this).val();
                    });
                    $('.satuan_select'+i).select2();
                    $('.satuan_select'+i).on('change', function(){
                        allsatuan[i] = $(this).val();
                    });
                }
                $("input[name='product[]']").each(function(i){
                    allproduct[i] = $(this).val();
                    // allproduct[i]['name'] = $(this).text();
                });
                $(".product").each(function(i){
                    allnamaproduct[i] = $(this).text();
                    // allproduct[i]['name'] = $(this).text();
                });
                $("input[name='stok[]']").each(function(i){
                    allstok[i] = $(this).val();
                });
                $("input[name='satuan[]']").each(function(i){
                    allsatuan[i] = $(this).val();
                });
                $("input[name='qty_so[]']").each(function(i){
                    allqty[i] = $(this).val();
                });
                $(result.satuan).each(function(i){
                    satuan[i] = this;
                });

                //     $.each(result.data, function(key, value) {
                //         $html='';
                //         $html+='<tr>';
                //         $html+='<td>';
                //             $html+='<span class="product">'+ value['namaproduk'] +'</span>';
                //             $html+='<input type="hidden" class="product_value" id="product" name="product[]" value="'+ value['produk_id'] +'">';
                //         $html+='</td>';
                //         $html+='<td>';
                //             $html+='<span class="price">'+ value['qtyproduk'] +'</span>';
                //             $html+='<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'+ value['qtyproduk'] +'">';
                //         $html+='</td>';
                //         $html+='<td>';
                //             $html+='<span class="choose">'+ value['satuan'] +'</span>';
                //             $html+='<input type="hidden" class="satuan_value" min=0 id="satuan" name="satuan[]" value="'+value['satuan']+'">';
                //         $html+='</td>';
                //             $html+='<td width="20%">';
                //             $html+='<input type="text" class="form-control qty" min=-'+ value['qtyproduk'] +' id="qty_so" name="qty_so[]" value="'+ value['qtySO'] +'" '+ value['readonly'] +'>';
                //         $html+='</td>';
                //         if(result.flag_process !='1'){
                //             $html+='<td class="text-right">';
                //             $html+='<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
                //             $html+='</td>';
                //         }
                //         $html+='</tr>';
                //         $('#detailData').append($html);
                    //     $(".qty").TouchSpin({
                    //         min :value['qtyproduk_min'],
                    //         max : 1000000,
                    //         buttondown_class: 'btn btn-white',
                    //         buttonup_class: 'btn btn-white'
                    // });
                // });
                console.log(result);

            },
            complete: function(){
                Swal.hideLoading();
                Swal.close();
            }
        });
    }
    $( "#pilihProduct" ).change(function() {
        $('#table1 tbody > tr').remove();

        // console.log(satuan[0].nama);
        if($(this).val() != null){
            console.log($(this).val());
            var id_product    = $(this).val();
            var gudang        = $('#gudang_dari').val();
            var index = jQuery.inArray(id_product, allproduct);
            console.log(allnamaproduct[index]);
            let html = '';
            html += '<tr>';
            html += '<td>';
            html += '<span class="product">' + allnamaproduct[index] + '</span>';
            html += '<input type="hidden" class="product_value" id="product" name="product[]" value="'+allproduct[index]+'">';
            html += '</td>';
            html += '<td>';
            html += '<span class="price">' + allstok[index] + '</span>';
            html += '<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'+allstok[index]+'">';
            html += '</td>';
            html += '<td>';
            html += '<select class="satuan_select" name="satuan[]" style="min-width:100px">';
            for(let x = 0; x<satuan.length; x++){
                if(satuan[x].id == allsatuan[index]){
                    html += '<option value="'+satuan[x].id+'" selected>'+satuan[x].nama+'</option>';
                }else{
                    html += '<option value="'+satuan[x].id+'">'+satuan[x].nama+'</option>';
                }
            }
            html += '</select>';
            html += '</td>';
            html += '<td width="15%">';
            html += '<input type="text" class="form-control qty" min=0 id="qty_so" name="qty_so[]" value="'+allqty[index]+'">';
            html += '</td>';
            html += '<td class="text-right">';
            html += '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
            html += '</td>';
            html += '</tr>';
            $('#detailData').append(html);
            html = '';
            $(".qty").TouchSpin({
                    min :0,
                    max : 1000000,
                    buttondown_class: 'btn btn-white',
                    buttonup_class: 'btn btn-white'
            });
            $('#qty_so').on('change', function(){
                allqty[index] = $(this).val();
            });
            $('.satuan_select').select2();
            $('.satuan_select').on('change', function(){
                allsatuan[index] = $(this).val();
            });
        }


        // console.log(val[jQuery.inArray(id_product, val)]);
            // if(jQuery.inArray(id_product, val) != -1) {

            //     Swal.fire('Ups','Produk sudah ada di data keranjang','info');
            //     $('#pilihProduct').val('');
            // }else {
            //     $.ajax({
            //         type: 'POST',
            //         url: '{{route('stokopname.tambahproduk')}}',
            //         // dataType: 'json',
            //         data: {
            //             _token        : '{{csrf_token()}}',
            //             id_product    : $(this).val(),
            //             gudang_dari   : gudang,
            //         },
            //         success: function(result){
            //             $('#detailData').append(result.html);
            //             $('.satuan_select'+id_product).select2();
            //             $(".qty").TouchSpin({
            //                 min : 0,
            //                 max : 1000000,
            //                 buttondown_class: 'btn btn-white',
            //                 buttonup_class: 'btn btn-white'
            //             });
            //         }
            //     });
            //     // $('#pilihProduct').val('');
            // }
    });

$('#clearproduct').on('click', function(){
    // $('#pilihProduct').val('');
    $('#table1 tbody > tr').remove();
    $("#pilihProduct").select2("val", "");
    console.log(allnamaproduct);
    let html = '';
    for(let index=0; index<allproduct.length;index++){
        html += '<tr>';
        html += '<td>';
        html += '<span class="product">' + allnamaproduct[index] + '</span>';
        html += '<input type="hidden" class="product_value" id="product" name="product[]" value="'+allproduct[index]+'">';
        html += '</td>';
        html += '<td>';
        html += '<span class="price">' + allstok[index] + '</span>';
        html += '<input type="hidden" class="stok_value" min=0 id="stok" name="stok[]" value="'+allstok[index]+'">';
        html += '</td>';
        html += '<td>';
        html += '<select class="satuan_select'+index+'" name="satuan[]" style="min-width:100px">';
        for(let x = 0; x<satuan.length; x++){
            if(satuan[x].id == allsatuan[index]){
                html += '<option value="'+satuan[x].id+'" selected>'+satuan[x].nama+'</option>';
            }else{
                html += '<option value="'+satuan[x].id+'">'+satuan[x].nama+'</option>';
            }
        }
        html += '</select>';
        html += '</td>';
        html += '<td width="15%">';
        html += '<input type="text" class="form-control qty" min=0 id="qty_so'+index+'" name="qty_so[]" value="'+allqty[index]+'">';
        html += '</td>';
        html += '<td class="text-right">';
        html += '<button class="btn btn-danger remove"><i class="fa fa-trash"></i> </button>';
        html += '</td>';
        html += '</tr>';
        $('#detailData').append(html);
        html = '';
        $(".qty").TouchSpin({
                min :0,
                max : 1000000,
                buttondown_class: 'btn btn-white',
                buttonup_class: 'btn btn-white'
        });
        $('#qty_so'+index).on('change', function(){
            allqty[index] = $(this).val();
        });
        $('.satuan_select'+index).select2();
        $('.satuan_select'+index).on('change', function(){
            allsatuan[i] = $(this).val();
        });
    }
})
</script>
@endpush
