<?php

namespace Touralis\MisFichajes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class InstallCommand extends Command
{
  /**
   * The name and signature of the console command.
   */
  protected $signature = 'touralis-misfichajes:install';

  /**
   * The console command description.
   */
  protected $description = 'Instala el paquete Mis Fichajes copiando archivos necesarios';

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $this->info('Iniciando instalación del paquete...');

    // Copiar archivos
    $this->copyFiles();

    // Crear usuario y empleado por defecto
    $this->createDefaultUserAndEmployee();

    $this->info('✅ Paquete instalado correctamente');

    return self::SUCCESS;
  }

  /**
   * Crear usuario y empleado por defecto
   */
  protected function createDefaultUserAndEmployee(): void
  {
    try {
      // Verificar si el usuario por defecto ya existe
      $userExists = DB::table('users')
        ->where('email', 'admin@fichajes.test')
        ->exists();

      if ($userExists) {
        $this->warn('⚠️ El usuario por defecto ya existe.');
        return;
      }

      $this->info('Creando usuario y empleado por defecto...');

      // Generar contraseña
      $password = $this->generatePassword();
      $hashedPassword = Hash::make($password);

      // Crear usuario
      $userId = DB::table('users')->insertGetId([
        'name' => 'Administrador',
        'email' => 'admin@fichajes.test',
        'password' => $hashedPassword,
        'email_verified_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      // Crear empleado asociado
      DB::table('fichaje_employers')->insert([
        'nombre' => 'Admin',
        'apellidos' => 'Sistema',
        'dni' => '12345678A',
        'mail' => 'admin@fichajes.test',
        'telefono' => '000000000',
        'password' => $hashedPassword,
        'puesto_trabajo' => 'Administrador',
        'horas_semanales' => '40',
        'numero_afiliacion_ss' => '0000000000000000',
        'user_id' => $userId,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      $this->info('✅ Usuario y empleado creados correctamente');
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

  /**
   * Generar una contraseña aleatoria de 12 caracteres
   */
  private function generatePassword(): string
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < 12; $i++) {
      $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  /**
   * Copiar archivos a sus ubicaciones correspondientes
   */
  protected function copyFiles(): void
  {
    $packagePath = __DIR__ . '/../../stubs';
    $basePath = $this->laravel->basePath();

    // Copiar controladores
    if (File::exists($packagePath . '/controllers')) {
      $this->info('Copiando controladores...');
      File::copyDirectory(
        $packagePath . '/controllers',
        $basePath . '/app/Http/Controllers'
      );
    }

    // Copiar modelos
    if (File::exists($packagePath . '/models')) {
      $this->info('Copiando modelos...');
      File::copyDirectory(
        $packagePath . '/models',
        $basePath . '/app/Models'
      );
    }

    // Copiar vistas
    if (File::exists($packagePath . '/views')) {
      $this->info('Copiando vistas...');
      File::copyDirectory(
        $packagePath . '/views',
        $basePath . '/resources/views/fichajes'
      );
    }

    // Copiar migraciones
    if (File::exists($packagePath . '/migrations')) {
      $this->info('Copiando migraciones...');
      $timestamp = date('Y_m_d_His');
      $migrations = File::files($packagePath . '/migrations');

      foreach ($migrations as $migration) {
        $filename = $timestamp . '_' . $migration->getFilename();
        File::copy(
          $migration->getPathname(),
          $basePath . '/database/migrations/' . $filename
        );
        $timestamp++;
      }

      $this->info('Ejecutando migraciones...');
      $this->call('migrate', ['--force' => true]);
    }

    // Copiar rutas
    if (File::exists($packagePath . '/routes/fichajes.php')) {
      $this->info('Añadiendo rutas al archivo web.php...');

      $sourceFile = $packagePath . '/routes/fichajes.php';
      $targetFile = $basePath . '/routes/web.php';

      // Leer contenido de la ruta del paquete
      $routesContent = File::get($sourceFile);

      // Quitar la etiqueta PHP inicial y espacios en blanco
      $routesContent = preg_replace('/^\s*<\?php\s*/', '', $routesContent);

      // Verificar si ya existen las rutas (para evitar duplicados)
      $webContent = File::get($targetFile);
      if (strpos($webContent, trim($routesContent)) === false) {
        // Añadir las rutas al final del archivo web.php con un comentario separador
        File::append($targetFile, "\n\n// Rutas añadidas por Mis Fichajes\n" . $routesContent . "\n");
        $this->info('✅ Rutas añadidas correctamente a web.php');
      } else {
        $this->warn('⚠️ Las rutas ya existen en web.php, no se duplicarán.');
      }
    }
  }
}
