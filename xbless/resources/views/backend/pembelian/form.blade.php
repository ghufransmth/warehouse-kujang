@extends('layouts.layout')
@section('title', 'Pembelian')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Pembelian Produk</h2>
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
        <br/>
        <a class="btn btn-white btn-sm" href="">Kembali</a>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
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
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($pembelian)? $enc_id : ''}}">

                        <div class="form-group row">
                            <label for=""  class="col-sm-2 col-form-label">No Faktur *</label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="nofaktur" name="nofaktur">
                            </div>

                            <label class="col-sm-2 col-form-label">Tanggal Faktur *</label>
                            <div class="col-sm-3 error-text">
                                <div class="input-group date">
                                     <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="faktur_date" name="faktur_date" value="{{isset($order)? $order->faktur_date : ''}}">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="" class="col-sm-2 col-form-label">Nominal Faktur *</label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="nominal" name="nominal">
                            </div>

                            <label class="col-sm-2 col-form-label">Tanggal Jatuh Tempo *</label>
                            <div class="col-sm-3 error-text">
                                <div class="input-group date">
                                     <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="jatuh_tempo" name="jatuh_tempo">
                                </div>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Keterangan *</label>
                            <div class="col-sm-4 error-text">
                                <textarea type="text" class="form-control" id="ket" name="ket"></textarea>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>
                        <div class="col-lg-2">
                            <input type="hidden" class="mb-1 form-control" name="total_detail" id="total_detail">
                            <a id="tambah_detail_product" class="text-white btn btn-success"><span class="fa fa-pencil-square-o"></span>Tambah</a>
                        </div>

                        {{-- <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Cari Produk *</label>
                            <div class="col-sm-6 error-text">
                                <select name="product_id" class="form-control select2" id="product_id">
                                    <option value="">Pilih Produk</option>
                                    @foreach($product as $key => $row)
                                    <option value="{{$row->id}}"> {{strtoupper($row->nama)}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-sm-4">
                                <a class="btn btn-white btn-sm" href="#">Cari Dengan Nama Produk</a>
                            </div>
                        </div> --}}

                        <div class="hr-line-dashed"></div>

                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-blue">
                                    <th>Produk</th>
                                    <th>Qty Order</th>
                                    <th>Satuan</th>
                                    <th>Tanggal Jatuh Tempo</th>
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody id="show_data">

                            </tbody>
                        </table>

                        <div class="form-group row">
                            <div class="col-sm-4 col-sm-offset-2 float-right">
                                <!-- <a class="btn btn-white btn-sm" href="{{route('pembelian.index')}}">Batal</a> -->
                                <button class="btn btn-primary btn-sm" type="submit" id="simpan">Simpan</button>
                                {{-- <button class="btn btn-success btn-sm" type="submit" id="simpanselesai">Selesai & Simpan</button> --}}
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
    $(document).on('click', '#tambah_detail_product', function(){
        var total_detail = $('#total_detail').val();
        var total = 1 + parseInt(total_detail);
        $('#total_detail').val(total);
        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{ route("pembelian.tambah_detail") }}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(msg){
                $('#show_data').append(msg);
            }
        });
   })
</script>
{{-- <script>
    var numbs=0;
    var basket= [];

    $(document).ready(function(){
        $(".select2").select2({allowClear: true});

        $('#product_id').on('select2:select',function(e){
            var data = e.params.data;
            console.log(data);
            is_exist = 0;
            if(data.id != ""){
                basket.forEach(function(e){
                    if(data.id == e.id){
                        $("#"+e.tombol).val(parseInt($("#"+e.tombol).val()) + 1);
                        is_exist = 1;
                    }
                });
                if(is_exist == 0){
                    numbs++;
                    qui = '<input type="hidden" value="'+data.id+'" id="pid_'+numbs+'" name="pid_'+numbs+'"><input class="touchspin1 form-control" type="text" value="1" id="qty_'+numbs+'" name="qty_'+numbs+'">';
                    aks = '<a href="#" onclick="javascript:hapus_order_detailss(3)" class="btn btn-danger btn-icon"><i class="fa fa-trash"></i></a>';
                    var html ="";
                    name = data.text.split("|")[1];
                    html +="<tr>";
                    html +="<td>"+name+"</td>";
                    html +="<td>"+qui+"</td>";
                    html +="<td>Satuan</td>";
                    html +="<td>"+aks+"</td>";
                    html +="</tr>";
                    $("#show_data").append(html);

                    $(".touchspin1").TouchSpin({
                        min: 1,
                        max: 100,
                        buttondown_class: 'btn btn-white',
                        buttonup_class: 'btn btn-white'
                    });

                    var sendData = {id:data.id, tombol:'qty_'+numbs};
                    basket.push(sendData);
                    console.log(basket);
                }
            }
        });

    })
</script> --}}
@endpush
