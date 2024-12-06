-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 14, 2023 at 10:02 AM
-- Server version: 8.0.27
-- PHP Version: 7.4.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mobile_marketing_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `compose_message_2`
--

CREATE TABLE IF NOT EXISTS `compose_message_2` (
  `compose_message_id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `sender_mobile_nos` longblob NOT NULL,
  `receiver_mobile_nos` longblob NOT NULL,
  `message_type` varchar(10) NOT NULL,
  `total_mobile_no_count` int NOT NULL,
  `valid_mobile_no_count` int NOT NULL,
  `campaign_name` varchar(30) NOT NULL,
  `cm_status` char(1) NOT NULL,
  `cm_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `compose_msg_media_2`
--

CREATE TABLE IF NOT EXISTS `compose_msg_media_2` (
  `compose_msg_media_id` int NOT NULL,
  `compose_message_id` int NOT NULL,
  `text_title` varchar(50) DEFAULT NULL,
  `text_reply` varchar(50) DEFAULT NULL,
  `text_number` varchar(15) DEFAULT NULL,
  `text_url` varchar(100) DEFAULT NULL,
  `text_address` varchar(100) DEFAULT NULL,
  `media_url` varchar(100) DEFAULT NULL,
  `media_type` varchar(10) DEFAULT NULL,
  `cmm_status` char(1) NOT NULL,
  `cmm_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- Table structure for table `compose_msg_status_2`
--

CREATE TABLE IF NOT EXISTS `compose_msg_status_2` (
  `com_msg_status_id` int NOT NULL,
  `compose_message_id` int NOT NULL,
  `sender_mobile_no` varchar(15) NOT NULL,
  `receiver_mobile_no` varchar(15) NOT NULL,
  `com_msg_content` varchar(2000) NOT NULL,
  `com_msg_status` char(1) NOT NULL,
  `com_msg_entry_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `response_status` char(1) DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL,
  `response_message` varchar(50) DEFAULT NULL,
  `delivery_status` char(1) DEFAULT NULL,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `read_status` char(1) DEFAULT NULL,
  `read_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 ROW_FORMAT=COMPACT;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `compose_message_2`
--
ALTER TABLE `compose_message_2`
  ADD PRIMARY KEY (`compose_message_id`),
  ADD KEY `user_id` (`user_id`,`product_id`);

--
-- Indexes for table `compose_msg_media_2`
--
ALTER TABLE `compose_msg_media_2`
  ADD PRIMARY KEY (`compose_msg_media_id`),
  ADD KEY `compose_whatsapp_id` (`compose_message_id`);

--
-- Indexes for table `compose_msg_status_2`
--
ALTER TABLE `compose_msg_status_2`
  ADD PRIMARY KEY (`com_msg_status_id`),
  ADD KEY `compose_whatsapp_id` (`compose_message_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `compose_message_2`
--
ALTER TABLE `compose_message_2`
  MODIFY `compose_message_id` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `compose_msg_media_2`
--
ALTER TABLE `compose_msg_media_2`
  MODIFY `compose_msg_media_id` int NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `compose_msg_status_2`
--
ALTER TABLE `compose_msg_status_2`
  MODIFY `com_msg_status_id` int NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
