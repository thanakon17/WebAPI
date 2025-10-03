
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

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


INSERT INTO `appliances` (`id`, `sku`, `name`, `brand`, `category`, `price`, `stock`, `warranty_months`, `energy_rating`, `created_at`, `updated_at`) VALUES
(1, 'TV-32A1', 'ทีวี 32 นิ้ว HD', 'Panaphonic', 'ทีวี', 4990.00, 12, 24, 3, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(2, 'TV-55U2', 'ทีวี 55 นิ้ว 4K', 'Sangsung', 'ทีวี', 16990.00, 7, 24, 5, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(3, 'FR-250S', 'ตู้เย็น 2 ประตู 250L', 'Hitano', 'ตู้เย็น', 8990.00, 10, 36, 5, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(4, 'AC-12000', 'แอร์ 12000 BTU อินเวอร์เตอร์', 'Daika', 'แอร์', 13990.00, 6, 60, 5, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(5, 'WM-8KG', 'เครื่องซักผ้า 8 กก.', 'Toshiha', 'เครื่องซักผ้า', 6990.00, 9, 24, 4, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(6, 'MW-23L', 'ไมโครเวฟ 23 ลิตร', 'Panaphonic', 'ไมโครเวฟ', 2490.00, 20, 12, 3, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(7, 'VA-1000', 'เครื่องดูดฝุ่น 1000W', 'Sangsung', 'เครื่องใช้ในบ้าน', 1590.00, 15, 12, 2, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(8, 'IH-2000', 'เตาแม่เหล็กไฟฟ้า 2000W', 'Sharpix', 'เครื่องครัว', 1290.00, 25, 12, 3, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(9, 'AR-5L', 'หม้อทอดไร้น้ำมัน 5 ลิตร', 'SmartCook', 'เครื่องครัว', 1790.00, 18, 12, 4, '2025-10-03 02:55:26', '2025-10-03 02:55:26'),
(10, 'FR-180S', 'ตู้เย็น 1 ประตู 180L', 'Toshiha', 'ตู้เย็น', 6490.00, 8, 24, 4, '2025-10-03 02:55:26', '2025-10-03 02:55:26');

ALTER TABLE `appliances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

ALTER TABLE `appliances`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;
