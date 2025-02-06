-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 10, 2024 at 06:21 AM
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
-- Database: `asset-inv`
--
CREATE DATABASE IF NOT EXISTS `asset-inv` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `asset-inv`;

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int(255) NOT NULL,
  `brand` varchar(255) NOT NULL,
  `model` varchar(255) NOT NULL,
  `serial_number` varchar(255) NOT NULL,
  `asset_tag` varchar(255) NOT NULL,
  `asset_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `location_asset` varchar(255) NOT NULL,
  `price_value` int(255) NOT NULL,
  `issued_to` varchar(255) NOT NULL,
  `date_acquired` varchar(255) NOT NULL,
  `remarks` text NOT NULL,
  `documents` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` text NOT NULL,
  `user_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `brand`, `model`, `serial_number`, `asset_tag`, `asset_type`, `status`, `equipment_name`, `location_asset`, `price_value`, `issued_to`, `date_acquired`, `remarks`, `documents`, `created_at`, `updated_at`, `user_id`) VALUES
(10, 'ASUS', 'Intel(R) Core(TM) i5-4590 CPU @ 3.30GHz 3.30 GHz', '944459C9-5EAF-4249-A9E0-FC60B2F12AD1', 'APAC-2024-8d31Y', 'System Unit', 'In Storage', 'CL-WS009', 'BAY 2', 0, 'Sheryl Joy Balagtas', '2024-10-03', 'PT000221808', '', '2024-10-03 04:03:19', '2024-10-09 13:52:53', 3),
(11, 'ASUS', 'Intel(R) Core(TM) i5-4590 CPU @ 3.30GHz 3.30 GHz', '4ADF194D-D958-4475-89F5-47B8721904AC', 'APAC-2024-7a0aH', 'System Unit', 'In Use', 'CL-WS027', 'BAY 2', 222, 'Joyce Anne Mananquil', '2024-10-03', 'PT000221826', '', '2024-10-03 04:04:43', '2024-10-09 03:14:50', 1),
(12, 'ASUS', 'VC239', 'G3LMTJ008854', 'APAC-2024-5d38K', 'Monitor', 'In Use', 'CL-WS009', 'BAY 2', 6000, 'Sheryl Joy Balagtas', '2024-10-03', 'PT000221916', '', '2024-10-03 04:06:57', '2024-10-03 06:06:57', 1),
(13, 'ASUS', 'VC239', 'G2LMTJ002645', 'APAC-2024-fc53Z', 'Monitor', 'In Use', 'CL-WS009', 'BAY 2', 6000, 'Sheryl Joy Balagtas', '2024-10-03', 'PT000221908', '', '2024-10-03 04:09:08', '2024-10-09 03:07:10', 1),
(14, 'ASUS', 'VC239', 'F9LMTJ013618', 'APAC-2024-d1c2E', 'Monitor', 'In Use', 'CL-WS027', 'BAY 2', 6000, 'Joyce Anne Mananquil', '2024-10-03', 'PT000221871', '', '2024-10-03 04:10:54', '2024-10-09 03:07:23', 1),
(15, 'ASUS', 'VC239', 'F9LMTJ013832', 'APAC-2024-f24cQ', 'Monitor', 'In Use', 'CL-WS027', 'BAY 2', 6000, 'Joyce Anne Mananquil', '2024-10-03', 'PT000221883', '', '2024-10-03 04:13:27', '2024-10-09 03:06:47', 1),
(16, 'EPOS Adapt 165', 'SCGD9', '221018982', 'APAC-2024-3ec9T', 'Headset', 'In Use', 'CL-WS009', 'BAY 2', 3250, 'Sheryl Joy Balagtas', '2024-10-03', 'PT000222644', '', '2024-10-03 04:17:01', '2024-10-09 02:59:01', 1),
(17, 'EPOS Adapt 165', 'SCGD9', '221018975', 'APAC-2024-3e69K', 'Headset', 'In Use', 'CL-WS027', 'BAY 2', 3250, 'Joyce Anne Mananquil', '2024-10-03', 'PT000222650', '', '2024-10-03 04:19:17', '2024-10-10 09:49:29', 3),
(27, 'a', 'a', 'a', 'APAC-2024-0cc1J', 'Switches', 'In Use', 'dwa', 'Work From Home', 2, 'awd', '2024-10-11', '1', '', '2024-10-10 03:14:17', '2024-10-10 11:14:17 AM', 3);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `password_reset_id` int(255) NOT NULL,
  `password_reset_code` varchar(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`password_reset_id`, `password_reset_code`, `user_id`, `created_at`) VALUES
(1, '17c4520f6cfd1', 1, '2024-10-03 02:45:40'),
(2, 'e523187b72ad4', 2, '2024-10-03 04:21:32'),
(3, '51fe66cec38d7', 3, '2024-10-09 01:15:39'),
(5, '41bc10bf51f8c', 5, '2024-10-09 04:12:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(13) NOT NULL,
  `username` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `pass_word` varchar(255) NOT NULL,
  `account_type` varchar(255) NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `firstname`, `lastname`, `pass_word`, `account_type`, `created_at`) VALUES
(1, 'superadmin', 'IBOSS', 'ADMIN', '$2y$10$xCBkD.Pp3nq3ZoAPy4SWp.HfW9j9c3hbF5fDRppOIc9lXZMUdHBzq', 'superadmin', '2024-10-03 02:45:40'),
(2, 'jude.garcia@iboss.com', 'Jude', 'Garcia', '$2y$10$HnbyA9N2N6RP995y/4NDXeFRVa8r2Y.j9rsz.9vQvSAIesOi4wKrO', 'admin', '2024-10-03 04:21:32'),
(3, 'rehsmir.mandap@iboss.com', 'Rehsmir', 'Mandap', '$2y$10$6u5sTH8uicNsdTJkFEPbTO11DRX9RJa2TuxEehUjX2wenyvpdy7Pi', 'superadmin', '2024-10-09 01:15:39'),
(5, 'gerardo.batul@iboss.com', 'Gerardo', 'Batul', '$2y$10$gBHFCNLLuOhFMaiLUN7YEOO/ZduRgLfSt/.1JphqcMnDVDtbo3HWu', 'user', '2024-10-09 04:12:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`password_reset_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `password_reset_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(13) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD CONSTRAINT `password_reset_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
