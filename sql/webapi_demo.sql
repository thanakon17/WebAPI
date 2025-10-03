-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 03, 2025 at 06:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webapi_demo`
--

-- --------------------------------------------------------

--
-- Table structure for table `appliances`
--

CREATE TABLE `appliances` (
  `id` int(10) UNSIGNED NOT NULL,
  `sku` varchar(32) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(80) NOT NULL,
  `category` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
  `warranty_months` tinyint(3) UNSIGNED NOT NULL DEFAULT 12,
  `energy_rating` tinyint(3) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appliances`
--

INSERT INTO `appliances` (`id`, `sku`, `name`, `brand`, `category`, `price`, `stock`, `warranty_months`, `energy_rating`, `created_at`, `updated_at`) VALUES
(1, 'TV-32A1', 'ทีวี 32 นิ้ว HD', 'Panaphonic', 'ทีวี', 4990.00, 12, 24, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(2, 'TV-55U2', 'ทีวี 55 นิ้ว 4K QLED', 'Sangsung', 'ทีวี', 19990.00, 7, 36, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(3, 'TV-65S3', 'ทีวี 65 นิ้ว OLED', 'LGee', 'ทีวี', 38500.00, 4, 36, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(4, 'TV-40FHD', 'ทีวี 40 นิ้ว Full HD', 'Panaphonic', 'ทีวี', 7490.00, 15, 24, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(5, 'TV-504KU', 'ทีวี 50 นิ้ว 4K UHD', 'Toshiha', 'ทีวี', 12990.00, 10, 24, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(6, 'FR-250S', 'ตู้เย็น 2 ประตู 250L Inverter', 'Hitano', 'ตู้เย็น', 8990.00, 10, 36, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(7, 'FR-180S', 'ตู้เย็น 1 ประตู 180L', 'Toshiha', 'ตู้เย็น', 6490.00, 8, 24, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(8, 'FR-550X', 'ตู้เย็น 4 ประตู 550L Premium', 'Sangsung', 'ตู้เย็น', 28900.00, 3, 60, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(9, 'FR-300W', 'ตู้เย็น 2 ประตู 300L', 'Sharpix', 'ตู้เย็น', 9990.00, 14, 36, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(10, 'FR-100D', 'ตู้เย็นมินิบาร์ 3.5 คิว', 'Panaphonic', 'ตู้เย็น', 3590.00, 25, 12, NULL, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(11, 'AC-12000', 'แอร์ 12000 BTU Inverter', 'Daika', 'แอร์', 13990.00, 6, 60, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(12, 'AC-9000', 'แอร์ 9000 BTU Standard', 'LGee', 'แอร์', 8990.00, 11, 24, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(13, 'AC-18K', 'แอร์ 18000 BTU Inverter', 'Hitano', 'แอร์', 20500.00, 5, 60, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(14, 'WM-8KG', 'เครื่องซักผ้าฝาบน 8 กก.', 'Toshiha', 'เครื่องซักผ้า', 6990.00, 9, 24, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(15, 'WM-12F', 'เครื่องซักผ้าฝาหน้า 12 กก.', 'Sangsung', 'เครื่องซักผ้า', 18900.00, 7, 36, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(16, 'WM-7KG', 'เครื่องซักผ้า 2 ถัง 7 กก.', 'Sharpix', 'เครื่องซักผ้า', 4500.00, 20, 12, NULL, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(17, 'MW-23L', 'ไมโครเวฟ 23 ลิตร', 'Panaphonic', 'เครื่องครัว', 2490.00, 20, 12, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(18, 'IH-2000', 'เตาแม่เหล็กไฟฟ้า 2000W', 'Sharpix', 'เครื่องครัว', 1290.00, 25, 12, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(19, 'AR-5L', 'หม้อทอดไร้น้ำมัน 5 ลิตร', 'SmartCook', 'เครื่องครัว', 1790.00, 18, 12, 4, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(20, 'MC-180', 'หม้อหุงข้าวไฟฟ้า 1.8 ลิตร', 'Hitano', 'เครื่องครัว', 890.00, 30, 12, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(21, 'KF-12C', 'กาต้มน้ำไฟฟ้า 1.2 ลิตร', 'Panaphonic', 'เครื่องครัว', 590.00, 45, 12, 2, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(22, 'VA-1000', 'เครื่องดูดฝุ่น 1000W', 'Sangsung', 'เครื่องใช้ในบ้าน', 1590.00, 15, 12, 2, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(23, 'VA-CORD', 'เครื่องดูดฝุ่นไร้สาย Premium', 'Dysonix', 'เครื่องใช้ในบ้าน', 14900.00, 5, 24, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(24, 'FAN-16W', 'พัดลมตั้งพื้น 16 นิ้ว', 'Mitsubishi', 'เครื่องใช้ในบ้าน', 990.00, 50, 12, 3, '2025-10-03 03:31:03', '2025-10-03 03:31:03'),
(25, 'AIR-P30', 'เครื่องฟอกอากาศ', 'LGee', 'เครื่องใช้ในบ้าน', 7900.00, 13, 24, 5, '2025-10-03 03:31:03', '2025-10-03 03:31:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appliances`
--
ALTER TABLE `appliances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appliances`
--
ALTER TABLE `appliances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;