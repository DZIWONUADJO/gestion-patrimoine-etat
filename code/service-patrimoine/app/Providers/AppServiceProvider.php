<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-references', fn (User $user): bool => $user->is_active
            && $user->role === 'ADMIN');

        Gate::define('manage-biens', fn (User $user): bool => $user->is_active
            && in_array($user->role, ['ADMIN', 'GESTIONNAIRE'], true));

        Gate::define('archive-biens', fn (User $user): bool => $user->is_active
            && $user->role === 'ADMIN');

        Gate::define('validate-affectations', fn (User $user): bool => $user->is_active
            && in_array($user->role, ['ADMIN', 'CHEF_SERVICE'], true));
    }
}
