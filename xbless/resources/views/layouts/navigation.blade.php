<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse" id="sidebar-menu">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="{{session('profile')}}" />
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
                    KM<span class="text-navy">U</span>
                </div>
            </li>
            <li>
                <a href="{{ url('/') }}"><i class="fa fa-home"></i> <span
                        class="nav-label">{{__('wording.menu_home')}}</span></a>
            </li>
            @can('master.index')
            <li>
                <a href="#"><i class="fa fa-cog"></i> <span class="nav-label">Master</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('staff.index')
                        <li class=""><a href="{{route('staff.index')}}">Master User</a></li>
                    @endcan
                    @can('sales.index')
                        <li class=""><a href="{{ route('sales.index') }}">Master Sales</a></li>
                    @endcan
                    @can('driver.index')
                        <li class=""><a href="{{ route('driver.index') }}">Master Driver</a></li>
                    @endcan
                    @can('expedisi.index')
                        <li class=""><a href="{{route('expedisi.index')}}">Master Expedisi</a></li>
                    @endcan
                    @can('type_channel.index')
                        <li class=""><a href="{{route('type_channel.index')}}">Master Tipe Channel</a></li>
                    @endcan
                    @can('gudang.index')
                        <li class=""><a href="{{ route('gudang.index') }}">Master Gudang</a></li>
                    @endcan
                    @can('supplier.index')
                        <li class=""><a href="{{ route('supplier.index') }}">Master Supplier</a></li>
                    @endcan
                    @can('diskon.index')
                        <li class=""><a href="{{ route('diskon.index') }}">Master Diskon</a></li>
                    @endcan
                    @can('distrik.index')
                        <li class=""><a href="{{ route('distrik.index') }}">Master Distrik</a></li>
                    @endcan
                    @can('payment.index')
                        <li class=""><a href="{{ route('payment.index') }}">Master Jenis Bayar</a></li>
                    @endcan
                    @can('toko.jenis.index')
                        <li class=""><a href="{{ route('toko.jenis.index') }}">Master Jenis Toko</a></li>
                    @endcan
                    @can('toko.kategori.index')
                        <li class=""><a href="{{ route('toko.kategori.index') }}">Master Kategori Toko</a></li>
                    @endcan
                    @can('komponen.index')
                        <li class=""><a href="{{ route('komponen.index') }}">Komponen</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('menuproduk.index')
            <li>
                <a href="#"><i class="fa fa-tasks"></i> <span class="nav-label">Master Produk</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('produk.index')
                    <li class=""><a href="{{ route('produk.index') }}">List Produk</a></li>
                    @endcan
                    @can('produk.tambah')
                    <li class=""><a href="{{ route('produk.tambah') }}">Tambah Produk</a></li>
                    @endcan
                    @can('produk.import')
                    <li class=""><a href="{{ route('produk.import') }}">Upload Produk</a></li>
                    @endcan
                    @can('kategori.index')
                    <li class=""><a href="{{ route('kategori.index') }}">List Kategori</a></li>
                    @endcan
                    @can('satuan.index')
                    <li class=""><a href="{{ route('satuan.index') }}">List Satuan</a></li>
                    @endcan

                </ul>
            </li>
            @endcan

            @can('menustok.index')
            <li>
                <a href="#"><i class="fa fa-database"></i> <span class="nav-label">Master Stok</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('adjstok.index')
                    <li><a href="{{route('adjstok.index_supplier')}}">Info Stok</a></li>
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
            @endcan
            @can('menutoko.index')
            <li>
                <a href="#"><i class="fa fa-th-large"></i> <span class="nav-label">Master Toko</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('toko.index')
                        <li class=""><a href="{{ route('toko.index') }}">List Toko</a></li>
                    @endcan
                    @can('toko.tambah')
                        <li class=""><a href="{{ route('toko.tambah') }}">Tambah Toko</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('menukeuangan.index')
            <li>
                <a href="#"><i class="fa fa-dollar"></i> <span class="nav-label"> Keuangan</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('transaksi.finance.index')
                        <li><a href="{{ route('transaksi.finance.index') }}">List Biaya</a></li>
                    @endcan
                    @can('transaksi.pembayaran.index')
                        <li><a href="{{ route('transaksi.pembayaran.index') }}">Transaksi Pembayaran</a></li>
                    @endcan
                    @can('transaksi.keuangan.index')
                        <li><a href="{{ route('transaksi.keuangan.index') }}">Transaksi Keuangan</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('menuretur.index')
            <li>
                <a href="#"><i class="fa fa-exchange"></i> <span class="nav-label">Retur Produk</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('retur.index')
                        <li><a href="{{ route('retur.index') }}">List Retur Produk</a></li>
                    @endcan
                    @can('retur.index_retur')
                        <li class=""><a href="{{ route('retur.index_retur') }}">Form Retur Produk</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('menukunjungan.index')
            <li>
                <a href="#"><i class="fa fa-users"></i> <span class="nav-label">Kunjungan Sales</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('kunjungan.index')
                        <li class=""><a href="{{  route('kunjungan.index') }}">List Kunjungan Sales</a></li>
                    @endcan
                    @can('kunjungan.tambah')
                        <li class=""><a href="{{  route('kunjungan.tambah') }}">Tambah Daftar Kunjungan</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('menupurchaserder.index')
            <li>
                <a href="#"><i class="fa fa-list-alt"></i> <span class="nav-label">Penjualan Produk</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('purchaseorder.tambah')
                        <li><a href="{{ route('purchaseorder.index') }}">List Penjualan Produk</a></li>
                    @endcan
                    @can('purchaseorder.tambah')
                        <li class=""><a href="{{ route('purchaseorder.tambah') }}">Tambah Penjualan Produk</a></li>
                    @endcan
                    @can('purchaseorder.import')
                        <li class=""><a href="{{ route('purchaseorder.import') }}">Upload Penjualan</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('menupembelian.index')
            <li>
                <a href="#"><i class="fa fa-cart-plus"></i> <span class="nav-label">Pembelian Produk</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('pembelian.index')
                        <li class=""><a href="{{ route('pembelian.index') }}">List Pembelian Produk</a></li>
                    @endcan
                    @can('pembelian.tambah')
                        <li class=""><a href="{{ route('pembelian.tambah') }}">Tambah Pembelian Produk</a></li>
                    @endcan
                    @can('pembelian_import.import')
                        <li class=""><a href="{{ route('pembelian_import.import') }}">Upload Pembelian Produk</a></li>
                    @endcan

                </ul>
            </li>
            @endcan
            @can('menupengiriman.index')
            <li>
                <a href="#"><i class="fa fa-car"></i> <span class="nav-label">Pengiriman</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('deliveryorder.index')
                        <li class=""><a href="{{route('deliveryorder.index')}}">Delivery Order</a></li>
                    @endcan
                    @can('historydeliveryorder.index')
                        <li class=""><a href="{{route('historydeliveryorder.index')}}">History DO</a></li>
                    @endcan
                </ul>
            </li>
            @endcan

            @can('menureport.index')
            <li>
                <a href="#"><i class="fa fa-paste"></i> <span class="nav-label">Report</span><span
                        class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    @can('reportbarangmasuk.index')
                        <li><a href="{{route('reportbarangmasuk.index')}}">Report Barang Masuk</a></li>
                    @endcan
                    @can('reportkeuangan.index')
                        <li><a href="{{route('reportkeuangan.index')}}">Report Keuangan</a></li>
                    @endcan
                    @can('reportlabarugi.index')
                        <li><a href="{{route('reportlabarugi.index')}}">Report Laba Rugi</a></li>
                    @endcan
                    @can('reportbarangkeluar.index')
                        <li><a href="{{route('reportbarangkeluar.index')}}">Report Barang Keluar</a></li>
                    @endcan
                    @can('reportpenjualan.index')
                        <li><a href="{{route('reportpenjualan.index')}}">Report Penjualan</a></li>
                    @endcan
                    @can('reportdeliveryorder.index')
                        <li><a href="{{route('reportdeliveryorder.index')}}">Report Delivery Order</a></li>
                    @endcan
                </ul>
            </li>
            @endcan
            @can('security.index')
            <li>
                <a href="#"><i class="fa fa-key"></i> <span class="nav-label">Keamanan</span><span
                        class="fa arrow"></span></a>
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
