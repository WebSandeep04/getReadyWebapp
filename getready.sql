-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2026 at 02:05 PM
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
-- Database: `getready`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_panel_users`
--

CREATE TABLE `admin_panel_users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_panel_users`
--

INSERT INTO `admin_panel_users` (`id`, `name`, `username`, `email`, `password`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', 'admin@getready.com', '$2y$12$eYqrrNdlLc0Sf2DSh9JdF.zrwZCJ8OyPoBblnz0.jHyjueOfPdCCK', 1, '2026-05-01 22:35:39', '2026-05-01 22:35:39');

-- --------------------------------------------------------

--
-- Table structure for table `admin_user_permissions`
--

CREATE TABLE `admin_user_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `admin_panel_user_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_user_permissions`
--

INSERT INTO `admin_user_permissions` (`id`, `admin_panel_user_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(3, 1, 3, NULL, NULL),
(4, 1, 4, NULL, NULL),
(5, 1, 5, NULL, NULL),
(6, 1, 6, NULL, NULL),
(7, 1, 7, NULL, NULL),
(8, 1, 8, NULL, NULL),
(9, 1, 9, NULL, NULL),
(10, 1, 10, NULL, NULL),
(11, 1, 11, NULL, NULL),
(12, 1, 12, NULL, NULL),
(13, 1, 13, NULL, NULL),
(14, 1, 14, NULL, NULL),
(15, 1, 15, NULL, NULL),
(16, 1, 16, NULL, NULL),
(17, 1, 17, NULL, NULL),
(18, 1, 18, NULL, NULL),
(19, 1, 19, NULL, NULL),
(20, 1, 20, NULL, NULL),
(21, 1, 21, NULL, NULL),
(22, 1, 22, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `availability_blocks`
--

CREATE TABLE `availability_blocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `type` enum('available','blocked') NOT NULL DEFAULT 'blocked',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `availability_blocks`
--

INSERT INTO `availability_blocks` (`id`, `cloth_id`, `start_date`, `end_date`, `type`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-05-14', '2026-05-30', 'available', NULL, '2026-05-02 02:50:24', '2026-05-02 02:50:24'),
(12, 3, '2026-05-05', '2026-05-08', 'available', NULL, '2026-05-06 00:41:47', '2026-05-06 00:41:47'),
(13, 4, '2026-05-05', '2026-05-08', 'available', NULL, '2026-05-06 00:42:04', '2026-05-06 00:42:04'),
(14, 5, '2026-05-05', '2026-05-08', 'available', NULL, '2026-05-06 00:42:38', '2026-05-06 00:42:38'),
(15, 6, '2026-05-06', '2026-05-09', 'available', NULL, '2026-05-06 00:42:53', '2026-05-06 00:42:53'),
(16, 7, '2026-05-08', '2026-05-11', 'available', NULL, '2026-05-06 00:43:09', '2026-05-06 00:43:09'),
(17, 9, '2026-05-09', '2026-05-12', 'blocked', 'Rented (Order #1)', '2026-05-06 00:43:47', '2026-05-06 00:43:47'),
(18, 9, '2026-05-08', '2026-05-08', 'blocked', 'Delivery buffer (Order #1)', '2026-05-06 00:43:47', '2026-05-06 00:43:47'),
(19, 9, '2026-05-13', '2026-05-14', 'blocked', 'Pre-pickup from owner buffer (Order #1)', '2026-05-06 00:43:47', '2026-05-06 00:43:47'),
(21, 2, '2026-05-05', '2026-05-08', 'available', NULL, '2026-05-07 00:45:34', '2026-05-07 00:45:34'),
(22, 10, '2026-05-16', '2026-05-19', 'blocked', 'Rented (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04'),
(23, 10, '2026-05-15', '2026-05-15', 'blocked', 'Delivery buffer (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04'),
(24, 10, '2026-05-20', '2026-05-21', 'blocked', 'Pre-pickup from owner buffer (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04'),
(25, 9, '2026-05-23', '2026-06-06', 'blocked', 'Rented (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04'),
(26, 9, '2026-05-22', '2026-05-22', 'blocked', 'Delivery buffer (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04'),
(27, 9, '2026-06-07', '2026-06-08', 'blocked', 'Pre-pickup from owner buffer (Order #3)', '2026-05-07 00:48:04', '2026-05-07 00:48:04');

-- --------------------------------------------------------

--
-- Table structure for table `body_type_fits`
--

CREATE TABLE `body_type_fits` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `body_type_fits`
--

INSERT INTO `body_type_fits` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Regular', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(2, 'Slim', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(3, 'Loose', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(4, 'Oversized', '2026-05-01 22:35:44', '2026-05-01 22:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `bottom_types`
--

CREATE TABLE `bottom_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bottom_types`
--

INSERT INTO `bottom_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Straight', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(2, 'Skinny', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(3, 'Wide Leg', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(4, 'Palazzo', '2026-05-01 22:35:44', '2026-05-01 22:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'H&M', 'brands/YxbuzJOKyHJ8azkQJDUhIEAkikdUJV67DSjwM1xv.png', '2026-05-01 22:35:44', '2026-05-02 06:03:45'),
(2, 'Zara', 'brands/9Ruf0Zez4tKUcVGyHHGlBwZeB6JAXJmygDLY4BgI.png', '2026-05-01 22:35:44', '2026-05-02 06:04:19'),
(3, 'savana', 'brands/cXUu4zDYKaJUFSVYpjqHI9vOYIGwRwaqJwqrC8Zm.jpg', '2026-05-01 22:35:44', '2026-05-02 06:08:18'),
(4, 'shein', 'brands/UKV9QyCLpbfO2JmRzJNJfSd4ZM5gsNWpzciXVkUc.png', '2026-05-01 22:35:44', '2026-05-05 05:23:31'),
(5, 'New me', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(6, 'Calvin klein', 'brands/kLPC4rWdwVQlYDgzq9BJmePcLT56dbl5obMFCuRH.png', '2026-05-01 22:35:44', '2026-05-05 05:20:37'),
(7, 'Forever 21', 'brands/43j2uN1xAVWPSuZptU23dItNU57GowlmXYiWvAVz.png', '2026-05-01 22:35:44', '2026-05-05 05:24:23'),
(8, 'Mango', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(9, 'adidas', 'brands/zGTUFcbRAYpb5qor5Hw4DYvWBJNKFPxN7ShbKfWT.png', '2026-05-01 22:35:44', '2026-05-05 05:25:30'),
(10, 'Nike', 'brands/BfXDiPSxQUPWHvhrBk8ap9PN8gw0jYLJYV9uz4BP.png', '2026-05-01 22:35:44', '2026-05-05 05:22:20'),
(11, 'Puma', 'brands/itZhaxEFUNQkvWACfEAxna9VxSxjkdh8VOlp2q7b.png', '2026-05-01 22:35:44', '2026-05-05 05:22:53'),
(12, 'plum', 'brands/547EQW5DWhXQf425k7Y30A4J5FhP2WaP2HW90o8L.jpg', '2026-05-01 22:35:44', '2026-05-05 05:26:22'),
(13, 'Dot & Key', 'brands/9wf2nZRjPMoZrqKgQRHLI0rLeVHMLmSapdFGJnuQ.png', '2026-05-01 22:35:44', '2026-05-05 05:27:06'),
(14, 'Dior', 'brands/zYl85SIPSqurfFs90aBMOQ1v8ed6rGf9rbFoP3DW.png', '2026-05-01 22:35:44', '2026-05-05 05:27:59'),
(15, 'MAC', 'brands/eY9v8tLrz9jBkCTjjYBwUwlPETgzkRNL2TCVVCQg.png', '2026-05-01 22:35:44', '2026-05-05 05:28:42'),
(16, 'Swiss Beauty', 'brands/vvXWrigi88IT7wueJGTcnEacO4hUdyhQYFVzXlj1.jpg', '2026-05-01 22:35:44', '2026-05-05 05:30:44'),
(17, 'Forever Fashion', 'brands/FypuCLZJoDENodLJit43ndGG3WLI9ItQwBXiQ0Oh.png', '2026-05-01 22:35:44', '2026-05-05 05:31:47'),
(18, 'Biba', 'brands/yQdfBddFHbXuopTTauie24uZq7Vp7wG21GNfvE6u.png', '2026-05-01 22:35:44', '2026-05-05 05:32:24'),
(19, 'Zudio', 'brands/aehi3Exk0onaWxvwYuOFLcgD1mNff1SoDVQ9HeeM.png', '2026-05-01 22:35:44', '2026-05-05 05:32:58'),
(20, 'Little Box', 'brands/Trck7UWJlJ3UFMNGPRWa1TEpciPwSfIuZT10Imyy.png', '2026-05-01 22:35:44', '2026-05-05 05:34:08'),
(21, 'Pantaloons', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(22, 'Westside', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(23, 'Taavi', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(24, 'Glitchez', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(25, 'Terractive', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(26, 'NUON', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(27, 'Primark Cares', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(28, 'DORI', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(29, 'TERRANOVA', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(30, 'Vero Moda', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(31, 'land\'s end', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(32, 'Lifestyle', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(33, 'Raymond', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(34, 'LYRA', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(35, 'DJ & C', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(36, 'Sqew', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(37, 'Levi\'s', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(38, 'Jenniffer', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(39, 'AKS', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(40, 'wardrobe', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(41, 'plusS', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(42, 'Asybuy', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(43, 'Max', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(44, '4WRD', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(45, 'miss twenty', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(46, 'RIO', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(47, 'trigya', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(48, 'opaque.clip', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(49, 'URBANIC', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(50, 'UK 7', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(51, 'sharman', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(52, 'Chanderi', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(53, 'Reegan', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(54, 'Mengghong ling', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(55, 'Roadster', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(56, 'love 4 label', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(57, 'berabond', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(58, 'Tokyo Talkies', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(59, 'indigo spao', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(60, 'WISHFUL BY W', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(61, 'Bitterlime', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(62, 'Amayra', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(63, 'Marks & Spencer', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(64, 'ELISIA', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(65, 'TALLY WEiJL', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(66, 'bape', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(67, 'Lakers', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(68, 'FILA', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(69, 'BLACKBERRYS', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(70, 'graf', NULL, '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(71, 'Analogue', NULL, '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-login_otp_1234567890', 's:6:\"910871\";', 1778311234),
('laravel-cache-login_otp_9812345678', 's:6:\"512509\";', 1777960869),
('laravel-cache-signup_verified_8948914407', 's:32:\"hC3eMtNWsXk1hupH0OVP0RILeD2hcSfB\";', 1778060019);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `purchase_type` enum('rent','buy') NOT NULL DEFAULT 'rent',
  `rental_start_date` date DEFAULT NULL,
  `rental_end_date` date DEFAULT NULL,
  `total_rental_cost` decimal(10,2) DEFAULT NULL,
  `total_selling_price` decimal(10,2) DEFAULT NULL,
  `rental_days` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `user_id`, `cloth_id`, `quantity`, `purchase_type`, `rental_start_date`, `rental_end_date`, `total_rental_cost`, `total_selling_price`, `rental_days`, `created_at`, `updated_at`) VALUES
(3, 5, 1, 1, 'buy', NULL, NULL, NULL, 4531.20, NULL, '2026-05-06 01:13:28', '2026-05-06 01:13:28'),
(4, 5, 9, 1, 'rent', '2026-05-22', '2026-05-25', 247.20, NULL, 4, '2026-05-06 01:47:54', '2026-05-06 02:50:20'),
(5, 5, 6, 1, 'rent', '2026-05-06', '2026-05-09', 741.60, NULL, 4, '2026-05-06 02:29:15', '2026-05-06 02:50:25');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Wedding Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(2, 'Festive Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(3, 'Formal Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(4, 'Ethnic Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(5, 'Traditional Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(6, 'Pre-Wedding Shoot Outfits', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(7, 'Indo-Western', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(8, 'Western Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(9, 'Premium Wear', '2026-05-01 22:35:43', '2026-05-01 22:35:43');

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `state_id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Mumbai', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(2, 1, 'Pune', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(3, 2, 'Ahmedabad', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(4, 2, 'Surat', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(5, 3, 'Bangalore', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(6, 4, 'New Delhi', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(7, 5, 'Jaipur', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `clothes`
--

CREATE TABLE `clothes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(2000) NOT NULL DEFAULT 'No description',
  `mrp` decimal(10,2) DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `gender` enum('Boy','Girl','Men','Women') NOT NULL,
  `brand_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fabric_id` bigint(20) UNSIGNED DEFAULT NULL,
  `color_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bottom_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `chest_bust` varchar(255) DEFAULT NULL,
  `waist` varchar(255) DEFAULT NULL,
  `length` varchar(255) DEFAULT NULL,
  `shoulder` varchar(255) DEFAULT NULL,
  `sleeve_length` varchar(255) DEFAULT NULL,
  `measurement_unit` varchar(255) NOT NULL DEFAULT 'inch',
  `fit_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `condition_id` bigint(20) UNSIGNED DEFAULT NULL,
  `defects` text DEFAULT NULL,
  `is_cleaned` tinyint(1) NOT NULL DEFAULT 0,
  `rent_price` decimal(10,2) NOT NULL,
  `security_deposit` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `sku` int(11) NOT NULL DEFAULT 1,
  `is_purchased` tinyint(1) NOT NULL DEFAULT 1,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `is_approved` tinyint(4) DEFAULT NULL,
  `resubmission_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `size_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clothes`
--

INSERT INTO `clothes` (`id`, `user_id`, `title`, `description`, `mrp`, `category_id`, `gender`, `brand_id`, `fabric_id`, `color_id`, `bottom_type_id`, `chest_bust`, `waist`, `length`, `shoulder`, `sleeve_length`, `measurement_unit`, `fit_type_id`, `condition_id`, `defects`, `is_cleaned`, `rent_price`, `security_deposit`, `selling_price`, `sku`, `is_purchased`, `is_available`, `is_approved`, `resubmission_count`, `created_at`, `updated_at`, `size_id`) VALUES
(1, 3, 'Test 01', 'test', 3500.00, 4, 'Girl', 44, 3, 25, NULL, '56', '2', '5', '2', '3', 'inch', 3, 3, 'Test', 0, 700.00, 700.00, 3200.00, 1, 1, 1, 1, 0, '2026-05-02 02:50:24', '2026-05-02 02:51:01', 4),
(2, 4, 'Jeans', 'Nothing', 1200.00, 3, 'Girl', 6, 21, 10, NULL, '12', '12', '12', '12', '12', 'cm', 4, 5, 'No', 0, 200.00, 200.00, 1000.00, 5, 0, 1, NULL, 2, '2026-05-05 01:45:09', '2026-05-07 00:45:34', 3),
(3, 4, 'Floral Printed Fit & Flare Midi Ethnic Dresses', 'Having a good Quality product', 1400.00, 5, 'Women', 4, 15, 20, NULL, '23`', '36', '40', '12', '9.45', 'inch', 1, 1, 'No Defects', 0, 1.00, 1.00, 1200.00, 0, 1, 0, 1, 1, '2026-05-05 02:06:30', '2026-05-07 00:48:05', 4),
(4, 4, 'Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers', 'Maroon Printed Kurta with Trousers', 1300.00, 4, 'Men', 1, 6, 1, NULL, '23`', '12', '40', '12', '9.45', 'inch', 2, 1, 'No defects', 0, 260.00, 260.00, 1000.00, 3, 1, 1, 1, 1, '2026-05-05 02:39:23', '2026-05-06 00:45:15', 3),
(5, 4, 'Black Sequinned Embellished Fusion Saree', 'Black saree\r\nSequinned Embellished Fusion saree with border\r\nHas embellished detail\r\nThe saree comes with an unstitched blouse piece', 1258.00, 1, 'Girl', 32, 17, 14, NULL, '23`', '36', '40', '12', '9.45', 'inch', 1, 4, 'No Defects', 0, 250.00, 250.00, 1000.00, 3, 1, 1, 1, 1, '2026-05-05 02:48:04', '2026-05-06 00:45:18', 6),
(6, 4, 'Men Self Design V-Neck Cotton T-shirt+', 'White t-shirt for men\r\nSelf design\r\nRegular length\r\nV-neck\r\nLong sleeves\r\nKnitted\r\nZip closure', 3000.00, 8, 'Men', 6, 3, 9, NULL, '12', '36', '12', '12', '9.45', 'cm', 3, 1, 'No Defects', 0, 600.00, 600.00, 2000.00, 4, 0, 1, 1, 1, '2026-05-05 03:55:31', '2026-05-06 00:45:20', 3),
(7, 4, 'Puff Sleeve Net A-Line Dress', 'White Solid A-Line dresses\r\nSquare Neck\r\nLong, Puff Sleeve\r\nKnee Length length in Straight hem\r\nNet fabric\r\nZip closure', 700.00, 8, 'Girl', 68, 7, 34, NULL, '12', '12', '12', '12', '12', 'inch', 3, 4, 'No Defect', 0, 140.00, 140.00, 661.00, 6, 1, 0, 1, 1, '2026-05-05 04:00:27', '2026-05-06 00:45:24', 2),
(8, 4, 'Boys Skinny Fit Jeans', 'Jeans in washed denim with a skinny fit through the hip, thigh and leg. Adjustable, elasticated waist, a fake fly with a press-stud, fake front pockets and real back pockets.', 800.00, 8, 'Boy', 19, 5, 6, NULL, '12', '12', '12', '12', '12', 'inch', 3, 2, 'No Defect', 0, 120.00, 120.00, 720.00, 4, 1, 1, 1, 1, '2026-05-05 04:14:26', '2026-05-06 00:45:29', 1),
(9, 4, 'Floral Print Fit & Flare Maxi Dress', 'Blue floral print fit & flare dress\r\nShirt collar\r\nShort, regular sleeves\r\nGathered or pleated detail\r\nMaxi length in flared hem\r\nCotton fabric', 1000.00, 3, 'Women', 1, 8, 23, NULL, '23`', '36', '40', '12', '9.45', 'inch', 2, 3, 'No Defect', 0, 200.00, 200.00, 900.00, 6, 0, 1, 1, 1, '2026-05-05 04:42:04', '2026-05-06 00:45:32', 5),
(10, 4, 'Women Checked Jumpsuit with Tie-Up', 'Good', 700.00, 9, 'Women', 18, 2, 14, NULL, '12', '12', '12', '12', '12', 'inch', 4, 3, 'No defect', 0, 120.00, 120.00, 600.00, 7, 1, 1, 1, 1, '2026-05-05 05:12:29', '2026-05-06 00:45:35', 4);

-- --------------------------------------------------------

--
-- Table structure for table `cloth_images`
--

CREATE TABLE `cloth_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cloth_images`
--

INSERT INTO `cloth_images` (`id`, `cloth_id`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'clothes/xRtgijMUM5Gt4iVOSCbt2TNdR70nmDLyY966wvQy.jpg', '2026-05-02 02:50:25', '2026-05-02 02:50:25'),
(2, 1, 'clothes/w9D5MVBNcYslrBzcLws37dKRPK7DR6gZEQv6aQq1.jpg', '2026-05-02 02:50:25', '2026-05-02 02:50:25'),
(3, 1, 'clothes/1HIhlew1g4RCjQaO2WYTgwfs5NvayMB2ytm40ySg.jpg', '2026-05-02 02:50:25', '2026-05-02 02:50:25'),
(4, 1, 'clothes/tfw27PTbGRFBxFiBxhCdzXdjcngyRErtSME3QBU4.jpg', '2026-05-02 02:50:25', '2026-05-02 02:50:25'),
(5, 2, 'clothes/2X4mfbzORyMnPvhWzlCbszDJ04u6ojYDbiMHz4al.png', '2026-05-05 01:45:09', '2026-05-05 01:45:09'),
(6, 2, 'clothes/OxXeoan1R4KplEsew81LRKbFh05gJByCOJTA9pNI.png', '2026-05-05 01:45:09', '2026-05-05 01:45:09'),
(7, 2, 'clothes/V1XsjEYcYyFJCieY2UenagT3w9vhm8Hc5yGWlT2T.jpg', '2026-05-05 01:45:09', '2026-05-05 01:45:09'),
(8, 3, 'clothes/mNjwO8mEDbNddrNY9MZTTZ82Q5sBJmsfcLPaQDAG.jpg', '2026-05-05 02:06:31', '2026-05-05 02:06:31'),
(9, 3, 'clothes/rPxMXiWltqJMkx48HdDgA1JpHIcp4ZEx564Q9o14.jpg', '2026-05-05 02:06:31', '2026-05-05 02:06:31'),
(10, 3, 'clothes/9n3eVpT3okSuft4r2T8r6nXA9RWQuKrwaqD4d5YP.jpg', '2026-05-05 02:06:31', '2026-05-05 02:06:31'),
(11, 4, 'clothes/FjILEY9s1Gv0Ixc1bET8KycYCNypWkVRg5U0FYXX.jpg', '2026-05-05 02:39:23', '2026-05-05 02:39:23'),
(12, 4, 'clothes/wxgWhP8j3wGKY1ngh0yR7Z2HUDkoopkbDvN9vMfH.jpg', '2026-05-05 02:39:23', '2026-05-05 02:39:23'),
(13, 4, 'clothes/9mMCghMU2B2zaW24Qvq3dwkq59iAl4JCALzYA1mX.jpg', '2026-05-05 02:39:23', '2026-05-05 02:39:23'),
(14, 5, 'clothes/f7BScDbdousSLYiopKml2Vl6cvGVwWSKCfqOfac9.jpg', '2026-05-05 02:48:04', '2026-05-05 02:48:04'),
(15, 5, 'clothes/RTzGFWEpIrpRVZB8a7ZZ7Qi7JjElBG4kvs7zi2Wa.jpg', '2026-05-05 02:48:04', '2026-05-05 02:48:04'),
(16, 5, 'clothes/0mXnqQbfjPmQwCOHJs1LmL9WGq1Q4DpwWTF0WMJm.jpg', '2026-05-05 02:48:04', '2026-05-05 02:48:04'),
(17, 6, 'clothes/Iv5lmh2d9ymBFODhKp632nACiG170JFWQEn5MHVU.jpg', '2026-05-05 03:55:32', '2026-05-05 03:55:32'),
(18, 6, 'clothes/q8uTZLWWLIHXEdL51ltZAvWsF3Q8Xd5yH3bV5lkZ.jpg', '2026-05-05 03:55:32', '2026-05-05 03:55:32'),
(19, 6, 'clothes/kdYhAiZRuIFnWvzY5wBX1vJHD8eK7a6nXRkSXoz6.jpg', '2026-05-05 03:55:32', '2026-05-05 03:55:32'),
(20, 7, 'clothes/6Z8prCRTMH5IQhEUR4k4GkMv76TFX2PMjtQzmnEu.jpg', '2026-05-05 04:00:27', '2026-05-05 04:00:27'),
(21, 7, 'clothes/TfmDb3Bg3xIdTxw37ykPc4aoU7vdaIh2FoBh7ge2.jpg', '2026-05-05 04:00:27', '2026-05-05 04:00:27'),
(22, 7, 'clothes/sGswAuwyaKii4Gq5scyQZGoSEDdypwwQhaZh3ZGn.jpg', '2026-05-05 04:00:27', '2026-05-05 04:00:27'),
(23, 7, 'clothes/XY0sGXGCNSGdAZIuwq0LJOhSmg1akgUDRXEkX1kj.jpg', '2026-05-05 04:00:27', '2026-05-05 04:00:27'),
(24, 8, 'clothes/NuVU1z1N0foH1ixuyz6iapB81ZIaXEz3A7y5KaQG.jpg', '2026-05-05 04:14:26', '2026-05-05 04:14:26'),
(25, 8, 'clothes/OTuS1CF6W9AlhTpi0rw6eAf3wQ0TmKZHz4TsEc7G.jpg', '2026-05-05 04:14:26', '2026-05-05 04:14:26'),
(26, 8, 'clothes/YlanUCHhnySsxoboAnlBqc3A2AIagmCc52NnSzzv.jpg', '2026-05-05 04:14:26', '2026-05-05 04:14:26'),
(27, 9, 'clothes/xDjinbfkrOZCPGoTuYbK2XyowbisLb3H9nUfYKOX.jpg', '2026-05-05 04:42:04', '2026-05-05 04:42:04'),
(28, 9, 'clothes/uDEaplCHNZ1w9avZbg0oIEUVQN8QbTbhNi5vGQj9.jpg', '2026-05-05 04:42:04', '2026-05-05 04:42:04'),
(29, 9, 'clothes/afGWcS4k9NTayzDbuRJ4BEHdNvwvNYkwbduIpQSx.jpg', '2026-05-05 04:42:04', '2026-05-05 04:42:04'),
(30, 10, 'clothes/ErgrMkwQoq9gHk5uBcDtkyVdrRcxivV8GHgcSRvF.jpg', '2026-05-05 05:12:29', '2026-05-05 05:12:29'),
(31, 10, 'clothes/1VljG24islM34HheCAhCxnWCOzgG1Iy5nOjijo78.jpg', '2026-05-05 05:12:29', '2026-05-05 05:12:29'),
(32, 10, 'clothes/y3hoPOMnWLRVick0nfaFpMxPlov1SDegQug6TnWw.jpg', '2026-05-05 05:12:29', '2026-05-05 05:12:29'),
(33, 10, 'clothes/ZeMYKqTExRrP3YNfN1H1yoKxOcNQjDBLfOftgKlb.jpg', '2026-05-05 05:12:29', '2026-05-05 05:12:29'),
(37, 3, 'clothes/7HHPl5aUN8AzutfZgmuhOMHHAo8WyB0AEiwclGJf.jpg', '2026-05-07 00:38:17', '2026-05-07 00:38:17');

-- --------------------------------------------------------

--
-- Table structure for table `cloth_measurements`
--

CREATE TABLE `cloth_measurements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `chest_cm` double DEFAULT NULL,
  `waist_cm` double DEFAULT NULL,
  `length_cm` double DEFAULT NULL,
  `shoulder_cm` double DEFAULT NULL,
  `sleeve_length_cm` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `colors`
--

CREATE TABLE `colors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `colors`
--

INSERT INTO `colors` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Maroon', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(2, 'Brown', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(3, 'Olive', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(4, 'Nude', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(5, 'Navy Blue', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(6, 'Blue', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(7, 'Pink', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(8, 'Purple', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(9, 'White', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(10, 'Green', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(11, 'Off White', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(12, 'Gold', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(13, 'Yellow', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(14, 'Black', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(15, 'Coral', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(16, 'Tan', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(17, 'Multi-color', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(18, 'Grey', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(19, 'Rose', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(20, 'Mustard', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(21, 'Red', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(22, 'Mauve', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(23, 'Beige', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(24, 'Sea Green', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(25, 'Khaki', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(26, 'Magenta', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(27, 'Burgundy', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(28, 'Charcoal', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(29, 'Cyan', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(30, 'Lavender', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(31, 'Rust', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(32, 'Orange', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(33, 'Peach', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(34, 'Wine', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(35, 'Denim Blue', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(36, 'Violet', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(37, 'Baby Pink', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(38, 'Crème', '2026-05-01 22:35:44', '2026-05-01 22:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_logs`
--

CREATE TABLE `delivery_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `pickup_status` enum('Pending','Picked','Failed') NOT NULL DEFAULT 'Pending',
  `delivery_status` enum('Pending','Delivered','Failed') NOT NULL DEFAULT 'Pending',
  `return_status` enum('Pending','Returned','Late','Damaged') NOT NULL DEFAULT 'Pending',
  `delivery_partner` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dry_cleaning_requests`
--

CREATE TABLE `dry_cleaning_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `requested_by` enum('Buyer','Seller') NOT NULL,
  `status` enum('Requested','Picked','Cleaned','Delivered') NOT NULL DEFAULT 'Requested',
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fabric_types`
--

CREATE TABLE `fabric_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fabric_types`
--

INSERT INTO `fabric_types` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Fleece', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(2, 'Polyester', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(3, 'Cotton', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(4, 'Knit', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(5, 'Denim', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(6, 'Cotton Blend', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(7, 'Net', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(8, 'Chiffon', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(9, 'Silk', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(10, 'Acrylic', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(11, 'Spandex / Lycra / Elastane', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(12, 'Silk Blend', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(13, 'Wool', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(14, 'Satin', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(15, 'Poly Cotton', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(16, 'Organza', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(17, 'Georgette', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(18, 'Banarasi Silk', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(19, 'Viscose', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(20, 'Nylon', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(21, 'Crepe', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(22, 'Aeropostale', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(23, 'Linen', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(24, 'Velvet', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(25, 'Mesh', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(26, 'Rayon', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(27, 'Cashmere', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(28, 'Synthetic Georgette', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(29, 'Leather', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(30, 'Fur', '2026-05-01 22:35:43', '2026-05-01 22:35:43'),
(31, 'Quilted', '2026-05-01 22:35:43', '2026-05-01 22:35:43');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `frontend_settings`
--

CREATE TABLE `frontend_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `section` varchar(255) NOT NULL DEFAULT 'general',
  `label` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `frontend_settings`
--

INSERT INTO `frontend_settings` (`id`, `key`, `value`, `type`, `section`, `label`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'site_logo', 'images/logo.png', 'image', 'logo', 'Site Logo', 'Main site logo (recommended size: 200x60px)', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(2, 'site_logo_alt', 'GetReady Logo', 'text', 'logo', 'Logo Alt Text', 'Alternative text for the logo', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(3, 'hero_title', 'Welcome to GetReady', 'text', 'hero', 'Hero Title', 'Main heading on the homepage', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(4, 'hero_subtitle', 'Your premier destination for fashion rental', 'text', 'hero', 'Hero Subtitle', 'Subtitle text below the main title', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(5, 'hero_description', 'Discover amazing fashion pieces for your special occasions. Rent, wear, and return with ease.', 'textarea', 'hero', 'Hero Description', 'Detailed description for the hero section', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(6, 'hero_image', 'images/main.png', 'image', 'hero', 'Hero Image', 'Main hero image (recommended size: 1200x600px)', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(7, 'hero_button_text', 'Start Shopping', 'text', 'hero', 'Hero Button Text', 'Text for the main call-to-action button', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(8, 'hero_button_url', '/clothes', 'text', 'hero', 'Hero Button URL', 'URL for the hero button', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(9, 'about_title', 'About GetReady', 'text', 'about', 'About Title', 'Title for the about section', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(10, 'about_content', 'GetReady is your premier fashion rental platform, offering a curated collection of designer pieces for every occasion. We believe that everyone deserves to look and feel amazing without the commitment of ownership.', 'textarea', 'about', 'About Content', 'Main content for the about section', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(11, 'about_image', 'images/about.jpg', 'image', 'about', 'About Image', 'Image for the about section (recommended size: 600x400px)', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(12, 'footer_title', 'GetReady', 'text', 'footer', 'Footer Title', 'Title displayed in the footer', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(13, 'footer_description', 'Your trusted partner in fashion rental. Quality, style, and convenience all in one place.', 'textarea', 'footer', 'Footer Description', 'Description text in the footer', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(14, 'footer_address', '123 Fashion Street, Style City, SC 12345', 'text', 'footer', 'Footer Address', 'Company address for the footer', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(15, 'footer_phone', '+1 (555) 123-4567', 'text', 'footer', 'Footer Phone', 'Contact phone number', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(16, 'footer_email', 'info@getready.com', 'text', 'footer', 'Footer Email', 'Contact email address', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(17, 'footer_copyright', '© 2024 GetReady. All rights reserved.', 'text', 'footer', 'Footer Copyright', 'Copyright text for the footer', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(18, 'social_facebook', 'https://facebook.com/getready', 'text', 'social', 'Facebook URL', 'Facebook page URL', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(19, 'social_instagram', 'https://instagram.com/getready', 'text', 'social', 'Instagram URL', 'Instagram profile URL', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(20, 'social_twitter', 'https://twitter.com/getready', 'text', 'social', 'Twitter URL', 'Twitter profile URL', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(21, 'site_title', 'GetReady - Fashion Rental Platform', 'text', 'general', 'Site Title', 'Main site title for SEO', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(22, 'site_description', 'Your premier destination for fashion rental. Rent designer pieces for special occasions.', 'textarea', 'general', 'Site Description', 'Site meta description for SEO', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(23, 'site_keywords', 'fashion rental, designer clothes, dress rental, formal wear', 'text', 'general', 'Site Keywords', 'SEO keywords for the site', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(24, 'contact_email', 'support@getready.com', 'text', 'general', 'Contact Email', 'Main contact email address', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(25, 'contact_phone', '+1 (555) 123-4567', 'text', 'general', 'Contact Phone', 'Main contact phone number', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `garment_conditions`
--

CREATE TABLE `garment_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `garment_conditions`
--

INSERT INTO `garment_conditions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Brand New', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(2, 'Like New', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(3, 'Excellent', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(4, 'Good', '2026-05-01 22:35:44', '2026-05-01 22:35:44'),
(5, 'Fair', '2026-05-01 22:35:44', '2026-05-01 22:35:44');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_extension_id` bigint(20) UNSIGNED DEFAULT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `type` enum('rent_sale','platform_fee_seller','platform_fee_buyer') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `issued_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_to_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `order_item_id`, `order_extension_id`, `invoice_number`, `type`, `amount`, `tax_amount`, `pdf_path`, `issued_by_id`, `issued_to_id`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, NULL, 'INV-CUL36DZK', 'rent_sale', 200.00, 0.00, 'invoices/1/INV-CUL36DZK_seller_buyer.pdf', 4, 5, '2026-05-06 00:08:14', '2026-05-06 00:08:14'),
(2, 1, NULL, NULL, 'GR-S-O2X7VECW', 'platform_fee_seller', 47.20, 7.20, 'invoices/1/GR-S-O2X7VECW_platform_seller.pdf', NULL, 4, '2026-05-06 00:08:14', '2026-05-06 00:08:14'),
(3, 1, NULL, NULL, 'GR-B-XLHWLN0H', 'platform_fee_buyer', 47.20, 7.20, 'invoices/1/GR-B-XLHWLN0H_platform_buyer.pdf', NULL, 5, '2026-05-06 00:08:14', '2026-05-06 00:08:14'),
(4, 2, NULL, NULL, 'INV-YFUL3FBD', 'rent_sale', 661.00, 0.00, 'invoices/2/INV-YFUL3FBD_seller_buyer.pdf', 4, 5, '2026-05-06 00:34:13', '2026-05-06 00:34:13'),
(5, 2, NULL, NULL, 'GR-S-SNFEIUV6', 'platform_fee_seller', 156.00, 23.80, 'invoices/2/GR-S-SNFEIUV6_platform_seller.pdf', NULL, 4, '2026-05-06 00:34:14', '2026-05-06 00:34:14'),
(6, 2, NULL, NULL, 'GR-B-AMHTEIYX', 'platform_fee_buyer', 274.98, 23.80, 'invoices/2/GR-B-AMHTEIYX_platform_buyer.pdf', NULL, 5, '2026-05-06 00:34:14', '2026-05-06 00:34:14'),
(7, 3, NULL, NULL, 'INV-GLTHVYEH', 'rent_sale', 2070.00, 0.00, 'invoices/3/INV-GLTHVYEH_seller_buyer.pdf', 4, 4, '2026-05-07 00:48:09', '2026-05-07 00:48:09'),
(8, 3, NULL, NULL, 'GR-S-BKRZMX7N', 'platform_fee_seller', 488.52, 74.52, 'invoices/3/GR-S-BKRZMX7N_platform_seller.pdf', NULL, 4, '2026-05-07 00:48:09', '2026-05-07 00:48:09'),
(9, 3, NULL, NULL, 'GR-B-FX4DQD4C', 'platform_fee_buyer', 704.52, 74.52, 'invoices/3/GR-B-FX4DQD4C_platform_buyer.pdf', NULL, 4, '2026-05-07 00:48:09', '2026-05-07 00:48:09');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_01_15_000000_make_email_nullable_in_users_table', 1),
(5, '2025_01_15_013500_make_gender_nullable_in_users_table', 1),
(6, '2025_07_14_112322_create_clothes_table', 1),
(7, '2025_07_14_112323_create_availability_blocks_table', 1),
(8, '2025_07_14_112323_create_cloth_measurements_table', 1),
(9, '2025_07_14_112324_create_orders_table', 1),
(10, '2025_07_14_112325_create_order_items_table', 1),
(11, '2025_07_14_112325_create_payments_table', 1),
(12, '2025_07_14_112326_create_delivery_logs_table', 1),
(13, '2025_07_14_112326_create_dry_cleaning_requests_table', 1),
(14, '2025_07_14_112327_create_cloth_images_table', 1),
(15, '2025_07_14_112327_create_ratings_table', 1),
(16, '2025_07_22_072111_create_category_table', 1),
(17, '2025_07_22_072112_create_fabric_types_table', 1),
(18, '2025_07_22_072113_create_colors_table', 1),
(19, '2025_07_22_072114_create_bottom_types_table', 1),
(20, '2025_07_22_072115_create_sizes_table', 1),
(21, '2025_07_22_072116_create_body_type_fits_table', 1),
(22, '2025_07_22_072117_create_garment_conditions_table', 1),
(23, '2025_07_29_094618_update_clothes_table_size_to_id', 1),
(24, '2025_07_30_105545_create_cart_items_table', 1),
(25, '2025_07_30_123340_create_notifications_table', 1),
(26, '2025_08_01_120809_add_rental_dates_to_cart_items', 1),
(27, '2025_08_03_081257_create_frontend_settings_table', 1),
(28, '2025_08_08_111455_update_is_approved_default_value', 1),
(29, '2025_08_08_112121_allow_null_is_approved', 1),
(30, '2025_08_08_115423_add_resubmission_count_to_clothes', 1),
(31, '2025_08_27_054102_add_purchase_value_to_clothes_table', 1),
(32, '2025_08_27_065857_add_is_purchased_to_clothes_table', 1),
(33, '2025_08_27_071811_add_purchase_fields_to_cart_items_table', 1),
(34, '2025_11_19_120000_add_type_flags_to_orders_table', 1),
(35, '2025_12_13_094237_create_brands_table', 1),
(36, '2025_12_13_111404_update_is_purchased_default_to_one_in_clothes_table', 1),
(37, '2025_12_13_130247_add_gstin_to_users_table', 1),
(38, '2025_12_24_042747_add_city_and_age_to_users_table', 1),
(39, '2025_12_24_070018_update_gender_enum_in_clothes_table', 1),
(40, '2025_12_24_075039_create_product_reviews_table', 1),
(41, '2025_12_24_075043_create_product_questions_table', 1),
(42, '2025_12_24_084033_create_replies_table', 1),
(43, '2026_01_21_065942_add_is_gst_and_gst_number_to_users_table', 1),
(44, '2026_01_22_041516_add_description_to_clothes_table', 1),
(45, '2026_01_22_075359_add_last_login_at_to_users_table', 1),
(46, '2026_01_22_125534_add_sku_to_clothes_table', 1),
(47, '2026_01_24_101219_modify_condition_in_clothes_table', 1),
(48, '2026_01_25_073641_change_is_approved_to_integer_in_clothes_table', 1),
(49, '2026_01_25_084030_change_is_approved_default_to_null_in_clothes_table', 1),
(50, '2026_01_25_090101_make_proper_foreign_keys_in_clothes_table', 1),
(51, '2026_01_25_090238_rename_clothes_columns_to_include_id_suffix', 1),
(52, '2026_01_25_091320_standardize_brand_and_condition_in_clothes_table', 1),
(53, '2026_01_25_153405_create_personal_access_tokens_table', 1),
(54, '2026_01_25_154747_create_shipments_table', 1),
(55, '2026_01_25_154911_change_order_status_to_string', 1),
(56, '2026_02_10_060956_rename_purchase_to_selling_price_in_clothes_and_cart_items', 1),
(57, '2026_02_10_065328_add_mrp_to_clothes_table', 1),
(58, '2026_02_10_122059_create_roles_table', 1),
(59, '2026_02_10_122116_create_admin_panel_users_table', 1),
(60, '2026_02_10_123822_create_permissions_table', 1),
(61, '2026_02_10_123953_create_admin_user_permissions_table', 1),
(62, '2026_02_10_124458_create_role_permissions_table', 1),
(63, '2026_02_11_070352_add_security_returned_at_to_orders_table', 1),
(64, '2026_02_11_071013_add_is_security_returned_to_orders_table', 1),
(65, '2026_02_11_101605_add_type_to_shipments_table', 1),
(66, '2026_02_11_122422_add_return_request_fields_to_orders_table', 1),
(67, '2026_02_11_133839_add_refunded_to_payment_status_enum', 1),
(68, '2026_02_14_050909_create_states_table', 1),
(69, '2026_02_14_050953_create_cities_table', 1),
(70, '2026_02_14_052327_add_state_id_and_city_id_to_users_table', 1),
(71, '2026_02_14_061003_create_taxes_table', 1),
(72, '2026_02_15_061223_add_detailed_pricing_to_order_items_table', 1),
(73, '2026_02_15_093135_add_seller_payout_to_orders_table', 1),
(74, '2026_02_15_122958_split_commission_gst_in_order_items_table', 1),
(75, '2026_02_16_163000_add_purchase_type_to_order_items', 1),
(76, '2026_02_17_103547_create_invoices_table', 1),
(77, '2026_02_23_034210_create_order_extensions_table', 1),
(78, '2026_02_23_073542_add_financial_breakdown_to_order_extensions_table', 1),
(79, '2026_02_23_113343_add_delivered_at_to_orders_table', 1),
(80, '2026_02_23_113349_add_delivered_at_to_orders_table', 1),
(81, '2026_02_23_122246_add_order_extension_id_to_invoices_table', 1),
(82, '2026_02_24_060328_add_return_date_to_orders_table', 1),
(83, '2026_02_25_051728_add_measurements_to_sizes_table', 1),
(84, '2026_02_28_100000_add_rental_conversion_fields_to_orders', 1),
(85, '2026_03_07_073019_add_measurement_unit_to_clothes_table', 1),
(86, '2026_03_19_054019_add_aadhaar_columns_to_users_table', 1),
(87, '2026_03_19_114730_create_virtual_try_ons_table', 1),
(88, '2026_03_20_045310_add_gst_api_details_to_users_table', 1),
(89, '2026_03_20_071741_add_aadhaar_api_details_to_users_table', 1),
(90, '2026_03_20_082123_add_missing_gst_and_aadhaar_fields_to_users_table', 1),
(91, '2026_03_20_092730_add_state_to_users_table_and_drop_ids', 1),
(92, '2026_03_20_101441_drop_state_city_id_from_users_table', 1),
(93, '2026_04_17_065410_create_prompts_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'info',
  `icon` varchar(255) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `icon`, `data`, `read`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'Welcome to GetReady!', 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.', 'success', 'bi-emoji-smile', NULL, 0, NULL, '2026-05-02 01:26:06', '2026-05-02 01:26:06'),
(2, 3, 'Welcome to GetReady!', 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.', 'success', 'bi-emoji-smile', NULL, 0, NULL, '2026-05-02 02:45:26', '2026-05-02 02:45:26'),
(3, 3, 'Item Listed Successfully Pending Approval', 'Your item \'Test 01\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":1}', 0, NULL, '2026-05-02 02:50:25', '2026-05-02 02:50:25'),
(4, 3, 'Item Approved', 'Your item \'Test 01\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":1,\"cloth_title\":\"Test 01\"}', 0, NULL, '2026-05-02 02:51:01', '2026-05-02 02:51:01'),
(5, 4, 'Welcome to GetReady!', 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.', 'success', 'bi-emoji-smile', NULL, 1, '2026-05-06 03:27:53', '2026-05-05 01:41:03', '2026-05-06 03:27:53'),
(6, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Jeans\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":2}', 1, '2026-05-06 03:27:53', '2026-05-05 01:45:10', '2026-05-06 03:27:53'),
(7, 4, 'Item Approved', 'Your item \'Jeans\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":2,\"cloth_title\":\"Jeans\"}', 1, '2026-05-06 03:27:53', '2026-05-05 01:45:33', '2026-05-06 03:27:53'),
(8, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Floral Printed Fit & Flare Midi Ethnic Dresses\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":3}', 1, '2026-05-06 03:27:53', '2026-05-05 02:06:31', '2026-05-06 03:27:53'),
(9, 4, 'Item Approved', 'Your item \'Floral Printed Fit & Flare Midi Ethnic Dresses\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":3,\"cloth_title\":\"Floral Printed Fit & Flare Midi Ethnic Dresses\"}', 1, '2026-05-06 03:27:53', '2026-05-05 02:06:56', '2026-05-06 03:27:53'),
(10, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":4}', 1, '2026-05-06 03:27:53', '2026-05-05 02:39:24', '2026-05-06 03:27:53'),
(11, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Black Sequinned Embellished Fusion Saree\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":5}', 1, '2026-05-06 03:27:53', '2026-05-05 02:48:04', '2026-05-06 03:27:53'),
(12, 4, 'Item Approved', 'Your item \'Black Sequinned Embellished Fusion Saree\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":5,\"cloth_title\":\"Black Sequinned Embellished Fusion Saree\"}', 1, '2026-05-06 03:27:53', '2026-05-05 02:48:28', '2026-05-06 03:27:53'),
(13, 4, 'Item Approved', 'Your item \'Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":4,\"cloth_title\":\"Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers\"}', 1, '2026-05-06 03:27:53', '2026-05-05 02:48:31', '2026-05-06 03:27:53'),
(14, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Men Self Design V-Neck Cotton T-shirt+\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":6}', 1, '2026-05-06 03:27:53', '2026-05-05 03:55:32', '2026-05-06 03:27:53'),
(15, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Puff Sleeve Net A-Line Dress\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":7}', 1, '2026-05-06 03:27:53', '2026-05-05 04:00:27', '2026-05-06 03:27:53'),
(16, 4, 'Item Approved', 'Your item \'Puff Sleeve Net A-Line Dress\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":7,\"cloth_title\":\"Puff Sleeve Net A-Line Dress\"}', 1, '2026-05-06 03:27:53', '2026-05-05 04:00:46', '2026-05-06 03:27:53'),
(17, 4, 'Item Approved', 'Your item \'Men Self Design V-Neck Cotton T-shirt+\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":6,\"cloth_title\":\"Men Self Design V-Neck Cotton T-shirt+\"}', 1, '2026-05-06 03:27:53', '2026-05-05 04:00:50', '2026-05-06 03:27:53'),
(18, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Boys Skinny Fit Jeans\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":8}', 1, '2026-05-06 03:27:53', '2026-05-05 04:14:26', '2026-05-06 03:27:53'),
(19, 4, 'Item Approved', 'Your item \'Boys Skinny Fit Jeans\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":8,\"cloth_title\":\"Boys Skinny Fit Jeans\"}', 1, '2026-05-06 03:27:53', '2026-05-05 04:14:48', '2026-05-06 03:27:53'),
(20, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Floral Print Fit & Flare Maxi Dress\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":9}', 1, '2026-05-06 03:27:53', '2026-05-05 04:42:04', '2026-05-06 03:27:53'),
(21, 4, 'Item Approved', 'Your item \'Floral Print Fit & Flare Maxi Dress\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":9,\"cloth_title\":\"Floral Print Fit & Flare Maxi Dress\"}', 1, '2026-05-06 03:27:53', '2026-05-05 04:42:21', '2026-05-06 03:27:53'),
(22, 4, 'Item Listed Successfully Pending Approval', 'Your item \'Women Checked Jumpsuit with Tie-Up\' has been listed successfully and is pending approval.', 'success', 'bi-check2-circle', '{\"cloth_id\":10}', 1, '2026-05-06 03:27:53', '2026-05-05 05:12:29', '2026-05-06 03:27:53'),
(23, 4, 'Item Approved', 'Your item \'Women Checked Jumpsuit with Tie-Up\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":10,\"cloth_title\":\"Women Checked Jumpsuit with Tie-Up\"}', 1, '2026-05-06 03:27:53', '2026-05-05 05:12:49', '2026-05-06 03:27:53'),
(24, 5, 'Welcome to GetReady!', 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.', 'success', 'bi-emoji-smile', NULL, 1, '2026-05-06 00:33:14', '2026-05-06 00:00:55', '2026-05-06 00:33:14'),
(25, 5, 'Order Placed Successfully', 'Your order #1 has been confirmed. Thank you for shopping with us!', 'success', 'bi-bag-check', '{\"order_id\":1}', 1, '2026-05-06 00:33:14', '2026-05-06 00:08:10', '2026-05-06 00:33:14'),
(26, 4, 'New Rental!', 'Good news! Your item \'Floral Print Fit & Flare Maxi Dress\' has been rented.', 'success', 'bi-cash-coin', '{\"cloth_id\":9,\"order_id\":1}', 1, '2026-05-06 03:27:53', '2026-05-06 00:08:10', '2026-05-06 03:27:53'),
(27, 5, 'Order Placed Successfully', 'Your order #2 has been confirmed. Thank you for shopping with us!', 'success', 'bi-bag-check', '{\"order_id\":2}', 0, NULL, '2026-05-06 00:34:11', '2026-05-06 00:34:11'),
(28, 4, 'New Sale!', 'Good news! Your item \'Puff Sleeve Net A-Line Dress\' has been sold.', 'success', 'bi-cash-coin', '{\"cloth_id\":7,\"order_id\":2}', 1, '2026-05-06 03:27:53', '2026-05-06 00:34:11', '2026-05-06 03:27:53'),
(29, 4, 'Item Approved', 'Your item \'Floral Printed Fit & Flare Midi Ethnic Dresses\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":3,\"cloth_title\":\"Floral Printed Fit & Flare Midi Ethnic Dresses\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:11', '2026-05-06 03:27:53'),
(30, 4, 'Item Approved', 'Your item \'Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":4,\"cloth_title\":\"Men Ethnic Motifs Printed Regular Pure Cotton Kurta with Trousers\"}', 1, '2026-05-06 03:27:57', '2026-05-06 00:45:15', '2026-05-06 03:27:57'),
(31, 4, 'Item Approved', 'Your item \'Black Sequinned Embellished Fusion Saree\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":5,\"cloth_title\":\"Black Sequinned Embellished Fusion Saree\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:18', '2026-05-06 03:27:53'),
(32, 4, 'Item Approved', 'Your item \'Men Self Design V-Neck Cotton T-shirt+\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":6,\"cloth_title\":\"Men Self Design V-Neck Cotton T-shirt+\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:20', '2026-05-06 03:27:53'),
(33, 4, 'Item Approved', 'Your item \'Puff Sleeve Net A-Line Dress\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":7,\"cloth_title\":\"Puff Sleeve Net A-Line Dress\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:24', '2026-05-06 03:27:53'),
(34, 4, 'Item Approved', 'Your item \'Boys Skinny Fit Jeans\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":8,\"cloth_title\":\"Boys Skinny Fit Jeans\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:29', '2026-05-06 03:27:53'),
(35, 4, 'Item Approved', 'Your item \'Floral Print Fit & Flare Maxi Dress\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":9,\"cloth_title\":\"Floral Print Fit & Flare Maxi Dress\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:32', '2026-05-06 03:27:53'),
(36, 4, 'Item Approved', 'Your item \'Women Checked Jumpsuit with Tie-Up\' has been approved and is now live on our platform!', 'success', 'bi-check-circle', '{\"cloth_id\":10,\"cloth_title\":\"Women Checked Jumpsuit with Tie-Up\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:45:35', '2026-05-06 03:27:53'),
(37, 4, 'Item Rejected', 'Your item \'Jeans\' has been rejected. Reason: Quality Are not good. Please review and resubmit.', 'warning', 'bi-exclamation-triangle', '{\"cloth_id\":2,\"cloth_title\":\"Jeans\",\"reject_reason\":\"Quality Are not good\"}', 1, '2026-05-06 03:27:53', '2026-05-06 00:46:54', '2026-05-06 03:27:53'),
(38, 6, 'Welcome to GetReady!', 'We are excited to have you on board. Start your journey by listing your first item or exploring our collection.', 'success', 'bi-emoji-smile', NULL, 1, '2026-05-06 04:00:49', '2026-05-06 04:00:37', '2026-05-06 04:00:49'),
(39, 4, 'Order Placed Successfully', 'Your order #3 has been confirmed. Thank you for shopping with us!', 'success', 'bi-bag-check', '{\"order_id\":3}', 0, NULL, '2026-05-07 00:48:05', '2026-05-07 00:48:05'),
(40, 4, 'New Sale!', 'Good news! Your item \'Floral Printed Fit & Flare Midi Ethnic Dresses\' has been sold.', 'success', 'bi-cash-coin', '{\"cloth_id\":3,\"order_id\":3}', 0, NULL, '2026-05-07 00:48:05', '2026-05-07 00:48:05'),
(41, 4, 'New Rental!', 'Good news! Your item \'Women Checked Jumpsuit with Tie-Up\' has been rented.', 'success', 'bi-cash-coin', '{\"cloth_id\":10,\"order_id\":3}', 0, NULL, '2026-05-07 00:48:05', '2026-05-07 00:48:05'),
(42, 4, 'New Rental!', 'Good news! Your item \'Floral Print Fit & Flare Maxi Dress\' has been rented.', 'success', 'bi-cash-coin', '{\"cloth_id\":9,\"order_id\":3}', 0, NULL, '2026-05-07 00:48:05', '2026-05-07 00:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `buyer_id` bigint(20) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `security_amount` decimal(10,2) NOT NULL,
  `security_returned_at` timestamp NULL DEFAULT NULL,
  `is_security_returned` tinyint(1) NOT NULL DEFAULT 0,
  `security_absorbed_into_purchase` tinyint(1) NOT NULL DEFAULT 0,
  `is_seller_paid` tinyint(1) NOT NULL DEFAULT 0,
  `seller_paid_at` timestamp NULL DEFAULT NULL,
  `has_rental_items` tinyint(1) NOT NULL DEFAULT 0,
  `has_purchase_items` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `return_reason` varchar(255) DEFAULT NULL,
  `return_details` text DEFAULT NULL,
  `return_images` text DEFAULT NULL,
  `admin_rejection_reason` text DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `rental_from` date NOT NULL,
  `rental_to` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `buyer_id`, `total_amount`, `security_amount`, `security_returned_at`, `is_security_returned`, `security_absorbed_into_purchase`, `is_seller_paid`, `seller_paid_at`, `has_rental_items`, `has_purchase_items`, `status`, `delivered_at`, `return_reason`, `return_details`, `return_images`, `admin_rejection_reason`, `delivery_address`, `rental_from`, `rental_to`, `return_date`, `created_at`, `updated_at`) VALUES
(1, 5, 447.20, 200.00, NULL, 0, 0, 0, NULL, 1, 0, 'Order Confirmed & Shipment Created', NULL, NULL, NULL, NULL, NULL, 'Krishna Tower\nGreen Park Extension', '2026-05-09', '2026-05-12', '2026-05-13', '2026-05-06 00:07:57', '2026-05-06 00:08:10'),
(2, 5, 935.98, 0.00, NULL, 0, 0, 0, NULL, 0, 1, 'Delivered', '2026-05-06 00:56:37', NULL, NULL, NULL, NULL, 'Krishna Tower\nGreen Park Extension', '2026-05-06', '2026-05-09', '2026-05-10', '2026-05-06 00:34:06', '2026-05-06 00:56:37'),
(3, 4, 9891.32, 320.00, NULL, 0, 0, 0, NULL, 1, 1, 'Order Confirmed & Shipment Created', NULL, NULL, NULL, NULL, NULL, 'Krishna Tower opposite Green Park Stadium\nCabin No. 710, 7th Floor, 15/63 Krishna Tower opposite Green Park Stadium, Kanpur- 208001', '2026-05-16', '2026-06-06', '2026-06-07', '2026-05-07 00:48:02', '2026-05-07 00:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_extensions`
--

CREATE TABLE `order_extensions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `old_rental_to` date NOT NULL,
  `new_rental_to` date NOT NULL,
  `extra_days` int(11) NOT NULL,
  `additional_amount` decimal(10,2) NOT NULL,
  `base_rent_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `buyer_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `seller_commission` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rent_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `buyer_commission_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `seller_commission_gst` decimal(10,2) NOT NULL DEFAULT 0.00,
  `seller_net_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','paid','cancelled','expired') NOT NULL DEFAULT 'pending',
  `is_admin_override` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_type` varchar(255) NOT NULL DEFAULT 'rent',
  `converted_to_purchase_at` timestamp NULL DEFAULT NULL,
  `conversion_amount` decimal(10,2) DEFAULT NULL,
  `base_rent` decimal(10,2) DEFAULT NULL,
  `buyer_commission` decimal(10,2) DEFAULT NULL,
  `seller_commission` decimal(10,2) DEFAULT NULL,
  `rent_gst` decimal(10,2) DEFAULT NULL,
  `buyer_commission_gst` decimal(10,2) DEFAULT NULL,
  `seller_commission_gst` decimal(10,2) DEFAULT NULL,
  `tcs_amount` decimal(10,2) DEFAULT NULL,
  `is_seller_gst` tinyint(1) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `cloth_id`, `purchase_type`, `converted_to_purchase_at`, `conversion_amount`, `base_rent`, `buyer_commission`, `seller_commission`, `rent_gst`, `buyer_commission_gst`, `seller_commission_gst`, `tcs_amount`, `is_seller_gst`, `price`, `created_at`, `updated_at`) VALUES
(1, 1, 9, 'rent', NULL, NULL, 200.00, 40.00, 40.00, 0.00, 7.20, 7.20, 0.00, 0, 247.20, '2026-05-06 00:07:57', '2026-05-06 00:07:57'),
(2, 2, 7, 'buy', NULL, NULL, 661.00, 132.20, 132.20, 118.98, 23.80, 23.80, 0.00, 0, 935.98, '2026-05-06 00:34:06', '2026-05-06 00:34:06'),
(3, 3, 3, 'buy', NULL, NULL, 1200.00, 240.00, 240.00, 216.00, 43.20, 43.20, 0.00, 0, 1699.20, '2026-05-07 00:48:02', '2026-05-07 00:48:02'),
(4, 3, 10, 'rent', NULL, NULL, 120.00, 24.00, 24.00, 0.00, 4.32, 4.32, 0.00, 0, 148.32, '2026-05-07 00:48:02', '2026-05-07 00:48:02'),
(5, 3, 9, 'rent', NULL, NULL, 750.00, 150.00, 150.00, 0.00, 27.00, 27.00, 0.00, 0, 927.00, '2026-05-07 00:48:02', '2026-05-07 00:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_status` enum('Pending','Paid','Failed','Refunded','Cancelled','Partially Refunded') NOT NULL DEFAULT 'Pending',
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `payment_status`, `amount`, `transaction_id`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'razorpay', 'Paid', 447.20, 'pay_mock_1778045888739', '2026-05-06 00:08:09', '2026-05-06 00:08:09', '2026-05-06 00:08:09'),
(2, 2, 'razorpay', 'Paid', 935.98, 'pay_mock_1778047449904', '2026-05-06 00:34:10', '2026-05-06 00:34:10', '2026-05-06 00:34:10'),
(3, 3, 'razorpay', 'Paid', 9891.32, 'pay_mock_1778134684108', '2026-05-07 00:48:04', '2026-05-07 00:48:04', '2026-05-07 00:48:04');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `label`, `module`, `created_at`, `updated_at`) VALUES
(1, 'admin.dashboard', 'Dashboard', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(2, 'user.index', 'Users', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(3, 'categories.index', 'Categories', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(4, 'brands.index', 'Brands', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(5, 'fabric_types.index', 'Fabric Types', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(6, 'colors.index', 'Colors', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(7, 'bottom_types.index', 'Bottom Types', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(8, 'sizes.index', 'Sizes', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(9, 'body_type_fits.index', 'Body Type Fits', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(10, 'garment_conditions.index', 'Outfit Conditions', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(11, 'role_master.index', 'Role Master', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(12, 'admin_panel_users.index', 'Admin Users', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(13, 'states.index', 'State', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(14, 'cities.index', 'City', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(15, 'admin.tax', 'Tax Management', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(16, 'admin.frontend', 'Frontend Settings', 'Setup', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(17, 'admin.cloth-approval', 'Clothes Approval', 'Approval', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(18, 'admin.orders', 'Orders', 'Operations', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(19, 'admin.security', 'Security Deposits', 'Operations', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(20, 'admin.payments', 'Payments', 'Operations', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(21, 'admin.reports.financial', 'Financial Report', 'Reports', '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(22, 'admin.reports.calendar', 'Alert Calendar', 'Reports', '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_questions`
--

CREATE TABLE `product_questions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `question` text NOT NULL,
  `answer` text DEFAULT NULL,
  `answered_by` bigint(20) UNSIGNED DEFAULT NULL,
  `answered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prompts`
--

CREATE TABLE `prompts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `prompt_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `rater_id` bigint(20) UNSIGNED NOT NULL,
  `rated_user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `review` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replies`
--

CREATE TABLE `replies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `repliable_type` varchar(255) NOT NULL,
  `repliable_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '2026-05-01 22:35:38', '2026-05-01 22:35:38'),
(2, 'manager', '2026-05-01 22:35:38', '2026-05-01 22:35:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL),
(2, 1, 2, NULL, NULL),
(3, 1, 3, NULL, NULL),
(4, 1, 4, NULL, NULL),
(5, 1, 5, NULL, NULL),
(6, 1, 6, NULL, NULL),
(7, 1, 7, NULL, NULL),
(8, 1, 8, NULL, NULL),
(9, 1, 9, NULL, NULL),
(10, 1, 10, NULL, NULL),
(11, 1, 11, NULL, NULL),
(12, 1, 12, NULL, NULL),
(13, 1, 13, NULL, NULL),
(14, 1, 14, NULL, NULL),
(15, 1, 15, NULL, NULL),
(16, 1, 16, NULL, NULL),
(17, 1, 17, NULL, NULL),
(18, 1, 18, NULL, NULL),
(19, 1, 19, NULL, NULL),
(20, 1, 20, NULL, NULL),
(21, 1, 21, NULL, NULL),
(22, 1, 22, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('tsq2MPdcpEWZynJafEMg4R8pwyvQ0Hd7ehJwHhnI', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZUhJRXFkVHltdHN5OFJHRmtEdllzeXlCU0hSam43RHl1aGp2T1pOWCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE0OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvY2xvdGhlcz9kZWFsX3R5cGU9YWxsJmZyb21fZGF0ZT0maXNfY2xlYW5lZD0mbXJwX21heD01MDAwMCZtcnBfbWluPTAmcGFnZT0xJnByaWNlX21heD0yMDAwMCZwcmljZV9taW49MCZwcm9kdWN0X3JhdGluZz0mcmRtX3ByaW9yaXR5PSZzZWFyY2g9JnNlbGxlcl9yYXRpbmc9JnNvcnRfYnk9ZGVmYXVsdCZzdGF0dXM9YW55JnRvX2RhdGU9Ijt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6NDt9', 1778328289);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'forward',
  `courier_name` varchar(255) NOT NULL DEFAULT 'Xpressbees',
  `waybill_number` varchar(255) DEFAULT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `label_url` varchar(255) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Pending',
  `courier_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`courier_response`)),
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `order_id`, `type`, `courier_name`, `waybill_number`, `tracking_url`, `label_url`, `reference_id`, `status`, `courier_response`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'forward', 'Xpressbees', 'XB411985373', 'https://www.xpressbees.com/track', 'https://www.xpressbees.com/track', '1', 'Booked', NULL, NULL, '2026-05-06 00:08:10', '2026-05-06 00:08:10'),
(2, 2, 'forward', 'Xpressbees', 'XB194496392', 'https://www.xpressbees.com/track', 'https://www.xpressbees.com/track', '2', 'Booked', NULL, NULL, '2026-05-06 00:34:11', '2026-05-06 00:34:11'),
(3, 3, 'forward', 'Xpressbees', 'XB889166787', 'https://www.xpressbees.com/track', 'https://www.xpressbees.com/track', '3', 'Booked', NULL, NULL, '2026-05-07 00:48:05', '2026-05-07 00:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `chest_bust` varchar(255) DEFAULT NULL,
  `waist` varchar(255) DEFAULT NULL,
  `length` varchar(255) DEFAULT NULL,
  `shoulder` varchar(255) DEFAULT NULL,
  `sleeve_length` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`id`, `name`, `created_at`, `updated_at`, `chest_bust`, `waist`, `length`, `shoulder`, `sleeve_length`) VALUES
(1, 'XS', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL),
(2, 'S', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL),
(3, 'M', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL),
(4, 'L', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL),
(5, 'XL', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL),
(6, 'XXL', '2026-05-01 22:35:44', '2026-05-01 22:35:44', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Maharashtra', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(2, 'Gujarat', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(3, 'Karnataka', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(4, 'Delhi', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(5, 'Rajasthan', 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `taxes`
--

CREATE TABLE `taxes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taxes`
--

INSERT INTO `taxes` (`id`, `name`, `percentage`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Gst 18%', 18.00, 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(2, 'Cgst 9%', 9.00, 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(3, 'Sgst 9%', 9.00, 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(4, 'Igst 18%', 18.00, 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45'),
(5, 'Tcs 10%', 10.00, 1, '2026-05-01 22:35:45', '2026-05-01 22:35:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `is_gst` tinyint(1) NOT NULL DEFAULT 0,
  `gst_number` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gstin` varchar(15) DEFAULT NULL,
  `aadhaar_number` varchar(12) DEFAULT NULL,
  `is_aadhaar_verified` tinyint(1) NOT NULL DEFAULT 0,
  `gender` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `gst_legal_name` varchar(255) DEFAULT NULL,
  `gst_trade_name` varchar(255) DEFAULT NULL,
  `gst_constitution_of_business` varchar(255) DEFAULT NULL,
  `gst_status` varchar(255) DEFAULT NULL,
  `gst_registration_date` varchar(255) DEFAULT NULL,
  `gst_principal_address` text DEFAULT NULL,
  `gst_nature_of_business` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gst_nature_of_business`)),
  `gst_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gst_details`)),
  `aadhaar_masked_number` varchar(255) DEFAULT NULL,
  `aadhaar_address` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`aadhaar_address`)),
  `aadhaar_dob` varchar(255) DEFAULT NULL,
  `aadhaar_care_of` varchar(255) DEFAULT NULL,
  `aadhaar_xml_link` text DEFAULT NULL,
  `aadhaar_pdf_link` text DEFAULT NULL,
  `aadhaar_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`aadhaar_details`)),
  `gst_members` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gst_members`)),
  `aadhaar_image_base64` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `is_gst`, `gst_number`, `name`, `email`, `phone`, `profile_image`, `address`, `state`, `city`, `age`, `gstin`, `aadhaar_number`, `is_aadhaar_verified`, `gender`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `last_login_at`, `gst_legal_name`, `gst_trade_name`, `gst_constitution_of_business`, `gst_status`, `gst_registration_date`, `gst_principal_address`, `gst_nature_of_business`, `gst_details`, `aadhaar_masked_number`, `aadhaar_address`, `aadhaar_dob`, `aadhaar_care_of`, `aadhaar_xml_link`, `aadhaar_pdf_link`, `aadhaar_details`, `gst_members`, `aadhaar_image_base64`) VALUES
(1, 0, NULL, 'Test User', 'test@example.com', '1234567890', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Male', NULL, '$2y$12$zyti2q0mv.DRmbpZ7kSpNeP7V.59Rbf0UcoOErvYjNN2MmtRGA6Eu', NULL, '2026-05-01 22:35:43', '2026-05-01 22:35:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 0, NULL, 'User-9204', NULL, '9953199204', NULL, NULL, NULL, NULL, 30, NULL, NULL, 0, 'Women', NULL, '$2y$12$khbNLpSXArf9UTsDYWgVmunJXskFHO70BjbLUklDqbJurvJ/pyaeO', 'bWQWcsfeOEklyml9BdaQpRG3S8MdERHWjxXBsgsJByprnPZ3nWt9Qxvkzs2C', '2026-05-02 01:26:06', '2026-05-07 02:55:35', '2026-05-07 02:55:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 0, NULL, 'User-4809', NULL, '9598124809', NULL, NULL, NULL, NULL, 30, NULL, NULL, 0, 'Men', NULL, '$2y$12$ro2lHodW2N032KOQWJtSUukytB3LDUfdMmnJVaR75gcZt5fxgjh5.', NULL, '2026-05-02 02:45:26', '2026-05-02 02:45:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 0, NULL, 'User-0058', 'ravi@triserv360.com', '8726490058', 'profile_images/AZVY3Hkjrf0uyHWfUKavfdH5u2HxNovaLFLC6qHr.jpg', 'Krishna Tower opposite Green Park Stadium\r\nCabin No. 710, 7th Floor, 15/63 Krishna Tower opposite Green Park Stadium, Kanpur- 208001', 'Uttar Pradesh', 'Kanpur Nagar', 20, NULL, NULL, 0, 'Men', NULL, '$2y$12$3Lmn49zDSCMPUvEwuBV7beEf8A38TcdaNepQzuZWvD.F/qG7qUKF2', 'NYMKy5cyVjaFNeHQ2XaKdjIWDjtPmMP0HrKs7kunjJuml2AhHs5nLrrHRgw9', '2026-05-05 01:41:03', '2026-05-09 02:42:00', '2026-05-09 02:42:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 0, NULL, 'User-8236', 'info@triserv360.com', '7007368236', NULL, 'Krishna Tower\r\nGreen Park Extension', 'Delhi', 'New Delhi', 20, NULL, NULL, 0, 'Women', NULL, '$2y$12$c50bH7JeXGLou/upJqJBmO5B5lSiwvLngDmPdn9eJoZNJqSc5nQ4u', 'sfknGhbiRtXeCyPPLgf12ZKdBxhLXnq8t8KLmXQcBsPab2tg4P3IL6dTpBeY', '2026-05-06 00:00:55', '2026-05-06 04:47:11', '2026-05-06 04:47:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 0, NULL, 'User-1440', NULL, '0894891440', NULL, NULL, NULL, NULL, 25, NULL, NULL, 0, 'Men', NULL, '$2y$12$jW9vqnzy8gHsfj55ckcs4eviVF6qjZgwk.GrGd4Z//q0wFuH2X4Cq', NULL, '2026-05-06 04:00:37', '2026-05-06 04:00:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `virtual_try_ons`
--

CREATE TABLE `virtual_try_ons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cloth_id` bigint(20) UNSIGNED NOT NULL,
  `job_id` varchar(255) DEFAULT NULL,
  `user_image_path` varchar(255) NOT NULL,
  `result_image_url` longtext DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_panel_users`
--
ALTER TABLE `admin_panel_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_panel_users_username_unique` (`username`),
  ADD UNIQUE KEY `admin_panel_users_email_unique` (`email`),
  ADD KEY `admin_panel_users_role_id_foreign` (`role_id`);

--
-- Indexes for table `admin_user_permissions`
--
ALTER TABLE `admin_user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_permission_unique` (`admin_panel_user_id`,`permission_id`),
  ADD KEY `admin_user_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `availability_blocks`
--
ALTER TABLE `availability_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `availability_blocks_cloth_id_foreign` (`cloth_id`);

--
-- Indexes for table `body_type_fits`
--
ALTER TABLE `body_type_fits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bottom_types`
--
ALTER TABLE `bottom_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_user_id_cloth_id_unique` (`user_id`,`cloth_id`),
  ADD KEY `cart_items_cloth_id_foreign` (`cloth_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_state_id_foreign` (`state_id`);

--
-- Indexes for table `clothes`
--
ALTER TABLE `clothes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clothes_user_id_foreign` (`user_id`),
  ADD KEY `clothes_category_foreign` (`category_id`),
  ADD KEY `clothes_fabric_foreign` (`fabric_id`),
  ADD KEY `clothes_color_foreign` (`color_id`),
  ADD KEY `clothes_bottom_type_foreign` (`bottom_type_id`),
  ADD KEY `clothes_size_foreign` (`size_id`),
  ADD KEY `clothes_fit_type_foreign` (`fit_type_id`),
  ADD KEY `clothes_brand_id_foreign` (`brand_id`),
  ADD KEY `clothes_condition_id_foreign` (`condition_id`);

--
-- Indexes for table `cloth_images`
--
ALTER TABLE `cloth_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cloth_images_cloth_id_foreign` (`cloth_id`);

--
-- Indexes for table `cloth_measurements`
--
ALTER TABLE `cloth_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cloth_measurements_cloth_id_foreign` (`cloth_id`);

--
-- Indexes for table `colors`
--
ALTER TABLE `colors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_logs_order_id_foreign` (`order_id`);

--
-- Indexes for table `dry_cleaning_requests`
--
ALTER TABLE `dry_cleaning_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dry_cleaning_requests_order_item_id_foreign` (`order_item_id`);

--
-- Indexes for table `fabric_types`
--
ALTER TABLE `fabric_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `frontend_settings`
--
ALTER TABLE `frontend_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `frontend_settings_key_unique` (`key`);

--
-- Indexes for table `garment_conditions`
--
ALTER TABLE `garment_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_order_id_foreign` (`order_id`),
  ADD KEY `invoices_order_item_id_foreign` (`order_item_id`),
  ADD KEY `invoices_issued_by_id_foreign` (`issued_by_id`),
  ADD KEY `invoices_issued_to_id_foreign` (`issued_to_id`),
  ADD KEY `invoices_order_extension_id_foreign` (`order_extension_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_id_read_index` (`user_id`,`read`),
  ADD KEY `notifications_user_id_created_at_index` (`user_id`,`created_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_buyer_id_foreign` (`buyer_id`);

--
-- Indexes for table `order_extensions`
--
ALTER TABLE `order_extensions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_extensions_order_id_foreign` (`order_id`),
  ADD KEY `order_extensions_payment_id_foreign` (`payment_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_cloth_id_foreign` (`cloth_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `product_questions`
--
ALTER TABLE `product_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_questions_cloth_id_foreign` (`cloth_id`),
  ADD KEY `product_questions_user_id_foreign` (`user_id`),
  ADD KEY `product_questions_answered_by_foreign` (`answered_by`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_reviews_cloth_id_user_id_unique` (`cloth_id`,`user_id`),
  ADD KEY `product_reviews_user_id_foreign` (`user_id`);

--
-- Indexes for table `prompts`
--
ALTER TABLE `prompts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ratings_rater_id_foreign` (`rater_id`),
  ADD KEY `ratings_rated_user_id_foreign` (`rated_user_id`),
  ADD KEY `ratings_order_id_foreign` (`order_id`);

--
-- Indexes for table `replies`
--
ALTER TABLE `replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replies_user_id_foreign` (`user_id`),
  ADD KEY `replies_repliable_type_repliable_id_index` (`repliable_type`,`repliable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shipments_order_id_foreign` (`order_id`),
  ADD KEY `shipments_waybill_number_index` (`waybill_number`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `states_name_unique` (`name`);

--
-- Indexes for table `taxes`
--
ALTER TABLE `taxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `virtual_try_ons`
--
ALTER TABLE `virtual_try_ons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `virtual_try_ons_user_id_foreign` (`user_id`),
  ADD KEY `virtual_try_ons_cloth_id_foreign` (`cloth_id`),
  ADD KEY `virtual_try_ons_job_id_index` (`job_id`),
  ADD KEY `virtual_try_ons_status_index` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_panel_users`
--
ALTER TABLE `admin_panel_users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_user_permissions`
--
ALTER TABLE `admin_user_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `availability_blocks`
--
ALTER TABLE `availability_blocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `body_type_fits`
--
ALTER TABLE `body_type_fits`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bottom_types`
--
ALTER TABLE `bottom_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `clothes`
--
ALTER TABLE `clothes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cloth_images`
--
ALTER TABLE `cloth_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `cloth_measurements`
--
ALTER TABLE `cloth_measurements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `colors`
--
ALTER TABLE `colors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dry_cleaning_requests`
--
ALTER TABLE `dry_cleaning_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fabric_types`
--
ALTER TABLE `fabric_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `frontend_settings`
--
ALTER TABLE `frontend_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `garment_conditions`
--
ALTER TABLE `garment_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_extensions`
--
ALTER TABLE `order_extensions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_questions`
--
ALTER TABLE `product_questions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prompts`
--
ALTER TABLE `prompts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `replies`
--
ALTER TABLE `replies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `taxes`
--
ALTER TABLE `taxes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `virtual_try_ons`
--
ALTER TABLE `virtual_try_ons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_panel_users`
--
ALTER TABLE `admin_panel_users`
  ADD CONSTRAINT `admin_panel_users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_user_permissions`
--
ALTER TABLE `admin_user_permissions`
  ADD CONSTRAINT `admin_user_permissions_admin_panel_user_id_foreign` FOREIGN KEY (`admin_panel_user_id`) REFERENCES `admin_panel_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `availability_blocks`
--
ALTER TABLE `availability_blocks`
  ADD CONSTRAINT `availability_blocks_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_state_id_foreign` FOREIGN KEY (`state_id`) REFERENCES `states` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `clothes`
--
ALTER TABLE `clothes`
  ADD CONSTRAINT `clothes_bottom_type_foreign` FOREIGN KEY (`bottom_type_id`) REFERENCES `bottom_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_category_foreign` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_color_foreign` FOREIGN KEY (`color_id`) REFERENCES `colors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_condition_id_foreign` FOREIGN KEY (`condition_id`) REFERENCES `garment_conditions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_fabric_foreign` FOREIGN KEY (`fabric_id`) REFERENCES `fabric_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_fit_type_foreign` FOREIGN KEY (`fit_type_id`) REFERENCES `body_type_fits` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_size_foreign` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `clothes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cloth_images`
--
ALTER TABLE `cloth_images`
  ADD CONSTRAINT `cloth_images_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cloth_measurements`
--
ALTER TABLE `cloth_measurements`
  ADD CONSTRAINT `cloth_measurements_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  ADD CONSTRAINT `delivery_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dry_cleaning_requests`
--
ALTER TABLE `dry_cleaning_requests`
  ADD CONSTRAINT `dry_cleaning_requests_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_issued_by_id_foreign` FOREIGN KEY (`issued_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `invoices_issued_to_id_foreign` FOREIGN KEY (`issued_to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_order_extension_id_foreign` FOREIGN KEY (`order_extension_id`) REFERENCES `order_extensions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_extensions`
--
ALTER TABLE `order_extensions`
  ADD CONSTRAINT `order_extensions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_extensions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`),
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_questions`
--
ALTER TABLE `product_questions`
  ADD CONSTRAINT `product_questions_answered_by_foreign` FOREIGN KEY (`answered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_questions_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_questions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_rated_user_id_foreign` FOREIGN KEY (`rated_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ratings_rater_id_foreign` FOREIGN KEY (`rater_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `replies`
--
ALTER TABLE `replies`
  ADD CONSTRAINT `replies_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `virtual_try_ons`
--
ALTER TABLE `virtual_try_ons`
  ADD CONSTRAINT `virtual_try_ons_cloth_id_foreign` FOREIGN KEY (`cloth_id`) REFERENCES `clothes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `virtual_try_ons_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
