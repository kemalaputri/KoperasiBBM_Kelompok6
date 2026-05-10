-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 04:36 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koperasibbm`
--

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas`
--

CREATE TABLE `aktivitas` (
  `id_aktivitas` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `aksi` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int NOT NULL,
  `id_pesanan` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL,
  `harga_saat_pesan` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `jumlah`, `harga_saat_pesan`) VALUES
(1, 1, 4, 1, 4000.00),
(2, 1, 2, 2, 4000.00),
(3, 2, 2, 1, 4000.00),
(4, 3, 1, 1, 3000.00),
(5, 4, 4, 1, 4000.00),
(6, 5, 1, 3, 3000.00),
(7, 5, 2, 6, 4000.00),
(8, 6, 1, 3, 3000.00),
(9, 7, 6, 1, 15000.00),
(10, 8, 1, 4, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Perlengkapan Sekolah'),
(2, 'Kebutuhan Pokok'),
(3, 'Makanan'),
(4, 'Minuman'),
(5, 'Kesehatan'),
(6, 'Peralatan Rumah Tangga');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int NOT NULL,
  `id_user` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id_keranjang`, `id_user`, `created_at`) VALUES
(1, 3, '2026-05-09 09:37:14'),
(2, 4, '2026-05-10 14:05:08'),
(3, 7, '2026-05-10 14:15:03'),
(4, 8, '2026-05-10 14:54:01');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang_item`
--

CREATE TABLE `keranjang_item` (
  `id_item` int NOT NULL,
  `id_keranjang` int NOT NULL,
  `id_produk` int NOT NULL,
  `jumlah` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `keranjang_item`
--

INSERT INTO `keranjang_item` (`id_item`, `id_keranjang`, `id_produk`, `jumlah`) VALUES
(5, 1, 2, 1),
(12, 4, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int NOT NULL,
  `tipe_laporan` enum('Harian','Mingguan','Bulanan') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `total_pendapatan` decimal(10,2) DEFAULT '0.00',
  `dibuat_oleh` int NOT NULL,
  `total_online` decimal(12,2) DEFAULT '0.00',
  `total_offline` decimal(12,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `tipe_laporan`, `tanggal_mulai`, `tanggal_selesai`, `total_pendapatan`, `dibuat_oleh`, `total_online`, `total_offline`, `created_at`, `sent_at`) VALUES
(2, 'Harian', '2026-05-10', '2026-05-10', 0.00, 2, 0.00, 0.00, '2026-05-10 10:29:17', NULL),
(3, 'Bulanan', '2026-05-01', '2026-05-31', 23000.00, 2, 0.00, 23000.00, '2026-05-10 10:29:26', NULL),
(4, 'Harian', '2026-05-10', '2026-05-10', 4000.00, 5, 0.00, 4000.00, '2026-05-10 14:59:09', NULL),
(5, 'Mingguan', '2026-04-27', '2026-05-03', 0.00, 5, 0.00, 0.00, '2026-05-10 14:59:22', NULL),
(8, 'Mingguan', '2026-05-18', '2026-05-24', 0.00, 5, 0.00, 0.00, '2026-05-10 15:27:58', NULL),
(9, 'Bulanan', '2026-04-01', '2026-04-30', 0.00, 5, 0.00, 0.00, '2026-05-10 15:28:08', NULL),
(10, 'Harian', '2026-05-10', '2026-05-10', 11000.00, 5, 0.00, 11000.00, '2026-05-10 15:42:00', '2026-05-10 16:07:35'),
(11, 'Mingguan', '2026-05-18', '2026-05-24', 0.00, 5, 0.00, 0.00, '2026-05-10 16:07:57', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_request`
--

CREATE TABLE `password_request` (
  `id_request` int NOT NULL,
  `id_user` int NOT NULL,
  `new_password` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_request`
--

INSERT INTO `password_request` (`id_request`, `id_user`, `new_password`, `status`, `created_at`) VALUES
(1, 5, '$2y$10$7aCj7Kx2KNhPeVLyVEVDZOTbZsjrJyX4MU/qjCbb75XlyZEqZgNDe', 'Pending', '2026-05-10 15:00:17'),
(2, 5, '$2y$10$fpocwldnU/v3ABWpa87dMO8IM8ZeRzEYFYhTYOhUPlYjH3gg0pYGy', 'Pending', '2026-05-10 15:28:55'),
(3, 5, '$2y$10$EP5goRhx5uXmD5l2JuJDee77UvhORmzXPpb55e8kkQF/PKluYJeP2', 'Pending', '2026-05-10 15:42:43'),
(4, 5, '$2y$10$5BqtQXu8YM0.WcCU.zUf5.G70W7qPBNTQ21yIR/I77/SOx267KXLO', 'Pending', '2026-05-10 16:08:25');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int NOT NULL,
  `kode_pesanan` varchar(20) DEFAULT NULL,
  `id_user` int NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `status` enum('Sedang disiapkan','Siap diambil','Selesai','Batal') NOT NULL DEFAULT 'Sedang disiapkan',
  `metode_pengambilan` enum('Mandiri','Slot Waktu') DEFAULT 'Mandiri',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `id_slot` int DEFAULT NULL,
  `jadwal_pengambilan` varchar(100) DEFAULT 'Mandiri',
  `tanggal_ambil` date DEFAULT NULL,
  `jam_ambil` varchar(50) DEFAULT NULL,
  `catatan` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_user`, `total_harga`, `status`, `metode_pengambilan`, `created_at`, `id_slot`, `jadwal_pengambilan`, `tanggal_ambil`, `jam_ambil`, `catatan`) VALUES
(1, 'KOP-20260510-771758', 3, 12000.00, 'Selesai', 'Slot Waktu', '2026-05-10 09:00:57', NULL, '2026-05-11 13:00 - 14:00', '2026-05-11', '13:00 - 14:00', ''),
(2, 'KOP-20260510-482035', 3, 4000.00, 'Sedang disiapkan', 'Slot Waktu', '2026-05-10 09:26:01', NULL, '2026-05-12 08:00 - 09:00', '2026-05-12', '08:00 - 09:00', ''),
(3, 'KOP-20260510-392385', 4, 3000.00, 'Batal', 'Slot Waktu', '2026-05-10 14:05:30', NULL, '2026-05-11 07:00 - 08:00', '2026-05-11', '07:00 - 08:00', 'yang bagus'),
(4, 'KOP-20260510-745020', 7, 4000.00, 'Siap diambil', 'Slot Waktu', '2026-05-10 14:15:17', NULL, '2026-05-11 07:00 - 08:00', '2026-05-11', '07:00 - 08:00', ''),
(5, 'KOP-20260510-984182', 8, 33000.00, 'Selesai', 'Slot Waktu', '2026-05-10 14:54:50', NULL, '2026-05-11 07:00 - 08:00', '2026-05-11', '07:00 - 08:00', 'diplastikkan'),
(6, 'KOP-20260510-619642', 8, 9000.00, 'Selesai', 'Slot Waktu', '2026-05-10 15:22:51', NULL, '2026-05-11 07:00 - 08:00', '2026-05-11', '07:00 - 08:00', ''),
(7, 'KOP-20260510-170084', 4, 15000.00, 'Selesai', 'Slot Waktu', '2026-05-10 15:44:22', NULL, '2026-05-11 07:00 - 08:00', '2026-05-11', '07:00 - 08:00', ''),
(8, 'KOP-20260510-574483', 4, 12000.00, 'Selesai', 'Slot Waktu', '2026-05-10 16:03:28', NULL, '2026-05-12 09:00 - 10:00', '2026-05-12', '09:00 - 10:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int NOT NULL,
  `id_kategori` int NOT NULL,
  `nama_produk` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `deskripsi` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_kategori`, `nama_produk`, `harga`, `stok`, `gambar`, `deskripsi`, `created_at`, `updated_at`) VALUES
(1, 1, 'Pensil 2B', 3000.00, 15, '6a0097904dfb9.jpeg', '', '2026-05-09 01:24:23', '2026-05-10 16:03:28'),
(2, 1, 'Pulpen Standar', 4000.00, 0, '6a0097a19c5b5.jpg', '', '2026-05-09 01:24:23', '2026-05-10 15:27:17'),
(3, 2, 'Gula Pasir 500g', 12000.00, 0, '6a00986a7170d.jpeg', '', '2026-05-09 01:24:23', '2026-05-10 14:38:34'),
(4, 4, 'Aqua 600ml', 4000.00, 96, '6a0097f7ab173.jpeg', '', '2026-05-09 01:24:23', '2026-05-10 16:09:09'),
(5, 5, 'Hansaplast', 8000.00, 8, '6a0098040f42f.jpeg', '', '2026-05-09 01:24:23', '2026-05-10 14:36:52'),
(6, 5, 'Pasta Gigi Pepsodent', 15000.00, 27, '6a00984a60f23.jpeg', '', '2026-05-09 01:24:23', '2026-05-10 16:07:21'),
(7, 3, 'Mie Goreng Instan', 3500.00, 30, '6a009acac6f2d.jpg', '', '2026-05-10 14:48:42', '2026-05-10 14:48:52'),
(8, 6, 'Shampo Clear Sachet', 25000.00, 33, '6a009b125034c.jpg', '', '2026-05-10 14:49:54', '2026-05-10 14:49:54');

-- --------------------------------------------------------

--
-- Table structure for table `slot_pengambilan`
--

CREATE TABLE `slot_pengambilan` (
  `id_slot` int NOT NULL,
  `hari` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `kuota` int NOT NULL DEFAULT '10'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `slot_pengambilan`
--

INSERT INTO `slot_pengambilan` (`id_slot`, `hari`, `jam_mulai`, `jam_selesai`, `kuota`) VALUES
(1, 'Senin', '07:00:00', '09:00:00', 10),
(2, 'Senin', '12:00:00', '14:00:00', 10),
(3, 'Selasa', '07:00:00', '09:00:00', 10),
(4, 'Rabu', '12:00:00', '14:00:00', 10),
(5, 'Kamis', '07:00:00', '09:00:00', 10),
(6, 'Jumat', '12:00:00', '14:00:00', 10);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_offline`
--

CREATE TABLE `transaksi_offline` (
  `id_transaksi_offline` int NOT NULL,
  `id_user` int DEFAULT NULL,
  `nama_kasir` varchar(100) NOT NULL,
  `total_harga` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi_offline`
--

INSERT INTO `transaksi_offline` (`id_transaksi_offline`, `id_user`, `nama_kasir`, `total_harga`, `created_at`) VALUES
(1, NULL, 'Operator Koperasi', 8000.00, '2026-05-09 14:13:28'),
(2, NULL, 'Operator Koperasi', 15000.00, '2026-05-09 15:44:10'),
(3, NULL, 'Operator Dua', 4000.00, '2026-05-10 14:58:53'),
(4, NULL, 'Operator Dua', 4000.00, '2026-05-10 15:27:17'),
(5, NULL, 'Operator Dua', 3000.00, '2026-05-10 15:41:31'),
(6, NULL, 'Operator Dua', 15000.00, '2026-05-10 16:07:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_telepon` varchar(20) DEFAULT NULL,
  `role` enum('admin','operator','user') NOT NULL DEFAULT 'user',
  `jenis_anggota` enum('Murid','Guru','Internal Sekolah') DEFAULT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `bagian_internal` enum('kantin','satpam','staf sekolah') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama_lengkap`, `username`, `password`, `no_telepon`, `role`, `jenis_anggota`, `nis`, `nip`, `bagian_internal`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$WfT5Ff1JHlFKIiwO8TtcTOkkTXRJX2uV3MSkmPFtqLpQlI4druIwa', NULL, 'admin', NULL, NULL, NULL, NULL, '2026-05-09 01:24:23'),
(2, 'Operator Koperasi', 'operator', '$2y$10$aKU04HdL3isbNTiFZinizeMyoQqeXh6XL1zGTVIEcKu2PezzXMwkK', NULL, 'operator', NULL, NULL, NULL, NULL, '2026-05-09 01:24:23'),
(3, 'Kemala Putri Oktaviani', 'kemala', '$2y$10$2JHKsOSQarRVqFLM0DtdUe.xOaUau0061TDtNvaGiztOblxZGoqnu', '081282828146', 'user', 'Murid', '1024012325', '', 'kantin', '2026-05-09 09:36:43'),
(4, 'Rafa Zulfahmi', 'Rafa', '$2y$10$tIesgokVrtl5.Ygc9g2otuQAO7OvWO9P9/tMgJ06dSvKPOBHbLs9e', '081276782365', 'user', 'Guru', '', '198507222005022005', 'kantin', '2026-05-10 09:30:32'),
(5, 'Operator Dua', 'operator2', '$2y$10$7aCj7Kx2KNhPeVLyVEVDZOTbZsjrJyX4MU/qjCbb75XlyZEqZgNDe', '081282828133', 'operator', NULL, NULL, NULL, NULL, '2026-05-10 12:52:03'),
(7, 'rinzani Fauziah', 'rinzani', '$2y$10$53vUED1Av0FJI8Kvpcn3med7taIDZJ/Gb/mWs8Ns/FRxcqDjHseee', '081282812146', 'user', 'Internal Sekolah', '', '', 'staf sekolah', '2026-05-10 14:14:40'),
(8, 'Putri Kemala', 'oktaviani', '$2y$10$NRgr1fFSSDlcGZWiqRJZEOT5NrPhFeCLRW9oNg7t5mph0PPPne5XG', '081282828132', 'user', 'Murid', '2024001370', '', 'kantin', '2026-05-10 14:53:10'),
(10, 'Fauziah Rinzani', 'fauziah', '$2y$10$Id8r67TU6r82rb8p9O0b8ePXQnRGKEVxTkY37rJu9SImBVCLXLjr6', '081282828213', 'user', 'Internal Sekolah', '', '198507222005022005', 'satpam', '2026-05-10 15:20:51'),
(14, 'kemala kemala', 'okta', '$2y$10$9uV5fDfMJbzg.cAAdwZfKOOb0J1QC4/GOIC1tRSeuTOrZ/SsxH1X2', '081282828133', 'user', 'Internal Sekolah', '', '', 'kantin', '2026-05-10 16:18:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD PRIMARY KEY (`id_aktivitas`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_pesanan` (`id_pesanan`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `keranjang_item`
--
ALTER TABLE `keranjang_item`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `id_keranjang` (`id_keranjang`),
  ADD KEY `id_produk` (`id_produk`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `dibuat_oleh` (`dibuat_oleh`);

--
-- Indexes for table `password_request`
--
ALTER TABLE `password_request`
  ADD PRIMARY KEY (`id_request`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `slot_pengambilan`
--
ALTER TABLE `slot_pengambilan`
  ADD PRIMARY KEY (`id_slot`);

--
-- Indexes for table `transaksi_offline`
--
ALTER TABLE `transaksi_offline`
  ADD PRIMARY KEY (`id_transaksi_offline`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aktivitas`
--
ALTER TABLE `aktivitas`
  MODIFY `id_aktivitas` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `keranjang_item`
--
ALTER TABLE `keranjang_item`
  MODIFY `id_item` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `password_request`
--
ALTER TABLE `password_request`
  MODIFY `id_request` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `slot_pengambilan`
--
ALTER TABLE `slot_pengambilan`
  MODIFY `id_slot` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transaksi_offline`
--
ALTER TABLE `transaksi_offline`
  MODIFY `id_transaksi_offline` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aktivitas`
--
ALTER TABLE `aktivitas`
  ADD CONSTRAINT `aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `keranjang_item`
--
ALTER TABLE `keranjang_item`
  ADD CONSTRAINT `keranjang_item_ibfk_1` FOREIGN KEY (`id_keranjang`) REFERENCES `keranjang` (`id_keranjang`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_item_ibfk_2` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE;

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `password_request`
--
ALTER TABLE `password_request`
  ADD CONSTRAINT `password_request_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE;

--
-- Constraints for table `transaksi_offline`
--
ALTER TABLE `transaksi_offline`
  ADD CONSTRAINT `transaksi_offline_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
