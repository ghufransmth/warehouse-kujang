@extends('layouts.layout')
@section('title', 'Proses Tanda Terima')
<style>
    .desc{
        max-width: 100px;
    }
</style>
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Proses Tanda Terima</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Proses Tanda Terima</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <form id="submitData" name="submitData">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Filter Tanggal : </label>
                            <div class="col-sm-2 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_start" name="tgl_start" value="{{$tgl_start}}">
                            </div>
                            <div class="col-sm-2 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_end" name="tgl_end" value="{{$tgl_end}}">
                            </div>

                            <label class="col-sm-2 col-form-label">Sales : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="sales" name="sales[]" multiple="multiple">
                                    <option disabled>-- Pilih Sales --</option>
                                    @foreach($sales as $key => $row)
                                        <option value="{{$row->name}}">{{ucfirst($row->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Perusahaan : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="perusahaan" name="perusahaan">
                                    <option value="">Pilih Perusahaan</option>
                                    @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}">{{ucfirst($row->name)}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-sm-2 col-form-label">Customer : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="customer" name="customer">
                                <option value="">Pilih Customer</option>
                                    @foreach($member as $key => $row)
                                        <option value="{{$row->id}}">{{ucfirst($row->name)}} - {{ucfirst($row->city)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">No Invoice : </label>
                            <div class="col-sm-4 error-text">
                                <input type="text" class="form-control" id="invoice" name="invoice" placeholder="00001/PT.KS/01/21">
                            </div>
                            <label class="col-sm-2 col-form-label">Kota : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="city" name="city[]" multiple="multiple">
                                    <option value="">-- Pilih Kota --</option>
                                    @foreach($cities as $key => $city)
                                        <option value="{{$city->name}}">{{ucfirst($city->name)}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-1 error-text">
                               <button class="btn btn-success" id="search-data" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                            @can('tandaterima.prosesaksi')
                            <div class="col-sm-1">
                                <button class="btn btn-secondary" id="save_tanda_terima" type="button"><span class="fa fa-save"></span>&nbsp; Proses Tanda Terima</button>
                            </div>
                            @endcan
                        </div>
                        <div class="hr-line-dashed"></div>
                    </form>
                </div>
            </div>
            <div class="ibox" id="data">
                @foreach($invoice as $key => $value)
                    <div class="ibox-content">
                        <div class="table-responsive">
                            <table class="table shoping-cart-table">
                                <tbody>
                                    <input type="checkbox" id="{{ $value->id }}" name="select_invoice" value="{{ $value->id }}">
                                    <tr>
                                        <td class="">
                                            <h3>
                                                <span id="customer">{{ $value->member_name }} - {{$value->member_city}}</span>
                                            </h3>
                                            <p class="medium info_invoice">
                                                Invoice : #<span id="invoice">{{ $value->no_nota }}</span>
                                            </p>
                                            <p class="medium info_invoice">
                                                No PO : #<span id="no_po">{{$value->purchase_no}}</span>
                                            </p>
                                            <p class="medium info_invoice">
                                                Sales : <span id="sales"> {{ $value->sales_name }}</span>
                                                ( Dibuat pada : <span id="tgl_buat">{{ $value->create }}</span> )
                                            </p>
                                            <p class="medium info_invoice">
                                                Expedisi : <span id="expedisi"> {{ $value->nama_expedisi }}</span>
                                            </p>
                                            @if($value->nama_expedisi_via != '-')
                                            <p class="medium info_invoice">
                                                Expedisi Via : <span id="expedisi"> {{ $value->nama_expedisi_via }}</span>
                                            </p>
                                            @endif
                                        </td>
                                        <td>
                                            <p class="medium info_invoice font-weight-bold text-uppercase"><span id="perusahaan">{{$value->perusahaan_name}}</span></p>
                                            <p class="medium info_invoice font-weight-bold"><span id="total">Rp. {{number_format($value->total, 0, '','.')}}</span></p>
                                            @if($value->pay_status == 0)
                                                <span class="badge badge-warning text-left">Belum Lunas</span>
                                            @elseif($value->paystatus == 1)
                                                <span class="badge badge-warning text-left">Lunas</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="user text-center" colspan="2">
                                            <div class="m-t-m">
                                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> PO - </span><span class="info_invoice" id="user_po">{{strtoupper($value->created_by)}}</span>
                                                &nbsp; | &nbsp;
                                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> INV - </span><span class="info_invoice" id="user_inv">{{ strtoupper($value->loginv) }}</span>
                                                &nbsp; | &nbsp;
                                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> GDG - </span><span class="info_invoice" id="user_gdg">{{ strtoupper($value->gudang) }}</span>
                                                &nbsp; | &nbsp;
                                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> INV - </span><span class="info_invoice" id="user_inv">{{ strtoupper($value->create_user) }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="hr-line-dashed"></div>
                            <div class="heading-elements">
                                <span class="heading-text">
                                    <span class="text-muted" id="created">{{ $value->create_invo }}</span>
                                </span>
                                <ul class='list-inline pull-right'>
                                    <li><a href='#' class='text-default' data-toggle='modal' data-target='#invoice_detail' onclick="detail_invoice({{$value->id}})"><i class='fa fa-eye fa-lg'></i></a></li>
                                    &nbsp; | &nbsp;
                                    <li class='dropdown'>
                                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-bars fa-lg'></i></a>
                                        <ul class='dropdown-menu' style="position: absolute !important; top: -208px !important; left: -147px !important; will-change: top, left !important;">
                                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_invoice'><i class='fa fa-print fa-lg'></i><span>&nbsp; Print invoice</span></a></li>
                                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_surat_jalan'><i class='fa fa-file-word-o fa-lg'></i><span>&nbsp; Surat Jalan</span></a></li>
                                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_packing_list'><i class='fa fa-print fa-lg'></i><span>&nbsp; Print Packing List</span></a></li>
                                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_amplop'><i class='fa fa-print fa-lg'></i>&nbsp; Print Amplop</a></li>
                                            <div class="hr-line-dashed"></div>
                                            <li><a href='#' data-toggle='modal' data-target='#modal_pengiriman' onclick="pengiriman({{ $value->id }})"><i class='fa fa-truck fa-lg'></i><span>&nbsp; Input Pengiriman</span></a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="float-right mt-3">
                    {{ $invoice->links() }}
                </div>
            </div>
        </div>
    </div>

    @include('backend.invoice.detail')

    <div class="modal fade" id="modal_pengiriman" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal_nota">Data Pengiriman</span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <input type="hidden" id="id_po_invoice">
              <div class="modal-body" id="data_invoice">
                <div class="warning">
                  <span>Input Data Pengiriman</span>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="row">
                  <div class="col-lg-4">
                    <span>Tanggal Kirim</span>
                  </div>
                  <div class="col-lg-8">
                    <input type="text" class="form-control formatTgl" id="tgl_invoice" name="tgl_invoice" value="dd-mm-yyyy">
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <input type="hidden" id="total">
                  <div class="col-lg-4">
                    <span>Expeisi</span>
                  </div>
                  <div class="input-group m-b col-lg-8">
                    <div id="expedisi_list"></div>
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <div class="col-lg-4">
                    <span>No Resi</span>
                  </div>
                  <div class="input-group m-b col-lg-8">
                    <input type="text" class="form-control" id="no_resi" placeholder="Masukan No Resi">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="save_pengiriman">Ya</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modal_pilihan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span id="title_modal_nota">Pilih Menu</span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <input type="hidden" id="id_invoice_menu">
                <div class="modal-body">
                    <div class='form-group has-feedback text-center' id="list_menu_detail">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $(".select2").select2({allowClear: true});
    });
    $('.formatTgl').datepicker({
        todayBtn: "linked",
        keyboardNavigation: false,
        forceParse: false,
        calendarWeeks: true,
        autoclose: true,
        format: "dd-mm-yyyy"
    });

    $(document).on('click', '#search-data', function(){
        var form = $('#submitData').serialize()
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'GET',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url : "{{route('tandaterima.filter')}}",
            data: form,
            success: function(response){
                $('#data').html(response)
            },
        })
    })

    $(document).on('click', '#save_pengiriman', function(){
        let tgl = $('#tgl_invoice').val()
        let expedisi = $('#exspedisi').val()
        let resi = $('#no_resi').val()
        let idinv = $('#id_po_invoice').val()

        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("invoice.simpan_pengiriman") }}',
            data: {
                enc_id: idinv,
                tgl_kirim: tgl,
                resi_no: resi,
                expedisi: expedisi
            },
            success: function(response){
                $('#modal_pengiriman').modal('hide')
                // console.log(response)
                if (response.success) {
                  Swal.fire('Yes',response.message,'info');
                } else {
                  Swal.fire('Ups',response.message,'info');
                }
            }
        })
    })

    $(document).on('click', '#save_tanda_terima', function(){
        let idinv = []
        $("input:checkbox[name=select_invoice]:checked").each(function(){
            idinv.push($(this).val())
        })
        Swal.fire({
            title: 'Apakah Anda yakin',
            text: "ingin memproses data ini sebagai Tanda Terima?",
            icon: 'danger',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajaxSetup({
                    headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
                });
                $.ajax({
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                    url: '{{ route("tandaterima.proses_tanda_terima") }}',
                    data: {
                        idinv: idinv
                    },
                    success: function(response){
                        console.log(response)
                        if (response.success) {
                            Swal.fire('Yes',response.message,'info');
                            // window.location.replace('{{route("tandaterima.index")}}');
                            // location.reload();
                            var form = $('#submitData').serialize()
                            $.ajax({
                                type: 'GET',
                                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                                url : "{{route('tandaterima.filter')}}",
                                data: form,
                                success: function(response){
                                    $('#data').html(response)
                                },
                            })
                        } else {
                            Swal.fire('Ups',response.message,'info');
                        }
                    }
                })
            }
        })
    })
</script>
<script>
    function detail_invoice(idpo){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("invoice.detail") }}',
            data: {
                enc_id: idpo
            },
            success: function(response){
                $('#list_produk').find('tr').remove();
                let invoice = response.invo
                let podetail = response.invoice_detail
                let id = invoice.invoice.id

                $('#id_invoice').val(id);
                let print = '{{route("invoice.menu_invoice",[null])}}/print/'+idpo
                $('#print').attr('href',`${print}`)
                let tabledata = ''
                $('#title_modal').html(`# ${ invoice.invoice.no_nota }`)
                $('#nota').html(`# ${ invoice.invoice.no_nota }`)
                $('#nama_perusahaan').html(`${ invoice.perusahaan.name }`)
                $('#alamat_perusahaan').html(`${ invoice.perusahaan.alamat }`)
                $('#telp_perusahaan').html(`${ invoice.perusahaan.contact }`)
                $('#member').html(`${ invoice.member.name }`)
                $('#alamat_member').html(`${ invoice.member.alamat }`)
                $('#telp_member').html(`${ invoice.member.contact }`)
                $('#tanggal_pesan').html(`${ invoice.invoice.date_order }`)
                $('#total_price').html(`Rp. ${ invoice.invoice.total }`)
                $('#nm_bank').html(`${ invoice.member.bank }`)
                $('#rek_bank').html(`${ invoice.member.rek }`)
                $('#kota').html(`${ invoice.member.city }`)
                $('#negara').html(`${ invoice.member.negara }`)
                $('#subTotal').html(`${ invoice.invoice.sub_total }`)
                $('#discount').html(`${ invoice.invoice.diskon }`)
                $('#total_discount').html(`${ invoice.invoice.total_diskon }`)
                $('#total_setelah_diskon').html(`${ invoice.invoice.setelah_diskon }`)
                $('#total_ppn').html(`${ invoice.invoice.ppn }`)
                $('#total_keseluruhan').html(`${ invoice.invoice.total }`)
                $('#header-table').html(response.header)
                $('#memo').val(invoice.invoice.memo);
                $('#colly').val(invoice.invoice.colly);
                for (let i = 0; i < podetail.length; i++) {
                  tabledata += podetail[i]
                }
                $('#list_produk').append(tabledata)
            }
        })
    }

    function pengiriman(idinv){
        $('#expedisi_list').html('')
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("invoice.pengiriman_detail") }}',
            data: {
                enc_id: idinv
            },
            success: function(response){
                // console.log(response)
                $('#id_po_invoice').val(idinv)
                $('#expedisi_list').html(`${ response.expedisi }`)
                $('#tgl_invoice').val(`${ response.tgl }`)
                if(response.resi_no != null){
                    $('#no_resi').val(`${ response.resi_no }`)
                }
            }
        })
    }

    function pilih_menu(idinv, menu){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("invoice.menu_invoice_list") }}',
            data: {
                enc_id: idinv,
                menu: menu
            },
            success: function(response){
                $('#list_menu_detail').html(response.list)
            }
        })
    }

    function proses_invoice(idinv, menu){
        window.open('{{route("invoice.menu_invoice",[null])}}/'+menu+'/'+idinv,'_blank');
    }

    function proses_surat_jalan(idinv, menu){
        window.open('{{route("invoice.menu_surat_jalan",[null])}}/'+menu+'/'+idinv,'_blank');
    }

    function proses_packing_list(idinv, menu){
        window.open('{{route("invoice.menu_packing_list",[null])}}/'+menu+'/'+idinv,'_blank');
    }

    function proses_amplop(idinv, menu){
        window.open('{{route("invoice.menu_amplop",[null])}}/'+menu+'/'+idinv,'_blank');
    }
</script>
<script>
    $(document).on("click","#simpan",function(){

        let idinv = $('#id_invoice').val()
        let memo  = $('#memo').val();
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("invoice.simpan_memo_colly") }}',
            dataType: "json",
            data: {
                enc_id: idinv,
                memo  : memo,
                type  : 2,
            },
            success: function(result){
                if (result.success) {
                    Swal.fire('Yes',result.message,'info');
                } else {
                    Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
                }
            }
        })
    });
</script>
@endpush
