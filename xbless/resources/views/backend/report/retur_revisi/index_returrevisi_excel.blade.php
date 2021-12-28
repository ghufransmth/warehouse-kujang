<table style="margin-top:20px;">
    <tr>
        <td colspan='7' style="text-align: center;" ><b><h3> LAPORAN RETUR DAN REVISI {{!empty($perusahaan) ? strtoupper($perusahaan->name) : ''}}</h3> </b></td>
    </tr>
    <tr>
        <td colspan='7' style="text-align: center;" ><b><h4>DARI TANGGAL {{strtoupper(date('d M Y',strtotime($filter_tgl_start)))}} - {{strtoupper(date('d M Y',strtotime($filter_tgl_end)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th style="border: 1px solid #0a0909;"><b>No</b></th>
            <th style="text-align: center; border: 1px solid #0a0909;"><b>No Retur/Revisi</b></th>
            <th style="text-align: center;border: 1px solid #0a0909;"><b>No Invoice</b></th>
            <th style="text-align: center;border: 1px solid #0a0909;"><b>Customer</b></th>
            <th style="text-align: center;border: 1px solid #0a0909;"><b>Kode-Nama (Produk)</b></th>
            <th style="text-align: center;border: 1px solid #0a0909;"><b>Keterangan</b></th>
        </tr> 
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
           <?php $jumlah= count($value->produk_info);?>
            <tr>
                <td style="border: 1px solid #0a0909;display: table-cell; vertical-align: middle" rowspan="{{$jumlah}}">{{$no + 1}}</td>
                <td style="border: 1px solid #0a0909;display: table-cell; vertical-align: middle;" rowspan="{{$jumlah}}">{{$value->noretur}}</td>
                <td style='border: 1px solid #0a0909;display: table-cell; vertical-align: middle;' rowspan="{{$jumlah}}">{{$value->no_inv}}</td>
                <td style='border: 1px solid #0a0909;display: table-cell; vertical-align: middle;' rowspan="{{$jumlah}}">{{$value->member_name}} - {{$value->nama_kota}}</td>
                @foreach ($value->produk_info as $key => $produkinfo)
                    @if($key==0)
                    <td style='border: 1px solid #0a0909;'>
                        {{$produkinfo}}
                    </td>
                    @endif
                @endforeach   
                @foreach ($value->ket as $key => $ket)
                    @if($key==0)
                    <td style='border: 1px solid #0a0909;text-align:left;'>
                        {{$ket}}
                    </td>
                    @endif
                @endforeach
               
            </tr>
            @if($jumlah > 1)
                @foreach ($value->produk_info as $key => $item_data)
                    @if($key != 0)
                        <tr>
                            <td style='border: 1px solid #0a0909;'>
                                {{$item_data}}
                            </td>
                            <td style='border: 1px solid #0a0909;text-align:left;'>
                                {{$value->ket[$key]}}
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endif
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='7' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


