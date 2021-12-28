<title>Order Produk {{ $produk_beli->notransaction }}</title>
<style>
    
   
    body {
        font-family: Arial, Helvetica, sans-serif;
        padding-left: 0.8cm;
        padding-right: 0.8cm;
        padding-top: 0.8cm;
    }
    .fs-12 {
        font-size: 12px;
    }
    .fs-13 {
        font-size: 13px;
    }
    .fs-14 {
        font-size: 14px;
    }
    .fs-16 {
        font-size: 16px;
    }
    .fs-17 {
        font-size: 17px;
    }
    .fs-18 {
        font-size: 18px;
    }
    .fs-20 {
        font-size: 20px;
    }
    .fs-26 {
        font-size: 26px;
    }
    .container-fluid {
        color: #000000;
    }
    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .font-weight-bold {
        font-weight: 900;
    }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -.75rem;
        margin-left: -.75rem
    }
    .no-gutters {
        margin-right: 0;
        margin-left: 0
    }
    .col,
    .col-md-auto {
        position: relative;
        width: 100%;
        padding-right: .75rem;
        padding-left: .75rem
    }
    .col {
        flex-basis: 0;
        flex-grow: 1;
        max-width: 100%
    }
    .card-body {
        flex: 1 1 auto;
        min-height: 1px;
        padding: 1.25rem
    }
    .col-md-auto {
        flex: 0 0 auto;
        width: auto;
        max-width: 100%
    }
    .col-md-6 {
        flex: 0 0 auto;
        width: auto;
        max-width: 50%
    }
    .justify-content-md-center {
        justify-content: center !important
    }
    .card {
        display: inline-block;
        width: 100%
    }
    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid #e3e6f0;
        border-radius: .35rem
    }
    .p {
        padding: 0px 10px 5px 20px;
    }
    .p2 {
        padding: 0px 0px 5px 0px;
    }
    .p3 {
        padding: 0px 0px 5px 10px;
    }
    .px-3 {
        padding-left: 30px;
        padding-right: 30px;
    }
    .ln-28 {
        line-height: 28px;
    }
    .kotak {
        border: 1px solid black;
        padding: 2px 5px 2px 5px;
    }
    .container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto
    }
    .font-weight {
        font-weight: bold;
    }
    .mt-2 {
        margin-top: 2rem;
    }
    .text-center{
        text-align: center;
    }
</style>
<style type='text/css' media='print'>
    @page {
        size: auto;  
    }
    * {
        padding: 0;
        margin: 0;
    }
    body {
        font-family: Arial, Helvetica, sans-serif;
        padding-left: 0.8cm;
        padding-right: 0.8cm;
        padding-top: 0.8cm;
    }
    .fs-12 {
        font-size: 12px;
    }
    .fs-13 {
        font-size: 13px;
    }
    .fs-14 {
        font-size: 14px;
    }
    .fs-16 {
        font-size: 16px;
    }
    .fs-17 {
        font-size: 17px;
    }
    .fs-18 {
        font-size: 18px;
    }
    .fs-20 {
        font-size: 20px;
    }
    .fs-26 {
        font-size: 26px;
    }
    .container-fluid {
        color: #000000;
    }
    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .font-weight-bold {
        font-weight: 900;
    }
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -.75rem;
        margin-left: -.75rem
    }
    .no-gutters {
        margin-right: 0;
        margin-left: 0
    }
    .col,
    .col-md-auto {
        position: relative;
        width: 100%;
        padding-right: .75rem;
        padding-left: .75rem
    }
    .col {
        flex-basis: 0;
        flex-grow: 1;
        max-width: 100%
    }
    .card-body {
        flex: 1 1 auto;
        min-height: 1px;
        padding: 1.25rem
    }
    .col-md-auto {
        flex: 0 0 auto;
        width: auto;
        max-width: 100%
    }
    .col-md-6 {
        flex: 0 0 auto;
        width: auto;
        max-width: 50%
    }
    .justify-content-md-center {
        justify-content: center !important
    }
    .card {
        display: inline-block;
        width: 100%
    }
    .card {
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
        background-color: #fff;
        background-clip: border-box;
        border: 1px solid #e3e6f0;
        border-radius: .35rem
    }
    .p {
        padding: 0px 10px 5px 20px;
    }
    .p2 {
        padding: 0px 0px 5px 0px;
    }
    .p3 {
        padding: 0px 0px 5px 10px;
    }
    .px-3 {
        padding-left: 30px;
        padding-right: 30px;
    }
    .ln-28 {
        line-height: 28px;
    }
    .kotak {
        border: 1px solid black;
        padding: 2px 5px 2px 5px;
    }
    .container {
        width: 100%;
        padding-right: 15px;
        padding-left: 15px;
        margin-right: auto;
        margin-left: auto
    }
    .font-weight {
        font-weight: bold;
    }
    .mt-2 {
        margin-top: 2rem;
    }
    .text-center{
        text-align: center;
    }
</style>
<div class="container">
    <p class="fs-12">{{date("d/m/Y")}}</p>
   
</div>
<h2 class="font-weight mt-2 text-center">Form Order Produk</h2>
<table width='100%' align='center' class="mt-2">
    <tr>
        <td width='50%' align="left">
            <table>
                <tr>
                    <td style='vertical-align: top;font-family:arial; font-size:15px' ><b>Perusahaan : {{$produk_beli->product_beli_details[0]->perusahaan->name}}</b></td>
                </tr>
                <tr>
                    <td style='vertical-align: top;font-family:arial;padding-top:.5rem; font-size:15px'><b>Gudang : {{$produk_beli->product_beli_details[0]->gudang->name}}</b></td>
                </tr>
                <tr>
                    <td style='vertical-align: top;font-family:arial;padding-top:.5rem; font-size:13px'>Tanggal Faktur : {{date('d M y',strtotime($produk_beli->faktur_date))}}</td>
                </tr>
                
            </table>
        </td>
        
        <td width='80%' style="float: right" align="right">
            <table>
                <tr>
                    <td style='vertical-align: top;font-family:arial; font-size:15px' ><b>Faktur No: {{$produk_beli->notransaction}}</b></td>
                </tr>
                <tr>
                    <td  style='vertical-align: top;font-family:arial; padding-top:.5rem; font-size:15px'><b>Pabrik : {{$produk_beli->factory_name}}</b></td>
                </tr>
                <tr>
                    <td style='vertical-align: top;font-family:arial; padding-top:.5rem; font-size:13px'>Tanggal Terima Barang : {{date('d M y',strtotime($produk_beli->warehouse_date))}}</td>
                </tr>
            </table>
        </td>
    <tr>
</table>
<div class="mt-2">
    <table width="100%" border="1" align="center">
        <thead>
            <th width="30%" style="font-size:14px">Produk</th>
            <th width="30%" style="font-size:14px">Qty Order</th>
            <th width="30%" style="font-size:14px">Qty Terima</th>
        </thead>
        <tbody>
            @foreach ($produk_beli->product_beli_details as $item)
                <tr>
                    <td align="left" style="font-size:13px">{{$item->produk->product_name}}</td>
                    <td align="center" style="font-size:13px">{{$item->qty}} {{$item->produk->satuans->name}}</td>
                    <td align="center" style="font-size:13px">{{$item->qty_receive}} {{$item->produk->satuans->name}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<table class="mt-2" width="60%">
    <tr>
        <td style="font-size:11px">Catatan Oleh Gudang :</td>
        
    </tr>
    <tr>
        <td style="font-size:11px">{{$produk_beli->note}}</td>
    </tr>
</table>
    


<script>
    window.print()
</script>