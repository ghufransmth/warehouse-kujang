@extends('layouts.layout')

@section('title', 'Manajemen Akses')

@section('content')
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>{{($dataSet) ? 'Ubah' : 'Tambah Baru'}} Akses</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{route('role.index')}}">Akses</a>
            </li>
            <li class="breadcrumb-item active">
                <strong>{{ ($dataSet) ? 'Ubah' : 'Tambah Baru'}}</strong>
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
                        @if($dataSet)
                        @can('role.ubah')
                        <form class="form-horizontal" method="POST" action="{{route('role.ubah',$dataSet->id)}}">
                        @endcan
                        @elsecan('role.tambah')
                        <form class="form-horizontal" method="POST" action="{{route('role.tambah')}}">
                            @else
                            <form class="form-horizontal" method="POST" action="#">
                            @endcan
                            {{ csrf_field() }}
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Akses *</label>
                                <div class="col-sm-10 error-text">
                                    <input type="text" class="form-control" id="name" name="name" value="{{ $dataSet ? $dataSet->name : ''}}" required> 
                                </div>
                            </div>
                            <div class="form-group row"><label class="col-sm-2 col-form-label">Modul *</label>
                                <div class="col-sm-10 error-text">
                                    <br/>
                                    <?php echo $checkbox_loop; ?> 
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                                <div class="form-group row">
                                    <div class="col-sm-4 col-sm-offset-2">
                                        <a class="btn btn-white btn-sm" href="{{route('role.index')}}">Batal</a>
                                        @if($dataSet)
                                        @can('role.ubah')
                                        <button type="submit" class="btn btn-primary" >Simpan</button>&nbsp;
                                        @endcan
                                        @elsecan('role.tambah')
                                        <button type="submit" class="btn btn-primary">Simpan</button>&nbsp;
                                        @endcan
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
@endsection
@push('scripts')
<script src="{{ asset('assets/js/plugins/iCheck/icheck.min.js')}}"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/iCheck/1.0.2/skins/all.css" rel="stylesheet"/>
<script type="text/javascript">
    $(document).ready(function(){
        $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass   : 'iradio_minimal-blue'
      })
     
      //Flat red color scheme for iCheck
      $('input[type="checkbox"].flat-red, input[type="radio"].flat-red').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass   : 'iradio_flat-green'
      })
  });
      $(function() {
        
  
          $('.check_tree').on('ifClicked', function(e){
              var $this     = $(this),
                  checked   = $this.prop("checked"),
                  container = $this.closest("li"),
                  parents   = container.parents("li").first().find('.check_tree').first(),
                  all       = true;
  
              // Centang juga anak2nya
              container.find('.check_tree').each(function() {
                  if(checked) {
                      $(this).iCheck('uncheck');
                  }else{
                      $(this).iCheck('check');
                  }
              });
  
              // Cek sodaranya
              container.siblings().each(function() {
                  return all = ($(this).find('.check_tree').first().prop("checked") === false);
              });
  
              // Cek bapaknya
              if(checked) {
                  parents.iCheck('check');
              }
          });
  
          $('.check_tree').on('ifChanged', function(e){
                  var $this     = $(this),
                      checked   = $this.prop("checked"),
                      parents   = $this.closest("li").parents("li").first().find('.check_tree').first(),
                      all       = true;
              
                  // Cek bapaknya
                  if(checked) {
                      parents.iCheck('check');
                  }
          });
      });
      </script>
@endpush