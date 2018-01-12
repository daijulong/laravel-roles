<?php
/**
 * 多角色用户模型trait
 *
 * 如每个用户可有多个角色，则此用户的模型须引入此trait
 */

namespace Daijulong\LaravelRoles\Traits;

use Daijulong\LaravelRoles\Permission;
use Illuminate\Support\Str;

trait MultipleRoleUserModel
{
    use PermissionUserModel;

    /**
     * 定义与角色关联关系
     *
     * @access public
     * @return mixed
     */
    public function role()
    {
        return $this->belongsToMany($this->roleModel(), Str::snake(class_basename($this->roleModel())), 'user_id', 'role_id');
    }

    /**
     * 获取用户所有权限
     *
     * @access public
     * @return array
     */
    public function getPermissions()
    {
        if (is_null($this->permissions)) {
            $userModelAllPermissions = Permission::getModelPermissionOnlyCodes($this->permissionUserModel());
            if (!$this->role) {
                $this->permissions = [];
            } else {
                $permissions = $this->role->pluck('permissions')->collapse()->unique()->toArray();
                $this->permissions = array_intersect($permissions, $userModelAllPermissions);
            }
        }
        return $this->permissions;
    }
}
