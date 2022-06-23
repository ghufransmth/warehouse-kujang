-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: May 09, 2022 at 03:23 PM
-- Server version: 5.7.34
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kujang`
--

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nested` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `nested`) VALUES
(1, 'Menu Produk', 'menuproduk.index', '1'),
(2, 'Produk', 'produk.index', '1.1'),
(3, 'Tambah Produk', 'produk.tambah', '1.1.1'),
(4, 'Ubah Produk', 'produk.ubah', '1.1.2'),
(5, 'Detail Produk', 'produk.detail', '1.1.3'),
(6, 'Hapus Produk', 'produk.delete', '1.1.4'),
(7, 'Kategori', 'kategori.index', '1.2'),
(8, 'Tambah Kategori', 'kategori.tambah', '1.2.1'),
(9, 'Ubah Kategori', 'kategori.ubah', '1.2.2'),
(10, 'Hapus Kategori', 'kategori.delete', '1.2.4'),
(11, 'Satuan', 'satuan.index', '1.3'),
(12, 'Tambah Satuan', 'satuan.tambah', '1.3.1'),
(13, 'Ubah Satuan', 'satuan.ubah', '1.3.2'),
(14, 'Hapus Satuan', 'satuan.delete', '1.3.4'),
(15, 'Import Produk', 'produk.import', '1.1.5'),
(27, 'Master', 'master.index', '2'),
(28, 'Type Channel', 'type_channel.index', '2.1'),
(29, 'Tambah Type Channel', 'type_channel.tambah', '2.1.1'),
(30, 'Ubah Type Channel', 'type_channel.ubah', '2.1.2'),
(31, 'Hapus Type Channel', 'type_channel.delete', '2.1.4'),
(32, 'Driver', 'driver.index', '2.2'),
(33, 'Tambah Driver', 'driver.tambah', '2.2.1'),
(34, 'Ubah Driver', 'driver.ubah', '2.2.2'),
(35, 'Hapus Driver', 'driver.delete', '2.2.4'),
(36, 'Distrik', 'distrik.index', '2.3'),
(37, 'Tambah Distrik', 'distrik.tambah', '2.3.1'),
(38, 'Ubah Distrik', 'distrik.ubah', '2.3.2'),
(39, 'Hapus Distrik', 'distrik.delete', '2.3.4'),
(40, 'Jenis Bayar', 'payment.index', '2.4'),
(41, 'Tambah Jenis Bayar', 'payment.tambah', '2.4.1'),
(42, 'Ubah Jenis Bayar', 'payment.ubah', '2.4.2'),
(44, 'Hapus Jenis Bayar', 'payment.delete', '2.4.4'),
(47, 'Staff', 'staff.index', '2.5'),
(48, 'Tambah Staff', 'staff.tambah', '2.5.1'),
(49, 'Ubah Staff', 'staff.ubah', '2.5.2'),
(50, 'Detail Staff', 'staff.detail', '2.5.3'),
(51, 'Hapus Staff', 'staff.hapus', '2.5.4'),
(52, 'Sales', 'sales.index', '2.6'),
(53, 'Tambah Sales', 'sales.tambah', '2.6.1'),
(54, 'Ubah Sales', 'sales.ubah', '2.6.2'),
(55, 'Detail Sales', 'sales.detail', '2.6.3'),
(56, 'Hapus Sales', 'sales.hapus', '2.6.4'),
(57, 'Supplier', 'supplier.index', '2.7'),
(58, 'Tambah Supplier', 'supplier.tambah', '2.7.1'),
(59, 'Ubah Supplier', 'supplier.ubah', '2.7.2'),
(60, 'Detail Supplier', 'supplier.detail', '2.7.3'),
(61, 'Hapus Supplier', 'supplier.hapus', '2.7.4'),
(64, 'Expedisi', 'expedisi.index', '2.8'),
(65, 'Tambah Expedisi', 'expedisi.tambah', '2.8.1'),
(66, 'Ubah Expedisi', 'expedisi.ubah', '2.8.2'),
(67, 'Detail Expedisi', 'expedisi.detail', '2.8.3'),
(68, 'Hapus Expedisi', 'expedisi.hapus', '2.8.4'),
(69, 'Diskon', 'diskon.index', '2.9'),
(70, 'Tambah Diskon', 'diskon.tambah', '2.9.1'),
(71, 'Ubah Diskon', 'diskon.ubah', '2.9.2'),
(73, 'Hapus Diskon', 'diskon.delete', '2.9.4'),
(74, 'Gudang', 'gudang.index', '2.a'),
(75, 'Tambah Gudang', 'gudang.tambah', '2.a.1'),
(76, 'Ubah Gudang', 'gudang.ubah', '2.a.2'),
(77, 'Hapus Gudang', 'gudang.delete', '2.a.4'),
(78, 'Stok', 'menustok.index', '3'),
(81, 'Adjustment Stock', 'adjstok.index', '3.3'),
(82, 'Tambah Adj Stock', 'adjstok.tambah', '3.3.1'),
(83, 'History Adjustment Stock', 'historyadjstok.index', '3.4'),
(84, 'Print History Adj Stock', 'historyadjstok.print', '3.4.1'),
(85, 'Export Excel History Adj Stock', 'historyadjstok.excel', '3.4.2'),
(86, 'Export PDF History Adj Stock', 'historyadjstok.pdf', '3.4.3'),
(87, 'Mutasi Stok', 'stokmutasi.tambah', '3.5'),
(88, 'History Mutasi Stok', 'historymutasistok.index', '3.6'),
(89, 'Print History Mutasi Stok', 'historymutasistok.print', '3.6.1'),
(90, 'Export Excel History Mutasi Stok', 'historymutasistok.excel', '3.6.2'),
(91, 'Export PDF History Mutasi Stok', 'historymutasistok.pdf', '3.6.3'),
(92, 'Stok Opname', 'stokopname.index', '3.7'),
(93, 'Tambah SO', 'stokopname.tambah', '3.7.1'),
(94, 'Ubah SO', 'stokopname.ubah', '3.7.2'),
(95, 'Approve SO', 'stokopname.approve', '3.7.3'),
(99, 'Menu Toko', 'menutoko.index', '6'),
(100, 'Toko', 'toko.index', '6.1'),
(101, 'Tambah Toko', 'toko.tambah', '6.1.1'),
(102, 'Ubah Toko', 'toko.ubah', '6.1.2'),
(103, 'Hapus Toko', 'toko.delete', '6.1.3'),
(106, 'Menu Penjualan', 'menupurchaserder.index', '8'),
(107, 'Penjualan Produk', 'purchaseorder.index', '8.1'),
(108, 'Tambah Penjualan Produk', 'purchaseorder.tambah', '8.1.1'),
(109, 'Import Penjualan', 'purchaseorder.import', '8.1.2'),
(110, 'Purchase Order Detail', 'purchaseorder.detail', '8.1.3'),
(111, 'Purchase Order Update Note', 'purchaseorder.note', '8.1.4'),
(112, 'Purchase Order Print', 'purchaseorder.print', '8.1.5'),
(114, 'Hapus Purchase Order', 'purchaseorder.delete', '8.1.7'),
(118, 'Menu Pengiriman', 'menupengiriman.index', '9'),
(119, 'Delivery Order', 'deliveryorder.index', '9.1'),
(120, 'Tambah Delivery Order', 'deliveryorder.tambah', '9.1.1'),
(121, 'Ganti Driver', 'deliveryorder.changedriver', '9.1.2'),
(122, 'Pengiriman', 'deliveryorder.pengiriman', '9.1.3'),
(131, 'Menu Pembelian', 'menupembelian.index', 'c'),
(132, 'Pembelian', 'pembelian.index', 'c.1'),
(133, 'Tambah Pembelian', 'pembelian.tambah', 'c.1.1'),
(134, 'Ubah Pembelian', 'pembelian.ubah', 'c.1.2'),
(135, 'Detail Pembelian', 'pembelian.detail', 'c.1.3'),
(136, 'Hapus Pembelian', 'pembelian.delete', 'c.1.4'),
(137, 'Report', 'menureport.index', 'd'),
(138, 'Report Keuangan', 'reportkeuangan.index', 'd.1'),
(139, 'Print Report Keuangan', 'reportkeuangan.print', 'd.1.1'),
(140, 'Excel Report Keuangan', 'reportkeuangan.excel', 'd.1.2'),
(141, 'Pdf Report Keuangan', 'reportkeuangan.pdf', 'd.1.3'),
(142, 'Report Laba Rugi', 'reportlabarugi.index', 'd.2'),
(143, 'Print Report Laba Rugi', 'reportlabarugi.print', 'd.2.1'),
(144, 'Excel Report Laba Rugi', 'reportlabarugi.excel', 'd.2.2'),
(145, 'Pdf Report Report Laba Rugi', 'reportlabarugi.pdf', 'd.2.3'),
(146, 'Report Delivery Order', 'reportdeliveryorder.index', 'd.3'),
(147, 'Print Report Delivery Order', 'reportdeliveryorder.print', 'd.3.1'),
(148, 'Excel Report Delivery Order', 'reportdeliveryorder.excel', 'd.3.2'),
(149, 'Pdf Report Delivery Order', 'reportdeliveryorder.pdf', 'd.3.3'),
(158, 'Barang Masuk', 'reportbarangmasuk.index', 'd.6'),
(159, 'Print Barang Masuk', 'reportbarangmasuk.print', 'd.6.1'),
(160, 'Excel Barang Masuk', 'reportbarangmasuk.excel', 'd.6.2'),
(161, 'Pdf Barang Masuk', 'reportbarangmasuk.pdf', 'd.6.3'),
(162, 'Barang Keluar', 'reportbarangkeluar.index', 'd.7'),
(163, 'Print Barang Keluar', 'reportbarangkeluar.print', 'd.7.1'),
(164, 'Excel Barang Keluar', 'reportbarangkeluar.excel', 'd.7.2'),
(165, 'Pdf Barang Keluar', 'reportbarangkeluar.pdf', 'd.7.3'),
(178, 'Report Penjualan', 'reportpenjualan.index', 'd.b'),
(179, 'Print Penjualan', 'reportpenjualan.print', 'd.b.1'),
(180, 'Excel Penjualan', 'reportpenjualan.excel', 'd.b.2'),
(181, 'Pdf Penjualan', 'reportpenjualan.pdf', 'd.b.3'),
(183, 'Keamanan', 'security.index', 'e'),
(184, 'Modul', 'permission.index', 'e.1'),
(185, 'Tambah Modul', 'permission.tambah', 'e.1.1'),
(186, 'Ubah Modul', 'permission.ubah', 'e.1.2'),
(187, 'Akses', 'role.index', 'e.2'),
(188, 'Tambah Akses', 'role.tambah', 'e.2.1'),
(189, 'Ubah Akses', 'role.ubah', 'e.2.2'),
(190, 'Daftar User Akses', 'role.user', 'e.2.3'),
(191, 'Hapus Akses', 'role.hapus', 'e.2.4'),
(192, 'Status Purchase Order Invoice Awal', 'purchaseorder.liststatusinvoiceawal', '8.1.b'),
(200, 'Menu Keuangan', 'menukeuangan.index', '5'),
(201, 'List Biaya', 'transaksi.finance.index', '5.1'),
(203, 'Transaksi Pembayaran', 'transaksi.pembayaran.index', '5.2'),
(204, 'Transaksi Keuangan', 'transaksi.keuangan.index', '5.3'),
(212, 'Menu Retur Produk', 'menuretur.index', '7'),
(213, 'List Retur Produk', 'retur.index', '7.1'),
(214, 'Form Retur Produk', 'retur.index_retur', '7.2'),
(216, 'Jenis Toko', 'toko.jenis.index', '2.b'),
(217, 'Kategori Toko', 'toko.kategori.index', '2.c'),
(218, 'Komponen', 'komponen.index', '2.d'),
(219, 'Tambah Jenis Toko', 'toko.jenis.tambah', '2.b.1'),
(220, 'Ubah Jenis Toko', 'toko.jenis.ubah', '2.b.2'),
(221, 'Hapus Jenis Toko', 'toko.jenis.delete', '2.b.3'),
(222, 'Tambah Kategori Toko', 'toko.kategori.tambah', '2.c.1'),
(223, 'Ubah Kategori Toko', 'toko.kategori.ubah', '2.c.2'),
(224, 'Hapus Kategori Toko', 'toko.kategori.delete', '2.c.3'),
(225, 'Tambah Komponen', 'komponen.tambah', '2.d.1'),
(226, 'Ubah Komponen', 'komponen.ubah', '2.d.2'),
(227, 'Hapus Komponen', 'komponen.delete', '2.d.3'),
(228, 'Menu Kunjungan', 'menukunjungan.index', 'f'),
(229, 'Kunjungan', 'kunjungan.index', 'f.1'),
(230, 'Tambah Kunjungan', 'kunjungan.tambah', 'f.1.1'),
(231, 'Ubah Kunjungan', 'kunjungan.ubah', 'f.1.2'),
(232, 'Hapus Kunjungan', 'kunjungan.delete', 'f.1.3'),
(233, 'Import Pembelian', 'pembelian_import.import', 'c.1.5'),
(234, 'History Delivery Order', 'historydeliveryorder.index', '9.2'),
(235, 'History Delivery Order Detail', 'historydeliveryorder.detail', '9.2.1'),
(236, 'Ubah Biaya', 'transaksi.finance.ubah', '5.1.1'),
(237, 'Hapus Biaya', 'transaksi.finance.delete', '5.1.2');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permissions_slug_index` (`slug`),
  ADD KEY `permissions_nested_index` (`nested`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=238;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
