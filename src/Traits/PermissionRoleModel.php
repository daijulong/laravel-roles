<?php
/**
 * 用户角色模型trait
 *
 * 用户角色的模型须引入此trait
 */

namespace Daijulong\LaravelRoles\Traits;

trait PermissionRoleModel
{
    /**
     * 权限码分隔符
     */
    protected $permissionsSeparator = '|';

    protected function getPermissionsAttribute($attribute)
    {
        return explode($this->permissionsSeparator, $attribute);
    }

    protected function setPermissionsAttribute(array $permissions)
    {
        return $this->attributes['permissions'] = implode($this->permissionsSeparator, $permissions);
    }
}
