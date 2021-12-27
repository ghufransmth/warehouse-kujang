@extends('layouts.layout')
@section('title', 'Perusahaan')
@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Master Perusahaan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Master Perusahaan</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('perusahaan.tambah')
            <a href="{{ route('perusahaan.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan

    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th width="10px;">No</th>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Kota</th>
                            <th>Status</th>
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
</div>
<!-- Modal -->
<div class="modal fade" id="modal_gudang" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span id="title_modal"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <input type="hidden" id="gudangdataid" value="">
            <div class="modal-body" id="gudang">

            </div>
            <div class="modal-footer">
                <button class="btn btn-danger btn-sm" type="submit" data-dismiss="modal">Cancel</button>
                @can('perusahaan.simpangudang')
                <button class="btn btn-success btn-sm" type="submit" id="simpan_gudang">Simpan</button>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script type="text/javascript">
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
                    "url": "{{ route("perusahaan.getdata") }}",
                    "dataType": "json",
                    "type": "POST",
                    data: function ( d ) {
                      d._token= "{{csrf_token()}}";
                    }
                  },

           "columns": [
               {
                 "data": "no",
                 "orderable" : false,
               },
               { "data": "kode"},
               { "data": "name"},
               { "data": "city"},
               { "data": "status"},
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

    function deletePerusahaan(e,enc_id){
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
                    url: '{{route("perusahaan.delete",[null])}}/' + enc_id,
                    headers: {'X-CSRF-TOKEN': token},
                    success: function(data){
                    if (data.status == 'success') {
                        Swal.fire('Yes',data.message,'success');
                        table.ajax.reload(null, true);
                     }else{
                       Swal.fire('Ups',data.message,'info');
                     }
                },
                error: function(data){
                    Swal.fire("Ups!", "Terjadi kesalahan pada sistem.", "error");
                }});
            }
        });
    }

    $(document).on("click", "#addgudang", function () {
        $('#gudang').html('')
        $('#gudangdataid').val('')
        var token = '{{ csrf_token() }}';
        var perusahaan = $(this).data('id');
        $.ajaxSetup({
            headers: { "X-CSRF-Token" : $("meta[name=csrf-token]").attr("content") }
        });
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': token},
            url: '{{ route("perusahaan.gudang") }}',
            data: {
                enc_id: perusahaan
            },
            success: function(response){
                // console.log(response)
                var gudang = response.datalist
                var select_gudang = ''
                for (let index = 0; index < gudang.length; index++) {
                    select_gudang += gudang[index].aksi
                }
                $('#gudang').html(select_gudang)
                $('#title_modal').html(`<h5 class="modal-title" id="exampleModalLabel"> Gudang Perusahaan ${response.perusahaan.name} </h5>`)
                $('#gudangdataid').val(response.perusahaan.id)
            }
        })
    });
    $(document).on('click','#simpan_gudang',function(){
        var gudang_id = [];
        var token = '{{ csrf_token() }}';
        var perusahaan = $('#gudangdataid').val()
        $("input:checkbox[name=gudangid]:checked").each(function(){
            gudang_id.push($(this).val())
        })
        $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': token},
            url: '{{ route("perusahaan.simpangudang") }}',
            data: {
                perusahaan_id: perusahaan,
                gudang_id: gudang_id
            },
            success: function(response){
                if(response.code == 200){
                    Swal.fire('Yes',response.message,'success');
                    $('#modal_gudang').modal('hide')
                }else{
                    Swal.fire(response.code,"Terjadi kesalahan pada sistem.",'Info');
                }
            }
        })

    })

</script>
@endpush
