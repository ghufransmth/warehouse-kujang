@extends('layouts.layout')
@section('title', 'Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{ isset($pembelian) ? 'Edit' : 'Tambah' }} Pembelian Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="">Pembelian Produk</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($produk) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <a class="btn btn-white btn-sm" href="pembelian.index">Kembali</a>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
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
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($pembelian)? $enc_id : ''}}">

                        <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label">No Faktur *</label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="nofaktur" name="nofaktur" autocomplete="off"
                                    value="{{isset($pembelian)? $pembelian->no_faktur: ''}}">
                            </div>

                            <label class="col-sm-2 col-form-label">Tanggal Faktur *</label>
                            <div class="col-sm-3 error-text">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control jatuh_tempo" id="faktur_date"
                                        name="faktur_date" placeholder="dd-mm-yyyy"
                                        value="{{isset($pembelian)? $pembelian->tgl_faktur : ''}}" autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label">Nominal Faktur *</label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="nominal" name="nominal" autocomplete="off"
                                    value="{{isset($pembelian)? $pembelian->nominal: ''}}" placeholder="Auto Kalkulasi..." readonly>
                            </div>

                            <label class="col-sm-2 col-form-label">Tanggal Jatuh Tempo *</label>
                            <div class="col-sm-4 error-text">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control jatuh_tempo" id="jatuh_tempo"
                                        name="jatuh_tempo" placeholder="dd-mm-yyyy" autocomplete="off"
                                        value="{{isset($pembelian)? date('d-m-Y',strtotime($pembelian->tgl_jatuh_tempo)) : date('d-m-Y') }}">
                                </div>
                            </div>
                            <input type="hidden" name="total_harga_pembelian" id="total_harga_pembelian" value="0">
                        </div>


                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Keterangan *</label>
                            <div class="col-sm-4 error-text">
                                <textarea type="text" class="form-control" id="ket" name="ket">{{ isset($pembelian)? $pembelian->keterangan: '' }}</textarea>
                            </div>

                            {{-- <div class="form-group row"> --}}
                            {{-- <label class="col-sm-2 col-form-label">Tanggal Transaksi *</label>
                            <div class="col-sm-3 error-text">
                                <div class="input-group date">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control jatuh_tempo" id="tgl_transaksi"
                                        name="tgl_transaksi" placeholder="dd-mm-yyyy" value="{{ isset($pembelian)? date('d-m-Y', strtotime($pembelian->tgl_transaksi)) : date('d-m-Y') }}" autocomplete="off">
                                </div>
                            </div> --}}
                            <label class="col-sm-2 col-form-label">Supplier </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="supplier" name="supplier">
                                    <option value="0">Pilih Supplier</option>
                                    @foreach($supplier as $key => $value)
                                    <option value="{{ $value->id }}"
                                        @if(isset($selectedsupplier))
                                            @if($value->id == $selectedsupplier)
                                                selected
                                            @endif
                                        @endif
                                    >{{ $value->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Gudang </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="gudang" name="gudang">
                                    <option value="0">Pilih Gudang</option>
                                    @foreach($gudang as $key => $value)
                                    <option value="{{ $value->id }}"
                                        @if(isset($selectegudang))
                                            @if($value->id == $selectedgudang)
                                                selected
                                            @endif
                                        @endif
                                    >{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <div class="hr-line-dashed"></div>
                        <div class="col-lg-2">
                            <input type="hidden" id="total_produk" class="mb-1 form-control" value="1">
                            <a id="tambah_detail_product" onclick="tambahProduk()"
                                class="text-white btn btn-success"><span class="fa fa-pencil-square-o"></span>Tambah</a>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="table-responsive">
                            <table class="table display table table-hover p-0 table-striped" style="overflow-x: auto;" id="example">
                                <thead>
                                    <tr class="text-white text-center bg-primary">
                                        <th>Produk</th>
                                        <th>Satuan</th>
                                        <th>Harga Product</th>
                                        <th>Qty Order</th>
                                        <th>Total Harga</th>
                                        <th>#</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_form" class="bg-white">
                                    @if(isset($pembelian))
                                        <input type="hidden" name="jumlahdetail" value="{{ (count($pembelian_detail) > 0)? count($pembelian_detail) : '0' }}" id="jumlahdetail">
                                         @foreach ($pembelian_detail as $key=> $item)
                                            <tr class="bg-white" id='product_{{ $key }}'>
                                                <td>
                                                    <select class="select2_produk_1" id="product_{{ $item->getproduct->id }}" name="produk[]" onchange="hitung(this.options[this.selectedIndex].value, {{ $key }})" width="100%">
                                                        <option value="{{ $item->getproduct->id }}">{{ $item->getproduct->nama }}</option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <select name="tipesatuan[]"
                                                        onchange="satuan(this.options[this.selectedIndex].value,{{ $key }})"
                                                        id="tipe_satuan_{{ $item->getproduct->id_satuan }}" class="select2_satuan_1">
                                                        <option value="1">PCS</option>
                                                    </select>
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control" name="harga_product[]"
                                                        id="harga_product_{{ $item->getproduct->id }}" value="{{ $item->product_price }}" readonly>
                                                </td>

                                                <td width="15%">
                                                    <input type="text" class="form-control touchspin" id="qty_{{ $item->getproduct->id }}" name="qty[]" value="{{ $item->qty }}" onkeyup="hitung_qty({{ $item->getproduct->id }})" onchange="hitung_qty({{ $item->getproduct->id }}, {{ $item->getproduct->id_satuan }})">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control total_harga" id="total_{{ $item->getproduct->id }}"
                                                        name="total[]" value="{{ $item->total }}" readonly>
                                                </td>

                                                <td>
                                                    <a class="text-white btn btn-danger btn-hemisperich btn-xs"
                                                        data-original-title='Hapus Data' id='deleteModal'><i
                                                            class='fa fa-trash'></i></a>
                                                </td>

                                            </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td>
                                                <select name="produk[]" id="product_1" class="select2_produk_1"
                                                    onchange="hitung(this.options[this.selectedIndex].value,1)"
                                                    width="100%">
                                                    <option value="">Pilih Produk</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="tipesatuan[]"
                                                    onchange="satuan(this.options[this.selectedIndex].value,1)"
                                                    id="tipe_satuan_1" class="select2_satuan_1">
                                                    <option value="null">Pilih Tipe Satuan</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="harga_product[]"
                                                    id="harga_product_1" value="PCS" readonly>
                                            </td>
                                            <td width="15%">
                                                <input type="text" class="form-control touchspin" id="qty_1" name="qty[]"
                                                    value="1" onkeyup="hitung_qty(1)" onchange="hitung_qty(1)">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control total_harga" id="total_1"
                                                    name="total[]" readonly>
                                            </td>
                                            <td>
                                                <a href="#"  onclick='deleteProduk({{ $key }})' class="text-white btn btn-danger btn-hemisperich btn-xs"
                                                    data-original-title='Hapus Data' id='deleteModal'><i
                                                        class='fa fa-trash'></i></a>
                                            </td>
                                        </tr>
                                    @endif

                                </tbody>
                            </table>
                        </div>
                        {{-- <input type="hidden" class="form-control mb-1" name="total_produk" id="total_produk" value="1"> --}}
                        <div class="hr-line-dashed"></div>
                        <table style="min-width: 100%">
                            <tr>
                                <td class="text-right">
                                    <h3><b>Total Harga Pembelian<b></h3>
                                </td>
                                <td width="1%"></td>
                                <td class="text-center" width="13%" id="harga_pembelian"><b> {{ isset($pembelian)? $pembelian->nominal : '' }}</b></td>
                                <td width="5%"></td>
                            </tr>
                        </table>
                        <!--<div class="form-group row">
                            <div class="col-sm-4 col-sm-offset-2 float-right">
                                <a class="btn btn-white btn-sm" href="{{route('pembelian.index')}}">Batal</a>
                                <button class="btn btn-primary btn-sm" type="submit" id="simpan">Simpan</button>
                                {{-- <button class="btn btn-success btn-sm" type="submit" id="simpanselesai">Selesai & Simpan</button> --}}
                            </div>
                        </div>-->

                        <div class="form-group row">
                            <div class="col-sm-6 col-sm-offset-2">
                                <a class="btn btn-white btn-sm" href="{{route('pembelian.index')}}">Batal</a>
                                <button class="btn btn-primary btn-sm" type="button" id="simpan">Selesai</button>
                            </div>
                            @can('draftpurchaseorder.tambah')
                            <div class="col-sm-6 text-right">

                                {{-- <button class="btn btn-info btn-sm" type="button" id="draft">Simpan Draft</button> --}}
                            </div>
                            @endcan
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
    $(document).ready(function(){
        $(".select2").select2({allowClear: true});
        @if(isset($pembelian))
            var jumlahdetail = $('#jumlahdetail').val();
            for(var i=0;i<jumlahdetail;i++){
                select_satuan(i);
                select_product(i);
            }
            total_pembelian();
        @endif
select_satuan(1);
select_product(1);
$("#simpan").on('click',function(){
        if($("#submitData").valid())
        {
            Swal.showLoading();
            SimpanData(1);
        }
    });
    $("#draft").on('click',function(){
        if($("#submitData").valid())
        {
            Swal.showLoading();
            SimpanData(1);
        }
    });
    $('#submitData').validate({
        rules: {
            member:{
                    required: true
                },
                sales:{
                    required: true
                },
                expedisi:{
                    required: true
                },
                catatan:{
                    required: true
                }
            },
            messages:{
                member:{
                    required: "Member tidak boleh kosong"
                },
                sales:{
                    required: "Sales tidak boleh kosong"
                },
                expedisi:{
                    required: "Expedisi tidak boleh kosong"
                },
                catatan:{
                    required: "Jika catatan kosong silahkan isi dengan tanda ( - )"
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
            function SimpanData(draft){
                $('#simpan').addClass("disabled");
                var form = $('#submitData').serializeArray();
                var dataFile = new FormData();
                var total_produk = $('#total_produk').val();

                $.each(form, function(idx,val){
                    dataFile.append(val.name, val.value);
                    dataFile.append('total_produk', total_produk);
                    dataFile.append('draft', draft);
                });
                $.ajax({
                    type: 'POST',
                    url: "{{ route('pembelian.simpan') }}",
                    headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                    data: dataFile,
                    processData: false,
                    contentType: false,
                    dataType: "json",
                    beforeSend: function(){
                        Swal.showLoading();
                    },
                    success: function(data){
                    if(data.success){
                        Swal.fire('Yes',data.message,'info');
                        if(data.draft=='0'){
                            window.location.replace('{{ route("pembelian.index") }}');
                        }else{
                            window.location.replace('{{ route("pembelian.tambah") }}');
                        }
                    }else{
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
                    Swal.fire('Maaf','silahkan check kembali form anda' ,'info');
                }
                });
            }
            $('.formatTgl').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            calendarWeeks: true,
            autoclose: true,
            format: "dd-mm-yyyy"
            });
    });
    function total_pembelian(){
        var sum = 0;
        var tes = $('.total_harga');
        // console.log(tes);
        $('.total_harga').each(function(){
            sum += parseFloat($(this).val());  // Or this.innerHTML, this.innerText
        });
        $('#harga_pembelian').text(sum);
        $('#total_harga_pembelian').val(sum);
        $('#nominal').val(sum);
    }
    function select_product(num){
    $('.select2_produk_'+num).select2({allowClear: false, width: '200px',
        ajax: {
                url: '{{ route("purchaseorder.search") }}',
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
                        text : item.kode_product+' - '+item.nama,
                    });
                });
                return{
                    results: results
                };
            }
        }
    });
}
function select_satuan(num){
    $('.select2_satuan_'+num).select2({allowClear: false, width: '200px',
        ajax: {
                url: '{{ route("pembelian.search_satuan") }}',
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
                        text : item.nama+' - '+item.qty+' PCS',
                    });
                });
                return{
                    results: results
                };
            }
        }
    });
}

$('.jatuh_tempo').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
<script>
    $( "#sales" ).change(function() {
        var member = $('#member').val();
        var val = [];
        if(member==''){
            $('#sales').val('');
            Swal.fire('Ups','Silahkan Pilih Customer terlebih dahulu','info');
            return false;
        }
    });
    $( "#member").change(function() {
        var member = $('#member').val();
        $('#total_produk').val(0);
        //cek member belum bayar invoice selama 4 bulan
        $.ajax({
            type: 'POST',
            url: '{{route("purchaseorder.cekstatusinvoice")}}',
            data: {
                'member_id': member,
            },
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            success: function(response) {
                console.log(response)
                if(response.info==1){
                    $('#showAlert').show();
                    toastr.error('MEMBER INI BELUM MELAKUKAN PEMBAYARAN PADA INVOICE','Ups');
                }else{
                    $('#showAlert').hide();
                }
            }
        });
        $('#detail_form').html('');
    });
    function tambahProduk(){
        @if(isset($pembelian))
        // var total_produk = $('#total_produk').val();
        var total_produk = $('#jumlahdetail').val();
        var total = 1 + parseInt(total_produk);
        $('#total_produk').val(total);
        $('#jumlahdetail').val(total);
        console.log(total)
        @else
        var total_produk = $('#total_produk').val();
        var total = 1 + parseInt(total_produk);
        $('#total_produk').val(total);
        console.log(total)
        @endif

        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{route("pembelian.tambah_detail")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                $('#detail_form').prepend(response);
            }
        });
    }
    function tambahProduk_(){
        var member = $('#member').val();
        var sales = $('#sales').val();
        if(member==''){
            Swal.fire('Ups','Silahkan Pilih Customer terlebih dahulu','info');
            return false;
        }else if(sales == ''){
            Swal.fire('Ups','Silahkan Pilih Sales terlebih dahulu','info');
            return false;
        }else{
            var total_produk = $('#total_produk').val();
            var total = 1 + parseInt(total_produk);
            $('#total_produk').val(total);
            console.log(total)
            $.ajax({
                type: 'POST',
                data: 'total='+total,
                url: '{{route("pembelian.tambah_detail")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)
                  $('#detail_form').prepend(response);
                }
            });
        }
    }
    function hitung(value, num){
        console.log('tes');
        $.ajax({
            type: 'POST',
            data: {
                'produk_id': value,
                'urut' : num
            },
            url: '{{route("pembelian.harga_product")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                if(response.success){
                    $('#harga_product_'+num).val(response.data.harga_jual);
                    $('#stock_product_'+num).val(response.data.getstock.stock_pembelian);
                }else{
                    Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
                }
            }
        });
    }
    function satuan(value, num){
        if($('#harga_product_'+num).val() == ""){
            Swal.fire('Ups', 'Pilih product terlebih dahulu', 'info');
            return false;
        }
        $.ajax({
            type: 'POST',
            data: {
                'satuan_id': value,
                'urut' : num
            },
            url: '{{route("pembelian.total_harga")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                if(response.success){
                    var total_qty = response.data.qty * $('#qty_'+num).val();
                    // console.log($('#stock_product_'+num).val() );
                    if($('#stock_product_'+num).val() < total_qty){
                        Swal.fire('Ups','Stock product tidak cukup', 'info');
                        return false;
                    }
                    var total = $('#harga_product_'+num).val() * total_qty;
                    $('#total_'+num).val(total);
                    total_pembelian();


                }else{
                    Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
                }
            }
        });
        $('total_'+num).val('')
    }
    function hitung_(value, num){
        $('#hargasatuan_'+num).val('')
        $('#total_'+num).val('')
        $('#tipeharga_'+num).val('')
        var member = $('#member').val();
        var sales = $('#sales').val();
        if(member==''){
            $('#member').val('')
            $('#produk_'+num).val('');
            Swal.fire('Ups','Silahkan Pilih Customer terlebih dahulu','info');
            return false;
        }else if(sales == ''){
            $('#produk_'+num).val('');
            Swal.fire('Ups','Silahkan Pilih Sales terlebih dahulu','info');
            return false;
        }else{
            $('#hargasatuan_'+num).val()
            $('#total_'+num).val()
            $.ajax({
                type: 'POST',
                data: {
                    'produk_id': value,
                    'urut' : num
                },
                url: '{{route("purchaseorder.harga_product")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)
                }
            });
        }
    }
    function harga(value, num){
        var member = $('#member').val();

        var sales = $('#sales').val();
        var produk = $('#produk_'+num).val();
        if(member==''){
            $('#member').val('')
            Swal.fire('Ups','Silahkan Pilih Customer terlebih dahulu','info');
            return false;
        }else if(sales == ''){
            $('#produk_'+num).val('');
            Swal.fire('Ups','Silahkan Pilih Sales terlebih dahulu','info');
            return false;
        }else if(produk == ''){
            $('#tipeharga_'+num).val('');
            Swal.fire('Ups','Silahkan Pilih Produk terlebih dahulu','info');
            return false;
        }else{
            $('#hargasatuan_'+num).val()
            $('#total_'+num).val()
            $.ajax({
                type: 'POST',
                data: {
                    'produk_id': produk,
                    'urut' : num,
                    'member' : member,
                },
                url: '{{route("purchaseorder.harga_product")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)
                    let data = response.data
                    let persen = response.persen
                    if(value == 1){
                        var qty = $('#qty_'+num).val()
                        var hargafix = Number(data.normal_price) + Number(Math.round(persen/100 * data.normal_price));
                        $('#hargasatuan_'+num).val(formatRupiah(hargafix, ''))
                        $('#total_'+num).val(formatRupiah(hargafix*qty, ''))
                    }else if(value == 2){
                        var qty = $('#qty_'+num).val()
                        var hargafix = Number(data.export_price) + Number(Math.round(persen/100 * data.export_price));
                        $('#hargasatuan_'+num).val(formatRupiah(hargafix, ''))
                        $('#total_'+num).val(formatRupiah(hargafix*qty, ''))
                    }
                }
            });
        }
    }

    function hitung_qty(num, numSatuan){
        console.log(num);
        console.log($('#product_'+num).val());
        // console.log($('#product_'+num+' option:selected').val());
        if($('#product_'+num).val() == 0){
            Swal.fire('Ups', 'Pilih product terlebih dahulu');
            return false;
        }else if($('#tipe_satuan_'+numSatuan).val() == "null"){
            Swal.fire('Ups', 'Pilih satuan terlebih dahulu');
            return false;
        }else{
            $.ajax({
                type: 'POST',
                data: {
                    'satuan_id': $('#tipe_satuan_'+numSatuan).val(),
                    'urut' : num
                },
                url: '{{route("purchaseorder.total_harga")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)

                    if(response.success){
                        var total_qty = response.data.qty * $('#qty_'+num).val();
                        // console.log(total_qty);
                        if($('#stock_product_'+num).val() < total_qty){
                            Swal.fire('Ups', 'Stock product tidak cukup', 'info');
                            return false;
                        }
                        var total = $('#harga_product_'+num).val() * total_qty;
                        $('#total_'+num).val(total);
                        // console.log(total);
                        total_pembelian();
                    }else{
                        Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
                    }
                }
            });
        }
    }

    $(".touchspin").TouchSpin({
        min: 1,
        max: 9999999999999999999999,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

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
                $('#product_'+id).remove();
                var total_produk = $('#total_produk').val();
                // console.log(total_produk)
                var total = parseInt(total_produk) - 1;
                $('#total_produk').val(total);
                // console.log(total)
                total_pembelian();
                Swal.fire(
                  'Pesan',
                  'Produk berhasil dihapus.',
                  'success'
                )
            }
        })
    }

    function formatRupiah(angka, prefix){
        var number_string = angka.toString(),
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
@endpush
