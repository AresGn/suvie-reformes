<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\PermissionService;

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
        // Directives Blade personnalisées pour les permissions
        $this->registerBladeDirectives();
    }

    /**
     * Enregistre les directives Blade personnalisées
     */
    private function registerBladeDirectives()
    {
        // Directive pour vérifier les rôles
        Blade::if('hasRole', function ($role) {
            return app(PermissionService::class)->hasRole($role);
        });

        // Directive pour vérifier les permissions
        Blade::if('hasPermission', function ($permission) {
            return app(PermissionService::class)->hasPermission($permission);
        });

        // Directive pour vérifier l'accès aux menus
        Blade::if('canAccessMenu', function ($menuId) {
            return app(PermissionService::class)->canAccessMenu($menuId);
        });

        // Directive pour vérifier si l'utilisateur est admin
        Blade::if('isAdmin', function () {
            return app(PermissionService::class)->isAdmin();
        });

        // Directives pour les actions CRUD
        Blade::if('canCreate', function ($resource) {
            return app(PermissionService::class)->canCreate($resource);
        });

        Blade::if('canRead', function ($resource) {
            return app(PermissionService::class)->canRead($resource);
        });

        Blade::if('canUpdate', function ($resource) {
            return app(PermissionService::class)->canUpdate($resource);
        });

        Blade::if('canDelete', function ($resource) {
            return app(PermissionService::class)->canDelete($resource);
        });
    }
}
