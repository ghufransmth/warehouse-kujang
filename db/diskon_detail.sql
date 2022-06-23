-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2022 at 03:57 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kms`
--

-- --------------------------------------------------------

--
-- Table structure for table `diskon_detail`
--

CREATE TABLE `diskon_detail` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(175) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_diskon` tinyint(1) DEFAULT NULL COMMENT '0: Distributor\r\n1: Principal',
  `min_beli` double DEFAULT NULL,
  `max_beli` double DEFAULT NULL,
  `nilai_diskon` double DEFAULT NULL,
  `jenis_diskon` tinyint(4) DEFAULT NULL COMMENT '0: Diskon Uang; 1: Bonus Barang',
  `kelipatan` tinyint(4) DEFAULT NULL COMMENT '0: Y; 1: T',
  `produk` int(11) DEFAULT NULL,
  `jml_produk` int(11) DEFAULT NULL,
  `satuan` int(11) DEFAULT NULL,
  `bonus_produk` int(11) DEFAULT NULL,
  `jml_bonus` int(11) DEFAULT NULL,
  `satuan_bonus` int(11) DEFAULT NULL,
  `tgl_dari` date DEFAULT NULL,
  `tgl_sampai` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `diskon_detail`
--

INSERT INTO `diskon_detail` (`id`, `name`, `flag_diskon`, `min_beli`, `max_beli`, `nilai_diskon`, `jenis_diskon`, `kelipatan`, `produk`, `jml_produk`, `satuan`, `bonus_produk`, `jml_bonus`, `satuan_bonus`, `tgl_dari`, `tgl_sampai`, `created_at`, `updated_at`) VALUES
(0, 'DISKON A', 0, 5000, 10000, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '0000-00-00', '0000-00-00', '2022-05-12 13:49:39', '2022-05-12 13:49:39'),
(0, 'Diskon B', 0, 100000, 200000, 5, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2022-05-12', '2022-05-15', '2022-05-12 13:51:26', '2022-05-12 13:51:26'),
(0, 'Diskon C', 0, 40000, 50000, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2022-05-12', '2022-05-12', '2022-05-12 13:53:27', '2022-05-12 13:53:27');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
