<?php

namespace Daijulong\LaravelRoles\Facades;

use Illuminate\Support\Facades\Facade;

class PermissionFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'permission';
    }
}
