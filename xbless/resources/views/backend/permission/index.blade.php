@extends('layouts.layout')

@section('title', 'Manajemen Modul ')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Modul</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Modul</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        <br/>

        @can('permission.tambah')
        <a href="{{ route('permission.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top" title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
              @if(session('message'))
                            <div class="alert alert-{{session('message')['status']}}">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            {{ session('message')['desc'] }}
                            </div>
                        @endif
                <div class="ibox-content">

                    <div class="table-responsive">
                        <table id="table1" class="table display table-bordered">
                        <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Slug</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                            @if(count($dataObj) > 0)
                            @foreach($dataObj as $n=>$data)
                            <tr>
                                <th scope="row">{{ ++$n }}</th>
                                <td>{{ $data->nested }}</td>
                                <td>{{ $data->name }}</td>
                                <td>{{ $data->slug }}</td>
                                <td class="text-center"> @can('permission.ubah')<a href="{{route('permission.ubah',Crypt::encrypt($data->id))}}" class="btn btn-warning btn-xs icon-btn md-btn-flat product-tooltip" title="Edit"><i class="fa fa-pencil"></i> Ubah</a>&nbsp;@endcan</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">no records</td>
                            </tr>
                        @endif
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

@endsection
@push('scripts')
<script type="text/javascript">
    var table,tabledata,table_index;
    $(document).ready(function(){
      $('#table1').DataTable({
              "pagingType": "full_numbers",
              "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
              "responsive": true,
              "dom": '<"html5">lftip',
              "stateSave"  : true,
              responsive: true,
              language: {
                  search: "_INPUT_",
                  searchPlaceholder: "Cari data",
              }
          });
    });
  </script>
@endpush
