-- Migration 03: per-teacher health records (all free text except dates)

CREATE TABLE IF NOT EXISTS `clinic_medications` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL DEFAULT '',
  `frequency` varchar(100) NOT NULL DEFAULT '',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `prescriber` varchar(150) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_medications_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_medications_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_conditions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL DEFAULT '',
  `diagnosed_on` date DEFAULT NULL,
  `resolved_on` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_conditions_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_conditions_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_vaccinations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `vaccine` varchar(255) NOT NULL,
  `dose_label` varchar(50) NOT NULL DEFAULT '',
  `date_given` date DEFAULT NULL,
  `next_due` date DEFAULT NULL,
  `lot_no` varchar(100) NOT NULL DEFAULT '',
  `given_by` varchar(150) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_vaccinations_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_vaccinations_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_allergies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `allergen` varchar(255) NOT NULL,
  `reaction` varchar(255) NOT NULL DEFAULT '',
  `severity` varchar(50) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_allergies_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_allergies_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_visits` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `visit_date` date NOT NULL,
  `complaint` varchar(255) NOT NULL DEFAULT '',
  `assessment` varchar(255) NOT NULL DEFAULT '',
  `action_taken` varchar(255) NOT NULL DEFAULT '',
  `attended_by` varchar(150) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_visits_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_visits_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_procedures` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `procedure_date` date DEFAULT NULL,
  `facility` varchar(150) NOT NULL DEFAULT '',
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_procedures_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_procedures_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_other_records` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) unsigned NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `record_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_clinic_other_records_teacher` (`teacher_id`),
  CONSTRAINT `fk_clinic_other_records_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `clinic_teachers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `schema_migrations` (`migration`) VALUES ('2026-11-11_03_clinic_records');
