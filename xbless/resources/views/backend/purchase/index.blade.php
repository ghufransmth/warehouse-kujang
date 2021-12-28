@extends('layouts.layout')

@section('title', 'Purchase Order')

@section('content')
<style>
  .swal2-container{
    z-index: 100000 !important;
  }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Purchase Order</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Purchase Order</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('purchaseorder.tambah')
        <a href="{{ route('purchaseorder.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
      <div class="col-lg-12">
          <div class="ibox">
              <div class="ibox-content">
                <form id="submitData" name="submitData">
                    <div class="form-group row">
                        <label class="col-sm-2 col-form-label">Perusahaan : </label>
                        <div class="col-sm-4 error-text">
                            <select class="form-control select2" id="perusahaan" name="perusahaan">
                                <option value="">Semua Perusahaan</option>
                                @foreach($perusahaan as $key => $row)
                                    <option value="{{$row->id}}" {{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-sm-2 col-form-label">Customer : </label>
                        <div class="col-sm-4 error-text">
                            <select class="form-control select2" id="customer" name="customer">
                            <option value="">Semua Customer</option>
                                @foreach($member as $key => $row)
                                    <option value="{{$row->id}}" {{ $selectedmember == $row->id ? 'selected=""' : '' }} >{{ucfirst($row->name)}}-{{ucfirst($row->city)}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-sm-1 error-text">
                           <button class="btn btn-success" id="search-data" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                        </div>
                    </div>
                    <div class="hr-line-dashed"></div>
                  </form>
                  <ul class="nav nav-tabs" id="myTab" role="tablist">
                    @if(Gate::check('purchaseorder.liststatuspo') || Gate::check('purchaseorder.liststatusinvoiceawal') || Gate::check('purchaseorder.liststatusinvoice'))
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==0?'active':(session('type')==""?'active':'')}}" id="listpo-tab" value="0" onclick="change_type(0)" data-toggle="tab" href="#listpo" role="tab" aria-controls="listpo" aria-selected="true">PO LIST</a>
                        </li>
                    @endif
                    @can('purchaseorder.liststatuspolisttolak')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==1?'active':''}}" id="listpotolak-tab" value="1" onclick="change_type(1)" data-toggle="tab" href="#listpotolak" role="tab" aria-controls="listpotolak" aria-selected="false">LIST PO TOLAK</a>
                        </li>
                    @endcan
                    @can('purchaseorder.liststatusinvoiceawal')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==2?'active':''}}" id="listpovalidasi-tab" value="1" onclick="change_type(2)" data-toggle="tab" href="#listpovalidasi" role="tab" aria-controls="listpovalidasi" aria-selected="false">LIST PO CEK HARGA</a>
                        </li>
                    @endcan
                    @can('purchaseorder.liststatusgudang')
                        @foreach ($gudang as $k=>$itemgudang)
                            <li class="nav-item">
                                <a class="nav-link {{session('type_gudang')==$itemgudang->id?'active':''}}" id="listgudang_{{$itemgudang->id}}-tab" value="1" onclick="change_type_gudang(3,{{$itemgudang->id}})" data-toggle="tab" href="#listpovalidasi" role="tab" aria-controls="listpovalidasi" aria-selected="false">{{strtoupper($itemgudang->name)}}</a>
                            </li>
                        @endforeach
                    @endcan
                  </ul>
                  <input type="hidden" class="form-control" id="type" value="{{session('type')}}"/>
                  <input type="hidden" class="form-control" id="type_gudang" value="{{session('type_gudang')}}"/>
                  <div class="hr-line-dashed"></div>
                  <div class="table-responsive">
                      <table id="table1" class="table display table-bordered" >
                      <thead>
                      <tr>
                          <th width="10px;">No</th>
                          <th width="300px;">#</th>
                          <th>Customer</th>
                          <th>Tanggal Update</th>
                          <th>Action Status</th>
                          <th>Status</th>
                          <th>Expedisi</th>
                          <th>Total (Rp.)</th>
                          <th class="text-center">Aksi</th>
                      </tr>
                      </thead>
                      <tbody>

                      </tbody>
                      <tfoot>
                      </tfoot>
                  </table>
                  </div>
              </div>
          </div>
      </div>
    </div>

    <!-- Modal Detail PO -->
    @include('backend.purchase.detail')

    <!-- Modal untuk Process Nota -->
    <div class="modal fade" id="modal_process_nota" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal_nota">Process Nota</span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <input type="hidden" id="id_po_invoice">
              <div class="modal-body" id="data_invoice">
                <div class="warning text-center">
                  <span>Apakah Anda yakin ingin memproses data ini sebagai Invoice ?</span>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="row">
                  <div class="col-lg-4">
                    <span>Tanggal Invoice</span>
                  </div>
                  <div class="col-lg-8">
                    <input type="text" class="form-control formatTgl" id="tgl_invoice" name="tgl_start" value="dd-mm-yyyy">
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <input type="hidden" id="total">
                  <div class="col-lg-4">
                    <span>Total</span>
                  </div>
                  <div class="input-group m-b col-lg-8">
                    <div class="input-group-append">
                      <span class="input-group-addon">Rp</span>
                    </div>
                    <input type="text" class="form-control" disabled id="total_invoice">
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <div class="col-lg-4">
                    <span>Diskon</span>
                  </div>
                  <div class="input-group m-b col-lg-8">
                    <input type="text" class="form-control" id="discount_invoice" oninput="this.value=this.value.replace(/[^0-9]/g,'');" onkeyup="hitung_diskon()" onchange="hitung_diskon()">
                    <div class="input-group-append">
                      <span class="input-group-addon">%</span>
                    </div>
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <div class="col-lg-4">
                    <span>Memo</span>
                  </div>
                  <div class="col-lg-8">
                     <textarea id="memo" class="form-control"></textarea>
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <div class="col-lg-4">
                    <span>Total Setelah Diskon</span>
                  </div>
                  <div class="col-lg-8">
                    <p id="setelah_diskon">100.000.000</p>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="process_nota">Ya</button>
              </div>
          </div>
      </div>
    </div>
    <!-- end of modal invoice -->

    <!-- Modal Untuk Notice Process Gudang -->
    <div class="modal fade" id="modal_process_gudang" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal">Proses Gudang</span>
                  <input type="hidden" id="id_process_gudang">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body text-center">
                <span id="note_process_gudang"></span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_process_gudang">Ya</button>
              </div>
          </div>
      </div>
    </div>
    <!-- End of Notice Process Gudang -->

    <!-- Modal Untuk Notice Process Invoice Awal -->
    <div class="modal fade" id="modal_process_invoice_awal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal">Proses Invoice ke Gudang</span>
                  <input type="hidden" id="id_process_invoice_awal">
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body text-center">
                <span id="note_process_invoice_awal"></span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_process_invoice_awal">Ya</button>
              </div>
          </div>
      </div>
    </div>
    <!-- End of Notice Process Invoice Awal -->

    <!-- Modal untuk Note Status Tolak -->
    <div class="modal fade" id="modal_status_tolak" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal"></span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" id="catatan_tolak">
                <input type="hidden" name="purchaseorder_po_tolak_id" id="purchaseorder_po_tolak_id" class="form-control">
                <input type="hidden" name="status_po_tolak_id" id="status_po_tolak_id" class="form-control">
                <span><h5>Catatan Penolakan </h5>
                  <textarea name="note_tolak" id="note_tolak" cols="50" rows="3"></textarea>
                </span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_status_tolak">Ya</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modal_status_tolak_new_adj" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal"></span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" id="catatan_tolak">
                <input type="hidden" name="purchaseorder_new_adj_id" id="purchaseorder_new_adj_id" class="form-control">
                <input type="hidden" name="status_new_adj_id" id="status_new_adj_id" class="form-control">
                <h5>Tipe Penolakan </h5>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_new_adj" id="lainlain_new_adj" value="1" >
                  <label class="form-check-label" for="lainlain">1. Lain-Lain</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_new_adj" id="salahharga_new_adj" value="2" checked>
                  <label class="form-check-label" for="salahharga">2. Salah Harga</label>
                </div>
                <span><h5>Catatan Penolakan </h5>
                  <textarea name="note_tolak" id="note_tolak_new_adj" cols="50" rows="3"></textarea>
                </span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_status_tolak_new_adj">Ya</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modal_status_tolak_gudang" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal"></span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" id="catatan_tolak">
                 <input type="hidden" name="purchaseorder_tolak_gudang_id" id="purchaseorder_tolak_gudang_id" class="form-control">
                 <input type="hidden" name="status_tolak_gudang_id" id="status_tolak_gudang_id" class="form-control">
                <h5>Tipe Penolakan </h5>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_gudang" id="lainlain_gudang" value="1" >
                  <label class="form-check-label" for="lainlain">1. Lain-Lain</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_gudang" id="salahharga_gudang" value="2" checked>
                  <label class="form-check-label" for="salahharga">2. Salah Harga</label>
                </div>
                <span><h5>Catatan Penolakan </h5>
                  <textarea name="note_tolak" id="note_tolak_gudang" cols="50" rows="3"></textarea>
                </span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_status_tolak_gudang">Ya</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modal_status_tolak_invoice" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <span id="title_modal"></span>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body" id="catatan_tolak">
                <input type="hidden" name="purchaseorder_tolak_invoice_id" id="purchaseorder_tolak_invoice_id" class="form-control">
                <input type="hidden" name="status_tolak_invoice_id" id="status_tolak_invoice_id" class="form-control">
                <h5>Tipe Penolakan </h5>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_invoice" id="lainlain_invoice" value="1" >
                  <label class="form-check-label" for="lainlain">1. Lain-Lain</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="type_tolak_invoice" id="salahharga_invoice" value="2" checked>
                  <label class="form-check-label" for="salahharga">2. Salah Harga</label>
                </div>
                <span><h5>Catatan Penolakan </h5>
                  <textarea name="note_tolak" id="note_tolak_invoice" cols="50" rows="3"></textarea>
                </span>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Tidak</button>
                  <button class="btn btn-success btn-sm" type="submit" id="simpan_status_tolak_invoice">Ya</button>
              </div>
          </div>
      </div>
    </div>

    <!-- Modal untuk update Expedisi -->
    <div class="modal fade" id="modal_expedisi" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
              <span id="title_modal">Expedisi</span>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <input type="hidden" id="expedisiid" value="">
          <div class="modal-body" id="gudang">
            <select name="expedisi" class="form-control" id="expedisi">
            </select>
          <div class="modal-footer">
              <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Cancel</button>
              <button class="btn btn-success btn-sm" type="submit" id="simpan_expedisi">Simpan</button>
          </div>
        </div>
      </div>
    </div>
</div>
</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.js" integrity="sha512-w5udtBztYTK9p9QHQR8R1aq8ke+YVrYoGltOdw9aDt6HvtwqHOdUHluU67lZWv0SddTHReTydoq9Mn+X/bRBcQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script type="text/javascript">
        var table,tabledata,table_index;
        $('.formatTgl').datepicker({
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: false,
            calendarWeeks: true,
            autoclose: true,
            format: "dd-mm-yyyy"
        });
        $(document).ready(function(){
            $(".select2").select2({allowClear: true});

            $.ajaxSetup({
               headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
            });

            table= $('#table1').DataTable({
                "processing": true,
                "serverSide": true,
                "stateSave"  : true,
                "pageLength": 25,
                "select" : true,
                "responsive": true,
                "dom": '<"html5">lftip',
                "ajax":{
                            "url": "{{ route("purchaseorder.getdata") }}",
                            "dataType": "json",
                            "type": "POST",
                                data: function ( d ) {
                                d._token= "{{csrf_token()}}";
                                d.filter_perusahaan = $('#perusahaan').val();
                                d.filter_member     = $('#customer').val();
                                d.type              = $('#type').val();
                                d.type_gudang       = $('#type_gudang').val();
                            }
                        },

                "columns": [

                    {
                        "data": "no",
                        "orderable" : false,
                    },

                    { "data": "rpo", "orderable" : false,},
                    { "data": {
                        customer: "customer",
                        encid: "enc_id"
                    },
                        "render": function(data, type, row){
                            return '<a href="#modal_image_produk" id="detailpo" data-id="'+data.enc_id+'" role="button" data-toggle="modal"> '+ data.customer +' </a>'
                        }
                        },
                    { "data": "tgl_po", "orderable" : false, },
                    { "data": "action_status", "orderable" : false, },
                    { "data": "status", "orderable" : false,},
                    { "data": {
                            expedisi: "expedisi",
                            enc_id: "enc_id",
                            "orderable" : false,
                        },
                        "render": function(data, type, row){
                        var namaexpedisi = data.expedisi;
                            if (namaexpedisi.length > 20) {
                            namaexpedisi = namaexpedisi.substr(0,20)+'...';
                            }
                            return '<a href="#modal_expedisi" id="detailexpedisi" data-id="'+data.enc_id+'" role="button" data-toggle="modal" class="btn btn-success"> '+ namaexpedisi +' </a>'
                        }
                        },


                    { "data": "total","className" : "text-right" , "orderable" : false,},
                    { "data" : "action",
                        "orderable" : false,
                        "className" : "text-center",
                    },
                ],
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari data",
                    emptyTable: "Belum ada data",
                    info: "Menampilkan data _START_ sampai _END_ dari _MAX_ data.",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data.",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    loadingRecords: "Loading...",
                    processing: "Mencari...",
                    paginate: {
                        "first": "Pertama",
                        "last": "Terakhir",
                        "next": "Sesudah",
                        "previous": "Sebelum"
                    },
                }
            });

            tabledata = $('#orderData').DataTable({
                dom     : 'lrtp',
                paging    : false,
                columnDefs: [ {
                        "targets": 'no-sort',
                        "orderable": false,
                } ]
            });

            $('#search-data').click(function(){
                table.ajax.reload(null, false);
            });

            $('#refresh').click(function(){
                table.ajax.reload(null, false);
            });

            table.on('select', function ( e, dt, type, indexes ){
                table_index = indexes;
                var rowData = table.rows( indexes ).data().toArray();
            });
        });

        $(document.body).on("keydown", function(e){
            ele = document.activeElement;
            if(e.keyCode==38){
                table.row(table_index).deselect();
                table.row(table_index-1).select();
            }
            else if(e.keyCode==40){

                table.row(table_index).deselect();
                table.rows(parseInt(table_index)+1).select();
                console.log(parseInt(table_index)+1);

            }
            else if(e.keyCode==13){

            }
        });
 </script>
 <script>
    function change_type(type){
        $('#type').val(type);
        $('#type_gudang').val("");
        table.ajax.reload(null, false);
    }

    function change_type_gudang(type,idgudang){
        $('#type').val(type);
        $('#type_gudang').val(idgudang);
        table.ajax.reload(null, false);
    }

    function updatepokrntolak(id_po, setvalue){
        var data = $(`#${setvalue}_${id_po}`).val();
        if(data < 0){
            $(`#${setvalue}_${id_po}`).val(0);
        }else{
            $.ajax({
                type: 'POST',
                url: "{{route('purchaseorder.updatepokrnditolak')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                data:{
                    'id_po': id_po,
                    'field': setvalue,
                    'value': data
                },
                dataType: "json",
                success: function(response){
                    console.log(response)
                    if(response.success){
                        toastr.success(response.msg,'Yes');
                        $(`#hargatotalsebelumdiskon_${id_po}`).html(response.totalsebelumdiskon);
                        $(`#unitsetelahdiskon_${id_po}`).html(response.unitsetelahdiskon);
                        $(`#hargasetelahdiskon_${id_po}`).html(response.hargasetelahdiskon);
                        $(`#total`).html(response.total_price);
                        $(`#total_price`).html(response.total_price);
                        $(`#subTotal`).html(response.subTotal);
                        table.ajax.reload(null, false);
                        return false;
                    }else{
                        toastr.error(response.msg,'Ups');
                    }
                }
            });
        }
    }

    function updatepo(id_po, setvalue){
        var data = $(`#${setvalue}_${id_po}`).val();
        if(data < 0){
            $(`#${setvalue}_${id_po}`).val(0);
        }else{
            $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.updatepo')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            data:{
                'id_po': id_po,
                'field': setvalue,
                'value': data
            },
            success: function(response){
                console.log(response);
                if(response.qtykirim.success){
                    toastr.success(`${response.qtykirim.description} ${response.qtykirim.alternate}`,'Yes');
                    $(`#${setvalue}_${id_po}`).val(response.qtykirim.value);
                    table.ajax.reload(null, false);
                }else{
                    toastr.success(response.msg,'Yes');
                }
            }
            });
        }
    }

    function change_status(status, id_po){
        if(status == 2){
            $('#modal_status_tolak').modal("show")
            $('#note_tolak').val("");
            $('#purchaseorder_po_tolak_id').val(id_po);
            $('#status_po_tolak_id').val(status);
        //     $(document).on('click','#simpan_status_tolak', function(){
        //         var note = $('#note_tolak').val();
        //         if(note==""){
        //             Swal.fire('Ups',"Catatan Penolakan wajib di isi.",'info');
        //             return false;
        //         }else{
        //             status_po_tolak(status, id_po, note,1);
        //         }
        //    })
        }else if(status == 1){
            $.ajax({
                type: 'POST',
                url : "{{route('purchaseorder.status_po')}}",
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                dataType: "json",
                beforeSend: function () {
                    Swal.showLoading();
                },
                data:{
                    'id_po': id_po,
                    'status': status
                },
                success: function(response){
                    if (response.code == 200) {
                        Swal.fire('Yes',response.msg,'info');
                        $('#modal_status_tolak').modal("hide")
                        $('#note_tolak').val("");
                        table.ajax.reload(null, true);
                    } else {
                        $('#modal_status_tolak').modal("hide");
                        $('#note_tolak').val("");
                        Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                    }
                },
            });
        }
    }

    $("#simpan_status_tolak").click(function( event ) {
        event.preventDefault();
        $('#simpan_status_tolak').addClass("disabled");
        var note              = $('#note_tolak').val();
        var id_po_tolak       = $('#purchaseorder_po_tolak_id').val();
        var status            = $('#status_po_tolak_id').val();
        if(note==""){
            Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
            $('#simpan_status_tolak').removeClass("disabled");
            return false;
        }else{
            status_po_tolak(status, id_po_tolak, note,1);
        }
    });

    function change_status_invoice_awal(status,id_po){
        if(status == 2){
            //DI TOLAK INVOICE AWAL
            $('#modal_status_tolak_new_adj').modal("show");
            $('#note_tolak_new_adj').val("");
            $('#purchaseorder_new_adj_id').val(id_po);
            $('#status_new_adj_id').val(status);

            // $('#modal_status_tolak_new_adj #salahharga_new_adj').prop('checked', true);
            // $(document).on('click','#simpan_status_tolak_new_adj', function(event){
            //       event.preventDefault();
            //       $('#simpan_status_tolak_new_adj').addClass("disabled");
            //       var type = $("#modal_status_tolak_new_adj input[name=type_tolak_new_adj]:checked").val();
            //       var note = $('#note_tolak_new_adj').val();
            //       var id_po_new_adj_inv = $('#purchaseorder_new_adj_id').val();
            //       if(note==""){
            //           Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
            //           $('#simpan_status_tolak_new_adj').removeClass("disabled");
            //       }else{
            //           status_invoice_tolak_awal(status, id_po_new_adj_inv, note,type);
            //       }
            // });
        }else if(status == 1){
            $('#modal_process_invoice_awal').modal("show")
            $('#id_process_invoice_awal').val(id_po);
            $('#note_process_invoice_awal').html("Apakah Anda ingin memproses PO ke Gudang?");
        }
    }

    $("#simpan_status_tolak_new_adj").click(function( event ) {
        event.preventDefault();
        $('#simpan_status_tolak_new_adj').addClass("disabled");
        var type              = $("#modal_status_tolak_new_adj input[name=type_tolak_new_adj]:checked").val();
        var note              = $('#note_tolak_new_adj').val();
        var id_po_new_adj_inv = $('#purchaseorder_new_adj_id').val();
        var status            = $('#status_new_adj_id').val();

        if(note==""){
            Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
            $('#simpan_status_tolak_new_adj').removeClass("disabled");
            return false;
        }else{
            status_invoice_tolak_awal(status, id_po_new_adj_inv, note,type);
        }
    });

    function change_status_gudang(status, id_po){
        if(status == 2){
            //DI TOLAK GUDANG
            $('#modal_status_tolak_gudang').modal("show");
            $('#note_tolak_gudang').val("");
            $('#purchaseorder_tolak_gudang_id').val(id_po);
            $('#status_tolak_gudang_id').val(status);
            // $(document).on('click','#simpan_status_tolak_gudang', function(){
            //     $('#simpan_status_tolak_gudang').addClass("disabled");
            //     var type = $("input[name=type_tolak_gudang]:checked").val();
            //     var note = $('#note_tolak_gudang').val();
            //     var purchaseorder_tolak_gudang_id = $('#purchaseorder_tolak_gudang_id').val();
            //     if(note==""){
            //         Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
            //         $('#simpan_status_tolak_gudang').removeClass("disabled");
            //     }else{
            //         status_gudang_tolak(status, purchaseorder_tolak_gudang_id, note,type);
            //     }
            // });
        }else if(status == 1){
            $('#modal_process_gudang').modal("show")
            $('#id_process_gudang').val();
            $('#note_process_gudang').html();
            $('#note_gudang').html();
            var purchaseorder_tolak_gudang_id = id_po;

            $.ajax({
                type: 'GET',
                url: '{{route("purchaseorder.check_gudang",[null])}}/' + purchaseorder_tolak_gudang_id,
                headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") },
                success: function(response){
                if(response.success){
                    $('#modal_status_tolak_gudang').modal("hide");
                    $('#id_process_gudang').val(id_po)
                    $('#note_process_gudang').html(`${response.message}`);
                }else{
                    $('#id_process_gudang').val(id_po)
                    $('#modal_status_tolak_gudang').modal("hide");
                    $('#note_process_gudang').html(`${response.message}`);
                }
                }
            });
        }
    }

    $("#simpan_status_tolak_gudang").click(function( event ) {
            event.preventDefault();
            $('#simpan_status_tolak_gudang').addClass("disabled");
            var type    = $("input[name=type_tolak_gudang]:checked").val();
            var note    = $('#note_tolak_gudang').val();
            var purchaseorder_tolak_gudang_id = $('#purchaseorder_tolak_gudang_id').val();
            var status = $('#status_tolak_gudang_id').val();
            if(note==""){
                Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
                $('#simpan_status_tolak_gudang').removeClass("disabled");
            }else{
                status_gudang_tolak(status, purchaseorder_tolak_gudang_id,note, type);
            }
    });

    $(document).on('click','#simpan_process_invoice_awal', function(){
        let id_po = $('#id_process_invoice_awal').val()
        $.ajax({
            type: 'POST',
            url : "{{route('purchaseorder.status_invoice_awal')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': 1
            },
            success: function(response){
                if (response.code == 200) {
                    $('#modal_process_invoice_awal').modal("hide");
                    Swal.fire('Yes',response.msg,'info');
                    table.ajax.reload(null, true);
                } else {
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            },
        });
    });

    $(document).on('click','#simpan_process_gudang_old', function(){
        let id_po = $('#id_process_gudang').val()
        $.ajax({
            type: 'POST',
            url : "{{route('purchaseorder.status_gudang')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': 1
            },
            success: function(response){
                if (response.code == 200) {
                    $('#modal_status_tolak').modal("hide");
                    $('#modal_process_gudang').modal("hide");
                    Swal.fire('Yes',response.msg,'info');
                    table.ajax.reload(null, true);
                } else {
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            },
        });
    });

    $("#simpan_process_gudang").click(function( event ) {
        event.preventDefault();
        $('#simpan_process_gudang').addClass("disabled");
        let id_po = $('#id_process_gudang').val()
        $.ajax({
            type: 'POST',
            url : "{{route('purchaseorder.status_gudang')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': 1
            },
            success: function(response){
                if (response.code == 200) {
                    $('#modal_status_tolak').modal("hide");
                    $('#modal_process_gudang').modal("hide");
                    $('#simpan_process_gudang').removeClass("disabled");
                    Swal.fire('Yes',response.msg,'info');
                    table.ajax.reload(null, true);
                } else {
                    $('#simpan_process_gudang').removeClass("disabled");
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            },
        });
    });

    function process_invoice(status, id_po){
        if(status == 1){
            let diskon  = $('#discount_invoice').val("")
            $('#modal_process_nota').modal("show");
            $('#memo').val('');
            var purchaseorder_tolak_invoice_id = id_po;
            $.ajax({
                type: 'GET',
                url: '{{route("purchaseorder.showpo",[null])}}/' + purchaseorder_tolak_invoice_id,
                headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") },
                success: function(response){
                // console.log(response)
                $('#tgl_invoice').val(`${response.date}`)
                $('#title_modal_nota').html(`Proses Nota ${response.nota}`)
                $('#total_invoice').val(formatRupiah(response.total, ''))
                $('#setelah_diskon').html(`Rp. ${formatRupiah(response.total, '')}`)
                $('#id_po_invoice').val(id_po)
                }
            });
        }else if(status == 2){
            $('#modal_status_tolak_invoice').modal("show")
            $('#note_tolak_invoice').val("");
            $('#purchaseorder_tolak_invoice_id').val(id_po);
            $('#status_tolak_invoice_id').val(status);

            // $(document).on('click','#simpan_status_tolak_invoice', function(){
            //   var note = $('#note_tolak_invoice').val()
            //   var type = $("input[name=type_tolak_invoice]:checked").val();
            //   var purchaseorder_tolak_invoice_id = $('#purchaseorder_tolak_invoice_id').val();
            //   status_invoice_tolak(status, purchaseorder_tolak_invoice_id, note,type)
            // });
        }
    }

    $("#simpan_status_tolak_invoice").click(function( event ) {
        event.preventDefault();
        $('#simpan_status_tolak_invoice').addClass("disabled");
        var note      = $('#note_tolak_invoice').val()
        var type      = $("input[name=type_tolak_invoice]:checked").val();
        var purchaseorder_tolak_invoice_id = $('#purchaseorder_tolak_invoice_id').val();
        var status    = $('#status_tolak_invoice_id').val();

        if(note==""){
            Swal.fire('Ups','Catatan Penolakan wajib diisi.','info');
            $('#simpan_status_tolak_invoice').removeClass("disabled");
            return false;
        }else{
            status_invoice_tolak(status, purchaseorder_tolak_invoice_id, note,type)
        }
    });

    $("#scan_barcode").change(function( event ) {
        event.preventDefault();
        var id_purchase = $('#modal_image_produk #id_purchase').val();
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.scan_qty_kirim')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            data:{
                'barcode'     : this.value,
                'id_purchase' : id_purchase
            },
            success: function(response){
                if (response.success) {
                    $('#scan_barcode').val("");
                    $('#qty_kirim_'+response.po_detail_id).val(response.qty_kirim);
                    toastr.success(response.message,'Yes');
                } else {
                    $('#scan_barcode').val("");
                    toastr.error(response.message,'Ups');
                }
            }
        });
    });

    $(document).on('click', '#process_nota_old', function(){
        let diskon  = $('#discount_invoice').val()
        let id_po   = $('#id_po_invoice').val()
        let tanggal_temp = $('#tgl_invoice').val()
        let memo   = $('#memo').val()
        let tanggal = tanggal_temp.split("-").reverse().join("-")
        if(diskon > 100 ){
            diskon = 100
        }else{
            diskon = diskon
        }
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.process_nota')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            data:{
                'id_po': id_po,
                'tanggal': tanggal,
                'diskon' : diskon,
                'memo' : memo,
            },
            success: function(response){
                if (response.success) {
                    Swal.fire('Yes',response.message,'info');
                    $('#modal_process_nota').modal("hide");
                    table.ajax.reload(null, true);
                } else {
                    $('#modal_process_nota').modal("hide");
                    Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
                    table.ajax.reload(null, true);
                }
            }
        });
    })

    $("#process_nota").click(function( event ) {
        event.preventDefault();
        $('#process_nota').addClass("disabled");
        let diskon  = $('#discount_invoice').val()
        let id_po   = $('#id_po_invoice').val()
        let tanggal_temp = $('#tgl_invoice').val()
        let memo   = $('#memo').val()
        let tanggal = tanggal_temp.split("-").reverse().join("-")
        if(diskon > 100 ){
            diskon = 100
        }else{
            diskon = diskon
        }
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.process_nota')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            data:{
                'id_po': id_po,
                'tanggal': tanggal,
                'diskon' : diskon,
                'memo' : memo,
            },
            success: function(response){
                if (response.success) {
                    Swal.fire('Yes',response.message,'info');
                    $('#modal_process_nota').modal("hide");
                    $('#process_nota').removeClass("disabled");
                    table.ajax.reload(null, true);
                } else {
                    $('#modal_process_nota').modal("hide");
                    $('#process_nota').removeClass("disabled");
                    Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
                    table.ajax.reload(null, true);
                }
            }
        });
    });

    function status_po_tolak(status, id_po, note,type){
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.status_po')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': status,
                'note' : note,
                'type' : type,
            },
            success: function(response){
                if (response.code == 200) {
                    Swal.fire('Yes',response.msg,'info');
                    $('#modal_status_tolak').modal("hide");
                    $('#nota_tolak').val("");
                    $('#purchaseorder_po_tolak_id').val("");
                    $('#status_po_tolak_id').val("");
                    table.ajax.reload(null, true);
                } else {
                    $('#modal_status_tolak').modal("hide");
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            }
        })
    }

    function status_gudang_tolak(status, id_po, note,type){
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.status_gudang')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': status,
                'note' : note,
                'type' : type,
            },
            success: function(response){
                if (response.code == 200) {
                    Swal.fire('Yes',response.msg,'info');
                    $('#simpan_status_tolak_gudang').removeClass("disabled");
                    $('#modal_status_tolak_gudang').modal("hide");
                    $('#note_tolak_gudang').val("");
                    $('#purchaseorder_tolak_gudang_id').val("");
                    $('#status_tolak_gudang_id').val("");
                    table.ajax.reload(null, true);
                } else {
                    $('#modal_status_tolak_gudang').modal("hide");
                    $('#simpan_status_tolak_gudang').removeClass("disabled");
                    $('#note_tolak_gudang').val("");
                    $('#purchaseorder_tolak_gudang_id').val("");
                    $('#status_tolak_gudang_id').val("");
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            }
        });
    }

    function status_invoice_tolak_awal(status, id_po, note,type){
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.status_invoice_awal')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': status,
                'note' : note,
                'type' : type,
            },
            success: function(response){
                if (response.code == 200) {
                    Swal.fire('Yes',response.msg,'info');
                    $('#modal_status_tolak_new_adj').modal("hide");
                    $('#note_tolak_new_adj').val("");
                    $('#purchaseorder_new_adj_id').val("");
                    $('#status_new_adj_id').val("");
                    $('#simpan_status_tolak_new_adj').removeClass("disabled");
                    table.ajax.reload(null, true);
                } else {
                    $('#modal_status_tolak_new_adj').modal("hide");
                    $('#purchaseorder_new_adj_id').val("");
                    $('#status_new_adj_id').val("");
                    $('#simpan_status_tolak_new_adj').removeClass("disabled");
                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            }
        })
    }

    function status_invoice_tolak(status, id_po, note,type){
        $.ajax({
            type: 'POST',
            url: "{{route('purchaseorder.status_invoice')}}",
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            dataType: "json",
            beforeSend: function () {
                Swal.showLoading();
            },
            data:{
                'id_po': id_po,
                'status': status,
                'note' : note,
                'type' : type,
            },
            success: function(response){
                if (response.code == 200) {
                    Swal.fire('Yes',response.msg,'info');
                    $('#modal_status_tolak_invoice').modal("hide");
                    $('#note_tolak_invoice').val("");
                    $('#status_tolak_invoice_id').val("");
                    $('#purchaseorder_tolak_invoice_id').val("");

                    table.ajax.reload(null, true);
                } else {
                    $('#modal_status_tolak_invoice').modal("hide");
                    $('#note_tolak_invoice').val("");
                    $('#status_tolak_invoice_id').val("");
                    $('#purchaseorder_tolak_invoice_id').val("");

                    Swal.fire('Ups','Maaf Terjadi Kesalahan dalam server','info');
                }
            }
        })
    }

    $(document).on("click", "#detail_po", function () {
        var idpo = $(this).data('id');
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("purchaseorder.detail") }}',
            data: {
                enc_id: idpo
            },
            success: function(response){

            $('#list_produk').find('tr').remove();
            let po = response.po
            let podetail = response.po_detail;
            let detailpoformat = response.detailpo;
            let id = po.purchase.id
            let print = '{{route("purchaseorder.print",[null])}}/' +id
            $('#print').attr('href',`${print}`)
            let tabledata = ''
            $('#title_modal').html(`PURCHASING ORDER # ${ po.purchase.no_nota }`)
            $('#nota').html(`PURCHASING ORDER # ${ po.purchase.no_nota }`)
            $('#nama_perusahaan').html(`${ po.perusahaan.name }`)
            $('#alamat_perusahaan').html(`${ po.perusahaan.alamat }`)
            $('#kota_perusahaan').html(`${ po.perusahaan.kota }`)
            $('#telp_perusahaan').html(`${ po.perusahaan.contact }`)
            $('#member_tujuan').html(`${ po.member.name }`)
            $('#id_purchase').val(po.purchase.id)
            $('#tanggal_pesan').html(`${ po.purchase.ttl_pesan }`)
            $('#total_price').html(`Rp. ${ po.purchase.total }`)
            $('#note').val(po.purchase.note)
            $('#note_gudang').html(po.purchase.note)
            $('#subTotal').html('Rp. '+po.purchase.sub_total)
            $('#total').html('Rp. '+po.purchase.total)
            $('#header-table').html(response.header)
            if(response.dis=='readonly'){
                $("#note").prop('readonly',true)
            }else{
                $("#note").prop('readonly',false)
            }
            if(response.buttonsimpan=='disable'){
                $(".simpancatatan").hide();
            }else{
                $(".simpancatatan").show();
            }

            for (let i = 0; i < podetail.length; i++) {
                let price = new Intl.NumberFormat('en-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 2
                })

                tabledata += podetail[i]
            }

            $('#list_produk').append(tabledata);
            $.each( detailpoformat, function( index, value ){
                    //console.log(value['id']);
                    new AutoNumeric('#price_'+value['id']+'', {
                        currencySymbol : '',
                        decimalCharacter : ',',
                        digitGroupSeparator : '.',
                        decimalPlaces:'0',
                    });
            });


            }
        })
    });

    $(document).on("click", "#detailpo", function () {
        var idpo = $(this).data('id');
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("purchaseorder.detail") }}',
            data: {
                enc_id: idpo
            },
            success: function(response){
            $('#list_produk').find('tr').remove();
            let po = response.po
            let podetail = response.po_detail
            let id = po.purchase.id
            let print = '{{route("purchaseorder.print",[null])}}/' +id
            $('#print').attr('href',`${print}`)
            let tabledata = ''
            $('#title_modal').html(`PURCHASING ORDER # ${ po.purchase.no_nota }`)
            $('#nota').html(`PURCHASING ORDER # ${ po.purchase.no_nota }`)
            $('#perusahaan').html(`${ po.perusahaan.name }`)
            $('#alamat_perusahaan').html(`${ po.perusahaan.alamat }`)
            $('#telp_perusahaan').html(`${ po.perusahaan.contact }`)
            $('#member_tujuan').html(`${ po.member.name }`)
            $('#id_purchase').val(po.purchase.id)
            $('#tanggal_pesan').html(`${ po.purchase.ttl_pesan }`)
            $('#total_price').html(`Rp. ${ po.purchase.total }`)
            $('#note').val(po.purchase.note)
            $('#note_gudang').html(po.purchase.note)
            $('#subTotal').html('Rp. '+po.purchase.sub_total)
            $('#total').html('Rp. '+po.purchase.total)
            $('#header-table').html(response.header)
            for (let i = 0; i < podetail.length; i++) {
                let price = new Intl.NumberFormat('en-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 2
                })
                tabledata += podetail[i]
            }
            $('#list_produk').append(tabledata)
            }
        })
    });

    $(document).on("click","#simpan",function(){
        let idpo = $('#id_purchase').val()
        let note = $('#note').val()
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("purchaseorder.note") }}',
            data: {
                idpo: idpo,
                note: note
            },
            success: function(response){
            if (response.success) {
                Swal.fire('Yes',response.msg,'info');
                table.ajax.reload(null, true);
            } else {
                Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
            }
            }
        })
    });

    $(document).on("click", "#detailexpedisi", function(){
        var idpo = $(this).data('id');
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
        type: 'POST',
        headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
        url: '{{ route("purchaseorder.expedisi") }}',
        success: function(response){
            let expedisi = response.data;
            let selection = '';
            selection = `<option value="">Pilih Expedisi</option>`
            for (let index = 0; index < expedisi.length; index++) {
            selection += `<option value=" ${expedisi[index].id} " >${expedisi[index].name}</option>`
            }
            $('#expedisi').html(selection)
            $('#expedisiid').val(idpo)
        }
        })
    });

    $(document).on("click", "#simpan_expedisi", function(){
        var idpo = $('#expedisiid').val()
        var idexp = $('#expedisi').val()
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
            url: '{{ route("purchaseorder.simpanexpedisi") }}',
            data:{
                idpo: idpo,
                idexp: idexp
            },
            success: function(response){
                if (response.success) {
                    $('#modal_expedisi').modal('hide')
                    Swal.fire('Yes',response.msg,'info');
                    table.ajax.reload(null, true);
                } else {
                    Swal.fire('Ups','Sedang Terjadi Masalah pada System','info');
                }
            }
        });
    });

    function hitung_diskon(){
        let diskon = $('#discount_invoice').val()
        let total = $('#total_invoice').val()
        if(diskon > 100){
        $('#discount_invoice').val(100)
            diskon = 100
        }else{
            $('#discount_invoice').val(diskon)
            diskon = diskon
        }

        let totaldiskon = diskon * ( total.replaceAll(".","") / 100);
        let hasildiskon = Math.round(total.replaceAll(".","") - totaldiskon);
        $('#setelah_diskon').html(`Rp. ${formatRupiah(hasildiskon, '')}`)
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
