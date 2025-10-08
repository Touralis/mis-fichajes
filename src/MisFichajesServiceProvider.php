<?php

namespace Touralis\MisFichajes;

use Illuminate\Support\ServiceProvider;
use Touralis\MisFichajes\Commands\InstallCommand;

class MisFichajesServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    // Registrar el comando artisan
    if ($this->app->runningInConsole()) {
      $this->commands([
        InstallCommand::class,
      ]);
    }

    // Publicar archivos (opcional, como alternativa al comando install)
    // if (method_exists($this, 'publishes')) {
    //   $this->publishes([
    //     __DIR__ . '/../stubs/controllers' => $this->app->basePath('app/Http/Controllers'),
    //     __DIR__ . '/../stubs/models' => $this->app->basePath('app/Models'),
    //     __DIR__ . '/../stubs/migrations' => $this->app->basePath('database/migrations'),
    //     __DIR__ . '/../stubs/views' => $this->app->basePath('resources/views'),
    //     __DIR__ . '/../stubs/routes' => $this->app->basePath('routes'),
    //   ], 'mis-fichajes');
    // }
  }

  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }
}
