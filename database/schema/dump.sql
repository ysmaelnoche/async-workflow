/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `approver_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `approver_permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `accnt_id` bigint(20) unsigned NOT NULL,
  `can_approve_pending` tinyint(1) NOT NULL DEFAULT 1,
  `can_approve_in_progress` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `approver_permissions_accnt_id_foreign` (`accnt_id`),
  CONSTRAINT `approver_permissions_accnt_id_foreign` FOREIGN KEY (`accnt_id`) REFERENCES `tb_account` (`accnt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_approvals` (
  `approval_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` bigint(20) unsigned NOT NULL,
  `approver_id` bigint(20) unsigned NOT NULL,
  `action` enum('Approved','Rejected','Noted','Submitted','Evaluate','Assigned','Send Feedback','Job Order Created') NOT NULL,
  `comments` text DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `approval_level` varchar(255) DEFAULT NULL,
  `sub_department` varchar(255) DEFAULT NULL,
  `estimated_completion_date` date DEFAULT NULL,
  `estimated_cost` decimal(12,2) DEFAULT NULL,
  `job_order_number` varchar(255) DEFAULT NULL,
  `signature_name` varchar(255) DEFAULT NULL,
  `signature_data` text DEFAULT NULL,
  `signature_style_choice` varchar(255) DEFAULT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `signature_style_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`approval_id`),
  KEY `form_approvals_form_id_foreign` (`form_id`),
  KEY `form_approvals_approver_id_foreign` (`approver_id`),
  KEY `form_approvals_signature_style_id_foreign` (`signature_style_id`),
  CONSTRAINT `form_approvals_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `tb_account` (`accnt_id`) ON DELETE CASCADE,
  CONSTRAINT `form_approvals_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_requests` (`form_id`) ON DELETE CASCADE,
  CONSTRAINT `form_approvals_signature_style_id_foreign` FOREIGN KEY (`signature_style_id`) REFERENCES `signature_styles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `form_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `form_requests` (
  `form_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `form_type` enum('IOM','Leave','Budget Slip','Vehicle Reservation and Trip Ticket','Loan','Job Orders') NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `from_department_id` bigint(20) unsigned DEFAULT NULL,
  `to_department_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by` bigint(20) unsigned NOT NULL,
  `current_approver_id` bigint(20) unsigned DEFAULT NULL,
  `assigned_sub_department` varchar(255) DEFAULT NULL,
  `auto_assignment_details` text DEFAULT NULL,
  `status` enum('Pending','Noted','Approved','Rejected','Cancelled','In Progress','Pending Target Department Approval','Pending PFMO Approval','Under Sub-Department Evaluation','Awaiting PFMO Decision') NOT NULL DEFAULT 'Pending',
  `date_submitted` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_approved` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  KEY `form_requests_from_department_id_foreign` (`from_department_id`),
  KEY `form_requests_to_department_id_foreign` (`to_department_id`),
  KEY `form_requests_requested_by_foreign` (`requested_by`),
  KEY `form_requests_current_approver_id_foreign` (`current_approver_id`),
  CONSTRAINT `form_requests_current_approver_id_foreign` FOREIGN KEY (`current_approver_id`) REFERENCES `tb_account` (`accnt_id`) ON DELETE SET NULL,
  CONSTRAINT `form_requests_from_department_id_foreign` FOREIGN KEY (`from_department_id`) REFERENCES `tb_department` (`department_id`) ON DELETE SET NULL,
  CONSTRAINT `form_requests_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `tb_account` (`accnt_id`) ON DELETE CASCADE,
  CONSTRAINT `form_requests_to_department_id_foreign` FOREIGN KEY (`to_department_id`) REFERENCES `tb_department` (`department_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `iom_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `iom_details` (
  `form_id` bigint(20) unsigned NOT NULL,
  `date_needed` date DEFAULT NULL,
  `priority` enum('Urgent','Routine','Rush') DEFAULT NULL,
  `purpose` varchar(100) DEFAULT NULL,
  `body` text DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  CONSTRAINT `iom_details_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_requests` (`form_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_order_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_order_progress` (
  `progress_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `update_type` enum('status','progress','issue','completion','pause','resume') NOT NULL DEFAULT 'progress',
  `progress_note` text NOT NULL,
  `percentage_complete` int(11) NOT NULL DEFAULT 0,
  `current_location` varchar(255) DEFAULT NULL,
  `issues_encountered` text DEFAULT NULL,
  `materials_needed` text DEFAULT NULL,
  `estimated_time_remaining` int(11) DEFAULT NULL,
  `photos` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photos`)),
  `priority_level` enum('Low','Normal','High','Urgent') NOT NULL DEFAULT 'Normal',
  `requires_assistance` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`progress_id`),
  KEY `job_order_progress_user_id_foreign` (`user_id`),
  KEY `job_order_progress_job_order_id_created_at_index` (`job_order_id`,`created_at`),
  CONSTRAINT `job_order_progress_job_order_id_foreign` FOREIGN KEY (`job_order_id`) REFERENCES `job_orders` (`job_order_id`) ON DELETE CASCADE,
  CONSTRAINT `job_order_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `tb_account` (`accnt_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_orders` (
  `job_order_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `job_order_number` varchar(255) NOT NULL,
  `form_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `control_number` varchar(255) DEFAULT NULL,
  `date_prepared` date DEFAULT NULL,
  `received_by` varchar(255) DEFAULT NULL,
  `requestor_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `request_description` text NOT NULL,
  `assistance` tinyint(1) NOT NULL DEFAULT 0,
  `repair_repaint` tinyint(1) NOT NULL DEFAULT 0,
  `installation` tinyint(1) NOT NULL DEFAULT 0,
  `cleaning` tinyint(1) NOT NULL DEFAULT 0,
  `check_up_inspection` tinyint(1) NOT NULL DEFAULT 0,
  `construction_fabrication` tinyint(1) NOT NULL DEFAULT 0,
  `pull_out_transfer` tinyint(1) NOT NULL DEFAULT 0,
  `replacement` tinyint(1) NOT NULL DEFAULT 0,
  `findings` text DEFAULT NULL,
  `actions_taken` text DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `job_completed_by` varchar(255) DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `job_completed` tinyint(1) NOT NULL DEFAULT 0,
  `for_further_action` tinyint(1) NOT NULL DEFAULT 0,
  `requestor_comments` text DEFAULT NULL,
  `requestor_signature` varchar(255) DEFAULT NULL,
  `requestor_signature_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed','For Further Action') NOT NULL DEFAULT 'Pending',
  `job_started_at` timestamp NULL DEFAULT NULL,
  `job_completed_at` timestamp NULL DEFAULT NULL,
  `work_duration_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`job_order_id`),
  UNIQUE KEY `job_orders_job_order_number_unique` (`job_order_number`),
  KEY `job_orders_form_id_foreign` (`form_id`),
  CONSTRAINT `job_orders_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_requests` (`form_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leave_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `leave_details` (
  `form_id` bigint(20) unsigned NOT NULL,
  `leave_type` enum('sick','vacation','emergency') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`form_id`),
  CONSTRAINT `leave_details_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `form_requests` (`form_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `signature_styles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `signature_styles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `font_family` varchar(255) NOT NULL,
  `preview_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sub_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sub_departments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `subdepartment_code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_departments_subdepartment_code_unique` (`subdepartment_code`),
  UNIQUE KEY `sub_departments_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_account` (
  `accnt_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Emp_No` varchar(255) NOT NULL,
  `department_id` bigint(20) unsigned NOT NULL,
  `sub_department_id` bigint(20) unsigned DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` enum('Head','Staff','Admin','VPAA') NOT NULL,
  `accessRole` enum('Approver','Viewer','Admin') NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`accnt_id`),
  UNIQUE KEY `tb_account_username_unique` (`username`),
  KEY `tb_account_emp_no_foreign` (`Emp_No`),
  KEY `tb_account_department_id_foreign` (`department_id`),
  KEY `tb_account_sub_department_id_foreign` (`sub_department_id`),
  CONSTRAINT `tb_account_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `tb_department` (`department_id`),
  CONSTRAINT `tb_account_emp_no_foreign` FOREIGN KEY (`Emp_No`) REFERENCES `tb_employeeinfo` (`Emp_No`),
  CONSTRAINT `tb_account_sub_department_id_foreign` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_department` (
  `department_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(255) NOT NULL,
  `category` enum('Non-teaching','Teaching') NOT NULL,
  `dept_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tb_employeeinfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tb_employeeinfo` (
  `Emp_No` varchar(255) NOT NULL,
  `Titles` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) DEFAULT NULL,
  `Suffix` varchar(255) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`Emp_No`),
  UNIQUE KEY `tb_employeeinfo_email_unique` (`Email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2025_06_01_084033_create_tb_department_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_06_01_084038_create_tb_employeeinfo_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_06_01_084042_create_tb_account_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_06_01_084043_create_approver_permissions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_06_01_085137_create_sessions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_06_01_090335_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_06_01_121905_create_form_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2025_06_01_122012_create_iom_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2025_06_01_122038_create_form_approvals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2025_06_01_124230_create_leave_details_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2025_06_01_131532_modify_form_requests_for_approvals',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2025_06_02_021510_add_signature_fields_to_form_approvals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2025_06_02_030000_create_signature_styles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2025_06_03_181825_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2025_06_03_182330_create_failed_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2025_06_07_000000_add_signature_style_id_to_form_approvals',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2025_06_10_005447_update_position_enum_in_tb_account_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2025_08_07_000001_add_sub_department_evaluation_status',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2025_08_07_000002_add_awaiting_pfmo_decision_status',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2025_08_09_154428_add_feedback_to_form_approvals_action_enum',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2025_08_19_120000_enhance_form_approvals_for_pfmo_workflow',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2025_08_19_120001_enhance_form_requests_for_pfmo_workflow',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2025_08_19_124159_add_auto_assignment_columns_to_form_requests_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2025_08_21_010800_create_sub_departments_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2025_08_21_103502_add_signature_style_choice_to_form_approvals',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2025_08_21_105819_add_additional_signature_styles',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2025_08_21_152634_create_job_orders_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2025_08_21_155527_create_job_order_progress_table',5);
