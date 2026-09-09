<?php

namespace App\Providers;

use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Infrastructure\Identifiers\UlidPublicIdGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PublicIdGenerator::class, UlidPublicIdGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
