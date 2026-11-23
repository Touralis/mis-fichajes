# Mis Fichajes

Aplicación independiente de control horario construida con Laravel 12 y Vue 3. Permite registrar entradas y salidas, consultar el historial, administrar cuentas y exportar registros en CSV. No utiliza Livewire ni Filament.

## Requisitos

PHP 8.2 o superior y las extensiones exigidas por Laravel, Composer, Node.js y npm para compilar, y MySQL/MariaDB o SQLite. En producción se recomienda HTTPS y una copia de seguridad periódica de la base de datos y del archivo `.env`.

## Instalación en Plesk

1. Cree el dominio y una base de datos MySQL/MariaDB en Plesk. Configure el directorio raíz del documento del dominio para que apunte a la carpeta `public` de este proyecto. Nunca exponga la raíz del proyecto como directorio público.
2. Suba el proyecto completo al alojamiento. Desde su directorio, con acceso SSH, ejecute `composer install --no-dev --optimize-autoloader` y `npm ci && npm run build`. Si el servidor no dispone de Node, compile con `npm ci && npm run build` en un equipo compatible y suba también `public/build`.
3. Copie `.env.example` a `.env` y configure `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`, y la conexión `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` con los datos de Plesk.
4. Ejecute `php artisan key:generate`, `php artisan migrate --force` y `php artisan misfichajes:admin`. Este último solicita los datos del primer administrador. No hay contraseñas predeterminadas.
5. Compruebe que el usuario de PHP puede escribir en `storage` y `bootstrap/cache`. Visite el dominio e inicie sesión.

Para desarrollo local: copie `.env.example` a `.env`, cree `database/database.sqlite`, ejecute `composer install`, `php artisan key:generate`, `php artisan migrate`, `php artisan misfichajes:admin`, `npm ci` y `npm run dev`; en otra terminal ejecute `php artisan serve`.

El resumen muestra los 100 registros más recientes; la sección Registros permite consultar el historial completo con filtros y paginación. El administrador puede exportar en CSV y corregir fichajes indicando un motivo; cada corrección conserva los valores anteriores y el responsable. Los empleados pueden descargar su propio informe PDF por período y los administradores pueden generarlo para cualquier empleado. Los informes se filtran por fecha de entrada y los fichajes abiertos no se suman al total. La aplicación no importa automáticamente los datos de la versión anterior: conserve la instalación y la base de datos originales hasta preparar y comprobar una migración específica.

## Integración en un Laravel existente

El código anterior del paquete se conserva en `src/` y `stubs/` como referencia histórica. La aplicación nueva es independiente; todavía no existe un instalador compatible que integre esta versión Vue en otro proyecto Laravel. No ejecute el instalador antiguo para instalar esta versión ni lo aplique a una base de datos existente. La integración requiere adaptar autenticación, rutas, migraciones y compilación al proyecto de destino.
