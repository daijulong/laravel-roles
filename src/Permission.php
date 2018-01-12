<?php

namespace Daijulong\LaravelRoles;

/**
 * 权限管理基础数据操作
 */
class Permission
{

    /**
     * 配置权限内容
     */
    private static $permissions = null;

    /**
     * 用户实例
     */
    private static $user = null;

    /**
     * 加载配置并设置用户
     *
     * @static
     * @access public
     * @param Model $user 一般为一个用户实例
     */
    public static function loadConfigAndSetUser($user)
    {
        self::loadPermissions();
        self::$user = $user;
    }

    /**
     * 检查并加载配置内容
     *
     * @static
     * @access private
     */
    private static function loadPermissions()
    {
        if (is_null(self::$permissions)) {
            self::$permissions = config('permission', []);
        }
    }

    /**
     * 从配置中取得用户模型的权限
     *
     * @static
     * @access public
     * @param  string $model
     * @return array
     */
    public static function getModelPermissions($model)
    {
        return isset(self::$permissions[$model]) ? self::$permissions[$model] : [];
    }

    /**
     * 获取用户实例
     *
     * @static
     * @access public
     * @return null|Model|Collection
     */
    public static function user()
    {
        return self::$user;
    }

    /**
     * 从配置中取得用户模型的权限（仅获取权限码）
     *
     * @static
     * @access public
     * @param string $model
     * @return array
     */
    public static function getModelPermissionOnlyCodes($model)
    {
        $permissions = self::getModelPermissions($model);
        if (!is_array($permissions) || empty($permissions)) {
            return [];
        }
        $permissionCodes = [];
        foreach ($permissions as $group_key => $group) {
            if (!isset($group['permissions']) || !is_array($group['permissions'])) {
                continue;
            }
            foreach ($group['permissions'] as $_permission_code => $_permission) {
                $permissionCodes[] = $group_key . '.' . $_permission_code;
            }
        }
        return $permissionCodes;
    }

    /**
     * 检查是否有权限
     *
     * @static
     * @access public
     * @param $permission
     * @param string $relation
     * @return mixed
     * @author daijulong <daijulong@gmail.com>
     */
    public static function can($permission, $relation = 'OR')
    {
        return self::$user ? self::$user->auth($permission, $relation) : false;
    }

    /**
     * 检查是否无权限
     *
     * @static
     * @access public
     * @param $permission
     * @param string $relation
     * @return mixed
     */
    public static function cannot($permission, $relation = 'OR')
    {
        return self::$user ? self::$user->unauth($permission, $relation) : true;
    }

    /**
     * 判断数据是否为用户所有
     *
     * @static
     * @access public
     * @param  Model|Collection|array|integer $data
     * @param string $userIdField
     * @return bool
     */
    public static function isOwner($data, $userIdField = 'user_id')
    {
        return self::$user ? self::$user->isOwner($data, $userIdField) : false;
    }

    /**
     * 判断数据是否不为用户所有
     *
     * @static
     * @access public
     * @param  Model|Collection|array|integer $data
     * @param string $userIdField
     * @return bool
     */
    public static function isNotOwner($data, $userIdField = 'user_id')
    {
        return !self::isOwner($data, $userIdField);
    }
}
