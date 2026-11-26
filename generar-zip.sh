#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

PROJECT_DIR=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
cd "$PROJECT_DIR"
for command in php npm node tar zip; do
    command -v "$command" >/dev/null 2>&1 || {
        printf 'Falta el comando requerido: %s\n' "$command" >&2
        exit 1
    }
done
for path in vendor/autoload.php node_modules/.bin/vite .env.example; do
    [[ -e "$path" ]] || {
        printf 'Falta %s. Instala las dependencias antes de continuar.\n' "$path" >&2
        exit 1
    }
done

printf 'Datos de la instalación de Mis Fichajes\n'
CONFIG_PATH=${1:-}
if [[ $# -gt 1 ]]; then
    echo 'Uso: ./generar-zip.sh [archivo.conf]' >&2
    exit 1
fi
if [[ -z "$CONFIG_PATH" ]]; then
    read -r -p 'Archivo de configuración (Enter para introducir los datos manualmente): ' CONFIG_PATH
fi
ICON_PATH='' PRIMARY_COLOR='' ACCENT_COLOR='' CONFIG_EXCLUDE='' APP_KEY=''
if [[ -n "$CONFIG_PATH" ]]; then
    [[ -f "$CONFIG_PATH" && -r "$CONFIG_PATH" ]] || {
        echo 'No se puede leer el archivo de configuración.' >&2
        exit 1
    }
    CONFIG_PATH=$(realpath -- "$CONFIG_PATH")
    case "$CONFIG_PATH" in "$PROJECT_DIR"/*) CONFIG_EXCLUDE="./${CONFIG_PATH#"$PROJECT_DIR"/}" ;; esac
    DEPLOY_URL='' MOBILE_APP_ID='' DEPLOY_DB_HOST='127.0.0.1' DEPLOY_DB_PORT='3306' DEPLOY_DB_DATABASE='' DEPLOY_DB_USERNAME='' DEPLOY_DB_PASSWORD=''
    declare -A CONFIG_SEEN=()
    while IFS= read -r line || [[ -n "$line" ]]; do
        line=${line%$'\r'}
        [[ -z "$line" || "$line" =~ ^[[:space:]]*# ]] && continue
        [[ "$line" == *=* ]] || {
            echo 'Línea no válida en la configuración: se esperaba CLAVE=valor.' >&2
            exit 1
        }
        key=${line%%=*}
        value=${line#*=}
        case "$key" in
        DEPLOY_URL | MOBILE_APP_ID | DEPLOY_DB_HOST | DEPLOY_DB_PORT | DEPLOY_DB_DATABASE | DEPLOY_DB_USERNAME | DEPLOY_DB_PASSWORD | APP_KEY | ICON_PATH | PRIMARY_COLOR | ACCENT_COLOR) ;;
        *)
            printf 'Clave de configuración desconocida: %s\n' "$key" >&2
            exit 1
            ;;
        esac
        [[ ! -v CONFIG_SEEN[$key] ]] || {
            printf 'Clave repetida en la configuración: %s\n' "$key" >&2
            exit 1
        }
        CONFIG_SEEN[$key]=1
        printf -v "$key" '%s' "$value"
    done <"$CONFIG_PATH"
    if [[ ! -v CONFIG_SEEN[DEPLOY_DB_PASSWORD] ]]; then
        read -r -s -p 'Contraseña de la base de datos (puede estar vacía): ' DEPLOY_DB_PASSWORD
        printf '\n'
    fi
    if [[ -n "$ICON_PATH" && "$ICON_PATH" != /* ]]; then ICON_PATH="$(dirname -- "$CONFIG_PATH")/$ICON_PATH"; fi
else
    read -r -p 'URL completa del subdominio (ej. https://fichajes.ejemplo.com): ' DEPLOY_URL
    read -r -p 'Identificador de la app móvil (ej. com.empresa.fichajes): ' MOBILE_APP_ID
    read -r -p 'Host de la base de datos [127.0.0.1]: ' DEPLOY_DB_HOST
    DEPLOY_DB_HOST=${DEPLOY_DB_HOST:-127.0.0.1}
    read -r -p 'Puerto de la base de datos [3306]: ' DEPLOY_DB_PORT
    DEPLOY_DB_PORT=${DEPLOY_DB_PORT:-3306}
    read -r -p 'Nombre de la base de datos: ' DEPLOY_DB_DATABASE
    read -r -p 'Usuario de la base de datos: ' DEPLOY_DB_USERNAME
    read -r -s -p 'Contraseña de la base de datos (puede estar vacía): ' DEPLOY_DB_PASSWORD
    printf '\n'
    read -r -p '¿Añadir un icono personalizado? (s/N): ' CUSTOM_ICON
    case "$CUSTOM_ICON" in
    s | S | si | SI | sí | Sí | SÍ) read -r -p 'Ruta del archivo PNG: ' ICON_PATH ;;
    '' | n | N | no | NO) ;;
    *)
        echo 'Respuesta no válida.' >&2
        exit 1
        ;;
    esac
    read -r -p '¿Personalizar la paleta de colores? (S/n): ' CUSTOM_PALETTE
    case "$CUSTOM_PALETTE" in
    '' | s | S | si | SI | sí | Sí | SÍ)
        read -r -p 'Color principal oscuro (ej. #1e4037): ' PRIMARY_COLOR
        read -r -p 'Color de acento claro (ej. #a8d563): ' ACCENT_COLOR
        ;;
    n | N | no | NO) ;;
    *)
        echo 'Respuesta no válida.' >&2
        exit 1
        ;;
    esac
fi
[[ "$DEPLOY_URL" =~ ^https?://[^[:space:]/]+(/[^[:space:]]*)?$ ]] || {
    echo 'La URL debe empezar por http:// o https://.' >&2
    exit 1
}
[[ "$MOBILE_APP_ID" =~ ^[A-Za-z][A-Za-z0-9]*(\.[A-Za-z][A-Za-z0-9]*)+$ ]] || {
    echo 'Identificador de app inválido. Usa al menos dos partes separadas por puntos, por ejemplo com.empresa.fichajes; cada parte debe empezar por una letra y contener solo letras o números.' >&2
    exit 1
}
[[ "$DEPLOY_DB_PORT" =~ ^[0-9]+$ ]] && ((DEPLOY_DB_PORT >= 1 && DEPLOY_DB_PORT <= 65535)) || {
    echo 'Puerto inválido.' >&2
    exit 1
}
[[ -n "$DEPLOY_DB_DATABASE" && -n "$DEPLOY_DB_USERNAME" && -n "$DEPLOY_DB_HOST" ]] || {
    echo 'El host, el nombre y el usuario de la base de datos son obligatorios.' >&2
    exit 1
}
if [[ -n "$PRIMARY_COLOR" || -n "$ACCENT_COLOR" ]]; then
    [[ "$PRIMARY_COLOR" =~ ^#[[:xdigit:]]{6}$ && "$ACCENT_COLOR" =~ ^#[[:xdigit:]]{6}$ ]] || {
        echo 'Introduce ambos colores en formato #RRGGBB.' >&2
        exit 1
    }
fi
if [[ -n "$ICON_PATH" ]]; then
    [[ -f "$ICON_PATH" && -r "$ICON_PATH" ]] || {
        echo 'No se puede leer el archivo del icono.' >&2
        exit 1
    }
    ICON_PATH=$(realpath -- "$ICON_PATH")
    ICON_FILE="$ICON_PATH" php -r '$i = @getimagesize(getenv("ICON_FILE")); if (!$i || $i[2] !== IMAGETYPE_PNG) { fwrite(STDERR, "El icono debe ser un PNG válido.\n"); exit(1); }'
fi
if [[ -n "$APP_KEY" ]]; then
    APP_KEY_CHECK="$APP_KEY" php -r '$key = getenv("APP_KEY_CHECK"); if (!str_starts_with($key, "base64:") || strlen((string) base64_decode(substr($key, 7), true)) !== 32) { fwrite(STDERR, "APP_KEY debe ser una clave Laravel base64 de 32 bytes válida.\n"); exit(1); }'
fi
export APP_KEY DEPLOY_URL DEPLOY_DB_HOST DEPLOY_DB_PORT DEPLOY_DB_DATABASE DEPLOY_DB_USERNAME DEPLOY_DB_PASSWORD
if [[ -n "$ICON_PATH" ]]; then
    php -r 'if (!extension_loaded("gd")) { fwrite(STDERR, "Para generar los iconos de la app móvil es necesaria la extensión GD de PHP.\n"); exit(1); }'
fi
TMP_DIR=$(mktemp -d "${TMPDIR:-/tmp}/misfichajes-package.XXXXXXXX")
trap 'rm -rf -- "$TMP_DIR"' EXIT
STAGE="$TMP_DIR/proyecto"
mkdir -p "$STAGE"
# Se conservan las dependencias, pero no datos locales ni archivos privados del entorno de desarrollo.
tar -cf - \
    --exclude='./.git' --exclude='./.agents' --exclude='./.codex' --exclude='./.aws' \
    --exclude='./mobile-app' --exclude='./stubs' --exclude='./src' --exclude='./generar-zip.sh' --exclude='./generar_zip.sh' --exclude='./install.txt' --exclude='./misfichajes-base.sql' --exclude='./admin-*.sql' \
    --exclude='./colores*.txt' --exclude='./ACTUALIZAR-PRODUCCION.txt' --exclude='./ACTUALIZAR_PRODUCCION.txt' --exclude='./migracion-*.sql' \
    --exclude='./*.conf' --exclude="${CONFIG_EXCLUDE:-./.no-config-to-exclude}" --exclude='./.env' --exclude='./.env.*' --exclude='./auth.json' --exclude='./.phpunit.result.cache' \
    --exclude='./public/hot' --exclude='./public/storage' --exclude='./bootstrap/cache/*.php' \
    --exclude='./storage' --exclude='./database/*.sqlite' --exclude='./database/*.sqlite-*' \
    --exclude='./*.zip' --exclude='./*.sql' --exclude='./.phpbrewrc' --exclude='./.idea' --exclude='./.vscode' \
    . | tar -xf - -C "$STAGE"
if [[ -n "$ICON_PATH" ]]; then
    ICON_FILE="$ICON_PATH" ICON_OUTPUT="$STAGE/public/icono-personalizado.png" php <<'PHPICON'
<?php
$path = getenv('ICON_FILE');
[$width, $height] = getimagesize($path);
if ($width === $height) {
    if (!copy($path, getenv('ICON_OUTPUT'))) throw new RuntimeException('No se pudo copiar el icono.');
} else {
    if (!extension_loaded('gd')) throw new RuntimeException('Para adaptar un PNG rectangular es necesaria la extensión GD de PHP.');
    $source = imagecreatefrompng($path);
    $canvas = imagecreatetruecolor(512, 512);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
    $scale = min(512 / $width, 512 / $height);
    $newWidth = max(1, (int) round($width * $scale));
    $newHeight = max(1, (int) round($height * $scale));
    imagecopyresampled($canvas, $source, intdiv(512 - $newWidth, 2), intdiv(512 - $newHeight, 2), 0, 0, $newWidth, $newHeight, $width, $height);
    if (!imagepng($canvas, getenv('ICON_OUTPUT'))) throw new RuntimeException('No se pudo guardar el icono.');
    imagedestroy($source);
    imagedestroy($canvas);
    echo "Icono adaptado a un lienzo cuadrado transparente.\n";
}
PHPICON
fi
if [[ -n "$PRIMARY_COLOR" ]]; then
    (cd "$STAGE" && node scripts/aplicar-paleta.mjs "$PRIMARY_COLOR" "$ACCENT_COLOR")
fi
printf 'Compilando los recursos de producción...\n'
(cd "$STAGE" && npm run build)
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
    'APP_KEY' => getenv('APP_KEY') ?: 'base64:'.base64_encode(random_bytes(32)), 'APP_URL' => getenv('DEPLOY_URL'),
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
unset DEPLOY_DB_PASSWORD APP_KEY

# Apache debe poder atravesar los directorios y leer los archivos públicos.
# La clave y las credenciales permanecen accesibles solo para el propietario.
chmod -R a+rX "$STAGE"
chmod 600 "$STAGE/.env"
chmod -R ug+rwX "$STAGE/storage" "$STAGE/bootstrap/cache"

OUTPUT="$PROJECT_DIR/misfichajes-$(date +%Y%m%d-%H%M%S)-$$.zip"
(cd "$STAGE" && zip -qry "$OUTPUT" .)
# Preparar la app móvil por separado; no se incluye en el ZIP del servidor.
mkdir -p "$TMP_DIR/mobile-icons"
cp -- mobile-app/constants/branding.ts "$TMP_DIR/branding.ts"
node scripts/configurar-mobile.mjs "$TMP_DIR/branding.ts" "$DEPLOY_URL" "$PRIMARY_COLOR" "$ACCENT_COLOR"
cp -- mobile-app/app.json "$TMP_DIR/mobile-app.json"
node scripts/configurar-mobile-id.mjs "$TMP_DIR/mobile-app.json" "$MOBILE_APP_ID"
if [[ -n "$ICON_PATH" ]]; then
    ICON_FILE="$ICON_PATH" MOBILE_OUTPUT="$TMP_DIR/mobile-icons" php scripts/configurar-mobile.php
    cp -- "$TMP_DIR/mobile-icons/"*.png mobile-app/assets/images/
fi
cp -- "$TMP_DIR/branding.ts" mobile-app/constants/branding.ts
cp -- "$TMP_DIR/mobile-app.json" mobile-app/app.json
printf 'App móvil preparada en mobile-app con la URL indicada.\n'
printf '\nZIP creado: %s\n' "$OUTPUT"
printf 'El ZIP contiene credenciales: guárdalo en un lugar privado.\n'
printf 'Para actualizar, conserva la base de datos, storage/app/private y el .env original. Consulta el archivo local ACTUALIZAR-PRODUCCION.txt.\n'
printf 'Importa misfichajes-base.sql por separado y configura la raíz web del subdominio en la carpeta public.\n'
printf 'El servidor necesita PHP y sus extensiones, y permisos de escritura en storage y bootstrap/cache.\n'
