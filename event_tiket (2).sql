-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 26 Apr 2026 pada 14.54
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_tiket`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `attendee`
--

CREATE TABLE `attendee` (
  `id_attendee` int(11) NOT NULL,
  `id_detail` int(11) NOT NULL,
  `kode_tiket` varchar(50) NOT NULL,
  `status_checkin` enum('belum','sudah') NOT NULL DEFAULT 'belum',
  `waktu_checkin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `attendee`
--

INSERT INTO `attendee` (`id_attendee`, `id_detail`, `kode_tiket`, `status_checkin`, `waktu_checkin`) VALUES
(64, 27, 'TKT-126FEC5A', 'sudah', '2026-04-23 06:30:56'),
(65, 27, 'TKT-D6C2E5AA', 'sudah', '2026-04-23 06:29:35'),
(66, 27, 'TKT-4F6808E7', 'belum', NULL),
(67, 27, 'TKT-EC12F256', 'belum', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `event`
--

CREATE TABLE `event` (
  `id_event` int(11) NOT NULL,
  `nama_event` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `id_venue` int(11) NOT NULL,
  `gambar` varchar(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `event`
--

INSERT INTO `event` (`id_event`, `nama_event`, `deskripsi`, `tanggal`, `id_venue`, `gambar`) VALUES
(9, 'Gebyar Anyar', '', '2026-04-25', 2, 'event_69e99fcdc1f88.webp');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `tanggal_order` datetime DEFAULT current_timestamp(),
  `total` int(11) NOT NULL DEFAULT 0,
  `status` enum('pending','paid','cancel') NOT NULL DEFAULT 'pending',
  `id_voucher` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id_order`, `id_user`, `tanggal_order`, `total`, `status`, `id_voucher`) VALUES
(16, 5, '2026-04-20 12:01:09', 0, 'paid', NULL),
(17, 5, '2026-04-20 14:15:42', 0, 'paid', NULL),
(18, 2, '2026-04-22 12:38:28', 0, 'cancel', NULL),
(19, 2, '2026-04-22 12:38:43', 0, 'cancel', NULL),
(20, 2, '2026-04-22 12:39:42', 1980000, 'pending', NULL),
(21, 2, '2026-04-22 12:40:35', 3980000, 'paid', NULL),
(22, 2, '2026-04-23 07:23:20', 20000, 'paid', NULL),
(23, 2, '2026-04-23 07:40:36', 600000, 'paid', NULL),
(24, 2, '2026-04-23 07:45:36', 20000, 'paid', NULL),
(25, 2, '2026-04-23 08:00:15', 20000, 'cancel', NULL),
(26, 2, '2026-04-23 08:43:35', 10000, 'cancel', NULL),
(27, 2, '2026-04-23 11:29:20', 750000, 'paid', 4);

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_detail`
--

CREATE TABLE `order_detail` (
  `id_detail` int(11) NOT NULL,
  `id_order` int(11) NOT NULL,
  `id_tiket` int(11) NOT NULL,
  `qty` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_detail`
--

INSERT INTO `order_detail` (`id_detail`, `id_order`, `id_tiket`, `qty`, `subtotal`) VALUES
(27, 27, 16, 4, 800000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `tiket`
--

CREATE TABLE `tiket` (
  `id_tiket` int(11) NOT NULL,
  `id_event` int(11) NOT NULL,
  `nama_tiket` varchar(50) NOT NULL,
  `harga` int(11) NOT NULL,
  `kuota` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tiket`
--

INSERT INTO `tiket` (`id_tiket`, `id_event`, `nama_tiket`, `harga`, `kuota`) VALUES
(16, 9, 'Normal', 200000, 96);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','petugas','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Admin', 'admin@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'User', 'user@event.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
(3, 'Sodiq Rahmadtullah', 'admin@gmail.com', '$2y$10$vAE4rg0nypFzYifcBHMRbuodIdM5p45jnw26E5cX1FUSd3GLmzT0.', 'user'),
(4, 'Nama Petugas', 'petugas@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas'),
(5, 'Puki', 'puki@gmail.com', '$2y$10$l4re42rhMzx5oAiqnma.zODdGHAdhnMsg.LyM4sXCYDswMr4xjqxC', 'user');

-- --------------------------------------------------------

--
-- Struktur dari tabel `venue`
--

CREATE TABLE `venue` (
  `id_venue` int(11) NOT NULL,
  `nama_venue` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `kapasitas` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `venue`
--

INSERT INTO `venue` (`id_venue`, `nama_venue`, `alamat`, `kapasitas`) VALUES
(2, 'Jakarta International ', 'Jl Ada', 1000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `voucher`
--

CREATE TABLE `voucher` (
  `id_voucher` int(11) NOT NULL,
  `kode_voucher` varchar(20) NOT NULL,
  `potongan` int(11) NOT NULL,
  `kuota` int(11) NOT NULL,
  `status` enum('aktif','nonaktif') NOT NULL DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `voucher`
--

INSERT INTO `voucher` (`id_voucher`, `kode_voucher`, `potongan`, `kuota`, `status`) VALUES
(4, 'EVENT2025', 50000, 21, 'aktif');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `attendee`
--
ALTER TABLE `attendee`
  ADD PRIMARY KEY (`id_attendee`),
  ADD UNIQUE KEY `kode_tiket` (`kode_tiket`),
  ADD KEY `id_detail` (`id_detail`);

--
-- Indeks untuk tabel `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`),
  ADD KEY `id_venue` (`id_venue`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id_order`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_voucher` (`id_voucher`);

--
-- Indeks untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_order` (`id_order`),
  ADD KEY `id_tiket` (`id_tiket`);

--
-- Indeks untuk tabel `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id_tiket`),
  ADD KEY `id_event` (`id_event`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `venue`
--
ALTER TABLE `venue`
  ADD PRIMARY KEY (`id_venue`);

--
-- Indeks untuk tabel `voucher`
--
ALTER TABLE `voucher`
  ADD PRIMARY KEY (`id_voucher`),
  ADD UNIQUE KEY `kode_voucher` (`kode_voucher`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `attendee`
--
ALTER TABLE `attendee`
  MODIFY `id_attendee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT untuk tabel `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id_order` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT untuk tabel `tiket`
--
ALTER TABLE `tiket`
  MODIFY `id_tiket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `venue`
--
ALTER TABLE `venue`
  MODIFY `id_venue` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `voucher`
--
ALTER TABLE `voucher`
  MODIFY `id_voucher` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `attendee`
--
ALTER TABLE `attendee`
  ADD CONSTRAINT `attendee_ibfk_1` FOREIGN KEY (`id_detail`) REFERENCES `order_detail` (`id_detail`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`id_venue`) REFERENCES `venue` (`id_venue`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`id_voucher`) REFERENCES `voucher` (`id_voucher`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `order_detail`
--
ALTER TABLE `order_detail`
  ADD CONSTRAINT `order_detail_ibfk_1` FOREIGN KEY (`id_order`) REFERENCES `orders` (`id_order`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_detail_ibfk_2` FOREIGN KEY (`id_tiket`) REFERENCES `tiket` (`id_tiket`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_ibfk_1` FOREIGN KEY (`id_event`) REFERENCES `event` (`id_event`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
