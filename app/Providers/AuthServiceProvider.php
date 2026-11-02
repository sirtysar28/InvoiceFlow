<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Admin area (Super Admin otomatis lolos semua)
        Gate::define('admin', fn ($user) => $user->isAdmin());
        Gate::define('superadmin', fn ($user) => $user->isSuperAdmin());

        // Menu permissions
        Gate::define('manage-companies', fn ($user) => $user->canManageCompanies());
        Gate::define('manage-customers', fn ($user) => $user->canManageCustomers());
        Gate::define('manage-products', fn ($user) => $user->canManageProducts());
        Gate::define('manage-quotations', fn ($user) => $user->canManageQuotations());
        Gate::define('manage-orders', fn ($user) => $user->canManageOrders());
        Gate::define('verify-payments', fn ($user) => $user->canVerifyPayments());
        Gate::define('view-reports', fn ($user) => $user->canViewReports());
    }
}
