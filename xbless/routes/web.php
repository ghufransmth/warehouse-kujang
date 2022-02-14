<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KotaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\EngineController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\DiskonController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\SatuanController;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\DistrikController;
use App\Http\Controllers\JenisBayarController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StokAdjController;
use App\Http\Controllers\ExpedisiController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProvinsiController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportBoController;
use App\Http\Controllers\ReportPoController;
use App\Http\Controllers\ReportSoController;
use App\Http\Controllers\JenisTokoController;
use App\Http\Controllers\BackorderController;
use App\Http\Controllers\StokAdminController;
use App\Http\Controllers\StokSalesController;
use App\Http\Controllers\JenisHargaController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\ReportStokController;
use App\Http\Controllers\StokMutasiController;
use App\Http\Controllers\KategoriTokoController;
use App\Http\Controllers\KomponenController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\StokMutasiHistoryController;
use App\Http\Controllers\StokOpnameController;
use App\Http\Controllers\TypeChannelController;
use App\Http\Controllers\ReportBoQtyController;
use App\Http\Controllers\ReturRevisiController;
use App\Http\Controllers\TandaTerimaController;
use App\Http\Controllers\PurchaseBatalController;
use App\Http\Controllers\StokAdjHistoryController;
use App\Http\Controllers\ReportPenjualanController;
use App\Http\Controllers\RequestPurchaseController;
use App\Http\Controllers\ReportSisaHutangController;
use App\Http\Controllers\ReportTransaksiController;
use App\Http\Controllers\ReportBarangMasukController;
use App\Http\Controllers\ReportReturRevisiController;
use App\Http\Controllers\ReportTandaTerimaController;
use App\Http\Controllers\DraftPurchaseController;
use App\Http\Controllers\ImportPembelianController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\PenjualanImportController;
use App\Http\Controllers\ProdukImportController;
use App\Http\Controllers\ReportBarangKeluarController;
use App\Http\Controllers\ReportPembelianController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\KunjunganSalesController;
use App\Http\Controllers\ReportRekapInvoiceController;
use App\Http\Controllers\ReturPembelianController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\ReturPenjualanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/manage/login', [LoginController::class, 'index'])->name('manage.login');
Route::post('/manage/login', [LoginController::class, 'checkLogin'])->name('manage.checklogin');
Route::group(['middleware' => ['auth', 'acl:web']], function () {
    Route::get('/', [BerandaController::class, 'index'])->name('manage.beranda');
    Route::group(['prefix' => 'beranda', 'as' => 'beranda.'], function(){
        Route::post('/getdata', [BerandaController::class, 'getData'])->name('getdata');
        Route::group(['prefix' => 'unilever', 'as' => 'unilever.'], function(){
            Route::post('/getdataunlever', [BerandaController::class, 'getDataUnilever'])->name('getdata');
            Route::get('/getdata/{id}', [BerandaController::class, 'detailUnilever'])->name('detail');
        });
        Route::group(['prefix' => 'penjualan', 'as' => 'penjualan.'], function(){
            Route::post('/getdatapenjualan', [BerandaController::class, 'getDataPenjualan'])->name('getdata');
            Route::get('/getdata/{id}', [BerandaController::class, 'detailPenjualan'])->name('detail');
        });
    });

    Route::get('/manage/logout', [LoginController::class, 'logout'])->name('manage.logout');

    // STAFF
    Route::get('manage/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('manage/staff/getdata', [StaffController::class, 'getData'])->name('staff.getdata');
    Route::get('manage/staff/tambah', [StaffController::class, 'tambah'])->name('staff.tambah');
    Route::get('manage/staff/detail/{id}', [StaffController::class, 'detail'])->name('staff.detail');
    Route::get('manage/staff/ubah/{id}', [StaffController::class, 'ubah'])->name('staff.ubah');
    Route::delete('manage/staff/hapus/{id?}', [StaffController::class, 'hapus'])->name('staff.hapus');
    Route::post('manage/staff/simpan', [StaffController::class, 'simpan'])->name('staff.simpan');


    //PERMISSION
    Route::get('manage/permission', [PermissionController::class, 'index'])->name('permission.index');
    Route::get('manage/permission/tambah', [PermissionController::class, 'tambah'])->name('permission.tambah');
    Route::get('manage/permission/ubah/{id}', [PermissionController::class, 'ubah'])->name('permission.ubah');
    Route::post('manage/permission/simpan/{id?}', [PermissionController::class, 'simpan'])->name('permission.simpan');
    Route::get('manage/permission/sidebar', [PermissionController::class, 'sidebar'])->name('permission.sidebar');

    //ROLE
    Route::get('manage/role', [RoleController::class, 'index'])->name('role.index');
    Route::get('manage/role/lihat/{id}', [RoleController::class, 'lihat'])->name('role.lihat');
    Route::get('manage/role/tambah', [RoleController::class, 'form'])->name('role.tambah');
    Route::get('manage/role/ubah/{id}', [RoleController::class, 'form'])->name('role.ubah');
    Route::get('manage/role/user/{id}', [RoleController::class, 'formuser'])->name('role.user');
    Route::post('manage/role/tambah', [RoleController::class, 'save'])->name('role.tambah');
    Route::post('manage/role/ubah/{id}', [RoleController::class, 'save'])->name('role.ubah');
    Route::post('manage/role/user/{id}', [RoleController::class, 'saveuser'])->name('role.user');
    Route::post('manage/role/getdata', [RoleController::class, 'getData'])->name('role.getdata');
    Route::delete('manage/role/hapus/{id?}', [RoleController::class, 'delete'])->name('role.hapus');

    //PROFILE
    Route::get('manage/profil', [StaffController::class, 'profil'])->name('profil.index');
    Route::post('manage/profil/simpan', [StaffController::class, 'profilSimpan'])->name('profil.simpan');
    Route::get('manage/newpassword', [StaffController::class, 'profilPassword'])->name('profil.password');
    Route::post('manage/password/simpan', [StaffController::class, 'profilNewPassword'])->name('profil.simpanpassword');


    // EXPEDISI
    Route::get('manage/expedisi', [ExpedisiController::class, 'index'])->name('expedisi.index');
    Route::post('manage/expedisi/getdata', [ExpedisiController::class, 'getData'])->name('expedisi.getdata');
    Route::get('manage/expedisi/tambah', [ExpedisiController::class, 'tambah'])->name('expedisi.tambah');
    Route::get('manage/expedisi/detail/{id}', [ExpedisiController::class, 'detail'])->name('expedisi.detail');
    Route::get('manage/expedisi/ubah/{id}', [ExpedisiController::class, 'ubah'])->name('expedisi.ubah');
    Route::delete('manage/expedisi/hapus/{id?}', [ExpedisiController::class, 'hapus'])->name('expedisi.hapus');
    Route::post('manage/expedisi/simpan', [ExpedisiController::class, 'simpan'])->name('expedisi.simpan');

    // SALES
    Route::get('manage/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('manage/sales/getdata', [SalesController::class, 'getData'])->name('sales.getdata');
    Route::get('manage/sales/tambah', [SalesController::class, 'tambah'])->name('sales.tambah');
    Route::get('manage/sales/getsales', [SalesController::class, 'getSales'])->name('sales.getSales');
    Route::get('manage/sales/detail/{id}', [SalesController::class, 'detail'])->name('sales.detail');
    Route::get('manage/sales/ubah/{id}', [SalesController::class, 'ubah'])->name('sales.ubah');
    Route::delete('manage/sales/hapus/{id?}', [SalesController::class, 'hapus'])->name('sales.hapus');
    Route::post('manage/sales/simpan', [SalesController::class, 'simpan'])->name('sales.simpan');
    Route::post('manage/sales/resetapp', [SalesController::class, 'resetAPP'])->name('sales.resetapp');

    // MEMBER
    Route::get('manage/member', [MemberController::class, 'index'])->name('member.index');
    Route::post('manage/member/getdata', [MemberController::class, 'getData'])->name('member.getdata');
    Route::get('manage/member/tambah', [MemberController::class, 'tambah'])->name('member.tambah');
    Route::get('manage/member/detail/{id}', [MemberController::class, 'detail'])->name('member.detail');
    Route::get('manage/member/ubah/{id}', [MemberController::class, 'ubah'])->name('member.ubah');
    Route::delete('manage/member/hapus/{id?}', [MemberController::class, 'hapus'])->name('member.hapus');
    Route::post('manage/member/simpan', [MemberController::class, 'simpan'])->name('member.simpan');
    Route::post('manage/member_sales', [MemberController::class, 'member_sales'])->name('member.member_sales');
    Route::post('manage/simpan_member_sales', [MemberController::class, 'simpan_member_sales'])->name('member.simpan_member_sales');
    Route::post('manage/member/resetapp', [MemberController::class, 'resetAPP'])->name('member.resetapp');

    // Brand
    Route::get('manage/brand', [BrandController::class, 'index'])->name('brand.index');
    Route::post('manage/brand/getdata', [BrandController::class, 'getData'])->name('brand.getdata');
    Route::get('manage/brand/tambah', [BrandController::class, 'tambah'])->name('brand.tambah');
    Route::get('manage/brand/ubah/{id}', [BrandController::class, 'ubah'])->name('brand.ubah');
    Route::delete('manage/brand/hapus/{id?}', [BrandController::class, 'hapus'])->name('brand.hapus');
    Route::post('manage/brand/simpan', [BrandController::class, 'simpan'])->name('brand.simpan');

    // Engine
    Route::get('manage/engine', [EngineController::class, 'index'])->name('engine.index');
    Route::post('manage/engine/getdata', [EngineController::class, 'getData'])->name('engine.getdata');
    Route::get('manage/engine/tambah', [EngineController::class, 'tambah'])->name('engine.tambah');
    Route::get('manage/engine/ubah/{id}', [EngineController::class, 'ubah'])->name('engine.ubah');
    Route::delete('manage/engine/hapus/{id?}', [EngineController::class, 'hapus'])->name('engine.hapus');
    Route::post('manage/engine/simpan', [EngineController::class, 'simpan'])->name('engine.simpan');


    // PROVINSI
    Route::get('manage/provinsi', [ProvinsiController::class, 'index'])->name('provinsi.index');
    Route::post('manage/provinsi/getdata', [ProvinsiController::class, 'getData'])->name('provinsi.getdata');
    Route::get('manage/provinsi/tambah', [ProvinsiController::class, 'tambah'])->name('provinsi.tambah');
    Route::get('manage/provinsi/ubah/{id}', [ProvinsiController::class, 'ubah'])->name('provinsi.ubah');
    Route::delete('manage/provinsi/hapus/{id?}', [ProvinsiController::class, 'hapus'])->name('provinsi.hapus');
    Route::post('manage/provinsi/simpan', [ProvinsiController::class, 'simpan'])->name('provinsi.simpan');

    // KOTA
    Route::get('manage/kota', [KotaController::class, 'index'])->name('kota.index');
    Route::post('manage/kota/getdata', [KotaController::class, 'getData'])->name('kota.getdata');
    Route::get('manage/kota/tambah', [KotaController::class, 'tambah'])->name('kota.tambah');
    Route::get('manage/kota/provinsi/{id?}', [KotaController::class, 'provinsi'])->name('kota.provinsi');
    Route::get('manage/kota/ubah/{id}', [KotaController::class, 'ubah'])->name('kota.ubah');
    Route::delete('manage/kota/hapus/{id?}', [KotaController::class, 'hapus'])->name('kota.hapus');
    Route::post('manage/kota/simpan', [KotaController::class, 'simpan'])->name('kota.simpan');


    //Informasi Stok Admin
    Route::get('manage/stokadmin', [StokAdminController::class, 'index'])->name('stokadmin.index');
    Route::post('manage/stokadmin/getdata', [StokAdminController::class, 'getData'])->name('stokadmin.getdata');

    //Informasi Stok Sales
    Route::get('manage/stoksales', [StokSalesController::class, 'index'])->name('stoksales.index');
    Route::post('manage/stoksales/getdata', [StokSalesController::class, 'getData'])->name('stoksales.getdata');

    //Adj Stock
    Route::get('manage/adjstok', [StokAdjController::class, 'index'])->name('adjstok.index');
    Route::post('manage/adjstok/getdata', [StokAdjController::class, 'getData'])->name('adjstok.getdata');
    Route::get('manage/adjstok/getdataproduct/{id?}', [StokAdjController::class, 'getDataProduct'])->name('adjstok.getdataproduct');
    Route::post('manage/adjstok/simpan', [StokAdjController::class, 'simpan'])->name('adjstok.simpan');
    Route::get('manage/adjstok/tambah', [StokAdjController::class, 'tambah'])->name('adjstok.tambah');

    //History Adj Stock
    Route::get('manage/historyadjstok', [StokAdjHistoryController::class, 'index'])->name('historyadjstok.index');
    Route::post('manage/historyadjstok/getdata', [StokAdjHistoryController::class, 'getData'])->name('historyadjstok.getdata');
    Route::get('manage/historyadjstok/print', [StokAdjHistoryController::class, 'print'])->name('historyadjstok.print');
    Route::get('manage/historyadjstok/pdf', [StokAdjHistoryController::class, 'pdf'])->name('historyadjstok.pdf');
    Route::get('manage/historyadjstok/excel', [StokAdjHistoryController::class, 'excel'])->name('historyadjstok.excel');

    //STOK MUTASI
    Route::get('manage/stokmutasi', [StokMutasiController::class, 'tambah'])->name('stokmutasi.tambah');
    Route::post('manage/stokmutasi/simpan', [StokMutasiController::class, 'simpan'])->name('stokmutasi.simpan');
    Route::get('manage/stokmutasi/getgudang/{id?}', [StokMutasiController::class, 'perusahaan_gudang'])->name('stokmutasi.perusahaan_gudang');
    Route::get('manage/stokmutasi/getproduk', [StokMutasiController::class, 'getProduk'])->name('stokmutasi.getproduct');
    Route::post('manage/stokmutasi/tambahproduk', [StokMutasiController::class, 'tambahProduk'])->name('stokmutasi.tambahproduk');


    //History Mutasi Stock
    Route::get('manage/historymutasistok', [StokMutasiHistoryController::class, 'index'])->name('historymutasistok.index');
    Route::post('manage/historymutasistok/getdata', [StokMutasiHistoryController::class, 'getData'])->name('historymutasistok.getdata');
    Route::get('manage/historymutasistok/print', [StokMutasiHistoryController::class, 'print'])->name('historymutasistok.print');
    Route::get('manage/historymutasistok/pdf', [StokMutasiHistoryController::class, 'pdf'])->name('historymutasistok.pdf');
    Route::get('manage/historymutasistok/excel', [StokMutasiHistoryController::class, 'excel'])->name('historymutasistok.excel');

    // STOK OPNAME
    Route::get('manage/stokopname', [StokOpnameController::class, 'index'])->name('stokopname.index');
    Route::get('manage/stokopname/tambah', [StokOpnameController::class, 'tambah'])->name('stokopname.tambah');
    Route::post('manage/stokopname/getdata', [StokOpnameController::class, 'getData'])->name('stokopname.getdata');
    Route::post('manage/stokopname/simpan', [StokOpnameController::class, 'simpan'])->name('stokopname.simpan');
    Route::get('manage/stokopname/detail/{id}', [StokOpnameController::class, 'detail'])->name('stokopname.detail');
    Route::get('manage/stokopname/print/{id}', [StokOpnameController::class, 'print'])->name('stokopname.print');
    Route::post('manage/stokopname/detaildataform', [StokOpnameController::class, 'detaildataform'])->name('stokopname.detaildataform');
    Route::get('manage/stockopname/detaildata/{id?}', [StokOpnameController::class, 'getdatadetail'])->name('stockopname.getdatadetail');

    Route::get('manage/stokopname/opname/getgudang/{id?}', [StokOpnameController::class, 'perusahaan_gudang'])->name('stokopname.perusahaan_gudang');
    Route::get('manage/stokopname/getproduk', [StokOpnameController::class, 'getProduk'])->name('stokopname.getproduct');
    Route::post('manage/stokopname/tambahproduk', [StokOpnameController::class, 'tambahProduk'])->name('stokopname.tambahproduk');
    Route::post('manage/stokopname/tambahprodukbarcode', [StokOpnameController::class, 'tambahProdukBarcode'])->name('stokopname.tambahprodukbarcode');

    //REPORT
    //REPORT SO
    Route::get('manage/reportso', [ReportSoController::class, 'index'])->name('reportso.index');
    Route::post('manage/reportso/cekdata', [ReportSoController::class, 'cekData'])->name('reportso.cekdata');
    Route::get('manage/reportso/print/{perusahaan?}/{tgl?}', [ReportSoController::class, 'print'])->name('reportso.print');
    Route::get('manage/reportso/pdf/{perusahaan?}/{tgl?}', [ReportSoController::class, 'pdf'])->name('reportso.pdf');
    Route::get('manage/reportso/excel/{perusahaan?}/{tgl?}', [ReportSoController::class, 'excel'])->name('reportso.excel');
    //REPORT PO
    Route::get('manage/reportpo', [ReportPoController::class, 'index'])->name('reportpo.index');
    Route::post('manage/reportpo/cekdata', [ReportPoController::class, 'cekData'])->name('reportpo.cekdata');
    Route::get('manage/reportpo/print/{perusahaan?}/{tgl?}', [ReportPoController::class, 'print'])->name('reportpo.print');
    Route::get('manage/reportpo/pdf/{perusahaan?}/{tgl?}', [ReportPoController::class, 'pdf'])->name('reportpo.pdf');
    Route::get('manage/reportpo/excel/{perusahaan?}/{tgl?}', [ReportPoController::class, 'excel'])->name('reportpo.excel');

    //REPORT BO
    Route::get('manage/reportbo', [ReportBoController::class, 'index'])->name('reportbo.index');
    Route::post('manage/reportbo/getdata', [ReportBoController::class, 'getData'])->name('reportbo.getdata');
    Route::get('manage/reportbo/print', [ReportBoController::class, 'print'])->name('reportbo.print');
    Route::get('manage/reportbo/pdf', [ReportBoController::class, 'pdf'])->name('reportbo.pdf');
    Route::get('manage/reportbo/excel', [ReportBoController::class, 'excel'])->name('reportbo.excel');
    //REPORT BO QTY
    Route::get('manage/reportboqty', [ReportBoQtyController::class, 'index'])->name('reportboqty.index');
    Route::post('manage/reportboqty/getdata', [ReportBoQtyController::class, 'getData'])->name('reportboqty.getdata');
    Route::get('manage/reportboqty/print', [ReportBoQtyController::class, 'print'])->name('reportboqty.print');
    Route::get('manage/reportboqty/pdf', [ReportBoQtyController::class, 'pdf'])->name('reportboqty.pdf');
    Route::get('manage/reportboqty/excel', [ReportBoQtyController::class, 'excel'])->name('reportboqty.excel');

    //REPORT PENJUALAN
    Route::get('manage/reportpenjualan', [ReportPenjualanController::class, 'index'])->name('reportpenjualan.index');
    Route::post('manage/reportpenjualan/getdata', [ReportPenjualanController::class, 'getData'])->name('reportpenjualan.getdata');
    Route::get('manage/reportpenjualan/print', [ReportPenjualanController::class, 'print'])->name('reportpenjualan.print');
    Route::get('manage/reportpenjualan/pdf', [ReportPenjualanController::class, 'pdf'])->name('reportpenjualan.pdf');
    Route::get('manage/reportpenjualan/excel', [ReportPenjualanController::class, 'excel'])->name('reportpenjualan.excel');

    // REPORT PEMBELIAN
    Route::get('manage/reportpembelian', [ReportPembelianController::class, 'index'])->name('reportpembelian.index');
    Route::post('manage/reportpembelian/getdata', [ReportPembelianController::class, 'getData'])->name('reportpembelian.getdata');
    Route::post('manage/reportpembelian/cekdata', [ReportPembelianController::class, 'cekData'])->name('reportpembelian.cekdata');

    //REPORT STOK
    Route::get('manage/reportstok', [ReportStokController::class, 'index'])->name('reportstok.index');
    Route::post('manage/reportstok/getdata', [ReportStokController::class, 'getData'])->name('reportstok.getdata');
    Route::get('manage/reportstok/print', [ReportStokController::class, 'print'])->name('reportstok.print');
    Route::get('manage/reportstok/pdf', [ReportStokController::class, 'pdf'])->name('reportstok.pdf');
    Route::get('manage/reportstok/excel', [ReportStokController::class, 'excel'])->name('reportstok.excel');
    Route::post('manage/reportstok/searchdata', [ReportStokController::class, 'searchData'])->name('reportstok.searchdata');
    Route::post('manage/reportstok/getallstock', [ReportStokController::class, 'productBeliDetail'])->name('reportstok.getallstock');

    //REPORT BARANG MASUK
    Route::get('manage/reportbarangmasuk', [ReportBarangMasukController::class, 'index'])->name('reportbarangmasuk.index');
    Route::post('manage/reportbarangmasuk/getdata', [ReportBarangMasukController::class, 'getData'])->name('reportbarangmasuk.getdata');
    Route::get('/detail/{id}', [ReportBarangMasukController::class, 'detail'])->name('reportbarangmasuk.detail');
    Route::get('manage/reportbarangmasuk/print', [ReportBarangMasukController::class, 'print'])->name('reportbarangmasuk.print');
    Route::get('manage/reportbarangmasuk/pdf', [ReportBarangMasukController::class, 'pdf'])->name('reportbarangmasuk.pdf');
    Route::get('manage/reportbarangmasuk/excel', [ReportBarangMasukController::class, 'excel'])->name('reportbarangmasuk.excel');

    //REPORT BARANG KELUAR
    Route::get('manage/reportbarangkeluar', [ReportBarangKeluarController::class, 'index'])->name('reportbarangkeluar.index');
    Route::post('manage/reportbarangkeluar/getdata', [ReportBarangKeluarController::class, 'getData'])->name('reportbarangkeluar.getdata');
    Route::get('manage/reportbarangkeluar/print', [ReportBarangKeluarController::class, 'print'])->name('reportbarangkeluar.print');
    Route::get('manage/reportbarangkeluar/pdf', [ReportBarangKeluarController::class, 'pdf'])->name('reportbarangkeluar.pdf');
    Route::get('manage/reportbarangkeluar/excel', [ReportBarangKeluarController::class, 'excel'])->name('reportbarangkeluar.excel');

    //REPORT INVOICE
    Route::get('manage/reportinvoice', [ReportRekapInvoiceController::class, 'index'])->name('reportinvoice.index');
    Route::post('manage/reportinvoice/getdata', [ReportRekapInvoiceController::class, 'getData'])->name('reportinvoice.getdata');
    Route::get('manage/reportinvoice/print', [ReportRekapInvoiceController::class, 'print'])->name('reportinvoice.print');
    Route::get('manage/reportinvoice/pdf', [ReportRekapInvoiceController::class, 'pdf'])->name('reportinvoice.pdf');
    Route::get('manage/reportinvoice/excel', [ReportRekapInvoiceController::class, 'excel'])->name('reportinvoice.excel');


    //REPORT RETUR REVISI
    Route::get('manage/reportreturrevisi', [ReportReturRevisiController::class, 'index'])->name('reportreturrevisi.index');
    Route::post('manage/reportreturrevisi/getdata', [ReportReturRevisiController::class, 'getData'])->name('reportreturrevisi.getdata');
    Route::get('manage/reportreturrevisi/print', [ReportReturRevisiController::class, 'print'])->name('reportreturrevisi.print');
    Route::get('manage/reportreturrevisi/pdf', [ReportReturRevisiController::class, 'pdf'])->name('reportreturrevisi.pdf');
    Route::get('manage/reportreturrevisi/excel', [ReportReturRevisiController::class, 'excel'])->name('reportreturrevisi.excel');

    //REPORT TANDA TERIMA
    Route::get('manage/reporttandaterima', [ReportTandaTerimaController::class, 'index'])->name('reporttandaterima.index');
    Route::post('manage/reporttandaterima/getdata', [ReportTandaTerimaController::class, 'getData'])->name('reporttandaterima.getdata');
    Route::get('manage/reporttandaterima/print', [ReportTandaTerimaController::class, 'print'])->name('reporttandaterima.print');
    Route::get('manage/reporttandaterima/pdf', [ReportTandaTerimaController::class, 'pdf'])->name('reporttandaterima.pdf');
    Route::get('manage/reporttandaterima/excel', [ReportTandaTerimaController::class, 'excel'])->name('reporttandaterima.excel');
    Route::post('manage/export/reporttandaterima', [ReportTandaTerimaController::class, 'manageExport'])->name('reporttandaterima.manageexport');

    //REPORT SISA HUTANG
    Route::get('manage/reportsisahutang', [ReportSisaHutangController::class, 'index'])->name('reportsisahutang.index');
    Route::post('manage/reportsisahutang/getdata', [ReportSisaHutangController::class, 'getData'])->name('reportsisahutang.getdata');
    Route::get('manage/reportsisahutang/print', [ReportSisaHutangController::class, 'print'])->name('reportsisahutang.print');
    Route::get('manage/reportsisahutang/pdf', [ReportSisaHutangController::class, 'pdf'])->name('reportsisahutang.pdf');
    Route::get('manage/reportsisahutang/excel', [ReportSisaHutangController::class, 'excel'])->name('reportsisahutang.excel');


    //DRAFT PO

    Route::get('/manage/draftpurchaseorder', [DraftPurchaseController::class, 'index'])->name('draftpurchaseorder.index');
    Route::post('manage/draftpurchaseorder/getdata', [DraftPurchaseController::class, 'getData'])->name('draftpurchaseorder.getdata');
    Route::get('manage/draftpurchaseorder/ubah/{id}', [DraftPurchaseController::class, 'ubah'])->name('draftpurchaseorder.ubah');
    Route::post('manage/draftpurchaseorder/simpan', [DraftPurchaseController::class, 'simpan'])->name('draftpurchaseorder.simpan');
    Route::delete('manage/draftpurchaseorder/hapus/{id?}', [DraftPurchaseController::class, 'hapus'])->name('draftpurchaseorder.hapus');

    Route::group(['prefix' => 'pembayaran', 'as' => 'pembayaran.'], function () {
        Route::get('/', [PembayaranController::class, 'index'])->name('index');
        Route::any('/search', [PembayaranController::class, 'search'])->name('search');
        Route::get('/detail/{id}', [PembayaranController::class, 'detail'])->name('detail');
        Route::post('/store', [PembayaranController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [PembayaranController::class, 'edit'])->name('edit');
        Route::post('/update/{id}', [PembayaranController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [PembayaranController::class, 'delete'])->name('delete');
        Route::post('/list_menu', [PembayaranController::class, 'menu_data_list'])->name('menu_data_list');
        Route::get('/data_pembayaran/{menu?}/{id?}', [PembayaranController::class, 'data_pembayaran'])->name('data_pembayaran');
        Route::post('/input_pengiriman', [PembayaranController::class, 'input_pengiriman'])->name('input_pengiriman');
        Route::post('/simpan_pengiriman', [PembayaranController::class, 'simpan_pengiriman'])->name('simpan_pengiriman');
    });

    //retur revisi
    Route::group(['prefix' => 'retur-revisi', 'as' => 'returrevisi.'], function () {
        Route::get('/', [ReturRevisiController::class, 'index'])->name('index');
        Route::post('/search', [ReturRevisiController::class, 'search'])->name('search');
        Route::get('/detail/{type}/{id}', [ReturRevisiController::class, 'detail'])->name('detail');
        Route::post('/store/{type}/{id}', [ReturRevisiController::class, 'store'])->name('store');
        Route::get('/log-retur/{type}/{id}', [ReturRevisiController::class, 'logRetur'])->name('log.retur');
        Route::get('/print-log-retur/{id}', [ReturRevisiController::class, 'logReturPrint'])->name('log.retur.print');
        Route::post('/update/{id}', [ReturRevisiController::class, 'update'])->name('update');
        Route::get('/delete/{id}', [ReturRevisiController::class, 'delete'])->name('delete');
    });

    Route::group(['prefix' => '/manage'], function () {
        // TYPE CHANNEL
        Route::group(['prefix' => 'type_channel', 'as' => 'type_channel.'], function(){
            Route::get('manage/type_channel', [TypeChannelController::class, 'index'])->name('index');
            Route::post('manage/type_channel/getdata', [TypeChannelController::class, 'getData'])->name('getdata');
            Route::get('manage/type_channel/tambah', [TypeChannelController::class, 'tambah'])->name('tambah');
            Route::get('manage/type_channel/ubah/{id}', [TypeChannelController::class, 'ubah'])->name('ubah');
            Route::delete('manage/type_channel/hapus/{id?}', [TypeChannelController::class, 'hapus'])->name('delete');
            Route::post('manage/type_channel/simpan', [TypeChannelController::class, 'simpan'])->name('simpan');
        });

        //PAYMENT
        Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
            Route::get('/', [JenisBayarController::class, 'index'])->name('index');
            Route::get('/tambah', [JenisBayarController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [JenisBayarController::class, 'ubah'])->name('ubah');
            Route::get('/parent', [JenisBayarController::class, 'parent'])->name('parent');
            Route::post('/getData', [JenisBayarController::class, 'getData'])->name('getdata');
            Route::post('/simpan', [JenisBayarController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [JenisBayarController::class, 'delete'])->name('delete');
        });

        //NEGARA
        Route::group(['prefix' => 'distrik', 'as' => 'distrik.'], function () {
            Route::get('/', [DistrikController::class, 'index'])->name('index');
            Route::get('/tambah', [DistrikController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [DistrikController::class, 'ubah'])->name('ubah');
            Route::post('/getData', [DistrikController::class, 'getData'])->name('getdata');
            Route::post('/simpan', [DistrikController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [DistrikController::class, 'delete'])->name('delete');
        });

        // GUDANG
        Route::group(['prefix' => 'gudang', 'as' => 'gudang.'], function () {
            Route::get('/', [GudangController::class, 'index'])->name('index');
            Route::get('/tambah', [GudangController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [GudangController::class, 'ubah'])->name('ubah');
            Route::post('/getData', [GudangController::class, 'getData'])->name('getdata');
            Route::post('/simpan', [GudangController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [GudangController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'diskon', 'as' => 'diskon.'], function () {
            Route::get('/', [DiskonController::class, 'index'])->name('index');
            Route::get('/tambah', [DiskonController::class, 'tambah'])->name('tambah');
            Route::get('/product', [DiskonController::class, 'get_product'])->name('product');
            Route::get('/ubah/{id}', [DiskonController::class, 'ubah'])->name('ubah');
            Route::post('/getData', [DiskonController::class, 'getData'])->name('getdata');
            Route::post('/simpan', [DiskonController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [DiskonController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'toko', 'as' => 'toko.'], function () {
            Route::group(['prefix' => 'jenis', 'as' => 'jenis.'], function () {
                Route::get('/', [JenisTokoController::class, 'index'])->name('index');
                Route::get('/tambah', [JenisTokoController::class, 'tambah'])->name('tambah');
                Route::get('/ubah/{id}', [JenisTokoController::class, 'ubah'])->name('ubah');
                Route::post('/getData', [JenisTokoController::class, 'getData'])->name('getdata');
                Route::post('/simpan', [JenisTokoController::class, 'simpan'])->name('simpan');
                Route::delete('/hapus/{id?}', [JenisTokoController::class, 'delete'])->name('delete');
            });

            Route::group(['prefix' => 'kategori', 'as' => 'kategori.'], function () {
                Route::get('/', [KategoriTokoController::class, 'index'])->name('index');
                Route::get('/tambah', [KategoriTokoController::class, 'tambah'])->name('tambah');
                Route::get('/ubah/{id}', [KategoriTokoController::class, 'ubah'])->name('ubah');
                Route::post('/getData', [KategoriTokoController::class, 'getData'])->name('getdata');
                Route::post('/simpan', [KategoriTokoController::class, 'simpan'])->name('simpan');
                Route::delete('/hapus/{id?}', [KategoriTokoController::class, 'delete'])->name('delete');
            });
        });

        //PERUSAHAAN
        Route::group(['prefix' => 'perusahaan', 'as' => 'perusahaan.'], function () {
            Route::get('/', [PerusahaanController::class, 'index'])->name('index');
            Route::get('/tambah', [PerusahaanController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [PerusahaanController::class, 'ubah'])->name('ubah');
            Route::get('/detail/{id}', [PerusahaanController::class, 'detail'])->name('detail');
            Route::post('/perusahaan_gudang', [PerusahaanController::class, 'perusahaan_gudang'])->name('gudang');
            Route::post('/simpan_perusahaan_gudang', [PerusahaanController::class, 'simpan_perusahaan_gudang'])->name('simpangudang');
            Route::post('/getData', [PerusahaanController::class, 'getData'])->name('getdata');
            Route::post('/simpan', [PerusahaanController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [PerusahaanController::class, 'delete'])->name('delete');
        });

        // KATEGORI
        Route::group(['prefix' => 'kategori', 'as' => 'kategori.'], function () {
            Route::get('/', [KategoriController::class, 'index'])->name('index');
            Route::get('/tambah', [KategoriController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [KategoriController::class, 'ubah'])->name('ubah');
            Route::post('/getData', [KategoriController::class, 'getdata'])->name('getdata');
            Route::post('/simpan', [KategoriController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [KategoriController::class, 'delete'])->name('delete');
        });

        // KOMPONEN
        Route::group(['prefix' => 'komponen', 'as' => 'komponen.'], function () {
            Route::get('/', [KomponenController::class, 'index'])->name('index');
            Route::get('/tambah', [KomponenController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [KomponenController::class, 'ubah'])->name('ubah');
            Route::post('/getData', [KomponenController::class, 'getdata'])->name('getdata');
            Route::post('/simpan', [KomponenController::class, 'simpan'])->name('simpan');
            Route::delete('/hapus/{id?}', [KomponenController::class, 'delete'])->name('delete');
        });

        // SATUAN
        Route::group(['prefix' => 'satuan', 'as' => 'satuan.'], function () {
            Route::get('/', [SatuanController::class, 'index'])->name('index');
            Route::get('/tambah', [SatuanController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [SatuanController::class, 'ubah'])->name('ubah');
            Route::post('/simpan', [SatuanController::class, 'simpan'])->name('simpan');
            Route::post('/getData', [SatuanController::class, 'getdata'])->name('getdata');
            Route::delete('/hapus/{id?}', [SatuanController::class, 'delete'])->name('delete');
        });

        // JENIS HARGA
        Route::group(['prefix' => 'jenisharga', 'as' => 'jenisharga.'], function () {
            Route::get('/', [JenisHargaController::class, 'index'])->name('index');
            Route::get('/tambah', [JenisHargaController::class, 'tambah'])->name('tambah');
            Route::get('/ubah/{id}', [JenisHargaController::class, 'ubah'])->name('ubah');
            Route::post('/simpan', [JenisHargaController::class, 'simpan'])->name('simpan');
            Route::post('/getData', [JenisHargaController::class, 'getdata'])->name('getdata');
            Route::delete('/hapus/{id?}', [JenisHargaController::class, 'delete'])->name('delete');
        });

        // PRODUK

        Route::get('/tambahproduk', [ProdukController::class, 'tambah'])->name('produk.tambah');
        Route::group(['prefix' => 'produk', 'as' => 'produk.'], function () {
            Route::get('/', [ProdukController::class, 'index'])->name('index');
            // Route::get('/tambah', [ProdukController::class, 'tambah'])->name('tambah');
            Route::get('/detail/{id}', [ProdukController::class, 'detail'])->name('detail');
            Route::get('/ubah/{id}', [ProdukController::class, 'ubah'])->name('ubah');
            Route::post('/simpan', [ProdukController::class, 'simpan'])->name('simpan');
            Route::post('/getData', [ProdukController::class, 'getdata'])->name('getdata');
            Route::post('/produk_image', [ProdukController::class, 'ProdukImage'])->name('image');
            Route::post('/delete_qrcode', [ProdukController::class, 'delete_qrcode'])->name('delete_qrcode');
            Route::delete('/hapus/{id?}', [ProdukController::class, 'delete'])->name('delete');

            //IMPORT PRODUCT
            Route::get('/import', [ProdukImportController::class, 'index'])->name('import');
            Route::get('/importsimpan', [ProdukImportController::class, 'importsimpan'])->name('importsimpan');
            Route::get('/importbatal', [ProdukImportController::class, 'importbatal'])->name('importbatal');
            Route::post('/uploadimport', [ProdukImportController::class, 'import'])->name('uploadimport');
            Route::post('/deleteimport', [ProdukImportController::class, 'hapus'])->name('deleteimport');
        });

        //PURCHASEORDER
        Route::get('/tambahpo', [PurchaseController::class, 'tambah'])->name('purchaseorder.tambah');
        Route::group(['prefix' => 'purchaseorder', 'as' => 'purchaseorder.'], function () {
            Route::get('/', [PurchaseController::class, 'index'])->name('index');
            // Route::get('/tambah', [PurchaseController::class, 'tambah'])->name('tambah');
            Route::get('/print/{id?}', [PurchaseController::class, 'print'])->name('print');
            Route::get('/showpo/{id?}', [PurchaseController::class, 'showpo'])->name('showpo');
            Route::get('/check_gudang/{id?}', [PurchaseController::class, 'check_gudang'])->name('check_gudang');
            Route::get('/search_produk', [PurchaseController::class, 'search_produk'])->name('search');
            Route::get('/search_satuan', [PurchaseController::class, 'search_satuan'])->name('search_satuan');
            Route::post('/expedisi', [PurchaseController::class, 'expedisi'])->name('expedisi');
            Route::post('/simpanexpedisi', [PurchaseController::class, 'simpan_expedisi'])->name('simpanexpedisi');
            Route::post('/simpan', [PurchaseController::class, 'simpan'])->name('simpan');
            Route::post('/addproduk', [PurchaseController::class, 'addproduk'])->name('addproduk');
            Route::post('/updatepo', [PurchaseController::class, 'updatepo'])->name('updatepo');
            Route::post('/updatepokrnditolak', [PurchaseController::class, 'updatepokrnditolak'])->name('updatepokrnditolak');
            Route::get('/edit/{id?}', [PurchaseController::class, 'edit'])->name('edit');

            //IMPORT PENJUALAN
            Route::get('/import', [PenjualanImportController::class, 'index'])->name('import');
            Route::post('/importsimpan', [PenjualanImportController::class, 'importsimpan'])->name('importsimpan');
            Route::get('/importbatal', [PenjualanImportController::class, 'importbatal'])->name('importbatal');
            Route::post('/uploadimport', [PenjualanImportController::class, 'import'])->name('uploadimport');
            Route::post('/deleteimport', [PenjualanImportController::class, 'hapus'])->name('deleteimport');

            //Approve & Reject Penjualan
            Route::get('/approve/{id?}', [PurchaseController::class, 'approve'])->name('approve');
            Route::get('/reject/{id?}', [PurchaseController::class, 'reject'])->name('reject');


            Route::post('/note', [PurchaseController::class, 'note'])->name('note');
            Route::post('/harga_product', [PurchaseController::class, 'harga_product'])->name('harga_product');
            Route::post('/total_harga', [PurchaseController::class, 'total_harga'])->name('total_harga');
            Route::post('/status_po', [PurchaseController::class, 'status_po'])->name('status_po');

            Route::post('/scan_qty_kirim', [PurchaseController::class, 'scan_qty_kirim'])->name('scan_qty_kirim');
            Route::post('/status_gudang', [PurchaseController::class, 'status_gudang'])->name('status_gudang');
            Route::post('/status_invoice', [PurchaseController::class, 'status_invoice'])->name('status_invoice');
            Route::post('/status_invoice_awal', [PurchaseController::class, 'status_invoice_awal'])->name('status_invoice_awal');

            Route::post('/detail', [PurchaseController::class, 'detail'])->name('detail');
            Route::post('/getData', [PurchaseController::class, 'getdata'])->name('getdata');
            Route::post('/process_nota', [PurchaseController::class, 'process_nota'])->name('process_nota');
            Route::post('/cekstatusinvoice', [PurchaseController::class, 'cekInvoiceBelumLunas'])->name('cekstatusinvoice');

            Route::delete('/hapus/{id?}', [PurchaseController::class, 'delete'])->name('delete');
        });

        //RETUR
        Route::group(['prefix' => 'retur', 'as' => 'retur.'], function () {
            Route::get('/', [ReturController::class, 'index'])->name('index');
            Route::get('/form_retur', [ReturController::class, 'index_retur'])->name('index_retur');
            Route::post('/getData', [ReturController::class, 'getdata'])->name('getdata');
            Route::post('/getDataRetur', [ReturController::class, 'getdata_retur'])->name('getdata_retur');
            Route::get('/penjualan', [ReturController::class, 'retur_penjualan'])->name('returpenjualan');
            Route::get('/list_transaksi',[ReturController::class, 'list_transaksi'])->name('list_transaksi');
            Route::get('/penjualan/{nofaktur?}', [ReturPenjualanController::class, 'form_retur'])->name('retur_penjualan');
            Route::post('/simpan', [ReturPenjualanController::class, 'simpan'])->name('simpan');
        });
        // REQUEST PURCHASE ORDER
        Route::group(['prefix' => 'requestpurchaseorder', 'as' => 'requestpurchaseorder.'], function () {
            Route::get('/', [RequestPurchaseController::class, 'index'])->name('index');
            Route::get('/detail/{id}', [RequestPurchaseController::class, 'detail'])->name('detail');
            Route::get('/pdf/{id?}', [RequestPurchaseController::class, 'pdf'])->name('pdf');
            Route::get('/excel/{id?}', [RequestPurchaseController::class, 'excel'])->name('excel');
            Route::get('/print/{id?}', [RequestPurchaseController::class, 'print'])->name('print');
            Route::post('/getdata', [RequestPurchaseController::class, 'getData'])->name('getdata');
            Route::post('/process', [RequestPurchaseController::class, 'process'])->name('process');
            Route::post('/perusahaan', [RequestPurchaseController::class, 'perusahaan'])->name('perusahaan');
            Route::post('/cancel', [RequestPurchaseController::class, 'cancel'])->name('cancel');

        });

        // PURCHASE ORDER BATAL
        Route::group(['prefix' => 'purchasebatal', 'as' => 'purchasebatal.'], function () {
            Route::get('/', [PurchaseBatalController::class, 'index'])->name('index');
            Route::post('/getdata', [PurchaseBatalController::class, 'getData'])->name('getdata');
            Route::post('/delete', [PurchaseBatalController::class, 'delete'])->name('delete');
        });

        // BACK ORDER
        Route::group(['prefix' => 'backorder', 'as' => 'backorder.'], function () {
            Route::get('/', [BackorderController::class, 'index'])->name('index');
            Route::get('/detail/{id}', [BackorderController::class, 'detail'])->name('detail');
            Route::post('/getdata', [BackorderController::class, 'getData'])->name('getdata');
            Route::post('/process', [BackorderController::class, 'process'])->name('process');
            Route::post('/perusahaan', [BackorderController::class, 'perusahaan'])->name('perusahaan');
        });

        // ORDER
        Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/tambah', [OrderController::class, 'tambah'])->name('tambah');
            Route::get('/detail/{id}', [OrderController::class, 'detail'])->name('detail');
            Route::get('/ubah/{id}', [OrderController::class, 'ubah'])->name('ubah');
            Route::post('/simpan', [OrderController::class, 'simpan'])->name('simpan');
            Route::post('/getData', [OrderController::class, 'getdata'])->name('getdata');
            Route::post('/produk_image', [OrderController::class, 'ProdukImage'])->name('image');
            Route::delete('/hapus/{id?}', [OrderController::class, 'delete'])->name('delete');
            Route::post('/getDataGudang', [OrderController::class, 'getDataGudang'])->name('getDataGudang');
            Route::get('/search_produk', [OrderController::class, 'search_produk'])->name('search');
            Route::post('approve', [OrderController::class, 'approve'])->name('approve');
            Route::post('/simpandanselesai', [OrderController::class, 'saveDone'])->name('saveDone');
            Route::get('print/{id}', [OrderController::class, 'print'])->name('print');
            Route::delete('detail/delete/{id?}', [OrderController::class, 'deleteDetailProduk'])->name('detail.beli.delete');
            Route::post('search_produck_barcode', [OrderController::class, 'searchProdukBarcode'])->name('search.produk.barcode');
        });

        // INVOICE
        Route::group(['prefix' => 'invoice', 'as' => 'invoice.'], function () {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/filter', [InvoiceController::class, 'filter_data'])->name('filter');
            Route::get('/menu_invoice/{menu?}/{id?}', [InvoiceController::class, 'menu_invoice'])->name('menu_invoice');
            Route::get('/menu_packing_list/{menu?}/{id?}', [InvoiceController::class, 'menu_packing_list'])->name('menu_packing_list');
            Route::get('/menu_surat_jalan/{menu?}/{id?}', [InvoiceController::class, 'menu_surat_jalan'])->name('menu_surat_jalan');
            Route::get('/menu_amplop/{menu?}/{id?}', [InvoiceController::class, 'menu_amplop'])->name('menu_amplop');
            Route::post('/detail', [InvoiceController::class, 'detail'])->name('detail');
            Route::post('/pengiriman', [InvoiceController::class, 'pengiriman_detail'])->name('pengiriman_detail');
            Route::post('/simpan_pengiriman', [InvoiceController::class, 'simpan_pengiriman'])->name('simpan_pengiriman');
            Route::post('/simpan_memo_colly', [InvoiceController::class, 'simpan_memo_colly'])->name('simpan_memo_colly');
            Route::post('/list_menu', [InvoiceController::class, 'menu_invoice_list'])->name('menu_invoice_list');
        });

        // TANDA TERIMA
        Route::get('/prosestandaterima', [TandaTerimaController::class, 'proses'])->name('tandaterima.proses');
        Route::group(['prefix' => 'tandaterima', 'as' => 'tandaterima.'], function () {
            Route::get('/', [TandaTerimaController::class, 'index'])->name('index');
            Route::get('/filter', [TandaTerimaController::class, 'filter_data'])->name('filter');
            Route::get('/data_tanda_terima/{menu?}/{id?}', [TandaTerimaController::class, 'data_tanda_terima'])->name('tanda_terima');
            Route::get('/data_pengiriman/{menu?}/{id?}', [TandaTerimaController::class, 'data_pengiriman'])->name('pengiriman');
            Route::post('/input_pengiriman', [TandaTerimaController::class, 'input_pengiriman'])->name('input_pengiriman');
            Route::post('/simpan_pengiriman', [TandaTerimaController::class, 'simpan_pengiriman'])->name('simpan_pengiriman');
            Route::post('/list_menu', [TandaTerimaController::class, 'menu_data_list'])->name('menu_data_list');
            Route::post('/getdata', [TandaTerimaController::class, 'getData'])->name('getdata');
            Route::post('/proses_tanda_terima', [TandaTerimaController::class, 'process_tanda_terima'])->name('proses_tanda_terima');
        });

        Route::group(['prefix' => 'transaksi', 'as' => 'transaksi.'], function(){
            Route::group(['prefix' => 'pembayaran', 'as' => 'pembayaran.'], function () {
                Route::get('/', function () {
                    return view('backend/pembayaran/pembayaran/index');
                })->name('index');
            });
            Route::group(['prefix' => 'keuangan', 'as' => 'keuangan.'], function () {
                Route::get('/', [ReportTransaksiController::class, 'index'])->name('index');
                Route::post('/getdata', [ReportTransaksiController::class, 'getData'])->name('getdata');
            });

            Route::group(['prefix' => 'finance', 'as' => 'finance.'], function(){
                Route::get('/',[FinanceController::class, 'index'])->name('index');
                Route::post('/getdata',[FinanceController::class, 'getData'])->name('getdata');
                Route::post('/simpan',[FinanceController::class, 'simpan'])->name('simpan');
                Route::get('/tambah', [FinanceController::class, 'tambah'])->name('tambah');
                Route::get('/edit/{id}', [FinanceController::class, 'ubah'])->name('edit');
                Route::delete('/delete/{id?}',[FinanceController::class,'hapus'])->name('delete');
            });
        });

        Route::group(['prefix' => 'kunjungan', 'as' => 'kunjungan.'], function(){
            Route::get('/',[KunjunganSalesController::class, 'index'])->name('index');
            Route::post('/list_data',[KunjunganSalesController::class, 'list_data'])->name('list_data');
            Route::post('/getdata',[KunjunganSalesController::class, 'getData'])->name('getdata');
            Route::post('/simpan',[KunjunganSalesController::class, 'simpan'])->name('simpan');
            Route::get('/tambah', [KunjunganSalesController::class, 'tambah'])->name('tambah');
            Route::get('/edit/{id}', [KunjunganSalesController::class, 'ubah'])->name('edit');
            Route::delete('/delete/{id?}',[KunjunganSalesController::class,'hapus'])->name('delete');
        });
    });

      // Toko
    Route::group(['prefix' => 'toko', 'as' => 'toko.'], function(){
       Route::get('/',[TokoController::class, 'index'])->name('index');
       Route::get('/gettoko',[TokoController::class, 'getToko'])->name('gettoko');
       Route::post('/getdata',[TokoController::class, 'getData'])->name('getdata');
       Route::post('/simpan',[TokoController::class, 'simpan'])->name('simpan');
       Route::get('/tambah', [TokoController::class, 'tambah'])->name('tambah');
       Route::get('/edit/{id}', [TokoController::class, 'ubah'])->name('edit');
       Route::delete('/delete/{id?}',[TokoController::class,'hapus'])->name('delete');
    });

    Route::group(['prefix' => 'pembelian', 'as' => 'pembelian.'], function(){

        Route::get('/',[PembelianController::class, 'index'])->name('index');
        Route::post('/getdata',[PembelianController::class, 'getData'])->name('getdata');
        Route::get('/tambah',[PembelianController::class, 'tambah'])->name('tambah');
        Route::get('/get_satuan', [PembelianController::class, 'get_satuan'])->name('get_satuan');
        Route::get('/ubah/{id}', [PembelianController::class, 'ubah'])->name('ubah');
        // Route::post('/simpan', [PembelianController::class, 'simpan'])->name('simpan');
        Route::post('/simpan', [PembelianController::class, 'coba_simpan'])->name('simpan');
        Route::post('/tambah_product',[PembelianController::class, 'tambah_product'])->name('tambah_detail');
        Route::get('/search_product', [PembelianController::class, 'search_product'])->name('search_product');
        Route::get('/search_satuan', [PembelianController::class, 'search_satuan'])->name('search_satuan');
        Route::delete('/delete/{id?}', [PembelianController::class, 'hapus'])->name('hapus');
        Route::post('/harga_product', [PembelianController::class, 'harga_product'])->name('harga_product');
    });

     //IMPORT PEMBELIAN
     Route::group(['prefix' => 'pembelian_import', 'as' => 'pembelian_import.'], function(){
        Route::get('/import', [ImportPembelianController::class, 'index'])->name('import');
        Route::post('/importsimpan', [ImportPembelianController::class, 'importsimpan'])->name('importsimpan');
        Route::get('/importbatal', [ImportPembelianController::class, 'importbatal'])->name('importbatal');
        Route::post('/uploadimport', [ImportPembelianController::class, 'import'])->name('uploadimport');
        Route::post('/deleteimport', [ImportPembelianController::class, 'hapus'])->name('deleteimport');
     });

     Route::group(['prefix' => 'retur_pembelian', 'as' => 'retur_pembelian.'],function(){
        Route::get('/', [ReturPembelianController::class, 'index'])->name('index');
        Route::post('/getdata', [ReturPembelianController::class, 'getData'])->name('getdata');
        Route::get('/form-retur/{id?}', [ReturPembelianController::class, 'tambah'])->name('form-retur');
        // Route::get('/detail_retur/{id}', [ReturPembelianController::class, 'detail_retur'])->name('detail_retur');
        Route::post('/simpan', [ReturPembelianController::class, 'simpan'])->name('simpan');
     });
});
