-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mariadb:3306
-- Generation Time: Feb 10, 2026 at 10:11 AM
-- Server version: 12.1.2-MariaDB-ubu2404
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_thai_520_w2;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `full_name`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$wMFQWxLAOtJEMLdF6tAUpul3VNVAjkAL3Gk9TPkrwed1zGHm2va3e', 'System Admin', NULL, '2026-02-10 07:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_thai_520_w2;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`) VALUES
(1, 'TP-Link');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_thai_520_w2;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`) VALUES
(1, 'Computer'),
(2, 'Server'),
(3, 'Rack'),
(4, 'Cable'),
(5, 'Router'),
(6, 'Switch'),
(7, 'Access Point'),
(8, 'Controller'),
(9, 'Network Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `version` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `image_path` varchar(255) DEFAULT NULL,
  `datasheet_path` varchar(255) DEFAULT NULL,
  `stock_qty` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_thai_520_w2;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `brand_id`, `version`, `model`, `price`, `unit`, `specifications`, `image_path`, `datasheet_path`, `stock_qty`) VALUES
(1, 6, 1, 'JetStream', 'TL-SG3428XF', 20000.00, 'เครื่อง', '{\"detail\":\"JetStream 24-Port SFP L2+ Managed Switch with 4 10GE SFP+ Slots\\r\\n10G Lightning-Fast Uplink: 4× 10 Gbps SFP+ slots enable high-bandwidth connectivity and non-blocking switching capacity. \\r\\nGigabit Fiber Speeds: 20× gigabit SFP ports and 4× gigabit SFP\\/RJ45 Combo ports s offer high-speed and reliable connections to other switches and devices.\\r\\nIntegrated into Omada SDN: Zero-Touch Provisioning (ZTP)*, Centralized Cloud Management, and Intelligent Monitoring.\\r\\nCentralized Management: Cloud access and Omada app for ultra convenience and easy management.\\r\\nStatic Routing: Helps route internal traffic for more efficient use of network resources.\\r\\nRobust Security Strategies: IP-MAC-Port Binding, ACL, Port Security, DoS Defend, Storm control, DHCP Snooping, 802.1X, Radius Authentication, and more.\\r\\nOptimize Voice and Video Applications: L2\\/L3\\/L4 QoS and IGMP snooping.\\r\\nStandalone Management: Web, CLI (Console Port, Telnet, SSH), SNMP, RMON, and Dual\"}', 'uploads/images/img_1770712109_TL-SG3428XF_UN_1_01_large_20220530031236v.jpg', 'uploads/docs/doc_1770712109_SDN 10-Gigabit L2+ Managed Switch.pdf', 0),
(2, 7, 1, 'Omada', 'OC300', 6490.00, 'เครื่อง', '{\"detail\":\"OC300\\r\\nOmada Hardware Controller\\r\\nCentralized Management: Up to 500 Omada access points, 100 JetStream switches, and 100 Omada routers.\\r\\nFree Cloud Access: Manage and monitor with the Omada app or Web UI from anywhere, anytime.\\r\\nOn-Premises Management: Locally monitor and manage devices with the ultimate security and stability.\\r\\nIndustry-Leading Hardware Design: A powerful chipset, durable metal casing, USB 3.0 port for auto backup, and two Gigabit ports.\\r\\nEasy and Intelligent Network Monitoring: The easy-to-use dashboard makes it simple to see the real-time network status and traffic distribution.\\r\\nReal-Time Network Topology: Helps IT admins quickly see and troubleshoot connections at a glance.\\r\\nEasier Network Maintenance: WiFi heatmap simulator, visualizable network report, and batch & multi-site management benefit network maintenance.\"}', 'uploads/images/img_1770712715_OC300_overview_01_large_20250126021819o.jpg', 'uploads/docs/doc_1770712715_OC300_Datasheet.pdf', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `2` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
