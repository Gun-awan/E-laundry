-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Nov 2026 pada 04.51
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laundry`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang_tambahan`
--

CREATE TABLE `barang_tambahan` (
  `id` int(11) NOT NULL,
  `nama_barang` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `barang_tambahan`
--

INSERT INTO `barang_tambahan` (`id`, `nama_barang`, `harga`) VALUES
(1, 'Jaket', 10000),
(2, 'Sprei', 15000),
(3, 'Sarung', 5000),
(4, 'Boneka', 20000);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id` int(11) NOT NULL,
  `transaksi_id` int(11) DEFAULT NULL,
  `paket_id` int(11) DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `subtotal` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `paket`
--

CREATE TABLE `paket` (
  `id` int(11) NOT NULL,
  `nama_paket` varchar(100) DEFAULT NULL,
  `harga` int(11) DEFAULT NULL,
  `keterangan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `paket`
--

INSERT INTO `paket` (`id`, `nama_paket`, `harga`, `keterangan`) VALUES
(1, 'Setrika', 5000, '1'),
(2, 'Cuci Setrika', 7000, '3'),
(3, 'Express', 10000, '1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `no_hp`, `alamat`) VALUES
(4, 'gunawan', '0881011428128', 'serang'),
(5, 'ahmad', '0812345678', 'desa serang'),
(6, 'Ayara', '085974550203', 'kalimas'),
(7, 'irgi', '0812131342', ''),
(8, 'dama', '0856564464', ''),
(9, 'zaki', '0832673', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `kode_invoice` varchar(50) DEFAULT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `paket_id` int(11) DEFAULT NULL,
  `berat` double DEFAULT NULL,
  `total` int(11) DEFAULT NULL,
  `tambahan` int(11) DEFAULT 0,
  `bayar` int(11) DEFAULT NULL,
  `kembalian` int(11) DEFAULT NULL,
  `status` enum('Diproses','Dicuci','Disetrika','Selesai','Diambil') NOT NULL DEFAULT 'Diproses',
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `deadline` datetime DEFAULT NULL,
  `status_bayar` enum('Bayar Sekarang','Bayar Nanti') DEFAULT 'Bayar Sekarang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id`, `kode_invoice`, `pelanggan_id`, `paket_id`, `berat`, `total`, `tambahan`, `bayar`, `kembalian`, `status`, `tanggal`, `deadline`, `status_bayar`) VALUES
(35, 'INV-05201410', 8, 3, 2, 20000, 0, 0, -20000, 'Diproses', '2026-05-20 07:10:34', '2026-05-21 14:10:34', 'Bayar Nanti'),
(36, 'INV-05201412', 5, 2, 2, 14000, 0, 0, -14000, 'Diproses', '2026-05-20 07:12:21', '2026-05-23 14:12:21', 'Bayar Nanti'),
(37, 'INV-05201419', 6, 2, 4, 28000, 0, 30000, 2000, 'Diproses', '2026-05-20 07:19:23', '2026-05-23 14:19:23', 'Bayar Sekarang'),
(38, 'INV-05201421', 5, 2, 2, 14000, 0, 0, -14000, 'Diproses', '2026-05-20 07:21:08', '2026-05-23 14:21:08', 'Bayar Nanti'),
(39, 'INV-05201425', 6, 3, 3, 30000, 0, 0, -30000, 'Diproses', '2026-05-20 07:25:32', '2026-05-23 14:25:32', 'Bayar Nanti'),
(40, 'INV-05201453', 6, 3, 2, 20000, 0, 0, -20000, 'Diproses', '2026-05-20 07:53:09', '2026-05-23 14:53:09', 'Bayar Nanti'),
(41, 'INV-05201459', 6, 1, 1, 5000, 0, 0, -5000, 'Selesai', '2026-05-20 07:59:26', '2026-05-21 14:59:26', 'Bayar Nanti');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','kasir') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang_tambahan`
--
ALTER TABLE `barang_tambahan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `paket`
--
ALTER TABLE `paket`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_hp` (`no_hp`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang_tambahan`
--
ALTER TABLE `barang_tambahan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `paket`
--
ALTER TABLE `paket`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
