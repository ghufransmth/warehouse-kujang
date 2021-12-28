<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\Permission;
use App\Models\Role;
class PermissionroleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         // Catch Permission first
         $permission = Permission::pluck('id','slug');

         // Add a permission to a role
         $role_manager = Role::where('name', 'Super Admin')->first();
         $role_manager->permission()->attach([

            //=========MENUPRODUK=============//
            $permission['menuproduk.index'],

            //DATA PRODUK
            $permission['produk.index'],
            $permission['produk.detail'],
            $permission['produk.tambah'],
            $permission['produk.ubah'],
            $permission['produk.delete'],

            //KATEGORI
            $permission['kategori.index'],
            $permission['kategori.tambah'],
            $permission['kategori.ubah'],
            $permission['kategori.delete'],

            //SATUAN
            $permission['satuan.index'],
            $permission['satuan.tambah'],
            $permission['satuan.ubah'],
            $permission['satuan.delete'],

             //JENIS HARGA
             $permission['jenisharga.index'],
             $permission['jenisharga.tambah'],
             $permission['jenisharga.ubah'],
             $permission['jenisharga.delete'],
             
             //BRAND
             $permission['brand.index'],
             $permission['brand.tambah'],
             $permission['brand.ubah'],
             $permission['brand.hapus'],

             //ENGINE
             $permission['engine.index'],
             $permission['engine.tambah'],
             $permission['engine.ubah'],
             $permission['engine.hapus'],

            //============MENU MASTER ===============//
            //MASTER
            $permission['master.index'],

            //NEGARA
            $permission['negara.index'],
            $permission['negara.tambah'],
            $permission['negara.ubah'],
            $permission['negara.delete'],

            //PROVINSI
            $permission['provinsi.index'],
            $permission['provinsi.tambah'],
            $permission['provinsi.ubah'],
            $permission['provinsi.hapus'],

            //KOTA
            $permission['kota.index'],
            $permission['kota.tambah'],
            $permission['kota.ubah'],
            $permission['kota.hapus'],

            //PERUSAHAAN
            $permission['perusahaan.index'],
            $permission['perusahaan.tambah'],
            $permission['perusahaan.ubah'],
            $permission['perusahaan.detail'],
            $permission['perusahaan.delete'],
            $permission['perusahaan.gudang'],
            $permission['perusahaan.simpangudang'],

            //STAFF
            $permission['staff.index'],
            $permission['staff.tambah'],
            $permission['staff.ubah'],
            $permission['staff.detail'],
            $permission['staff.hapus'],

            //SALES
            $permission['sales.index'],
            $permission['sales.tambah'],
            $permission['sales.ubah'],
            $permission['sales.detail'],
            $permission['sales.hapus'],

             //MEMBER
             $permission['member.index'],
             $permission['member.tambah'],
             $permission['member.ubah'],
             $permission['member.detail'],
             $permission['member.hapus'],

            //EXPEDISI
            $permission['expedisi.index'],
            $permission['expedisi.tambah'],
            $permission['expedisi.ubah'],
            $permission['expedisi.detail'],
            $permission['expedisi.hapus'],

            //EXPEDISI VIA
            $permission['expedisivia.index'],
            $permission['expedisivia.tambah'],
            $permission['expedisivia.ubah'],
            $permission['expedisivia.detail'],
            $permission['expedisivia.hapus'],

             //GUDANG
             $permission['gudang.index'],
             $permission['gudang.tambah'],
             $permission['gudang.ubah'],
             $permission['gudang.delete'],



            //============MENU STOK ===============//
            //STOK
             $permission['menustok.index'],

            //STOK ADMIN
            $permission['stokadmin.index'],
            
             //STOK SALES
             $permission['stoksales.index'],
             
             //ADJ STOK
             $permission['adjstok.index'],
             $permission['adjstok.tambah'],

             //HISTORY ADJ STOK
             $permission['historyadjstok.index'],
             $permission['historyadjstok.print'],
             $permission['historyadjstok.excel'],
             $permission['historyadjstok.pdf'],

             //MUTASI STOK
             $permission['stokmutasi.tambah'],

             //HISTORY MUTASI STOK
             $permission['historymutasistok.index'],
             $permission['historymutasistok.print'],
             $permission['historymutasistok.excel'],
             $permission['historymutasistok.pdf'],
             
            //OPNAME STOK
            $permission['stokopname.index'],
            $permission['stokopname.tambah'],
            $permission['stokopname.ubah'],
            $permission['stokopname.approve'],

            

            //============MENU FEE SALES ===============//
            //FEE SALES
            $permission['menufeesales.index'],
            $permission['feesales.index'],
            $permission['feesales.detail'],

            //============MENU INVOICE ===============//
            $permission['menuinvoice.index'],
            $permission['invoice.index'],
            $permission['invoice.menu_invoice'],
            $permission['invoice.menu_surat_jalan'],
            $permission['invoice.menu_amplop'],
            $permission['invoice.menu_packing_list'],
            $permission['invoice.simpan_pengiriman'],

            //============MENU PO ===============//
            //PURCHASE ORDER
            $permission['menupurchaserder.index'],
            $permission['purchaseorder.index'],
            $permission['purchaseorder.tambah'],
            $permission['purchaseorder.updatepo'],
            $permission['purchaseorder.detail'],
            $permission['purchaseorder.note'],
            $permission['purchaseorder.print'],
            $permission['purchaseorder.expedisi'],
            $permission['purchaseorder.delete'],
            $permission['purchaseorder.liststatuspo'],
            $permission['purchaseorder.liststatusgudang'],
            $permission['purchaseorder.liststatusinvoice'],

            //MENU REQUEST PURCHASE ORDER
            $permission['menurequestpurchaseorder.index'],
            $permission['requestpurchaseorder.index'],
            $permission['requestpurchaseorder.detail'],
            $permission['requestpurchaseorder.print'],
            $permission['requestpurchaseorder.pdf'],
            $permission['requestpurchaseorder.excel'],
            $permission['requestpurchaseorder.cancel'],

            //MENU PURCHASE BATAL
            $permission['menupurchasebatal.index'],
            $permission['purchasebatal.index'],
            $permission['purchasebatal.delete'],

            //MENU BACKORDER
            $permission['menubackorder.index'],
            $permission['backorder.index'],
            $permission['backorder.detail'],

            //============MENU ORDER PRODUK ===============//
            $permission['menuorder.index'],
            $permission['order.index'],
            $permission['order.tambah'],
            $permission['order.ubah'],
            $permission['order.detail'],
            $permission['order.delete'],

            //============MENU REPORT ===============//
            $permission['menureport.index'],

            $permission['reportso.index'],
            $permission['reportso.print'],
            $permission['reportso.excel'],
            $permission['reportso.pdf'],

            $permission['reportpo.index'],
            $permission['reportpo.print'],
            $permission['reportpo.excel'],
            $permission['reportpo.pdf'],

            $permission['reportreturrevisi.index'],
            $permission['reportreturrevisi.print'],
            $permission['reportreturrevisi.excel'],
            $permission['reportreturrevisi.pdf'],


            $permission['reportinvoice.index'],
            $permission['reportinvoice.print'],
            $permission['reportinvoice.excel'],
            $permission['reportinvoice.pdf'],

            $permission['reporttandaterima.index'],
            $permission['reporttandaterima.print'],
            $permission['reporttandaterima.excel'],
            $permission['reporttandaterima.pdf'],


            $permission['reportbarangmasuk.index'],
            $permission['reportbarangmasuk.print'],
            $permission['reportbarangmasuk.excel'],
            $permission['reportbarangmasuk.pdf'],

            $permission['reportbarangkeluar.index'],
            $permission['reportbarangkeluar.print'],
            $permission['reportbarangkeluar.excel'],
            $permission['reportbarangkeluar.pdf'],

        
            //tambah sisa hutang ya
            $permission['reportsisahutang.index'],
            $permission['reportsisahutang.print'],
            $permission['reportsisahutang.excel'],
            $permission['reportsisahutang.pdf'],


            $permission['reportbo.index'],
            $permission['reportbo.print'],
            $permission['reportbo.excel'],
            $permission['reportbo.pdf'],


            $permission['reportboqty.index'],
            $permission['reportboqty.print'],
            $permission['reportboqty.excel'],
            $permission['reportboqty.pdf'],


            $permission['reportpenjualan.index'],
            $permission['reportpenjualan.print'],
            $permission['reportpenjualan.excel'],
            $permission['reportpenjualan.pdf'],

            $permission['reportstok.index'],

            //MENU Keamanan
            $permission['security.index'],
 
            $permission['permission.index'],
            $permission['permission.tambah'],
            $permission['permission.ubah'],
 
            $permission['role.index'],
            $permission['role.tambah'],
            $permission['role.ubah'],
            $permission['role.user'],
            $permission['role.hapus'],

            
         ]);
    }
}
