@extends('layouts.layout')

@section('title', 'Manajemen Akses User')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Akses User</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('role.index')}}">Akses</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>Akses User</strong>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        
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
                        @can('role.user')
                        <form class="form-inline" method="POST" action="{{ route('role.user',$dataSet->id) }}">
                        @else
                        <form class="form-inline" method="POST" action="#">
                        @endcan

                        {{ csrf_field() }}

                        <div class="form-group mb-2 col-sm-2">
                            <label>Pilih User</label>
                        </div>
                        <div class="form-group col-sm-2 mb-2">
                            <select class="form-control" name="tambah_user" required>
                                <option value="">Pilih User</option>
                                @foreach($userObj as $row)
                                <option value="{{$row->id}}">{{$row->fullname}}</option>
                                @endforeach
                            </select>
                        </div>

                        @can('role.user')
                            <button type="submit" class="btn btn-primary mb-2">Simpan</button>
                        @endcan
                        </form>
                            <div class="table-responsive">
                            <table id="table1" class="table">
                                <thead>
                                <tr>
                                    <th class="text-left" width="5%">No</th>
                                    <th class="text-left">Nama</th>
                                    <th class="text-left"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($dataSet->user as $row)
                                    <tr>
                                        <td class="text-left">{{$loop->iteration}}.</td>
                                        <td>{{$row->fullname}}</td>
                                        <td class="text-left">
                                            @can('role.user')
                                            <form class="formDelete" action="{{ route('role.user', $dataSet->id) }}" method="post">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" name="hapus_user" value="{{ $row->id }}">
                                                <div class="btn-group">
                                                    <button type="submit" class="btn btn-danger btn-xs icon-btn md-btn-flat product-tooltip" title="Hapus"><i class="fa fa-times"></i> Hapus</button>
                                                </div>
                                            </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
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
<script type="text/javascript">
    var table,tabledata,table_index;
       $(document).ready(function(){
           
         $('#table1').DataTable({
                 "pagingType": "full_numbers",
                 "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                 responsive: true,
                 language: {
                     search: "_INPUT_",
                     searchPlaceholder: "Cari data",
                 }
             }); 
       });
 </script>
@endpush