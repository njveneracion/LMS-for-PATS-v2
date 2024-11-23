-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2024 at 08:18 PM
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
-- Database: `pats`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_us`
--

INSERT INTO `about_us` (`id`, `content`) VALUES
(1, 'Others who use this device won’t see your activity, so you can browse more privately. This wont change how data is collected by websites you visit and the services they use, including Google. Downloads, bookmarks and reading list items will be saved. ');

-- --------------------------------------------------------

--
-- Table structure for table `active_sessions`
--

CREATE TABLE `active_sessions` (
  `session_id` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `active_sessions`
--

INSERT INTO `active_sessions` (`session_id`, `user_id`, `last_activity`) VALUES
('l10r6l9so5bc6ap14iv7jnto0c', 1, '2024-11-23 19:02:26');

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `description`, `created_at`) VALUES
(905, 1, 'Login', 'User logged in successfully!', '2024-09-16 14:55:03'),
(915, 1, 'Login', 'User logged in successfully!', '2024-09-17 09:49:26'),
(916, 1, 'Logout', 'User logged out successfully.', '2024-09-17 09:51:03'),
(917, 1, 'Login', 'User logged in successfully!', '2024-10-27 10:26:33'),
(918, 1, 'Logout', 'User logged out successfully.', '2024-10-27 10:28:22'),
(919, 1, 'Login', 'User logged in successfully!', '2024-10-27 10:29:17'),
(920, 1, 'Logout', 'User logged out successfully.', '2024-10-27 10:29:38'),
(925, 99, 'Login', 'User logged in successfully!', '2024-10-27 10:42:41'),
(926, 99, 'Add Learning Material', 'Added new learning material: sdwdwd', '2024-10-27 10:43:51'),
(927, 99, 'Add Quiz', 'Added new quiz: Quiz No.1', '2024-10-27 10:44:00'),
(928, 99, 'Added a question to the quiz', 'Quiz No.1', '2024-10-27 10:44:14'),
(929, 99, 'Logout', 'User logged out successfully.', '2024-10-27 10:45:10'),
(932, 99, 'Login', 'User logged in successfully!', '2024-10-27 10:48:39'),
(933, 99, 'Logout', 'User logged out successfully.', '2024-10-27 10:48:53'),
(937, 99, 'Login', 'User logged in successfully!', '2024-10-27 10:50:20'),
(938, 99, 'Logout', 'User logged out successfully.', '2024-10-27 10:50:46'),
(941, 1, 'Login', 'User logged in successfully!', '2024-10-27 10:52:00'),
(942, 1, 'Logout', 'User logged out successfully.', '2024-10-27 10:52:33'),
(943, 99, 'Login', 'User logged in successfully!', '2024-11-16 17:34:17'),
(944, 99, 'Logout', 'User logged out successfully.', '2024-11-16 17:35:07'),
(949, 1, 'Login', 'User logged in successfully!', '2024-11-18 05:49:55'),
(950, 1, 'Logout', 'User logged out successfully.', '2024-11-18 05:53:47'),
(951, 99, 'Login', 'User logged in successfully!', '2024-11-18 05:54:05'),
(952, 99, 'Login', 'User logged in successfully!', '2024-11-18 07:12:01'),
(953, 1, 'Login', 'User logged in successfully!', '2024-11-18 07:18:45'),
(954, 1, 'Login', 'User logged in successfully!', '2024-11-18 07:19:54'),
(955, 1, 'Logout', 'User logged out successfully.', '2024-11-18 07:34:13'),
(956, 1, 'Login', 'User logged in successfully!', '2024-11-18 07:34:36'),
(957, 1, 'Logout', 'User logged out successfully.', '2024-11-18 08:50:32'),
(958, 1, 'Login', 'User logged in successfully!', '2024-11-18 08:52:19'),
(959, 1, 'Logout', 'User logged out successfully.', '2024-11-18 08:59:30'),
(960, 1, 'Login', 'User logged in successfully!', '2024-11-18 09:09:02'),
(961, 1, 'Logout', 'User logged out successfully.', '2024-11-18 09:32:53'),
(962, 1, 'Login', 'User logged in successfully!', '2024-11-18 09:34:11'),
(963, 1, 'Logout', 'User logged out successfully.', '2024-11-18 11:12:46'),
(964, 1, 'Login', 'User logged in successfully!', '2024-11-18 11:17:59'),
(965, 1, 'Logout', 'User logged out successfully.', '2024-11-18 11:18:41'),
(966, 1, 'Login', 'User logged in successfully!', '2024-11-18 11:18:52'),
(967, 1, 'Login', 'User logged in successfully!', '2024-11-18 11:40:45'),
(968, 1, 'Logout', 'User logged out successfully.', '2024-11-18 12:23:41'),
(969, 102, 'Login', 'User logged in successfully!', '2024-11-18 12:29:31'),
(970, 102, 'Login', 'User logged in successfully!', '2024-11-18 12:55:40'),
(971, 102, 'Add Learning Material', 'Added new learning material: Introduction', '2024-11-18 13:28:18'),
(972, 102, 'Add Learning Material', 'Added new learning material: Introduction', '2024-11-18 13:30:44'),
(973, 102, 'Add Quiz', 'Added new quiz: aaa', '2024-11-18 13:31:39'),
(974, 102, 'Add Task Sheets', 'Added new task sheet: sss', '2024-11-18 13:46:14'),
(975, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:52:37'),
(976, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:54:34'),
(977, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:55:00'),
(978, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:55:40'),
(979, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:56:07'),
(980, 102, 'Added a question to the quiz', 'aaa', '2024-11-18 13:56:27'),
(981, 102, 'Deleted a question from the quiz', 'aaa', '2024-11-18 13:56:40'),
(982, 102, 'Deleted a question from the quiz', 'aaa', '2024-11-18 13:56:44'),
(983, 102, 'Deleted a question from the quiz', 'aaa', '2024-11-18 13:56:46'),
(984, 102, 'Deleted a question from the quiz', 'aaa', '2024-11-18 13:56:47'),
(985, 102, 'Deleted a question from the quiz', 'aaa', '2024-11-18 13:56:50'),
(986, 102, 'Logout', 'User logged out successfully.', '2024-11-18 14:15:22'),
(987, 1, 'Login', 'User logged in successfully!', '2024-11-18 14:16:14'),
(988, 1, 'Logout', 'User logged out successfully.', '2024-11-18 14:22:36'),
(989, 102, 'Login', 'User logged in successfully!', '2024-11-18 14:23:21'),
(990, 102, 'Logout', 'User logged out successfully.', '2024-11-18 14:54:47'),
(991, 1, 'Login', 'User logged in successfully!', '2024-11-18 14:55:04'),
(992, 1, 'Logout', 'User logged out successfully.', '2024-11-18 14:55:54'),
(993, 102, 'Login', 'User logged in successfully!', '2024-11-18 14:56:04'),
(995, 102, 'Logout', 'User logged out successfully.', '2024-11-18 16:04:50'),
(998, 103, 'Login', 'User logged in successfully!', '2024-11-18 16:58:16'),
(999, 103, 'Logout', 'User logged out successfully.', '2024-11-18 17:40:53'),
(1000, 102, 'Login', 'User logged in successfully!', '2024-11-18 17:41:11'),
(1001, 102, 'Logout', 'User logged out successfully.', '2024-11-18 17:52:49'),
(1002, 103, 'Login', 'User logged in successfully!', '2024-11-18 17:52:59'),
(1003, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-18 17:54:02'),
(1004, 102, 'Login', 'User logged in successfully!', '2024-11-19 17:10:51'),
(1005, 102, 'Logout', 'User logged out successfully.', '2024-11-19 17:13:03'),
(1006, 103, 'Login', 'User logged in successfully!', '2024-11-19 17:13:17'),
(1007, 103, 'Logout', 'User logged out successfully.', '2024-11-19 17:54:53'),
(1008, 102, 'Login', 'User logged in successfully!', '2024-11-19 17:55:01'),
(1009, 102, 'Logout', 'User logged out successfully.', '2024-11-19 17:55:22'),
(1010, 103, 'Login', 'User logged in successfully!', '2024-11-19 17:55:34'),
(1011, 103, 'Logout', 'User logged out successfully.', '2024-11-19 17:57:27'),
(1012, 102, 'Login', 'User logged in successfully!', '2024-11-19 17:57:35'),
(1013, 102, 'Logout', 'User logged out successfully.', '2024-11-19 17:57:58'),
(1014, 103, 'Login', 'User logged in successfully!', '2024-11-19 17:58:06'),
(1015, 103, 'Auto Unenroll and Delete', 'Student  (ID: 103) auto-unenrolled and deleted from course ID: 21, batch ID: 23 due to inactivity.', '2024-11-19 18:03:32'),
(1016, 103, 'Logout', 'User logged out successfully.', '2024-11-19 18:03:41'),
(1017, 102, 'Login', 'User logged in successfully!', '2024-11-19 18:03:49'),
(1018, 102, 'Logout', 'User logged out successfully.', '2024-11-19 18:04:27'),
(1019, 103, 'Login', 'User logged in successfully!', '2024-11-19 18:04:38'),
(1020, 103, 'Logout', 'User logged out successfully.', '2024-11-19 18:04:57'),
(1021, 103, 'Login', 'User logged in successfully!', '2024-11-19 18:05:04'),
(1022, 103, 'Logout', 'User logged out successfully.', '2024-11-19 18:05:35'),
(1023, 102, 'Login', 'User logged in successfully!', '2024-11-19 18:05:54'),
(1024, 102, 'Logout', 'User logged out successfully.', '2024-11-19 18:06:16'),
(1025, 1, 'Login', 'User logged in successfully!', '2024-11-19 18:06:29'),
(1026, 1, 'Logout', 'User logged out successfully.', '2024-11-19 18:06:44'),
(1027, 103, 'Login', 'User logged in successfully!', '2024-11-19 18:07:02'),
(1028, 103, 'Logout', 'User logged out successfully.', '2024-11-19 18:08:35'),
(1031, 102, 'Login', 'User logged in successfully!', '2024-11-19 18:15:10'),
(1032, 102, 'Logout', 'User logged out successfully.', '2024-11-19 18:16:21'),
(1036, 102, 'Login', 'User logged in successfully!', '2024-11-19 18:19:18'),
(1037, 102, 'Graded task sheet submission', '91', '2024-11-19 18:19:28'),
(1038, 102, 'Graded task sheet submission', '92', '2024-11-19 18:19:31'),
(1039, 102, 'Logout', 'User logged out successfully.', '2024-11-19 18:19:39'),
(1042, 102, 'Login', 'User logged in successfully!', '2024-11-19 18:21:00'),
(1043, 102, 'Logout', 'User logged out successfully.', '2024-11-19 18:21:09'),
(1046, 1, 'Login', 'User logged in successfully!', '2024-11-19 18:24:14'),
(1047, 1, 'Add User', 'Added new student: Nelson Jay Veneracion', '2024-11-19 18:58:37'),
(1048, 1, 'Add User', 'Added new student: dwdwdw', '2024-11-19 18:58:49'),
(1050, 1, 'Logout', 'User logged out successfully.', '2024-11-19 19:48:05'),
(1051, 103, 'Login', 'User logged in successfully!', '2024-11-19 19:48:16'),
(1052, 103, 'Logout', 'User logged out successfully.', '2024-11-19 19:49:19'),
(1053, 102, 'Login', 'User logged in successfully!', '2024-11-19 19:49:33'),
(1054, 102, 'Logout', 'User logged out successfully.', '2024-11-19 19:50:01'),
(1055, 1, 'Login', 'User logged in successfully!', '2024-11-19 19:50:33'),
(1056, 103, 'Login', 'User logged in successfully!', '2024-11-19 19:51:27'),
(1057, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 19:51:39'),
(1058, 1, 'Logout', 'User logged out successfully.', '2024-11-19 19:54:26'),
(1059, 103, 'Login', 'User logged in successfully!', '2024-11-19 19:54:39'),
(1060, 103, 'Logout', 'User logged out successfully.', '2024-11-19 20:01:03'),
(1061, 1, 'Login', 'User logged in successfully!', '2024-11-19 20:01:12'),
(1062, 103, 'Login', 'User logged in successfully!', '2024-11-19 21:49:25'),
(1063, 1, 'Logout', 'User logged out successfully.', '2024-11-19 22:07:04'),
(1064, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:07:16'),
(1065, 102, 'Logout', 'User logged out successfully.', '2024-11-19 22:09:04'),
(1066, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:09:18'),
(1067, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:12:16'),
(1068, 1, 'Login', 'User logged in successfully!', '2024-11-19 22:12:38'),
(1069, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:17:20'),
(1070, 1, 'Logout', 'User logged out successfully.', '2024-11-19 22:19:27'),
(1071, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:21:11'),
(1072, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:24:01'),
(1073, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:24:10'),
(1074, 102, 'Graded task sheet submission', '93', '2024-11-19 22:24:21'),
(1075, 102, 'Logout', 'User logged out successfully.', '2024-11-19 22:24:33'),
(1076, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:24:41'),
(1077, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:25:23'),
(1078, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:25:43'),
(1079, 102, 'Logout', 'User logged out successfully.', '2024-11-19 22:26:02'),
(1080, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:26:14'),
(1081, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:26:19'),
(1082, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:27:10'),
(1083, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:27:19'),
(1084, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:29:16'),
(1085, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:30:00'),
(1086, 102, 'Logout', 'User logged out successfully.', '2024-11-19 22:34:06'),
(1087, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:34:17'),
(1088, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:34:22'),
(1089, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:36:12'),
(1090, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:37:20'),
(1091, 106, 'Login', 'User logged in successfully!', '2024-11-19 22:38:47'),
(1092, 106, 'Logout', 'User logged out successfully.', '2024-11-19 22:39:28'),
(1093, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:39:36'),
(1094, 102, 'Logout', 'User logged out successfully.', '2024-11-19 22:39:45'),
(1095, 106, 'Login', 'User logged in successfully!', '2024-11-19 22:39:56'),
(1096, 106, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:40:03'),
(1097, 106, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:44:36'),
(1098, 106, 'Logout', 'User logged out successfully.', '2024-11-19 22:45:20'),
(1099, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:45:40'),
(1100, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:45:56'),
(1101, 102, 'Login', 'User logged in successfully!', '2024-11-19 22:48:09'),
(1102, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:48:35'),
(1103, 103, 'Login', 'User logged in successfully!', '2024-11-19 22:48:44'),
(1104, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:51:23'),
(1105, 103, 'Logout', 'User logged out successfully.', '2024-11-19 22:52:12'),
(1106, 107, 'Login', 'User logged in successfully!', '2024-11-19 22:53:13'),
(1107, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-19 22:54:01'),
(1108, 107, 'Logout', 'User logged out successfully.', '2024-11-19 22:56:21'),
(1109, 1, 'Login', 'User logged in successfully!', '2024-11-19 22:56:35'),
(1110, 1, 'Logout', 'User logged out successfully.', '2024-11-19 23:04:09'),
(1111, 107, 'Login', 'User logged in successfully!', '2024-11-19 23:04:20'),
(1112, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-19 23:04:26'),
(1113, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-19 23:08:22'),
(1114, 107, 'Logout', 'User logged out successfully.', '2024-11-19 23:09:57'),
(1115, 104, 'Login', 'User logged in successfully!', '2024-11-19 23:10:35'),
(1116, 104, 'Enroll', 'Student enrolled in a course.', '2024-11-19 23:11:32'),
(1117, 104, 'Logout', 'User logged out successfully.', '2024-11-19 23:18:33'),
(1118, 1, 'Login', 'User logged in successfully!', '2024-11-19 23:18:52'),
(1119, 1, 'Logout', 'User logged out successfully.', '2024-11-19 23:44:56'),
(1120, 107, 'Login', 'User logged in successfully!', '2024-11-19 23:45:08'),
(1121, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-19 23:45:13'),
(1122, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-19 23:52:10'),
(1123, 107, 'Logout', 'User logged out successfully.', '2024-11-19 23:54:30'),
(1124, 1, 'Login', 'User logged in successfully!', '2024-11-19 23:54:46'),
(1125, 1, 'Login', 'User logged in successfully!', '2024-11-20 00:05:16'),
(1126, 1, 'Logout', 'User logged out successfully.', '2024-11-20 00:08:34'),
(1127, 107, 'Login', 'User logged in successfully!', '2024-11-20 00:08:45'),
(1128, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-20 00:08:50'),
(1129, 1, 'Login', 'User logged in successfully!', '2024-11-20 12:08:57'),
(1130, 1, 'Logout', 'User logged out successfully.', '2024-11-20 12:33:14'),
(1131, 103, 'Login', 'User logged in successfully!', '2024-11-20 12:33:35'),
(1132, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 12:33:45'),
(1133, 103, 'Logout', 'User logged out successfully.', '2024-11-20 12:49:21'),
(1134, 1, 'Login', 'User logged in successfully!', '2024-11-20 12:49:28'),
(1135, 1, 'Logout', 'User logged out successfully.', '2024-11-20 12:53:11'),
(1136, 103, 'Login', 'User logged in successfully!', '2024-11-20 12:54:09'),
(1137, 103, 'Logout', 'User logged out successfully.', '2024-11-20 12:58:05'),
(1138, 1, 'Login', 'User logged in successfully!', '2024-11-20 12:58:19'),
(1139, 1, 'Logout', 'User logged out successfully.', '2024-11-20 14:57:58'),
(1140, 103, 'Login', 'User logged in successfully!', '2024-11-20 14:58:05'),
(1141, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 14:59:41'),
(1142, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 15:01:30'),
(1143, 103, 'Logout', 'User logged out successfully.', '2024-11-20 15:04:33'),
(1144, 1, 'Login', 'User logged in successfully!', '2024-11-20 15:04:50'),
(1145, 1, 'Logout', 'User logged out successfully.', '2024-11-20 15:45:12'),
(1146, 103, 'Login', 'User logged in successfully!', '2024-11-20 15:45:21'),
(1147, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 15:45:27'),
(1148, 103, 'Logout', 'User logged out successfully.', '2024-11-20 15:47:14'),
(1149, 1, 'Login', 'User logged in successfully!', '2024-11-20 15:47:57'),
(1150, 1, 'Login', 'User logged in successfully!', '2024-11-20 15:49:10'),
(1151, 1, 'Login', 'User logged in successfully!', '2024-11-20 17:05:33'),
(1152, 103, 'Login', 'User logged in successfully!', '2024-11-20 17:42:28'),
(1153, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 17:44:13'),
(1154, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 17:45:00'),
(1155, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 17:49:07'),
(1156, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 17:57:55'),
(1157, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:07:55'),
(1158, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:31:45'),
(1159, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:36:51'),
(1160, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:44:19'),
(1161, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:50:53'),
(1162, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:54:53'),
(1163, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 18:56:58'),
(1164, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 19:09:38'),
(1165, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 19:32:19'),
(1166, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 19:36:57'),
(1167, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 19:50:45'),
(1168, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 19:54:02'),
(1169, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 20:05:50'),
(1170, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 20:12:42'),
(1171, 1, 'Logout', 'User logged out successfully.', '2024-11-20 20:28:15'),
(1172, 1, 'Login', 'User logged in successfully!', '2024-11-20 20:28:21'),
(1173, 1, 'Login', 'User logged in successfully!', '2024-11-20 20:40:57'),
(1174, 103, 'Login', 'User logged in successfully!', '2024-11-20 20:54:14'),
(1175, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-20 20:55:55'),
(1176, 1, 'Login', 'User logged in successfully!', '2024-11-21 01:20:24'),
(1177, 1, 'Logout', 'User logged out successfully.', '2024-11-21 01:24:55'),
(1178, 1, 'Login', 'User logged in successfully!', '2024-11-21 01:25:15'),
(1179, 1, 'Logout', 'User logged out successfully.', '2024-11-21 01:26:21'),
(1180, 1, 'Login', 'User logged in successfully!', '2024-11-22 13:43:47'),
(1181, 1, 'Login', 'User logged in successfully!', '2024-11-22 14:17:20'),
(1182, 1, 'Logout', 'User logged out successfully.', '2024-11-22 14:29:18'),
(1183, 1, 'Login', 'User logged in successfully!', '2024-11-22 17:55:00'),
(1184, 1, 'Logout', 'User logged out successfully.', '2024-11-22 18:18:23'),
(1185, 1, 'Login', 'User logged in successfully!', '2024-11-22 18:18:44'),
(1186, 1, 'Logout', 'User logged out successfully.', '2024-11-22 18:25:13'),
(1187, 1, 'Login', 'User logged in successfully!', '2024-11-22 18:25:24'),
(1188, 1, 'Logout', 'User logged out successfully.', '2024-11-22 18:51:42'),
(1189, 103, 'Login', 'User logged in successfully!', '2024-11-22 18:51:58'),
(1190, 103, 'Auto Unenroll and Delete', 'Student Mark Emannuel Dela Cruz (ID: 103) auto-unenrolled and deleted from course ID: 21, batch ID: 23 due to inactivity.', '2024-11-22 18:51:58'),
(1191, 103, 'Logout', 'User logged out successfully.', '2024-11-22 18:52:08'),
(1192, 103, 'Login', 'User logged in successfully!', '2024-11-22 18:52:18'),
(1193, 103, 'Logout', 'User logged out successfully.', '2024-11-22 18:52:23'),
(1194, 107, 'Login', 'User logged in successfully!', '2024-11-22 18:52:37'),
(1195, 107, 'Enroll', 'Student enrolled in a course.', '2024-11-22 18:52:43'),
(1196, 107, 'Logout', 'User logged out successfully.', '2024-11-22 18:53:10'),
(1197, 102, 'Login', 'User logged in successfully!', '2024-11-22 18:53:54'),
(1198, 102, 'Logout', 'User logged out successfully.', '2024-11-22 18:54:04'),
(1199, 107, 'Login', 'User logged in successfully!', '2024-11-22 18:54:18'),
(1200, 107, 'Logout', 'User logged out successfully.', '2024-11-22 19:05:43'),
(1201, 1, 'Login', 'User logged in successfully!', '2024-11-22 19:05:54'),
(1202, 1, 'Logout', 'User logged out successfully.', '2024-11-22 19:34:33'),
(1203, 1, 'Login', 'User logged in successfully!', '2024-11-22 19:51:14'),
(1204, 1, 'Delete User', 'Deleted user ID: 105', '2024-11-22 20:14:36'),
(1205, 1, 'Logout', 'User logged out successfully.', '2024-11-22 20:14:47'),
(1206, 107, 'Login', 'User logged in successfully!', '2024-11-22 20:15:05'),
(1207, 107, 'Logout', 'User logged out successfully.', '2024-11-22 20:15:33'),
(1208, 103, 'Login', 'User logged in successfully!', '2024-11-22 20:15:39'),
(1209, 102, 'Login', 'User logged in successfully!', '2024-11-22 20:16:37'),
(1210, 102, 'Logout', 'User logged out successfully.', '2024-11-22 20:16:49'),
(1211, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-22 20:17:08'),
(1212, 102, 'Login', 'User logged in successfully!', '2024-11-22 20:17:28'),
(1213, 102, 'Logout', 'User logged out successfully.', '2024-11-22 20:21:56'),
(1214, 1, 'Login', 'User logged in successfully!', '2024-11-22 20:22:02'),
(1215, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-22 20:23:32'),
(1216, 1, 'Logout', 'User logged out successfully.', '2024-11-22 20:23:51'),
(1217, 102, 'Login', 'User logged in successfully!', '2024-11-22 20:23:57'),
(1218, 102, 'Logout', 'User logged out successfully.', '2024-11-22 20:24:17'),
(1219, 1, 'Login', 'User logged in successfully!', '2024-11-22 20:24:33'),
(1220, 1, 'Logout', 'User logged out successfully.', '2024-11-22 20:29:04'),
(1221, 1, 'Login', 'User logged in successfully!', '2024-11-22 20:29:27'),
(1222, 103, 'Logout', 'User logged out successfully.', '2024-11-22 20:29:38'),
(1223, 1, 'Login', 'User logged in successfully!', '2024-11-23 07:30:04'),
(1224, 103, 'Login', 'User logged in successfully!', '2024-11-23 11:39:26'),
(1225, 1, 'Login', 'User logged in successfully!', '2024-11-23 11:41:45'),
(1226, 1, 'Logout', 'User logged out successfully.', '2024-11-23 11:42:20'),
(1227, 103, 'Logout', 'User logged out successfully.', '2024-11-23 11:42:36'),
(1228, 1, 'Login', 'User logged in successfully!', '2024-11-23 11:42:48'),
(1229, 102, 'Login', 'User logged in successfully!', '2024-11-23 12:18:07'),
(1230, 102, 'Logout', 'User logged out successfully.', '2024-11-23 12:18:34'),
(1231, 1, 'Login', 'User logged in successfully!', '2024-11-23 13:42:15'),
(1232, 1, 'Logout', 'User logged out successfully.', '2024-11-23 13:42:58'),
(1233, 102, 'Login', 'User logged in successfully!', '2024-11-23 13:44:38'),
(1234, 102, 'Logout', 'User logged out successfully.', '2024-11-23 13:45:29'),
(1235, 107, 'Login', 'User logged in successfully!', '2024-11-23 13:46:35'),
(1236, 1, 'Login', 'User logged in successfully!', '2024-11-23 18:29:27'),
(1237, 103, 'Login', 'User logged in successfully!', '2024-11-23 18:30:02'),
(1238, 102, 'Login', 'User logged in successfully!', '2024-11-23 18:31:06'),
(1239, 103, 'Logout', 'User logged out successfully.', '2024-11-23 18:34:57'),
(1240, 102, 'Logout', 'User logged out successfully.', '2024-11-23 18:35:22'),
(1241, 1, 'Login', 'User logged in successfully!', '2024-11-23 18:35:48'),
(1242, 102, 'Login', 'User logged in successfully!', '2024-11-23 18:36:00'),
(1243, 1, 'Logout', 'User logged out successfully.', '2024-11-23 18:36:19'),
(1244, 103, 'Login', 'User logged in successfully!', '2024-11-23 18:36:26'),
(1245, 102, 'Add Learning Material', 'Added new learning material: Reviewer', '2024-11-23 18:38:12'),
(1246, 102, 'Add Quiz', 'Added new quiz: Quiz 1', '2024-11-23 18:39:04'),
(1247, 102, 'Added a question to the quiz', 'Quiz 1', '2024-11-23 18:39:44'),
(1248, 102, 'Add Task Sheets', 'Added new task sheet: Assignment 1', '2024-11-23 18:40:22'),
(1249, 102, 'Logout', 'User logged out successfully.', '2024-11-23 18:46:34'),
(1250, 102, 'Login', 'User logged in successfully!', '2024-11-23 18:47:15'),
(1251, 103, 'Enroll', 'Student enrolled in a course.', '2024-11-23 18:49:08'),
(1252, 102, 'Logout', 'User logged out successfully.', '2024-11-23 18:50:43'),
(1253, 1, 'Login', 'User logged in successfully!', '2024-11-23 18:50:57'),
(1254, 1, 'Logout', 'User logged out successfully.', '2024-11-23 18:51:29'),
(1255, 102, 'Login', 'User logged in successfully!', '2024-11-23 18:51:36'),
(1256, 102, 'Graded task sheet submission', '94', '2024-11-23 18:52:48'),
(1257, 103, 'Logout', 'User logged out successfully.', '2024-11-23 19:01:47'),
(1258, 102, 'Logout', 'User logged out successfully.', '2024-11-23 19:02:16'),
(1259, 1, 'Login', 'User logged in successfully!', '2024-11-23 19:02:25'),
(1260, 1, 'Logout', 'User logged out successfully.', '2024-11-23 19:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `instructor_id`, `title`, `content`, `course_id`, `created_at`) VALUES
(41, 102, 'aa', '<p>aa</p>', 21, '2024-11-18 15:33:48'),
(44, 102, 'dwdwd', '<p>qsqsq</p>\r\n', 21, '2024-11-18 15:35:20'),
(45, 102, 'sqsqs', '<p>qsqs</p>', 21, '2024-11-18 15:45:40'),
(46, 102, 'sqs', '<p><strong>sqsqsqsqs&nbsp;</strong></p><p><strong>sqsqsqsqs</strong></p>', 21, '2024-11-18 15:45:52'),
(47, 102, 'dwdwdw', '<p><span class=\"text-huge\">sqsqsqsq</span></p>', 21, '2024-11-18 15:46:01'),
(49, 102, 'sqsqs', '<p>qsqsqsqs<strong>sqsqsqsqsqs</strong></p>', 21, '2024-11-18 15:50:24'),
(50, 102, 'Finals is ahead', '<p>wdwdwdwdwd</p>', 22, '2024-11-23 18:55:10');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assessment_title` varchar(50) NOT NULL,
  `assessment_description` text DEFAULT NULL,
  `assessment_type` enum('pre','post') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`assessment_id`, `course_id`, `assessment_title`, `assessment_description`, `assessment_type`, `created_at`) VALUES
(9945, 22, 'Final Test', '', 'post', '2024-11-23 18:41:09');

-- --------------------------------------------------------

--
-- Table structure for table `assessment_questions`
--

CREATE TABLE `assessment_questions` (
  `question_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_name` text NOT NULL,
  `option_a` varchar(100) NOT NULL,
  `option_b` varchar(100) NOT NULL,
  `option_c` varchar(100) NOT NULL,
  `option_d` varchar(100) NOT NULL,
  `correct_option` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessment_questions`
--

INSERT INTO `assessment_questions` (`question_id`, `assessment_id`, `question_name`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_at`) VALUES
(23, 9945, 'Wd', 'wdwd', 'dwd', 'dwdwd', 'wdwd', 'A', '2024-11-23 18:41:17'),
(24, 9945, 'qsqsqs', 'ssqs', 'qsqsq', 'qsqsqsq', 'sqsqs', 'A', '2024-11-23 18:41:22');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `batch_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `batch_name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `capacity` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batches`
--

INSERT INTO `batches` (`batch_id`, `course_id`, `batch_name`, `start_date`, `end_date`, `capacity`, `created_at`, `updated_at`) VALUES
(18, 16, 'FrontEnd', '2024-09-16', '2024-09-18', 5, '2024-09-16 14:45:42', '2024-09-16 14:45:42'),
(19, 17, 'Batch 1 - 2024', '2024-10-27', '2024-10-28', 10, '2024-10-27 10:43:05', '2024-10-27 10:43:05'),
(20, 18, '33', '2024-11-18', '2024-11-19', 33, '2024-11-18 07:41:03', '2024-11-18 07:41:03'),
(21, 19, '33', '2024-11-18', '2024-11-19', 33, '2024-11-18 07:41:56', '2024-11-18 07:41:56'),
(22, 20, 'aaa', '2024-11-18', '2024-11-19', 2, '2024-11-18 12:19:29', '2024-11-18 12:19:29'),
(23, 21, 'Batch 1 - 2023', '2024-11-18', '2024-11-24', 12, '2024-11-18 13:10:45', '2024-11-20 17:44:06'),
(24, 22, 'Alpha', '2024-11-24', '2024-11-25', 10, '2024-11-23 18:37:40', '2024-11-23 18:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `certificate_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `certificate_path` varchar(100) NOT NULL,
  `generated_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`certificate_id`, `student_id`, `course_id`, `certificate_path`, `generated_at`, `is_verified`) VALUES
(149, 103, 21, '../certificates/download-certificates/1732307297.pdf', '2024-11-23 04:28:17', 1),
(150, 103, 22, '../certificates/download-certificates/1732388044.pdf', '2024-11-24 02:54:07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `certificate_templates`
--

CREATE TABLE `certificate_templates` (
  `id` int(11) NOT NULL,
  `template_image_path` varchar(255) NOT NULL,
  `font_path` varchar(255) NOT NULL,
  `text_color` varchar(7) NOT NULL,
  `student_name_x` int(11) NOT NULL,
  `student_name_y` int(11) NOT NULL,
  `course_name_x` int(11) NOT NULL,
  `course_name_y` int(11) NOT NULL,
  `completion_date_x` int(11) NOT NULL,
  `completion_date_y` int(11) NOT NULL,
  `qr_code_x` int(11) NOT NULL,
  `qr_code_y` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `student_name_font_size` int(11) DEFAULT NULL,
  `course_name_font_size` int(11) DEFAULT NULL,
  `completion_date_font_size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificate_templates`
--

INSERT INTO `certificate_templates` (`id`, `template_image_path`, `font_path`, `text_color`, `student_name_x`, `student_name_y`, `course_name_x`, `course_name_y`, `completion_date_x`, `completion_date_y`, `qr_code_x`, `qr_code_y`, `created_at`, `student_name_font_size`, `course_name_font_size`, `completion_date_font_size`) VALUES
(1, '../certificates/templates/purepng.com-certificate-templateobjectscertificate-templateobject-award-certificate-template-631522323360ryv35.png', '../certificates/fonts/Bokor-Regular.ttf', '#c6de17', 402, 309, 575, 461, 221, 458, 610, 92, '2024-11-20 20:52:31', 45, 18, 18);

-- --------------------------------------------------------

--
-- Table structure for table `cms_courses`
--

CREATE TABLE `cms_courses` (
  `id` int(11) NOT NULL,
  `course_header` varchar(150) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cms_courses`
--

INSERT INTO `cms_courses` (`id`, `course_header`, `title`, `description`, `image`) VALUES
(1, 'Our Tesda Online Courses', 'qsqs', 'sqsq', '../uploads/course-photo/Certificate-of-Completion-template-1-768x577.png'),
(5, '', 'sqsqsqsqs', 'sqsqsqssq', '../uploads/course-photo/WebDev.jpg'),
(6, '', 'TypeScript', 'wdwdwdwdwdwdwdwdwdwdwddw', '../uploads/course-photo/1_v2s19jk0rs5OfwWcB9j44w.png'),
(7, '', 'Minecraft', 'wdwdwdw', '../uploads/course-photo/minecraft-1106261_1920.png');

-- --------------------------------------------------------

--
-- Table structure for table `contact_info`
--

CREATE TABLE `contact_info` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_info`
--

INSERT INTO `contact_info` (`id`, `email`, `phone`, `address`) VALUES
(1, 'njsven@gmail.com', '09513408075', 'Purok 4 Bicos, Rizal, Nueva Ecija');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_name` varchar(50) NOT NULL,
  `course_img` varchar(100) NOT NULL,
  `course_desc` text NOT NULL,
  `course_duration` int(11) NOT NULL,
  `course_code` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `user_id`, `course_name`, `course_img`, `course_desc`, `course_duration`, `course_code`) VALUES
(20, 99, 'wdwd', '../uploads/course-photo/f851d52bbc8568211cac1eba30794556.png', '', 1380, 'wdswdw'),
(21, 102, 'TypeScript', '../uploads/course-photo/e40ed983a979c0d4360502443c3d193c.png', 'JSAHDJSDJHSDGDqsqsq', 2043, 'QWERTY'),
(22, 102, 'Artificial Intelligence', '../uploads/course-photo/f1dba2fe5e66af52b349c910e4c5a119.jpg', '', 12020, '1234');

-- --------------------------------------------------------

--
-- Table structure for table `course_material`
--

CREATE TABLE `course_material` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `material_title` varchar(50) NOT NULL,
  `material_desc` text NOT NULL,
  `material_file` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_material`
--

INSERT INTO `course_material` (`material_id`, `course_id`, `material_title`, `material_desc`, `material_file`, `created_at`) VALUES
(4142, 22, 'Finals', '', '00005.mp4', '2024-11-24 02:41:02'),
(4318, 22, 'Lesson 1', '', 'm2-res_1080p.mp4', '2024-11-24 02:38:58'),
(5178, 21, 'Introduction', 'aaqq', 'bitcoin-digital-currency.1920x1080.mp4', '2024-11-18 21:30:54'),
(6877, 22, 'Introduction', '', 'bitcoin-digital-currency.1920x1080.mp4', '2024-11-24 02:38:38');

-- --------------------------------------------------------

--
-- Table structure for table `course_registrations`
--

CREATE TABLE `course_registrations` (
  `registration_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `tvl_name` varchar(100) DEFAULT NULL,
  `scholarship_type` enum('twsp','ttsp','pesfa','step') DEFAULT NULL,
  `trainer_name` varchar(100) DEFAULT NULL,
  `training_schedule` varchar(100) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(100) DEFAULT NULL,
  `civil_status` enum('single','married','divorced','widowed') DEFAULT NULL,
  `sex` enum('male','female','other') DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  `highest_education_attainment` varchar(50) DEFAULT NULL,
  `is_pwd` tinyint(1) NOT NULL DEFAULT 0,
  `disability_type` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `pic_path` varchar(255) NOT NULL,
  `birthCert_path` varchar(255) NOT NULL,
  `status` enum('declined','approved','resubmit','pending') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_registrations`
--

INSERT INTO `course_registrations` (`registration_id`, `course_id`, `student_id`, `tvl_name`, `scholarship_type`, `trainer_name`, `training_schedule`, `first_name`, `middle_name`, `last_name`, `extension`, `date_of_birth`, `place_of_birth`, `civil_status`, `sex`, `mobile_number`, `email_address`, `highest_education_attainment`, `is_pwd`, `disability_type`, `reason`, `pic_path`, `birthCert_path`, `status`, `created_at`) VALUES
(46, 21, 106, 'qsqs', 'twsp', 'sqsqs', '121', 'Jerald', 'Bwenipayo', 'Maon', '', '2024-11-08', 'sqsq', 'married', 'male', '0951308075', 'njveneracion.gwapo28@gmail.com', 'highschool', 0, '', 'dwd', '../uploads/course-registrations/2x2-pic/673d139ca5a26_ecert-template.png', '../uploads/course-registrations/birth-certificate/673d139ca5d77_1.png', 'approved', '2024-11-19 22:39:24'),
(49, 21, 107, 'qss', 'ttsp', 'sqsqs', '22', 'Nelson Jay', '', 'Vneeracion', '', '2024-11-08', 'sqsqs', 'single', 'male', '0951308075', 'enjayveneracion@gmail.com', 'elementary', 0, '', 's', '../uploads/course-registrations/2x2-pic/673d16f88aa5a_Capture1.PNG', '../uploads/course-registrations/birth-certificate/673d16f88ad13_462537956_924733862322756_1923309904706437878_n.png', 'approved', '2024-11-19 22:53:44'),
(50, 21, 104, 's', 'ttsp', 'sqsqs', '121', 'Nelson', '', 'Veneracion', '', '2024-11-14', 'SQSQ', 'single', 'male', '3843749384798', 'a@gmail.com', 'highschool', 0, '', 'd', '../uploads/course-registrations/2x2-pic/673d1b0f12d23_kindpng_4517876.png', '../uploads/course-registrations/birth-certificate/673d1b0f12f6b_1001062733.jpg', 'approved', '2024-11-19 23:11:11'),
(51, 21, 103, 'wqsdwd', 'ttsp', 'dwdwdwd', '23', 'Mark Emannuel', '', 'Dela Cruz', '', '2024-11-02', 'qsqs', 'single', 'female', '4535354355', 'njveneracion.042803@gmail.com', 'highschool', 0, '', 'wsdwd', '../uploads/course-registrations/2x2-pic/6740e68eab9d8_Certificate-of-Completion-template-1-768x577.png', '../uploads/course-registrations/birth-certificate/6740e68eabbe0_purepng.com-certificate-templateobjectscertificate-templateobject-award-certificate-template-631522323360ryv35.png', 'approved', '2024-11-22 20:16:14'),
(52, 22, 103, 'wqsdwd', 'twsp', 'dwdwdwd', '12', 'Mark Emannuel', 'Flores', 'Dela Cruz', '', '2024-11-24', 'qsqs', 'single', 'male', '4535354355', 'njveneracion.042803@gmail.com', 'elementary', 0, '', 'a', '../uploads/course-registrations/2x2-pic/67422214efc06_464907212_426210397178776_8046158930636097532_n.jpg', '../uploads/course-registrations/birth-certificate/67422214efe38_Certificate-of-Completion-template-1-768x577.png', 'approved', '2024-11-23 18:42:28');

-- --------------------------------------------------------

--
-- Table structure for table `cta_content`
--

CREATE TABLE `cta_content` (
  `id` int(11) NOT NULL,
  `heading` varchar(255) NOT NULL,
  `subheading` text NOT NULL,
  `button_text` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cta_content`
--

INSERT INTO `cta_content` (`id`, `heading`, `subheading`, `button_text`) VALUES
(1, 'Ready to Enhance Your Skills Online?', 'Come with PATS, we are here to teach you everything!', 'START YOUR LEARNING JOURNEY');

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `discussion_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`discussion_id`, `course_id`, `user_id`, `message`, `created_at`, `parent_id`) VALUES
(56, 21, 103, 'wwd', '2024-11-19 17:24:06', NULL),
(57, 21, 103, 'aaa', '2024-11-19 17:24:17', 56),
(58, 21, 103, 'aaa', '2024-11-19 17:26:24', 56),
(59, 21, 103, 'aaa', '2024-11-19 17:27:13', 56),
(60, 21, 102, 'er23e3', '2024-11-23 13:45:27', 56),
(61, 22, 103, 'How to access the modules?', '2024-11-23 18:49:28', NULL),
(62, 22, 102, 'Find the Learning materials and then navigate into it', '2024-11-23 18:49:59', 61);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('In Progress','Completed') NOT NULL,
  `completion_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `user_id`, `course_id`, `batch_id`, `enrollment_date`, `status`, `completion_date`) VALUES
(103, 103, 21, 23, '2024-11-22 20:23:32', 'Completed', '2024-11-23 04:28:17'),
(104, 103, 22, 24, '2024-11-23 18:49:08', 'Completed', '2024-11-24 02:59:56');

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faqs`
--

INSERT INTO `faqs` (`id`, `question`, `answer`) VALUES
(1, 'How is PATS Online Learning Platform?', 'This is Great yey'),
(2, 'What is PATS?', 'HAHAHAHA GAO'),
(5, 'dwdwdw', 'wd'),
(6, 'wdwdwd', 'wdwdd'),
(7, 'dsqsqsqs', 'qsqs'),
(8, 'wd', 'wdwdwd');

-- --------------------------------------------------------

--
-- Table structure for table `footer_text`
--

CREATE TABLE `footer_text` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `footer_text`
--

INSERT INTO `footer_text` (`id`, `content`) VALUES
(1, ' © 2024 Philippine Academy of Technical Studies. All rights reserved. wd\r\n');

-- --------------------------------------------------------

--
-- Table structure for table `header`
--

CREATE TABLE `header` (
  `id` int(11) NOT NULL,
  `logo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `header`
--

INSERT INTO `header` (`id`, `logo`) VALUES
(1, '../uploads/logo/pats-logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `hero_section`
--

CREATE TABLE `hero_section` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `subtitle` text NOT NULL,
  `button_text` varchar(100) NOT NULL,
  `background_img` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_section`
--

INSERT INTO `hero_section` (`id`, `title`, `subtitle`, `button_text`, `background_img`) VALUES
(1, 'Welcome to PATS Online Learning Platform ', 'In this platform, you can enroll to your desired course even you are not in the school', 'START LEARNING', '../uploads/hero-bg/pats-picture.PNG');

-- --------------------------------------------------------

--
-- Table structure for table `learning_materials`
--

CREATE TABLE `learning_materials` (
  `material_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learning_materials`
--

INSERT INTO `learning_materials` (`material_id`, `course_id`, `title`, `description`, `file_path`, `created_at`) VALUES
(1426, 13, 'wsd', 'wdwdwd', 'njven_resume (1).docx', '2024-09-14 14:15:53'),
(4623, 13, 'review this', '', 'Veneracion Nelson Jay  S. (3).pdf', '2024-09-14 20:25:01'),
(6665, 13, 'hihihihih', 'edef', 'Veneracion Nelson Jay  S. (3).pdf', '2024-09-14 13:58:49'),
(9988, 6, 'Introduction', 'sqs', '1725968327.pdf', '2024-09-15 08:09:51'),
(8567, 13, 'a', 'a', 'GROUP-2-NSTP.pdf', '2024-09-16 06:54:52'),
(9711, 14, 'LESSON 1: Use of Farm Tools and Equipment', '', '12+Rules+to+Learn+to+Code+[2nd+Edition]+2022.pdf', '2024-09-16 08:52:20'),
(7872, 16, 'Intro to Web', 'wdw', 'Activity.docx', '2024-09-16 14:49:01'),
(238, 17, 'sdwdwd', '', 'Activity (1).docx', '2024-10-27 10:43:51'),
(3156, 21, 'Introduction', 'a', 'STS-PAPER-final.pdf', '2024-11-18 13:30:44'),
(6341, 22, 'Reviewer', '', 'HTML and CSS ITS Exam Reviewer.pdf', '2024-11-23 18:38:12');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `recipient_type` enum('student','instructor','admin') NOT NULL,
  `course_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `instructor_id`, `recipient_type`, `course_id`, `message`, `status`, `created_at`) VALUES
(381, 103, 0, 'student', 21, 'Congratulations! You have completed the course. Please wait for the instructor to verify your completion.', 'read', '2024-11-22 20:23:46'),
(382, 103, 0, 'student', 21, 'Your certificate for the course has been generated.', 'read', '2024-11-22 20:24:00'),
(383, 103, 0, 'student', 21, 'Your certificate for the course has been generated.', 'read', '2024-11-23 13:44:43'),
(384, 103, 0, 'student', 22, 'Your course registration for Artificial Intelligence has been approved. 1234 is the enrollment key.', 'read', '2024-11-23 18:48:53'),
(385, 102, 0, 'instructor', 22, 'New submission on 2024-11-23 19:52:32: Mark Emannuel Dela Cruz has submitted the task sheet \'Assignment 1\' for the course \'Artificial Intelligence\'.', 'read', '2024-11-23 18:52:32'),
(386, 103, 102, 'student', 22, 'Your task sheet submission for \'\' has been graded. Status: Passed', 'read', '2024-11-23 18:52:48'),
(387, 103, 0, 'student', 22, 'Congratulations! You have completed the course. Please wait for the instructor to verify your completion.', 'read', '2024-11-23 18:54:04'),
(388, 103, 0, 'student', 22, 'Your certificate for the course has been generated.', 'read', '2024-11-23 18:54:23'),
(389, 103, 102, 'student', 22, 'Good job kapatid', 'read', '2024-11-23 18:55:25');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `email` varchar(60) NOT NULL,
  `token` varchar(6) NOT NULL,
  `expiration` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `email`, `token`, `expiration`, `created_at`) VALUES
(8, 'admin101@gmail.com', '903254', '2024-09-11 08:52:38', '2024-09-11 06:37:38'),
(35, 'njveneracion.042803@gmail.com', '676633', '2024-09-14 18:50:49', '2024-09-14 16:35:49'),
(39, 'njveneracion.gwapo28@gmail.com', '810941', '2024-09-14 21:49:05', '2024-09-14 19:34:05');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int(100) NOT NULL,
  `course_id` int(11) NOT NULL,
  `quiz_name` varchar(50) NOT NULL,
  `quiz_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quiz_id`, `course_id`, `quiz_name`, `quiz_description`, `created_at`) VALUES
(4638, 21, 'aaa', '', '2024-11-18 13:31:39'),
(9327, 22, 'Quiz 1', '', '2024-11-23 18:39:04');

-- --------------------------------------------------------

--
-- Table structure for table `quiz_questions`
--

CREATE TABLE `quiz_questions` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_name` text NOT NULL,
  `option_a` varchar(100) NOT NULL,
  `option_b` varchar(100) NOT NULL,
  `option_c` varchar(100) NOT NULL,
  `option_d` varchar(100) NOT NULL,
  `correct_option` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_questions`
--

INSERT INTO `quiz_questions` (`question_id`, `quiz_id`, `question_name`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_at`) VALUES
(21, 4638, 'What is WEb?', 'a', 'dwdw', 'wdwdw', 'dwdwd', 'A', '2024-11-18 13:56:07'),
(23, 9327, 'What is the meaning of AI?', 'Artificial Intelligence', 'Data Science', 'Machine Learning', 'Deep Learning', 'A', '2024-11-23 18:39:44');

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `header` text NOT NULL,
  `sub_header` text NOT NULL,
  `icon` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `skills`
--

INSERT INTO `skills` (`id`, `header`, `sub_header`, `icon`, `title`, `description`) VALUES
(34, 'Hi PEEPS, welcom to PATS', 'Hello worldssssssefefefefef edfefeffe efef ef efefe fefef efefefefefefefefef ejdoehfiehf', '', '', ''),
(54, '', '', 'fa-brain', 'Intel', 'hello world ahahhaa'),
(55, '', '', 'fa-book', 'Resources', 'WHAHAHA'),
(56, '', '', 'fa-code', 'Coding', 'lezfg'),
(57, '', '', 'fa-beer', 'Coffee', 'sarap');

-- --------------------------------------------------------

--
-- Table structure for table `social_links`
--

CREATE TABLE `social_links` (
  `id` int(11) NOT NULL,
  `platform` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `social_links`
--

INSERT INTO `social_links` (`id`, `platform`, `url`) VALUES
(15, 'Facebook', 'https://www.facebook.com/njveneracionnnnnnccccdd'),
(33, 'Instagram', 'https://www.facebook.com/njveneracionnnnnnccccdd'),
(34, 'Twitter', 'https://www.facebook.com/njveneracionnnnnnccccdd');

-- --------------------------------------------------------

--
-- Table structure for table `student_activity`
--

CREATE TABLE `student_activity` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `last_activity` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_answers`
--

CREATE TABLE `student_answers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `student_answer` char(1) NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_answers`
--

INSERT INTO `student_answers` (`id`, `student_id`, `quiz_id`, `question_id`, `student_answer`, `correct_answer`, `created_at`) VALUES
(124, 103, 4638, 21, 'A', 'A', '2024-11-22 20:23:45'),
(125, 103, 9327, 23, 'A', 'A', '2024-11-23 18:51:44');

-- --------------------------------------------------------

--
-- Table structure for table `student_assessment_answers`
--

CREATE TABLE `student_assessment_answers` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `student_answer` char(1) NOT NULL,
  `correct_answer` char(1) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_assessment_answers`
--

INSERT INTO `student_assessment_answers` (`id`, `student_id`, `assessment_id`, `question_id`, `student_answer`, `correct_answer`, `created_at`) VALUES
(95, 103, 9945, 23, 'A', 'A', '2024-11-23 18:54:02'),
(96, 103, 9945, 24, 'A', 'A', '2024-11-23 18:54:02');

-- --------------------------------------------------------

--
-- Table structure for table `student_progress`
--

CREATE TABLE `student_progress` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `content_id` int(11) DEFAULT NULL,
  `content_type` enum('Material','Quiz','Task Sheet','pre-assessment','post-assessment') DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_progress`
--

INSERT INTO `student_progress` (`id`, `student_id`, `course_id`, `content_id`, `content_type`, `is_completed`, `completed_at`) VALUES
(569, 103, 21, 5178, 'Material', 1, '2024-11-23 04:23:36'),
(570, 103, 21, 4638, 'Quiz', 1, '2024-11-23 04:23:45'),
(571, 103, 22, 6877, 'Material', 1, '2024-11-24 02:50:25'),
(572, 103, 22, 4318, 'Material', 1, '2024-11-24 02:50:30'),
(573, 103, 22, 9327, 'Quiz', 1, '2024-11-24 02:51:44'),
(574, 103, 22, 3066, 'Task Sheet', 0, NULL),
(575, 103, 22, 3066, 'Task Sheet', 1, '2024-11-24 02:52:48'),
(576, 103, 22, 3066, 'Task Sheet', 1, '2024-11-24 02:52:48'),
(577, 103, 22, 4142, 'Material', 1, '2024-11-24 02:53:33'),
(578, 103, 22, 9945, 'post-assessment', 1, '2024-11-24 02:54:02');

-- --------------------------------------------------------

--
-- Table structure for table `task_sheets`
--

CREATE TABLE `task_sheets` (
  `task_sheet_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `task_sheet_title` varchar(50) NOT NULL,
  `task_sheet_description` text DEFAULT NULL,
  `task_sheet_file` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_sheets`
--

INSERT INTO `task_sheets` (`task_sheet_id`, `course_id`, `task_sheet_title`, `task_sheet_description`, `task_sheet_file`, `created_at`) VALUES
(3066, 22, 'Assignment 1', '', 'enrolled_students_report (2).pdf', '2024-11-23 18:40:22');

-- --------------------------------------------------------

--
-- Table structure for table `task_sheet_submissions`
--

CREATE TABLE `task_sheet_submissions` (
  `submission_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `task_sheet_id` int(11) NOT NULL,
  `submission` text NOT NULL,
  `submitted_at` datetime NOT NULL DEFAULT current_timestamp(),
  `file_path` varchar(100) DEFAULT NULL,
  `status` enum('pending','passed','failed') DEFAULT 'pending',
  `feedback` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_sheet_submissions`
--

INSERT INTO `task_sheet_submissions` (`submission_id`, `student_id`, `task_sheet_id`, `submission`, `submitted_at`, `file_path`, `status`, `feedback`) VALUES
(94, 103, 3066, 'This is shit you know', '2024-11-24 02:52:32', NULL, 'passed', 'nice');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `testimonial` text NOT NULL,
  `author` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `testimonial`, `author`) VALUES
(6, 'This is great and super lupet', 'wsjhduiawdhiuwd'),
(7, 'LUPETZKIE', 'Janine'),
(8, 'hahahahaha fagio', 'NJ'),
(9, 'lupe lupet', 'HAHA'),
(10, 'Astig ng coding exercise', 'Nelson Jay Veneracion / Student');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `fullname` varchar(60) NOT NULL,
  `username` varchar(30) NOT NULL,
  `profile_picture` varchar(100) NOT NULL,
  `email` varchar(40) NOT NULL,
  `password` varchar(70) NOT NULL,
  `role` varchar(15) NOT NULL,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiration` datetime DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `fullname`, `username`, `profile_picture`, `email`, `password`, `role`, `otp`, `otp_expiration`, `is_verified`, `created_at`) VALUES
(1, 'NJ VENERACION POGI', 'patscab', '322000373_610003367550585_4896684962202158506_n.jpg', 'patscabanatuan@gmail.com', 'Patscab1@', 'admin', NULL, NULL, 1, '2024-09-16 22:43:28'),
(99, 'Juan Dela Cruz', 'instructor', '', 'njvenxxviii@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'instructor', NULL, NULL, 1, '2024-10-27 11:42:05'),
(100, 'MAster', 'student', '', 'takeriissk@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'instructor', '437754', '2024-10-27 12:00:48', 0, '2024-10-27 11:45:48'),
(102, 'Nelson Jay Veneracion', 'njsvenn', '464907212_426210397178776_8046158930636097532_n.jpg', 'njsvenn@gmail.com', 'dbf85d797860fef5391740976c2d701038aa25009567d4d52a2ff6731c34848d', 'instructor', NULL, NULL, 1, '2024-11-18 13:24:38'),
(103, 'Mark Emannuel Dela HAHAHA', 'njven', '346108125_777912083950952_2765172609085739785_n.jpg', 'njveneracion.042803@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'student', NULL, NULL, 1, '2024-11-18 17:57:37'),
(104, 'Nelson Jay Veneracion', 'dwdw', '', 'a@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'student', NULL, NULL, 1, '2024-11-20 02:58:37'),
(106, 'Jerald Maon', '2we2e', '', 'njveneracion.gwapo28@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'student', NULL, NULL, 1, '2024-11-19 23:38:10'),
(107, 'NJ VENERACION', 'qwewqdq', '', 'enjayveneracion@gmail.com', '2fa13879ac5cadbfbd0a017043d49cf837b18dd9830dedc9192e1602c5f70bba', 'student', NULL, NULL, 1, '2024-11-19 23:52:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `active_sessions`
--
ALTER TABLE `active_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `instructor_id` (`instructor_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`batch_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`certificate_id`),
  ADD KEY `certificates_ibfk_1` (`student_id`),
  ADD KEY `certificates_ibfk_2` (`course_id`);

--
-- Indexes for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_courses`
--
ALTER TABLE `cms_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_info`
--
ALTER TABLE `contact_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `courses_ibfk_1` (`user_id`);

--
-- Indexes for table `course_material`
--
ALTER TABLE `course_material`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `course_material_ibfk_1` (`course_id`);

--
-- Indexes for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `course_registrations_ibfk_1` (`course_id`),
  ADD KEY `course_registrations_ibfk_2` (`student_id`);

--
-- Indexes for table `cta_content`
--
ALTER TABLE `cta_content`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`discussion_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `discussions_ibfk_3` (`parent_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `enrollments_ibfk_1` (`user_id`),
  ADD KEY `enrollments_ibfk_2` (`course_id`),
  ADD KEY `enrollments_ibfk_3` (`batch_id`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `footer_text`
--
ALTER TABLE `footer_text`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `header`
--
ALTER TABLE `header`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_section`
--
ALTER TABLE `hero_section`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quiz_id`),
  ADD KEY `quiz_ibfk_1` (`course_id`);

--
-- Indexes for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `social_links`
--
ALTER TABLE `social_links`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `student_activity`
--
ALTER TABLE `student_activity`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_student_course` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_answers_ibfk_1` (`student_id`),
  ADD KEY `student_answers_ibfk_2` (`quiz_id`),
  ADD KEY `student_answers_ibfk_3` (`question_id`);

--
-- Indexes for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `assessment_id` (`assessment_id`),
  ADD KEY `question_id` (`question_id`);

--
-- Indexes for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`content_id`),
  ADD KEY `student_progress_ibfk_1` (`student_id`),
  ADD KEY `student_progress_ibfk_2` (`course_id`);

--
-- Indexes for table `task_sheets`
--
ALTER TABLE `task_sheets`
  ADD PRIMARY KEY (`task_sheet_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `task_sheet_submissions`
--
ALTER TABLE `task_sheet_submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`task_sheet_id`),
  ADD KEY `task_sheet_id` (`task_sheet_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_us`
--
ALTER TABLE `about_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1261;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9946;

--
-- AUTO_INCREMENT for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `certificate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=151;

--
-- AUTO_INCREMENT for table `certificate_templates`
--
ALTER TABLE `certificate_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cms_courses`
--
ALTER TABLE `cms_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contact_info`
--
ALTER TABLE `contact_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `course_material`
--
ALTER TABLE `course_material`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9832;

--
-- AUTO_INCREMENT for table `course_registrations`
--
ALTER TABLE `course_registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `cta_content`
--
ALTER TABLE `cta_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `discussion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `footer_text`
--
ALTER TABLE `footer_text`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `header`
--
ALTER TABLE `header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hero_section`
--
ALTER TABLE `hero_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=390;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quiz_id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2147483648;

--
-- AUTO_INCREMENT for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `social_links`
--
ALTER TABLE `social_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `student_activity`
--
ALTER TABLE `student_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_answers`
--
ALTER TABLE `student_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `student_progress`
--
ALTER TABLE `student_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=579;

--
-- AUTO_INCREMENT for table `task_sheets`
--
ALTER TABLE `task_sheets`
  MODIFY `task_sheet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7362;

--
-- AUTO_INCREMENT for table `task_sheet_submissions`
--
ALTER TABLE `task_sheet_submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_3` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `announcements_ibfk_4` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `assessment_questions`
--
ALTER TABLE `assessment_questions`
  ADD CONSTRAINT `assessment_questions_ibfk_1` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_material`
--
ALTER TABLE `course_material`
  ADD CONSTRAINT `course_material_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `course_registrations`
--
ALTER TABLE `course_registrations`
  ADD CONSTRAINT `course_registrations_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `course_registrations_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussions_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `discussions` (`discussion_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_3` FOREIGN KEY (`batch_id`) REFERENCES `batches` (`batch_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_9` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz_questions`
--
ALTER TABLE `quiz_questions`
  ADD CONSTRAINT `quiz_questions_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_activity`
--
ALTER TABLE `student_activity`
  ADD CONSTRAINT `student_activity_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `student_activity_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `student_answers`
--
ALTER TABLE `student_answers`
  ADD CONSTRAINT `student_answers_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_answers_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `quiz_questions` (`question_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_assessment_answers`
--
ALTER TABLE `student_assessment_answers`
  ADD CONSTRAINT `student_assessment_answers_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_answers_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`assessment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_assessment_answers_ibfk_3` FOREIGN KEY (`question_id`) REFERENCES `assessment_questions` (`question_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_progress`
--
ALTER TABLE `student_progress`
  ADD CONSTRAINT `student_progress_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `student_progress_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_sheets`
--
ALTER TABLE `task_sheets`
  ADD CONSTRAINT `task_sheets_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `task_sheet_submissions`
--
ALTER TABLE `task_sheet_submissions`
  ADD CONSTRAINT `task_sheet_submissions_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `task_sheet_submissions_ibfk_4` FOREIGN KEY (`task_sheet_id`) REFERENCES `task_sheets` (`task_sheet_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
