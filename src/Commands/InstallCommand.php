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
    $this->info('¡Hola Mundo desde Mis Fichajes!');
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
        $basePath . '/app/Http/Controllers/Fichajes'
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
        $timestamp++; // Incrementar para evitar conflictos
      }
    }

    // Copiar vistas
    if (File::exists($packagePath . '/views')) {
      $this->info('Copiando vistas...');
      File::copyDirectory(
        $packagePath . '/views',
        $basePath . '/resources/views/fichajes'
      );
    }

    // Copiar rutas
    if (File::exists($packagePath . '/routes')) {
      $this->info('Copiando archivo de rutas...');
      File::copy(
        $packagePath . '/routes/fichajes.php',
        $basePath . '/routes/fichajes.php'
      );
    }
  }
}
