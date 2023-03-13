-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 13, 2023 at 03:41 AM
-- Server version: 8.0.32-0ubuntu0.20.04.2
-- PHP Version: 7.4.3-4ubuntu2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `contacts`
--

-- --------------------------------------------------------

--
-- Table structure for table `bulk_uploads`
--

CREATE TABLE `bulk_uploads` (
  `id` bigint UNSIGNED NOT NULL,
  `contact_group_id` bigint UNSIGNED DEFAULT NULL,
  `original_name` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `new_name` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `extension` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `size` int UNSIGNED NOT NULL DEFAULT '0',
  `mime` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `comments` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint UNSIGNED NOT NULL,
  `bulk_upload_id` bigint UNSIGNED DEFAULT NULL,
  `surname` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `other_names` varchar(254) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `comments` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_groups`
--

CREATE TABLE `contact_groups` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `comments` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_group_mapping`
--

CREATE TABLE `contact_group_mapping` (
  `id` bigint UNSIGNED NOT NULL,
  `contact_id` bigint UNSIGNED NOT NULL,
  `group_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forgot`
--

CREATE TABLE `forgot` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `token` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `comments` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messaging`
--

CREATE TABLE `messaging` (
  `id` bigint UNSIGNED NOT NULL,
  `contact_group_id` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messaging_contact_mapping`
--

CREATE TABLE `messaging_contact_mapping` (
  `id` bigint UNSIGNED NOT NULL,
  `messaging_id` bigint UNSIGNED NOT NULL,
  `contact_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `table_name` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module`, `slug`, `table_name`, `description`, `created_by`, `created_at`, `updated_by`) VALUES
(1, 'Auth', 'auth', NULL, 'Authentication: Login, register, forgot and logout', 1, '2023-03-10 07:18:53', 1),
(2, 'Session', 'session', 'sessions', 'Logged in user sessions', 1, '2023-03-10 07:18:53', 1),
(3, 'Dashboard', 'dashboard', NULL, 'The dashboard page', 1, '2023-03-10 07:18:53', 1),
(4, 'Profile', 'profile', NULL, 'The user profile page', 1, '2023-03-10 07:18:53', 1),
(5, 'Module', 'module', 'modules', 'Application modules', 1, '2023-03-10 07:18:53', 1),
(6, 'Status', 'status', 'status', 'Application status', 1, '2023-03-10 07:18:53', 1),
(7, 'User', 'user', 'users', 'Application users', 1, '2023-03-10 07:18:53', 1),
(8, 'Role', 'role', 'roles', 'Application roles', 1, '2023-03-10 07:18:53', 1),
(9, 'Permission', 'permission', 'permissions', 'Application permissions', 1, '2023-03-10 07:18:53', 1),
(10, 'Forgot', 'forgot', 'forgot', 'Password reset requests', 1, '2023-03-10 07:18:53', 1),
(11, 'Group', 'group', 'group', 'Contact groups', 1, '2023-03-10 07:18:53', 1),
(12, 'Contact', 'contact', 'contact', 'Contacts', 1, '2023-03-10 07:18:53', 1),
(13, 'Messaging', 'messaging', 'messaging', 'messaging', 1, '2023-03-10 07:18:53', 1),
(14, 'Upload', 'upload', 'bulk_uploads', 'upload', 1, '2023-03-10 07:18:53', 1);

-- --------------------------------------------------------

--
-- Table structure for table `module_status_mapping`
--

CREATE TABLE `module_status_mapping` (
  `id` bigint UNSIGNED NOT NULL,
  `module_id` bigint UNSIGNED NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `module_status_mapping`
--

INSERT INTO `module_status_mapping` (`id`, `module_id`, `status_id`, `created_by`, `created_at`) VALUES
(1, 2, 1, 1, '2023-03-10 10:44:36'),
(2, 2, 7, 1, '2023-03-10 10:44:36'),
(3, 7, 1, 1, '2023-03-10 10:44:36'),
(4, 7, 3, 1, '2023-03-10 10:44:36'),
(5, 7, 4, 1, '2023-03-10 10:44:36'),
(6, 7, 5, 1, '2023-03-10 10:44:36'),
(7, 7, 6, 1, '2023-03-10 10:44:36'),
(8, 8, 1, 1, '2023-03-10 10:44:36'),
(9, 8, 2, 1, '2023-03-10 10:44:36'),
(10, 8, 4, 1, '2023-03-10 10:44:36'),
(11, 9, 1, 1, '2023-03-10 10:44:36'),
(12, 9, 2, 1, '2023-03-10 10:44:36'),
(13, 9, 4, 1, '2023-03-10 10:44:36'),
(14, 2, 8, 1, '2023-03-10 12:34:37'),
(15, 10, 1, 1, '2023-03-10 22:39:56'),
(16, 10, 7, 1, '2023-03-10 22:39:56'),
(17, 10, 9, 1, '2023-03-10 22:39:56');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `module_id` bigint UNSIGNED NOT NULL,
  `permission` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module_id`, `permission`, `status_id`, `created_by`, `created_at`) VALUES
(1, 2, 'Can view sessions', 1, 1, '2023-03-11 11:26:48'),
(2, 2, 'Can export sessions', 1, 1, '2023-03-11 11:26:48'),
(3, 7, 'Can view users', 1, 1, '2023-03-11 11:26:48'),
(4, 7, 'Can add users', 1, 1, '2023-03-11 11:26:48'),
(5, 7, 'Can edit users', 1, 1, '2023-03-11 11:26:48'),
(6, 7, 'Can delete users', 1, 1, '2023-03-11 11:26:48'),
(7, 7, 'Can enable users', 1, 1, '2023-03-11 11:26:48'),
(8, 7, 'Can disable users', 1, 1, '2023-03-11 11:26:48'),
(9, 7, 'Can close users', 1, 1, '2023-03-11 11:26:48'),
(10, 7, 'Can export users', 1, 1, '2023-03-11 11:26:48'),
(11, 7, 'Can manage user roles', 1, 1, '2023-03-11 11:26:48'),
(12, 8, 'Can view roles', 1, 1, '2023-03-11 11:26:48'),
(13, 8, 'Can add roles', 1, 1, '2023-03-11 11:26:48'),
(14, 8, 'Can edit roles', 1, 1, '2023-03-11 11:26:48'),
(15, 8, 'Can delete roles', 1, 1, '2023-03-11 11:26:48'),
(16, 8, 'Can enable roles', 1, 1, '2023-03-11 11:26:48'),
(17, 8, 'Can disable roles', 1, 1, '2023-03-11 11:26:48'),
(18, 8, 'Can export roles', 1, 1, '2023-03-11 11:26:48'),
(19, 8, 'Can manage role permissions', 1, 1, '2023-03-11 11:26:48'),
(20, 8, 'Can manage role users', 1, 1, '2023-03-11 11:26:48'),
(21, 9, 'Can view permissions', 1, 1, '2023-03-11 11:26:48'),
(22, 9, 'Can enable permissions', 1, 1, '2023-03-11 11:26:48'),
(23, 9, 'Can disable permissions', 1, 1, '2023-03-11 11:26:48'),
(24, 9, 'Can export permissions', 1, 1, '2023-03-11 11:26:48'),
(25, 11, 'Can view groups', 1, 1, '2023-03-11 11:26:48'),
(26, 11, 'Can add groups', 1, 1, '2023-03-11 11:26:48'),
(27, 11, 'Can edit groups', 1, 1, '2023-03-11 11:26:48'),
(28, 11, 'Can delete groups', 1, 1, '2023-03-11 11:26:48'),
(29, 11, 'Can enable groups', 1, 1, '2023-03-11 11:26:48'),
(30, 11, 'Can disable groups', 1, 1, '2023-03-11 11:26:48'),
(31, 11, 'Can export groups', 1, 1, '2023-03-11 11:26:48'),
(32, 11, 'Can manage group contacts', 1, 1, '2023-03-11 11:26:48'),
(33, 11, 'Can message all contacts in a group', 1, 1, '2023-03-11 11:26:48'),
(34, 12, 'Can view contacts', 1, 1, '2023-03-11 11:26:48'),
(35, 12, 'Can add contacts', 1, 1, '2023-03-11 11:26:48'),
(36, 12, 'Can edit contacts', 1, 1, '2023-03-11 11:26:48'),
(37, 12, 'Can delete contacts', 1, 1, '2023-03-11 11:26:48'),
(38, 12, 'Can enable contacts', 1, 1, '2023-03-11 11:26:48'),
(39, 12, 'Can disable contacts', 1, 1, '2023-03-11 11:26:48'),
(40, 12, 'Can export contacts', 1, 1, '2023-03-11 11:26:48'),
(41, 12, 'Can bulk upload contacts', 1, 1, '2023-03-11 11:26:48'),
(42, 12, 'Can manage contact groups', 1, 1, '2023-03-11 11:26:48'),
(43, 12, 'Can message a contact', 1, 1, '2023-03-11 11:26:48'),
(44, 13, 'Can view sent messages', 1, 1, '2023-03-11 11:26:48'),
(45, 13, 'Can export sent messages', 1, 1, '2023-03-11 11:26:48'),
(46, 14, 'Can view uploads', 1, 1, '2023-03-11 11:26:48'),
(47, 14, 'Can export uploads', 1, 1, '2023-03-11 11:26:48'),
(48, 14, 'Can download uploaded files', 1, 1, '2023-03-11 11:26:48');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `role` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role`, `description`, `status_id`, `created_by`, `created_at`, `updated_by`) VALUES
(1, 'Super Admin', 'Has all permissions', 1, 1, '2023-03-10 11:39:40', 1),
(2, 'Admin', 'Administrates the application - manages users, roles and permissions', 1, 1, '2023-03-10 11:39:40', 1),
(3, 'Auditor', 'View only permissions across the whole application', 1, 1, '2023-03-10 11:39:40', 1);

-- --------------------------------------------------------

--
-- Table structure for table `role_permission_mapping`
--

CREATE TABLE `role_permission_mapping` (
  `id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permission_mapping`
--

INSERT INTO `role_permission_mapping` (`id`, `role_id`, `permission_id`, `created_by`, `created_at`) VALUES
(1, 1, 1, 1, '2023-03-11 02:24:43'),
(2, 1, 2, 1, '2023-03-11 02:24:43'),
(3, 1, 3, 1, '2023-03-11 02:24:43'),
(4, 1, 4, 1, '2023-03-11 02:24:43'),
(5, 1, 5, 1, '2023-03-11 02:24:43'),
(6, 1, 6, 1, '2023-03-11 02:24:43'),
(7, 1, 7, 1, '2023-03-11 02:24:43'),
(8, 1, 8, 1, '2023-03-11 02:24:43'),
(9, 1, 9, 1, '2023-03-11 02:24:43'),
(10, 1, 10, 1, '2023-03-11 02:24:43'),
(11, 1, 11, 1, '2023-03-11 02:24:43'),
(12, 1, 12, 1, '2023-03-11 02:24:43'),
(13, 1, 13, 1, '2023-03-11 02:24:43'),
(14, 1, 14, 1, '2023-03-11 02:24:43'),
(15, 1, 15, 1, '2023-03-11 02:24:43'),
(16, 1, 16, 1, '2023-03-11 02:24:43'),
(17, 1, 17, 1, '2023-03-11 02:24:43'),
(18, 1, 18, 1, '2023-03-11 02:24:43'),
(19, 1, 19, 1, '2023-03-11 02:24:43'),
(20, 1, 20, 1, '2023-03-11 02:24:43'),
(21, 1, 21, 1, '2023-03-11 02:24:43'),
(22, 1, 22, 1, '2023-03-11 02:24:43'),
(23, 1, 23, 1, '2023-03-11 02:24:43'),
(24, 1, 24, 1, '2023-03-11 02:24:43'),
(25, 1, 25, 1, '2023-03-11 02:24:43'),
(26, 1, 26, 1, '2023-03-11 02:24:43'),
(27, 1, 27, 1, '2023-03-11 02:24:43'),
(28, 1, 28, 1, '2023-03-11 02:24:43'),
(29, 1, 29, 1, '2023-03-11 02:24:43'),
(30, 1, 30, 1, '2023-03-11 07:11:50'),
(31, 1, 31, 1, '2023-03-11 07:11:50'),
(32, 1, 32, 1, '2023-03-11 07:11:50'),
(33, 1, 33, 1, '2023-03-11 07:11:50'),
(34, 1, 34, 1, '2023-03-11 07:11:50'),
(35, 1, 35, 1, '2023-03-11 07:11:50'),
(36, 1, 36, 1, '2023-03-11 07:12:05'),
(37, 1, 37, 1, '2023-03-11 07:12:05'),
(38, 1, 38, 1, '2023-03-11 07:12:05'),
(39, 1, 39, 1, '2023-03-11 07:12:13'),
(40, 1, 40, 1, '2023-03-11 07:12:13'),
(41, 1, 41, 1, '2023-03-11 07:12:13'),
(42, 1, 42, 1, '2023-03-11 07:12:13'),
(43, 1, 43, 1, '2023-03-11 07:12:13'),
(44, 1, 44, 1, '2023-03-11 07:12:13'),
(45, 1, 45, 1, '2023-03-11 07:12:13'),
(46, 1, 46, 1, '2023-03-11 07:12:13'),
(47, 1, 47, 1, '2023-03-11 07:12:13'),
(48, 1, 48, 1, '2023-03-11 07:12:13'),
(49, 2, 25, 1, '2023-03-13 00:55:04'),
(50, 2, 26, 1, '2023-03-13 00:55:04'),
(51, 2, 27, 1, '2023-03-13 00:55:04'),
(52, 2, 28, 1, '2023-03-13 00:55:04'),
(53, 2, 29, 1, '2023-03-13 00:55:04'),
(54, 2, 30, 1, '2023-03-13 00:55:04'),
(55, 2, 31, 1, '2023-03-13 00:55:04'),
(56, 2, 32, 1, '2023-03-13 00:55:04'),
(57, 2, 33, 1, '2023-03-13 00:55:04'),
(58, 2, 34, 1, '2023-03-13 00:55:04'),
(59, 2, 35, 1, '2023-03-13 00:55:04'),
(60, 2, 36, 1, '2023-03-13 00:55:04'),
(61, 2, 37, 1, '2023-03-13 00:55:04'),
(62, 2, 38, 1, '2023-03-13 00:55:04'),
(63, 2, 39, 1, '2023-03-13 00:55:04'),
(64, 2, 40, 1, '2023-03-13 00:55:04'),
(65, 2, 41, 1, '2023-03-13 00:55:04'),
(66, 2, 42, 1, '2023-03-13 00:55:04'),
(67, 2, 43, 1, '2023-03-13 00:55:04'),
(68, 2, 44, 1, '2023-03-13 00:55:04'),
(69, 2, 45, 1, '2023-03-13 00:55:04'),
(70, 2, 46, 1, '2023-03-13 00:55:04'),
(71, 2, 47, 1, '2023-03-13 00:55:04'),
(72, 2, 48, 1, '2023-03-13 00:55:04'),
(73, 3, 1, 1, '2023-03-13 00:55:49'),
(74, 3, 2, 1, '2023-03-13 00:55:49'),
(75, 3, 3, 1, '2023-03-13 00:55:49'),
(76, 3, 10, 1, '2023-03-13 00:55:49'),
(77, 3, 12, 1, '2023-03-13 00:55:49'),
(78, 3, 18, 1, '2023-03-13 00:55:49'),
(79, 3, 21, 1, '2023-03-13 00:55:49'),
(80, 3, 24, 1, '2023-03-13 00:55:49'),
(81, 3, 25, 1, '2023-03-13 00:55:49'),
(82, 3, 31, 1, '2023-03-13 00:55:49'),
(83, 3, 34, 1, '2023-03-13 00:55:49'),
(84, 3, 40, 1, '2023-03-13 00:55:49'),
(85, 3, 44, 1, '2023-03-13 00:55:49'),
(86, 3, 45, 1, '2023-03-13 00:55:49'),
(87, 3, 46, 1, '2023-03-13 00:55:49'),
(88, 3, 47, 1, '2023-03-13 00:55:49'),
(89, 3, 48, 1, '2023-03-13 00:55:49');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint DEFAULT NULL,
  `ip_address` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `session` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `logged_in_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status_id` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `status`, `slug`, `description`, `created_by`, `created_at`, `updated_by`) VALUES
(1, 'Active', 'active', 'For active records', 1, '2023-03-10 06:58:58', 1),
(2, 'Inactive', 'inactive', 'For inactive records', 1, '2023-03-10 06:58:58', 1),
(3, 'Created', 'created', 'For created records that have not been activated yet', 1, '2023-03-10 06:58:58', 1),
(4, 'Deleted', 'deleted', 'For deleted records. Kept for audit purposes', 1, '2023-03-10 06:58:58', 1),
(5, 'Dormant', 'dormant', 'For dormant records', 1, '2023-03-10 06:58:58', 1),
(6, 'Closed', 'closed', 'For closed records', 1, '2023-03-10 06:58:58', 1),
(7, 'Expired', 'expired', 'For expired records', 1, '2023-03-10 06:58:58', 1),
(8, 'Logged Out', 'logged-out', 'For logged out sessions', 1, '2023-03-10 06:58:58', 1),
(9, 'Used', 'used', 'Used tokens', 1, '2023-03-10 06:58:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status_id` bigint UNSIGNED NOT NULL,
  `comments` varchar(254) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_count` int UNSIGNED NOT NULL DEFAULT '0',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_by` bigint UNSIGNED NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `status_id`, `comments`, `login_count`, `created_by`, `created_at`, `updated_by`) VALUES
(1, 'superadmin', 'superadmin@email.com', '$2y$10$CbGLkKAxF/jo5BbnMo8LDOLGyJQllnFjv2GK1nla.cBD4BeNEo8RC', 1, NULL, 3, 1, '2023-03-13 00:50:49', 1),
(2, 'admin', 'admin@email.com', '$2y$10$V7KDy.W53u0wD3SKiPdwp.q5rTcsqfWsuWoFBBLYqFqP4CltLhbpi', 1, NULL, 1, 1, '2023-03-13 00:50:49', 1),
(3, 'auditor', 'auditor@email.com', '$2y$10$JA84ZObZXJXOP5LSsQCzdefFhd3cq2NZKDzTvt9JBAK.vxbxQyFrO', 1, NULL, 2, 1, '2023-03-13 00:50:49', 1),
(4, 'user', 'user@email.com', '$2y$10$W4aB1pxwWYFA8Ir6eb2Tiel0zJTv0esu6P8Whwk0uYvfrWDMfv9GC', 1, NULL, 2, 1, '2023-03-13 00:50:49', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_role_mapping`
--

CREATE TABLE `user_role_mapping` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_role_mapping`
--

INSERT INTO `user_role_mapping` (`id`, `user_id`, `role_id`, `created_by`, `created_at`) VALUES
(1, 1, 1, 1, '2023-03-13 00:51:49'),
(2, 2, 2, 1, '2023-03-13 00:51:49'),
(3, 3, 3, 1, '2023-03-13 00:51:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bulk_uploads`
--
ALTER TABLE `bulk_uploads`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_groups`
--
ALTER TABLE `contact_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_group_mapping`
--
ALTER TABLE `contact_group_mapping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forgot`
--
ALTER TABLE `forgot`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `messaging`
--
ALTER TABLE `messaging`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messaging_contact_mapping`
--
ALTER TABLE `messaging_contact_mapping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module` (`module`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `module_status_mapping`
--
ALTER TABLE `module_status_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_status_id` (`module_id`,`status_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission` (`permission`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role` (`role`);

--
-- Indexes for table `role_permission_mapping`
--
ALTER TABLE `role_permission_mapping`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_id` (`role_id`,`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session` (`session`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `status` (`status`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_role_mapping`
--
ALTER TABLE `user_role_mapping`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bulk_uploads`
--
ALTER TABLE `bulk_uploads`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_groups`
--
ALTER TABLE `contact_groups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_group_mapping`
--
ALTER TABLE `contact_group_mapping`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forgot`
--
ALTER TABLE `forgot`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messaging`
--
ALTER TABLE `messaging`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messaging_contact_mapping`
--
ALTER TABLE `messaging_contact_mapping`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `module_status_mapping`
--
ALTER TABLE `module_status_mapping`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permission_mapping`
--
ALTER TABLE `role_permission_mapping`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_role_mapping`
--
ALTER TABLE `user_role_mapping`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
