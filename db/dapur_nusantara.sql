-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 25, 2025 at 12:58 PM
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
-- Database: `dapur_nusantara`
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int NOT NULL,
  `nama_menu` varchar(255) NOT NULL,
  `harga` varchar(255) NOT NULL,
  `gambar_menu` varchar(255) NOT NULL,
  `jenis_makanan` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `nama_menu`, `harga`, `gambar_menu`, `jenis_makanan`) VALUES
(1, 'Nasi Goreng Jawa', 'Rp 28.000', 'nasigoreng.jpg', 'makanan'),
(2, 'Mie goreng special', 'Rp 25.000', 'miegorengspesial.jpg', 'makanan'),
(3, 'Sate ayam madura', 'Rp 35.000', 'sate.jpg', 'makanan');

-- --------------------------------------------------------

--
-- Table structure for table `menuandalan`
--

CREATE TABLE `menuandalan` (
  `menu_id` int NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `nama_menu` varchar(255) NOT NULL,
  `deskripsi_menu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menuandalan`
--

INSERT INTO `menuandalan` (`menu_id`, `gambar`, `nama_menu`, `deskripsi_menu`) VALUES
(1, 'menu_1763335266_590.jpeg', 'Gulai Belacan', 'Gulai Belacan adalah hidangan khas bercita rasa kuat yang memadukan kuah santan kaya rempah dengan aroma belacan yang khas. Teksturnya lembut dan gurih, dengan perpaduan rasa pedas, manis, dan sedikit aroma asap yang membuatnya semakin menggugah selera. Biasanya disajikan dengan potongan daging atau seafood, gulai ini menawarkan kehangatan dan kedalaman rasa yang membuat setiap suapan terasa istimewa. Cocok disantap dengan nasi hangat untuk pengalaman kuliner yang autentik dan memuaskan.'),
(2, 'menu_1763378865_514.jpg', 'Soto Kudus', 'Soto Kudus adalah hidangan berkuah bening khas Jawa Tengah yang terkenal dengan rasa gurih dan aroma rempah yang lembut. Kuahnya dibuat dari rebusan ayam kampung yang dipadukan dengan bumbu seperti serai, daun jeruk, bawang putih, dan sedikit kecap, menghasilkan cita rasa hangat yang ringan namun kaya. Disajikan dengan suwiran ayam, tauge, bawang goreng, serta perasan jeruk nipis, Soto Kudus menawarkan pengalaman makan yang segar dan nyaman di setiap suapan. Cocok dinikmati kapan saja, terutama saat ingin hidangan yang menenangkan dan penuh cita rasa tradisional.'),
(3, 'menu_1763379116_858.jpg', 'Sate Bandeng', 'Sate Bandeng adalah hidangan khas Banten yang dibuat dari ikan bandeng tanpa duri yang diolah hingga memiliki tekstur lembut dan rasa gurih alami. Daging ikan yang sudah dibumbui rempah khas kemudian dipadatkan kembali ke dalam kulitnya dan dipanggang perlahan hingga menghasilkan aroma smoky yang menggugah selera. Perpaduan rasa manis, gurih, dan sedikit asin menjadikan Sate Bandeng unik sekaligus memuaskan. Hidangan ini tidak hanya kaya cita rasa, tetapi juga mencerminkan tradisi kuliner daerah yang penuh kehangatan dan keautentikan. Cocok disantap bersama nasi hangat maupun sebagai sajian khusus di acara keluarga.');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `food_rating` int NOT NULL,
  `service_rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `user_name`, `food_rating`, `service_rating`, `comment`, `created_at`) VALUES
(1, 8, 'Fathurrizqi Hidayat', 2, 4, 'B aja Sieh', '2025-11-18 15:21:39'),
(2, 9, 'Narno Winarno', 4, 4, 'enak banget makanannya apalagi pelayanannya, orangnya ramah-ramah', '2025-11-18 15:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `request_cancellations`
--

CREATE TABLE `request_cancellations` (
  `id` int NOT NULL,
  `reservation_id` int NOT NULL,
  `user_id` int NOT NULL,
  `cancel_type` enum('refund','no_refund') NOT NULL DEFAULT 'no_refund',
  `nama_pemesan` varchar(255) NOT NULL,
  `notelp` varchar(50) NOT NULL,
  `rekening` varchar(255) DEFAULT NULL,
  `requested_at` datetime NOT NULL,
  `status` enum('pending','processed','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `request_cancellations`
--

INSERT INTO `request_cancellations` (`id`, `reservation_id`, `user_id`, `cancel_type`, `nama_pemesan`, `notelp`, `rekening`, `requested_at`, `status`, `admin_note`) VALUES
(4, 15, 7, 'refund', 'Faris Putra Suryadinata', '2314153531', NULL, '2025-11-23 11:49:44', 'rejected', '\nDenied cancellation by admin (2025-11-23 11:49:58)'),
(5, 23, 7, 'no_refund', 'Fakhir dwi', '08142641412', '', '2025-11-25 19:52:52', 'pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `meja_id` int NOT NULL,
  `nama_pemesan` varchar(100) NOT NULL,
  `notelp` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `status` enum('pending','waiting_admin','confirmed','approved','cancelled','request_cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `meja_id`, `nama_pemesan`, `notelp`, `tanggal`, `jam_mulai`, `jam_selesai`, `status`, `bukti_pembayaran`, `created_at`) VALUES
(15, 7, 6, 'Faris Putra Suryadinata', '2314153531', '2025-11-24', '14:00:00', '15:30:00', 'cancelled', '1763873314_gulaibelacan.jpeg', '2025-11-23 04:48:16'),
(16, 7, 5, 'fakhri dwi', '08561827183', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:31:45'),
(17, 3, 4, 'Faris Putra Suryadinata', '089188672', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:32:44'),
(18, 3, 7, 'Faris Putra Suryadinata', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:38:31'),
(19, 3, 6, 'Faris Putra Suryadinata', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:41:04'),
(20, 3, 4, 'Faris Putra Suryadinata', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:46:28'),
(21, 7, 8, 'Fakhir dwi', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 11:55:04'),
(22, 7, 3, 'Fakhir dwi', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'cancelled', NULL, '2025-11-25 12:07:52'),
(23, 7, 5, 'Fakhir dwi', '08142641412', '2025-11-26', '13:00:00', '14:30:00', 'request_cancel', '1764075009_satebandeng.jpg', '2025-11-25 12:50:00');

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int NOT NULL,
  `nomor_meja` int NOT NULL,
  `kapasitas` int NOT NULL,
  `status` enum('tersedia','tidak tersedia') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `nomor_meja`, `kapasitas`, `status`) VALUES
(1, 1, 2, 'tersedia'),
(2, 2, 2, 'tersedia'),
(3, 3, 4, 'tersedia'),
(4, 4, 4, 'tersedia'),
(5, 5, 6, 'tersedia'),
(6, 6, 6, 'tersedia'),
(7, 7, 8, 'tersedia'),
(8, 8, 8, 'tersedia'),
(9, 9, 10, 'tersedia'),
(10, 10, 12, 'tersedia');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@nusantara.com', '$2y$10$8r3WxF8DxF7Ul1PO2uVmxe7Dc65/7XxX9U.JgMihwQnufjJReyW6C', 'admin', '2025-09-21 12:07:11'),
(3, 'Faris Putra Suryadinata', 'Faris12345@gmail.com', '$2y$10$DId61JyfuseGg2hSF62OiOGSIpVqUM/lbe4be7sC2j.XrH2RoyW1y', 'user', '2025-10-01 07:01:33'),
(4, 'Fathurrizqi Hidayat', 'Fathur12345@gmail.com', '$2y$10$h6jybpoCi/NVgZdypMNDluzQnqJZfw9fBKoGQxK9Kwho2Kt/eLb4e', 'admin', '2025-10-15 07:23:00'),
(5, 'Ujang', 'ujang1234@gmail.com', '$2y$10$l/.40mMOt4B/VBcO1vd/JOSb0zkvyxZl0BuqvuzhzqL/uDI5xT.pa', 'user', '2025-11-12 01:46:34'),
(6, 'test', 'test123@gmail.com', '$2y$10$gF1XxW.PQ0b0ikm9BtDiu.Oaf5yP1Zt.8EFcPJ4oeZaFt/CfZwNSW', 'user', '2025-11-14 07:15:15'),
(7, 'fakhri dwi', 'fakhridwi@gmail.com', '$2y$10$t31QZqMlyZA.UypVdh.Oqu7e/XVPXohSSvi0r0tQQxwjHOcw91IAe', 'user', '2025-11-17 00:21:05'),
(8, 'Fathurrizqi Hidayat', 'fathur123@gmail.com', '$2y$10$MXWIJKrInXuRiyu4TH355uk/DNyziDuNW78KOKaYCFCmXqdQNW7L6', 'user', '2025-11-18 13:59:54'),
(9, 'Narno Winarno', 'narno123@gmail.com', '$2y$10$fVFW3vXaAsy.kc817y1vUeav9dTvut6Rhh6Kxt5NVBP05PgRUY2Tq', 'user', '2025-11-18 15:23:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `menuandalan`
--
ALTER TABLE `menuandalan`
  ADD PRIMARY KEY (`menu_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `request_cancellations`
--
ALTER TABLE `request_cancellations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `meja_id` (`meja_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_meja` (`nomor_meja`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menuandalan`
--
ALTER TABLE `menuandalan`
  MODIFY `menu_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_cancellations`
--
ALTER TABLE `request_cancellations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_cancellations`
--
ALTER TABLE `request_cancellations`
  ADD CONSTRAINT `request_cancellations_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_cancellations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`meja_id`) REFERENCES `tables` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
