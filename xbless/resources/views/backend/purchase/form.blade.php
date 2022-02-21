@extends('layouts.layout')
@section('title', 'Manajemen Penjualan ')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($penjualan) ? 'Edit' : 'Tambah'}} Penjualan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('purchaseorder.index')}}">Penjualan</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($penjualan) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <a class="btn btn-white btn-sm" href="{{route('purchaseorder.index')}}">Batal</a>
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
                    <div class="alert alert-danger" id="showAlert" style="display: none">
                        MEMBER INI BELUM MELAKUKAN PEMBAYARAN PADA INVOICE
                    </div>
                    <form id="submitData">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($penjualan)? $enc_id : ''}}">

                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">No Transaksi </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" name="no_transaksi" value="{{ isset($penjualan)? $penjualan->no_faktur : '' }}" class="form-control" id="no_transaksi">
                            </div>
                            <label class="col-sm-2 col-form-label">Tgl Transaksi </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_transaksi"
                                    value="{{ isset($penjualan)? date('d-m-Y', strtotime($penjualan->tgl_faktur)) : date('d-m-Y') }}" name="tgl_transaksi" autocomplete="off">

                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Toko </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="toko" name="toko">
                                    <option value="0">Pilih Toko</option>
                                    @foreach($toko as $key => $value)
                                    <option value="{{ $value->id }}"
                                    @if(isset($selectedtoko))
                                        @if($value->id == $selectedtoko)
                                            selected
                                        @endif
                                    @endif
                                    >{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">Sales </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="sales" name="sales">
                                    <option value="0">Pilih Sales</option>
                                    @foreach($sales as $key => $value)
                                    <option value="{{ $value->id }}"
                                        @if(isset($selectedsales))
                                            @if($value->id == $selectedsales)
                                                selected
                                            @endif
                                        @endif
                                    >{{ $value->nama }}</option>
                                    @endforeach



                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Status Pembayaran </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="status_pembayaran" name="status_pembayaran">
                                    <option value="">Pilih Status Pembayaran</option>
                                    <option value="1"
                                        @if(isset($selectedstatuslunas))
                                            @if($selectedstatuslunas == '1')
                                                selected
                                            @endif
                                        @endif
                                    >Lunas</option>
                                    <option value="0"
                                    @if(isset($selectedstatuslunas))
                                        @if($selectedstatuslunas == 0)
                                            selected
                                        @endif
                                    @endif
                                    >Belum Lunas</option>
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">Tgl Jatuh Tempo </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" name="tgl_jatuh_tempo" class="form-control formatTgl"
                                    id="tgl_jatuh_tempo" value="{{ isset($penjualan)? date('d-m-Y', strtotime($penjualan->tgl_jatuh_tempo)) : date('d-m-Y') }}" autocomplete="off">
                                <input type="hidden" name="total_harga_penjualan" id="total_harga_penjualan" value="0">
                                <input type="hidden" name="total_diskon" id="diskon_penjualan" value="0">
                                <input type="hidden" name="jumlah_penjualan" id="jumlah_penjualan" value="0">
                                <input type="hidden" name="nilai_diskon" id="nilai_diskon" value="0">
                            </div>

                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label"><a href="#!" onclick="tambahProduk()"
                                class="btn btn-success btn-sm icon-btn sm-btn-flat product-tooltip" title="Tambah Produk">Tambah
                                Produk</a></label>
                            <div class="col-sm-4 error-text">
                                {{-- <select class="form-control select2" id="status_pembayaran" name="status_pembayaran">
                                    <option value="">Pilih Status Pembayaran</option>
                                    <option value="1"
                                        @if(isset($selectedstatuslunas))
                                            @if($selectedstatuslunas == '1')
                                                selected
                                            @endif
                                        @endif
                                    >Lunas</option>
                                    <option value="0"
                                    @if(isset($selectedstatuslunas))
                                        @if($selectedstatuslunas == 0)
                                            selected
                                        @endif
                                    @endif
                                    >Belum Lunas</option>
                                </select> --}}
                            </div>
                            <label class="col-sm-2 col-form-label">Jenis Pembayaran</label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="jenis_pembayaran" name="jenis_pembayaran">
                                    <option value="">Pilih Jenis Pembayaran</option>
                                    <option value="1"
                                        @if(isset($selectedjenispembayaran))
                                            @if($selectedjenispembayaran == '1')
                                                selected
                                            @endif
                                        @endif
                                    >Cash</option>
                                    <option value="2"
                                    @if(isset($selectedjenispembayaran))
                                        @if($selectedjenispembayaran == '2')
                                            selected
                                        @endif
                                    @endif
                                    >Cek/giro</option>
                                    <option value="3"
                                    @if(isset($selectedjenispembayaran))
                                        @if($selectedjenispembayaran == 3)
                                            selected
                                        @endif
                                    @endif
                                    >Transfer</option>
                                </select>
                            </div>

                        </div>

                        {{-- <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Catatan </label>
                                <div class="col-sm-10 error-text">
                                <textarea class="form-control" id="note" name="note">{{isset($purchaseorder)? $purchaseorder->note : ''}}</textarea>
                </div>
            </div> --}}
            <div class="">
                {{-- <a href="#!" onclick="tambahProduk()"
                    class="btn btn-success btn-sm icon-btn sm-btn-flat product-tooltip" title="Tambah Produk">Tambah
                    Produk</a> --}}
            </div>
            <div class="hr-line-dashed"></div>
            <div class="table-responsive">
                <table id="table1" class="table display table table-hover p-0 table-striped" style="overflow-x: auto;">
                    <thead>
                        <tr class="text-white text-center bg-primary">
                            <th>Produk</th>
                            <th>Stock Product (PCS)</th>
                            <th>Harga Product</th>
                            <th>Tipe Satuan</th>
                            <th>Qty Order</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ajax_produk" class="bg-white">
                        @if(isset($penjualan))
                        <input type="hidden" name="jumlahdetail" value="{{ (count($detail_penjualan) > 0)? count($detail_penjualan) : '0'  }}" id="jumlahdetail">
                            @foreach($detail_penjualan as $key => $detail)
                            <tr class="bg-white" id='dataajaxproduk_{{ $key }}'>
                                <td>
                                    <select class="select2_produk_{{ $key }}" id="product_{{ $key }}" name="produk[]"
                                        onchange="hitung(this.options[this.selectedIndex].value, {{ $key }})" width="100%">
                                        <option value="{{ $detail->getproduct->id }}">{{ $detail->getproduct->nama }}</option>

                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="stock_product[]" value="{{ $detail->getproduct->getstock->stock_penjualan }}" id="stock_product_{{ $key }}"
                                        readonly>
                                </td>
                                <td>
                                    <input type="text" class="form-control" id="harga_product_{{ $key }}" name="harga_product[]"
                                        value="{{$detail->harga_product}}" readonly>
                                </td>
                                <td>
                                    <select class="select2_satuan_{{ $key }}" id="tipe_satuan_{{ $key }}" name="tipesatuan[]"
                                        onchange="satuan(this.options[this.selectedIndex].value, {{ $key }})">
                                        <option value="1">PCS </option>
                                    </select>
                                </td>
                                <td width="15%">
                                    <input type="text" class="form-control touchspin" id="qty_{{ $key }}" name="qty[]" value="{{ $detail->qty }}"
                                        onkeyup="hitung_qty({{ $key }})" onchange="hitung_qty({{ $key }})">
                                </td>
                                <td>
                                    <input type="text" class="form-control total_harga" id="total_{{ $key }}" name="total[]" value="{{ $detail->total_harga }}"
                                        readonly>
                                </td>
                                {{-- <td>
                                    -
                                </td> --}}
                                <td><a href='#!' onclick='deleteProduk({{ $key }})' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
                            </tr>
                            @endforeach
                        @else
                        <tr class="bg-white">
                            <td>
                                <select class="select2_produk_1" id="product_1" name="produk[]"
                                    onchange="hitung(this.options[this.selectedIndex].value, 1)" width="100%">
                                    <option value="0">Pilih Produk </option>

                                </select>
                            </td>
                            <td>
                                <input type="text" class="form-control" name="stock_product[]" id="stock_product_1"
                                    readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="harga_product_1" name="harga_product[]"
                                    value=" PCS" readonly>
                            </td>
                            <td>
                                <select class="select2_satuan_1" id="tipe_satuan_1" name="tipesatuan[]"
                                    onchange="satuan(this.options[this.selectedIndex].value, 1)">
                                    <option value="null">Pilih Tipe Satuan </option>
                                </select>
                            </td>
                            <td width="15%">
                                <input type="text" class="form-control touchspin" id="qty_1" name="qty[]" value="1"
                                    onkeyup="hitung_qty(1)" onchange="hitung_qty(1)">
                            </td>
                            <td>
                                <input type="text" class="form-control total_harga" id="total_1" name="total[]"
                                    readonly>
                            </td>
                            {{-- <input type="hidden" name="" class="total_diskon" name="diskon[]" value=""> --}}
                            <td>
                                -
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <input type="hidden" class="form-control mb-1" name="total_produk" id="total_produk" value="{{ isset($penjualan)? count($detail_penjualan) : '1' }}">
            <div class="hr-line-dashed"></div>
            <table style="min-width: 100%">
                <tr>
                    <td class="text-right">Total Harga</td>
                    <td width="1%" class="text-right">&nbsp;&nbsp;Rp.</td>
                    <td class="text-center" width="13%" id="harga_penjualan"> {{ isset($penjualan)? $penjualan->total_harga : '' }}</td>
                    <td width="5%"></td>
                </tr>
                <tr>
                    <td class="text-right">Diskon</td>
                    <td width="1%">&nbsp;&nbsp;Rp.</td>
                    <td class="text-center" width="13%" id="total_diskon"> {{ isset($penjualan)? $penjualan->total_harga : '' }}</td>
                    <td width="5%"></td>
                </tr>
                <tr>
                    <td class="text-right">Jumlah</td>
                    <td width="1%">&nbsp;&nbsp;Rp.</td>
                    <td class="text-center" width="13%" id="jumlah_total"> {{ isset($penjualan)? $penjualan->total_harga : '' }}</td>
                    <td width="5%"></td>
                </tr>
            </table>
            <div class="form-group row">
                <div class="col-sm-6 col-sm-offset-2">
                    <a class="btn btn-white btn-sm" href="{{route('provinsi.index')}}">Batal</a>
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
    $(document).ready(function () {
        $(".select2").select2({allowClear: true});
        @if(isset($penjualan))
            var jumlahdetail = $('#jumlahdetail').val();
            // console.log(jumlahdetail);
            for(var i=0;i<jumlahdetail;i++){
                select_satuan(i);
                select_product(i);
            }
            total_penjualan();
        @endif
        select_satuan(1);
        select_product(1);
        $("#simpan").on('click',function(){
            if($("#submitData").valid())
            {
                Swal.showLoading();
                SimpanData(0);
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
            messages: {
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
            // submitHandler: function(form) {
            //     Swal.showLoading();
            //     SimpanData();
            // }
        });
        function SimpanData(draft){

            $('#simpan').addClass("disabled");
                var form = $('#submitData').serializeArray()
                var dataFile = new FormData()
                var total_produk = $('#total_produk').val();


            $.each(form, function(idx, val) {
                dataFile.append(val.name, val.value)
                dataFile.append('total_produk', total_produk);
                dataFile.append('draft', draft);
            })
            $.ajax({
                type: 'POST',
                url : "{{route('purchaseorder.simpan')}}",
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
                        if(data.draft=='0'){
                            window.location.replace('{{route("requestpurchaseorder.index")}}');
                        }else{
                            //ke draft
                            window.location.replace('{{route("purchaseorder.index")}}');
                        }

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
function formatRupiah(angka, prefix){
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
    split   		= number_string.split(','),
    sisa     		= split[0].length % 3,
    rupiah     		= split[0].substr(0, sisa),
    ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if(ribuan){
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
}
function total_penjualan(){
    var sum = 0;
    var tes = $('.total_harga');
    // console.log(tes);
    $('.total_harga').each(function(){
        sum += parseFloat($(this).val());  // Or this.innerHTML, this.innerText
    });
    $('#harga_penjualan').text(formatRupiah(sum));
    $('#total_harga_penjualan').val(sum);

    $.ajax({
        type: 'POST',
        data: {
            'harga_penjualan': $('#total_harga_penjualan').val(),
        },
        url: '{{route("purchaseorder.total_diskon")}}',
        headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
        success: function(response) {
            console.log(response)
            if(response.success == true){
                $('#diskon_penjualan').val(response.total_diskon);
                $('#total_diskon').text(formatRupiah(response.total_diskon));
                $('#jumlah_penjualan').val(response.jumlah_total);
                $('#jumlah_total').text(formatRupiah(response.jumlah_total));
                $('#nilai_diskon').val(response.nilai_diskon);
            }else{
                Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
            }
        }
    });
}
function select_product(num){
    console.log(num);
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
                url: '{{ route("purchaseorder.search_satuan") }}',
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
        $('#ajax_produk').html('');
    });
    function tambahProduk(){

        @if(isset($penjualan))
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
            url: '{{route("purchaseorder.addproduk")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                $('#ajax_produk').prepend(response);
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
                url: '{{route("purchaseorder.addproduk")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)
                  $('#ajax_produk').prepend(response);
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
            url: '{{route("purchaseorder.harga_product")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                if(response.success){
                    $('#harga_product_'+num).val(response.data.harga_jual);
                    $('#stock_product_'+num).val(response.data.getstock.stock_penjualan);
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
                'satuan_id': $('#tipe_satuan_'+num).val(),
                'urut' : num
            },
            url: '{{route("purchaseorder.total_harga")}}',
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
                    total_penjualan();


                }else{
                    Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
                }
            }
        });
        $('total_'+num).val('')
    }
    // To Be Continue
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

    function hitung_qty(num){
        // console.log($('#product_'+num+' option:selected').val());
        // console.log($('#harga_product_'+num).val());
        if($('#product_'+num).val() == 0){
            Swal.fire('Ups', 'Pilih product terlebih dahulu');
            return false;
        }else if($('#tipe_satuan_'+num).val() == "null"){
            Swal.fire('Ups', 'Pilih satuan terlebih dahulu');
            return false;
        }else{
            $.ajax({
                type: 'POST',
                data: {
                    'satuan_id': $('#tipe_satuan_'+num).val(),
                    'qty': $('#qty_'+num).val(),
                    'harga_product': $('#harga_product_'+num).val(),
                    'harga_penjualan': $('#total_harga_penjualan').val(),
                    'urut' : num
                },
                url: '{{route("purchaseorder.total_harga")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    console.log(response)
                    if(response.success){
                        var total_qty = response.data.qty * $('#qty_'+num).val();
                        if($('#stock_product_'+num).val() < total_qty){
                            Swal.fire('Ups', 'Stock product tidak cukup', 'info');
                            return false;
                        }
                        var total = $('#harga_product_'+num).val() * total_qty;
                        $('#total_'+num).val(total);
                        total_penjualan();
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
                $('#dataajaxproduk_'+id).remove();
                var total_produk = $('#total_produk').val();
                // console.log(total_produk)
                var total = parseInt(total_produk) - 1;
                $('#total_produk').val(total);
                // console.log(total)
                total_penjualan();
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
