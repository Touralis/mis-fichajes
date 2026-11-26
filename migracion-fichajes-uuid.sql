-- Mis Fichajes: conversión de identificadores de fichajes a UUID.
-- Selecciona la base de datos correcta en phpMyAdmin antes de ejecutar.
-- Haz una copia de seguridad completa y detén la aplicación durante toda la operación.
-- Ejecuta el archivo una sola vez. Las tablas *_uuid_backup conservan los originales.
-- Si se produce un error, no vuelvas a ejecutar el archivo sin revisar el estado.

CREATE TABLE `attendance_uuid_map` (
  `old_id` bigint unsigned NOT NULL PRIMARY KEY,
  `uuid` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT INTO `attendance_uuid_map` (`old_id`, `uuid`)
  SELECT `id`, UUID() FROM `attendance_records`;

CREATE TABLE `attendance_records_uuid_new` LIKE `attendance_records`;
ALTER TABLE `attendance_records_uuid_new`
  MODIFY `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
INSERT INTO `attendance_records_uuid_new`
  (`id`, `user_id`, `started_at`, `ended_at`, `start_latitude`, `start_longitude`, `start_accuracy`, `end_latitude`, `end_longitude`, `end_accuracy`, `origin`, `generated_by`, `created_at`, `updated_at`)
SELECT m.`uuid`, r.`user_id`, r.`started_at`, r.`ended_at`, r.`start_latitude`, r.`start_longitude`, r.`start_accuracy`, r.`end_latitude`, r.`end_longitude`, r.`end_accuracy`, r.`origin`, r.`generated_by`, r.`created_at`, r.`updated_at`
FROM `attendance_records` r JOIN `attendance_uuid_map` m ON m.`old_id` = r.`id`;
ALTER TABLE `attendance_records_uuid_new`
  ADD CONSTRAINT `attendance_uuid_new_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `attendance_uuid_new_generated_fk` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);

CREATE TABLE `attendance_corrections_uuid_new` LIKE `attendance_corrections`;
ALTER TABLE `attendance_corrections_uuid_new`
  MODIFY `attendance_record_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
INSERT INTO `attendance_corrections_uuid_new`
  (`id`, `attendance_record_id`, `actor_id`, `previous_started_at`, `previous_ended_at`, `new_started_at`, `new_ended_at`, `reason`, `created_at`, `updated_at`)
SELECT c.`id`, m.`uuid`, c.`actor_id`, c.`previous_started_at`, c.`previous_ended_at`, c.`new_started_at`, c.`new_ended_at`, c.`reason`, c.`created_at`, c.`updated_at`
FROM `attendance_corrections` c JOIN `attendance_uuid_map` m ON m.`old_id` = c.`attendance_record_id`;
ALTER TABLE `attendance_corrections_uuid_new`
  ADD CONSTRAINT `corrections_uuid_new_record_fk` FOREIGN KEY (`attendance_record_id`) REFERENCES `attendance_records_uuid_new` (`id`),
  ADD CONSTRAINT `corrections_uuid_new_actor_fk` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

-- La operación falla antes de sustituir las tablas si falta alguna corrección.
CREATE TABLE `attendance_uuid_validation` (`valid` tinyint NOT NULL, CONSTRAINT `attendance_uuid_validation_check` CHECK (`valid` = 1));
INSERT INTO `attendance_uuid_validation` (`valid`)
SELECT IF((SELECT COUNT(*) FROM `attendance_records`) = (SELECT COUNT(*) FROM `attendance_records_uuid_new`)
       AND (SELECT COUNT(*) FROM `attendance_corrections`) = (SELECT COUNT(*) FROM `attendance_corrections_uuid_new`), 1, NULL);

RENAME TABLE `attendance_corrections` TO `attendance_corrections_uuid_backup`,
             `attendance_records` TO `attendance_records_uuid_backup`,
             `attendance_records_uuid_new` TO `attendance_records`,
             `attendance_corrections_uuid_new` TO `attendance_corrections`;
INSERT INTO `migrations` (`migration`, `batch`)
SELECT '2026_11_26_000001_convert_attendance_ids_to_uuid', COALESCE(MAX(`batch`), 0) + 1 FROM `migrations`;
DROP TABLE `attendance_uuid_validation`;
-- attendance_uuid_map permite consultar la correspondencia entre identificadores antiguos y nuevos.
