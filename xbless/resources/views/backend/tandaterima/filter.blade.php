@foreach($invoice as $key => $value)
    <div class="ibox-content">
        <div class="table-responsive">
            <table class="table shoping-cart-table">
                <tbody>
                    <input type="checkbox" id="{{ $value->id }}" name="select_invoice" value="{{ $value->id }}">
                    <tr>
                        <td class="desc">
                            <h3>
                                <span id="customer">{{ $value->member_name }} - {{$value->member_city}}</span>
                            </h3>
                            <p class="medium">
                                Invoice : #<span id="invoice">{{ $value->no_nota }}</span>
                            </p>
                            <p class="medium">
                                No PO : #<span id="no_po">{{$value->purchase_no}}</span>
                            </p>
                            <p class="medium">
                                Sales : <span id="sales"> {{ $value->sales_name }}</span>
                                ( Dibuat pada : <span id="tgl_buat">{{ $value->create }}</span> )
                            </p>
                            <p class="medium info_invoice">
                                Expedisi : <span id="expedisi"> {{ $value->nama_expedisi }}</span>
                            </p>
                            @if($value->nama_expedisi_via != '-')
                            <p class="medium info_invoice">
                                Expedisi Via : <span id="expedisi"> {{ $value->nama_expedisi_via }}</span>
                            </p>
                            @endif
                        </td>
                        <td>
                            <p class="medium font-weight-bold text-uppercase"><span id="perusahaan">{{$value->perusahaan_name}}</span></p>
                            <p class="medium font-weight-bold"><span id="total">Rp. {{number_format($value->total, 0, '','.')}}</span></p>
                            @if($value->pay_status == 0)
                                <span class="badge badge-warning text-left">Belum Lunas</span>
                            @elseif($value->paystatus == 1)
                                <span class="badge badge-warning text-left">Lunas</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="user text-center" colspan="2">
                            <div class="m-t-m">
                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> PO - </span><span class="info_invoice" id="user_po">{{strtoupper($value->created_by)}}</span>
                                &nbsp; | &nbsp;
                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> INV - </span><span class="info_invoice" id="user_inv">{{ strtoupper($value->loginv) }}</span>
                                &nbsp; | &nbsp;
                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> GDG - </span><span class="info_invoice" id="user_gdg">{{ strtoupper($value->gudang) }}</span>
                                &nbsp; | &nbsp;
                                <span class="text-muted info_invoice"><i class="fa fa-user"></i> INV - </span><span class="info_invoice" id="user_inv">{{ strtoupper($value->create_user) }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="hr-line-dashed"></div>
            <div class="heading-elements">
                <span class="heading-text">
                    <span class="text-muted" id="created">{{ $value->create_invo }}</span>
                </span>
                <ul class='list-inline pull-right'>
                    <li><a href='#' class='text-default' data-toggle='modal' data-target='#invoice_detail' onclick="detail_invoice({{$value->id}})"><i class='fa fa-eye fa-lg'></i></a></li>
                    &nbsp; | &nbsp;
                    <li class='dropdown'>
                        <a href='#' class='dropdown-toggle' data-toggle='dropdown'><i class='fa fa-bars fa-lg'></i></a>
                        <ul class='dropdown-menu' style="position: absolute !important; top: -208px !important; left: -147px !important; will-change: top, left !important;">
                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_invoice'><i class='fa fa-print fa-lg'></i><span>&nbsp; Print invoice</span></a></li>
                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_surat_jalan'><i class='fa fa-file-word-o fa-lg'></i><span>&nbsp; Surat Jalan</span></a></li>
                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_packing_list'><i class='fa fa-print fa-lg'></i><span>&nbsp; Print Packing List</span></a></li>
                            <li><a href='#!' data-toggle='modal' data-target='#modal_pilihan' onclick='pilih_menu({{$value->id}}, this.name)' name='proses_amplop'><i class='fa fa-print fa-lg'></i>&nbsp; Print Amplop</a></li>
                            <div class="hr-line-dashed"></div>
                            <li><a href='#' data-toggle='modal' data-target='#modal_pengiriman' onclick="pengiriman({{ $value->id }})"><i class='fa fa-truck fa-lg'></i><span>&nbsp; Input Pengiriman</span></a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endforeach
