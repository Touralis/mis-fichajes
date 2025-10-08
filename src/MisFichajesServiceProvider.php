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
  }

  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }
}
