@extends('layouts.layout')
@section('title', 'Supplier')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Master Supplier</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Master Supplier</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        {{-- @can('supplier.tambah') --}}
        <a href="{{ route('supplier.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        {{-- @endcan --}}

    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="table1" class="table display table table p-0 table-hover table-striped" style="overflow-x: auto; width:100%;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="5%">No</th>
                                    <th>Nama</th>
                                    <th class="text-center" width="50%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    var table,tabledata,table_index;
    $(document).ready(function(){
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        table= $('#table1').DataTable({
            "processing": true,
            "serverSide": true,
            "pageLength": 25,
            "select" : true,
            "responsive": true,
            "stateSave"  : true,
            "dom": '<"html5">lftip',
            "ajax":{
                "url": "{{ route("supplier.getdata") }}",
                "dataType": "json",
                "type": "POST",
                data: function(d){
                    d._token= "{{ csrf_token() }}";
                }
            },
            "columns": [
                {
                    "data": "no",
                    "orderable": false,
                },
                {
                    "data": "nama",
                },
                {
                    "data": "action",
                    "orderable": false,
                    "className": "text-center",
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
            dom : 'lrtp',
            paging : false,
            columnDefs: [{
                "targets": 'no-sort',
                "orderable": false,
            }]
        });

        $('#filter').click(function(){
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

function deleteData(e,enc_id){
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
                console.log(result)
                if (result.value) {
                    $.ajaxSetup({
                        headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
                    });
                    $.ajax({
                        type: 'delete',
                        url: '{{route("supplier.delete",[null])}}/' + enc_id,
                        headers: {'X-CSRF-TOKEN': token},
                        success: function(data){
                        console.log(data)
                        if (data.status == 'success') {
                            Swal.fire('Success',data.message,'success');
                            table.ajax.reload(null, true);
                        }else{
                        Swal.fire('Warning',data.message,'info');
                        }
                    },
                    error: function(data){
                        console.log(data);
                        Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                    }});
                }
            });
      }

$(document.body).on("keydown", function(e){
    ele = document.activeElement;
    if(e.keyCode==38){
          table.row(table_index).deselect();
          table.row(table_index-1).select();
        }else if(e.keyCode==40){
            table.row(table_index).deselect();
            table.rows(parseInt(table_index)+1).select();
            console.log(parseInt(table_index)+1);
        }else if(e.keyCode==13){

        }
});
</script>
@endpush
