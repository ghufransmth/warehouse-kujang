@extends('layouts.layout')
@section('title', 'LAPORAN HISTORY STOK')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
/* .pagination{
    display:none !important;
}
.dataTables_length {
    display:none !important;
} */
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>LAPORAN STOK</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>LAPORAN STOK</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                <form id="submitData" name="submitData">
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Cari Kode / Nama Produk : </label>
                                <div class="col-sm-8 error-text">
                                    {{-- <input type="text" class="form-control" id="filter_keyword" name="filter_keyword" value="{{$selectedfilterkeyword}}"> --}}
                                    <select class="form-control select2 selectProduct" id="filter_keyword" name="filter_keyword" required>
                                        <option value="0"> Semua Produk</option>
                                        @foreach($product as $key => $row)
                                            <option value="{{$row->id}}" {{$selectedfilterkeyword == $row->id ? 'selected' : ''}}>{{strtoupper($row->product_code)}} | {{strtoupper($row->product_name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Kategori :</label>
                                <div class="col-sm-8 error-text">
                                    <select class="form-control select2" id="kategori" name="kategori" multiple="multiple">
                                        @foreach($kategori as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedkategori == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->cat_name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Dari Tanggal : </label>
                                <div class="col-sm-3 error-text">

                                <input type="text" class="form-control formatTgl" id="filter_tgl_start" name="filter_tgl_start"  value="{{$tgl_start}}">
                                </div>
                                <label class="col-sm-2 col-form-label">Sampai Tanggal : </label>
                                <div class="col-sm-3 error-text">
                                    <input type="text" class="form-control formatTgl" id="filter_tgl_end" name="filter_tgl_end" value="{{$tgl_end}}">
                                </div>


                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Perusahaan : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="0">Semua Perusahaan</option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Gudang : </label>
                                <div class="col-sm-3 error-text">
                                    {{-- <select class="form-control select2" id="gudang" name="gudang" multiple="multiple"> --}}
                                    <select class="form-control select2" id="gudang" name="gudang">
                                        <option value="0">-- Pilih Gudang --</option>
                                        @foreach($gudang as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedgudang == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                &nbsp;

                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            <div class="form-group row">
                                @can('reportbarangmasuk.excel')
                                <div class="col-xs-3">
                                &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="button" id="ExportExcel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                @endcan
                                @can('reportbarangmasuk.print')
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="button" id="Print"><span class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                                @endcan
                                @can('reportbarangmasuk.pdf')
                                <div class="col-xs-3">
                                    <button class="btn btn-danger" type="button" id="ExportPdf"><span class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                                </div>
                                @endcan
                            </div>
                            </form>

                            <div class="hr-line-dashed"></div>

                    <div class="table-responsive">
                        <h3 id="label-table">History Stok {{$selectedfilterkeyword ?? ''}} Periode {{date('d M Y', strtotime($tgl_start))}} - {{date('d M Y', strtotime($tgl_end))}} </h3>
                        <table id="table1" class="table display table-bordered">
                            <thead>
                            <tr>
                                <th width="5%" >No</th>
                                <th>No Invoice</th>
                                <th>No Transaksi</th>
                                <th>Tanggal</th>
                                <th>Stock Code</th>
                                <th>Gudang</th>
                                <th>Keterangan</th>
                                <th>Qty</th>
                                <th>Satuan</th>
                            </tr>
                            </thead>
                            <tbody>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7">
                                        <span id="stock-periode">Stok Periode {{$selectedfilterkeyword ?? ''}} {{date('d M Y', strtotime($tgl_start))}} - {{date('d M Y', strtotime($tgl_end))}}</span>
                                    </th>
                                    <th colspan="2">
                                         <span id="sum-stock-periode">0</span>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7">
                                        SUMMARY
                                    </th>
                                    <th colspan="2">

                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7">
                                        <span id="label-stock-cut-off">STOCK CUT OFF {{$selectedfilterkeyword ?? ''}} SAMPAI TANGGAL 31 DESEMBER {{date('Y', strtotime('-1 year'))}}</span>
                                    </th>
                                    <th colspan="2">
                                        <span id="stock-cut-off-one-year">0</span>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7">
                                        <span id="stock-periode-summary">Stok {{$selectedfilterkeyword ?? ''}} Periode {{date('d M Y', strtotime($tgl_start))}} - {{date('d M Y', strtotime($tgl_end))}}</span>

                                    </th>
                                    <th colspan="2">
                                        <span id="sum-stock-periode-summary">0</span>
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7">
                                        <span id="total-stock-periode">Total Stok {{$selectedfilterkeyword ?? ''}} Periode {{date('d M Y', strtotime($tgl_start))}} - {{date('d M Y', strtotime($tgl_end))}}</span>
                                    </th>
                                    <th colspan="2">
                                        <span id="total-summary-stock-periode"></span>
                                    </th>
                                </tr>

                            </tfoot>
                        </table>
                    </div>



                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js" crossorigin="anonymous"></script>
<script type="text/javascript">
    var table;

       $(document).ready(function(){
            let qty_stock = 0;
            $('.select2').select2();
            $('.formatTgl').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                format: "dd-mm-yyyy"
            });
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
            $.ajaxSetup({
                headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
            });

            table= $('#table1').DataTable({
                "processing": true,
                "serverSide": true,
                "pageLength": 10,
                "select" : true,
                "bFilter":false,
                "dom": '<"html5">lftip',
                "ajax":{
                        "url": "{{ route("reportstok.getdata") }}",
                        "dataType": "json",
                        "type": "POST",
                        data: function ( d ) {
                            d._token= "{{csrf_token()}}";
                            d.filter_kategori         = $('#kategori').val();
                            d.filter_status           = $('#status').val();
                            d.filter_keyword          = $('#filter_keyword').val();
                            d.filter_tgl_start        = $('#filter_tgl_start').val();
                            d.filter_tgl_end          = $('#filter_tgl_end').val();
                            d.filter_perusahaan       = $('#perusahaan').val();
                            d.filter_gudang           = $('#gudang').val();
                            d.filter_kategori_length  = $('#kategori').val().length;
                            d.filter_gudang_length    = $('#gudang').val().length;
                        },
                        "dataSrc": function ( json ) {
                            $('#sum-stock-periode').html(json.sum_qty);
                            $('#sum-stock-periode-summary').html(json.sum_qty);
                            $('#stock-cut-off-one-year').html(json.cut_off_qty);
                            $('#total-summary-stock-periode').html(json.total);

                            return json.data;
                        },
                    },

                "columns": [
                    {
                        "data": "no",
                        "orderable" : false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {"data":"no_invoice"},
                    {"data":"no_transaction"},
                    {"data":"dateorder"},
                    { "data": "product_code"},
                    { "data" : "gudang_name"},
                    { "data": "ket"},
                    { "data": "qty"},
                    {"data" : "satuan"}
                ],
                ordering: true,
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
                },
            });


            table.on('select', function ( e, dt, type, indexes ){
                table_index = indexes;
                var rowData = table.rows( indexes ).data().toArray();
            });

            $('#table1 tbody tr').each(function (idx) {
               $(this).children("td:eq(0)").html(idx + 1);
            });

            $('#cariData').on('click', function() {
                let productId    = $('#filter_keyword').val()
                let perusahaanId = $('#perusahaan').val()
                let gudangId     = $('#gudang').val()
                let text = ''
                

                if(productId == 0 || perusahaanId == 0 || gudangId == 0) {
                    if(productId == 0) {
                        text = 'Produk harus dipilih'
                    }else if(perusahaanId == 0) {
                        text = 'Perusahaan harus dipilih'
                    }else if(gudangId == 0) {
                        text = 'Gudang harus dipilih'
                    }
                    Swal.fire('Oopss',`${text}`,'info');
                    return false
                }else {
                    table.ajax.reload(null, false);

                    // let product = $('#filter_keyword').val()
                    let product = $('.selectProduct').select2('data')
                    let product_name = product[0].text.split('|');
                    let prevYear = "{{date('Y',strtotime('-1 year'))}}"
                    $('#label-table').html(`History Stok ${product[0].id != "0" ? product_name[1] : ''} Periode ${moment(splitDate($('#filter_tgl_start').val())).format("DD MMM YYYY")} - ${moment(splitDate($('#filter_tgl_end').val())).format("DD MMM YYYY")}`)
                    $('#stock-periode').html(`Stok Periode ${product[0].id != "0" ? product_name[1] : ''} ${moment(splitDate($('#filter_tgl_start').val())).format("DD MMM YYYY")} - ${moment(splitDate($('#filter_tgl_end').val())).format("DD MMM YYYY")}`)
                    $('#stock-periode-summary').html(`Stok ${product[0].id != "0" ? product_name[1] : ''} Periode ${moment(splitDate($('#filter_tgl_start').val())).format("DD MMM YYYY")} - ${moment(splitDate($('#filter_tgl_end').val())).format("DD MMM YYYY")}`)
                    $('#total-stock-periode').html(`Total ${product[0].id != "0" ? product_name[1] : ''} Stok Periode ${moment(splitDate($('#filter_tgl_start').val())).format("DD MMM YYYY")} - ${moment(splitDate($('#filter_tgl_end').val())).format("DD MMM YYYY")}`)
                    // $('#label-stock-cut-off').html(`STOCK CUT OFF ${product[0].id != "0" ? product_name[1] : ''} SAMPAI TANGGAL 31 DESEMBER ${prevYear}`)
                    $('#label-stock-cut-off').html(`STOCK CUT OFF ${product[0].id != "0" ? product_name[1] : ''} SAMPAI TANGGAL ${moment(splitDate($('#filter_tgl_start').val())).subtract(1,'days').format("DD MMM YYYY")}`)
                }
            });

            $('#Print').on('click', function() {
                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){

                        window.open('{{route('reportstok.print')}}', '_blank');

                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });

            $('#ExportPdf').on('click', function() {
                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){
                    window.open('{{route('reportstok.pdf')}}', '_blank');
                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });

            $('#ExportExcel').on('click', function() {
                var jumlahdata = table.rows().count();
                if(jumlahdata > 0){
                    window.open('{{route('reportstok.excel')}}', '_blank');
                }else{
                    Swal.fire('Ups','Tidak ada data','info');
                    return false;
                }
            });

            $('.selectProduct').select2({
                placeholder: 'Semua Produk',
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
                        let results = [];
                        results.push({
                            id: 0,
                            text: "Semua Produk"
                        })
                        $.each(data, function(index, item){
                            results.push({
                                id: item.id,
                                text : item.product_code+'|'+item.product_name
                            });
                        });

                        return{
                            results: results
                        };

                    }
                }
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

       function splitDate(textDate) {
           let splitData = textDate.split('-')
           let arrData = [splitData[1], splitData[0], splitData[2]]
           return new Date(arrData.join('-'));
       }

 </script>
@endpush
