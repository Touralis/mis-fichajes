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
    if ($this->confirm('¿Desea crear usuarios por defecto?')) {
      $this->createDefaultUserAndEmployee();
    }
    $this->info('✅ Paquete instalado correctamente');

    return self::SUCCESS;
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
