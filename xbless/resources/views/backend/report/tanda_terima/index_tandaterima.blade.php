@extends('layouts.layout')
@section('title', 'LAPORAN TANDA TERIMA')
@section('content')
<style>
.swal2-container{
    z-index: 99999 !important;
}
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>LAPORAN TANDA TERIMA</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>LAPORAN TANDA TERIMA</a>
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
                <form action="#" id="submitData" name="submitData" method="POST" target="_blank">            
                            <div class="hr-line-dashed"></div>
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Perusahaan <sup style="color:red">*</sup> : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="perusahaan" name="perusahaan">
                                        <option value="">Pilih Perusahaan </option>
                                        @foreach($perusahaan as $key => $row)
                                        <option value="{{$row->id}}"{{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Sales : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="sales" name="sales[]" multiple="multiple">
                                        @foreach($sales as $key => $row)
                                        <option value="{{$row->id}}" @foreach($selectedsales as $k => $result) {{ $result == $row->id ? 'selected=""' : '' }}  @endforeach >{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                               
                                
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Kota :</label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="city" name="city[]" multiple="multiple">
                                        @foreach($city as $key => $row)
                                        <option value="{{$row->id}}" @foreach($selectedcity as $k => $result) {{ $result == $row->id ? 'selected=""' : '' }}  @endforeach >{{ucfirst($row->name)}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-sm-2 col-form-label">Member : </label>
                                <div class="col-sm-3 error-text">
                                    <select class="form-control select2" id="member" name="member">
                                        <option value="">Pilih Member</option>
                                        @foreach($member as $key => $row)
                                            <option value="{{$row->id}}" {{ $selectedmember == $row->id ? 'selected' : '' }}>{{ucfirst($row->name)}} ({{$row->getcity->name}})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Dari Tanggal <sup style="color:red">*</sup> : </label>
                                <div class="col-sm-3 error-text">
                                <input type="text" class="form-control formatTgl" id="tgl_start" name="tgl_start" value="{{$tgl_start}}" required>
                                </div>
                                <label class="col-sm-2 col-form-label">Sampai Tanggal <sup style="color:red">*</sup> : </label>
                                <div class="col-sm-3 error-text">
                                    <input type="text" class="form-control formatTgl" id="tgl_end" name="tgl_end" value="{{$tgl_end}}" required>
                                </div>
                                &nbsp;
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-1 error-text">
                                    <button class="btn btn-success" id="cariData" type="button"><span class="fa fa-search"></span>&nbsp; Cari Data</button>
                                 </div>
                            </div>
                            
                            <div class="hr-line-dashed"></div>
                            {{-- <div class="form-group row">
                                @can('reporttandaterima.excel')
                                <div class="col-xs-3">
                                &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="submit" id="ExportExcel" name="action" value="excel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                                </div>
                                @endcan
                                @can('reporttandaterima.print')
                                <div class="col-xs-3">
                                    <button class="btn btn-secondary" type="submit" id="Print" name="action" value="print"><span class="fa fa-print"></span> Print</button>&nbsp;
                                </div>
                                @endcan
                                @can('reporttandaterima.pdf')
                                <div class="col-xs-3">
                                    <button class="btn btn-danger" type="submit" id="ExportPdf" name="action" value="pdf"><span class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                                </div>
                                @endcan
                            </div> --}}
                            </form>
                           
                            <div class="hr-line-dashed"></div>
                            <span class="text-muted" style="font-size: 10px">Keterangan: <sup style="color: red">*</sup> (wajib diisi)</span>
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
            
            <div class="ibox-content d-block mx-auto">
                <form action="{{route('reporttandaterima.manageexport')}}" id="submitData" name="submitData" method="POST" target="_blank">
                    @csrf
                    <input type="hidden" name="perusahaan" id="perusahaan_id">
                    <input type="hidden" name="sales[]" id="sales_id">
                    <input type="hidden" name="member" id="member_id">
                    <input type="hidden" name="city[]" id="city_id">
                    <input type="hidden" name="tgl_start" id="tgl_start_export">
                    <input type="hidden" name="tgl_end" id="tgl_end_export">
                    @can('reporttandaterima.excel')
                    <div class="form-group row text-center">
                        <div class="col-xs-3">
                        &nbsp;&nbsp;&nbsp;<button class="btn btn-primary" type="submit" id="ExportExcel" name="action" value="excel"><span class="fa fa-file-excel-o"></span> Export Excel </button>&nbsp;
                        </div>
                        @endcan
                        @can('reporttandaterima.print')
                        <div class="col-xs-3">
                            <button class="btn btn-secondary" type="submit" id="Print" name="action" value="print"><span class="fa fa-print"></span> Print</button>&nbsp;
                        </div>
                        @endcan
                        @can('reporttandaterima.pdf')
                        <div class="col-xs-3">
                            <button class="btn btn-danger" type="submit" id="ExportPdf" name="action" value="pdf"><span class="fa fa-file-pdf-o"></span> Export PDF</button>&nbsp;
                        </div>
                        @endcan
                    </div>
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
            
            
       });
       $('#cariData').on('click', function(e) {
           e.preventDefault()
           let sales = $('#sales').val().length == 0 ? 0 : $('#sales').val()
           let city  = $('#city').val().length  == 0 ? 0 : $('#city').val()
           $('#perusahaan_id').val($('#perusahaan').val())
           $('#sales_id').val(sales)
           $('#member_id').val($('#member').val() ?? '')
           $('#city_id').val(city)
           $('#tgl_start_export').val($('#tgl_start').val())
           $('#tgl_end_export').val($('#tgl_end').val())
           $('#modal_action').modal('show');
       })
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