@extends('layouts.layout')

@section('title', 'Purchase Order')

@section('content')
<style>
    .swal2-container {
        z-index: 100000 !important;
    }
</style>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Proses Penjualan</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Proses Penjualan</a>
            </li>
        </ol>
    </div>
    <div class="col-lg-2">
        {{-- <br />
        <button id="refresh" class="btn btn-primary" data-toggle="tooltip" data-placement="top"
            title="Refresh Data"><span class="fa fa-refresh"></span></button>
        @can('purchaseorder.tambah')
        <a href="{{ route('purchaseorder.tambah')}}" class="btn btn-success" data-toggle="tooltip" data-placement="top"
            title="Tambah Data"><span class="fa fa-pencil-square-o"></span>&nbsp; Tambah</a>
        @endcan --}}
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
                            {{-- <label class="col-sm-2 col-form-label">Toko : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="toko" name="toko">
                                    <option value="">Semua Toko</option>
                                    @foreach($toko as $key => $tko)
                                    <option value="{{ $tko->id }}">{{ $tko->name }}</option>
                                    @endforeach --}}
                                    {{-- @foreach($perusahaan as $key => $row)
                                    <option value="{{$row->id}}"
                                    {{ $selectedperusahaan == $row->id ? 'selected=""' : '' }}>{{ucfirst($row->name)}}
                                    </option>
                                    @endforeach --}}
                                {{-- </select>
                            </div>
                            <label class="col-sm-2 col-form-label">Sales : </label>
                            <div class="col-sm-4 error-text">
                                <select class="form-control select2" id="sales" name="sales">
                                    <option value="">Semua Sales</option>
                                    @foreach($sales as $key => $sles)
                                    <option value="{{ $sles->id }}">{{ $sles->nama }}</option>

                                    @endforeach --}}
                                    {{-- @foreach($member as $key => $row)
                                    <option value="{{$row->id}}" {{ $selectedmember == $row->id ? 'selected=""' : '' }}
                                    >{{ucfirst($row->name)}}-{{ucfirst($row->city)}}</option>
                                    @endforeach --}}
                                {{-- </select>
                            </div> --}}
                        </div>
                        {{-- <div class="form-group row">
                            <div class="col-sm-1 error-text">
                                <button class="btn btn-success" id="search-data" type="button"><span
                                        class="fa fa-search"></span>&nbsp; Cari Data</button>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div> --}}
                    </form>
                    {{-- <ul class="nav nav-tabs" id="myTab" role="tablist">
                        @if(Gate::check('purchaseorder.liststatuspo') ||
                        Gate::check('purchaseorder.liststatusinvoiceawal') ||
                        Gate::check('purchaseorder.liststatusinvoice'))
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==0?'active':(session('type')==""?'active':'')}}"
                                id="listpo-tab" value="0" onclick="change_type(0)" data-toggle="tab" href="#listpo"
                                role="tab" aria-controls="listpo" aria-selected="true">LIST PENJUALAN</a>
                        </li>
                        @endif
                        @can('purchaseorder.liststatuspolisttolak')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==1?'active':''}}" id="listpotolak-tab" value="1"
                                onclick="change_type(1)" data-toggle="tab" href="#listpotolak" role="tab"
                                aria-controls="listpotolak" aria-selected="false">LIST PENJUALAN BELUM LUNAS</a>
                        </li>
                        @endcan
                        @can('purchaseorder.liststatusinvoiceawal')
                        <li class="nav-item">
                            <a class="nav-link {{session('type')==2?'active':''}}" id="listpovalidasi-tab" value="1"
                                onclick="change_type(2)" data-toggle="tab" href="#listpovalidasi" role="tab"
                                aria-controls="listpovalidasi" aria-selected="false">LIST PENJUALAN LUNAS</a>
                        </li>
                        @endcan --}}
                        {{-- @can('purchaseorder.liststatusgudang')
                        @foreach ($gudang as $k=>$itemgudang)
                            <li class="nav-item">
                                <a class="nav-link {{session('type_gudang')==$itemgudang->id?'active':''}}"
                        id="listgudang_{{$itemgudang->id}}-tab" value="1"
                        onclick="change_type_gudang(3,{{$itemgudang->id}})" data-toggle="tab" href="#listpovalidasi"
                        role="tab" aria-controls="listpovalidasi"
                        aria-selected="false">{{strtoupper($itemgudang->name)}}</a>
                        </li>
                        @endforeach
                        @endcan --}}
                    {{-- </ul> --}}
                    <input type="hidden" class="form-control" id="type" value="{{session('type')}}" />
                    <input type="hidden" class="form-control" id="type_gudang" value="{{session('type_gudang')}}" />
                    {{-- <div class="hr-line-dashed"></div> --}}
                    <div class="table-responsive">
                        <table id="table1" class="table display table p-0 table-hover table-striped"
                            style="overflow-x: auto;">
                            <thead>
                                <tr class="text-white text-center bg-primary">
                                    <th width="10px;">No</th>
                                    <th>No Faktur</th>
                                    <th>Sales</th>
                                    <th>Toko</th>
                                    <th>Tgl Transaksi</th>
                                    {{-- <th>Tgl Jatuh Tempo</th> --}}
                                    <th width="40px">Status Pembayaran</th>
                                    <th>Total Harga</th>
                                    <th>Total Diskon</th>
                                    <th>Gudang</th>
                                    <th>Supplier</th>
                                    <th>Created By</th>
                                    <th class="text-center" width="11%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td width="10px;">No</td>
                                    <td>{{ $penjualan->no_faktur }}</td>
                                    <td>{{ $penjualan->getsales->nama }}</td>
                                    <td>{{ $penjualan->gettoko->nama }}</td>
                                    <td>{{ date('d-m-Y', strtotime($penjualan->tgl_faktur)) }}</td>
                                    {{-- <td>{{ date('d-m-Y', strtotime($penjualan->tgl_jatuh_tempo)) }}</td> --}}
                                    <td>{{ ($penjualan->status_lunas == 0)? 'Belum lunas' : 'Lunas' }}</td>
                                    <td>{{ format_uang($penjualan->total_harga) }}</td>
                                    <td>{{ format_uang($penjualan->total_diskon) }}</td>
                                    <td><select name="gudang" class="form-control" id="gudang">
                                        <option value="">Pilih Gudang</option>
                                        @foreach($gudang as $key => $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                                        @endforeach
                                    </select></td>
                                    <td>
                                        <select name="gudang" class="form-control" id="supplier">
                                        </select>
                                    </td>
                                    <td>{{ $penjualan->created_by }}</td>
                                    <td class="text-center" width="11%"><a href="" class="btn btn-primary"> Proses</a></td>
                                </tr>
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
    {{-- @include('backend.purchase.detail') --}}




</div>
@endsection
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.js"
    integrity="sha512-w5udtBztYTK9p9QHQR8R1aq8ke+YVrYoGltOdw9aDt6HvtwqHOdUHluU67lZWv0SddTHReTydoq9Mn+X/bRBcQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    $(document).ready(function(){
        $('#gudang').change(function(){
            let html = '';
            $.ajax({
                type: 'POST',
                data: {
                    'id_gudang' : this.value,
                    'enc_id'    : '{{ $enc_id }}',
                },
                url: '{{route("purchaseorder.getsupplier")}}',
                headers: {'X-CSRF-TOKEN': $('[name="_token"]').val()},
                success: function(response) {
                    // console.log(response);
                    // console.log(response.data[0].getsupplier)
                    if(response.success){

                        for(var i=0;i<response.data.length; i++){
                            html += `<option value="${response.data[i].getsupplier.id}">${response.data[i].getsupplier.nama}</option>`
                        }

                        $('#supplier').html(html);

                    }else{
                        Swal.fire('Ups', 'Product Tidak ditemukan', 'info');
                    }
                }
            });
        });
    });
</script>
@endpush
