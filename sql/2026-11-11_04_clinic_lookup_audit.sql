-- Migration 04: autocomplete store + audit log
CREATE TABLE IF NOT EXISTS `clinic_lookup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `value` varchar(255) NOT NULL,
  `use_count` int(10) unsigned NOT NULL DEFAULT 1,
  `last_used` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_clinic_lookup` (`category`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `clinic_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `table_name` varchar(64) NOT NULL,
  `row_id` int(10) unsigned DEFAULT NULL,
  `action` varchar(20) NOT NULL,
  `old_json` mediumtext DEFAULT NULL,
  `new_json` mediumtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_clinic_audit_row` (`table_name`,`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `schema_migrations` (`migration`) VALUES ('2026-11-11_04_clinic_lookup_audit');
