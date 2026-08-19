-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 18, 2026 at 10:44 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hostel_agency`
--

-- --------------------------------------------------------

--
-- Table structure for table `amenities`
--

CREATE TABLE `amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `icon` varchar(50) DEFAULT 'check-circle'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `amenities`
--

INSERT INTO `amenities` (`id`, `name`, `icon`) VALUES
(1, 'Wi-Fi', 'wifi'),
(2, 'Laundry', 'washing-machine'),
(3, 'Study Rooms', 'book'),
(4, '24/7 Security', 'shield'),
(5, 'Backup Power', 'bolt'),
(6, 'Water Supply', 'droplet'),
(7, 'Kitchen', 'utensils'),
(8, 'Parking', 'car'),
(9, 'CCTV', 'camera-video'),
(10, 'Common Room / TV Lounge', 'tv');

-- --------------------------------------------------------

--
-- Table structure for table `birthday_wishes`
--

CREATE TABLE `birthday_wishes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sent_by` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `room_type_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL DEFAULT '2026/2027',
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `payment_plan` enum('full','installment') NOT NULL DEFAULT 'full',
  `payment_status` enum('unpaid','partial','paid') NOT NULL DEFAULT 'unpaid',
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `hostel_id`, `room_type_id`, `academic_year`, `status`, `amount`, `payment_plan`, `payment_status`, `amount_paid`, `booked_at`) VALUES
(1, 2, 6, 13, '2026/2027', 'confirmed', '1900.00', 'full', 'paid', '1900.00', '2026-08-12 15:59:17');

-- --------------------------------------------------------

--
-- Table structure for table `feedback_messages`
--

CREATE TABLE `feedback_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `type` enum('feedback','question') NOT NULL DEFAULT 'feedback',
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','in_progress','resolved') NOT NULL DEFAULT 'new',
  `admin_reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `distance_to_campus_km` decimal(5,2) DEFAULT 0.00,
  `main_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`id`, `name`, `description`, `address`, `latitude`, `longitude`, `distance_to_campus_km`, `main_image`, `created_at`) VALUES
(1, 'Jerry\'s Hostel', 'A vibrant hostel community close to the main campus gate, popular for its lively social atmosphere and modern furnishing.', '12 Unity Road, Campus North', '5.6500000', '-0.1870000', '0.50', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRZ5J8YvSZItLSY6aKs8E_Qb7ZWNClcatTM9zK1qm9lZRnlJn0G&s', '2026-08-12 15:49:39'),
(2, 'Serene Heights', 'A quiet, study-focused hostel with dedicated reading rooms and reliable backup power, ideal for focused students.', '4 Serenity Ave, Campus East', '5.6520000', '-0.1830000', '1.20', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSmQCJey3odLsknf22Ptg1fksZTSiNOlolR1Vk4n2OUl2gX0_0&s', '2026-08-12 15:49:39'),
(3, 'Owusu\'s Hostel', 'Premium accommodation offering en-suite rooms, air conditioning and a rooftop common area with campus views.', '9 Golden Gate St, Campus West', '5.6480000', '-0.1900000', '0.80', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS-FtDVILSsg5zTT-cRxy_vUnrO9tHkcwghP_sXmVUA4XkG3__T&s', '2026-08-12 15:49:39'),
(4, 'Campus View Hostel', 'Affordable shared rooms with a friendly community feel and easy walking distance to lecture halls.', '21 View Street, Near Main Gate', '5.6510000', '-0.1850000', '0.30', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRDRRzoUFHGZOfmMWtJoTOgc_ovlIhnD78OhDdphGEMC_4n1xQ&s', '2026-08-12 15:49:39'),
(5, 'Fiifi\'s Hostel', 'Modern hostel with landscaped courtyards, laundry services, and a dedicated kitchen for residents.', 'Tanakrom', '5.6470000', '-0.1820000', '1.50', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTzh-VqLK9cb5T2GS3aY7mM15622yNRsL8Syapk_su9CJCsgMCobjMN98g&s', '2026-08-12 15:49:39'),
(6, 'Patrick\'s Hostel', 'Budget-friendly hostel by the riverside with scenic views, communal kitchen, and secure parking.', 'pipe-Ano', '5.6455000', '-0.1795000', '2.10', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTFU7oMdUaZGJv8H8_blybb6YzOv2fI9yOxwQZSX6hqV2lr6nwAIJzXUjM&s', '2026-08-12 15:49:39'),
(7, 'Foster\'s Hostel', 'Boutique-style hostel with tastefully furnished premium rooms and 24/7 concierge-style security.', '8 Heritage Lane, Campus North East', '5.6540000', '-0.1810000', '1.00', 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/650477683.webp?k=5304fe5a2679c4052bef4937d0d51aa992cd527ab70fb909cdcb8d068c52a7b9&o=', '2026-08-12 15:49:39');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_amenities`
--

CREATE TABLE `hostel_amenities` (
  `hostel_id` int(11) NOT NULL,
  `amenity_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_amenities`
--

INSERT INTO `hostel_amenities` (`hostel_id`, `amenity_id`) VALUES
(1, 1),
(1, 2),
(1, 4),
(1, 9),
(1, 10),
(2, 1),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(3, 1),
(3, 3),
(3, 4),
(3, 5),
(3, 8),
(3, 9),
(3, 10),
(4, 1),
(4, 2),
(4, 3),
(4, 6),
(4, 10),
(5, 1),
(5, 2),
(5, 3),
(5, 4),
(5, 6),
(5, 7),
(6, 1),
(6, 6),
(6, 7),
(6, 8),
(7, 1),
(7, 2),
(7, 3),
(7, 4),
(7, 5),
(7, 7),
(7, 9),
(7, 10);

-- --------------------------------------------------------

--
-- Table structure for table `hostel_photos`
--

CREATE TABLE `hostel_photos` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `photo_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_photos`
--

INSERT INTO `hostel_photos` (`id`, `hostel_id`, `photo_path`) VALUES
(4, 2, 'https://picsum.photos/seed/serene1/500/350'),
(5, 2, 'https://picsum.photos/seed/serene2/500/350'),
(17, 7, '/assets/uploads/hostels/7/0895091d60e83911.jpg'),
(18, 7, '/assets/uploads/hostels/7/5f88b3493a7433cf.jpg'),
(19, 6, '/assets/uploads/hostels/6/6cc1b377c3d6eb80.jpg'),
(20, 6, '/assets/uploads/hostels/6/d631ffcae6b45cdf.jpg'),
(21, 5, '/assets/uploads/hostels/5/a29b6cfd76189958.jpg'),
(22, 5, '/assets/uploads/hostels/5/41276f532bfd6e30.jpg'),
(23, 4, '/assets/uploads/hostels/4/36a969b9c13a52c4.jpg'),
(24, 4, '/assets/uploads/hostels/4/d1f2a93c8c5e472b.jpg'),
(25, 3, '/assets/uploads/hostels/3/f76072439252cfbc.jpg'),
(26, 3, '/assets/uploads/hostels/3/43f3bff401a71f88.jpg'),
(27, 1, '/assets/uploads/hostels/1/2804105657b9c426.jpg'),
(28, 1, '/assets/uploads/hostels/1/1e344974fd548abe.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_videos`
--

CREATE TABLE `hostel_videos` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `video_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `installment_plans`
--

CREATE TABLE `installment_plans` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `installment_number` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_posts`
--

CREATE TABLE `news_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `published_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news_posts`
--

INSERT INTO `news_posts` (`id`, `title`, `body`, `image`, `is_published`, `published_at`, `created_by`) VALUES
(1, 'Hostel Applications Now Open for 2026/2027', 'Applications for the upcoming academic year are now open across all seven partner hostels. Early applicants get priority room selection, so browse and book before rooms fill up.', NULL, 1, '2026-08-12 15:49:40', NULL),
(2, 'New Payment Option: Pay in 3 Installments', 'You can now split your hostel fees into three manageable payments via Paystack — 40% to secure your room, then two follow-up payments over the semester. Look for the payment plan option when booking.', NULL, 1, '2026-08-12 15:49:40', NULL),
(3, 'Now Live: Order Food & Essentials for Delivery', 'You can now order food, snacks, and everyday essentials right from your dashboard, with secure delivery payments through Paystack. Check out the new Shop section!', NULL, 1, '2026-08-12 15:49:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `delivery_phone` varchar(30) NOT NULL,
  `delivery_notes` varchar(255) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 15.00,
  `total_amount` decimal(10,2) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `status` enum('pending','processing','out_for_delivery','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `delivery_address`, `delivery_phone`, `delivery_notes`, `subtotal`, `delivery_fee`, `total_amount`, `amount_paid`, `payment_status`, `status`, `created_at`) VALUES
(1, 2, 'AMANFUL 324', '0550040399', 'RED GATE', '30.00', '15.00', '45.00', '45.00', 'paid', 'processing', '2026-08-15 09:08:52'),
(2, 2, 'room1', '0500000000', 'gate1', '103.00', '15.00', '118.00', '118.00', 'paid', 'processing', '2026-08-18 08:37:17'),
(3, 2, 'town 12', '0550040299', 'gate 213', '32.00', '15.00', '47.00', '47.00', 'paid', 'processing', '2026-08-18 20:42:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `unit_price`, `quantity`, `line_total`) VALUES
(1, 1, 9, 'Fried Rice and Chicken', '30.00', 1, '30.00'),
(2, 2, 5, 'A4 Printing Paper (Ream)', '28.00', 1, '28.00'),
(3, 2, 6, 'Reading Lamp', '45.00', 1, '45.00'),
(4, 2, 9, 'Fried Rice and Chicken', '30.00', 1, '30.00'),
(5, 3, 7, 'Fried Rice & Sausage', '32.00', 1, '32.00');

-- --------------------------------------------------------

--
-- Table structure for table `order_payments`
--

CREATE TABLE `order_payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `channel` varchar(40) DEFAULT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `gateway_response` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_payments`
--

INSERT INTO `order_payments` (`id`, `order_id`, `user_id`, `amount`, `reference`, `channel`, `status`, `gateway_response`, `created_at`, `paid_at`) VALUES
(1, 1, 2, '45.00', 'HAOR-20260815110859-55b7e250', 'mobile_money', 'success', 'Approved', '2026-08-15 09:08:59', '2026-08-15 09:09:27'),
(2, 2, 2, '118.00', 'HAOR-20260818103722-1990cea7', 'mobile_money', 'success', 'Approved', '2026-08-18 08:37:22', '2026-08-18 08:37:53'),
(3, 3, 2, '47.00', 'HAOR-20260818224245-e282ed5f', 'mobile_money', 'success', 'Approved', '2026-08-18 20:42:45', '2026-08-18 20:43:08');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `installment_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `channel` varchar(40) DEFAULT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `gateway_response` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `installment_id`, `user_id`, `amount`, `reference`, `channel`, `status`, `gateway_response`, `created_at`, `paid_at`) VALUES
(1, 1, NULL, 2, '1900.00', 'HABK-20260812175923-b692072f', 'mobile_money', 'success', 'Approved', '2026-08-12 15:59:23', '2026-08-12 15:59:46');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('food','item','other') NOT NULL DEFAULT 'item',
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `price`, `image`, `stock_quantity`, `is_available`, `created_at`) VALUES
(1, 'Jollof Rice & Chicken', 'A generous portion of jollof rice served with grilled chicken and coleslaw.', 'food', '35.00', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRljnQWqXg1gkeV_ZZMB8hK60nwb-l5-U27sN4Za3O9Hz6Hv1IAoZTkd4c&s', 50, 1, '2026-08-12 15:49:40'),
(2, 'Waakye Special', 'Waakye with fish, gari, spaghetti, and shito — a campus favourite.', 'food', '30.00', '/assets/uploads/products/3b5e2c32a9cd6320.jpeg', 40, 1, '2026-08-12 15:49:40'),
(3, 'Bottled Water (Pack of 12)', 'A pack of 12 x 500ml bottled water.', 'item', '24.00', '/assets/uploads/products/292b401d069b805f.jpg', 100, 1, '2026-08-12 15:49:40'),
(4, 'Instant Noodles (Carton)', 'A carton of 40 packs of instant noodles.', 'item', '60.00', '/assets/uploads/products/5fc5e2a73d67bf18.webp', 30, 1, '2026-08-12 15:49:40'),
(5, 'A4 Printing Paper (Ream)', '500 sheets of A4 printing paper for assignments and projects.', 'item', '28.00', '/assets/uploads/products/cd0583dee9403aca.jpg', 60, 1, '2026-08-12 15:49:40'),
(6, 'Reading Lamp', 'A rechargeable LED reading lamp — perfect for late-night study sessions.', 'other', '45.00', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGbfMJUe2OJLApH75VnY3lfVZbPrBirp8ezQNg1HS9oQEodXs&s', 20, 1, '2026-08-12 15:49:40'),
(7, 'Fried Rice & Sausage', 'Fried rice served with grilled sausage and fresh salad.', 'food', '32.00', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSpbDW3nO8JXuJwdr2j7z_iPyAKbMhNX-tXz9M8GaYmnRQcFaIy&s', 45, 1, '2026-08-12 15:49:40'),
(8, 'Toiletries Bundle', 'Soap, toothpaste, toothbrush, and sponge — a handy essentials bundle.', 'item', '38.00', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS0EfWD8XlUB7tDyxjtjB2b-u5sjWidwhR4eOUFzZUEHD2MYNQ&s', 35, 1, '2026-08-12 15:49:40'),
(9, 'Fried Rice and Chicken', 'Best fried rice in town', 'food', '30.00', '/assets/uploads/products/d1f96000b98a2c37.jpg', 20, 1, '2026-08-15 09:06:04'),
(10, 'Macbook 23', 'I have a MacBook Pro 2023, late 2023, and I read that I could upgrade the HDD to an SSD to make it run much faster', 'item', '6500.00', '/assets/uploads/products/f7a7f0fceca8bf8a.jpg', 20, 1, '2026-08-18 20:36:34'),
(11, 'PS 5 controller', 'Shop PlayStation®5 accessories, including the DualSense PS5 controller and Pulse 3D Wireless Headset. Buy now directly from PlayStation.', 'item', '1000.00', '/assets/uploads/products/5451928cdf68e2e0.jpg', 50, 1, '2026-08-18 20:38:48'),
(12, 'Bluetooth speakers', 'Whether you\'re at home or on the go, you can be sure Marshall Bluetooth speakers will provide a mighty sound. Discover our range of speakers here.', 'item', '300.00', '/assets/uploads/products/b0bfa6d11f699594.jpg', 20, 1, '2026-08-18 20:40:38');

-- --------------------------------------------------------

--
-- Table structure for table `room_types`
--

CREATE TABLE `room_types` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `type` enum('single','shared','premium') NOT NULL,
  `price_per_year` decimal(10,2) NOT NULL,
  `size_sqm` decimal(6,2) DEFAULT NULL,
  `furnishing` varchar(255) DEFAULT NULL,
  `total_rooms` int(11) NOT NULL DEFAULT 10,
  `booked_rooms` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_types`
--

INSERT INTO `room_types` (`id`, `hostel_id`, `type`, `price_per_year`, `size_sqm`, `furnishing`, `total_rooms`, `booked_rooms`) VALUES
(1, 1, 'single', '4500.00', '12.00', 'Bed, wardrobe, desk, chair', 10, 3),
(2, 1, 'shared', '2800.00', '18.00', 'Bunk beds, wardrobes, study desks', 20, 12),
(3, 1, 'premium', '6200.00', '16.00', 'AC, en-suite bathroom, wardrobe, desk', 8, 2),
(4, 2, 'single', '4200.00', '11.00', 'Bed, wardrobe, desk', 12, 5),
(5, 2, 'shared', '2600.00', '17.00', 'Bunk beds, wardrobes', 25, 20),
(6, 3, 'single', '5200.00', '14.00', 'Bed, wardrobe, desk, mini-fridge', 10, 4),
(7, 3, 'premium', '7800.00', '20.00', 'AC, en-suite, smart TV, wardrobe', 12, 6),
(8, 4, 'shared', '2200.00', '16.00', 'Bunk beds, wardrobes, study desks', 30, 18),
(9, 4, 'single', '3800.00', '10.00', 'Bed, wardrobe, desk', 10, 7),
(10, 5, 'single', '4000.00', '12.00', 'Bed, wardrobe, desk, bookshelf', 15, 6),
(11, 5, 'shared', '2500.00', '18.00', 'Bunk beds, wardrobes', 20, 9),
(12, 5, 'premium', '6000.00', '18.00', 'AC, en-suite, wardrobe, desk', 6, 1),
(13, 6, 'shared', '1900.00', '16.00', 'Bunk beds, wardrobes', 25, 11),
(14, 6, 'single', '3200.00', '10.00', 'Bed, wardrobe, desk', 12, 3),
(15, 7, 'premium', '8500.00', '22.00', 'AC, en-suite, smart TV, mini-fridge, sofa', 10, 4),
(16, 7, 'single', '5500.00', '13.00', 'Bed, wardrobe, desk, mini-fridge', 8, 2);

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hostel_id` int(11) DEFAULT NULL,
  `display_name` varchar(120) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `message` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `hostel_id`, `display_name`, `rating`, `message`, `is_approved`, `created_at`) VALUES
(1, NULL, 1, 'Ama K.', 5, 'Unity Hostel made my first year so much easier. Close to campus, great community, and the admin team responded to every request fast.', 1, '2026-08-12 15:49:40'),
(2, NULL, 3, 'Kwame O.', 5, 'Golden Gate Lodge is worth every cedi. The rooftop common area is my favourite place to study in the evenings.', 1, '2026-08-12 15:49:40'),
(3, NULL, 4, 'Efua B.', 4, 'Affordable and convenient — Campus View is a 5 minute walk to my first lecture. Wifi could be faster but overall a great stay.', 1, '2026-08-12 15:49:40'),
(4, NULL, 2, 'Yaw A.', 5, 'Serene Heights lives up to its name. Quiet, clean, and the backup power meant I never missed an online class during outages.', 1, '2026-08-12 15:49:40'),
(5, NULL, NULL, 'Abena D.', 5, 'Booking through Hostel Agency was so much easier than going hostel to hostel myself. The comparison tool saved me hours.', 1, '2026-08-12 15:49:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('student','admin') NOT NULL DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `student_id`, `date_of_birth`, `profile_picture`, `role`, `created_at`) VALUES
(2, 'Foster Tetteh', 'fosterdza19@gmail.com', '0550040399', '$2y$10$9Wflm1.OD/2/qRIXCoxjBurF3dqr7FWyf0j/4opFA4joQWv4XXn8e', '', '2000-11-06', '/assets/uploads/profiles/user2_7c4b026bff71.jpg', 'student', '2026-08-12 15:57:46'),
(4, 'System Administrator', 'admin@hostelagency.com', NULL, '$2b$10$FH8w1ZWXb9xVG2Rb5da7seblbOF/i1x8O/SzIKWpDMdhPjWN6BpNO', NULL, NULL, NULL, 'admin', '2026-08-14 20:06:26');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `amenities`
--
ALTER TABLE `amenities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `birthday_wishes`
--
ALTER TABLE `birthday_wishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `sent_by` (`sent_by`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `room_type_id` (`room_type_id`);

--
-- Indexes for table `feedback_messages`
--
ALTER TABLE `feedback_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostel_amenities`
--
ALTER TABLE `hostel_amenities`
  ADD PRIMARY KEY (`hostel_id`,`amenity_id`),
  ADD KEY `amenity_id` (`amenity_id`);

--
-- Indexes for table `hostel_photos`
--
ALTER TABLE `hostel_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `hostel_videos`
--
ALTER TABLE `hostel_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `installment_plans`
--
ALTER TABLE `installment_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `news_posts`
--
ALTER TABLE `news_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `installment_id` (`installment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_types`
--
ALTER TABLE `room_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`);

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
-- AUTO_INCREMENT for table `amenities`
--
ALTER TABLE `amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `birthday_wishes`
--
ALTER TABLE `birthday_wishes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback_messages`
--
ALTER TABLE `feedback_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `hostel_photos`
--
ALTER TABLE `hostel_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `hostel_videos`
--
ALTER TABLE `hostel_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `installment_plans`
--
ALTER TABLE `installment_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news_posts`
--
ALTER TABLE `news_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_payments`
--
ALTER TABLE `order_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `room_types`
--
ALTER TABLE `room_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `birthday_wishes`
--
ALTER TABLE `birthday_wishes`
  ADD CONSTRAINT `birthday_wishes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `birthday_wishes_ibfk_2` FOREIGN KEY (`sent_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback_messages`
--
ALTER TABLE `feedback_messages`
  ADD CONSTRAINT `feedback_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `hostel_amenities`
--
ALTER TABLE `hostel_amenities`
  ADD CONSTRAINT `hostel_amenities_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hostel_amenities_ibfk_2` FOREIGN KEY (`amenity_id`) REFERENCES `amenities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_photos`
--
ALTER TABLE `hostel_photos`
  ADD CONSTRAINT `hostel_photos_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hostel_videos`
--
ALTER TABLE `hostel_videos`
  ADD CONSTRAINT `hostel_videos_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `installment_plans`
--
ALTER TABLE `installment_plans`
  ADD CONSTRAINT `installment_plans_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_posts`
--
ALTER TABLE `news_posts`
  ADD CONSTRAINT `news_posts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_payments`
--
ALTER TABLE `order_payments`
  ADD CONSTRAINT `order_payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`installment_id`) REFERENCES `installment_plans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_types`
--
ALTER TABLE `room_types`
  ADD CONSTRAINT `room_types_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `testimonials_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
