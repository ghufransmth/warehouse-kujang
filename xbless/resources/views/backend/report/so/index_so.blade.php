@extends('layouts.layout')
@section('title', 'PRINT LAPORAN SALES ORDER')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>PRINT LAPORAN SALES ORDER</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Laporan Sales Order</a>
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
                                <label class="col-sm-2 col-form-label">Pilih Perusahaan : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan</option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}">{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Filter Tanggal :</label>
                                <div class="col-sm-3 error-text">
                                        <input type="text" class="form-control formatTgl" id="filter_tgl" name="filter_tgl" value="{{date('d-m-Y')}}">
                                </div>
                                &nbsp;
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                </div>
                            </div>
                            <div class="hr-line-dashed"></div>
                            </form>
                            <div class="hr-line-dashed"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_action"  role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index: 2050 !important;">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <b><span id="title_modal">Modal Pilihan</span></b>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="ibox-content text-center">
                    <form id="submitDataModal" name="submitDataModal">    
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="hr-line-dashed"></div>
                        <div class="form-group row">
                            @can('reportso.excel')
                            <div class="col-xs-3">
                            &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="button" id="ExportExcel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                            </div>
                            @endcan
                            @can('reportso.print')
                            <div class="col-xs-3">
                                <button class="btn btn-secondary" type="button" id="Print"><span class="fa fa-print"></span> Print</button>&nbsp;
                            </div>
                            @endcan
                            @can('reportso.pdf')
                            <div class="col-xs-3">
                                <button class="btn btn-danger" type="button" id="ExportPdf"><span class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                            </div>
                            @endcan
                            
                        </div>
                        <div class="hr-line-dashed"></div>
                    </form>
                </div>
                
            </div>
        </div>
    </div> 
@endsection
@push('scripts')
<script type="text/javascript">
    var table,tabledata,table_index,tableproduct;
       $(document).ready(function(){
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
           
        $('#cariData').on('click', function() {
            if($('#perusahaan').val()==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info'); 
                return false;
            }else if($('#filter_tgl').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info'); 
                return false;
            }else{
                $.ajax({
                    type: 'POST',
                    url: '{{route('reportso.cekdata')}}',
                    data: {
                        _token: '{{csrf_token()}}',
                        perusahaan_id : $('#perusahaan').val(),
                        filter_tgl    : $('#filter_tgl').val(),
                    },
                    dataType: "json",
                    success: function(result){
                        if (result.success) {
                            $('#modal_action').modal('show');
                        }else {
                            Swal.fire('Ups',result.message,'info'); 
                            return false;
                        }
                        
                    }
                });
            }
            
        });
        $('#Print').on('click', function() {
            if($('#perusahaan').val()==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info'); 
                return false;
            }else if($('#filter_tgl').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info'); 
                return false;
            }else{
                window.open('{{route('reportso.print',[null,null])}}/'+$('#perusahaan').val()+'/'+$('#filter_tgl').val(), '_blank');
            }
            
        });
        $('#ExportExcel').on('click', function() {
            if($('#perusahaan').val()==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info'); 
                return false;
            }else if($('#filter_tgl').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info'); 
                return false;
            }else{
                window.open('{{route('reportso.excel',[null,null])}}/'+$('#perusahaan').val()+'/'+$('#filter_tgl').val(), '_blank');
            }
        });
        $('#ExportPdf').on('click', function() {
            if($('#perusahaan').val()==''){
                Swal.fire('Ups','Silahkan Pilih Perusahaan terlebih dahulu','info'); 
                return false;
            }else if($('#filter_tgl').val()==''){
                Swal.fire('Ups','Silahkan Pilih Tanggal terlebih dahulu','info'); 
                return false;
            }else{
                window.open('{{route('reportso.pdf',[null,null])}}/'+$('#perusahaan').val()+'/'+$('#filter_tgl').val(), '_blank');
               
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
    
 </script>
@endpush