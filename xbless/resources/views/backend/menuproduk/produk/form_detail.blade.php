@extends('layouts.layout')
@section('title', 'Manajemen Penjualan ')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Produk</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('purchaseorder.index')}}">Master Produk</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Detail Produk</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <a class="btn btn-white btn-sm" href="{{route('produk.index')}}">Batal</a>
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
                        <input type="hidden" name="enc_id" id="enc_id" value="{{isset($produk)? $enc_id : ''}}">

                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">

                            <label class="col-sm-2 col-form-label">Nama Produk </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control formatTgl" id="nama_product"
                                    value="{{ isset($produk)? $produk->nama : '' }}" name="nama_product" autocomplete="off">

                            </div>
                            <label class="col-sm-2 col-form-label">Kode Produk </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" name="kode_product" value="{{ isset($produk)? $produk->kode_product : '' }}" class="form-control" id="kode_product">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Kategori </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="kategori" name="kategori">
                                    <option value="0">Pilih Kategori</option>
                                    @foreach($kategori as $key => $value)
                                    <option value="{{ $value->id }}"
                                    @if(isset($selectedkategori))
                                        @if($value->id == $selectedkategori)
                                            selected
                                        @endif
                                    @endif
                                    >{{ $value->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="col-sm-2 col-form-label">Harga Jual  <span style="float: right">Rp.</span></label>
                            <div class="col-sm-4 error-text">
                                <input type="text" name="harga_jual" value="{{ isset($produk)? number_format($produk->harga_jual,0,",",".") : '' }}" class="form-control" id="harga_jual">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label"><a href="#!" onclick="tambahSupplier()"
                                class="btn btn-success btn-sm icon-btn sm-btn-flat product-tooltip" title="Tambah Supplier">Tambah
                                Supplier</a></label>
                            <div class="col-sm-4 error-text">

                            </div>


                        </div>

            <div class="">
            </div>
            <div class="hr-line-dashed"></div>
            <div class="table-responsive">
                <table id="table1" class="table display table table-hover p-0 table-striped" style="overflow-x: auto;">
                    <thead>
                        <tr class="text-white text-center bg-primary">
                            <th width="10%">Supplier</th>
                            <th>Harga Pembelian</th>
                            <th width="5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="ajax_produk" class="bg-white">
                        @if(count($produk->getdetailproduct) > 0)
                        <input type="hidden" name="jumlahdetail" value="{{ (count($produk->getdetailproduct) > 0)? count($produk->getdetailproduct) : '0'  }}" id="jumlahdetail">
                            @foreach($produk->getdetailproduct as $key => $detail)
                            <tr class="bg-white" id='dataajaxproduk_{{ $key+1 }}'>
                                <td>
                                    <select class="select2_supplier_{{ $key+1 }}" id="supplier_{{ $key }}" name="supplier[]" width="100%">
                                        <option value="{{ $detail->getsupplier->id }}">{{ $detail->getsupplier->nama }}</option>

                                    </select>
                                </td>
                                <td>
                                    <input type="text" class="form-control" id="harga_pembelian_{{ $key+1 }}" name="harga_pembelian[]"
                                        value="{{$detail->harga_pembelian}}">
                                </td>
                                <td><a href='#!' onclick='deleteProduk({{ $key+1 }})' class='btn btn-danger btn-sm icon-btn sm-btn-flat product-tooltip' title='Hapus'><i class='fa fa-trash'></i></a></td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <input type="hidden" class="form-control mb-1" name="total_produk" id="total_produk" value="{{ isset($penjualan)? count($detail_penjualan) : '1' }}">
            <div class="hr-line-dashed"></div>
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
        // $('.select2_supplier_1').select2({width: '500px'})
        var count_data = "{{ count($produk->getdetailproduct) }}";
        for(let i=1; i<= count_data; i++ ){
            select_supplier(i);

        }

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
                url : "{{route('produk.detail_simpan')}}",
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
                    if(data.success == true){
                        Swal.fire('Yes', data.messages, 'success');
                        window.location.replace('{{route("produk.index")}}');

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

function select_supplier(num){
    // console.log(num);
    $('.select2_supplier_'+num).select2({allowClear: false, width: '500px',
        ajax: {
                url: '{{ route("produk.supplier") }}',
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
    });
}
    function tambahSupplier(){
        var total_produk = $('#jumlahdetail').val();
        var total = 1 + parseInt(total_produk);
        $('#jumlahdetail').val(total);

        $.ajax({
            type: 'POST',
            data: 'total='+total,
            url: '{{route("produk.addsupplier")}}',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            success: function(response) {
                console.log(response)
                $('#ajax_produk').prepend(response);
            },
            complete: function(){
                console.log(total);
                select_supplier(total);
            }
        });

    }

</script>
<script>


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


</script>
@endpush
