<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('permissions')->insert([
            //MENU PRODUK
            ['nested'=>'1', 'name'=>'Menu Produk', 'slug'=>'menuproduk.index'],
            
            //PRODUK
            ['nested'=>'1.1', 'name'=>'Produk', 'slug'=>'produk.index'],
            ['nested'=>'1.1.1', 'name'=>'Tambah Produk', 'slug'=>'produk.tambah'],
            ['nested'=>'1.1.2', 'name'=>'Ubah Produk', 'slug'=>'produk.ubah'],
            ['nested'=>'1.1.3', 'name'=>'Detail Produk', 'slug'=>'produk.detail'],
            ['nested'=>'1.1.4', 'name'=>'Hapus Produk', 'slug'=>'produk.delete'],

             //KATEGORI
             ['nested'=>'1.2', 'name'=>'Kategori', 'slug'=>'kategori.index'],
             ['nested'=>'1.2.1', 'name'=>'Tambah Kategori', 'slug'=>'kategori.tambah'],
             ['nested'=>'1.2.2', 'name'=>'Ubah Kategori', 'slug'=>'kategori.ubah'],
             ['nested'=>'1.2.4', 'name'=>'Hapus Kategori', 'slug'=>'kategori.delete'],

             //SATUAN
             ['nested'=>'1.3', 'name'=>'Satuan', 'slug'=>'satuan.index'],
             ['nested'=>'1.3.1', 'name'=>'Tambah Satuan', 'slug'=>'satuan.tambah'],
             ['nested'=>'1.3.2', 'name'=>'Ubah Satuan', 'slug'=>'satuan.ubah'],
             ['nested'=>'1.3.4', 'name'=>'Hapus Satuan', 'slug'=>'satuan.delete'],

            //JENIS HARGA
            ['nested'=>'1.4', 'name'=>'Jenis Harga', 'slug'=>'jenisharga.index'],
            ['nested'=>'1.4.1', 'name'=>'Tambah Jenis Harga', 'slug'=>'jenisharga.tambah'],
            ['nested'=>'1.4.2', 'name'=>'Ubah Jenis Harga', 'slug'=>'jenisharga.ubah'],
            ['nested'=>'1.4.4', 'name'=>'Hapus Jenis Harga', 'slug'=>'jenisharga.delete'],

            //BRAND
            ['nested'=>'1.5', 'name'=>'Brand', 'slug'=>'brand.index'],
            ['nested'=>'1.5.1', 'name'=>'Tambah Brand', 'slug'=>'brand.tambah'],
            ['nested'=>'1.5.2', 'name'=>'Ubah Brand', 'slug'=>'brand.ubah'],
            ['nested'=>'1.5.4', 'name'=>'Hapus Brand', 'slug'=>'brand.hapus'],

            //ENGINE
            ['nested'=>'1.6', 'name'=>'Engine', 'slug'=>'engine.index'],
            ['nested'=>'1.6.1', 'name'=>'Tambah Engine', 'slug'=>'engine.tambah'],
            ['nested'=>'1.6.2', 'name'=>'Ubah Engine', 'slug'=>'engine.ubah'],
            ['nested'=>'1.6.4', 'name'=>'Hapus Engine', 'slug'=>'engine.hapus'],
 


            //MASTER
            ['nested'=>'2', 'name'=>'Master', 'slug'=>'master.index'],

            //NEGARA
            ['nested'=>'2.1', 'name'=>'Negara', 'slug'=>'negara.index'],
            ['nested'=>'2.1.1', 'name'=>'Tambah Negara', 'slug'=>'negara.tambah'],
            ['nested'=>'2.1.2', 'name'=>'Ubah Negara', 'slug'=>'negara.ubah'],
            ['nested'=>'2.1.4', 'name'=>'Hapus Negara', 'slug'=>'negara.delete'],

            //PROVINSI
            ['nested'=>'2.2', 'name'=>'Provinsi', 'slug'=>'provinsi.index'],
            ['nested'=>'2.2.1', 'name'=>'Tambah Provinsi', 'slug'=>'provinsi.tambah'],
            ['nested'=>'2.2.2', 'name'=>'Ubah Provinsi', 'slug'=>'provinsi.ubah'],
            ['nested'=>'2.2.4', 'name'=>'Hapus Provinsi', 'slug'=>'provinsi.hapus'],
            
            //KOTA
            ['nested'=>'2.3', 'name'=>'Kota', 'slug'=>'kota.index'],
            ['nested'=>'2.3.1', 'name'=>'Tambah Kota', 'slug'=>'kota.tambah'],
            ['nested'=>'2.3.2', 'name'=>'Ubah Kota', 'slug'=>'kota.ubah'],
            ['nested'=>'2.3.4', 'name'=>'Hapus Kota', 'slug'=>'kota.hapus'],

            //PERUSAHAAN
            ['nested'=>'2.4', 'name'=>'Perusahaan', 'slug'=>'perusahaan.index'],
            ['nested'=>'2.4.1', 'name'=>'Tambah Perusahaan', 'slug'=>'perusahaan.tambah'],
            ['nested'=>'2.4.2', 'name'=>'Ubah Perusahaan', 'slug'=>'perusahaan.ubah'],
            ['nested'=>'2.4.3', 'name'=>'Detail Perusahaan', 'slug'=>'perusahaan.detail'],
            ['nested'=>'2.4.4', 'name'=>'Hapus Perusahaan', 'slug'=>'perusahaan.delete'],
            ['nested'=>'2.4.5', 'name'=>'Perusahaan Gudang', 'slug'=>'perusahaan.gudang'],
            ['nested'=>'2.4.6', 'name'=>'Kelola Perusahaan Gudang', 'slug'=>'perusahaan.simpangudang'],

            //STAFF
            ['nested'=>'2.5', 'name'=>'Staff', 'slug'=>'staff.index'],
            ['nested'=>'2.5.1', 'name'=>'Tambah Staff', 'slug'=>'staff.tambah'],
            ['nested'=>'2.5.2', 'name'=>'Ubah Staff', 'slug'=>'staff.ubah'],
            ['nested'=>'2.5.3', 'name'=>'Detail Staff', 'slug'=>'staff.detail'],
            ['nested'=>'2.5.4', 'name'=>'Hapus Staff', 'slug'=>'staff.hapus'],

            //SALES
            ['nested'=>'2.6', 'name'=>'Sales', 'slug'=>'sales.index'],
            ['nested'=>'2.6.1', 'name'=>'Tambah Sales', 'slug'=>'sales.tambah'],
            ['nested'=>'2.6.2', 'name'=>'Ubah Sales', 'slug'=>'sales.ubah'],
            ['nested'=>'2.6.3', 'name'=>'Detail Sales', 'slug'=>'sales.detail'],
            ['nested'=>'2.6.4', 'name'=>'Hapus Sales', 'slug'=>'sales.hapus'],
            

            //MEMBER
            ['nested'=>'2.7', 'name'=>'Member', 'slug'=>'member.index'],
            ['nested'=>'2.7.1', 'name'=>'Tambah Member', 'slug'=>'member.tambah'],
            ['nested'=>'2.7.2', 'name'=>'Ubah Member', 'slug'=>'member.ubah'],
            ['nested'=>'2.7.3', 'name'=>'Detail Member', 'slug'=>'member.detail'],
            ['nested'=>'2.7.4', 'name'=>'Hapus Member', 'slug'=>'member.hapus'],
            ['nested'=>'2.7.5', 'name'=>'Data Member Sales', 'slug'=>'member.member_sales'],
            ['nested'=>'2.7.6', 'name'=>'Kelola Member Sales', 'slug'=>'member.simpan_member_sales'],

             //EXPEDISI
            ['nested'=>'2.8', 'name'=>'Expedisi', 'slug'=>'expedisi.index'],
            ['nested'=>'2.8.1', 'name'=>'Tambah Expedisi', 'slug'=>'expedisi.tambah'],
            ['nested'=>'2.8.2', 'name'=>'Ubah Expedisi', 'slug'=>'expedisi.ubah'],
            ['nested'=>'2.8.3', 'name'=>'Detail Expedisi', 'slug'=>'expedisi.detail'],
            ['nested'=>'2.8.4', 'name'=>'Hapus Expedisi', 'slug'=>'expedisi.hapus'],
 
             //EXPEDISI VIA
            ['nested'=>'2.9', 'name'=>'Expedisi Via', 'slug'=>'expedisivia.index'],
            ['nested'=>'2.9.1', 'name'=>'Tambah Expedisi Via', 'slug'=>'expedisivia.tambah'],
            ['nested'=>'2.9.2', 'name'=>'Ubah Expedisi Via', 'slug'=>'expedisivia.ubah'],
            ['nested'=>'2.9.3', 'name'=>'Detail Expedisi Via', 'slug'=>'expedisivia.detail'],
            ['nested'=>'2.9.4', 'name'=>'Hapus Expedisi Via', 'slug'=>'expedisivia.hapus'],

             //GUDANG
            ['nested'=>'2.a', 'name'=>'Gudang', 'slug'=>'gudang.index'],
            ['nested'=>'2.a.1', 'name'=>'Tambah Gudang', 'slug'=>'gudang.tambah'],
            ['nested'=>'2.a.2', 'name'=>'Ubah Gudang', 'slug'=>'gudang.ubah'],
            ['nested'=>'2.a.4', 'name'=>'Hapus Gudang', 'slug'=>'gudang.delete'],

            //MASTER STOK
            ['nested'=>'3', 'name'=>'Stok', 'slug'=>'menustok.index'],
            //INFORMASI STOK ADMIN :1
            ['nested'=>'3.1', 'name'=>'Informasi Stok Admin', 'slug'=>'stokadmin.index'],
            //INFORMASI STOK SALES :2
            ['nested'=>'3.2', 'name'=>'Informasi Stok Sales', 'slug'=>'stoksales.index'],
            //ADJUSTMENT STOK :3
            ['nested'=>'3.3', 'name'=>'Adjustment Stock', 'slug'=>'adjstok.index'],
            ['nested'=>'3.3.1', 'name'=>'Tambah Adj Stock', 'slug'=>'adjstok.tambah'],
            //HISTORY ADJ STOK :4
            ['nested'=>'3.4', 'name'=>'History Adjustment Stock', 'slug'=>'historyadjstok.index'],
            ['nested'=>'3.4.1', 'name'=>'Print History Adj Stock', 'slug'=>'historyadjstok.print'],
            ['nested'=>'3.4.2', 'name'=>'Export Excel History Adj Stock', 'slug'=>'historyadjstok.excel'],
            ['nested'=>'3.4.3', 'name'=>'Export PDF History Adj Stock', 'slug'=>'historyadjstok.pdf'],
            //MUTASI STOK : 5
            ['nested'=>'3.5', 'name'=>'Mutasi Stok', 'slug'=>'stokmutasi.tambah'],
            //HISTORY MUTASI STOK : 6
            ['nested'=>'3.6', 'name'=>'History Mutasi Stok', 'slug'=>'historymutasistok.index'],
            ['nested'=>'3.6.1', 'name'=>'Print History Mutasi Stok', 'slug'=>'historymutasistok.print'],
            ['nested'=>'3.6.2', 'name'=>'Export Excel History Mutasi Stok', 'slug'=>'historymutasistok.excel'],
            ['nested'=>'3.6.3', 'name'=>'Export PDF History Mutasi Stok', 'slug'=>'historymutasistok.pdf'],
            //OPNAME STOK : 7
            ['nested'=>'3.7', 'name'=>'Stok Opname', 'slug'=>'stokopname.index'],
            ['nested'=>'3.7.1', 'name'=>'Tambah SO', 'slug'=>'stokopname.tambah'],
            ['nested'=>'3.7.2', 'name'=>'Ubah SO', 'slug'=>'stokopname.ubah'],
            ['nested'=>'3.7.3', 'name'=>'Approve SO', 'slug'=>'stokopname.approve'],

            //============MENU FEE SALES ===============//
            //FEE SALES
            ['nested'=>'4', 'name'=>'Menu Fee Sales', 'slug'=>'menufeesales.index'],
            ['nested'=>'4.1', 'name'=>'Fee Sales', 'slug'=>'feesales.index'],
            ['nested'=>'4.1.1', 'name'=>'Fee Sales Detail', 'slug'=>'feesales.detail'],
            
            //============MENU TANDA TERIMA 5 ===============//

            //============MASTER INVOICE 6===============//
            ['nested'=>'6', 'name'=>'Menu Invoice', 'slug'=>'menuinvoice.index'],
            ['nested'=>'6.1', 'name'=>'Invoice', 'slug'=>'invoice.index'],
            ['nested'=>'6.1.1', 'name'=>'Print Invoice', 'slug'=>'invoice.menu_invoice'],
            ['nested'=>'6.1.2', 'name'=>'Surat Jalan Invoice', 'slug'=>'invoice.menu_surat_jalan'],
            ['nested'=>'6.1.3', 'name'=>'Print Amplop', 'slug'=>'invoice.menu_amplop'],
            ['nested'=>'6.1.4', 'name'=>'Print Packing List', 'slug'=>'invoice.menu_packing_list'],
            ['nested'=>'6.1.5', 'name'=>'Input Pengiriman', 'slug'=>'invoice.simpan_pengiriman'],

            //============MENU PROSES RETUR REVISI 7 ===============//
            
            //============MASTER PO ===============//
            ['nested'=>'8', 'name'=>'Menu Purchase Order', 'slug'=>'menupurchaserder.index'],
            //MENU PO : 1
            ['nested'=>'8.1', 'name'=>'Purchase Order', 'slug'=>'purchaseorder.index'],
            ['nested'=>'8.1.1', 'name'=>'Purchase Order Tambah', 'slug'=>'purchaseorder.tambah'],
            ['nested'=>'8.1.2', 'name'=>'Purchase Order Update PO', 'slug'=>'purchaseorder.updatepo'],
            ['nested'=>'8.1.3', 'name'=>'Purchase Order Detail', 'slug'=>'purchaseorder.detail'],
            ['nested'=>'8.1.4', 'name'=>'Purchase Order Update Note', 'slug'=>'purchaseorder.note'],
            ['nested'=>'8.1.5', 'name'=>'Purchase Order Print', 'slug'=>'purchaseorder.print'],
            ['nested'=>'8.1.6', 'name'=>'Purchase Order Update Expedisi', 'slug'=>'purchaseorder.expedisi'],
            ['nested'=>'8.1.7', 'name'=>'Hapus Purchase Order', 'slug'=>'purchaseorder.delete'],
            ['nested'=>'8.1.8', 'name'=>'Status Purchase Order PO', 'slug'=>'purchaseorder.liststatuspo'],
            ['nested'=>'8.1.9', 'name'=>'Status Purchase Order Gudang', 'slug'=>'purchaseorder.liststatusgudang'],
            ['nested'=>'8.1.a', 'name'=>'Status Purchase Order Invoice', 'slug'=>'purchaseorder.liststatusinvoice'],

            //MENU REQUEST PO : 2
            ['nested'=>'9', 'name'=>'Menu RPO', 'slug'=>'menurequestpurchaseorder.index'],

            ['nested'=>'9.1', 'name'=>'Request Order', 'slug'=>'requestpurchaseorder.index'],
            ['nested'=>'9.1.1', 'name'=>'Detail Request Order', 'slug'=>'requestpurchaseorder.detail'],
            ['nested'=>'9.1.2', 'name'=>'Print Request Order', 'slug'=>'requestpurchaseorder.print'],
            ['nested'=>'9.1.3', 'name'=>'Export PDF Request Order', 'slug'=>'requestpurchaseorder.pdf'],
            ['nested'=>'9.1.4', 'name'=>'Export Excel Request Order', 'slug'=>'requestpurchaseorder.excel'],
            ['nested'=>'9.1.5', 'name'=>'Cancel Request Order', 'slug'=>'requestpurchaseorder.cancel'],
            
            //MENU ORDER BATAL : 3
            ['nested'=>'a', 'name'=>'Menu PO Batal', 'slug'=>'menupurchasebatal.index'],
            ['nested'=>'a.1', 'name'=>'PO Batal', 'slug'=>'purchasebatal.index'],
            ['nested'=>'a.1.1', 'name'=>'Delete PO Batal', 'slug'=>'purchasebatal.delete'],
            
            //MENU BACKORDER : 4
            ['nested'=>'b', 'name'=>'Menu Back Order', 'slug'=>'menubackorder.index'],
            ['nested'=>'b.1', 'name'=>'Backorder', 'slug'=>'backorder.index'],
            ['nested'=>'b.1.1', 'name'=>'Backoder Detail', 'slug'=>'backorder.detail'],

            //MENU ORDER
            ['nested'=>'c', 'name'=>'Menu Order', 'slug'=>'menuorder.index'],
            ['nested'=>'c.1', 'name'=>'Order', 'slug'=>'order.index'],
            ['nested'=>'c.1.1', 'name'=>'Tambah Order', 'slug'=>'order.tambah'],
            ['nested'=>'c.1.2', 'name'=>'Ubah Order', 'slug'=>'order.ubah'],
            ['nested'=>'c.1.3', 'name'=>'Detail Order', 'slug'=>'order.detail'],
            ['nested'=>'c.1.4', 'name'=>'Hapus Order', 'slug'=>'order.delete'],
         
            //MENU REPORT
            ['nested'=>'d', 'name'=>'Report', 'slug'=>'menureport.index'],

            ['nested'=>'d.1', 'name'=>'Report SO', 'slug'=>'reportso.index'],
            ['nested'=>'d.1.1', 'name'=>'Print Report SO', 'slug'=>'reportso.print'],
            ['nested'=>'d.1.2', 'name'=>'Excel Report SO', 'slug'=>'reportso.excel'],
            ['nested'=>'d.1.3', 'name'=>'Pdf Report SO', 'slug'=>'reportso.pdf'],

            ['nested'=>'d.2', 'name'=>'Report PO', 'slug'=>'reportpo.index'],
            ['nested'=>'d.2.1', 'name'=>'Print Report PO', 'slug'=>'reportpo.print'],
            ['nested'=>'d.2.2', 'name'=>'Excel Report PO', 'slug'=>'reportpo.excel'],
            ['nested'=>'d.2.3', 'name'=>'Pdf Report PO', 'slug'=>'reportpo.pdf'],

            ['nested'=>'d.3', 'name'=>'Report Retur Revisi', 'slug'=>'reportreturrevisi.index'],
            ['nested'=>'d.3.1', 'name'=>'Print Report Retur Revisi', 'slug'=>'reportreturrevisi.print'],
            ['nested'=>'d.3.2', 'name'=>'Excel Report Retur Revisi', 'slug'=>'reportreturrevisi.excel'],
            ['nested'=>'d.3.3', 'name'=>'Pdf Report Retur Revisi', 'slug'=>'reportreturrevisi.pdf'],

            ['nested'=>'d.4', 'name'=>'Report Rekap Invoice', 'slug'=>'reportinvoice.index'],
            ['nested'=>'d.4.1', 'name'=>'Print Report Rekap Invoice', 'slug'=>'reportinvoice.print'],
            ['nested'=>'d.4.2', 'name'=>'Excel Report Rekap Invoice', 'slug'=>'reportinvoice.excel'],
            ['nested'=>'d.4.3', 'name'=>'Pdf Report Rekap Invoice', 'slug'=>'reportinvoice.pdf'],

            ['nested'=>'d.5', 'name'=>'Report Tanda Terima', 'slug'=>'reporttandaterima.index'],
            ['nested'=>'d.5.1', 'name'=>'Print Report Tanda Terima', 'slug'=>'reporttandaterima.print'],
            ['nested'=>'d.5.2', 'name'=>'Excel Report Tanda Terima', 'slug'=>'reporttandaterima.excel'],
            ['nested'=>'d.5.3', 'name'=>'Pdf Report Tanda Terima', 'slug'=>'reporttandaterima.pdf'],

            ['nested'=>'d.6', 'name'=>'Barang Masuk', 'slug'=>'reportbarangmasuk.index'],
            ['nested'=>'d.6.1', 'name'=>'Print Barang Masuk', 'slug'=>'reportbarangmasuk.print'],
            ['nested'=>'d.6.2', 'name'=>'Excel Barang Masuk', 'slug'=>'reportbarangmasuk.excel'],
            ['nested'=>'d.6.3', 'name'=>'Pdf Barang Masuk', 'slug'=>'reportbarangmasuk.pdf'],

            ['nested'=>'d.7', 'name'=>'Barang Keluar', 'slug'=>'reportbarangkeluar.index'],
            ['nested'=>'d.7.1', 'name'=>'Print Barang Keluar', 'slug'=>'reportbarangkeluar.print'],
            ['nested'=>'d.7.2', 'name'=>'Excel Barang Keluar', 'slug'=>'reportbarangkeluar.excel'],
            ['nested'=>'d.7.3', 'name'=>'Pdf Barang Keluar', 'slug'=>'reportbarangkeluar.pdf'],

            //tambah sisa hutang ya
            ['nested'=>'d.8', 'name'=>'Sisa Hutang', 'slug'=>'reportsisahutang.index'],
            ['nested'=>'d.8.1', 'name'=>'Print Sisa Hutang', 'slug'=>'reportsisahutang.print'],
            ['nested'=>'d.8.2', 'name'=>'Excel Sisa Hutang', 'slug'=>'reportsisahutang.excel'],
            ['nested'=>'d.8.3', 'name'=>'Pdf Sisa Hutang', 'slug'=>'reportsisahutang.pdf'],

            ['nested'=>'d.9', 'name'=>'Back Order', 'slug'=>'reportbo.index'],
            ['nested'=>'d.9.1', 'name'=>'Print Back Order', 'slug'=>'reportbo.print'],
            ['nested'=>'d.9.2', 'name'=>'Excel Back Order', 'slug'=>'reportbo.excel'],
            ['nested'=>'d.9.3', 'name'=>'Pdf Back Order', 'slug'=>'reportbo.pdf'],

            ['nested'=>'d.a', 'name'=>'Qty Back Order', 'slug'=>'reportboqty.index'],
            ['nested'=>'d.a.1', 'name'=>'Print Qty Back Order', 'slug'=>'reportboqty.print'],
            ['nested'=>'d.a.2', 'name'=>'Excel Qty Back Order', 'slug'=>'reportboqty.excel'],
            ['nested'=>'d.a.3', 'name'=>'Pdf Qty Back Order', 'slug'=>'reportboqty.pdf'],

            
            ['nested'=>'d.b', 'name'=>'Report Penjualan', 'slug'=>'reportpenjualan.index'],
            ['nested'=>'d.b.1', 'name'=>'Print Penjualan', 'slug'=>'reportpenjualan.print'],
            ['nested'=>'d.b.2', 'name'=>'Excel Penjualan', 'slug'=>'reportpenjualan.excel'],
            ['nested'=>'d.b.3', 'name'=>'Pdf Penjualan', 'slug'=>'reportpenjualan.pdf'],

            ['nested'=>'d.c', 'name'=>'Report Stok', 'slug'=>'reportstok.index'],

           


            ['nested'=>'e', 'name'=>'Keamanan', 'slug'=>'security.index'],
            ['nested'=>'e.1', 'name'=>'Modul', 'slug'=>'permission.index'],
            ['nested'=>'e.1.1', 'name'=>'Tambah Modul', 'slug'=>'permission.tambah'],
            ['nested'=>'e.1.2', 'name'=>'Ubah Modul', 'slug'=>'permission.ubah'],
            ['nested'=>'e.2', 'name'=>'Akses', 'slug'=>'role.index'],
            ['nested'=>'e.2.1', 'name'=>'Tambah Akses', 'slug'=>'role.tambah'],
            ['nested'=>'e.2.2', 'name'=>'Ubah Akses', 'slug'=>'role.ubah'],
            ['nested'=>'e.2.3', 'name'=>'Daftar User Akses', 'slug'=>'role.user'],
            ['nested'=>'e.2.4', 'name'=>'Hapus Akses', 'slug'=>'role.hapus'],

        ]);
    }
}
