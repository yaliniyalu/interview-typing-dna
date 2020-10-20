-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2020 at 08:58 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `interview_typing_dna_prod`
--

-- --------------------------------------------------------

--
-- Table structure for table `candidate_verifications`
--

CREATE TABLE `candidate_verifications` (
  `job_application_id` int(11) NOT NULL,
  `type` enum('Experience','Skills','Email','Mobile','Address','Person','Typing','All Other Details') NOT NULL,
  `status` enum('Pending','Verified','Invalid') NOT NULL DEFAULT 'Pending',
  `data` varchar(25) DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_on` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `interview_details`
--

CREATE TABLE `interview_details` (
  `job_application_id` int(11) NOT NULL,
  `interview_level_id` int(11) NOT NULL,
  `scheduled_date` timestamp NULL DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `attended_on` timestamp NULL DEFAULT NULL,
  `attended_by_photo` char(40) DEFAULT NULL,
  `interviewed_by` int(11) DEFAULT NULL,
  `score` int(10) UNSIGNED DEFAULT NULL,
  `remarks` varchar(250) DEFAULT NULL,
  `status` enum('Promoted','Rejected','Pending') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `interview_levels`
--

CREATE TABLE `interview_levels` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `interview_questions`
--

CREATE TABLE `interview_questions` (
  `id` int(11) NOT NULL,
  `job_application_id` int(11) NOT NULL,
  `interview_level_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `t_dna_match` tinyint(1) DEFAULT 0,
  `t_dna_confidence` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `code` char(32) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `email` varchar(150) NOT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` varchar(250) DEFAULT NULL,
  `avatar` varchar(50) DEFAULT NULL,
  `t_dna_pattern` text DEFAULT NULL,
  `job_post_id` int(11) NOT NULL,
  `skills` varchar(250) DEFAULT NULL,
  `experience` varchar(250) DEFAULT NULL,
  `comments` varchar(250) DEFAULT NULL,
  `details` text NOT NULL,
  `current_level` int(11) DEFAULT NULL,
  `total_scores` int(11) NOT NULL,
  `status` enum('Accepted','Rejected','Selected','Pending') NOT NULL DEFAULT 'Pending',
  `is_active` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `job_posts`
--

CREATE TABLE `job_posts` (
  `id` int(11) NOT NULL,
  `title` varchar(250) NOT NULL,
  `skills` varchar(250) DEFAULT NULL,
  `experience` varchar(250) DEFAULT NULL,
  `salary` varchar(250) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_on` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(250) NOT NULL,
  `password` char(60) NOT NULL,
  `type` enum('Admin','Employee','User') NOT NULL,
  `avatar` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `type`, `avatar`, `is_active`) VALUES
(5, 'Admin', 'admin@interview.yalini.tk', 'admin', 'Admin', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `candidate_verifications`
--
ALTER TABLE `candidate_verifications`
  ADD PRIMARY KEY (`job_application_id`,`type`);

--
-- Indexes for table `interview_details`
--
ALTER TABLE `interview_details`
  ADD PRIMARY KEY (`job_application_id`,`interview_level_id`);

--
-- Indexes for table `interview_levels`
--
ALTER TABLE `interview_levels`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `interview_questions`
--
ALTER TABLE `interview_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `password` (`code`);

--
-- Indexes for table `job_posts`
--
ALTER TABLE `job_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `interview_levels`
--
ALTER TABLE `interview_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `interview_questions`
--
ALTER TABLE `interview_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `job_posts`
--
ALTER TABLE `job_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
