@extends('layouts.layout')

@section('title', 'Purchase Order Batal')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Purchase Order Batal</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Purchase Order Batal</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Refresh Data"><span class="fa fa-refresh"></span></button>
    </div>
</div>


<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="row">
      <div class="col-lg-12">
          <div class="ibox">
              <div class="ibox-content">
                <div class="hr-line-dashed"></div>
                <form id="submitData" name="submitData">
                <div class="form-group row text-right" style="margin-right:20px;">
                  <div class="col-sm-12 error-text">
                  @can('purchasebatal.delete')
                  <button class="btn btn-danger fa fa-delete text-white" id="deletedata">Hapus</button>
                  </div>
                @endcan
                </form>
                </div>
                <div class="hr-line-dashed"></div>
                  <div class="table-responsive">

                    <table id="table1" class="table display table-bordered" >
                        <thead>
                        <tr>
                            <th width="10px;">No</th>
                            <th width="320px;">#</th>
                            <th>Customer</th>
                            <th>Tanggal Dibuat</th>
                            <th>Status</th>
                            <th>Expedisi</th>
                            <th>Total (Rp.)</th>
                            <th class="text-center"><br> <input type="checkbox" id="selectall"></th>
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
    @include('backend.purchase.detail')
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
           "stateSave"  : true,
           "responsive": true,
           "dom": '<"html5">lftip',
           "ajax":{
                    "url": "{{ route("purchasebatal.getdata") }}",
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

               { "data": "rpo"},
               { "data": {
                 customer: "customer",
                 note: "note"
               },
                  "render": function(data, type, row){
                    // return '<span>'+data.customer+'</span><div class="label label-danger">'+data.note+'</div>'
                    return '<span>'+data.customer+'</span>'
                  }
                },
               { "data": "tgl_po" },
               { "data": "status" },
               { "data": "expedisi" },
               { "data": "total" },
               { "data" : "action",
                 "orderable" : false,
                 "className" : "text-center",
                 "render": function(data, type, row){
                    return '<div><label> <input type="checkbox" name="pobatal" value="'+ data +'"></label></div>'
                 }
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
    $(document).on('click','#selectall', function(){
        $('input:checkbox').not(this).prop('checked', this.checked);
    })

    $(document).on('click','#deletedata', function(){
        let alldata = []
        var token = '{{ csrf_token() }}'
        $("input:checkbox[name=pobatal]:checked").each(function(){
            alldata.push($(this).val())
        });
        if(alldata != ''){
          $.ajax({
            type: 'POST',
            headers: {'X-CSRF-TOKEN': token},
            url: '{{ route("purchasebatal.delete") }}',
            data: {
                datapo: alldata
            },
            success: function(response){
                if(response.success){
                    Swal.fire('Yes',response.msg,'success');
                    table.ajax.reload(null, false);
                }else{

                    Swal.fire(response.code,"Terjadi kesalahan pada sistem.",'Info');
                    table.ajax.reload(null, false);
                }
            }
        })
        }else{
          Swal.fire("Ups","Silahkan centang data yang ingin di hapus terlebih dahulu.",'Info');
          return false;
        }



    })
</script>
@endpush
