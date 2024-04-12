<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
        // Gate for approving admin users
        Gate::define('approve-admin', function ($user) {
            return $user->role === 'superadmin';
        });

        // Gate for approving student users
        Gate::define('approve-student', function ($user) {
            return $user->role === 'admin' || $user->role === 'superadmin';
        });
    }
}
