<?php

namespace Touralis\MisFichajes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

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

    $this->info('✅ Paquete instalado correctamente');

    return self::SUCCESS;
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
