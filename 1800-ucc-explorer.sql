-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 30, 2025 at 01:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `1800-ucc-explorer`
--

-- --------------------------------------------------------

--
-- Table structure for table `ucc_assignees`
--

CREATE TABLE `ucc_assignees` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_buyers`
--

CREATE TABLE `ucc_buyers` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_adress1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_adress2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_fax` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_fips` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_county` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_sic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_sic_desc` text COLLATE utf8mb4_unicode_ci,
  `buyer_duns` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_contacts`
--

CREATE TABLE `ucc_contacts` (
  `id` int NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firstname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_equipments`
--

CREATE TABLE `ucc_equipments` (
  `id` int NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_filing_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_ucc_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_desc` text COLLATE utf8mb4_unicode_ci,
  `equipment_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_serial_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_end_year` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_attachment` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `equipment_tae` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_files`
--

CREATE TABLE `ucc_files` (
  `id` int UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_imported` int DEFAULT '0',
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_filings`
--

CREATE TABLE `ucc_filings` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assignee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_contact_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_contact_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_lease_acqui_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_lien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_comments` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_fips2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ucc_batch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ucc_providers`
--

CREATE TABLE `ucc_providers` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_company` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ucc_assignees`
--
ALTER TABLE `ucc_assignees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_buyers`
--
ALTER TABLE `ucc_buyers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_contacts`
--
ALTER TABLE `ucc_contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_equipments`
--
ALTER TABLE `ucc_equipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_files`
--
ALTER TABLE `ucc_files`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_filings`
--
ALTER TABLE `ucc_filings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ucc_providers`
--
ALTER TABLE `ucc_providers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ucc_contacts`
--
ALTER TABLE `ucc_contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `ucc_equipments`
--
ALTER TABLE `ucc_equipments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=951;

--
-- AUTO_INCREMENT for table `ucc_files`
--
ALTER TABLE `ucc_files`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
