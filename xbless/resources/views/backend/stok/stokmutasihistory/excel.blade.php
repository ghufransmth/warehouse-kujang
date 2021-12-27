<table style="margin-top:20px;">
    <tr>
        <td colspan='8' style="text-align: center;" ><b><h3> LAPORAN MUTASI STOK {{strtoupper($perusahaan->name)}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='8' style="text-align: center;" ><b><h4> DARI TANGGAL {{strtoupper(date('d M Y',strtotime($tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table style="margin-top:20px;">
    <tr>
        <td colspan='2'>Dari Gudang </td>
        <td>: <b>{{strtoupper($gudang->name)}} </b></td>
    </tr>
    <tr>
        <td colspan='2'>Ke Gudang </td>
        <td>: <b>{{strtoupper($gudang_tujuan->name)}} </b></td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th width="5%" style="border: 1px solid #000000;"><b>No</b></th>
            <th style="border: 1px solid #000000;"><b>Produk</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Sebelum Mutasi</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Mutasi</b></th>
            <th style="border: 1px solid #000000;"><b>Stock Setelah Mutasi</b></th>
            <th style="border: 1px solid #000000;"><b>Tanggal Mutasi</b></th>
            <th style="border: 1px solid #000000;"><b>Dibuat Oleh</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td style="border: 1px solid #000000;">{{$no + 1}}</td>
            <td style="border: 1px solid #000000;">{!! $value->nama_produk !!}</td>
            <td style="border: 1px solid #000000;">{{$value->dari_stock}}</td>
            <td style="border: 1px solid #000000;">{{$value->ke_stock}}</td>
            <td style="border: 1px solid #000000;">{{$value->new_stock}}</td>
            <td style="border: 1px solid #000000;">{{$value->tgl_mutasi}}</td>
            <td style="border: 1px solid #000000;">{{$value->created_by}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

