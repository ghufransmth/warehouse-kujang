<table style="margin-top:20px;">
    <tr>
        <td colspan='8' style="text-align: center;" ><b><h3> LAPORAN ADJUSTMENT STOK {{strtoupper($perusahaan->name)}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='8' style="text-align: center;" ><b><h4> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table style="margin-top:20px;">
    <tr>
        <td colspan='2'>Gudang </td>
        <td>: <b>{{strtoupper($gudang->name)}} </b></td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th width="5%" style="border: 1px solid #000000;"><b>No</b></th>
            <th style="border: 1px solid #000000;"><b>Produk</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Lama</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Adjustment</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Baru</b></th>
            <th style="border: 1px solid #000000;"><b>Catatan</b></th>
            <th style="border: 1px solid #000000;"><b>Tanggal Adjustment</b></th>
            <th style="border: 1px solid #000000;"><b>Transaksi dibuat</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td style="border: 1px solid #000000;">{{$no + 1}}</td>
            <td style="border: 1px solid #000000;">{!! $value->nama_produk !!}</td>
            <td style="border: 1px solid #000000;">{{$value->stock_lama}}</td>
            <td style="border: 1px solid #000000;">{{$value->stock_adj}}</td>
            <td style="border: 1px solid #000000;">{{$value->stock_new}}</td>
            <td style="border: 1px solid #000000;">{{$value->note}}</td>
            <td style="border: 1px solid #000000;">{{$value->tgl_adj}}</td>
            <td style="border: 1px solid #000000;">{{$value->created_by}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

