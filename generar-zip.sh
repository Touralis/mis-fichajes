#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

PROJECT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
cd "$PROJECT_DIR"
for command in php npm tar zip; do
    command -v "$command" >/dev/null 2>&1 || { printf 'Falta el comando requerido: %s\n' "$command" >&2; exit 1; }
done
for path in vendor/autoload.php node_modules/.bin/vite .env.example; do
    [[ -e "$path" ]] || { printf 'Falta %s. Instala las dependencias antes de continuar.\n' "$path" >&2; exit 1; }
done

printf 'Datos de la instalación de Mis Fichajes\n'
read -r -p 'URL completa del subdominio (ej. https://fichajes.ejemplo.com): ' DEPLOY_URL
read -r -p 'Host de la base de datos [127.0.0.1]: ' DEPLOY_DB_HOST
DEPLOY_DB_HOST=${DEPLOY_DB_HOST:-127.0.0.1}
read -r -p 'Puerto de la base de datos [3306]: ' DEPLOY_DB_PORT
DEPLOY_DB_PORT=${DEPLOY_DB_PORT:-3306}
read -r -p 'Nombre de la base de datos: ' DEPLOY_DB_DATABASE
read -r -p 'Usuario de la base de datos: ' DEPLOY_DB_USERNAME
read -r -s -p 'Contraseña de la base de datos (puede estar vacía): ' DEPLOY_DB_PASSWORD
printf '\n'
[[ "$DEPLOY_URL" =~ ^https?://[^[:space:]/]+(/[^[:space:]]*)?$ ]] || { echo 'La URL debe empezar por http:// o https://.' >&2; exit 1; }
[[ "$DEPLOY_DB_PORT" =~ ^[0-9]+$ ]] && (( DEPLOY_DB_PORT >= 1 && DEPLOY_DB_PORT <= 65535 )) || { echo 'Puerto inválido.' >&2; exit 1; }
[[ -n "$DEPLOY_DB_DATABASE" && -n "$DEPLOY_DB_USERNAME" ]] || { echo 'El nombre y el usuario de la base de datos son obligatorios.' >&2; exit 1; }
export DEPLOY_URL DEPLOY_DB_HOST DEPLOY_DB_PORT DEPLOY_DB_DATABASE DEPLOY_DB_USERNAME DEPLOY_DB_PASSWORD

printf 'Compilando los recursos de producción...\n'
npm run build
TMP_DIR=$(mktemp -d "${TMPDIR:-/tmp}/misfichajes-package.XXXXXXXX")
trap 'rm -rf -- "$TMP_DIR"' EXIT
STAGE="$TMP_DIR/proyecto"
mkdir -p "$STAGE"
# Se conservan las dependencias, pero no datos locales ni archivos privados del entorno de desarrollo.
tar -cf - \
    --exclude='./.git' --exclude='./.agents' --exclude='./.codex' --exclude='./.aws' \
    --exclude='./mobile-app' --exclude='./stubs' --exclude='./src' --exclude='./generar-zip.sh' --exclude='./generar_zip.sh' --exclude='./install.txt' --exclude='./misfichajes-base.sql' --exclude='./admin-*.sql' \
    --exclude='./.env' --exclude='./.env.*' --exclude='./auth.json' --exclude='./.phpunit.result.cache' \
    --exclude='./public/hot' --exclude='./public/storage' --exclude='./bootstrap/cache/*.php' \
    --exclude='./storage' --exclude='./database/*.sqlite' --exclude='./database/*.sqlite-*' \
    --exclude='./*.zip' --exclude='./.phpbrewrc' --exclude='./.idea' --exclude='./.vscode' \
    . | tar -xf - -C "$STAGE"
mkdir -p "$STAGE/storage/app/private" "$STAGE/storage/app/public" "$STAGE/storage/framework/cache/data" "$STAGE/storage/framework/sessions" "$STAGE/storage/framework/testing" "$STAGE/storage/framework/views" "$STAGE/storage/logs" "$STAGE/bootstrap/cache"

STAGE_DIR="$STAGE" php <<'PHP'
<?php
$stage = getenv('STAGE_DIR');
$env = file_get_contents(getcwd().'/.env.example');
function quoteEnv(string $value): string {
    return '"'.str_replace(['\\', '"', '$', "\r", "\n"], ['\\\\', '\\"', '\\$', '\\r', '\\n'], $value).'"';
}
$values = [
    'APP_ENV' => 'production', 'APP_DEBUG' => 'false',
    'APP_KEY' => 'base64:'.base64_encode(random_bytes(32)), 'APP_URL' => getenv('DEPLOY_URL'),
    'LOG_LEVEL' => 'warning', 'DB_CONNECTION' => 'mysql',
    'DB_HOST' => getenv('DEPLOY_DB_HOST'), 'DB_PORT' => getenv('DEPLOY_DB_PORT'),
    'DB_DATABASE' => getenv('DEPLOY_DB_DATABASE'), 'DB_USERNAME' => getenv('DEPLOY_DB_USERNAME'),
    'DB_PASSWORD' => getenv('DEPLOY_DB_PASSWORD'),
];
foreach ($values as $key => $value) {
    $line = $key.'='.quoteEnv($value);
    $pattern = '/^\s*#?\s*'.preg_quote($key, '/').'=.*$/m';
    if (preg_match($pattern, $env)) $env = preg_replace_callback($pattern, fn () => $line, $env, 1);
    else $env .= "\n".$line."\n";
}
if (file_put_contents($stage.'/.env', $env) === false) throw new RuntimeException('No se pudo crear el archivo .env.');
PHP
unset DEPLOY_DB_PASSWORD

# Apache debe poder atravesar los directorios y leer los archivos públicos.
# La clave y las credenciales permanecen accesibles solo para el propietario.
chmod -R a+rX "$STAGE"
chmod 600 "$STAGE/.env"
chmod -R ug+rwX "$STAGE/storage" "$STAGE/bootstrap/cache"

OUTPUT="$PROJECT_DIR/misfichajes-$(date +%Y%m%d-%H%M%S)-$$.zip"
(cd "$STAGE" && zip -qry "$OUTPUT" .)
printf '\nZIP creado: %s\n' "$OUTPUT"
printf 'El ZIP contiene credenciales: guárdalo en un lugar privado.\n'
printf 'Importa misfichajes-base.sql por separado y configura la raíz web del subdominio en la carpeta public.\n'
printf 'El servidor necesita PHP y sus extensiones, y permisos de escritura en storage y bootstrap/cache.\n'
