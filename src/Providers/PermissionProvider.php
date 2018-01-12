<?php

namespace Daijulong\LaravelRoles\Providers;

use Daijulong\LaravelRoles\Console\IdentityCommand;
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
        if (config('app.env') != 'production') {
            $this->app->singleton('command.permission.identity', function ($app) {
                return new IdentityCommand($app['files'], $app['composer']);
            });
            $this->commands(['command.permission.identity']);
        }

        $this->app->singleton('permission', function () {
            return new Permission();
        });
    }
}
