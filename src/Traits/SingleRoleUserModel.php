<?php
/**
 * 单角色用户模型trait
 *
 * 如用户仅可有一个角色，则此用户的模型须引入此trait
 */

namespace Daijulong\LaravelRoles\Traits;

use Daijulong\LaravelRoles\Permission;

trait SingleRoleUserModel
{
    use PermissionUserModel;

    /**
     * 定义与角色的belongsTo关联
     *
     * @access public
     * @return mixed
     */
    public function role()
    {
        return $this->belongsTo($this->roleModel());
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
                $this->permissions = array_intersect($this->role->permissions, $userModelAllPermissions);
            }
        }
        return $this->permissions;
    }
}
