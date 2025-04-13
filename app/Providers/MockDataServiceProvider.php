<?php

namespace App\Providers;

use App\Services\MockDataService;
use Illuminate\Support\ServiceProvider;

class MockDataServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mock-data', function ($app) {
            return new MockDataService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
} 