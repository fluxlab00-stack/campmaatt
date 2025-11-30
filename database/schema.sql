-- CampMart Database Schema
-- Production-ready e-commerce platform for campus communities

-- Create Database
CREATE DATABASE IF NOT EXISTS campmart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campmart;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 02:02 PM
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
-- Database: `campmart`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `bookmark_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `bookmarked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `campuses`
--

CREATE TABLE `campuses` (
  `campus_id` int(11) NOT NULL,
  `campus_name` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `campuses`
--

INSERT INTO `campuses` (`campus_id`, `campus_name`, `city`, `state`, `created_at`, `updated_at`) VALUES
(1, 'University of Lagos, Lagos', 'Lagos', 'Lagos', '2025-11-15 19:08:15', '2025-11-16 09:51:44'),
(2, 'Obafemi Awolowo University, Ife', 'Ile-Ife', 'Osun', '2025-11-15 19:08:15', '2025-11-16 09:51:18'),
(3, 'University of Ibadan, Ibadan', 'Ibadan', 'Oyo', '2025-11-15 19:08:15', '2025-11-16 09:51:34'),
(4, 'Ahmadu Bello University, Zaria', 'Zaria', 'Kaduna', '2025-11-15 19:08:15', '2025-11-16 09:49:54'),
(5, 'University of Nigeria, Nsukka', 'Nsukka', 'Enugu', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(11, 'Federal University of Technology, Akure', '', '', '2025-11-16 09:52:52', '2025-11-16 09:52:52');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `icon_url` varchar(500) DEFAULT NULL,
  `parent_category_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `icon_url`, `parent_category_id`, `created_at`, `updated_at`) VALUES
(1, 'Gadgets', 'fas fa-mobile-alt', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(2, 'Electronics', 'fas fa-tv', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(3, 'Apartments', 'fas fa-home', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(4, 'Food', 'fas fa-utensils', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(5, 'Beauty & Cosmetics', 'fas fa-spray-can', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(6, 'Vehicles', 'fas fa-car', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(7, 'Animals & Pets', 'fas fa-paw', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(8, 'Entertainment', 'fas fa-gamepad', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(9, 'Services & Freelance', 'fas fa-briefcase', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(10, 'Books & Stationery', 'fas fa-book', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(11, 'Academic Helps', 'fas fa-graduation-cap', NULL, '2025-11-15 19:08:15', '2025-11-16 09:53:25'),
(12, 'Fashion & Clothing', 'fas fa-tshirt', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(13, 'Sports & Fitness', 'fas fa-dumbbell', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(14, 'Health & Wellness', 'fas fa-heartbeat', NULL, '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(15, 'Shoes', 'fas fa-graduation-cap', NULL, '2025-11-16 09:53:45', '2025-11-16 09:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `chats`
--

CREATE TABLE `chats` (
  `chat_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chats`
--

INSERT INTO `chats` (`chat_id`, `listing_id`, `buyer_id`, `seller_id`, `last_message_at`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, '2025-11-16 10:11:05', '2025-11-16 10:04:58', '2025-11-16 10:11:05');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`, `updated_at`) VALUES
(1, 'Computer Science', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(2, 'Medicine', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(3, 'Civil Engineering', '2025-11-15 19:08:15', '2025-11-16 09:54:38'),
(4, 'Law', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(5, 'Business Administration', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(6, 'Mass Communication', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(7, 'Pharmacy', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(8, 'Nursing', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(9, 'Architecture', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(10, 'Economics', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(21, 'Mechanical Engineering', '2025-11-16 09:54:17', '2025-11-16 09:54:17');

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`level_id`, `level_name`, `created_at`, `updated_at`) VALUES
(1, '100L', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(2, '200L', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(3, '300L', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(4, '400L', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),
(5, '500L', '2025-11-15 19:08:15', '2025-11-15 19:08:15'),

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `listing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `is_free` tinyint(1) DEFAULT 0,
  `is_available_today` tinyint(1) DEFAULT 0,
  `quantity_available` int(11) DEFAULT 1,
  `condition_status` enum('New','Like New','Used - Good','Used - Fair','For Parts/Repair') NOT NULL,
  `location_description` varchar(500) DEFAULT NULL,
  `status` enum('active','sold','hidden','deleted') DEFAULT 'active',
  `views_count` int(11) DEFAULT 0,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`listing_id`, `user_id`, `category_id`, `title`, `description`, `price`, `is_free`, `is_available_today`, `quantity_available`, `condition_status`, `location_description`, `status`, `views_count`, `posted_at`, `sold_at`, `created_at`, `updated_at`) VALUES
(1, 1, 10, 'Book', 'Nice looking', 2000.00, 0, 0, 3, 'Like New', 'Block C, Room 22', 'active', 18, '2025-11-15 19:52:28', '2025-11-15 19:55:37', '2025-11-15 19:52:28', '2025-11-16 10:01:00'),
(2, 1, 5, 'phone', 'no description', 0.00, 1, 0, 2, 'Like New', 'block D, Room 22', 'active', 1, '2025-11-16 07:54:58', NULL, '2025-11-16 07:54:58', '2025-11-16 07:54:58'),
(3, 1, 7, 'laptop', 'very good laptop', 6500.00, 0, 1, 3, 'Like New', 'block D, Room 22', 'active', 7, '2025-11-16 07:56:40', NULL, '2025-11-16 07:56:40', '2025-11-16 10:33:53'),
(4, 1, 5, 'Gaming Table', 'implement the user training section with real training contents and resources. let each training navigate to appropriate training resources.\r\n\r\nThe imersive devotion implement the user training section with real training contents and resources. let each training navigate to appropriate training resources', 7500.00, 0, 1, 4, 'Used - Good', 'block D, Room 22', 'active', 6, '2025-11-16 07:59:08', NULL, '2025-11-16 07:59:08', '2025-11-16 09:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `listing_images`
--

CREATE TABLE `listing_images` (
  `image_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listing_images`
--

INSERT INTO `listing_images` (`image_id`, `listing_id`, `image_url`, `is_primary`, `created_at`) VALUES
(1, 1, 'assets/uploads/listings/6918d9fc6d750_1763236348.png', 1, '2025-11-15 19:52:28'),
(2, 1, 'assets/uploads/listings/6918e3554e065_1763238741.png', 0, '2025-11-15 20:32:21'),
(3, 1, 'assets/uploads/listings/6918e3555151f_1763238741.png', 0, '2025-11-15 20:32:21'),
(4, 1, 'assets/uploads/listings/6918e355532ed_1763238741.png', 0, '2025-11-15 20:32:21'),
(5, 1, 'assets/uploads/listings/6918e35554461_1763238741.jpg', 0, '2025-11-15 20:32:21'),
(6, 2, 'assets/uploads/listings/6919835214c27_1763279698.jpg', 1, '2025-11-16 07:54:58'),
(7, 3, 'assets/uploads/listings/691983b8dc396_1763279800.png', 1, '2025-11-16 07:56:40'),
(8, 4, 'assets/uploads/listings/6919844c8ab39_1763279948.jpg', 1, '2025-11-16 07:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `listing_tags`
--

CREATE TABLE `listing_tags` (
  `tag_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `tag_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listing_tags`
--

INSERT INTO `listing_tags` (`tag_id`, `listing_id`, `tag_name`, `created_at`) VALUES
(3, 1, 'gaming', '2025-11-15 20:32:21'),
(4, 1, 'laptop', '2025-11-15 20:32:21'),
(5, 2, 'gaming', '2025-11-16 07:54:58'),
(6, 2, 'laptop', '2025-11-16 07:54:58'),
(7, 3, 'book', '2025-11-16 07:56:40'),
(8, 3, 'biro', '2025-11-16 07:56:40'),
(9, 4, 'book', '2025-11-16 07:59:08'),
(10, 4, 'biro', '2025-11-16 07:59:08'),
(11, 4, 'food', '2025-11-16 07:59:08'),
(12, 4, 'phone', '2025-11-16 07:59:08'),
(13, 4, 'fan', '2025-11-16 07:59:08'),
(14, 4, 'TV', '2025-11-16 07:59:08'),
(15, 4, 'Block', '2025-11-16 07:59:08');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found`
--

CREATE TABLE `lost_found` (
  `lost_found_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_type` enum('lost','found') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `location_lost_found` varchar(500) NOT NULL,
  `date_lost_found` date DEFAULT NULL,
  `status` enum('active','resolved') DEFAULT 'active',
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lost_found`
--

INSERT INTO `lost_found` (`lost_found_id`, `user_id`, `item_type`, `item_name`, `description`, `location_lost_found`, `date_lost_found`, `status`, `posted_at`, `created_at`, `updated_at`) VALUES
(3, 1, 'lost', 'Tea cup and Bag', 'no', 'Block CA', '2025-11-05', 'active', '2025-11-16 08:51:08', '2025-11-16 08:51:08', '2025-11-16 08:51:08'),
(4, 1, 'found', 'Ball and case', 'the case on', 'Akure, Nigeria', '2025-11-14', 'active', '2025-11-16 08:51:59', '2025-11-16 08:51:59', '2025-11-16 08:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found_images`
--

CREATE TABLE `lost_found_images` (
  `image_id` int(11) NOT NULL,
  `lost_found_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lost_found_images`
--

INSERT INTO `lost_found_images` (`image_id`, `lost_found_id`, `image_url`, `created_at`) VALUES
(1, 3, 'assets/uploads/lost_found/1763283068_6919907c244cd_logox.png', '2025-11-16 08:51:08'),
(2, 4, 'assets/uploads/lost_found/1763283119_691990af1aef8_bfn packages.png', '2025-11-16 08:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `lost_found_items`
--

CREATE TABLE `lost_found_items` (
  `item_id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `last_seen_found_location` text NOT NULL,
  `owner_contact` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_lost_found` date DEFAULT NULL,
  `is_resolved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meetpoint_suggestions`
--

CREATE TABLE `meetpoint_suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `meet_point_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','accepted','rejected','rescheduled') DEFAULT 'pending',
  `suggested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meet_points`
--

CREATE TABLE `meet_points` (
  `meet_point_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `point_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `campus_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `chat_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `meetpoint_suggestion_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `chat_id`, `sender_id`, `message_text`, `meetpoint_suggestion_id`, `is_read`, `sent_at`) VALUES
(1, 1, 2, 'hello', NULL, 1, '2025-11-16 10:05:16'),
(2, 1, 2, 'i want to learn more about your product CSRF protection', NULL, 1, '2025-11-16 10:05:52'),
(3, 1, 1, 'Expand the following information to create a comprehensive modern website structure and content for an NGO (Foundation) that is passionate about rebuilding Nigeria and empowering people across the country through various empowerment programs including skill acquisition, sensitization,', NULL, 0, '2025-11-16 10:10:26'),
(4, 1, 2, 'how are you sure about this product', NULL, 1, '2025-11-16 10:11:05');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL,
  `listing_id` int(11) DEFAULT NULL,
  `reported_user_id` int(11) DEFAULT NULL,
  `report_type` enum('fraud','item_sold','offensive','misleading','spam','other') NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','action_taken') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `saved_items`
--

CREATE TABLE `saved_items` (
  `saved_item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller_ratings`
--

CREATE TABLE `seller_ratings` (
  `rating_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `listing_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `rated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `whatsapp_link` varchar(500) DEFAULT NULL,
  `profile_picture_url` varchar(500) DEFAULT 'assets/images/default-avatar.png',
  `student_id_passport_url` varchar(500) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `level_id` int(11) DEFAULT NULL,
  `campus_id` int(11) NOT NULL,
  `current_state` varchar(100) DEFAULT NULL,
  `current_city` varchar(100) DEFAULT NULL,
  `preferred_meetpoints` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `total_ratings` int(11) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_admin` tinyint(1) DEFAULT 0,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone_number`, `whatsapp_link`, `profile_picture_url`, `student_id_passport_url`, `department_id`, `level_id`, `campus_id`, `bio`, `average_rating`, `total_ratings`, `is_verified`, `is_active`, `is_admin`, `last_login_at`, `google_id`, `created_at`, `updated_at`) VALUES
(1, 'Godwin', 'Ogbaji', 'ogbajigodwin@gmail.com', '$2y$12$wFSiCJrKDXzf8NnnrQAa7eQL.4YFpjUjpmvdsoAEaWHKn6u77JY1m', '08032318588', 'https://wa.me/2348032318588', 'assets/uploads/profiles/6918d8ee414d0_1763236078.jpg', NULL, 5, 5, 4, NULL, 0.00, 0, 0, 1, 1, '2025-11-16 09:30:20', NULL, '2025-11-15 19:47:58', '2025-11-16 09:30:20'),
(2, 'Peter', 'Akinnubi', 'paamintlorg@gmail.com', '$2y$12$ClA5oqrSF/XJG.FOjCNo3eVEtOxfTSx8sjgK2QpeE1.8CSZvVgF2G', '08054704146', 'https://wa.me/2348054704146', 'assets/uploads/profiles/691997b93ec32_1763284921.png', NULL, 6, 3, 3, NULL, 0.00, 0, 0, 1, 0, '2025-11-16 09:22:01', NULL, '2025-11-16 09:22:01', '2025-11-16 09:22:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`bookmark_id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`listing_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_listing` (`listing_id`);

--
-- Indexes for table `campuses`
--
ALTER TABLE `campuses`
  ADD PRIMARY KEY (`campus_id`),
  ADD KEY `idx_campus_name` (`campus_name`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `idx_category_name` (`category_name`),
  ADD KEY `idx_parent_category` (`parent_category_id`);

--
-- Indexes for table `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `idx_buyer` (`buyer_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_listing` (`listing_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD KEY `idx_dept_name` (`department_name`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level_id`),
  ADD UNIQUE KEY `level_name` (`level_name`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`listing_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_posted_at` (`posted_at`),
  ADD KEY `idx_is_free` (`is_free`),
  ADD KEY `idx_is_available_today` (`is_available_today`);
ALTER TABLE `listings` ADD FULLTEXT KEY `idx_search` (`title`,`description`);

--
-- Indexes for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_listing` (`listing_id`),
  ADD KEY `idx_primary` (`is_primary`);

--
-- Indexes for table `listing_tags`
--
ALTER TABLE `listing_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD KEY `idx_listing` (`listing_id`),
  ADD KEY `idx_tag_name` (`tag_name`);

--
-- Indexes for table `lost_found`
--
ALTER TABLE `lost_found`
  ADD PRIMARY KEY (`lost_found_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_type` (`item_type`),
  ADD KEY `idx_status` (`status`);
ALTER TABLE `lost_found` ADD FULLTEXT KEY `idx_search` (`item_name`,`description`);

--
-- Indexes for table `lost_found_images`
--
ALTER TABLE `lost_found_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `idx_lost_found` (`lost_found_id`);

--
-- Indexes for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_poster` (`poster_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_resolved` (`is_resolved`);
ALTER TABLE `lost_found_items` ADD FULLTEXT KEY `idx_search` (`item_name`,`description`);

--
-- Indexes for table `meetpoint_suggestions`
--
ALTER TABLE `meetpoint_suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `idx_chat` (`chat_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `meet_points`
--
ALTER TABLE `meet_points`
  ADD PRIMARY KEY (`meet_point_id`),
  ADD KEY `campus_id` (`campus_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `idx_chat` (`chat_id`),
  ADD KEY `idx_sender` (`sender_id`),
  ADD KEY `idx_sent_at` (`sent_at`),
  ADD KEY `meetpoint_suggestion_id` (`meetpoint_suggestion_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `reported_user_id` (`reported_user_id`),
  ADD KEY `idx_reporter` (`reporter_id`),
  ADD KEY `idx_listing` (`listing_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD PRIMARY KEY (`saved_item_id`),
  ADD UNIQUE KEY `unique_save` (`user_id`,`listing_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_listing` (`listing_id`);

--
-- Indexes for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD PRIMARY KEY (`rating_id`),
  ADD UNIQUE KEY `unique_rating` (`buyer_id`,`listing_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_listing` (`listing_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `level_id` (`level_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_campus` (`campus_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `bookmark_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `campuses`
--
ALTER TABLE `campuses`
  MODIFY `campus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `chats`
--
ALTER TABLE `chats`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `listing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `listing_images`
--
ALTER TABLE `listing_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `listing_tags`
--
ALTER TABLE `listing_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `lost_found`
--
ALTER TABLE `lost_found`
  MODIFY `lost_found_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lost_found_images`
--
ALTER TABLE `lost_found_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meetpoint_suggestions`
--
ALTER TABLE `meetpoint_suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meet_points`
--
ALTER TABLE `meet_points`
  MODIFY `meet_point_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `saved_items`
--
ALTER TABLE `saved_items`
  MODIFY `saved_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  MODIFY `rating_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `listings`
--
ALTER TABLE `listings`
  ADD CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `listings_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `listing_images`
--
ALTER TABLE `listing_images`
  ADD CONSTRAINT `listing_images_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `listing_tags`
--
ALTER TABLE `listing_tags`
  ADD CONSTRAINT `listing_tags_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_found`
--
ALTER TABLE `lost_found`
  ADD CONSTRAINT `lost_found_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_found_images`
--
ALTER TABLE `lost_found_images`
  ADD CONSTRAINT `lost_found_images_ibfk_1` FOREIGN KEY (`lost_found_id`) REFERENCES `lost_found` (`lost_found_id`) ON DELETE CASCADE;

--
-- Constraints for table `lost_found_items`
--
ALTER TABLE `lost_found_items`
  ADD CONSTRAINT `lost_found_items_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `lost_found_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `meetpoint_suggestions`
--
ALTER TABLE `meetpoint_suggestions`
  ADD CONSTRAINT `meetpoint_suggestions_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`chat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meetpoint_suggestions_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meet_points`
--
ALTER TABLE `meet_points`
  ADD CONSTRAINT `meet_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `meet_points_ibfk_2` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`campus_id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`chat_id`) REFERENCES `chats` (`chat_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`meetpoint_suggestion_id`) REFERENCES `meetpoint_suggestions` (`suggestion_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`meetpoint_suggestion_id`) REFERENCES `meetpoint_suggestions` (`suggestion_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_ibfk_5` FOREIGN KEY (`meetpoint_suggestion_id`) REFERENCES `meetpoint_suggestions` (`suggestion_id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`reported_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `saved_items`
--
ALTER TABLE `saved_items`
  ADD CONSTRAINT `saved_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_items_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_ratings`
--
ALTER TABLE `seller_ratings`
  ADD CONSTRAINT `seller_ratings_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_ratings_ibfk_2` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_ratings_ibfk_3` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`level_id`) REFERENCES `levels` (`level_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`campus_id`) REFERENCES `campuses` (`campus_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
