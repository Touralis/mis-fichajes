<?php

namespace Touralis\MisFichajes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallCommand extends Command
{
  protected $signature = 'touralis-misfichajes:install';
  protected $description = 'Instala el paquete Mis Fichajes copiando archivos necesarios';

  public function handle(): int
  {
    $this->info('Iniciando instalación del paquete...');
    $this->copyFiles();

    if ($this->confirm('¿Desea habilitar PWA en este proyecto?')) {
      $this->installPwa();
      $this->injectPwaInLayout();
    }

    if ($this->confirm('¿Desea añadir una APK para descarga?')) {
      $this->installApk();
    }

    if ($this->confirm('¿Desea crear usuarios por defecto?')) {
      $this->createDefaultUserAndEmployee();
    }
    $this->info('✅ Paquete instalado correctamente');

    return self::SUCCESS;
  }

  protected function installApk(): void
  {
    $this->info('📱 Instalación de APK');

    $apkPath = $this->ask('Introduce la ruta absoluta del archivo APK');

    if (!File::exists($apkPath)) {
      $this->error('❌ El archivo no existe en la ruta especificada');
      return;
    }

    if (!str_ends_with(strtolower($apkPath), '.apk')) {
      $this->error('❌ El archivo debe tener extensión .apk');
      return;
    }

    $publicPath = $this->laravel->publicPath();
    $destinationPath = $publicPath . '/app.apk';

    try {
      File::copy($apkPath, $destinationPath);
      $this->info('✅ APK copiada correctamente a public/app.apk');

      $fileSize = File::size($destinationPath);
      $fileSizeMB = round($fileSize / 1024 / 1024, 2);
      $this->line("📦 Tamaño del archivo: {$fileSizeMB} MB");
    } catch (\Exception $e) {
      $this->error('❌ Error al copiar la APK: ' . $e->getMessage());
    }
  }

  protected function installPwa(): void
  {
    $this->info('Instalando soporte PWA...');

    $packagePath = __DIR__ . '/../../stubs/pwa';
    $publicPath = $this->laravel->publicPath();

    // Manifest
    if (!File::exists($publicPath . '/manifest.json')) {
      File::copy(
        $packagePath . '/manifest.json',
        $publicPath . '/manifest.json'
      );
      $this->info('✅ manifest.json copiado');
    } else {
      $this->warn('⚠️ manifest.json ya existe');
    }

    // Service Worker
    if (!File::exists($publicPath . '/sw.js')) {
      File::copy(
        $packagePath . '/sw.js',
        $publicPath . '/sw.js'
      );
      $this->info('✅ sw.js copiado');
    } else {
      $this->warn('⚠️ sw.js ya existe');
    }

    // Icons
    if (File::exists($packagePath . '/icons')) {
      File::ensureDirectoryExists($publicPath . '/icons');

      foreach (File::files($packagePath . '/icons') as $icon) {
        $destination = $publicPath . '/icons/' . $icon->getFilename();
        if (!File::exists($destination)) {
          File::copy($icon->getPathname(), $destination);
        }
      }

      $this->info('✅ Iconos PWA copiados');
    }
  }

  protected function injectPwaInLayout(): void
  {
    $layouts = [
      resource_path('views/layouts/app.blade.php'),
      resource_path('views/layouts/main.blade.php'),
    ];

    $snippet = <<<HTML
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#2563eb">
<script>
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js');
}
</script>
HTML;

    foreach ($layouts as $layout) {
      if (!File::exists($layout)) {
        continue;
      }

      $content = File::get($layout);

      if (str_contains($content, 'serviceWorker.register')) {
        $this->warn("⚠️ PWA ya registrado en {$layout}");
        continue;
      }

      $content = str_replace(
        '</head>',
        $snippet . PHP_EOL . '</head>',
        $content
      );

      File::put($layout, $content);
      $this->info("✅ PWA inyectado en {$layout}");
    }
  }

  protected function createDefaultUserAndEmployee(): void
  {
    try {
      $adminExists = DB::table('users')->where('email', 'admin@fichajes.test')->exists();
      $userExists = DB::table('users')->where('email', 'user@fichajes.test')->exists();
      if ($userExists || $adminExists) {
        $this->warn('⚠️ Los usuarios por defecto ya existen.');
        return;
      }

      $this->info('Creando usuario y empleado por defecto...');
      $password = $this->generatePassword();
      $hashedPassword = Hash::make($password);

      $userId = DB::table('users')->insertGetId([
        'name' => 'Empleado Por Defecto',
        'email' => 'user@fichajes.test',
        'password' => $hashedPassword,
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      DB::table('fichaje_employers')->insert([
        'nombre' => 'Empleado',
        'apellidos' => 'Por Defecto',
        'dni' => '12345678A',
        'mail' => 'user@fichajes.test',
        'telefono' => '000000000',
        'password' => $password,
        'puesto_trabajo' => 'Empleado',
        'horas_diarias' => '8',
        'numero_afiliacion_ss' => '0000000000000000',
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      $this->info('✅ Empleado creado correctamente');
      $this->line('');
      $this->line('═══════════════════════════════════════');
      $this->line('📧 Email: user@fichajes.test');
      $this->line('🔐 Contraseña: ' . $password);
      $this->line('═══════════════════════════════════════');
      $this->line('');

      $password = $this->generatePassword();
      $hashedPassword = Hash::make($password);

      $userId = DB::table('users')->insertGetId([
        'name' => 'Admin',
        'email' => 'admin@fichajes.test',
        'password' => $hashedPassword,
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      $this->info('✅ Administrador creado correctamente');
      $this->line('');
      $this->line('═══════════════════════════════════════');
      $this->line('📧 Email: admin@fichajes.test');
      $this->line('🔐 Contraseña: ' . $password);
      $this->line('═══════════════════════════════════════');
      $this->line('');
    } catch (\Exception $e) {
      $this->error('❌ Error al crear usuario y empleado por defecto: ' . $e->getMessage());
    }
  }

  private function generatePassword(): string
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < 12; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }

  protected function copyFiles(): void
  {
    $packagePath = __DIR__ . '/../../stubs';
    $basePath = $this->laravel->basePath();

    // --- Copiar controladores ---
    if (File::exists($packagePath . '/controllers')) {
      $this->info('Copiando controladores...');
      foreach (File::files($packagePath . '/controllers') as $file) {
        $destination = $basePath . '/app/Http/Controllers/' . $file->getFilename();
        if (File::exists($destination)) {
          $this->warn("⚠️ Controlador {$file->getFilename()} ya existe, no se copiará.");
          continue;
        }
        File::copy($file->getPathname(), $destination);
      }
    }

    // --- Copiar modelos ---
    if (File::exists($packagePath . '/models')) {
      $this->info('Copiando modelos...');
      foreach (File::files($packagePath . '/models') as $file) {
        $destination = $basePath . '/app/Models/' . $file->getFilename();
        if (File::exists($destination)) {
          $this->warn("⚠️ Modelo {$file->getFilename()} ya existe, no se copiará.");
          continue;
        }
        File::copy($file->getPathname(), $destination);
      }
    }

    // --- Copiar vistas ---
    if (File::exists($packagePath . '/views')) {
      $this->info('Copiando vistas...');
      foreach (File::allFiles($packagePath . '/views') as $file) {
        $relativePath = $file->getRelativePathname();
        $destination = $basePath . '/resources/views/fichajes/' . $relativePath;
        if (File::exists($destination)) {
          $this->warn("⚠️ Vista {$relativePath} ya existe, no se copiará.");
          continue;
        }
        File::ensureDirectoryExists(dirname($destination));
        File::copy($file->getPathname(), $destination);
      }
    }

    // --- Copiar migraciones ---
    if (File::exists($packagePath . '/migrations')) {
      $this->info('Copiando migraciones...');
      $timestamp = date('Y_m_d_His');
      $migrations = File::files($packagePath . '/migrations');

      foreach ($migrations as $migration) {
        $originalName = $migration->getFilename();
        $destinationPattern = $basePath . '/database/migrations/*_' . $originalName;
        if (!empty(glob($destinationPattern))) {
          $this->warn("⚠️ La migración {$originalName} ya existe, no se copiará.");
          continue;
        }
        $filename = $timestamp . '_' . $originalName;
        File::copy($migration->getPathname(), $basePath . '/database/migrations/' . $filename);
        $timestamp++;
      }

      $this->info('Ejecutando migraciones...');
      $this->call('migrate', ['--force' => true]);
    }

    // --- Copiar rutas ---
    if (File::exists($packagePath . '/routes/fichajes.php')) {
      $this->info('Añadiendo rutas al archivo web.php...');
      $sourceFile = $packagePath . '/routes/fichajes.php';
      $targetFile = $basePath . '/routes/web.php';
      $routesContent = File::get($sourceFile);
      $routesContent = preg_replace('/^\s*<\?php\s*/', '', $routesContent);

      $webContent = File::get($targetFile);
      if (strpos($webContent, trim($routesContent)) === false) {
        File::append($targetFile, "\n\n// Rutas añadidas por Mis Fichajes\n" . $routesContent . "\n");
        $this->info('✅ Rutas añadidas correctamente a web.php');
      } else {
        $this->warn('⚠️ Las rutas ya existen en web.php, no se duplicarán.');
      }
    }
  }
}
