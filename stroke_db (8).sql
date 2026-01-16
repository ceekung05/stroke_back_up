-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 10:45 AM
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
-- Database: `stroke_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_er`
--

CREATE TABLE `tbl_er` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `consult_neuro_datetime` datetime DEFAULT NULL COMMENT 'การส่งปรึกษาแพทย์เฉพาะทางของประสาทวิทยา',
  `ctnc_datetime` datetime DEFAULT NULL COMMENT 'การวินิจฉัย และ Imaging',
  `cta_datetime` datetime DEFAULT NULL,
  `mri_datetime` datetime DEFAULT NULL,
  `consult_intervention_datetime` datetime DEFAULT NULL COMMENT 'Neuro-Interventionist',
  `aspect_score` tinyint(4) DEFAULT NULL COMMENT 'ผล CT/CTA',
  `collateral_score` tinyint(4) DEFAULT NULL,
  `occlusion_site` varchar(100) DEFAULT NULL,
  `ct_result` enum('ischemic','hemorrhagic') DEFAULT NULL COMMENT 'ผล CT (Ischemic / Hemorrhagic)',
  `fibrinolytic_type` enum('rtpa','sk','tnk','no') DEFAULT NULL COMMENT 'Fibrinolytic',
  `tpa_datetime` datetime DEFAULT NULL COMMENT 'วันเวลาเริ่มยา Fibrinolytic',
  `anesthesia_set_datetime` datetime DEFAULT NULL COMMENT 'set ดมยา',
  `activate_team_datetime` datetime DEFAULT NULL COMMENT 'Activate Team',
  `consult_neurosurgeon_datetime` datetime DEFAULT NULL COMMENT 'วันเวลาปรึกษา Neurosurgeon',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `time_door_to_doctor_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลา Door to Doctor (นาที)',
  `time_door_to_ct_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลา Door to CT (นาที)',
  `time_door_to_cta_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลา Door to CTA (นาที)',
  `time_door_to_intervention_min` int(11) DEFAULT NULL COMMENT 'Door to Interventionist (นาที)',
  `time_door_to_needle_min` int(11) DEFAULT NULL COMMENT 'Door to Needle (นาที)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางลูก: เก็บข้อมูล ER (ฟอร์มที่ 2)';

--
-- Dumping data for table `tbl_er`
--

INSERT INTO `tbl_er` (`id`, `admission_id`, `consult_neuro_datetime`, `ctnc_datetime`, `cta_datetime`, `mri_datetime`, `consult_intervention_datetime`, `aspect_score`, `collateral_score`, `occlusion_site`, `ct_result`, `fibrinolytic_type`, `tpa_datetime`, `anesthesia_set_datetime`, `activate_team_datetime`, `consult_neurosurgeon_datetime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `time_door_to_doctor_min`, `time_door_to_ct_min`, `time_door_to_cta_min`, `time_door_to_intervention_min`, `time_door_to_needle_min`) VALUES
(1, 9, '2025-11-21 10:30:00', '2025-11-21 10:35:00', '2025-11-21 10:40:00', '2025-11-21 10:45:00', '2025-11-21 10:50:00', 7, 3, 'Right M2 of MCA', 'ischemic', 'tnk', '2026-01-12 10:57:00', '2025-11-21 11:00:00', '2025-11-21 11:10:00', '0000-00-00 00:00:00', '2025-11-21 03:27:30', 'System', '2025-11-21 03:27:30', NULL, NULL, NULL, NULL, NULL, NULL),
(2, 8, '2025-12-06 13:47:00', '2025-11-21 13:47:00', '2025-11-21 13:46:00', '2025-11-21 13:49:00', '2025-11-21 16:04:00', 4, 3, 'Right ACA', 'hemorrhagic', NULL, '2026-01-12 00:00:00', NULL, NULL, '0000-00-00 00:00:00', '2025-11-21 06:45:45', 'System', '2025-11-21 06:45:45', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 10, '2025-11-21 16:31:00', '2025-11-22 18:31:00', '2025-11-22 16:32:00', '2025-11-23 16:36:00', '2025-11-26 16:35:00', 7, 3, 'Right Beyond M2 of MCA', 'ischemic', 'sk', '2026-01-12 16:34:00', '2025-11-22 16:35:00', '2025-11-12 16:37:00', '0000-00-00 00:00:00', '2025-11-21 09:32:56', 'System', '2025-11-21 09:32:56', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 11, '2025-12-04 15:24:00', '2025-12-04 14:22:00', '2025-12-04 14:33:00', '2025-12-04 14:33:00', '2025-12-04 14:37:00', 4, 4, 'Left ACA', 'ischemic', 'rtpa', '2026-01-12 14:19:00', '2025-12-04 14:21:00', '2025-12-05 14:21:00', '0000-00-00 00:00:00', '2025-12-04 07:18:59', 'System', '2025-12-04 07:18:59', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 56, '2025-12-22 14:58:00', '2025-12-22 14:00:00', '2025-12-22 17:54:00', '2025-12-22 14:56:00', '2025-12-22 14:57:00', 3, 3, 'Left PCA', 'ischemic', 'sk', '2026-01-12 14:58:00', '2025-12-22 14:00:00', '2025-12-12 14:59:00', '0000-00-00 00:00:00', '2025-12-22 08:01:10', 'System', '2025-12-22 08:01:10', 'System', NULL, NULL, NULL, NULL, NULL),
(6, 57, '2025-12-24 11:31:00', '2025-12-24 11:31:00', '2025-12-24 11:32:00', '2025-12-01 11:29:00', '2025-12-24 11:31:00', 6, 3, 'Left Beyond M2 of MCA', 'hemorrhagic', NULL, '2026-01-12 00:00:00', NULL, NULL, '0000-00-00 00:00:00', '2025-12-24 04:29:44', 'System', '2025-12-24 04:29:44', 'System', NULL, NULL, NULL, NULL, NULL),
(7, 60, '2025-12-29 00:23:00', '2025-12-29 11:25:00', '2025-12-29 11:25:00', '2025-12-29 11:28:00', '2025-12-29 01:24:00', 4, 3, 'Left ACA', 'hemorrhagic', NULL, '2026-01-12 00:00:00', NULL, NULL, '0000-00-00 00:00:00', '2025-12-29 04:24:10', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:24:10', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL, NULL),
(8, 62, '2026-01-09 14:48:00', '2026-01-09 14:48:00', '2026-01-09 14:51:00', '2026-01-09 17:48:00', '2026-01-09 14:51:00', 3, 1, 'Right Beyond M2 of MCA', 'hemorrhagic', NULL, '2026-01-12 00:00:00', NULL, NULL, '0000-00-00 00:00:00', '2026-01-09 07:49:07', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 07:49:07', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL, NULL),
(9, 67, '2026-01-12 11:50:00', '2026-01-12 12:00:00', '2026-01-12 12:32:00', '2026-01-12 12:40:00', '2026-01-12 11:50:00', 4, 2, 'Left PCA', 'ischemic', 'no', '2026-01-12 12:50:00', '2026-01-12 13:05:00', '2026-01-12 13:15:00', '2026-01-12 14:32:00', '2026-01-12 03:52:15', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:27:39', '0', 10, 20, 52, 10, 70),
(15, 68, '2026-01-13 15:15:00', '2026-01-13 15:30:00', '2026-01-13 15:35:00', '2026-01-13 15:45:00', '2026-01-13 16:12:00', 7, 3, 'Right ACA', 'hemorrhagic', NULL, NULL, NULL, NULL, '2026-01-13 16:30:00', '2026-01-13 07:22:11', '0', '2026-01-13 07:22:11', '0', 5, 20, 25, 62, NULL),
(16, 66, '2026-01-10 11:32:00', '2026-01-14 12:20:00', '2026-01-14 12:32:00', '2026-01-14 12:42:00', '2026-01-14 12:55:00', 3, 2, 'Intracranial left ICA', 'ischemic', NULL, '2026-01-14 13:01:00', '2026-01-14 13:10:00', '2026-01-14 13:15:00', NULL, '2026-01-14 07:44:18', '0', '2026-01-14 07:44:18', '0', 1532, 7340, 7352, 7375, 7381),
(17, 71, NULL, NULL, NULL, NULL, NULL, 0, 0, '', 'hemorrhagic', NULL, NULL, NULL, NULL, NULL, '2026-01-15 06:32:18', '0', '2026-01-15 06:32:18', '0', NULL, NULL, NULL, NULL, NULL),
(18, 75, '2026-01-16 14:10:00', '2026-01-16 14:20:00', '2026-01-16 14:30:00', '2026-01-16 14:40:00', '2026-01-16 14:50:00', 4, 2, 'Right M1 of MCA', 'hemorrhagic', NULL, NULL, NULL, NULL, '2026-01-16 14:59:00', '2026-01-16 09:35:54', '0', '2026-01-16 09:35:54', '0', 10, 20, 30, 50, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_followup`
--

CREATE TABLE `tbl_followup` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `followup_label` varchar(100) NOT NULL COMMENT 'การติดตามผล',
  `scheduled_date` date NOT NULL COMMENT 'วันที่นัดหมาย',
  `status` enum('pending','attended','no_show') NOT NULL DEFAULT 'pending' COMMENT 'สถานะ',
  `mrs_score` tinyint(4) DEFAULT NULL COMMENT 'mRS Score ในระบบนัดหมายติดตามผล',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางลูก 1:N เก็บรายการนัด Follow-up (mRS 1, 3, 6, 12)';

--
-- Dumping data for table `tbl_followup`
--

INSERT INTO `tbl_followup` (`id`, `admission_id`, `followup_label`, `scheduled_date`, `status`, `mrs_score`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 9, 'mRS 1 เดือน', '2025-12-20', 'attended', 1, '2025-11-21 04:46:57', '', '2025-11-21 04:47:04', ''),
(2, 9, 'mRS 3 เดือน', '2026-02-20', 'attended', 1, '2025-11-21 04:46:57', '', '2025-11-21 04:47:10', ''),
(3, 9, 'mRS 6 เดือน', '2026-05-20', 'attended', 1, '2025-11-21 04:46:57', '', '2025-11-21 04:47:12', ''),
(4, 9, 'mRS 12 เดือน', '2026-11-20', 'attended', 1, '2025-11-21 04:46:57', '', '2025-11-21 04:47:14', ''),
(5, 8, 'mRS 1 เดือน', '2025-12-23', 'attended', 6, '2025-11-21 06:54:56', '', '2025-11-21 06:55:04', ''),
(6, 8, 'mRS 3 เดือน', '2026-02-23', 'pending', NULL, '2025-11-21 06:54:56', '', '2025-11-21 06:54:56', ''),
(7, 8, 'mRS 6 เดือน', '2026-05-23', 'pending', NULL, '2025-11-21 06:54:56', '', '2025-11-21 06:54:56', ''),
(8, 8, 'mRS 12 เดือน', '2026-11-23', 'pending', NULL, '2025-11-21 06:54:56', '', '2025-11-21 06:54:56', ''),
(9, 56, 'mRS 1 เดือน', '2026-01-23', 'attended', 2, '2025-12-23 07:13:08', 'System', '2025-12-23 07:14:45', 'System'),
(10, 56, 'mRS 3 เดือน', '2026-03-23', 'pending', NULL, '2025-12-23 07:13:08', 'System', '2025-12-23 07:13:08', 'System'),
(11, 56, 'mRS 6 เดือน', '2026-06-23', 'pending', NULL, '2025-12-23 07:13:08', 'System', '2025-12-23 07:13:08', 'System'),
(12, 56, 'mRS 12 เดือน', '2026-12-23', 'pending', NULL, '2025-12-23 07:13:08', 'System', '2025-12-23 07:13:08', 'System'),
(17, 57, 'mRS 1 เดือน', '2026-01-25', 'pending', NULL, '2025-12-25 08:08:02', 'System', '2025-12-25 08:08:02', 'System'),
(18, 57, 'mRS 3 เดือน', '2026-03-25', 'pending', NULL, '2025-12-25 08:08:02', 'System', '2025-12-25 08:08:02', 'System'),
(19, 57, 'mRS 6 เดือน', '2026-06-25', 'pending', NULL, '2025-12-25 08:08:02', 'System', '2025-12-25 08:08:02', 'System'),
(20, 57, 'mRS 12 เดือน', '2026-12-25', 'pending', NULL, '2025-12-25 08:08:02', 'System', '2025-12-25 08:08:02', 'System'),
(21, 11, 'mRS 1 เดือน', '2026-01-25', 'attended', 0, '2025-12-25 08:10:07', 'System', '2025-12-25 08:10:44', 'System'),
(22, 11, 'mRS 3 เดือน', '2026-03-25', 'no_show', NULL, '2025-12-25 08:10:07', 'System', '2025-12-25 08:10:49', 'System'),
(23, 11, 'mRS 6 เดือน', '2026-06-25', 'no_show', NULL, '2025-12-25 08:10:07', 'System', '2025-12-25 08:10:51', 'System'),
(24, 11, 'mRS 12 เดือน', '2026-12-25', 'no_show', NULL, '2025-12-25 08:10:07', 'System', '2025-12-25 08:10:53', 'System'),
(25, 10, 'mRS 1 เดือน', '2026-01-25', 'attended', 0, '2025-12-25 08:22:14', 'System', '2025-12-25 08:23:37', 'System'),
(26, 10, 'mRS 3 เดือน', '2026-03-25', 'attended', 1, '2025-12-25 08:22:14', 'System', '2025-12-25 08:35:07', 'System'),
(27, 10, 'mRS 6 เดือน', '2026-08-26', 'attended', 5, '2025-12-25 08:22:14', 'System', '2025-12-25 08:40:26', 'System'),
(28, 10, 'mRS 12 เดือน', '2026-12-25', 'pending', NULL, '2025-12-25 08:22:14', 'System', '2025-12-25 08:22:14', 'System'),
(29, 60, 'mRS 1 เดือน', '2026-01-29', 'pending', NULL, '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(30, 60, 'mRS 3 เดือน', '2026-03-29', 'pending', NULL, '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(31, 60, 'mRS 6 เดือน', '2026-06-29', 'pending', NULL, '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(32, 60, 'mRS 12 เดือน', '2026-12-29', 'pending', NULL, '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:29:12', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(33, 67, 'mRS 1 เดือน', '2026-02-12', 'pending', NULL, '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(34, 67, 'mRS 3 เดือน', '2026-04-12', 'pending', NULL, '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(35, 67, 'mRS 6 เดือน', '2026-07-12', 'pending', NULL, '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(36, 67, 'mRS 12 เดือน', '2027-01-12', 'pending', NULL, '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:02:36', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(37, 62, 'mRS 1 เดือน', '2026-02-12', 'pending', NULL, '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(38, 62, 'mRS 3 เดือน', '2026-04-12', 'pending', NULL, '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(39, 62, 'mRS 6 เดือน', '2026-07-12', 'pending', NULL, '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(40, 62, 'mRS 12 เดือน', '2027-01-12', 'pending', NULL, '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:11:31', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(41, 68, 'mRS 1 เดือน', '2026-02-13', 'attended', 1, '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:36:47', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(42, 68, 'mRS 3 เดือน', '2026-04-13', 'pending', NULL, '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(43, 68, 'mRS 6 เดือน', '2026-07-13', 'pending', NULL, '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(44, 68, 'mRS 12 เดือน', '2027-01-13', 'pending', NULL, '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:36:40', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(45, 75, 'mRS 1 เดือน', '2026-02-17', 'attended', 4, '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:37:51', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(46, 75, 'mRS 3 เดือน', '2026-04-18', 'no_show', NULL, '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:37:54', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(47, 75, 'mRS 6 เดือน', '2026-07-16', 'pending', NULL, '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(48, 75, 'mRS 12 เดือน', '2027-01-16', 'pending', NULL, '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:37:33', 'สุขใจ (ทดสอบ) ซ่อมไว');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_or_procedure`
--

CREATE TABLE `tbl_or_procedure` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `procedure_type` enum('mt','hemo') NOT NULL,
  `mt_anesthesia_datetime` datetime DEFAULT NULL COMMENT 'anesthesia_time',
  `mt_puncture_datetime` datetime DEFAULT NULL COMMENT 'puncture_time',
  `mt_recanalization_datetime` datetime DEFAULT NULL COMMENT 'Recalnaligation Time',
  `mt_occlusion_vessel` varchar(100) DEFAULT NULL COMMENT 'occlusionvessel',
  `mt_tici_score` enum('0','1','2a','2b','3') DEFAULT NULL COMMENT 'TICI Score (ผลลัพธ์การเปิดเส้นเลือด)',
  `mt_procedure_technique` varchar(50) DEFAULT NULL COMMENT 'Procedure Technique',
  `mt_pass_count` tinyint(4) DEFAULT NULL COMMENT 'เปิดกี่ครั้ง',
  `mt_med_integrilin` tinyint(1) DEFAULT 0 COMMENT 'Integrilin',
  `mt_integrilin_bolus` decimal(5,2) DEFAULT NULL COMMENT 'Integrilin_bolus',
  `mt_integrilin_drip` decimal(5,2) DEFAULT NULL COMMENT 'Integrilin_drip',
  `mt_med_nimodipine` tinyint(1) DEFAULT 0 COMMENT 'Nimodipine',
  `mt_nimodipine_bolus` decimal(5,2) DEFAULT NULL COMMENT 'Nimodipine_bolus',
  `mt_nimodipine_drip` decimal(5,2) DEFAULT NULL COMMENT 'Nimodipine_drip',
  `mt_xray_dose` decimal(10,2) DEFAULT NULL COMMENT 'Dose X-ray (mGy)',
  `mt_flu_time` decimal(5,2) DEFAULT NULL COMMENT 'Flu time (min)',
  `mt_cone_beam_ct` tinyint(1) DEFAULT NULL COMMENT 'cone_beam_ct',
  `mt_cone_beam_ct_details` varchar(255) DEFAULT NULL COMMENT 'cone_beam_ct_details',
  `hemo_location` varchar(255) DEFAULT NULL COMMENT 'Location (ตำแหน่งเลือดออก)',
  `hemo_volume_cc` decimal(6,2) DEFAULT NULL COMMENT 'Hemorrhage (CC) (ปริมาตรเลือด)',
  `hemo_proc_craniotomy` tinyint(1) DEFAULT 0 COMMENT 'หัตถการที่ทำ',
  `hemo_proc_craniectomy` tinyint(1) DEFAULT 0 COMMENT 'หัตถการที่ทำ',
  `hemo_proc_ventriculostomy` tinyint(1) DEFAULT 0 COMMENT 'หัตถการที่ทำ',
  `complication_details` varchar(255) DEFAULT NULL COMMENT 'complication',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `time_door_to_puncture_min` int(11) DEFAULT NULL COMMENT 'Door to Puncture (นาที)',
  `time_onset_to_recanalization_min` int(11) DEFAULT NULL COMMENT 'Onset to Recanalization (นาที)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางลูก: เก็บข้อมูลหัตถการ (ฟอร์มที่ 3)';

--
-- Dumping data for table `tbl_or_procedure`
--

INSERT INTO `tbl_or_procedure` (`id`, `admission_id`, `procedure_type`, `mt_anesthesia_datetime`, `mt_puncture_datetime`, `mt_recanalization_datetime`, `mt_occlusion_vessel`, `mt_tici_score`, `mt_procedure_technique`, `mt_pass_count`, `mt_med_integrilin`, `mt_integrilin_bolus`, `mt_integrilin_drip`, `mt_med_nimodipine`, `mt_nimodipine_bolus`, `mt_nimodipine_drip`, `mt_xray_dose`, `mt_flu_time`, `mt_cone_beam_ct`, `mt_cone_beam_ct_details`, `hemo_location`, `hemo_volume_cc`, `hemo_proc_craniotomy`, `hemo_proc_craniectomy`, `hemo_proc_ventriculostomy`, `complication_details`, `created_at`, `created_by`, `updated_at`, `updated_by`, `time_door_to_puncture_min`, `time_onset_to_recanalization_min`) VALUES
(1, 9, 'mt', '2025-11-21 11:38:00', '2025-11-21 11:42:00', '2025-11-21 11:48:00', 'Left ACA', '1', 'aspiration alone', 10, 1, 2.00, 1.00, 1, 5.00, 4.00, 120.00, 220.00, 1, 'test11', '', 0.00, 0, 0, 0, 'ไม่มีภาวะแทรกซ้อน', '2025-11-21 04:09:32', 'System', '2025-11-21 04:38:23', '0', NULL, NULL),
(8, 8, 'hemo', NULL, NULL, NULL, 'Left ICA', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'xfxxff', 500.00, 1, 1, 1, 'การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด', '2025-11-21 06:46:44', 'System', '2025-11-21 06:46:44', '0', NULL, NULL),
(9, 56, 'mt', '2025-12-23 14:05:00', '2025-12-23 14:06:00', '2025-12-23 14:07:00', 'Left ICA', '2a', 'aspiration alone', 3, 1, 23.00, 12.00, 0, 0.00, 0.00, 2223.00, 999.99, 1, 'test222', 'retetwte', 22.00, 0, 0, 1, 'ไม่มีภาวะแทรกซ้อน', '2025-12-23 07:08:27', 'System', '2025-12-24 04:12:40', 'System', NULL, NULL),
(11, 57, 'hemo', NULL, NULL, NULL, 'Left ICA', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'test221', 2.00, 0, 0, 1, 'มีภาวะเลือดออกในสมอง', '2025-12-25 08:01:29', 'System', '2025-12-25 08:01:29', 'System', NULL, NULL),
(12, 11, 'mt', '2025-12-25 15:09:00', '2025-12-25 15:11:00', '2025-12-25 15:09:00', 'Left ICA', '1', 'aspiration alone', 1, 1, 2.00, 2.00, 0, 0.00, 0.00, 22.00, 33.00, 0, '', '', 0.00, 0, 0, 0, 'มีภาวะเลือดออกในสมอง', '2025-12-25 08:09:20', 'System', '2025-12-25 08:09:20', 'System', NULL, NULL),
(13, 10, 'hemo', NULL, NULL, NULL, 'Left ICA', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'ไไำไ', 3231.00, 0, 0, 1, 'ไม่มีภาวะแทรกซ้อน', '2025-12-25 08:21:31', 'System', '2025-12-25 08:21:31', 'System', NULL, NULL),
(14, 60, 'hemo', NULL, NULL, NULL, 'Left ICA', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'กกก', 2.00, 0, 0, 1, 'การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด', '2025-12-29 04:25:46', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:25:46', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL),
(15, 67, 'mt', '2026-01-12 13:25:00', '2026-01-12 13:54:00', '2026-01-12 13:59:00', '0', '1', 'aspiration alone', 1, 0, 0.00, 0.00, 1, 23.00, 12.00, 25.00, 3.00, 1, 'ะำหะ', 'testtest', 12.00, 1, 1, 0, 'มีภาวะเลือดออกในสมอง', '2026-01-12 03:56:02', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:34:14', 'สุขใจ (ทดสอบ) ซ่อมไว', 134, 209),
(16, 62, 'hemo', NULL, NULL, NULL, 'Left ICA', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'dff', 342.00, 0, 1, 0, 'การบาดเจ็บต่อหลอดเลือด เช่น ทะลุ ฉีดขาด', '2026-01-12 07:03:13', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:03:13', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL),
(18, 68, 'mt', '2026-01-13 16:25:00', '2026-01-13 16:45:00', '2026-01-13 16:55:00', '0', '2a', 'Solumbra', 2, 1, 23.00, 23.00, 0, 0.00, 0.00, 3.00, 5.00, 1, 'tret', '', 0.00, 0, 0, 0, 'มีภาวะเลือดออกในสมอง', '2026-01-13 07:29:34', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:29:34', 'สุขใจ (ทดสอบ) ซ่อมไว', 95, 1700),
(19, 71, 'hemo', NULL, NULL, NULL, '0', '0', 'aspiration alone', 1, 0, 3.00, 55.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', '', 0.00, 0, 0, 0, 'มีภาวะเลือดออกในสมอง', '2026-01-15 06:33:03', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 06:33:03', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL),
(20, 72, 'hemo', NULL, NULL, NULL, '0', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', '', 0.00, 0, 0, 0, 'ไม่มีภาวะแทรกซ้อน', '2026-01-15 07:02:28', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 07:02:28', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL),
(21, 75, 'hemo', NULL, NULL, NULL, '0', '0', 'aspiration alone', 1, 0, 0.00, 0.00, 0, 0.00, 0.00, 0.00, 0.00, 0, '', 'test', 20.00, 1, 1, 1, 'ไม่มีภาวะแทรกซ้อน', '2026-01-16 09:36:15', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:36:15', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_patient`
--

CREATE TABLE `tbl_patient` (
  `hn` varchar(50) NOT NULL COMMENT 'คีย์หลัก (จาก API)',
  `id_card` varchar(13) DEFAULT NULL COMMENT 'เลขบัตรประชาชน',
  `flname` varchar(255) NOT NULL COMMENT 'ชื่อ-สกุล',
  `birthdate` date DEFAULT NULL COMMENT 'วันเกิด',
  `age` int(11) NOT NULL,
  `gender` enum('ชาย','หญิง','ไม่ระบุ') DEFAULT NULL COMMENT 'เพศ',
  `blood_type` varchar(20) DEFAULT NULL,
  `phone_number` int(11) DEFAULT NULL,
  `address_full` text DEFAULT NULL COMMENT 'ที่อยู่',
  `subdistrict` text DEFAULT NULL,
  `district` text DEFAULT NULL,
  `province` text DEFAULT NULL,
  `zipcode` text DEFAULT NULL,
  `other_id_type` enum('Alien','Passport') DEFAULT NULL COMMENT 'ประเภทบัตร',
  `other_id_number` varchar(50) DEFAULT NULL COMMENT 'เลขที่บัตร',
  `treatment_scheme` enum('health_insurance','social_security','affiliation','self_pay','t99') DEFAULT NULL COMMENT 'สิทธิการรักษา',
  `created_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางเก็บข้อมูลประชากรหลักของผู้ป่วย';

--
-- Dumping data for table `tbl_patient`
--

INSERT INTO `tbl_patient` (`hn`, `id_card`, `flname`, `birthdate`, `age`, `gender`, `blood_type`, `phone_number`, `address_full`, `subdistrict`, `district`, `province`, `zipcode`, `other_id_type`, `other_id_number`, `treatment_scheme`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
('1/49', '0000000000001', 'นายทดสอบ ระบบ1', '2017-11-18', 8, 'ชาย', 'O', 0, '80/21 ต.ควนลัง อ.หาดใหญ่ จ.สงขลา 90110', '0', 'หาดใหญ่', 'สงขลา', '90110', 'Passport', '', 'self_pay', '', '2025-11-19 07:34:10', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:34:48'),
('1/51', '5800700026057', 'นายโกศล ปาละกุล', NULL, 44, 'ชาย', '', NULL, '7/2 ม.6 ต.ฉลุง อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, 'Alien', '5050', 'self_pay', '', '2025-11-21 09:30:55', '', '2025-11-21 09:30:55'),
('1/52', '1909802916315', 'น.ส.พฤศจิอร ทองแกมแก้ว', '0000-00-00', 20, 'หญิง', '', NULL, '16/1 หมู่ 15 ต.ท่าช้าง อ.บางกล่ำ จ.สงขลา 90110', NULL, NULL, NULL, NULL, 'Alien', '1234', 'health_insurance', '', '2025-12-04 07:12:44', '', '2025-12-04 07:12:44'),
('1/53', '3909800396493', 'นายไสว อิสสระ', '1963-09-07', 58, '', 'ไม่ทราบ', NULL, '10 ถ.เพชรเกษม ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', '', '2025-12-09 02:31:15', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:10:54'),
('1/54', '1909800382917', 'นายวัชร์นล อเนกอัครวัฒน์', '0000-00-00', 36, 'ชาย', '', NULL, '69 ต.คอหงส์ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, 'Alien', '7777', 'self_pay', '', '2025-12-11 03:45:48', '', '2025-12-11 03:45:48'),
('1/55', '1909802333590', 'นายอำพล สว่างจันทร์', '0000-00-00', 23, 'ชาย', '', NULL, 'ไม่ทราบเลขที่ ต.ควนลัง อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, 'Alien', '7777', 't99', '', '2025-12-11 04:12:24', '', '2025-12-11 04:12:24'),
('1/56', '1909803860046', 'ด.ช.ธันวา อิเบ็ญหมาน', '0000-00-00', 12, 'ชาย', '', NULL, '32/2 ม.7 ต.ท่าช้าง อ.บางกล่ำ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 't99', '', '2025-12-11 04:17:02', '', '2025-12-11 04:17:44'),
('1/57', '1909803982931', 'ด.ญ.ณัชชา ย๊ะส๊ะ', '0000-00-00', 1, 'หญิง', '', NULL, '23/5 .. ต.สำนักแต้ว อ.สะเดา จ.สงขลา ', NULL, NULL, NULL, NULL, 'Passport', '8888', 'affiliation', '', '2025-12-11 04:23:46', '', '2025-12-11 04:23:46'),
('1/58', '3901100530190', 'นายอรุณ แก้วบุญแก้ว', NULL, 36, 'ชาย', 'A+', NULL, '60/1 ต.ทุ่งตำเสา อ.หาดใหญ่ จ.สงขลา -', NULL, NULL, NULL, NULL, 'Passport', '112', 't99', '', '2025-12-15 02:54:22', '', '2025-12-15 04:29:03'),
('1/59', '1570500106499', 'นายธวัชชัย เชื้อเมืองพาน', '1988-01-22', 30, 'ชาย', 'B+', NULL, '59 ต.ทานตะวัน อ.พาน จ.เชียงราย ', NULL, NULL, NULL, NULL, 'Passport', '4544', 't99', '', '2025-12-15 02:44:24', '', '2025-12-15 04:19:12'),
('1/60', '0000000000002', 'นายพิศาล ศรีระนำ', '1971-07-01', 47, 'ชาย', 'PP', NULL, '21 ม.3 ต.ไพรวัน อ.ตากใบ จ.นราธิวาส ', NULL, NULL, NULL, NULL, 'Passport', '123456', 'health_insurance', NULL, '2025-12-15 04:26:18', 'System', '2025-12-16 06:54:18'),
('1/61', '1949900282361', 'นายมานาฟ เซาะแม', '1996-10-04', 21, 'ชาย', 'B', NULL, '8/6 ต.จะบังติกอ อ.เมืองปัตตานี จ.ปัตตานี ', NULL, NULL, NULL, NULL, NULL, NULL, 'affiliation', 'System', '2025-12-16 06:56:13', 'System', '2025-12-16 06:59:03'),
('1/62', '1909800687611', 'นายเจริญทรัพย์ แข็งกำเหนิด', '1993-01-18', 30, 'ชาย', 'C', NULL, '161 ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'health_insurance', NULL, '2025-12-16 07:02:46', NULL, '2025-12-16 07:02:46'),
('1/63', '3901101349286', 'นายร่อโสน มากเชื้อ', '1976-12-25', 46, 'ชาย', 'F', NULL, '34/1  ม.9 ต.คลองแห อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 'health_insurance', NULL, '2025-12-16 07:30:56', NULL, '2025-12-16 07:30:56'),
('1/64', '1900300140932', 'น.ส.อารียา หมัดสา', '1991-06-26', 29, 'หญิง', 'c', NULL, '101/2 ต.นาทวี อ.นาทวี จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'affiliation', NULL, '2025-12-16 07:49:37', NULL, '2025-12-17 02:00:03'),
('1/65', '1920300009364', 'นายสุชน โออิน', '1984-11-06', 40, 'ชาย', 'B', NULL, '135 ต.นาชุมเห็ด อ.ย่านตาขาว จ.ตรัง ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'System', '2025-12-17 02:13:49', 'System', '2025-12-17 02:16:14'),
('1/66', '1909802749494', 'นายศัตญา แก้วชุม', '2003-05-09', 21, 'ชาย', 'O', NULL, '139/1 ต.รัตภูมิ อ.ควนเนียง จ.สงขลา ', NULL, NULL, NULL, NULL, 'Passport', '1234', 'self_pay', 'System', '2025-12-17 02:28:04', 'System', '2025-12-17 02:41:33'),
('1/67', '1909300210360', 'ด.ช.วิศรุต พูลศิริ', '2023-12-31', 0, 'ชาย', 'O', NULL, '3/3 ม.2 ต.นาทวี อ.นาทวี จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'System', '2025-12-17 07:57:21', NULL, '2025-12-17 07:57:21'),
('1/68', '6016700750163', 'MR.THET PAI MOE (เมียนมาร์)', '2002-02-16', 22, 'ชาย', 'B', NULL, '- ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, 'Passport', '8877', 'self_pay', 'System', '2025-12-17 08:22:10', 'System', '2025-12-19 03:59:48'),
('1/69', '', 'ด.ช.ม.นาดียา หมิเถาะ', '2025-12-31', 0, 'ชาย', 'B', NULL, '25/2 ม.8 ต.สะกอม อ.จะนะ จ.สงขลา 90150', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 04:40:01', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 04:57:50'),
('10/49', '2900601022104', 'นายอัสมาน หะยีดอเลาะ', '1987-11-30', 22, 'ชาย', 'O', NULL, '135/2 ม.1 ต.สะบ้าย้อย อ.สะบ้าย้อย จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'System', '2025-12-22 07:52:09', NULL, '2025-12-22 07:52:09'),
('10/50', '3909800911665', 'นางสาวจุไร ทองขาว', '1957-01-01', 68, 'หญิง', 'AB', NULL, '58/79 ซ.6 ถ.กาญจนาวนิช ม.2 ต.คอหงส์ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 't99', 'System', '2025-12-23 08:15:12', NULL, '2025-12-23 08:15:12'),
('10/69', '3820400306805', 'นายสมภร เดชพิชัย', '1968-06-07', 57, 'ชาย', 'AB', NULL, '144/2 ต.แหลมสอม อ.ปะเหลียน จ.ตรัง ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 06:19:06', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 06:19:06'),
('1001/45', '0000000000003', 'นายทดลอง คนที่ 2', '1967-10-10', 58, '', 'O', 27548741, 'xx/xx ต.*คันนายาว อ.เขตบางกะปิ จ.กรุงเทพมหานคร ', '0', 'เขตบางกะปิ', 'กรุงเทพมหานคร', '', NULL, NULL, 'health_insurance', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 02:17:54', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:44:56'),
('11/49', '2900601027971', 'นายซาการียา อาแว', '1991-04-01', 34, 'ชาย', 'O', NULL, '135/13 ม.1 ต.สะบ้าย้อย อ.สะบ้าย้อย จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'affiliation', 'System', '2025-12-23 08:12:51', NULL, '2025-12-23 08:12:51'),
('15/69', '1909805129837', 'ด.ช.พชรวัฒน์ เหล็มปาน', '2026-01-01', 0, 'ชาย', 'A', NULL, '81/1 ม.1 ต.ท่าชะมวง อ.รัตภูมิ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 07:23:47', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:51:53'),
('16/69', '8571576081550', 'นายยุฟู่ แซ่ลี', '1998-01-25', 27, 'ชาย', 'O', NULL, '110/0 ม.5 ต.ท่าช้าง อ.บางกล่ำ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:00:18', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:16:45'),
('17/69', '1579400045411', 'ด.ญ.กัญญาพัชร วงเวียน', '2020-10-15', 5, 'หญิง', 'O', NULL, '61 ม.3 ต.ฉลุง อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:19:35', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:40:22'),
('18/53', '1959800145095', 'ด.ญ.ณัฐธิดา แซ่กง', '2000-10-08', 9, 'หญิง', 'O', NULL, '159 ถ.นาคราชบำรุง ต.เบตง อ.เบตง จ.ยะลา 95110', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 03:49:53', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 03:49:53'),
('18/69', '1900201127890', 'น.ส.รัชดา มรรคโช', '2009-05-20', 16, 'หญิง', 'AB', NULL, '71 ม.7 ต.จะทิ้งพระ อ.สทิงพระ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:03:32', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:03:32'),
('2/49', '1940200078755', 'นายสูวาบรี โตะหัด', '1989-03-25', 29, 'ชาย', 'A', NULL, '9/4 ม.5 ต.สะบ้าย้อย อ.สะบ้าย้อย จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'System', '2025-12-19 04:06:08', NULL, '2025-12-19 04:06:08'),
('2/69', '1800400177511', 'นายอภินันท์ ธนากรรฐ์', '1991-08-18', 34, 'ชาย', 'B', NULL, '32 ถ.ถัดอุทิศ ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 06:11:46', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:18:42'),
('20/69', '3900900579575', 'นายบุญธรรม อุไรรัตน์', '1957-08-17', 68, 'ชาย', 'X', NULL, '114/1 ต.รัตภูมิ อ.ควนเนียง จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 08:37:38', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:13:20'),
('21/69', '1940101260733', 'น.ส.เจ๊ะกอลีเยาะ มูซอ', '2001-02-01', 24, 'หญิง', 'A', NULL, '245/2 ม.5 ต.เขื่อนบางลาง อ.บันนังสตา จ.ยะลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:37:04', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:37:04'),
('22/69', '1959900957857', 'นายสาลามิน วาโด', '2005-10-15', 20, 'หญิง', 'AB', NULL, 'รับมาจาก ถ.ทุ่งเสา 2 ต.ทุ่งตำเสา อ.หาดใหญ่ จ.สงขลา 90110', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 03:37:03', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 03:37:03'),
('3/49', '3100200135922', 'นายปานเพชร ใจทอง', '1972-01-02', 38, 'ชาย', 'A', NULL, '2/47 ถ.หน้าสถานี ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'System', '2025-12-19 04:16:44', NULL, '2025-12-19 04:16:44'),
('3/69', '2900800006958', 'น.ส.ศุภลักษณ์ เอียดแก้ว', '1996-10-10', 29, 'หญิง', 'B', NULL, '69 ม.3 ต.โรง อ.กระแสสิน จ.สงขลา ', 'โรง', 'กระแสสิน', 'สงขลา', '', NULL, NULL, 'affiliation', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 06:39:56', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-14 09:23:58'),
('4/49', '3900400043099', 'นางบุญ ทองบุญ', '1916-11-30', 103, 'หญิง', 'O', NULL, '7 ม.4 ต.ท่าประดู่ อ.นาทวี จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'System', '2025-12-19 04:23:50', 'System', '2025-12-19 04:27:36'),
('4/69', '1909804892898', 'ด.ญ.พลอยชมพู เอียดแก้ว', '2022-09-19', 3, 'หญิง', 'B', 0, '69 ม.3 ต.โรง อ.กระแสสิน จ.สงขลา ', '0', 'กระแสสิน', 'สงขลา', '', NULL, NULL, 'self_pay', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 02:16:50', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 04:15:16'),
('5/49', '1884445784512', 'นายวัชรพัฒน์ ดำน้อย', '1989-01-01', 23, 'ชาย', 'A', NULL, '524/13 ม.1 ต.ควนลัง อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'System', '2025-12-19 07:47:05', NULL, '2025-12-19 07:47:05'),
('6/49', '5570400016687', 'นายธวัชชัย แสงโพธิ์', '1983-10-30', 36, 'ชาย', 'O', NULL, '91 ถ.เทศาพัฒนา ซ.5/1 ต.หาดใหญ่ อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'System', '2025-12-22 02:38:10', NULL, '2025-12-22 02:38:10'),
('7/49', '1900400031418', 'นายสายชล นวนติ้ง', '1986-03-30', 34, 'หญิง', 'AB+', NULL, '42 ม.4 ต.ประกอบ อ.นาทวี จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 'affiliation', 'System', '2025-12-22 07:09:35', 'System', '2025-12-22 07:51:11'),
('8/49', '0000000000009', 'นายกลับ ยิ้มแก้ว', '1918-01-01', 94, 'ชาย', 'b', NULL, '51 ม.2 ต.ดอนทราย อ.ปากพะยูน จ.พัทลุง ', NULL, NULL, NULL, NULL, NULL, NULL, 'self_pay', 'System', '2025-12-22 07:29:41', NULL, '2025-12-22 07:29:41'),
('9/49', '3930200094498', 'นางบุญยวีย์ รุ่งนุ่นทองหอม', '1976-05-13', 48, 'หญิง', 'B', NULL, '30/7 ต.คลองแห อ.หาดใหญ่ จ.สงขลา ', NULL, NULL, NULL, NULL, NULL, NULL, 't99', 'System', '2025-12-22 07:33:23', NULL, '2025-12-22 07:33:23'),
('9/69', '1949900668260', 'นายอาลาวี เพชทะลุง', '2008-05-14', 17, 'ชาย', 'O', NULL, '76/4 ต.ดอน อ.ปะนาเระ จ.ปัตตานี ', NULL, NULL, NULL, NULL, NULL, NULL, 'social_security', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 08:29:48', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 08:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stroke_admission`
--

CREATE TABLE `tbl_stroke_admission` (
  `id` int(11) NOT NULL,
  `patient_hn` varchar(50) NOT NULL COMMENT 'คีย์นอก: ใช้เชื่อมโยงว่าการ Admission นี้เป็นของผู้ป่วยคนไหน',
  `patient_an` varchar(50) NOT NULL,
  `date_admit` datetime NOT NULL,
  `is_ht` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_dm` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_old_cva` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_mi` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_af` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_dlp` tinyint(1) DEFAULT 0 COMMENT 'โรคประจำตัว',
  `is_other_text` varchar(255) DEFAULT NULL COMMENT 'โรคประจำตัว',
  `addict_alcohol` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด ',
  `addict_smoking` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด ',
  `comorbid_kratom` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด(กระท่อม)',
  `comorbid_cannabis` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด(กัญชา)',
  `comorbid_crystal_meth` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด(ไอซ์)',
  `comorbid_yaba` tinyint(1) DEFAULT 0 COMMENT 'สารเสพติด(ยาบ้า)',
  `arrival_type` enum('refer','ems','walk_in','ipd') DEFAULT NULL COMMENT 'ประเภทการมาของคนไข้',
  `refer_from_hospital` varchar(255) DEFAULT NULL COMMENT 'Refer จาก (ระบุโรงพยาบาล):',
  `transfer_departure_datetime` datetime DEFAULT NULL,
  `refer_arrival_datetime` datetime DEFAULT NULL COMMENT 'วันที่ที่ผป.มาถึง รพ/รพท ต้นทาง',
  `ems_first_medical_contact` datetime DEFAULT NULL COMMENT 'First Medical contact (เวลาที่รับโทรศัพท์)',
  `walk_in_datetime` datetime DEFAULT NULL COMMENT 'เวลาที่มาถึงห้องฉุกเฉิน',
  `ipd_ward_name` varchar(255) DEFAULT NULL COMMENT 'ward ผู้ป่วย',
  `ipd_onset_datetime` datetime DEFAULT NULL COMMENT 'เวลาที่เริ่มมีอาการในหอผู้ป่วย',
  `med_anti_platelet` tinyint(1) DEFAULT 0 COMMENT 'Anti-platelet (ยาต้านเกล็ดเลือด)',
  `med_asa` tinyint(1) DEFAULT 0 COMMENT 'ASA (Aspirin)',
  `med_cilostazol` tinyint(1) NOT NULL DEFAULT 0,
  `med_ticaqrelor` tinyint(1) NOT NULL DEFAULT 0,
  `med_clopidogrel` tinyint(1) DEFAULT 0 COMMENT 'Clopidogrel (Copidogel / Plavix / Suluntra)',
  `med_anti_coagulant` tinyint(1) DEFAULT 0 COMMENT 'Anti-coagulant (ยาต้านการแข็งตัวของเลือด)',
  `med_warfarin` tinyint(1) DEFAULT 0 COMMENT 'ยา Warfarin',
  `med_noac` tinyint(1) DEFAULT 0 COMMENT 'ยา NOAC',
  `pre_morbid_mrs` tinyint(1) DEFAULT NULL COMMENT 'MRS Score (ก่อนเกิดอาการ)',
  `fast_track_status` enum('yes','no') DEFAULT NULL COMMENT 'ผู้ป่วยเข้าเกณฑ์ Stroke Fast Track หรือไม่?',
  `symp_face` tinyint(1) DEFAULT 0 COMMENT 'F(ใบหน้าเบี้ยว และปากเบี้ยว)',
  `symp_arm` tinyint(1) DEFAULT 0 COMMENT 'A(Arm) แขน-ขา อ่อนแรง ชาครึ่งซีก',
  `symp_speech` tinyint(1) DEFAULT 0 COMMENT 'S(Speech) พูดไม่ชัด พูดติดขัด',
  `symp_vision` tinyint(1) DEFAULT 0 COMMENT 'V(Vision)',
  `symp_aphasia` tinyint(1) DEFAULT 0 COMMENT 'A(Aphasia)',
  `symp_neglect` tinyint(1) DEFAULT 0 COMMENT 'N(Neglect)',
  `gcs_e` tinyint(1) DEFAULT NULL COMMENT 'การประเมินแรกรับ E',
  `gcs_v` tinyint(1) DEFAULT NULL COMMENT 'การประเมินแรกรับ V',
  `gcs_m` tinyint(1) DEFAULT NULL COMMENT 'การประเมินแรกรับ M',
  `nihss_score` tinyint(2) DEFAULT NULL COMMENT 'NISHH',
  `onset_datetime` datetime DEFAULT NULL COMMENT 'เวลาที่เริ่มมีอาการ',
  `hospital_arrival_datetime` datetime DEFAULT NULL COMMENT 'เวลาที่ถึงรพ.',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` varchar(255) NOT NULL,
  `time_onset_to_refer_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลา Onset ถึง รพ.ชุมชน (นาที)',
  `time_onset_to_hatyai_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลา Onset ถึง รพ.หาดใหญ่ (นาที)',
  `time_refer_hospital_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลาอยู่ รพ.ชุมชน (นาที)',
  `time_refer_travel_min` int(11) DEFAULT NULL COMMENT 'ระยะเวลาเดินทาง (นาที)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางเก็บข้อมูลการมารับบริการ Stroke แต่ละครั้ง';

--
-- Dumping data for table `tbl_stroke_admission`
--

INSERT INTO `tbl_stroke_admission` (`id`, `patient_hn`, `patient_an`, `date_admit`, `is_ht`, `is_dm`, `is_old_cva`, `is_mi`, `is_af`, `is_dlp`, `is_other_text`, `addict_alcohol`, `addict_smoking`, `comorbid_kratom`, `comorbid_cannabis`, `comorbid_crystal_meth`, `comorbid_yaba`, `arrival_type`, `refer_from_hospital`, `transfer_departure_datetime`, `refer_arrival_datetime`, `ems_first_medical_contact`, `walk_in_datetime`, `ipd_ward_name`, `ipd_onset_datetime`, `med_anti_platelet`, `med_asa`, `med_cilostazol`, `med_ticaqrelor`, `med_clopidogrel`, `med_anti_coagulant`, `med_warfarin`, `med_noac`, `pre_morbid_mrs`, `fast_track_status`, `symp_face`, `symp_arm`, `symp_speech`, `symp_vision`, `symp_aphasia`, `symp_neglect`, `gcs_e`, `gcs_v`, `gcs_m`, `nihss_score`, `onset_datetime`, `hospital_arrival_datetime`, `created_at`, `created_by`, `updated_at`, `updated_by`, `time_onset_to_refer_min`, `time_onset_to_hatyai_min`, `time_refer_hospital_min`, `time_refer_travel_min`) VALUES
(2, '1/49', '', '0000-00-00 00:00:00', 1, 0, 1, 0, 1, 1, '', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'หอผู้ป่วยจักษุ', '2025-11-21 10:19:00', 1, 0, 0, 0, 1, 0, 0, 0, 2, 'yes', 0, 0, 0, 0, 0, 0, 3, 3, 3, NULL, '2025-11-11 09:18:00', '2025-11-12 10:23:00', '2025-11-21 02:22:56', 'System', '2025-11-21 02:22:56', '', NULL, NULL, NULL, NULL),
(3, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test1', 1, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-11-21 09:33:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 4, 'yes', 0, 0, 0, 1, 1, 1, 4, 4, 5, 29, '2025-11-11 09:33:00', '2025-11-12 09:45:00', '2025-11-21 02:33:45', 'System', '2025-11-21 02:33:45', '', NULL, NULL, NULL, NULL),
(4, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test2', 1, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-11-21 09:42:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 6, 'no', 1, 1, 1, 1, 1, 1, 4, 5, 6, 42, '2025-11-21 09:43:00', '2025-11-21 09:49:00', '2025-11-21 02:43:14', 'System', '2025-11-21 02:43:14', '', NULL, NULL, NULL, NULL),
(5, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test3', 1, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-11-21 09:04:00', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 4, 40, '2025-11-21 09:01:00', '2025-11-21 09:05:00', '2025-11-21 02:59:07', 'System', '2025-11-21 02:59:07', '', NULL, NULL, NULL, NULL),
(6, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test3', 1, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-11-21 09:04:00', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 4, 40, '2025-11-21 09:01:00', '2025-11-21 09:05:00', '2025-11-21 03:01:10', 'System', '2025-11-21 03:01:10', '', NULL, NULL, NULL, NULL),
(7, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test3', 1, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-11-21 09:04:00', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 4, 40, '2025-11-21 09:01:00', '2025-11-21 09:05:00', '2025-11-21 03:05:53', 'System', '2025-11-21 03:05:53', '', NULL, NULL, NULL, NULL),
(8, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test3', 1, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-11-21 09:04:00', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'no', 1, 1, 1, 1, 1, 1, 1, 5, 1, 38, '2025-11-21 09:01:00', '2025-11-21 09:05:00', '2025-11-21 03:06:30', 'System', '2025-11-21 03:06:30', '', NULL, NULL, NULL, NULL),
(9, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test4', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมชาย 2', '2025-11-21 10:22:00', 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 1, 2, 1, 32, '2025-11-21 10:12:00', '2025-11-21 10:17:00', '2025-11-21 03:12:57', 'System', '2025-11-21 03:12:57', '', NULL, NULL, NULL, NULL),
(10, '1/51', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'แแแแ', 0, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'พิเศษประกันสังคม ชั้น 5', '2025-11-21 16:31:00', 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 3, 3, 3, 2, '2025-11-21 16:30:00', '2025-11-21 16:30:00', '2025-11-21 09:30:55', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-11-21 09:30:55', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(11, '1/52', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test4', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 5', '2025-12-04 14:13:00', 1, 1, 0, 0, 1, 1, 1, 1, 4, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 6, 42, '2025-12-04 14:13:00', '2025-12-04 14:20:00', '2025-12-04 07:12:44', 'System', '2025-12-04 07:12:44', '', NULL, NULL, NULL, NULL),
(12, '1/53', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'xxx', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'หอผู้ป่วยจักษุ', '2025-12-09 09:30:00', 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 6, 42, '2025-12-09 09:31:00', '2025-12-09 09:50:00', '2025-12-09 02:31:15', 'System', '2025-12-09 02:31:15', '', NULL, NULL, NULL, NULL),
(13, '1/54', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-12-11 10:45:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 4, 4, 25, '2025-12-11 10:50:00', '2025-12-11 11:50:00', '2025-12-11 03:45:48', 'System', '2025-12-11 03:45:48', '', NULL, NULL, NULL, NULL),
(14, '1/55', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'ศัลยโรคหลอดเลือดสมอง', '2025-12-11 11:11:00', 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 4, 25, '2025-12-11 11:11:00', '2025-12-11 11:50:00', '2025-12-11 04:12:24', 'System', '2025-12-11 04:12:24', '', NULL, NULL, NULL, NULL),
(15, '1/56', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 1, 0, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-11 11:17:00', NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, 2, 25, '2025-12-11 11:17:00', '2025-12-11 11:28:00', '2025-12-11 04:17:44', 'System', '2025-12-11 04:17:44', '', NULL, NULL, NULL, NULL),
(16, '1/57', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-12-11 11:23:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 5, 'no', 0, 0, 0, 0, 0, 0, 3, 2, 2, 0, '2025-12-11 11:30:00', '2025-12-11 11:50:00', '2025-12-11 04:23:46', 'System', '2025-12-11 04:23:46', '', NULL, NULL, NULL, NULL),
(17, '1/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-12 15:31:00', NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0, 3, 'no', 0, 0, 0, 0, 0, 0, 1, 1, 1, 25, '2025-12-11 15:32:00', '2025-12-11 15:41:00', '2025-12-12 08:30:58', 'System', '2025-12-12 08:30:58', '', NULL, NULL, NULL, NULL),
(18, '1/49', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 5', '2025-12-12 15:35:00', 1, 0, 0, 0, 1, 0, 0, 0, 1, 'yes', 1, 0, 0, 0, 0, 0, 2, 3, 2, 25, '2025-12-11 15:36:00', '2025-12-11 20:35:00', '2025-12-12 08:36:00', 'System', '2025-12-12 08:36:00', '', NULL, NULL, NULL, NULL),
(19, '1/59', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-15 09:45:00', NULL, NULL, 0, 0, 0, 0, 0, 1, 0, 1, 3, 'yes', 0, 1, 0, 0, 0, 0, 2, 2, 3, 25, '2025-12-11 09:48:00', '2025-12-11 09:50:00', '2025-12-15 02:44:24', 'System', '2025-12-15 02:44:24', '', NULL, NULL, NULL, NULL),
(20, '1/58', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-12-11 09:55:00', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0, 3, 'no', 0, 0, 0, 0, 0, 0, 2, 3, 3, 25, '2025-12-11 09:57:00', '2025-12-11 11:54:00', '2025-12-15 02:54:22', 'System', '2025-12-15 02:54:22', '', NULL, NULL, NULL, NULL),
(21, '1/58', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 1', '2025-12-15 10:02:00', 0, 0, 0, 0, 0, 1, 0, 1, 5, 'no', 0, 0, 0, 0, 0, 0, NULL, NULL, 6, 25, '2025-12-11 10:05:00', '2025-12-11 10:07:00', '2025-12-15 03:02:52', 'System', '2025-12-15 03:02:52', '', NULL, NULL, NULL, NULL),
(22, '1/59', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 3', '2025-12-15 10:07:00', 1, 0, 0, 0, 1, 1, 1, 0, 2, 'yes', 0, 1, 1, 0, 0, 0, 4, 5, 5, 25, '2025-12-11 10:09:00', '2025-12-11 10:15:00', '2025-12-15 03:08:02', 'System', '2025-12-15 03:08:02', '', NULL, NULL, NULL, NULL),
(23, '1/59', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 3', '2025-12-15 10:07:00', 1, 0, 0, 0, 1, 1, 1, 0, 2, 'yes', 0, 1, 1, 0, 0, 0, 4, 5, 5, 25, '2025-12-11 10:09:00', '2025-12-11 10:15:00', '2025-12-15 04:19:12', 'System', '2025-12-15 04:19:12', '', NULL, NULL, NULL, NULL),
(24, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 04:26:18', 'System', '2025-12-15 04:26:18', '', NULL, NULL, NULL, NULL),
(25, '1/58', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 1', '2025-12-15 10:02:00', 0, 0, 0, 0, 0, 1, 0, 1, 5, 'no', 0, 0, 0, 0, 0, 0, NULL, NULL, 6, 25, '2025-12-11 10:05:00', '2025-12-11 10:07:00', '2025-12-15 04:29:03', 'System', '2025-12-15 04:29:03', '', NULL, NULL, NULL, NULL),
(26, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 04:31:43', 'System', '2025-12-15 04:31:43', '', NULL, NULL, NULL, NULL),
(27, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 04:39:47', 'System', '2025-12-15 04:39:47', '', NULL, NULL, NULL, NULL),
(28, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 07:57:19', 'System', '2025-12-15 07:57:19', '', NULL, NULL, NULL, NULL),
(29, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 08:50:23', 'System', '2025-12-15 08:50:23', '', NULL, NULL, NULL, NULL),
(30, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 08:50:51', 'System', '2025-12-15 08:50:51', '', NULL, NULL, NULL, NULL),
(31, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 08:50:56', 'System', '2025-12-15 08:50:56', '', NULL, NULL, NULL, NULL),
(32, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 08:51:08', 'System', '2025-12-15 08:51:08', '', NULL, NULL, NULL, NULL),
(33, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-15 08:51:22', 'System', '2025-12-15 08:51:22', '', NULL, NULL, NULL, NULL),
(34, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-16 06:54:18', 'System', '2025-12-16 06:54:18', 'System', NULL, NULL, NULL, NULL),
(35, '1/61', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'จักษุโสตศอนาสิก (รวมสระอาพาธ)', '2025-12-16 13:58:00', 1, 1, 0, 0, 1, 1, 1, 0, 3, 'yes', 0, 1, 0, 0, 0, 0, 2, 3, 6, 25, '2025-12-11 13:00:00', '2025-12-11 18:56:00', '2025-12-16 06:56:13', 'System', '2025-12-16 06:56:13', 'System', NULL, NULL, NULL, NULL),
(36, '1/61', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, '', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'จักษุโสตศอนาสิก (รวมสระอาพาธ)', '2025-12-16 13:58:00', 1, 1, 0, 0, 1, 1, 1, 0, 3, 'yes', 0, 1, 1, 1, 1, 0, 2, 3, 6, 40, '2025-12-11 13:00:00', '2025-12-11 18:56:00', '2025-12-16 06:59:03', 'System', '2025-12-16 06:59:03', '', NULL, NULL, NULL, NULL),
(37, '1/62', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 1, 0, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-16 14:03:00', NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 2, 'no', 0, 0, 0, 0, 0, 0, 1, 3, 2, 25, '2025-12-11 14:04:00', '2025-12-11 15:02:00', '2025-12-16 07:02:46', 'System', '2025-12-16 07:02:46', '', NULL, NULL, NULL, NULL),
(38, '1/63', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'พิเศษประกันสังคม ชั้น 5', '2025-12-16 14:30:00', 1, 1, 0, 0, 0, 0, 0, 0, 3, 'no', 0, 0, 0, 0, 0, 0, 1, 4, 1, 39, '2025-12-11 14:33:00', '2025-12-11 14:36:00', '2025-12-16 07:30:56', 'System', '2025-12-16 07:30:56', '', NULL, NULL, NULL, NULL),
(39, '1/64', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-16 14:50:00', NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 3, 'no', 0, 0, 0, 0, 0, 0, 4, 5, 4, 29, '2025-12-11 14:50:00', '2025-12-11 14:54:00', '2025-12-16 07:50:27', 'System', '2025-12-16 07:50:27', '', NULL, NULL, NULL, NULL),
(40, '1/60', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-15 11:27:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, 'yes', 1, 1, 1, 1, 1, 1, 2, 2, NULL, 25, '2025-12-11 11:31:00', '2025-12-11 01:26:00', '2025-12-16 07:50:55', 'System', '2025-12-16 07:50:55', '', NULL, NULL, NULL, NULL),
(41, '1/64', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'โรคติดเชื้อในเด็ก', '2025-12-17 08:01:00', 1, 1, 0, 0, 0, 0, 0, 0, 6, 'no', 0, 0, 0, 0, 0, 0, 3, 3, 3, 29, '2025-12-11 09:59:00', '2025-12-11 00:59:00', '2025-12-17 02:00:03', 'System', '2025-12-17 02:00:03', '', NULL, NULL, NULL, NULL),
(42, '1/65', '', '0000-00-00 00:00:00', 0, 0, 0, 1, 0, 0, '', 0, 1, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-17 09:13:00', NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 1, 1, 1, 21, '2025-12-17 09:14:00', '2025-12-17 09:18:00', '2025-12-17 02:13:49', 'System', '2025-12-17 02:13:49', '', NULL, NULL, NULL, NULL),
(43, '1/65', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'โรคติดเชื้อในเด็ก', '2025-12-17 09:15:00', 1, 1, 0, 0, 0, 0, 0, 0, 1, 'no', 0, 0, 0, 0, 0, 0, 1, 1, 1, 21, '2025-12-17 09:17:00', '2025-12-17 09:20:00', '2025-12-17 02:16:14', 'System', '2025-12-17 02:16:14', '', NULL, NULL, NULL, NULL),
(44, '1/66', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'หอผู้ป่วยจักษุ', '2025-12-17 09:41:00', 1, 1, 0, 0, 1, 1, 1, 1, 4, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 6, 21, '2025-12-17 09:41:00', '2025-12-17 09:46:00', '2025-12-17 02:41:33', 'System', '2025-12-17 02:41:33', 'System', NULL, NULL, NULL, NULL),
(45, '1/67', '', '0000-00-00 00:00:00', 1, 0, 0, 0, 0, 0, '', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'กุมารเวชกรรม 1', '2025-12-17 14:56:00', 1, 0, 0, 0, 1, 1, 0, 1, 2, 'yes', 1, 1, 1, 1, 1, 1, 4, 3, 3, 21, '2025-12-17 14:00:00', '2025-12-17 15:57:00', '2025-12-17 07:57:21', 'System', '2025-12-17 07:57:21', 'System', NULL, NULL, NULL, NULL),
(46, '1/68', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, '', 1, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-17 15:23:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 2, 'yes', 1, 1, 1, 1, 1, 1, 4, 5, 6, 21, '2025-12-17 15:25:00', '2025-12-17 15:35:00', '2025-12-17 08:22:10', 'System', '2025-12-17 08:22:10', 'System', NULL, NULL, NULL, NULL),
(47, '1/68', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'refer', 'รพ.ยะลา', NULL, '2025-12-19 10:58:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 1, 3, 1, 21, '2025-12-19 10:01:00', '2025-12-19 10:05:00', '2025-12-19 03:59:48', 'System', '2025-12-19 03:59:48', 'System', NULL, NULL, NULL, NULL),
(48, '2/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'refer', 'รพ.ยะลา', NULL, '2025-12-19 11:05:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, 'yes', 1, 1, 1, 1, 1, 1, 4, 3, 3, 21, '2025-12-18 11:05:00', '2025-12-11 11:12:00', '2025-12-19 04:06:08', 'System', '2025-12-19 04:06:08', 'System', NULL, NULL, NULL, NULL),
(49, '3/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 1', '2025-12-19 11:16:00', 1, 1, 0, 0, 1, 1, 1, 1, 1, 'yes', 1, 1, 1, 1, 1, 1, 1, 1, 1, 4, '2025-12-11 11:20:00', '2025-12-11 11:29:00', '2025-12-19 04:16:44', 'System', '2025-12-19 04:16:44', 'System', NULL, NULL, NULL, NULL),
(50, '4/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมชาย 3', '2025-12-19 11:25:00', 1, 1, 0, 0, 1, 1, 1, 1, 1, 'no', 0, 0, 0, 0, 0, 0, 1, 1, 1, 20, '2025-12-11 11:50:00', '2025-12-11 00:23:00', '2025-12-19 04:27:36', 'System', '2025-12-19 04:27:36', 'System', NULL, NULL, NULL, NULL),
(51, '5/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 3', '2025-12-19 14:47:00', 1, 1, 0, 0, 1, 1, 1, 1, 4, 'yes', 1, 1, 1, 1, 1, 1, 4, 4, 4, 20, '2025-12-11 14:49:00', '2025-12-11 16:47:00', '2025-12-19 07:47:05', 'System', '2025-12-19 07:47:05', 'System', NULL, NULL, NULL, NULL),
(52, '6/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test6', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 5', '2025-12-22 09:37:00', 1, 1, 0, 0, 1, 1, 1, 1, 4, 'yes', 1, 1, 1, 1, 1, 1, 4, 4, 3, 20, '2025-12-11 09:41:00', '2025-12-11 09:52:00', '2025-12-22 02:38:10', 'System', '2025-12-22 02:38:10', 'System', NULL, NULL, NULL, NULL),
(53, '7/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-22 14:10:00', NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 3, 'no', 1, 1, 0, 0, 0, 0, 2, 3, 2, 20, '2025-12-11 14:10:00', '2025-12-11 14:15:00', '2025-12-22 07:09:35', 'System', '2025-12-22 07:09:35', 'System', NULL, NULL, NULL, NULL),
(54, '8/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 1, 0, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-22 14:28:00', NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 3, 'no', 0, 0, 0, 0, 0, 0, 4, 3, 5, 40, '2025-12-22 14:29:00', '2025-12-22 14:32:00', '2025-12-22 07:29:41', 'System', '2025-12-22 07:29:41', 'System', NULL, NULL, NULL, NULL),
(55, '9/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 'test222', 1, 0, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมชาย 2', '2025-12-22 14:32:00', 1, 1, 0, 0, 0, 0, 0, 0, 1, 'yes', 1, 0, 0, 0, 0, 1, 3, 2, 3, 21, '2025-12-22 13:33:00', NULL, '2025-12-22 07:33:23', 'System', '2025-12-22 07:33:23', 'System', NULL, NULL, NULL, NULL),
(56, '10/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 0, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-12-22 14:52:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 0, 2, 'no', 0, 0, 0, 0, 0, 0, 3, 3, 3, 0, '2025-12-22 14:53:00', '2025-12-22 15:52:00', '2025-12-22 07:52:09', 'System', '2025-12-22 07:52:09', 'System', NULL, NULL, NULL, NULL),
(57, '11/49', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 0, 'test222', 0, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 1', '2025-12-23 15:12:00', 1, 1, 0, 0, 0, 0, 0, 0, 2, 'no', 0, 0, 0, 0, 0, 0, 3, 4, 3, 21, '2025-12-23 15:14:00', '2025-12-23 16:12:00', '2025-12-23 08:12:51', 'System', '2025-12-23 08:12:51', 'System', NULL, NULL, NULL, NULL),
(58, '10/50', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 1, '', 0, 1, 0, 0, 0, 0, 'refer', 'รพ.สะเดา', NULL, '2025-12-22 15:14:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 2, 'no', 0, 0, 0, 0, 0, 0, 4, 3, 2, 21, '2025-12-23 15:17:00', '2025-12-23 16:15:00', '2025-12-23 08:15:12', 'System', '2025-12-23 08:15:12', 'System', NULL, NULL, NULL, NULL),
(59, '1/53', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test1', 1, 1, 0, 0, 0, 0, 'ems', '', NULL, NULL, '2025-12-23 16:02:00', NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 2, '', 1, 1, 1, 1, 1, 1, 1, 2, 3, 42, '2025-12-23 16:03:00', '2025-12-23 16:07:00', '2025-12-23 09:03:04', 'System', '2025-12-23 09:03:04', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, -1, NULL, NULL),
(60, '18/53', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 1, '', 0, 0, 0, 0, 0, 0, 'walk_in', '', NULL, NULL, NULL, '2025-12-29 10:51:00', NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 2, 'no', 0, 0, 0, 0, 0, 0, 4, 3, 4, 25, '2025-12-29 10:50:00', '2025-12-29 10:03:00', '2025-12-29 03:49:53', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 03:49:53', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(61, '20/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 0, 0, 1, 1, 1, 1, 'refer', 'รพช.ควนขนุน', NULL, '2026-01-08 16:46:00', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 3, 'no', 1, 0, 0, 0, 0, 0, 1, 2, 3, 12, '2026-01-08 15:46:00', '2026-02-06 15:52:00', '2026-01-08 08:46:59', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-08 08:46:59', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(62, '15/69', '', '0000-00-00 00:00:00', 0, 0, 1, 0, 0, 0, '', 0, 0, 0, 0, 0, 0, 'refer', 'รพช.สมเด็จพระบรมราชินีนาถ ณ อ.นาทวี', '2026-01-09 14:36:00', '2026-01-09 15:32:00', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 1, '', 0, 1, 0, 0, 0, 0, 1, 2, 1, 12, '2026-01-09 14:32:00', '2026-01-09 14:36:00', '2026-01-09 07:32:30', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 07:32:30', 'สุขใจ (ทดสอบ) ซ่อมไว', 60, 4, NULL, NULL),
(63, '16/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 0, 0, 1, 0, 0, 0, 'refer', 'รพช.นาโยง', '2026-01-09 15:00:00', '2026-01-09 14:30:00', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 3, 'no', 1, 0, 0, 0, 0, 0, 1, 2, 3, 16, '2026-01-09 14:49:00', '2026-01-09 15:00:00', '2026-01-09 08:00:18', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 08:00:18', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(64, '18/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 0, 0, 0, 0, 0, 1, 'walk_in', '', NULL, NULL, NULL, '2026-01-09 16:04:00', NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 2, '', 0, 1, 0, 0, 0, 0, 1, 2, 1, NULL, '2026-01-09 16:05:00', '2026-01-09 16:04:00', '2026-01-09 09:03:32', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:03:32', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, -1, NULL, NULL),
(65, '20/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 1, '', 0, 0, 0, 1, 1, 0, 'ems', '', NULL, NULL, '2026-01-09 16:12:00', NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 3, '', 1, 0, 1, 0, 0, 0, 3, 1, 5, 41, '2026-01-09 16:02:00', '2026-01-09 16:13:00', '2026-01-09 09:13:20', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:13:20', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, 10, NULL, NULL),
(66, '21/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 0, 1, '', 0, 0, 0, 1, 0, 0, 'refer', 'รพช.สะเดา', NULL, '2026-01-09 08:30:00', NULL, NULL, NULL, NULL, 1, 0, 1, 1, 0, 0, 0, 0, 1, '', 0, 0, 1, 0, 0, 0, 2, 3, NULL, 25, '2026-01-09 16:36:00', '2026-01-09 10:00:00', '2026-01-09 09:37:04', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-09 09:37:04', 'สุขใจ (ทดสอบ) ซ่อมไว', -486, -516, NULL, NULL),
(67, '22/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, '', 0, 0, 1, 1, 1, 1, 'refer', 'รพศ.ตรัง', '2026-01-12 10:35:00', '2026-01-12 11:40:00', NULL, NULL, NULL, NULL, 1, 0, 0, 0, 1, 0, 0, 0, 2, '', 1, 0, 0, 0, 0, 0, 1, 2, 3, 12, '2026-01-12 10:30:00', '2026-01-12 11:40:00', '2026-01-12 03:37:03', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 03:37:03', 'สุขใจ (ทดสอบ) ซ่อมไว', 70, 70, -65, 65),
(68, '2/69', '', '0000-00-00 00:00:00', 0, 0, 0, 1, 0, 0, '', 1, 1, 0, 0, 1, 1, 'refer', 'รพช.ละงู', '2026-01-13 14:10:00', '2026-01-13 15:10:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 1, 1, 1, 3, '', 1, 1, 1, 1, 1, 1, 1, 2, 3, 42, '2026-01-12 12:35:00', '2026-01-13 15:10:00', '2026-01-13 07:18:42', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:18:42', 'สุขใจ (ทดสอบ) ซ่อมไว', 1595, 1595, -60, 60),
(69, '3/69', '', '0000-00-00 00:00:00', 0, 0, 0, 0, 1, 0, 'test888', 1, 0, 0, 0, 0, 1, 'ipd', '', NULL, NULL, NULL, NULL, 'อายุรกรรมหญิง 3', '0000-00-00 00:00:00', 1, 1, 0, 0, 1, 0, 0, 0, 1, '', 0, 0, 0, 0, 0, 0, 1, 1, NULL, 2, '2026-01-14 12:45:00', '2026-01-14 12:56:00', '2026-01-14 09:23:58', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-14 09:23:58', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, 11, NULL, NULL),
(70, '4/69', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test999', 1, 1, 1, 1, 1, 1, 'refer', 'รพศ.ยะลา', '2026-01-15 08:55:00', '2026-01-16 00:00:00', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 1, '', 1, 1, 1, 1, 1, 1, 1, 2, 1, 15, '2026-01-15 07:50:00', '2026-01-15 10:00:00', '2026-01-15 02:16:50', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 02:16:50', 'สุขใจ (ทดสอบ) ซ่อมไว', 970, 130, -905, 65),
(71, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 0, 0, 0, '', 1, 1, 0, 0, 0, 0, 'ipd', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, 1, 0, 1, 1, 0, '', 1, 0, 1, 1, 0, 0, 4, 3, 4, 42, '2026-01-15 12:00:00', NULL, '2026-01-15 06:31:40', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 06:31:40', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(72, '1/49', '', '0000-00-00 00:00:00', 0, 0, 1, 1, 0, 0, '', 1, 0, 0, 0, 1, 1, 'ipd', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 1, 0, 1, 0, 1, 0, 2, '', 1, 1, 1, 0, 0, 0, 3, 2, 4, 8, NULL, '2026-01-15 12:00:00', '2026-01-15 07:01:43', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 07:01:43', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, NULL, NULL, NULL),
(73, '1001/45', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test0099', 1, 1, 1, 1, 1, 1, 'ipd', '', NULL, NULL, NULL, NULL, 'ศัลยโรคหลอดเลือดสมอง', NULL, 1, 1, 1, 1, 1, 1, 1, 1, 2, '', 1, 1, 1, 1, 1, 1, 1, 2, 3, 24, '2026-01-16 23:55:00', '0000-00-00 00:00:00', '2026-01-16 02:17:54', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 02:17:54', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL, 15, NULL, NULL),
(74, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'ewrt', 1, 1, 1, 1, 1, 1, 'refer', 'รพช.สะเดา', '2026-01-16 12:43:00', '2026-01-16 12:00:00', NULL, NULL, NULL, NULL, 1, 1, 0, 0, 1, 0, 0, 0, 1, '', 1, 1, 1, 1, 0, 0, 3, 2, 2, 3, '2026-01-16 13:22:00', '2026-01-16 13:22:00', '2026-01-16 09:29:42', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:29:42', 'สุขใจ (ทดสอบ) ซ่อมไว', -82, NULL, 43, 39),
(75, '1/49', '', '0000-00-00 00:00:00', 1, 1, 1, 1, 1, 1, 'test4', 1, 1, 1, 1, 1, 1, 'refer', 'รพช.ละงู', '2026-01-16 12:30:00', '2026-01-16 14:00:00', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 2, '', 1, 1, 1, 1, 1, 1, 4, 2, 2, 3, '2026-01-16 13:22:00', '2026-01-16 14:00:00', '2026-01-16 09:34:48', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:34:48', 'สุขใจ (ทดสอบ) ซ่อมไว', 38, 38, -90, 90),
(76, '1001/45', '1/45', '2010-09-10 00:00:00', 1, 1, 1, 1, 1, 1, 'test1', 1, 1, 1, 1, 1, 1, 'refer', 'รพช.ละงู', '2026-01-16 13:20:00', '2026-01-16 14:30:00', NULL, NULL, NULL, NULL, 1, 1, 1, 1, 1, 1, 1, 1, 4, '', 1, 1, 1, 1, 1, 1, 1, 2, 3, 20, '2026-01-16 14:00:00', '2026-01-16 15:15:00', '2026-01-16 09:44:56', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:44:56', 'สุขใจ (ทดสอบ) ซ่อมไว', 30, 75, -70, 115);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` int(11) NOT NULL,
  `hr_fname` varchar(255) NOT NULL COMMENT 'ชื่อ-นามสกุล ของบุคลากร',
  `hr_cid` varchar(13) NOT NULL COMMENT 'เลขประจำตัวประชาชน',
  `password` varchar(255) DEFAULT NULL,
  `position_in_work` varchar(255) NOT NULL COMMENT 'ตำแหน่งงาน',
  `department_name` varchar(255) NOT NULL COMMENT 'ชื่อหน่วยงานหลัก (ระดับภารกิจ/ฝ่าย)',
  `hr_department_sub_name` varchar(255) NOT NULL COMMENT 'ชื่อหน่วยงานย่อย (ระดับกลุ่มงาน)',
  `hr_department_sub_sub_name` varchar(255) NOT NULL COMMENT 'ชื่อหน่วยงานย่อยที่สุด (ระดับงาน)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'เวลาที่สร้าง',
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp() COMMENT 'เวลาอัพเดท',
  `updated_by` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL COMMENT 'เวลาที่ใช้งานล่าสุด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `hr_fname`, `hr_cid`, `password`, `position_in_work`, `department_name`, `hr_department_sub_name`, `hr_department_sub_sub_name`, `created_at`, `created_by`, `updated_at`, `updated_by`, `last_login`) VALUES
(1, 'พิชัย จินดาประเสริฐ', '3333333333332', NULL, 'นักศึกษาฝึกงาน', 'ภารกิจสุขภาพดิจิทัล', 'กลุ่มงานเทคโนโลยีสารสนเทศ', 'งานเทคโนโลยีสารสนเทศ', '2025-10-28 04:49:08', 'admin', '2025-10-28 04:49:08', '[value-11]', NULL),
(2, 'สุขใจ (ทดสอบ) ซ่อมไว', '2222222222223', NULL, 'นักวิชาการคอมพิวเตอร์', 'ภารกิจสุขภาพดิจิทัล', 'กลุ่มงานเทคโนโลยีสารสนเทศ', 'งานเทคโนโลยีสารสนเทศ', '2025-10-28 06:59:18', '2222222222223', '2026-01-16 09:33:05', '2222222222223', '2026-01-16 09:33:05'),
(36, 'ฟาดีฟ สาและ', '1959900398475', NULL, 'นักวิชาการคอมพิวเตอร์', 'ภารกิจสุขภาพดิจิทัล', 'กลุ่มงานสุขภาพดิจิทัล', 'กลุ่มงานสุขภาพดิจิทัล', '2025-11-05 02:35:21', '1959900398475', NULL, NULL, '2025-11-05 02:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ward`
--

CREATE TABLE `tbl_ward` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `followup_ct_datetime` datetime DEFAULT NULL COMMENT 'ส่งตรวจ CT brain',
  `followup_ct_result` varchar(255) DEFAULT NULL COMMENT 'ผล:',
  `discharge_assess_datetime` datetime DEFAULT NULL COMMENT 'อาการก่อนจำหน่ายประจำวันที่',
  `discharge_mrs` tinyint(4) DEFAULT NULL COMMENT 'mRS (ณ วันจำหน่าย)',
  `discharge_barthel` tinyint(4) DEFAULT NULL COMMENT 'Barthel Index',
  `discharge_plan_status` enum('came','not_came') DEFAULT NULL COMMENT 'การวางแผนจำหน่าย มา/ไม่มา',
  `discharge_date` date DEFAULT NULL COMMENT 'มา วันที่:',
  `discharge_status` enum('recovery','improve','disability','refer','against','death') DEFAULT NULL COMMENT 'สถานะจำหน่าย',
  `first_followup_date` date DEFAULT NULL COMMENT 'วันที่นัดครั้งแรก',
  `discharge_destination` enum('home','refer') DEFAULT NULL COMMENT 'แผนการจำหน่าย (กลับบ้าน or refer)',
  `refer_name_hospital` varchar(255) DEFAULT NULL COMMENT 'ระบุชื่อ รพ. กรณีส่งต่อ',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL,
  `discharge_type` varchar(50) DEFAULT NULL COMMENT 'ประเภทการจำหน่าย (Approval, Refer, Against, Death)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางลูก 1:1 เก็บข้อมูลสรุป Ward และการจำหน่าย';

--
-- Dumping data for table `tbl_ward`
--

INSERT INTO `tbl_ward` (`id`, `admission_id`, `followup_ct_datetime`, `followup_ct_result`, `discharge_assess_datetime`, `discharge_mrs`, `discharge_barthel`, `discharge_plan_status`, `discharge_date`, `discharge_status`, `first_followup_date`, `discharge_destination`, `refer_name_hospital`, `created_at`, `created_by`, `updated_at`, `updated_by`, `discharge_type`) VALUES
(1, 9, '2025-11-21 11:43:00', 'ืnxxxn', '2025-11-21 11:59:00', 3, 20, 'came', '2025-11-21', 'recovery', '2025-11-20', 'home', NULL, '2025-11-21 04:42:46', 'System', '2025-11-21 06:43:42', '0', NULL),
(3, 8, '2025-11-21 13:55:00', '2223', '2025-11-21 13:57:00', 3, 127, 'not_came', '0000-00-00', 'disability', '2025-11-23', 'refer', NULL, '2025-11-21 06:53:40', 'System', '2025-11-21 06:54:26', '0', NULL),
(4, 56, '2025-12-23 14:15:00', 'ปปปป', '2025-12-23 14:14:00', 4, 12, 'came', '2025-12-24', 'improve', '2025-12-23', 'home', NULL, '2025-12-23 07:12:21', 'System', '2025-12-23 07:12:40', 'System', NULL),
(5, 57, '2025-12-25 15:03:00', 'xxx', '2025-12-25 15:03:00', 2, 127, 'not_came', '0000-00-00', 'disability', '2025-12-25', 'home', NULL, '2025-12-25 08:02:41', 'System', '2025-12-25 08:02:51', 'System', NULL),
(6, 11, '2025-12-25 15:10:00', 'we33', '2025-12-25 15:11:00', 2, 127, 'not_came', '0000-00-00', 'recovery', '2025-12-25', 'home', NULL, '2025-12-25 08:09:58', 'System', '2025-12-25 08:10:05', 'System', NULL),
(7, 10, '2025-12-25 15:23:00', 'ree', '2025-12-25 15:22:00', 4, 127, 'not_came', '0000-00-00', 'recovery', '2025-12-25', 'home', NULL, '2025-12-25 08:21:58', 'System', '2025-12-25 08:22:11', 'System', NULL),
(8, 60, '2025-12-29 11:30:00', '22', '2025-12-29 11:30:00', 3, 21, 'came', '2025-12-29', 'recovery', '2025-12-29', 'refer', NULL, '2025-12-29 04:27:42', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:28:44', 'สุขใจ (ทดสอบ) ซ่อมไว', NULL),
(9, 67, '2026-01-12 12:50:00', 'test', '2026-01-12 13:25:00', 3, 4, 'came', '2026-01-12', 'improve', '2026-01-12', 'refer', NULL, '2026-01-12 04:12:49', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 06:57:13', 'สุขใจ (ทดสอบ) ซ่อมไว', 'approval'),
(11, 62, '2026-01-12 12:00:00', 'erw', '2026-01-12 12:43:00', 2, 127, NULL, NULL, NULL, '2026-01-12', 'refer', 'test11', '2026-01-12 07:03:46', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:11:24', 'สุขใจ (ทดสอบ) ซ่อมไว', 'approval'),
(12, 68, '2026-01-13 17:05:00', 'xxxx', '2026-01-13 17:15:00', 1, 23, 'came', '2026-01-13', 'improve', '2026-01-13', 'home', '', '2026-01-13 07:31:13', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:36:31', 'สุขใจ (ทดสอบ) ซ่อมไว', 'approval'),
(13, 71, NULL, '', NULL, 4, 0, 'came', NULL, NULL, NULL, NULL, NULL, '2026-01-15 06:33:29', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 06:35:42', 'สุขใจ (ทดสอบ) ซ่อมไว', 'against'),
(15, 75, '2026-01-16 16:20:00', 'test', '2026-01-16 16:30:00', 1, 12, 'not_came', NULL, 'recovery', '2026-01-16', 'home', '', '2026-01-16 09:37:18', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:37:28', 'สุขใจ (ทดสอบ) ซ่อมไว', 'approval');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_ward_monitoring`
--

CREATE TABLE `tbl_ward_monitoring` (
  `id` int(11) NOT NULL,
  `admission_id` int(11) NOT NULL,
  `record_datetime` datetime NOT NULL COMMENT 'วันที่/เวลา (Date/Time)',
  `sbp` smallint(6) DEFAULT NULL COMMENT 'SBP (mmHg)',
  `dbp` smallint(6) DEFAULT NULL COMMENT 'DBP(mmHg)',
  `nihss` tinyint(4) DEFAULT NULL COMMENT 'NIHSS (ประเมินซ้ำ)',
  `gcs` varchar(20) DEFAULT NULL COMMENT 'GCS (E_M_V_)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='ตารางลูก 1:N เก็บ Flowsheet (SBP, NIHSS) จาก Ward';

--
-- Dumping data for table `tbl_ward_monitoring`
--

INSERT INTO `tbl_ward_monitoring` (`id`, `admission_id`, `record_datetime`, `sbp`, `dbp`, `nihss`, `gcs`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 9, '2025-11-21 11:50:00', 50, 55, 42, '44', '2025-11-21 04:41:48', 'System', '2025-11-21 04:41:48', '0'),
(2, 8, '2025-11-22 23:50:00', 22, 33, 44, '55', '2025-11-21 06:47:33', 'System', '2025-11-21 06:47:33', '0'),
(3, 8, '2025-11-21 13:47:00', 55, 44, 33, '22', '2025-11-21 06:47:49', 'System', '2025-11-21 06:47:49', '0'),
(4, 8, '2025-11-21 13:53:00', 11, 22, 33, '55', '2025-11-21 06:52:31', 'System', '2025-11-21 06:52:31', '0'),
(5, 56, '2025-12-23 14:10:00', 220, 445, 24, '125', '2025-12-23 07:10:04', 'System', '2025-12-23 07:10:04', 'System'),
(6, 57, '2025-12-25 09:01:00', 25, 21, 15, '222', '2025-12-25 08:02:10', 'System', '2025-12-25 08:02:10', 'System'),
(7, 11, '2025-12-25 09:09:00', 33, 22, 32, 'e3', '2025-12-25 08:09:32', 'System', '2025-12-25 08:09:32', 'System'),
(8, 10, '2025-12-25 09:21:00', 231, 231, 23, '2', '2025-12-25 08:21:39', 'System', '2025-12-25 08:21:39', 'System'),
(9, 60, '2025-12-29 05:25:00', 22, 11, 24, 'E5', '2025-12-29 04:27:00', 'สุขใจ (ทดสอบ) ซ่อมไว', '2025-12-29 04:27:00', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(10, 67, '2026-01-12 14:04:00', 25, 23, 13, '45', '2026-01-12 04:11:19', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 04:11:19', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(11, 62, '2026-01-12 08:03:00', 22, 343, 34, '343', '2026-01-12 07:03:24', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-12 07:03:24', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(12, 68, '2026-01-13 08:30:00', 32, 12, 23, '22', '2026-01-13 07:30:04', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-13 07:30:04', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(13, 72, '2026-01-15 08:02:00', 3, 3, 0, '', '2026-01-15 07:02:43', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-15 07:02:43', 'สุขใจ (ทดสอบ) ซ่อมไว'),
(14, 75, '2026-01-16 17:36:00', 1, 1, 42, '125', '2026-01-16 09:36:39', 'สุขใจ (ทดสอบ) ซ่อมไว', '2026-01-16 09:36:39', 'สุขใจ (ทดสอบ) ซ่อมไว');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_er`
--
ALTER TABLE `tbl_er`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_id` (`admission_id`);

--
-- Indexes for table `tbl_followup`
--
ALTER TABLE `tbl_followup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- Indexes for table `tbl_or_procedure`
--
ALTER TABLE `tbl_or_procedure`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_id` (`admission_id`);

--
-- Indexes for table `tbl_patient`
--
ALTER TABLE `tbl_patient`
  ADD PRIMARY KEY (`hn`),
  ADD UNIQUE KEY `id_card` (`id_card`);

--
-- Indexes for table `tbl_stroke_admission`
--
ALTER TABLE `tbl_stroke_admission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_hn` (`patient_hn`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `hr_cid` (`hr_cid`);

--
-- Indexes for table `tbl_ward`
--
ALTER TABLE `tbl_ward`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admission_id` (`admission_id`);

--
-- Indexes for table `tbl_ward_monitoring`
--
ALTER TABLE `tbl_ward_monitoring`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admission_id` (`admission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_er`
--
ALTER TABLE `tbl_er`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tbl_followup`
--
ALTER TABLE `tbl_followup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `tbl_or_procedure`
--
ALTER TABLE `tbl_or_procedure`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `tbl_stroke_admission`
--
ALTER TABLE `tbl_stroke_admission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `tbl_ward`
--
ALTER TABLE `tbl_ward`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tbl_ward_monitoring`
--
ALTER TABLE `tbl_ward_monitoring`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_er`
--
ALTER TABLE `tbl_er`
  ADD CONSTRAINT `tbl_er_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `tbl_stroke_admission` (`id`);

--
-- Constraints for table `tbl_followup`
--
ALTER TABLE `tbl_followup`
  ADD CONSTRAINT `tbl_followup_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `tbl_stroke_admission` (`id`);

--
-- Constraints for table `tbl_or_procedure`
--
ALTER TABLE `tbl_or_procedure`
  ADD CONSTRAINT `tbl_or_procedure_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `tbl_stroke_admission` (`id`);

--
-- Constraints for table `tbl_stroke_admission`
--
ALTER TABLE `tbl_stroke_admission`
  ADD CONSTRAINT `tbl_stroke_admission_ibfk_1` FOREIGN KEY (`patient_hn`) REFERENCES `tbl_patient` (`hn`);

--
-- Constraints for table `tbl_ward`
--
ALTER TABLE `tbl_ward`
  ADD CONSTRAINT `tbl_ward_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `tbl_stroke_admission` (`id`);

--
-- Constraints for table `tbl_ward_monitoring`
--
ALTER TABLE `tbl_ward_monitoring`
  ADD CONSTRAINT `tbl_ward_monitoring_ibfk_1` FOREIGN KEY (`admission_id`) REFERENCES `tbl_stroke_admission` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
