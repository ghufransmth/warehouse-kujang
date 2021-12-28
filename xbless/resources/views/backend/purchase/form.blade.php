@extends('layouts.layout')
@section('title', 'Manajemen Purchase Order ')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{isset($purchaseorder) ? 'Edit' : 'Tambah'}} Purchase Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('purchaseorder.index')}}">Purchase Order</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{isset($purchaseorder) ? 'Edit' : 'Tambah'}}</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
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
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                            <input type="hidden" name="enc_id" id="enc_id" value="{{isset($purchaseorder)? $enc_id : ''}}">

                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Customer </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="member" name="member">
                                        <option value="">Pilih Customer</option>

                                        @foreach($member as $key => $row)
                                            <option value="{{$row->id}}"{{ $selectedmember == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}} - {{ucfirst($row->city)}}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Sales </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="sales" name="sales">
                                        <option value="">Pilih Sales</option>

                                        @foreach($sales as $key => $row)
                                            <option value="{{$row->id}}"{{ $selectedsales == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Expedisi </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="expedisi" name="expedisi">
                                        <option value="">Pilih Expedisi</option>

                                        @foreach($expedisi as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedexpedisi == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach

                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Expedisi Via </label>
                                <div class="col-sm-4 error-text">
                                    <select class="form-control select2" id="expedisi_via" name="expedisi_via">
                                        <option value="">Pilih Expedisi Via</option>

                                        @foreach($expedisivia as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedexpedisivia == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Catatan </label>
                                <div class="col-sm-10 error-text">
                                <textarea class="form-control" id="note" name="note">{{isset($purchaseorder)? $purchaseorder->note : ''}}</textarea>
                                </div>
                            </div>
                            <div class="">
                                <a href="#!" onclick="tambahProduk()" class="btn btn-success btn-sm icon-btn sm-btn-flat product-tooltip" title="Tambah Produk">Tambah Produk</a>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="table-responsive">
                                <table id="table1" class="table">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Tipe Harga</th>
                                            <th>Harga Satuan</th>
                                            <th>Qty Order</th>
                                            <th>Total Satuan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ajax_produk">
                                        <tr>
                                            <td>
                                                <select class="select2_produk" id="produk_1" name="produk[]" onchange="hitung(this.options[this.selectedIndex].value, 1)" width="100%">
                                                    <option value="0">Pilih Produk </option>

                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-control" id="tipeharga_1" name="tipeharga[]" onchange="harga(this.options[this.selectedIndex].value, 1)">
                                                    <option value="">Pilih Tipe Harga </option>
                                                    @foreach($tipeharga as $key => $row)
                                                    <option value="{{$key}}"{{ $selectedproduct == $key ? 'selected=""' : '' }}>{{ucfirst($row)}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" id="hargasatuan_1" name="hargasatuan[]" readonly>
                                            </td>
                                            <td width="15%">
                                                <input type="text" class="form-control touchspin" id="qty_1" name="qty[]" value="1" onkeyup="hitung_qty(1)" onchange="hitung_qty(1)">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" id="total_1" name="total[]" readonly>
                                            </td>
                                            <td>
                                                -
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" class="form-control mb-1" name="total_produk" id="total_produk" value="0">
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <div class="col-sm-6 col-sm-offset-2">
                                    <a class="btn btn-white btn-sm" href="{{route('provinsi.index')}}">Batal</a>
                                    <button class="btn btn-primary btn-sm" type="button" id="simpan">Selesai</button>
                                </div>
                                @can('draftpurchaseorder.tambah')
                                <div class="col-sm-6 text-right" >

                                    <button class="btn btn-info btn-sm" type="button" id="draft">Simpan Draft</button>
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
        $('.select2_produk').select2({allowClear: false, width: '200px',
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
                          text : item.product_code+'-'+item.product_name,
                      });
                    });
                    return{
                      results: results
                    };
                }
            }
        });
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
                            window.location.replace('{{route("purchaseorder.tambah")}}');
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
        $('#ajax_produk').html('');
    });

    function tambahProduk(){
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

    // To Be Continue
    function hitung(value, num){
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
                url: '{{route("purchaseorder.proses_hitung")}}',
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
                url: '{{route("purchaseorder.proses_hitung")}}',
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
        $qty = $('#qty_'+num).val()
        var value = $('#hargasatuan_'+num).val().replace(/\./g, "") * $qty
        console.log(value)
        // console.log($('#hargasatuan_'+num).val())
        $('#total_'+num).val(formatRupiah(value, ''))
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
                console.log(total_produk)
                var total = parseInt(total_produk) - 1;
                $('#total_produk').val(total);
                console.log(total)
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
