<div class="ibox mt-3">
    <div class="ibox-content">
        <div class="table-responsive">
            <table id="table-pembayaran" class="table display table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Invoice</th>
                        <th>No Nota</th>
                        <th>Member - Kota</th>
                        <th>Perusahaan</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice_tanda_terima as $inv)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $inv->no_tanda_terima }}</td>
                            <td>
                                @foreach ($inv->invoiceNoNota->where('perusahaan_id', $inv->perusahaan_id) as $no_nota)
                                    {{ $no_nota ->no_nota }} <br>
                                @endforeach
                            </td>
                            <td>{{ $inv->getMember->name }} - {{ $inv->getMember->prov }}</td>
                            <td>{{ $inv->getPerusahaan->name }}</td>
                            <td>{{ date('Y-m-d', strtotime($inv->invoice_date)) }}</td>
                            <td>
                                <a href="{{ route('pembayaran.detail', Crypt::encrypt($inv->id)) }}" class="btn btn-danger btn-sm" title="Input Pembayaran"><i class="fa fa-money-bill-alt"></i></a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#table-pembayaran').dataTable();
    })
</script>
