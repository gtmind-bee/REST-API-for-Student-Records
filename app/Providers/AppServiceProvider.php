<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Sanctum 4 migrations are published explicitly by the package.
        // This project includes its own personal_access_tokens migration.
    }

    public function boot(): void
    {
        //
    }
}
