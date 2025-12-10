<?php

namespace App\Providers;

use App\Repositories\Contracts\IncidentRepositoryInterface;
use App\Repositories\Contracts\TradeRepositoryInterface;
use App\Repositories\IncidentRepository;
use App\Repositories\TradeRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider for binding Repository interfaces to implementations.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(TradeRepositoryInterface::class, TradeRepository::class);
        $this->app->bind(IncidentRepositoryInterface::class, IncidentRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
