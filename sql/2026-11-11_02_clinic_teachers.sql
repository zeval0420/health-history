-- Migration 02: teachers. Autofilled rows are COPIED from reserve-a-lab `accounts`; no foreign key.
CREATE TABLE IF NOT EXISTS `clinic_teachers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_no` varchar(20) DEFAULT NULL,
  `source` varchar(20) NOT NULL DEFAULT 'manual',
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) NOT NULL DEFAULT '',
  `position` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(100) NOT NULL DEFAULT '',
  `dob` date DEFAULT NULL,
  `sex` varchar(20) NOT NULL DEFAULT '',
  `department` varchar(100) NOT NULL DEFAULT '',
  `contact` varchar(100) NOT NULL DEFAULT '',
  `emergency_contact` varchar(255) NOT NULL DEFAULT '',
  `blood_type` varchar(10) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clinic_teachers_employee_no` (`employee_no`),
  KEY `idx_clinic_teachers_name` (`last_name`,`first_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `schema_migrations` (`migration`) VALUES ('2026-11-11_02_clinic_teachers');
