<!-- Modal -->
<div class="modal fade" id="invoice_detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<input type="hidden" value="" id="id_invoice">
    <div class="modal-dialog modal-lg" style="max-width:95%;" role="document">
        <div class="modal-content">
            <div class="modal-header header_modal">
                Invoice &nbsp; <span id="title_modal"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="image_produk">
                <div class="row">
                    <div class="col-md-8 content-group">
                        <h5 class="info_" id="nama_perusahaan"></h5>
                        <h5 class="" id="alamat_perusahaan"></h5>
                        <h5 class="" id="kota_perusahaan"></h5>
                        <h5 class="" id="telp_perusahaan"></h5>
                        <br/>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted class="info_invoice_to">Invoice To : </h6></li>
                            <h5 class="info_" id="member"></h5>
                            <h5 class="" id="alamat_member"></h5>
                            <h5 class="" id="telp_member"></h5>
                        </ul>
                    </div>
                    <div class="col-md-4 content-group">
                        <div class="float-right">
                            <b><h3 class="info_">INVOICE <span id="nota"></span></h3></b>
                            <h6 class="float-right">Date : <b><span class="float-right" id="tanggal_pesan"></span></b></h6>
                        </div>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <br/>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted>Payment Detail : </h6></li>
                        </ul>
                        <ul class="list-condensed list-unstyled">
                            <h4 class="payment_detail">Total Due : <b><span class="text-right float-right" id="total_price"></span></b></h4>
                            <h4 class="payment_detail">Nama Bank : <b><span class="text-right float-right" id="nm_bank"></span></b></h4>
                            <h4 class="payment_detail">No Rekening : <span class="text-right float-right" id="rek_bank"></span></h4>
                            <h4 class="payment_detail">Kota : <span class="text-right float-right" id="kota"></span></h4>
                            <h4 class="payment_detail">Negara : <span class="text-right float-right" id="negara"></span></h4>
                        </ul>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id="table1" class="table table_invoice">
                        <thead id="header-table">
                        </thead>
                        <tbody id="list_produk">
                        </tbody>
                    </table>
                </div>
                <div class="hr-line-dashed"></div>
                <div class="row">
                    <div class="col-sm-4 content-group">
                        <ul class="list-condensed list-unstyled">
                            <li><h5 class="text-semibold">Informasi Tambahan</h5></li>
                            <li><h5 class="text-semibold">Memo : </h5><h6>* Edit Memo</h6></li>
                            <li><textarea name="memo" class="form-control" id="memo" cols="50" rows="2" value=""></textarea></li>
                            <br>
                            {{-- <li><h5 class="text-semibold">Colly : </h5><h6>* Edit Colly</h6></li>
                            <li><input type="text" class="form-control" name="colly" id="colly"  value=""/></li><br> --}}
                            <li><a class="btn btn-primary btn-sm text-white" type="submit" id="simpan">Simpan</a></li>
                        </ul>
                    </div>
                    <div class="col-sm-3 content-group">
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table table_invoice">
                                <tbody>
                                    <tr>
                                        <th>Subtotal : </th>
                                        <td id="subTotal" class="text-right">Rp. 200.000,00</td>
                                    </tr>
                                    <tr>
                                        <th>Diskon <span id="discount">10</span> % : </th>
                                        <td id="total_discount" class="text-right">Rp. 200.000,00</td>
                                    </tr>
                                    <tr>
                                        <th>Total Setelah Diskon : </th>
                                        <td id="total_setelah_diskon" class="text-right">Rp. 200.000,00</td>
                                    </tr>
                                    <tr>
                                        <th>PPN 10 % : </th>
                                        <td id="total_ppn" class="text-right">Rp. 200.000,00</td>
                                    </tr>
                                    <tr>
                                        <th>Total : </th>
                                        <td id="total_keseluruhan" class="text-right">Rp. 200.000,00</td>
                                    </tr>
                                    <tr>
                                        <th> </th>
                                        <td><a id="print" target="_blank" class="btn btn-default btn-lg icon-btn md-btn-flat product-tooltip" title="Print"><i class="fa fa-print"></i> Print</a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
