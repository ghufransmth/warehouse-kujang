@extends('layouts.layout')

@section('title', 'Detail Back Order ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Back Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('backorder.index')}}">Back Order</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('backorder.index')}}">Kembali</a>
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
                    <form id="submitData">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($purchase)? $enc_id : ''}}">
                        <div class="ibox-content">

                            <div class="row">
                                <div class="col-md-6">
                                    <h6>
                                        Permintaan Pemesanan Pembelian Kepada :
                                    </h6>
                                    <span>{{ $member->name }} - {{ $member->city }}</span>
                                </div>
                            </div>

                            <div class="hr-line-dashed"></div>

                            <div class="table-responsive">
                                <table id="table1" class="table" >
                                    <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Diskon (%)</th>
                                        <th>Qty Order</th>
                                        <th>Unit Sebelum Diskon</th>
                                        <th>Harga Total Sebelum Diskon</th>
                                        <th>Unit Setelah Diskon</th>
                                        <th>Harga Setelah Diskon</th>
                                        <th>Perusahaan</th>
                                        <th>Gudang</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <form id="sumbitData">
                                        @foreach($purchasedetail as $key => $value)
                                            <tr>
                                                <td><span><b>{{ $value->product_name }}</b></span><br/><p style="color:#1c84c6">{{ $value->productcode }}</p></td>
                                                <td width="6%">
                                                <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
                                                    <input class="form-control" id="diskon_produk_{{ $value->id }}" name="diskon_produk_{{ $value->id }}" type="text" value="{{ isset($value)? $value->discount: '' }}" onchange="price_diskon({{ $value->id }})" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                                </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input class="form-control touchspin2" id="qty_produk_{{ $value->id }}" name="qty_produk_{{ $value->id }}" type="text" value="{{ isset($value)? $value->qty: '' }}" onkeyup="price_diskon({{ $value->id }})" onchange="price_diskon({{ $value->id }})" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
                                                    </div>
                                                     <center>
                                                    <span valign="" class="text-center" style="margin-top: 8px;"><b>{{ucfirst($value->satuan) }}</b></span>
                                                    </center>
                                                </td>
                                                <td>
                                                    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
                                                        <input class="form-control" id="price_produk_{{ $value->id }}" name="price_produk_{{ $value->id }}" type="text" value="{{ $value->price }}" onkeyup="price_diskon({{ $value->id }})" size="10" onchange="price_diskon({{ $value->id }})">
                                                    </div>
                                                </td>
                                                <td><span id="total_sebelum_{{$value->id}}">{{ number_format($value->totalsebelum, 0, '','.') }}</span></td>
                                                <td><span id="unit_sesudah_{{$value->id}}">{{ number_format($value->unitsesudah, 0, '', '.') }}</span>
                                                <input name="sesudah_unit_{{$value->id}}" type="hidden" value="{{ $value->unitsesudah }}"></td>
                                                <td><span id="total_sesudah_{{$value->id}}">{{number_format($value->totalsesudah, 0, '','.')}}</span>
                                                <input type="hidden" id="sesudah_total_{{$value->id}}" name="sesudah_total_{{$value->id}}" value="{{$value->totalsesudah}}">
                                                <input type="hidden" id="total_{{$value->id}}" name="total[]" value="{{$value->totalsesudah}}"></td>
                                                <td width="18%">
                                                    <div class="col-sm-12 error-text">
                                                        <select name="perusahaan_asal_{{$value->id}}" class="form-control" id="perusahaan_asal_{{$value->id}}" onchange="gudang_perusahaan(this.options[this.selectedIndex].value, {{ $value->id }}, {{ $value->product_id }})" width="100%">
                                                        <option value="">Pilih Perusahaan</option>
                                                            @foreach($perusahaan as $key => $row)
                                                            <option value="{{$row->id}}" >{{ucfirst($row->name)}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </td>
                                                <td width="18%">
                                                    <div class="col-sm-10 error-text">
                                                        <select name="gudang_{{$value->id}}" class="form-control select2" id="gudang_{{$value->id}}" onchange="cek_stock(this.options[this.selectedIndex].value, {{ $value->id }})" width="100%">
                                                        <option value="">Pilih Gudang</option>

                                                        </select>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </form>
                                    </tbody>
                                </table>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="row">
                                <div class="col-sm-7 content-group">
                                    <h5 class="text-semibold">Informasi Tambahan</h5>
                                    <span>{{ $purchase->note }}</span>
                                </div>
                                <div class="col-sm-5">
                                    <span>Total Due</span>
                                    <div class="table-responsive no-border">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <th>Subtotal : </th>
                                                    <td id="subTotal" class="text-right">{{ number_format($totalprice, 0, '', '.') }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Total : </th>
                                                    <td id="total" class="text-right">{{ number_format($totalprice, 0, '', '.') }}</td>
                                                    <input type="hidden" id="totalinput" name="totalinput" value="{{$totalprice}}">
                                                </tr>
                                                <tr>
                                                    <th> </th>
                                                    <td><button class="btn btn-primary btn-sm" type="submit" id="simpan">Simpan</button></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.js" integrity="sha512-w5udtBztYTK9p9QHQR8R1aq8ke+YVrYoGltOdw9aDt6HvtwqHOdUHluU67lZWv0SddTHReTydoq9Mn+X/bRBcQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function (){
        @foreach($purchasedetail as $key => $value)
        var autoNumericInstance = new AutoNumeric('#price_produk_'+{{$value->id}}+'', {
                currencySymbol : '',
                decimalCharacter : ',',
                digitGroupSeparator : '.',
                decimalPlaces:'0',
            });
        @endforeach
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
                // Swal.showLoading();
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
                url : "{{route('backorder.process')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:dataFile,
                processData: false,
                contentType: false,
                dataType: "json",
                beforeSend: function () {
                   Swal.showLoading();
                },
                success: function(data){
                    console.log(data);
                    let msg = data.msg
                    let backorder = data.backorder
                    let splitdata = data.splitdata
                    let msgAlasan = data.msgAlasan
                    let msgAlternate = data.msgalternate


                    if(data.success && backorder){
                        Swal.fire('Yes',`${msg} ${msgAlasan}`,'info')
                        window.location.replace('{{route("backorder.index")}}');
                    }else if(data.success && splitdata){
                        Swal.fire('Yes',`${msg} ${msgAlasan} ${msgAlternate}`,'info')
                        window.location.replace('{{route("purchaseorder.index")}}');
                    }else if(data.success && data.clear){
                        Swal.fire('Yes',`${msg}`,'info')
                        window.location.replace('{{route("purchaseorder.index")}}');
                    }else{
                        Swal.fire('Ups',`${msg}`,'info')
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
            })
        }
    })
</script>

<script>
    function gudang_perusahaan(id_perusahaan, id_gudang, id_product){
        $.ajax({
                type: 'POST',
                url : "{{route('requestpurchaseorder.perusahaan')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                dataType: "json",
                data:{
                    'id_perusahaan': id_perusahaan,
                    'id_product'   : id_product,
                },
                success: function(response){
                    console.log(response)
                    let data
                    var gudang = response.data
                    data = `<option value="" >Pilih Gudang</option>`
                    for (let index = 0; index < gudang.length; index++) {
                        if(gudang != null){
                            data += `<option value="${gudang[index].gudang_id}" data-id="${gudang[index].qty}">${gudang[index].gudang_name} ( ${gudang[index].qty} ) </option>`
                        }
                    }
                    $(`#gudang_${id_gudang}`).html(data)
                },
            });
    }

    function cek_stock(id_gudang, number){
        let gudang = $('#gudang_'+number).find(':selected').data('id')
        let qty = $('#qty_produk_'+number).val()

        if(qty > gudang){
            $('#gudang_'+number).val('');
            Swal.fire('Ups','jumlah order produk melebihi stock, order produk akah dialihkan ke backoder','info');
            console.log('lebih')
        }
        console.log(qty)
        console.log(gudang)
    }

    function price_diskon(number){
        let gudang = $('#gudang_'+number).find(':selected').data('id')
        let diskoncheck = $('#diskon_produk_'+number).val()
        let diskon = 0
        if(diskoncheck > 100){
            diskon = 100
        }else if(diskoncheck < 0){
            diskon = 0
        }else{
            diskon = diskoncheck
        }
        let format = new Intl.NumberFormat('en-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 2
            })
        let qty = $('#qty_produk_'+number).val()
        let price = $('#price_produk_'+number).val().replace(/\./g, "");

        let total_harga =  Math.round((diskon/100)*(price*qty))
        let harga = price*diskon/100;
        let total_awal = price*qty

        let hasil_unit = Math.round(price-harga)
        let hasil_total = Math.round(hasil_unit*qty)
        $('#total_sebelum_'+number).html(formatRupiah(total_awal,''))

        $('#unit_sesudah_'+number).html(formatRupiah(hasil_unit, ''))
        $('#total_sesudah_'+number).html(formatRupiah(hasil_total, ''))
        $('#total_'+number).val(hasil_total)

        $('#sesudah_unit_'+number).val(Math.round(price-harga))
        $('#sesudah_total_'+number).val(hasil_total)
        $('#diskon_produk_'+number).val(diskon)

        var total = 0
        $('input[name="total[]"]').each(function(){
            total += parseInt($(this).val())
        })

        $('#subTotal').html(formatRupiah(total, ''))
        $('#total').html(formatRupiah(total, ''))
        $('#totalinput').val(total)
        if(qty > gudang){
            $('#gudang_'+number).val('');
        }
    }

    $(".touchspin1").TouchSpin({
        min: 0,
        max: 100,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

    $(".touchspin2").TouchSpin({
        min: 1,
        max: 9999999999999999999,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

    $(".touchspin3").TouchSpin({
        min: 1,
        max: 9999999999999999999,
        buttondown_class: 'btn btn-white',
        buttonup_class: 'btn btn-white'
    });

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
