-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 09:36 AM
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
-- Database: `housing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `posted_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `posted_date`) VALUES
(2, 'Water Supply Interruption', 'Water supply will be temporarily unavailable on Sunday from 9 AM to 3 PM due to maintenance work.', '2026-01-08 06:22:06'),
(3, 'Electricity Maintenance Notice', 'Scheduled electricity maintenance will take place on Friday night. Short power outages may occur.', '2026-01-08 06:22:06'),
(4, 'Community Meeting Announcement', 'A community meeting will be held in the community hall on Monday at 6 PM. All residents are encouraged to attend.', '2026-01-08 06:22:06'),
(5, 'Security Awareness Notice', 'Please ensure that all main gates are properly closed after 10 PM for everyone’s safety.', '2026-01-08 06:22:06'),
(6, 'Garbage Collection Schedule Update', 'Garbage collection timings have been updated. Please place garbage outside your homes before 8 AM.', '2026-01-08 06:22:06'),
(7, 'Parking Policy Reminder', 'Unauthorized parking is not allowed in emergency lanes. Vehicles may be towed without notice.', '2026-01-08 06:22:06'),
(8, 'Sports Ground Maintenance', 'The sports ground will remain closed on Tuesday for grass maintenance and cleaning.', '2026-01-08 06:22:06'),
(9, 'Fire Drill Exercise', 'A fire drill will be conducted on Thursday at 11 AM. Please cooperate with security staff.', '2026-01-08 06:22:06'),
(10, 'Festival Decoration Guidelines', 'Residents planning festival decorations are requested to follow society safety guidelines.', '2026-01-08 06:22:06'),
(11, 'Lift Maintenance Notice', 'Lift maintenance work is scheduled for Saturday from 10 AM to 1 PM. Please use stairs during this time.', '2026-01-08 06:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `service_provider_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('open','assigned','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `resident_id`, `service_provider_id`, `title`, `description`, `category`, `status`, `created_at`, `resolved_at`) VALUES
(1, 1, 2, 'dfsa', 'dfsa', 'Water Supply', 'resolved', '2026-01-08 07:34:01', NULL),
(2, 1, 2, 'df', 'ff', 'Painting', 'in_progress', '2026-01-08 07:35:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `availability` enum('available','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`id`, `name`, `description`, `capacity`, `availability`, `created_at`) VALUES
(2, 'Community Hall', 'Main hall for weddings, meetings and events', 300, 'available', '2026-01-08 06:15:39'),
(3, 'Sports Ground', 'Outdoor ground for football and cricket', 500, 'available', '2026-01-08 06:15:39'),
(4, 'Gymnasium', 'Indoor gym with modern fitness equipment', 50, 'available', '2026-01-08 06:15:39'),
(5, 'Swimming Pool', 'Large swimming pool for residents', 100, 'maintenance', '2026-01-08 06:15:39'),
(6, 'Children Play Area', 'Safe play area for children', 80, 'available', '2026-01-08 06:15:39'),
(7, 'Jogging Track', 'Dedicated jogging and walking track', 200, 'available', '2026-01-08 06:15:39'),
(8, 'Basketball Court', 'Full size basketball court', 30, 'available', '2026-01-08 06:15:39'),
(9, 'Tennis Court', 'Professional tennis court', 20, 'maintenance', '2026-01-08 06:15:39'),
(10, 'Badminton Court', 'Indoor badminton court', 20, 'available', '2026-01-08 06:15:39'),
(11, 'Cricket Practice Nets', 'Practice area for cricket players', 40, 'available', '2026-01-08 06:15:39'),
(12, 'Library', 'Quiet library with books and study space', 60, 'available', '2026-01-08 06:15:39'),
(13, 'Conference Room', 'Meeting room with projector and seating', 40, 'available', '2026-01-08 06:15:39'),
(14, 'Guest House', 'Guest accommodation for visitors', 25, 'available', '2026-01-08 06:15:39'),
(15, 'Parking Area', 'Dedicated parking area for residents', 400, 'available', '2026-01-08 06:15:39'),
(16, 'Community Kitchen', 'Shared kitchen for community events', 50, 'maintenance', '2026-01-08 06:15:39'),
(17, 'Prayer Hall', 'Peaceful prayer and meditation hall', 150, 'available', '2026-01-08 06:15:39'),
(18, 'Yoga Room', 'Yoga and meditation room', 30, 'available', '2026-01-08 06:15:39'),
(19, 'Table Tennis Room', 'Indoor table tennis facility', 15, 'available', '2026-01-08 06:15:39'),
(20, 'Snooker Room', 'Snooker and indoor games room', 20, 'available', '2026-01-08 06:15:39'),
(21, 'Open Air Theater', 'Outdoor theater for community events', 250, 'available', '2026-01-08 06:15:39');

-- --------------------------------------------------------

--
-- Table structure for table `facility_bookings`
--

CREATE TABLE `facility_bookings` (
  `id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `resident_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facility_bookings`
--

INSERT INTO `facility_bookings` (`id`, `facility_id`, `resident_id`, `booking_date`, `start_time`, `end_time`, `status`, `reason`, `created_at`) VALUES
(1, 11, 1, '2026-01-16', '15:22:00', '15:22:00', 'pending', 'dsa', '2026-01-08 07:23:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','resident','service_provider') NOT NULL,
  `address` text DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `house_no` varchar(20) DEFAULT NULL,
  `block` varchar(20) DEFAULT NULL,
  `service_type` varchar(50) DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `address`, `profile_picture`, `status`, `house_no`, `block`, `service_type`, `experience_years`, `created_at`, `updated_at`) VALUES
(1, 'NASIR ABBAS', 'residence@gmail.com', '03176526827', '$2y$10$y1X3SAuo5BHi4xn4cn47Qe8mfCsugWpvG/xOUGiIJf8aqiYYAqWpW', 'resident', 'XYZ', 'uploads/profile_1.jpg', 'active', '33', '3', NULL, NULL, '2026-01-08 06:04:46', '2026-01-08 07:16:03'),
(2, 'Service Provider', 'provider@gmail.com', '3176526824', '$2y$10$qZWQGneuSHrK4CWvNwCr8.oE4nxFDeJqho5bxTL05egj2pN8pAqlO', 'service_provider', 'Street jeff xxxx\r\nAp04', 'uploads/profile_2.jpg', 'active', NULL, NULL, 'Plumber', 4, '2026-01-08 06:44:24', '2026-01-08 07:59:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `resident_id` (`resident_id`),
  ADD KEY `service_provider_id` (`service_provider_id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`),
  ADD KEY `resident_id` (`resident_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`service_provider_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `facility_bookings`
--
ALTER TABLE `facility_bookings`
  ADD CONSTRAINT `facility_bookings_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `facility_bookings_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
