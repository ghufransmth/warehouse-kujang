<!-- Modal -->
<div class="modal fade" id="modal_image_produk" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<input type="hidden" value="" id="id_purchase">
    <div class="modal-dialog modal-lg" style="max-width:95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span id="title_modal"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="image_produk">
                <div class="row">
                    <div class="col-md-8 content-group">
                        <h5 class="text-uppercase text-semibold" id="nama_perusahaan"></h5>
                        <h5 class="text-uppercase text-semibold" id="alamat_perusahaan"></h5>
                        <h5 class="text-uppercase text-semibold" id="kota_perusahaan"></h5>
                        <h5 class="text-uppercase text-semibold" id="telp_perusahaan"></h5>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted>Pemesanan Kepada :  </h6></li>
                            <li><h5><span id="member_tujuan"></span></h5></li>
                        </ul>
                    </div>
                    <div class="col-md-4 content-group">
                        <h5 class="text-uppercase text-semibold" id="nota">
                        </h5>
                        <h5 class="text-uppercase text-semibold">Date : <span id="tanggal_pesan"></span></h5>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted>Detail Pembayaran : </h6></li>
                        </ul>
                        @if( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusgudang') && Gate::check('purchaseorder.liststatusinvoice') )
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @elseif( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusgudang'))
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @elseif( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusinvoice') )
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @elseif( Gate::check('purchaseorder.liststatusgudang') && Gate::check('purchaseorder.liststatusinvoice') )
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @elseif( Gate::check('purchaseorder.liststatusgudang'))
                        @can('purchaseorder.liststatusgudang')
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : </h4>
                        </ul>
                        @endcan
                        @elseif( Gate::check('purchaseorder.liststatuspo'))
                        @can('purchaseorder.liststatuspo')
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @endcan
                        @elseif( Gate::check('purchaseorder.liststatusgudang'))
                        @can('purchaseorder.liststatusinvoice')
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                        </ul>
                        @endcan
                        @endif



                    </div>
                </div>
                @can('purchaseorder.liststatusgudang')
                <hr/>
                <div>
                    <div class="col-md-4">
                        <input type="text" class="form-control mt-3 product-barcode" id="scan_barcode" placeholder="Input kode barcode" autofocus/>
                    </div>
                </div>
                <hr/>
                @endcan
                <div class="table-responsive">
                    <table id="table1" class="table" >
                        <thead id="header-table">
                        </thead>
                        <tbody id="list_produk">
                        </tbody>
                    </table>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="row">
                    @if( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusgudang') && Gate::check('purchaseorder.liststatusinvoice') )
                    @can('purchaseorder.liststatusinvoice')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5><h6>* edit catatan</h6></li>
                            <li><textarea name="note" id="note" cols="50" rows="2" value=""></textarea></li>
                            <li><a class="btn btn-primary btn-sm text-white simpancatatan" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @elseif( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusgudang'))
                    @can('purchaseorder.liststatuspo')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5><h6>* edit catatan</h6></li>
                            <li><textarea name="note" id="note" cols="50" rows="2" value=""></textarea></li>
                            <li><a class="btn btn-primary btn-sm text-white simpancatatan" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @elseif( Gate::check('purchaseorder.liststatuspo') && Gate::check('purchaseorder.liststatusinvoice'))
                    @can('purchaseorder.liststatusinvoice')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5><h6>* edit catatan</h6></li>
                            <li><textarea name="note" id="note" cols="50" rows="2" value=""></textarea></li>
                            <li><a class="btn btn-primary btn-sm text-white simpancatatan" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @elseif(Gate::check('purchaseorder.liststatusgudang'))
                    @can('purchaseorder.liststatusgudang')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5></li>
                            <li><h5 id="note_gudang"></h5></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @elseif(Gate::check('purchaseorder.liststatuspo'))
                    @can('purchaseorder.liststatuspo')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5><h6>* edit catatan</h6></li>
                            <li><textarea name="note" id="note" cols="50" rows="2" value=""></textarea></li>
                            <li><a class="btn btn-primary btn-sm text-white simpancatatan" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @elseif(Gate::check('purchaseorder.liststatusinvoice'))
                    @can('purchaseorder.liststatusinvoice')
                    <div class="col-sm-7 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Catatan : </h5><h6>* edit catatan</h6></li>
                            <li><textarea name="note" id="note" cols="50" rows="2" value=""></textarea></li>
                            <li><a class="btn btn-primary btn-sm text-white simpancatatan" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total" class="text-right"></td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
