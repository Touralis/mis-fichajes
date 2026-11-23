#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

PROJECT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
cd "$PROJECT_DIR"
command -v php >/dev/null 2>&1 || { echo 'Es necesario tener PHP instalado.' >&2; exit 1; }

read -r -p 'Nombre del administrador: ' ADMIN_NAME
read -r -p 'Correo electrónico: ' ADMIN_EMAIL
read -r -s -p 'Contraseña (mínimo 8 caracteres): ' ADMIN_PASSWORD
printf '\n'
export ADMIN_NAME ADMIN_EMAIL ADMIN_PASSWORD
php <<'PHP'
<?php
$name = trim(getenv('ADMIN_NAME'));
$email = trim(getenv('ADMIN_EMAIL'));
$password = getenv('ADMIN_PASSWORD');
if ($name === '' || mb_strlen($name, 'UTF-8') > 255 || !mb_check_encoding($name, 'UTF-8')) {
    fwrite(STDERR, "El nombre debe tener entre 1 y 255 caracteres válidos.\n"); exit(1);
}
if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "El correo electrónico no es válido.\n"); exit(1);
}
if (mb_strlen($password, 'UTF-8') < 8 || strlen($password) > 72 || !mb_check_encoding($password, 'UTF-8')) {
    fwrite(STDERR, "La contraseña debe tener al menos 8 caracteres y no superar 72 bytes.\n"); exit(1);
}
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
if ($hash === false) { fwrite(STDERR, "No se pudo generar la contraseña.\n"); exit(1); }
// Los valores hexadecimales evitan problemas con comillas y modos SQL de MySQL.
$sqlValue = static fn (string $value): string => "CONVERT(0x".bin2hex($value)." USING utf8mb4)";
$sql = "-- Crear un administrador de Mis Fichajes en MySQL o MariaDB.\n".
       "-- El correo debe ser único; esta consulta no modifica usuarios existentes.\n".
       "INSERT INTO `users` (`name`, `email`, `password`, `role`, `active`, `created_at`, `updated_at`)\nVALUES (".
       $sqlValue($name).", ".$sqlValue($email).", ".$sqlValue($hash).", 'admin', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);\n";
$file = 'admin-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.sql';
$handle = fopen($file, 'x');
if ($handle === false || fwrite($handle, $sql) !== strlen($sql) || !fclose($handle)) {
    fwrite(STDERR, "No se pudo guardar el archivo SQL.\n"); exit(1);
}
echo "SQL creado: ".getcwd().DIRECTORY_SEPARATOR.$file."\n";
echo "Ejecuta el contenido en la base de datos del proyecto.\n";
PHP
unset ADMIN_PASSWORD
