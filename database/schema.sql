-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2026 at 01:35 PM
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
-- Database: `malkiagrid`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  `status` enum('active','disabled') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@malkiagrid.co.tz', '$2y$10$fPjOMvan7Zl5dplhZFqlfe3Q2Uqqx3Vai5Hd6Gbaioe82DrJ0ZS0m', 'super_admin', 'active', '2026-04-27 11:33:53', '2026-04-27 11:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `target_scope` enum('all_users','active_users','pending_users') NOT NULL DEFAULT 'all_users',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_targets`
--

CREATE TABLE `announcement_targets` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefit_categories`
--

CREATE TABLE `benefit_categories` (
  `id` int(11) NOT NULL,
  `name_sw` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `benefit_categories`
--

INSERT INTO `benefit_categories` (`id`, `name_sw`, `name_en`, `is_active`, `created_at`) VALUES
(1, 'Afya', 'Health', 1, '2026-04-27 11:33:53'),
(2, 'Fedha', 'Finance', 1, '2026-04-27 11:33:53'),
(3, 'Mafunzo', 'Training', 1, '2026-04-27 11:33:53'),
(4, 'Urembo', 'Beauty', 1, '2026-04-27 11:33:53'),
(5, 'Usafiri', 'Travel', 1, '2026-04-27 11:33:53'),
(6, 'Biashara', 'Business', 1, '2026-04-27 11:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `benefit_claims`
--

CREATE TABLE `benefit_claims` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `benefit_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','redeemed','cancelled') NOT NULL DEFAULT 'pending',
  `claim_note` text DEFAULT NULL,
  `admin_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `redeemed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefit_claim_logs`
--

CREATE TABLE `benefit_claim_logs` (
  `id` int(11) NOT NULL,
  `claim_id` int(11) NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefit_offers`
--

CREATE TABLE `benefit_offers` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `eligibility` text DEFAULT NULL,
  `minimum_mscore` int(11) DEFAULT 0,
  `claim_limit` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('draft','published','expired','closed') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `benefit_providers`
--

CREATE TABLE `benefit_providers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `category` varchar(150) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` int(11) NOT NULL,
  `name_sw` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `code` varchar(80) NOT NULL,
  `required_for_score` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `name_sw`, `name_en`, `code`, `required_for_score`, `is_active`, `created_at`) VALUES
(1, 'Kitambulisho cha NIDA', 'NIDA ID', 'nida', 1, 1, '2026-04-27 11:33:53'),
(2, 'Cheti cha BRELA', 'BRELA Certificate', 'brela', 1, 1, '2026-04-27 11:33:53'),
(3, 'TIN / TRA', 'TIN / TRA', 'tin_tra', 1, 1, '2026-04-27 11:33:53'),
(4, 'Leseni ya Biashara', 'Business License', 'business_license', 1, 1, '2026-04-27 11:33:53'),
(5, 'Uthibitisho wa Benki', 'Bank Proof', 'bank_proof', 1, 1, '2026-04-27 11:33:53'),
(6, 'Cheti cha Mafunzo', 'Training Certificate', 'training_certificate', 0, 1, '2026-04-27 11:33:53'),
(7, 'Nyingine', 'Other', 'other', 0, 1, '2026-04-27 11:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `document_verification_logs`
--

CREATE TABLE `document_verification_logs` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funding_applications`
--

CREATE TABLE `funding_applications` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount_requested` decimal(15,2) NOT NULL,
  `purpose` text NOT NULL,
  `business_summary` text DEFAULT NULL,
  `repayment_plan` text DEFAULT NULL,
  `status` enum('submitted','under_review','approved','rejected','more_info_requested','disbursed','active_repayment','completed','cancelled','defaulted') NOT NULL DEFAULT 'submitted',
  `admin_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funding_disbursements`
--

CREATE TABLE `funding_disbursements` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `amount_disbursed` decimal(15,2) NOT NULL,
  `disbursement_date` date NOT NULL,
  `disbursed_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funding_repayment_logs`
--

CREATE TABLE `funding_repayment_logs` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `amount_paid` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `funding_repayment_schedules`
--

CREATE TABLE `funding_repayment_schedules` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `amount_due` decimal(15,2) NOT NULL,
  `status` enum('pending','paid','late','waived') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mscore_category_results`
--

CREATE TABLE `mscore_category_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `max_score` int(11) NOT NULL DEFAULT 0,
  `explanation` text DEFAULT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mscore_current_scores`
--

CREATE TABLE `mscore_current_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_score` int(11) NOT NULL DEFAULT 0,
  `profile_score` int(11) NOT NULL DEFAULT 0,
  `document_score` int(11) NOT NULL DEFAULT 0,
  `banking_score` int(11) NOT NULL DEFAULT 0,
  `training_score` int(11) NOT NULL DEFAULT 0,
  `compliance_score` int(11) NOT NULL DEFAULT 0,
  `tier` enum('Beginner','Bronze','Silver','Gold') NOT NULL DEFAULT 'Beginner',
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mscore_score_history`
--

CREATE TABLE `mscore_score_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_score` int(11) NOT NULL DEFAULT 0,
  `profile_score` int(11) NOT NULL DEFAULT 0,
  `document_score` int(11) NOT NULL DEFAULT 0,
  `banking_score` int(11) NOT NULL DEFAULT 0,
  `training_score` int(11) NOT NULL DEFAULT 0,
  `compliance_score` int(11) NOT NULL DEFAULT 0,
  `tier` varchar(50) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_id_counters`
--

CREATE TABLE `m_id_counters` (
  `id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(80) DEFAULT NULL,
  `related_module` varchar(100) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunities`
--

CREATE TABLE `opportunities` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `provider_name` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `eligibility` text DEFAULT NULL,
  `minimum_mscore` int(11) DEFAULT 0,
  `deadline` date DEFAULT NULL,
  `status` enum('draft','published','closed') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunity_applications`
--

CREATE TABLE `opportunity_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `opportunity_id` int(11) NOT NULL,
  `status` enum('submitted','under_review','shortlisted','accepted','rejected','withdrawn') NOT NULL DEFAULT 'submitted',
  `application_note` text DEFAULT NULL,
  `admin_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `opportunity_categories`
--

CREATE TABLE `opportunity_categories` (
  `id` int(11) NOT NULL,
  `name_sw` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `opportunity_categories`
--

INSERT INTO `opportunity_categories` (`id`, `name_sw`, `name_en`, `is_active`, `created_at`) VALUES
(1, 'Ajira', 'Jobs', 1, '2026-04-27 11:33:53'),
(2, 'Biashara', 'Business', 1, '2026-04-27 11:33:53'),
(3, 'Ruzuku', 'Grants', 1, '2026-04-27 11:33:53'),
(4, 'Mafunzo', 'Training', 1, '2026-04-27 11:33:53'),
(5, 'Ushirikiano', 'Partnership', 1, '2026-04-27 11:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

CREATE TABLE `platform_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`) VALUES
(1, 'default_language', 'sw', 'Lugha kuu ya mfumo', NULL, '2026-04-27 11:33:53'),
(2, 'minimum_mscore_for_funding', '60', 'M-SCORE ya chini kwa M-Fund', NULL, '2026-04-27 11:33:53'),
(3, 'minimum_profile_completion_for_funding', '70', 'Ukamilifu wa wasifu kwa M-Fund', NULL, '2026-04-27 11:33:53'),
(4, 'upload_max_file_size_mb', '5', 'Ukubwa wa juu wa faili MB', NULL, '2026-04-27 11:33:53'),
(5, 'support_phone', '+255', 'Namba ya msaada', NULL, '2026-04-27 11:33:53'),
(6, 'support_email', 'support@malkiagrid.co.tz', 'Barua pepe ya msaada', NULL, '2026-04-27 11:33:53'),
(7, 'maintenance_mode', 'off', 'Hali ya matengenezo', NULL, '2026-04-27 11:33:53');

-- --------------------------------------------------------

--
-- Table structure for table `training_programs`
--

CREATE TABLE `training_programs` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `provider` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `delivery_mode` enum('physical','online','hybrid') NOT NULL DEFAULT 'physical',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `minimum_mscore` int(11) DEFAULT 0,
  `status` enum('draft','published','closed','completed') NOT NULL DEFAULT 'draft',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_registrations`
--

CREATE TABLE `training_registrations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `registration_status` enum('pending','approved','rejected','waitlisted','cancelled') NOT NULL DEFAULT 'pending',
  `participation_status` enum('not_started','attended','completed','no_show') NOT NULL DEFAULT 'not_started',
  `certificate_status` enum('none','issued','verified') NOT NULL DEFAULT 'none',
  `admin_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `m_id` varchar(30) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `surname` varchar(100) NOT NULL,
  `nida_number` varchar(50) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('pending','active','rejected','suspended') NOT NULL DEFAULT 'pending',
  `preferred_language` varchar(10) NOT NULL DEFAULT 'sw',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_documents`
--

CREATE TABLE `user_documents` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `status` enum('pending','verified','rejected','resubmission_requested') NOT NULL DEFAULT 'pending',
  `version_number` int(11) NOT NULL DEFAULT 1,
  `previous_document_id` int(11) DEFAULT NULL,
  `admin_comment` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `ward` varchar(100) DEFAULT NULL,
  `street` varchar(150) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `has_registered_business` enum('yes','no') DEFAULT 'no',
  `business_name` varchar(150) DEFAULT NULL,
  `business_type` varchar(100) DEFAULT NULL,
  `business_sector` varchar(150) DEFAULT NULL,
  `business_registration_number` varchar(100) DEFAULT NULL,
  `has_bank_account` enum('yes','no') DEFAULT 'no',
  `bank_name` varchar(150) DEFAULT NULL,
  `mobile_money_number` varchar(30) DEFAULT NULL,
  `heard_about` varchar(150) DEFAULT NULL,
  `profile_completion` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_training_records`
--

CREATE TABLE `user_training_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `certificate_file` varchar(255) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `announcement_targets`
--
ALTER TABLE `announcement_targets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `benefit_categories`
--
ALTER TABLE `benefit_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `benefit_claims`
--
ALTER TABLE `benefit_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `benefit_id` (`benefit_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `benefit_claim_logs`
--
ALTER TABLE `benefit_claim_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `benefit_offers`
--
ALTER TABLE `benefit_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `provider_id` (`provider_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `benefit_providers`
--
ALTER TABLE `benefit_providers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `document_verification_logs`
--
ALTER TABLE `document_verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `funding_applications`
--
ALTER TABLE `funding_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_number` (`reference_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `funding_disbursements`
--
ALTER TABLE `funding_disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `disbursed_by` (`disbursed_by`);

--
-- Indexes for table `funding_repayment_logs`
--
ALTER TABLE `funding_repayment_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `funding_repayment_schedules`
--
ALTER TABLE `funding_repayment_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `mscore_category_results`
--
ALTER TABLE `mscore_category_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mscore_current_scores`
--
ALTER TABLE `mscore_current_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `mscore_score_history`
--
ALTER TABLE `mscore_score_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `m_id_counters`
--
ALTER TABLE `m_id_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `year` (`year`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `opportunity_applications`
--
ALTER TABLE `opportunity_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_opportunity` (`user_id`,`opportunity_id`),
  ADD KEY `opportunity_id` (`opportunity_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `opportunity_categories`
--
ALTER TABLE `opportunity_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `training_programs`
--
ALTER TABLE `training_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_training` (`user_id`,`training_id`),
  ADD KEY `training_id` (`training_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_id` (`m_id`),
  ADD UNIQUE KEY `nida_number` (`nida_number`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `previous_document_id` (`previous_document_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_training_records`
--
ALTER TABLE `user_training_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `training_id` (`training_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_logs`
--
ALTER TABLE `admin_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement_targets`
--
ALTER TABLE `announcement_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_categories`
--
ALTER TABLE `benefit_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `benefit_claims`
--
ALTER TABLE `benefit_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_claim_logs`
--
ALTER TABLE `benefit_claim_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_offers`
--
ALTER TABLE `benefit_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `benefit_providers`
--
ALTER TABLE `benefit_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `document_verification_logs`
--
ALTER TABLE `document_verification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funding_applications`
--
ALTER TABLE `funding_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funding_disbursements`
--
ALTER TABLE `funding_disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funding_repayment_logs`
--
ALTER TABLE `funding_repayment_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `funding_repayment_schedules`
--
ALTER TABLE `funding_repayment_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mscore_category_results`
--
ALTER TABLE `mscore_category_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mscore_current_scores`
--
ALTER TABLE `mscore_current_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mscore_score_history`
--
ALTER TABLE `mscore_score_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_id_counters`
--
ALTER TABLE `m_id_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunities`
--
ALTER TABLE `opportunities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunity_applications`
--
ALTER TABLE `opportunity_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `opportunity_categories`
--
ALTER TABLE `opportunity_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `platform_settings`
--
ALTER TABLE `platform_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `training_programs`
--
ALTER TABLE `training_programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_registrations`
--
ALTER TABLE `training_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_documents`
--
ALTER TABLE `user_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_training_records`
--
ALTER TABLE `user_training_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_logs`
--
ALTER TABLE `admin_logs`
  ADD CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `announcement_targets`
--
ALTER TABLE `announcement_targets`
  ADD CONSTRAINT `announcement_targets_ibfk_1` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `announcement_targets_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `benefit_claims`
--
ALTER TABLE `benefit_claims`
  ADD CONSTRAINT `benefit_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `benefit_claims_ibfk_2` FOREIGN KEY (`benefit_id`) REFERENCES `benefit_offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `benefit_claims_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `benefit_claim_logs`
--
ALTER TABLE `benefit_claim_logs`
  ADD CONSTRAINT `benefit_claim_logs_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `benefit_claims` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `benefit_claim_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `benefit_offers`
--
ALTER TABLE `benefit_offers`
  ADD CONSTRAINT `benefit_offers_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `benefit_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `benefit_offers_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `benefit_providers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `benefit_offers_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `document_verification_logs`
--
ALTER TABLE `document_verification_logs`
  ADD CONSTRAINT `document_verification_logs_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `user_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_verification_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_verification_logs_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `funding_applications`
--
ALTER TABLE `funding_applications`
  ADD CONSTRAINT `funding_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `funding_applications_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `funding_disbursements`
--
ALTER TABLE `funding_disbursements`
  ADD CONSTRAINT `funding_disbursements_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `funding_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `funding_disbursements_ibfk_2` FOREIGN KEY (`disbursed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `funding_repayment_logs`
--
ALTER TABLE `funding_repayment_logs`
  ADD CONSTRAINT `funding_repayment_logs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `funding_applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `funding_repayment_logs_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `funding_repayment_schedules` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `funding_repayment_logs_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `funding_repayment_schedules`
--
ALTER TABLE `funding_repayment_schedules`
  ADD CONSTRAINT `funding_repayment_schedules_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `funding_applications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mscore_category_results`
--
ALTER TABLE `mscore_category_results`
  ADD CONSTRAINT `mscore_category_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mscore_current_scores`
--
ALTER TABLE `mscore_current_scores`
  ADD CONSTRAINT `mscore_current_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mscore_score_history`
--
ALTER TABLE `mscore_score_history`
  ADD CONSTRAINT `mscore_score_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `opportunities`
--
ALTER TABLE `opportunities`
  ADD CONSTRAINT `opportunities_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `opportunity_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `opportunities_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `opportunity_applications`
--
ALTER TABLE `opportunity_applications`
  ADD CONSTRAINT `opportunity_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opportunity_applications_ibfk_2` FOREIGN KEY (`opportunity_id`) REFERENCES `opportunities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `opportunity_applications_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `platform_settings`
--
ALTER TABLE `platform_settings`
  ADD CONSTRAINT `platform_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_programs`
--
ALTER TABLE `training_programs`
  ADD CONSTRAINT `training_programs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD CONSTRAINT `training_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_registrations_ibfk_2` FOREIGN KEY (`training_id`) REFERENCES `training_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `training_registrations_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_documents`
--
ALTER TABLE `user_documents`
  ADD CONSTRAINT `user_documents_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_documents_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_documents_ibfk_3` FOREIGN KEY (`previous_document_id`) REFERENCES `user_documents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_documents_ibfk_4` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_training_records`
--
ALTER TABLE `user_training_records`
  ADD CONSTRAINT `user_training_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_training_records_ibfk_2` FOREIGN KEY (`training_id`) REFERENCES `training_programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_training_records_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
