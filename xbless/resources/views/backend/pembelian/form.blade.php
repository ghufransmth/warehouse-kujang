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
                                    <input type="text" class="form-control jatuh_tempo" id="jatuh_tempo" name="jatuh_tempo" placeholder="dd-mm-yyyy" autocomplete="off">
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
                            <input type="hidden" class="mb-1 form-control" name="total_detail" id="total_detail" value="0">
                            <a id="tambah_detail_product" class="text-white btn btn-success"><span class="fa fa-pencil-square-o"></span>Tambah</a>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <table class="table table-bordered table-striped" id="example">
                            <thead>
                                <tr class="bg-blue">
                                    <th>Produk</th>
                                    <th>Qty Order</th>
                                    <th>Satuan</th>
                                    {{-- <th>Tanggal Jatuh Tempo</th> --}}
                                    <th>#</th>
                                </tr>
                            </thead>
                            <tbody id="detail_form">
                                @if(isset($product_beli_detail))
                                    @foreach($product_beli_detail as $key => $result)
                                    @php $no = $key+1; @endphp
                                    <tr id="detail_product_{{ $no }}">
                                        <input type="hidden" id="detail_product" name="detail_product[]" value="{{ isset($result)? $result->id : 'null' }}">
                                        <td>
                                            <select name="product[]" id="product_{{ $no }}" class="select2_product form-control">
                                                <option value="{{ $result->product_id }}">{{ $result->kode_produk }} - {{ $result->product->nama }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control qty_touchspin" id="qty" name="qty[]" value="{{isset($result)? $result->qty : 0}}">
                                        </td>
                                        <td>
                                            <select id='satuan_{{$no}}' name='satuan[]' class='select2 satuan form-control'>
                                                <option value='{{$result->satuan_id}}' selected>{{$result->satuan}}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <a class="text-white btn btn-danger btn-hemisperich btn-xs" onclick='javascript:deleteDetail({{$no}},{{$result->id}})' data-original-title='Hapus Data' id='deleteModal'><i class='fa fa-trash'></i></a>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <div class="form-group row">
                            <div class="col-sm-4 col-sm-offset-2 float-right">
                                <a class="btn btn-white btn-sm" href="{{route('pembelian.index')}}">Batal</a>
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
        console.log(total_detail)
        var total = 1 + parseInt(total_detail);
        $('#total_detail').val(total);
        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{ route("pembelian.tambah_detail") }}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(msg){
                $('#detail_form').append(msg);
            }
        });
   });

   $(document).on('click', '#simpan', function(e){
        e.preventDefault()
        var form = $('#submitData').serializeArray()
        var dataFile = new FormData()
        $.each(form, function(idx, val) {
            dataFile.append(val.name, val.value)
        })
        console.log(dataFile)
        $.ajax({
            type: 'POST',
            url : "{{route('pembelian.simpan')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            data:dataFile,
            processData: false,
            contentType: false,
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            success: function(data){
                console.log(data)
                if (data.success) {
                    Swal.fire('Yes',data.message,'info');
                    window.location.replace('{{route("pembelian.index")}}');
                } else {
                    Swal.fire('Ups',data.message,'info');
                }
            },
            complete: function () {
                Swal.hideLoading();
                $('#simpan').removeClass("disabled");
            },
        });
    })

   $('#example').DataTable({
        'searching': false,
        'paging': false,
        'ordering': false,
        'info': false,
        language : {
            "zeroRecords": " "
        }
    })

    $('.select2_product').select2({
        placeholder: 'Pilih Product',
        ajax: {
            url: '{{ route("pembelian.search_product") }}',
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
                    text : item.code+' | '+item.name,
                    satuan: item.satuan_product
                });
                });
                return{
                    results: results
                };
            }
        }
    });

    $('.jatuh_tempo').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });
</script>
@endpush
