<!-- Modal -->
<div class="modal fade" id="invoice_detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<input type="hidden" value="" id="id_purchase">
    <div class="modal-dialog modal-lg" style="max-width:95%;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                invoice <span id="title_modal"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="image_produk">
                <div class="row">
                    <div class="col-md-8 content-group">
                        <h5 class="text-uppercase text-semibold" id="nama_perusahaan"></h5>
                        <h5 class="text-uppercase text-semibold" id="alamat_perusahaan"></h5>
                        <h5 class="text-uppercase text-semibold" id="telp_perusahaan"></h5>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted>Invoice To : </h6></li>
                            <h5 class="text-uppercase text-semibold" id="member"></h5>
                            <h5 class="text-uppercase text-semibold" id="alamat_member"></h5>
                            <h5 class="text-uppercase text-semibold" id="telp_member"></h5>
                        </ul>
                    </div>
                    <div class="col-md-4 content-group">
                        <h3 class="text-uppercase text-semibold" id="nota"></h3>
                        <h5 class="text-uppercase text-semibold">Date : <span id="tanggal_pesan"></span></h5>
                        <ul class="list-condensed list-unstyled">
                            <li><h6 text-muted>Payment Detail : </h6></li>
                        </ul>
                        <ul class="list-condensed list-unstyled">
                            <h4>Total Due : <span class="text-right" id="total_price"></span></h4>
                            <h4>Nama Bank : <span class="text-right" id="nm_bank"></span></h4>
                            <h4>No Rekening : <span class="text-right" id="rek_bank"></span></h4>
                            <h4>Kota : <span class="text-right" id="kota"></span></h4>
                            <h4>Negara : <span class="text-right" id="negara"></span></h4>
                        </ul>
                    </div>
                </div>
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
                    <div class="col-sm-7 content-group">
                        
                    </div>
                    <div class="col-sm-5">
                        <span>Total Due</span>
                        <div class="table-responsive no-border">
                            <table class="table">
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
