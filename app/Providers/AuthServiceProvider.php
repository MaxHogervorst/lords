<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Register AuthPolicy for guest authorization
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define admin gate
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        // Define gates for admin-only features
        Gate::define('manage-invoices', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-fiscus', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-sepa', function ($user) {
            return $user->isAdmin();
        });

        // Define guest gate using AuthPolicy
        Gate::define('guest', [\App\Policies\AuthPolicy::class, 'guest']);
    }
}
