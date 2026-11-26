-- Mis Fichajes: instalación inicial para MySQL/MariaDB.
-- Importar en una base de datos vacía seleccionada previamente.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'employee',
  `position` varchar(255) DEFAULT NULL,
  `daily_hours` decimal(4,2) NOT NULL DEFAULT 8.00,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `signature_path` varchar(255) DEFAULT NULL,
  `identity_number` varchar(50) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `social_security_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE `attendance_records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `start_latitude` decimal(10,7) DEFAULT NULL,
  `start_longitude` decimal(10,7) DEFAULT NULL,
  `start_accuracy` decimal(10,2) DEFAULT NULL,
  `end_latitude` decimal(10,7) DEFAULT NULL,
  `end_longitude` decimal(10,7) DEFAULT NULL,
  `end_accuracy` decimal(10,2) DEFAULT NULL,
  `origin` varchar(20) DEFAULT NULL,
  `generated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_records_user_id_started_at_index` (`user_id`,`started_at`),
  CONSTRAINT `attendance_records_generated_by_foreign` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`),
  CONSTRAINT `attendance_records_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attendance_corrections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `attendance_record_id` bigint(20) unsigned NOT NULL,
  `actor_id` bigint(20) unsigned NOT NULL,
  `previous_started_at` timestamp NOT NULL,
  `previous_ended_at` timestamp NULL DEFAULT NULL,
  `new_started_at` timestamp NOT NULL,
  `new_ended_at` timestamp NULL DEFAULT NULL,
  `reason` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_corrections_attendance_record_id_foreign` (`attendance_record_id`),
  KEY `attendance_corrections_actor_id_foreign` (`actor_id`),
  CONSTRAINT `attendance_corrections_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`),
  CONSTRAINT `attendance_corrections_attendance_record_id_foreign` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `day_observations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `day_observations_date_user_id_index` (`date`,`user_id`),
  CONSTRAINT `day_observations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `company_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `signature_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `allow_attendance_generation` tinyint(1) NOT NULL DEFAULT 0,
  `require_geolocation` tinyint(1) NOT NULL DEFAULT 0,
  `allow_attendance_correction` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000000_create_users_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000001_create_cache_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('0001_01_01_000002_create_jobs_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_01_000000_create_attendance_tables', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_02_000000_create_attendance_corrections_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_03_000000_create_company_settings_table', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_04_000000_add_employee_identification', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_05_000000_add_attendance_generation_settings', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_06_000000_remove_generated_attendance_flag', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_07_000000_add_employee_phone', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_08_000000_add_attendance_geolocation', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_09_000000_add_attendance_correction_setting', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_10_000000_add_user_signature', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_11_000000_create_day_observations', 1);
INSERT INTO `migrations` (`migration`, `batch`) VALUES ('2026_01_12_000000_add_attendance_origin', 1);

INSERT INTO `users` (`name`, `email`, `password`, `role`, `active`, `created_at`, `updated_at`) VALUES ('Admin', 'admin@gmail.com', '$2y$10$Y7rsfTOOgHSlOYKOxBNvm.i8lNFeG0u2vr8IMiIe/nV3XtF/qA.Re', 'admin', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
