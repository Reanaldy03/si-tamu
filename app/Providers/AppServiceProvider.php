<?php

namespace App\Providers;

use App\Domain\Shared\Contracts\PublicIdGenerator;
use App\Infrastructure\Identifiers\UlidPublicIdGenerator;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\OrganizationSetting;
use App\Models\User;
use App\Models\Visit;
use App\Policies\AuditLogPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\OrganizationSettingPolicy;
use App\Policies\ReportPolicy;
use App\Policies\UserPolicy;
use App\Policies\VisitPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Visit::class, VisitPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(OrganizationSetting::class, OrganizationSettingPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        Gate::define('reports.view', [ReportPolicy::class, 'view']);
        Gate::define('reports.export', [ReportPolicy::class, 'export']);
        Gate::define('audit.view', fn (User $user) => $user->hasPermission('audit.view'));
    }
}
