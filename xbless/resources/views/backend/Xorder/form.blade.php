@extends('layouts.layout')
@section('title', 'Order Produk')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($order) ? 'Edit' : 'Tambah'}} Order Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('order.index')}}">Order Produk</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($order) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <a class="btn btn-white btn-sm" href="{{route('order.index')}}">Kembali</a>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($order)? $enc_id : ''}}">

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">No Faktur *</label>
                                @if(isset($order))
                                    <div class="col-sm-4 error-text">
                                        <input type="text" class="form-control" id="nofaktur" name="nofaktur" value="{{isset($order)? $order->notransaction : ''}}" disabled>
                                    </div>
                                    @if ($order->status != 0) {{-- ini untuk order yang belum simpan dan selesai --}}
                                        <label class="col-sm-2 col-form-label">Pabrik *</label>
                                        <div class="col-sm-4 error-text">
                                            <input type="text" class="form-control" id="pabrik" name="pabrik" value="{{isset($order)? $order->factory_name : ''}}" disabled>
                                        </div>
                                    @else {{-- ini yang order sudah simpan dan selesai --}}
                                        <label class="col-sm-2 col-form-label">Pabrik *</label>
                                        <div class="col-sm-4 error-text">
                                            <input type="text" class="form-control" id="pabrik" name="pabrik" value="{{isset($order)? $order->factory_name : ''}}">
                                        </div>
                                    @endif
                                @else
                                    <div class="col-sm-4 error-text">
                                        <input type="text" class="form-control" id="nofaktur" name="nofaktur" value="">
                                    </div>
                                    <label class="col-sm-2 col-form-label">Pabrik *</label>
                                    <div class="col-sm-4 error-text">
                                        <input type="text" class="form-control" id="pabrik" name="pabrik" value="">
                                    </div>
                                @endif
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Perusahaan *</label>
                                @if(isset($order))
                                    @if ($order->status != 0) {{-- ini untuk order yang belum simpan dan selesai --}}
                                    <div class="col-sm-4 error-text">
                                        <select name="perusahaan_id" class="form-control select2" id="perusahaan_id" disabled>
                                        <option value="">Pilih Perusahaan</option>
                                            @foreach($perusahaan as $key => $row)
                                            <option value="{{$row->id}}"{{ $order->product_beli_details[0]->perusahaan_id == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else {{-- ini yang order sudah simpan dan selesai --}}
                                    <div class="col-sm-4 error-text">
                                        <select name="perusahaan_id" class="form-control select2" id="perusahaan_id">
                                        <option value="">Pilih Perusahaan</option>
                                            @foreach($perusahaan as $key => $row)
                                            <option value="{{$row->id}}"{{ $order->product_beli_details[0]->perusahaan_id == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    @if($order->flag_proses == 0)
                                    <label class="col-sm-2 col-form-label">Gudang *</label>
                                    <div class="col-sm-4 error-text">
                                        <select name="gudang_id" class="form-control select2" id="gudang_id">
                                            <option value="">Pilih Gudang</option>
                                            @foreach($gudang as $key => $row)
                                            <option value="{{$row->id}}" {{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <label class="col-sm-2 col-form-label">Gudang *</label>
                                    <div class="col-sm-4 error-text">
                                        <select name="gudang_id" class="form-control select2" id="gudang_id" disabled>
                                            <option value="">Pilih Gudang</option>
                                            @foreach($gudang as $key => $row)
                                            <option value="{{$row->id}}" {{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                @else
                                    <div class="col-sm-4 error-text">
                                        <select name="perusahaan_id" class="form-control select2" id="perusahaan_id">
                                        <option value="">Pilih Perusahaan</option>
                                            @foreach($perusahaan as $key => $row)
                                            <option value="{{$row->id}}">{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label class="col-sm-2 col-form-label">Gudang *</label>
                                    <div class="col-sm-4 error-text">
                                        <select name="gudang_id" class="form-control select2" id="gudang_id">
                                            <option value="">Pilih Gudang</option>
                                            @foreach($gudang as $key => $row)
                                            <option value="{{$row->id}}">{{ucfirst($row->name)}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group row">
                                @if(isset($order))
                                    @if ($order->status != 0) {{-- ini untuk order yang belum simpan dan selesai --}}
                                    <label class="col-sm-2 col-form-label">Tanggal Faktur *</label>
                                    <div class="col-sm-4 error-text">
                                        <div class="input-group date">
                                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                            <input type="text" class="form-control" id="faktur_date" name="faktur_date" value="{{isset($order)? date('d/m/Y',strtotime($order->faktur_date)) : ''}}" disabled>
                                        </div>
                                    </div>
                                    @else {{-- ini yang udah simpan dan selesai --}}
                                    <label class="col-sm-2 col-form-label">Tanggal Faktur *</label>
                                    <div class="col-sm-4 error-text">

                                        <input type="text" class="form-control formatTgl" id="faktur_date" name="faktur_date" value="{{isset($order)? date('d-m-Y',strtotime($order->faktur_date)) : ''}}">
                                    </div>
                                    @endif
                                    @if($order->flag_proses == 0)
                                    <label class="col-sm-2 col-form-label">Tanggal Sampai *</label>
                                    <div class="col-sm-4 error-text">
                                        <input type="text" class="form-control formatTgl" id="destination_date" name="destination_date" value="{{isset($order)? date('d-m-Y',strtotime($order->warehouse_date)) : ''}}">
                                    </div>
                                    @else
                                    <label class="col-sm-2 col-form-label">Tanggal Sampai *</label>
                                    <div class="col-sm-4 error-text">
                                        <input type="text" class="form-control formatTgl" id="destination_date" name="destination_date" value="{{isset($order)? date('d-m-Y',strtotime($order->warehouse_date)) : ''}}" disabled>
                                    </div>
                                    @endif
                                @else
                                    <label class="col-sm-2 col-form-label">Tanggal Faktur *</label>
                                    <div class="col-sm-3 error-text">
                                            <input type="text" class="form-control formatTgl" id="faktur_date" name="faktur_date" value="{{date('d-m-Y')}}">
                                    </div>
                                @endif
                            </div>
                            @if(isset($order) && $order->status == 0)
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Catatan</label>
                                    <div class="col-sm-10">
                                        <textarea name="note" id="" cols="30" rows="10" class="form-control" placeholder="Catatan..."></textarea>
                                    </div>

                                </div>
                            @endif
                            <div class="hr-line-dashed"></div>
                                @if(!isset($order))
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Pilih Produk* <br>atau<br> Scan Barcode</label>
                                    <div class="col-sm-6 error-text">
                                        <select name="product_id" class="form-control select2 selectProduct" id="product_id">
                                            <option value="">Pilih Produk</option>
                                            @foreach($product as $key => $row)
                                            <option value="{{$row->id}}"{{ $selectedsatuan == $row->id ? 'selected=""' : '' }}>{{strtoupper($row->product_code)}} | {{strtoupper($row->product_name)}}</option>
                                            @endforeach
                                        </select>
                                        <br>
                                        <input type="text" class="form-control mt-3 product-barcode" name="product-item" placeholder="Input kode barcode" autofocus/>
                                    </div>
                                </div>
                                @else
                                    @if($order->status == 0) {{-- ini untuk order yang belum simpan dan selesai --}}
                                    <div class="form-group row">
                                        <label class="col-sm-2 col-form-label">Pilih Produk* <br>atau<br> Scan Barcode</label>
                                        <div class="col-sm-6 error-text">
                                            <select class="form-control select2 selectProduct" id="product_id">
                                                <option value="">Pilih Produk</option>
                                                @foreach($product as $key => $row)
                                                <option value="{{$row->id}}">{{strtoupper($row->product_code)}} | {{strtoupper($row->product_name)}}</option>
                                                @endforeach
                                            </select>
                                            <br>
                                            <input type="text" class="form-control mt-3 product-barcode" id="product-item" placeholder="Input kode barcode"/>
                                        </div>
                                    </div>
                                    @endif
                                @endif

                            <div class="hr-line-dashed"></div>

                            <table class="table table-bordered table-striped" id="table-detail">
                                <thead>
                                    <tr class="bg-blue">
                                        <th>Produk</th>
                                        <th>Qty Order</th>
                                        @if(isset($order))
                                        <th>Qty Terima</th>
                                        @endif
                                        <th>Satuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="show_data">
                                    @if (isset($order))
                                       @if ($order->status == 1) {{-- untuk order status sudah simpan dan selesai--}}
                                            @foreach ($order->product_beli_details as $key => $item)
                                                <tr>
                                                    <td>{{$item->produk->product_name}}</td>
                                                    <td>{{$item->qty}}</td>
                                                    <td>
                                                        @if ($order->flag_proses == 0)
                                                            <input type="hidden" value="{{$item->id}}" id="pbdid_{{$item->id}}" name="pbdid[]">
                                                            <input type="hidden" value="{{$item->produk_id}}" id="pid_{{$item->produk_id}}" name="pid[]">
                                                            <input type="hidden" value="{{$item->qty}}" name="qty[]" id="product-qty-{{$item->id}}">
                                                            <input class="touchspin-receive form-control input-qty-receive-new" type="text" value="{{$item->qty_receive}}" data-id="{{$item->id}}"  name="qty_receive[]">
                                                        @else
                                                        {{$item->qty_receive}}
                                                        @endif
                                                    </td>
                                                    <td>{{$item->produk->satuans->name}}</td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach ($order->product_beli_details as $key => $item)

                                                <tr id='row-edit-detail-{{$item->produk_id}}'>
                                                    <td>{{$item->produk->product_name}}</td>
                                                    
                                                    <td> <input class="touchspin-receive form-control input-qty-order" type="text" value="{{$item->qty}}" data-id="{{$item->produk_id}}"   name="qty[]" id="qty_{{$item->produk_id}}"></td>
                                                    <td>
                                                        <input type="hidden" value="{{$item->id}}" id="pbid_{{$item->id}}" name="pbid[]">
                                                        <input type="hidden" value="{{$item->produk_id}}" id="pid_{{$item->produk_id}}" name="pid[]">
                                                        <input type="hidden" value="0" id="qty_receive_helper_{{$item->produk_id}}">
                                                        <input class="touchspin-receive form-control input-qty-receive" type="text" value="{{$item->qty_receive}}" data-id="{{$item->produk_id}}" name="qty_receive[]" id="qty_receive_{{$item->produk_id}}">
                                                    </td>
                                                    <td>{{$item->produk->satuans->name}}</td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-icon delete-item-edit"  data-id="{{Crypt::encryptString($item->id)}}" data-product="{{$item->produk_id}}"><i class="fa fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    @endif
                                </tbody>
                            </table>

                            <div class="form-group row">
                                <div class="col-sm-4 col-sm-offset-2 float-right">
                                    <!-- <a class="btn btn-white btn-sm" href="{{route('produk.index')}}">Batal</a> -->
                                    @if (!isset($order))
                                        <button class="btn btn-primary btn-sm" type="submit" id="simpan" name="simpan">Simpan</button>
                                        <button class="btn btn-success btn-sm" type="submit" id="simpanselesai" name="simpanselesai">Selesai & Simpan
                                    @else
                                        @if ($order->status == 0 && $order->flag_proses == 0) {{--order status masih simpan--}}
                                            <button class="btn btn-success btn-sm" type="submit" id="simpanselesai" name="simpanselesai">Selesai & Simpan
                                        @elseif($order->status == 1 && $order->flag_proses == 0) {{--order status sudah simpan dan selesai--}}
                                            <button class="btn btn-success btn-sm" type="submit" id="approved" name="approved">Approved
                                        @endif
                                    @endif
                                    </button>
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
    let numbs= parseInt(<?=isset($order) ? $count_arr_product_beli_item : 0?>);
    let dataBasket = JSON.parse(<?= isset($order) ? json_encode($product_beli_item) : 0?>);
    const statusProses = "<?= isset($order) ? $order->status : '-1' ?>";
    let basket = [];
    
    if(dataBasket.length > 0) {

        dataBasket.map((el,i) => {
            item_basket = {id:el,tombol:'qty_'+(i+1)}
            basket.push(item_basket)
        })
        // console.log(basket);
    }

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

        $(".select2").select2({allowClear: true});

        $('.selectProduct').select2({
            ajax: {
                url: '{{ route("order.search") }}',
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
                        text : item.product_code+'|'+item.product_name,
                        satuan: item.satuans.name
                    });
                    });
                    return{
                        results: results
                    };

                }
            }
        });

        // touchspin untuk merubah qty receive
        $(".touchspin-receive").TouchSpin({
            min: 1,
            max: 99999999,
            buttondown_class: 'btn btn-white',
            buttonup_class: 'btn btn-white'
        });

        $('#product_id').on('select2:select', function (e) {
            var data = e.params.data;
            let checkIsset = "<?= isset($order) ? 1 : 0?>";
            let html = '';
    
            if(data.id !=""){
        
                if(checkProductExist(data.id, basket)) {
                    
                    if(checkIsset == 1) {
                        $("#qty_receive_"+data.id).val(parseInt($("#qty_receive_"+data.id).val()) + 1);
                    }else {
                        $("#qty_"+data.id).val(parseInt($("#qty_"+data.id).val()) + 1);
                    }
                }else {
                    if(checkIsset == 0) {  
                        // numbs++;
                        // qui = '<input type="hidden" value="'+data.id+'" id="pid_'+numbs+'" name="pid[]"><input class="touchspin1 form-control input-qty-order" type="text" value="1" id="qty_'+numbs+'" name="qty[]" data-key="'+numbs+'">';
                        qui = '<input type="hidden" value="'+data.id+'" id="pid_'+data.id+'" name="pid[]"><input class="touchspin1 form-control input-qty-order" type="text" value="1" id="qty_'+data.id+'" name="qty[]" data-key="'+data.id+'">';
                        // aks = '<a href="#" class="btn btn-danger btn-icon delete-item" data-id="'+data.id+'"><i class="fa fa-trash"></i></a>';
                        aks = '<a href="#" class="btn btn-danger btn-icon delete-item" data-id="'+data.id+'"><i class="fa fa-trash"></i></a>';
                        // var html ="";
                        name = data.text.split("|")[1];
                        html +="<tr id='row-detail-"+data.id+"'>";
                        html +="<td>"+name+"</td>";
                        html +="<td>"+qui+"</td>";
                        html +="<td>"+data.satuan+"</td>";
                        html +="<td>"+aks+"</td>";
                        html +="</tr>";
                        $("#show_data").prepend(html);

                        $(".touchspin1").TouchSpin({
                            min: 1,
                            max: 999999,
                            buttondown_class: 'btn btn-white',
                            buttonup_class: 'btn btn-white'
                        });

                        // var sendData = {id:data.id, tombol:'qty_'+numbs};
                        var sendData = {id:data.id};
                        basket.push(sendData);
                       
                    }else if(checkIsset == 1) {
                       
                        // numbs++;
                        // qui = '<input type="hidden" value="0" id="pbid_'+numbs+'" name="pbid[]"> <input type="hidden" value="'+data.id+'" id="pid_'+data.id+'" name="pid[]"><input class="touchspin1 form-control input-qty-order" type="text" value="1" id="qty_'+numbs+'" data-id="0" name="qty[]" data-key="'+numbs+'">';
                        qui = '<input type="hidden" value="0" id="pbid_'+data.id+'" name="pbid[]"> <input type="hidden" value="'+data.id+'" id="pid_'+data.id+'" name="pid[]"><input class="touchspin1 form-control input-qty-order" type="text" value="1" id="qty_'+data.id+'" data-id="0" name="qty[]" data-key="'+data.id+'">';
                        qty_receive = '<input type="hidden" value="0" id="qty_receive_helper_'+data.id+'"><input class="touchspin1 form-control input-qty-receive" type="text" value="1" id="qty_receive_'+numbs+'" data-id="0" data-key="'+numbs+'" name="qty_receive[]">';
                        // aks = '<button type="button" class="btn btn-danger btn-icon delete-item-edit" data-id="'+data.id+'"><i class="fa fa-trash"></i></button>';
                        aks = '<button type="button" class="btn btn-danger btn-icon delete-item-edit" data-id="'+data.id+'"><i class="fa fa-trash"></i></button>';
                        // var html ="";
                        name = data.text.split("|")[1];
                        html +="<tr id='row-detail-"+data.id+"'>";
                        html +="<td>"+name+"</td>";
                        html +="<td>"+qui+"</td>";
                        html +="<td>"+qty_receive+"</td>";
                        html +="<td>"+data.satuan+"</td>";
                        html +="<td>"+aks+"</td>";
                        html +="</tr>";
                        $("#show_data").prepend(html);

                        $(".touchspin1").TouchSpin({
                            min: 1,
                            max: 9999999,
                            buttondown_class: 'btn btn-white',
                            buttonup_class: 'btn btn-white'
                        });
                        var sendData = {id:data.id};
                        basket.push(sendData);
                        // console.log(basket);
                    }
                }
            }
        });

        if(statusProses == '-1' ) {
            $('#submitData').validate({

                rules: {
                    nofaktur:{
                        required: true
                    },
                    pabrik:{
                        required: true
                    },
                    perusahaan_id:{
                        required: true
                    },
                    gudang_id:{
                        required: true
                    },
                    faktur_date:{
                        required: true
                    },
                    produk_id: {
                        required: true
                    }

                },

                messages: {
                    nofaktur:{
                        required: "No Faktur wajib di isi."
                    },
                    pabrik:{
                        required: "Pabrik wajib di isi."
                    },
                    perusahaan_id:{
                        required: "Silahkan pilih salah satu Perusahaan."
                    },
                    gudang_id:{
                        required: "Silahkan pilih salah satu Gudang."
                    },
                    faktur_date:{
                        required: "Tanggal Faktur wajib di isi."
                    },
                    product_id:{
                        required: "Silahkan pilih salah satu Produk."
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
        }else if(statusProses == 0) {
            $('#submitData').validate({

                rules: {
                    nofaktur:{
                        required: true
                    },
                    pabrik:{
                        required: true
                    },
                    perusahaan_id:{
                        required: true
                    },
                    gudang_id:{
                        required: true
                    },
                    faktur_date:{
                        required: true
                    },

                },

                messages: {
                    nofaktur:{
                        required: "No Faktur wajib di isi."
                    },
                    pabrik:{
                        required: "Pabrik wajib di isi."
                    },
                    perusahaan_id:{
                        required: "Silahkan pilih salah satu Perusahaan."
                    },
                    gudang_id:{
                        required: "Silahkan pilih salah satu Gudang."
                    },
                    faktur_date:{
                        required: "Tanggal Faktur wajib di isi."
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
        }else if(statusProses == 1) {
            $('#submitData').validate({

                rules: {
                    nofaktur:{
                        required: true
                    },
                    pabrik:{
                        required: true
                    },
                    perusahaan_id:{
                        required: true
                    },
                    gudang_id:{
                        required: true
                    },
                    faktur_date:{
                        required: true
                    },

                },

                messages: {

                    gudang_id:{
                        required: "Silahkan pilih salah satu Gudang."
                    },
                    destination_date:{
                        required: "Tanggal Sampai wajib di isi."
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
        }

        function SimpanData(){
            $('#simpan').addClass("disabled");
                var form = $('#submitData').serializeArray()
                var image = document.querySelector('input[name=cover]')
                var dataFile = new FormData()
                $.each(form, function(idx, val) {
                    dataFile.append(val.name, val.value)
                })
                if($('#enc_id').val() === '') {
                    $.ajax({
                        type: 'POST',
                        url : "{{route('order.simpan')}}",
                        headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                        data:dataFile,
                        processData: false,
                        contentType: false,
                        dataType: "json",
                        beforeSend: function () {
                            Swal.showLoading();
                        },
                        success: function(data){
                            // console.log(data)
                            if (data.success) {
                                Swal.fire('Yes',data.message,'info');
                                window.location.replace('{{route("order.index")}}');
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
                            if(data.responseJSON.code === 409) {
                                Swal.fire('Ups',data.responseJSON.message,'error');
                            }else {
                                Swal.fire('Ups','Terjadi kesalahan pada sistem','error');
                            }

                            // console.log(data);
                        }
                    });
                }else {

                    if(statusProses == 0) {
                        $.ajax({
                            type: 'POST',
                            url : "{{route('order.saveDone')}}",
                            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                            data:dataFile,
                            processData: false,
                            contentType: false,
                            dataType: "json",
                            beforeSend: function () {
                                Swal.showLoading();
                                $('#simpanselesai').addClass("disabled");
                            },
                            success: function(data){
                                if (data.success) {
                                    Swal.fire('Yes',data.message,'info');
                                    window.location.replace('{{route("order.index")}}');
                                } else {
                                    Swal.fire('Ups',data.message,'info');
                                }
                                Swal.hideLoading();
                            },
                            complete: function () {
                                Swal.hideLoading();
                                $('#simpanselesai').removeClass("disabled");
                            },
                            error: function(data){
                                $('#simpanselesai').removeClass("disabled");
                                Swal.hideLoading();
                                Swal.fire('Ups','Ada kesalahan pada sistem','info');
                                // console.log(data);
                            }
                        });
                    }else if(statusProses == 1) {
                        $.ajax({
                            type: 'POST',
                            url : "{{route('order.approve')}}",
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
                                    window.location.replace('{{route("order.index")}}');
                                } else {
                                    Swal.fire('Ups',data.message,'info');
                                }
                                Swal.hideLoading();
                            },
                            complete: function () {
                                Swal.hideLoading();
                                $('#approved').removeClass("disabled");
                            },
                            error: function(data){
                                $('#approved').removeClass("disabled");
                                Swal.hideLoading();
                                Swal.fire('Ups','Ada kesalahan pada sistem','info');
                                // console.log(data);
                            }
                        });
                    }

                }

        }
    });
    $("#harga_produk" ).keyup(function() {
        var value = Number(this.value.replace(/\./g, ""));
        value = formatRupiah(this.value, '');
        var nilai = this.value.replace(/\./g, "");
        if(value.charAt(0) > 0){
            $('#harga_produk').val(getprice(nilai));
        }else{
            if(value.charAt(1)=='0'){
                $('#harga_produk').val(0);
            }else{
                $('#harga_produk').val(getprice(value));
            }
        }

    });
    $("#harga_export" ).keyup(function() {
        var value = Number(this.value.replace(/\./g, ""));
        value = formatRupiah(this.value, '');
        var nilai = this.value.replace(/\./g, "");
        if(value.charAt(0) > 0){
            $('#harga_export').val(getprice(nilai));
        }else{
            if(value.charAt(1)=='0'){
                $('#harga_export').val(0);
            }else{
                $('#harga_export').val(getprice(value));
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
    $('#destination_date').datepicker({
                startView: 1,
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                autoclose: true,
                format: "dd/mm/yyyy"
    });
    // function selectPerusahaan
    $(document).on('change','#perusahaan_id', function() {

        $.ajax({
            type: 'POST',
            url : "{{route('order.getDataGudang')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            data:{perusahaan_id:$(this).val()},
            dataType: "json",
            beforeSend: function() {
                Swal.showLoading();
            },
            success: function(data){

                if (data.success) {
                    Swal.close();
                    $('#gudang_id').html(`<option value=""></option> ${data.data.map(el => {
                    return `<option value="${el.id}">${el.name}</option>`
                    })}`)
                    // console.log(data.data);
                } else {
                    Swal.fire('Ups',data.message,'error');
                }
                Swal.hideLoading();
            },
            error: function(data){
                Swal.hideLoading();
                Swal.fire('Ups','Ada kesalahan pada sistem','info');
                // console.log(data);
            }

        });
    })

    $(document).on('click','.delete-item',function(e){
        e.preventDefault();
        let id = $(this).data('id')
        $('tr#row-detail-'+id+'').remove()
        basket.splice(basket.findIndex(v => v.id === id), 1);
    })

    $(document).on('click','.delete-item-edit', function() {

        let id = $(this).data('id')
        let productId = $(this).data('product')
        if(typeof id == 'string') {
            var token = '{{ csrf_token() }}';
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data akan terhapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya",
                cancelButtonText:"Batal",
                confirmButtonColor: "#ec6c62",
                closeOnConfirm: false
            }).then(function(result) {

                if (result.value) {
                    $.ajaxSetup({
                        headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
                    });
                    $.ajax({
                        type: 'delete',
                        url: '{{route("order.detail.beli.delete",[null])}}/'+id,
                        headers: {'X-CSRF-TOKEN': token},
                        success: function(data){
                            // console.log(data);
                            if (data.code == 200) {
                                Swal.fire('Yes',data.message,'success');
                                $('tr#row-edit-detail-'+productId+'').remove()
                                basket.splice(basket.findIndex(v => v.id === productId), 1);
                            }else{
                                Swal.fire('Ups',data.message,'info');
                            }
                        },
                        error: function(data){
                            // console.log(data);
                            Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                        }
                    });
                }
            });
        }else if(typeof id == 'number') {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Data akan terhapus!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonClass: "btn-danger",
                confirmButtonText: "Ya",
                cancelButtonText:"Batal",
                confirmButtonColor: "#ec6c62",
                closeOnConfirm: false
            }).then(function(result) {
                Swal.fire('Yes','Berhasil menghapus data','success');
                $('tr#row-detail-'+id+'').remove()
                basket.splice(basket.findIndex(v => v.id === id), 1);
            });

        }
    })

    $(document).on('change','.input-qty-receive',function() {
        // let key = $(this).data('key');
        let key = $(this).data('id');
        let qtyReceiveValue = parseInt($(this).val())
        let qtyValue = parseInt($(`#qty_${key}`).val())
        // console.log(qtyReceiveValue);
        // console.log(qtyValue);
        // if(qtyReceiveValue != qtyValue) {
        //     $(this).val(qtyValue)
        // }
    });

    $(document).on('change','.input-qty-order',function() {
        let key = $(this).data('key');
        let qtyValue = parseInt($(this).val())
        let qtyReceiveValue = $(`#qty_receive_${key}`).val(qtyValue);
    });

    //enter product barcode
    $('.product-barcode').keypress(function(e) {
        if(e.which === 13) {
            e.preventDefault()
            let key = e.which;
            let checkIsset = "<?= isset($order) ? 1 : 0?>";
            var token = '{{ csrf_token() }}';
            let html = ''
                $.ajax({
                    type: 'post',
                    url: '{{route("order.search.produk.barcode")}}',
                    data: {
                        barcode:$(this).val(),
                        _token:token
                    },
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data){ 
                        if (data.code == 200) {
                            let is_exist = 0;
                            let qtyOrderInput;
                            let aksOrderInput
                            if(basket.length == 0) {
                                qtyOrderInput = '<input type="hidden" value="'+data.data.product_id+'" id="pid_'+data.data.product_id+'" name="pid[]"><input class="touchspin1 form-control input-qty-order" type="text" value="'+data.data.isi+'" id="qty_'+data.data.product_id+'" name="qty[]" data-key="1">'
                                aksOrderInput = '<a href="#" class="btn btn-danger btn-icon delete-item" data-id="'+data.data.product_id+'"><i class="fa fa-trash"></i></a>';
                                html +="<tr id='row-detail-"+data.data.product_id+"'>";
                                html +="<td>"+data.data.get_product.product_name+"</td>";
                                html +="<td>"+qtyOrderInput+"</td>";
                                html +="<td>"+data.data.get_product.satuans.name+"</td>";
                                html +="<td>"+aksOrderInput+"</td>";
                                html +="</tr>";

                                $("#show_data").append(html);

                                $(".touchspin1").TouchSpin({
                                    min: 1,
                                    max: 999999,
                                    buttondown_class: 'btn btn-white',
                                    buttonup_class: 'btn btn-white'
                                });

                                sendData = {id:data.data.product_id, tombol:'qty_'+data.data.product_id};
                                basket.push(sendData);
                                $('.product-barcode').val('')
                            }else {
                                if(!checkProductExist(data.data.product_id, basket)) {
                                    qtyOrderInput = '<input type="hidden" value="'+data.data.product_id+'" id="pid_'+data.data.product_id+'" name="pid[]"><input type="hidden" value="0" id="qty_receive_helper_'+data.data.product_id+'"><input class="touchspin1 form-control input-qty-order" type="text" value="'+data.data.isi+'" id="qty_'+data.data.product_id+'" name="qty[]" data-key="1">'
                                    aksOrderInput = '<a href="#" class="btn btn-danger btn-icon delete-item" data-id="'+data.data.product_id+'"><i class="fa fa-trash"></i></a>';
                                    
                                    html +="<tr id='row-detail-"+data.data.product_id+"'>";
                                    html +="<td>"+data.data.get_product.product_name+"</td>";
                                    html +="<td>"+qtyOrderInput+"</td>";

                                    if(checkIsset == 1) {
                                        let qtyOrderReceive = '<input type="hidden" value="0" id="pbid_0" name="pbid[]"><input class="touchspin1 touchspin-receive form-control input-qty-receive" type="text" value="'+data.data.isi+'" id="qty_receive_'+data.data.product_id+'" data-id="'+data.data.product_id+'" name="qty_receive[]">'
                                        html += "<td>"+qtyOrderReceive+"</td>"
                                    }

                                    html +="<td>"+data.data.get_product.satuans.name+"</td>";
                                    html +="<td>"+aksOrderInput+"</td>";
                                    html +="</tr>";
                                    
                                    
                                    $("#show_data").prepend(html);
                                        $(".touchspin1").TouchSpin({
                                        min: 1,
                                        max: 999999,
                                        buttondown_class: 'btn btn-white',
                                        buttonup_class: 'btn btn-white'
                                    });

                                    sendData = {id:data.data.product_id, tombol:'qty_'+numbs};
                                    basket.push(sendData);
                                }else {
                                    if(checkIsset == 1) {

                                        if(parseInt($("#qty_receive_helper_"+data.data.product_id).val()) == 0) {
                                            $("#qty_receive_helper_"+data.data.product_id).val(data.data.isi)
                                            $("#qty_receive_"+data.data.product_id).val(parseInt(data.data.isi));
                                        }else {
                                            $("#qty_receive_helper_"+data.data.product_id).val(data.data.isi)
                                            $("#qty_receive_"+data.data.product_id).val(parseInt($("#qty_receive_"+data.data.product_id).val()) + parseInt(data.data.isi));
                                        }
                                        
                                    }else {
                                        $("#qty_"+data.data.product_id).val(parseInt($("#qty_"+data.data.product_id).val()) + parseInt(data.data.isi));
                                    }
                                
                            
                                }
                                $('.product-barcode').val('')
                            }
                        }else{
                            Swal.fire('Ups',data.message,'info');
                            $('.product-barcode').val('')
                        }
                },
                error: function(data){
                    // console.log(data);
                    Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                }
            });
        }
        
    })

    $('#submitData').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
    });


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

    function checkProductExist(id, arr) {
        for (var i=0; i < arr.length; i++) {
            if (arr[i].id === id) {
                return true
            }
        }
        return false
    }

</script>
@endpush
