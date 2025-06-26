-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 26, 2025 at 09:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_buanapetshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id_admin` bigint(11) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `profile` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id_admin`, `id_user`, `nama`, `profile`) VALUES
(1, 3, 'admin111', 'profile_3_1749987691.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tb_barang`
--

CREATE TABLE `tb_barang` (
  `id_barang` bigint(11) NOT NULL,
  `nama_barang` varchar(255) NOT NULL,
  `deskripsi_barang` varchar(255) NOT NULL,
  `foto_barang` text NOT NULL,
  `tipe_barang` varchar(255) NOT NULL,
  `harga_barang` varchar(255) NOT NULL,
  `tanggal_dibuat` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_booking`
--

CREATE TABLE `tb_booking` (
  `id_booking` bigint(11) NOT NULL,
  `id_service` bigint(11) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `waktu_booking` datetime NOT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_booking`
--

INSERT INTO `tb_booking` (`id_booking`, `id_service`, `id_user`, `waktu_booking`, `created_at`) VALUES
(18, 6, 28, '2025-06-23 20:00:00', NULL),
(20, 6, 27, '2025-06-30 20:00:00', '2025-06-26 14:29:04');

-- --------------------------------------------------------

--
-- Table structure for table `tb_cart`
--

CREATE TABLE `tb_cart` (
  `id_cart` bigint(11) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `id_barang` bigint(11) NOT NULL,
  `jumlah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_jadwal`
--

CREATE TABLE `tb_jadwal` (
  `id_jadwal` bigint(11) NOT NULL,
  `id_service` bigint(11) NOT NULL,
  `hari` varchar(255) NOT NULL,
  `duty_start` time NOT NULL,
  `duty_end` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jadwal`
--

INSERT INTO `tb_jadwal` (`id_jadwal`, `id_service`, `hari`, `duty_start`, `duty_end`) VALUES
(21, 6, 'Senin', '20:00:00', '21:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tb_pelanggan`
--

CREATE TABLE `tb_pelanggan` (
  `id_pelanggan` bigint(11) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `profile` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pelanggan`
--

INSERT INTO `tb_pelanggan` (`id_pelanggan`, `id_user`, `nama`, `profile`) VALUES
(10, 27, 'tes1123', '../assets/uploads/profile/profile_27_1749986364.png'),
(11, 28, 'Adamas', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_pimpinan`
--

CREATE TABLE `tb_pimpinan` (
  `id_pimpinan` bigint(11) NOT NULL,
  `id_user` bigint(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `profile` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_pimpinan`
--

INSERT INTO `tb_pimpinan` (`id_pimpinan`, `id_user`, `nama`, `profile`) VALUES
(11, 26, 'tespimpinan11', 'profile_26_1749988327.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tb_service`
--

CREATE TABLE `tb_service` (
  `id_service` bigint(11) NOT NULL,
  `nama_service` varchar(255) NOT NULL,
  `deskripsi_service` varchar(255) NOT NULL,
  `harga_service` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_service`
--

INSERT INTO `tb_service` (`id_service`, `nama_service`, `deskripsi_service`, `harga_service`) VALUES
(6, 'Grooming Basic', 'tes', '100000');

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi_cart`
--

CREATE TABLE `tb_transaksi_cart` (
  `id_transaksi_cart` bigint(11) NOT NULL,
  `id_cart` bigint(11) NOT NULL,
  `status_transaksi_cart` varchar(255) NOT NULL,
  `bukti_transaksi_cart` text DEFAULT NULL,
  `tgl_transaksi_cart` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_transaksi_service`
--

CREATE TABLE `tb_transaksi_service` (
  `id_transaksi_service` bigint(11) NOT NULL,
  `id_booking` bigint(11) NOT NULL,
  `status_transaksi_service` varchar(255) NOT NULL,
  `bukti_transaksi_service` text DEFAULT NULL,
  `tgl_transaksi_service` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_transaksi_service`
--

INSERT INTO `tb_transaksi_service` (`id_transaksi_service`, `id_booking`, `status_transaksi_service`, `bukti_transaksi_service`, `tgl_transaksi_service`) VALUES
(9, 18, 'pending', 'payment_service_18_1750341797.jpg', '2025-06-19');

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` bigint(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `username`, `password`, `role`) VALUES
(3, 'admin1', '123', 'admin'),
(26, 'tespimpinan1', '123123', 'pimpinan'),
(27, 'tes1123', '123123', 'pelanggan'),
(28, 'tes123', '123123', 'pelanggan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_barang`
--
ALTER TABLE `tb_barang`
  ADD PRIMARY KEY (`id_barang`);

--
-- Indexes for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD PRIMARY KEY (`id_booking`),
  ADD KEY `id_service` (`id_service`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_cart`
--
ALTER TABLE `tb_cart`
  ADD PRIMARY KEY (`id_cart`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indexes for table `tb_jadwal`
--
ALTER TABLE `tb_jadwal`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_service` (`id_service`);

--
-- Indexes for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_pimpinan`
--
ALTER TABLE `tb_pimpinan`
  ADD PRIMARY KEY (`id_pimpinan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `tb_service`
--
ALTER TABLE `tb_service`
  ADD PRIMARY KEY (`id_service`);

--
-- Indexes for table `tb_transaksi_cart`
--
ALTER TABLE `tb_transaksi_cart`
  ADD PRIMARY KEY (`id_transaksi_cart`),
  ADD KEY `id_cart` (`id_cart`);

--
-- Indexes for table `tb_transaksi_service`
--
ALTER TABLE `tb_transaksi_service`
  ADD PRIMARY KEY (`id_transaksi_service`),
  ADD KEY `id_booking` (`id_booking`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id_admin` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_barang`
--
ALTER TABLE `tb_barang`
  MODIFY `id_barang` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tb_booking`
--
ALTER TABLE `tb_booking`
  MODIFY `id_booking` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tb_cart`
--
ALTER TABLE `tb_cart`
  MODIFY `id_cart` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tb_jadwal`
--
ALTER TABLE `tb_jadwal`
  MODIFY `id_jadwal` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  MODIFY `id_pelanggan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_pimpinan`
--
ALTER TABLE `tb_pimpinan`
  MODIFY `id_pimpinan` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tb_service`
--
ALTER TABLE `tb_service`
  MODIFY `id_service` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tb_transaksi_cart`
--
ALTER TABLE `tb_transaksi_cart`
  MODIFY `id_transaksi_cart` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tb_transaksi_service`
--
ALTER TABLE `tb_transaksi_service`
  MODIFY `id_transaksi_service` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD CONSTRAINT `tb_admin_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_booking`
--
ALTER TABLE `tb_booking`
  ADD CONSTRAINT `tb_booking_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `tb_service` (`id_service`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_booking_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_cart`
--
ALTER TABLE `tb_cart`
  ADD CONSTRAINT `tb_cart_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tb_cart_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `tb_barang` (`id_barang`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_jadwal`
--
ALTER TABLE `tb_jadwal`
  ADD CONSTRAINT `tb_jadwal_ibfk_1` FOREIGN KEY (`id_service`) REFERENCES `tb_service` (`id_service`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  ADD CONSTRAINT `tb_pelanggan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_pimpinan`
--
ALTER TABLE `tb_pimpinan`
  ADD CONSTRAINT `tb_pimpinan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `tb_user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_transaksi_cart`
--
ALTER TABLE `tb_transaksi_cart`
  ADD CONSTRAINT `tb_transaksi_cart_ibfk_1` FOREIGN KEY (`id_cart`) REFERENCES `tb_cart` (`id_cart`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_transaksi_service`
--
ALTER TABLE `tb_transaksi_service`
  ADD CONSTRAINT `tb_transaksi_service_ibfk_1` FOREIGN KEY (`id_booking`) REFERENCES `tb_booking` (`id_booking`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
