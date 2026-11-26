-- Ejecutar una sola vez en phpMyAdmin, seleccionando la base de datos de Mis Fichajes.
-- Esta actualización parte de una instalación sin firmas que conservar.
ALTER TABLE `users` ADD COLUMN `signature_data` LONGTEXT NULL;
ALTER TABLE `company_settings` ADD COLUMN `signature_data` LONGTEXT NULL, ADD COLUMN `signature_mime` VARCHAR(32) NULL;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_26_000002_store_signatures_in_database', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
