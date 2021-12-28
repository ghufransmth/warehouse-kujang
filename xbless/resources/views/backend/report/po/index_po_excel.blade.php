<table style="margin-top:20px;">
    <tr>
        <td colspan='5' style="text-align: center;" ><b><h3> LAPORAN PO ({{strtoupper($perusahaan->name)}})</h3> </b></td>
    </tr>
    <tr>
        <td colspan='5' style="text-align: center;" ><b><h4>{{strtoupper(date('d M Y',strtotime($tgl)))}}</h4></b>
    </td>
    </tr>
</table>
<table>
    <thead>
        <tr>
            <th width="5%" style="border: 1px solid #000000;"><b>No</b></th>
            <th style="border: 1px solid #000000;"><b>No PO</b></th>
            <th style="border: 1px solid #000000;"><b>Nama Customer</b></th>
            <th style="border: 1px solid #000000;"><b>Kota</b></th>
            <th style="border: 1px solid #000000;"><b>Status</b></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $no => $value)
        <tr>
            <td style="border: 1px solid #000000;">{{$no + 1}}</td>
            <td style="border: 1px solid #000000;">{!! $value->no_nota !!}</td>
            <td style="border: 1px solid #000000;">{{$value->nama_member}}</td>
            <td style="border: 1px solid #000000;">{{$value->kota_member}}</td>
            <td style="border: 1px solid #000000;">{{$value->status}}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<table>
    <tr>
        <td colspan='5' style="text-align: left;" ><i>Printed date : <?php echo date("d M Y") ?> </i></td>
    </tr>
</table>


