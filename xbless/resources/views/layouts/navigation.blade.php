<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse" id="sidebar-menu">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{session('profile')}}"/>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="block m-t-xs font-bold">{{strtoupper(auth()->user()->username)}}</span>
                        <span class="text-muted text-xs block">{{session('namaakses')}}<b class="caret"></b></span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a class="dropdown-item" href="{{route('profil.index')}}">Profil</a></li>
                        <li><a class="dropdown-item" href="{{route('profil.password')}}">Ubah Password</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{route('manage.logout')}}">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    BENSCO
                </div>
            </li>
            <li>
                <a href="{{ url('/') }}"><i class="fa fa-home"></i> <span class="nav-label">{{__('wording.menu_home')}}</span></a>
            </li>
            @can('master.index')
            <li>
                <a href="#"><i class="fa fa-cog"></i> <span class="nav-label">Master</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li class=""><a href="{{route('staff.index')}}">Master User</a></li>
                    <li class=""><a href="{{ route('sales.index') }}">Master Sales</a></li>
                    <li class=""><a href="{{route('expedisi.index')}}">Master Expedisi</a></li>
                    <li class=""><a href="{{route('type_channel.index')}}">Master Tipe Channel</a></li>
                    <li class=""><a href="{{ route('gudang.index') }}">Master Gudang</a></li>
                    <li class=""><a href="{{ route('diskon.index') }}">Master Diskon</a></li>
                    <li class=""><a href="{{ route('distrik.index') }}">Master Distrik</a></li>
                    <li class=""><a href="{{ route('payment.index') }}">Master Jenis Bayar</a></li>
                    <li class=""><a href="{{ route('toko.jenis.index') }}">Master Jenis Toko</a></li>
                    <li class=""><a href="{{ route('toko.kategori.index') }}">Master Kategori Toko</a></li>
                </ul>
            </li>
            @endcan
            @can('menuproduk.index')
            <li>
                <a href="#"><i class="fa fa-tasks"></i> <span class="nav-label">Master Produk</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('produk.index')
                    <li class=""><a href="{{ route('produk.index') }}">List Produk</a></li>
                    @endcan
                    @can('produk.tambah')
                    <li class=""><a href="{{ route('produk.tambah') }}">Tambah Produk</a></li>
                    @endcan
                    @can('produk.tambah')
                    <li class=""><a href="{{ route('produk.import') }}">Upload Produk</a></li>
                    @endcan
                    @can('kategori.index')
                    <li class=""><a href="{{ route('kategori.index') }}">List Kategori</a></li>
                    @endcan
                    @can('satuan.index')
                    <li class=""><a href="{{ route('satuan.index') }}">List Satuan</a></li>
                    @endcan
                    <!-- @can('jenisharga.index')
                    <li class=""><a href="{{ route('jenisharga.index') }}">List Jenis Harga</a></li>
                    @endcan
                    @can('brand.index')
                    <li class=""><a href="{{ route('brand.index') }}">List Brand</a></li>
                    @endcan
                    @can('engine.index')
                    <li class=""><a href="{{ route('engine.index') }}">List Engine Model</a></li>
                    @endcan -->
                </ul>
            </li>
            @endcan
            <li>
                <a href="#"><i class="fa fa-database"></i> <span class="nav-label">Master Stok</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('stokadmin.index')
                    <li><a href="{{route('stokadmin.index')}}">Informasi Stok</a></li>
                    @endcan
                    @can('adjstok.index')
                    <li><a href="{{route('adjstok.index')}}">Update Stok</a></li>
                    @endcan
                    @can('stokmutasi.tambah')
                    <li><a href="{{route('stokmutasi.tambah')}}">Mutasi Stok</a></li>
                    @endcan
                    @can('historymutasistok.index')
                    <li><a href="{{route('historymutasistok.index')}}">History Mutasi Stok</a></li>
                    @endcan
                    @can('stokopname.index')
                    <li><a href="{{route('stokopname.index')}}">Opname Stok</a></li>
                    @endcan
                </ul>
            </li>
            <li>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">Master Toko</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li class=""><a href="{{ route('toko.index') }}">List Toko</a></li>
                    <li class=""><a href="{{ route('toko.tambah') }}">Tambah Toko</a></li>
                </ul>
            </li>
            <li>
                <a href="#"><i class="fa fa-list-alt"></i> <span class="nav-label">Penjualan Produk</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li><a href="{{ route('purchaseorder.index') }}">List Penjualan Produk</a></li>
                    @can('purchaseorder.tambah')
                    <li class=""><a href="{{ route('purchaseorder.tambah') }}">Tambah Penjualan Produk</a></li>
                    @endcan
                    @can('purchaseorder.tambah')
                        <li class=""><a href="{{ route('purchaseorder.import') }}">Upload Penjualan</a></li>
                    @endcan
                </ul>
            </li>

            @can('menuorder.index')
            <li>
                <a href="#"><i class="fa fa-cart-plus"></i> <span class="nav-label">Pembelian Produk</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('order.index')
                    <li class=""><a href="{{ route('pembelian.index') }}">List Pembelian Produk</a></li>
                    @endcan
                    @can('purchaseorder.tambah')
                        <li class=""><a href="{{ route('pembelian.tambah') }}">Tambah Pembelian Produk</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            {{-- @can('menuorder.index')
            <li>
                <a href="#"><i class="fa fa-cart-plus"></i> <span class="nav-label">Pembelian Produk</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('order.index')
                    <li class=""><a href="{{ route('order.index') }}">List Pembelian Produk</a></li>
                    @endcan
                    @can('purchaseorder.tambah')
                        <li class=""><a href="{{ route('purchaseorder.tambah') }}">Tambah Pembelian Produk</a></li>
                    @endcan
                </ul>
            </li>
            @endcan --}}
            @can('menureport.index')
            <li>
                <a href="#"><i class="fa fa-paste"></i> <span class="nav-label">Report</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('reportso.index')
                    <li><a href="{{route('reportso.index')}}">Report SO</a></li>
                    @endcan
                    @can('reportpo.index')
                    <li><a href="{{route('reportpo.index')}}">Report PO</a></li>
                    @endcan
                    @can('reportreturrevisi.index')
                    <li><a href="{{route('reportreturrevisi.index')}}">Report Retur Revisi</a></li>
                    @endcan
                    @can('reportinvoice.index')
                    <li><a href="{{route('reportinvoice.index')}}">Report Rekap Invoice</a></li>
                    @endcan
                    @can('reporttandaterima.index')
                    <li><a href="{{route('reporttandaterima.index')}}">Report Tanda Terima</a></li>
                    @endcan
                    @can('reportbarangmasuk.index')
                    <li><a href="{{route('reportbarangmasuk.index')}}">Report Barang Masuk</a></li>
                    @endcan
                    @can('reportbarangkeluar.index')
                    <li><a href="{{route('reportbarangkeluar.index')}}">Report Barang Keluar</a></li>
                    @endcan
                    @can('reportsisahutang.index')
                    <li><a href="{{route('reportsisahutang.index')}}">Report Sisa Hutang</a></li>
                    @endcan
                    @can('reportbo.index')
                    <li><a href="{{route('reportbo.index')}}">Report Back Order</a></li>
                    @endcan
                    @can('reportboqty.index')
                    <li><a href="{{route('reportboqty.index')}}">Report Qty Back Order</a></li>
                    @endcan
                    @can('reportpenjualan.index')
                    <li><a href="{{route('reportpenjualan.index')}}">Report Penjualan</a></li>
                    @endcan
                    @can('reportstok.index')
                    <li><a href="{{route('reportstok.index')}}">Report Stok</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('security.index')
            <li>
                <a href="#"><i class="fa fa-key"></i> <span class="nav-label">Keamanan</span><span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('permission.index')
                    <li class=""><a href="{{route('permission.index')}}">Manajemen Modul</a></li>
                    @endcan
                    @can('role.index')
                    <li class=""><a href="{{route('role.index')}}">Manajemen Akses</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

        </ul>

    </div>
</nav>
<script src="{{ asset('assets/js/jquery-3.1.1.min.js')}}"></script>
{{-- <script language="javascript" type="text/javascript">
    $(document).ready(function () {
        setInterval(function(){
            getData();
        }, 150000);
        getData();
    });
    function getData(){
        $.ajax({
            type: 'POST',
            url: '{{route("getdata.show_count")}}',
            data:{
                _token :"{{csrf_token()}}"
            },
            dataType: "json",
            success: function(data){
                if (data.success) {
                    $('#req_po_count').html(data.rpo);
                    $('#po_batal_count').html(data.pobatal);
                    $('#back_order_count').html(data.backorder);
                }else{
                    $('#req_po_count').html('0');
                    $('#po_batal_count').html('0');
                    $('#back_order_count').html('0');
                }
            },
            error: function(data){
                console.log(data);
            }
        });
    }

</script> --}}
