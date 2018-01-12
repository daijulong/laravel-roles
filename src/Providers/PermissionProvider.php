<?php

namespace Daijulong\LaravelRoles\Providers;

use Illuminate\Support\ServiceProvider;
use Daijulong\LaravelRoles\Permission;

class PermissionProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/permission.php' => config_path('permission.php')
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('permission', function () {
            return new Permission();
        });
    }
}
