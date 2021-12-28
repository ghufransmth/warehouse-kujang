@extends('layouts.layout')

@section('title', 'Detail Invoice')

@section('css')
@endsection

@section('content')

<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2>Detail Invoice untuk {{ ucfirst($type) }}</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{route('manage.beranda')}}">Beranda</a>
            </li>
            <li class="breadcrumb-item active">
                <a>Detail Invoice untuk {{ ucfirst($type) }}</a>
            </li>
        </ol>
    </div>
</div>
<div class="wrapper wrapper-content animated fadeInRight ecommerce">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="card mb-4">
                    <div class="card-header d-flex flex-row align-items-center justify-content-between" style="border-bottom: 1px solid rgba(0,0,0,.1);">
                        <h5 class="m-0 font-weight-bold text-primary">INVOICE #{{ $invoice->no_nota }}</h5>
                    </div>
                    <div class="row p-3">
                        <div class="col-md-6">
                            <h4>{{ $invoice->getPerusahaan->name }}</h4>
                            <p class="mb-0">{{ $invoice->getPerusahaan->address }}</p>
                            <p class="mb-0">{{ $invoice->getPerusahaan->city }}</p>
                            <p class="mb-0">{{ $invoice->getPerusahaan->telephone }}</p>
                            <br>
                            <br>

                            <span class="text-secondary">Invoice To:</span>
                            <h4>{{ $invoice->getMember->name }}</h4>
                            <h4>{{ $invoice->getMember->phone }}</h4>
                            <br>
                            <br>

                            <small>Scan Barcode</small>
                            <input type="text" class="form-control" autofocus placeholder="Input Kode Barcode">
                        </div>
                        <div class="col-md-6 text-right">
                            <h4>INVOICE #{{ $invoice->no_nota }}</h4>
                            <p>Date : <b>{{ date('d F Y', strtotime($invoice->created_at)) }}</b></p>
                        </div>
                    </div>
                    <div class="ibox mt-3">
                        <div class="ibox-content">
                            <form action="{{ route('returrevisi.store', [$type, $invoice->id]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="perusahaan_id" value="{{$invoice->getPerusahaan->id }}">
                                <div class="table-responsive">
                                    <table id="table-pembayaran" class="table display table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                {{-- <th>Harga Satuan Sebelum diskon</th> --}}
                                                {{-- <th>Diskon (%)</th> --}}
                                                <th>Harga Satuan Setelah Diskon</th>
                                                <th class="text-center" style="width:100px;">Qty</th>
                                                <th class="text-right">Harga Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="tabel-product">
                                            @php
                                                $total = 0;
                                                foreach ($invoice->getDetail as $getTotal) {
                                                    $total = $total + (($getTotal->price-(($getTotal->price*$getTotal->discount)/100))*$getTotal->qty);
                                                }

                                                $discount = round($invoice->subtotal*($invoice->discount/100));
                                                $afterdiscount = round($invoice->subtotal - $discount);
                                                $ppn = round($afterdiscount*(10/100));
                                                $grandTotal = $afterdiscount + $ppn;
                                            @endphp
                                            @foreach ($invoice->getDetail as $item)
                                                <tr>
                                                    <input type="hidden" name="diskon[]" value="{{ $item->discount }}">
                                                    <input type="hidden" name="invoice_detail_id[]" value="{{$item->id}}">
                                                    <td>{{ $item->product_name }}</td>
                                                    {{-- <td><input type="text" class="form-control" value="10.000,00"></td> --}}
                                                    {{-- <td><input type="text" class="form-control-plaintext" value="{{ $item->discount }}"></td> --}}
                                                    <td class="text-right">
                                                        @if ($type == 'revisi')
                                                            <input type="hidden" name="current_price[]" class="current_price" value="{{ round($item->price-($item->price*$item->discount/100)) }}">
                                                            <input type="text" name="price[]" class="form-control-plaintext price text-right" value="{{ round($item->price-($item->price*$item->discount)/100) }}">
                                                        @else
                                                            <input type="hidden" name="current_price[]" value="{{ round($item->price-($item->price*$item->discount/100)) }}">
                                                            <input type="text" name="price[]" class="form-control-plaintext price text-right" value="{{ round($item->price-($item->price*$item->discount/100)) }}" readonly>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">
                                                        @if ($type == 'revisi')
                                                            <input type="hidden" name="current_qty[]" value="{{ $item->qty }}">
                                                            <input type="text" name="qty[]" class="form-control-plaintext numberformat text-center" value="{{ $item->qty }}" readonly>
                                                        @else
                                                            <input type="hidden" name="current_qty[]" value="{{ $item->qty }}">
                                                            <input type="text" name="qty[]" class="form-control numberformat text-center" value="{{ $item->qty }}">
                                                        @endif

                                                    </td>
                                                    <td class="text-right">
                                                        <input type="hidden" name="current_total[]" value="{{ $item->ttl_price }}">
                                                        <input type="text" name="total[]" class="form-control-plaintext total text-right" value="{{ $item->ttl_price }}" readonly>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            <tr class="text-right">
                                                <td colspan="3">SubTotal</td>
                                                <td>
                                                    <input type="text" name="subtotal" class="form-control-plaintext subtotal text-right" value="{{ $invoice->subtotal }}" readonly>
                                                </td>
                                            </tr>
                                            <tr class="text-right">
                                                <td colspan="3">Diskon {{ $invoice->discount ? $invoice->discount : 0 }} %</td>
                                                <td>
                                                    <input type="text" name="discount" class="form-control-plaintext discount text-right" value="{{ $invoice->discount ? $discount : 0 }}" readonly>
                                                </td>
                                            </tr>
                                            <tr class="text-right">
                                                <td colspan="3">Total Setelah Diskon</td>
                                                <td>
                                                    <input type="text" name="total_after_discount" class="form-control-plaintext total_after_discount text-right" value="{{ $invoice->discount ? $afterdiscount : $invoice->subtotal }}" readonly>
                                                </td>
                                            </tr>
                                            <tr class="text-right">
                                                <td colspan="3">PPN (10%)</td>
                                                <td>
                                                    <input type="text" name="ppn" class="form-control-plaintext ppn text-right" value="{{ $ppn }}" readonly>
                                                </td>
                                            </tr>
                                            <tr class="text-right">
                                                <td colspan="3">Total</td>
                                                <td>
                                                    <input type="text" name="grand_total" class="form-control-plaintext grand-total text-right" value="{{ $grandTotal }}" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <div class="form-group">
                                        <label>Catatan</label>
                                        <textarea name="note" class="form-control" cols="30" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <a href="javascript:" onclick="backHistory()" class="btn btn-secondary"><i class="fa fa-reply mr-1"></i> Kembali</a>
                                    @can('returrevisi.simpan')
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save mr-1"></i> Submit</button>
                                    @endcan
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('scripts')
<script>
    $(function () {

        let price = $(".price");
        for(var i = 0; i < price.length; i++){
            let priceTotal  = $(price[i]).val();
            $(price[i]).val(formatRupiah(priceTotal))
        }

        let total = $(".total");
        for(var i = 0; i < total.length; i++){
            let totalPayment = $(total[i]).val();
            $(total[i]).val(formatRupiah(totalPayment))
        }

        let subTotal = $('.subtotal').val();
        $('.subtotal').val(formatRupiah(subTotal));

        let discount = $('.discount').val();
        $('.discount').val(formatRupiah(discount));

        let totalAfterDiscount = $('.total_after_discount').val();
        $('.total_after_discount').val(formatRupiah(totalAfterDiscount));


        let ppn = $('.ppn').val();
        $('.ppn').val(formatRupiah(ppn));

        let grandTotal = $('.grand-total').val();
        $('.grand-total').val(formatRupiah(grandTotal));
    })
</script>
<script>
    function formatRupiah(data) {
        let	rupiahFormat = data.toString().split('').reverse().join(''),
            ribuan 	= rupiahFormat.match(/\d{1,3}/g);
            ribuan	= ribuan.join('.').split('').reverse().join('');
            return ribuan;
    }
</script>
<script type="text/javascript">
    $('input.numberformat').keyup(function(event) {

    // skip for arrow keys
    if(event.which >= 37 && event.which <= 40) return;

    // format number
    $(this).val(function(index, value) {
            return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            ;
        });
    });

    $('input.price').keyup(function(event) {

    // skip for arrow keys
    if(event.which >= 37 && event.which <= 40) return;

    // format number
    $(this).val(function(index, value) {
            return value
            .replace(/\D/g, "")
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
            ;
        });
    });
</script>
<script type="text/javascript">
    $('.tabel-product').delegate('.numberformat,.total,.price','keyup', function(){
        let tr=$(this).parent().parent();
        let price=tr.find('.price').val();
        let qty=tr.find('.numberformat').val();
        var priceRupiah = price.split('.').join("");

        //qty
        tr.find('.numberformat').val(qty);

        //total
        tr.find('.total').val(formatRupiah(parseInt(priceRupiah)*parseInt(qty)))

        //sub total
        let subtotal = 0;
        $('.total').each(function(){
            subtotal += parseInt(this.value.split('.').join(""));
        });
        $('.subtotal').val(formatRupiah(subtotal));

        //discount
        let discountPercen = '{{ $invoice->discount }}';
        $('.discount').val(formatRupiah(Math.round(parseInt(subtotal) * discountPercen / 100)))

        //total_after_discount
        let totalAfterDiscont = parseInt(subtotal-(subtotal*discountPercen / 100));
        $('.total_after_discount').val(formatRupiah(Math.round(parseInt(subtotal-(subtotal*discountPercen / 100)))))

        //ppn
        let ppn = parseInt(totalAfterDiscont) * 10 / 100;
        $('.ppn').val(formatRupiah(Math.round(parseInt(totalAfterDiscont) * 10 / 100)))

        //grand total
        $('.grand-total').val(formatRupiah(Math.round(totalAfterDiscont+ppn)))

    });
</script>
<script>
    function backHistory() {
        window.history.back()
    }
</script>
@endpush


