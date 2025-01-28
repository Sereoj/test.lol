<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::ignoreRoutes(); // Указываем, что стандартные маршруты не используются

        Passport::tokensCan([
            'access_api' => 'Access to API endpoints',
            'issue_access_token' => 'Issue new access tokens',
        ]);

        Passport::setDefaultScope([
            'access_api',
            'issue_access_token',
        ]);
    }
}
