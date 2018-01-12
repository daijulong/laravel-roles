<?php
/**
 * 用户模型trait
 */

namespace Daijulong\LaravelRoles\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

trait PermissionUserModel
{
    /**
     * 用户所有权限
     * @var array
     */
    protected $permissions = null;

    /**
     * 检查用户是否有某权限
     *
     * @access public
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        $model = $this->permissionUserModel();
        if (strpos($permission, ':')) {
            list($model, $permission) = explode(':', $permission, 2);
        }

        if ($model == $this->permissionUserModel()) {
            return in_array($permission, $this->getPermissions());
        }

        $user = null;
        try {
            if ($this->{$model}) {
                $user = $this->{$model};
            } elseif ($this->user->{$model}) {
                $user = $this->user->{$model};
            }
        } catch (\Exception $exception) {

        }

        if ($user) {
            return $user->hasPermission($permission);
        }
        return false;
    }

    /**
     * 定义与角色关联关系
     *
     * @abstract
     * @access public
     * @return mixed
     */
    abstract public function role();

    /**
     * 获取用户所有权限
     *
     * @abstract
     * @access public
     * @return mixed
     */
    abstract public function getPermissions();

    /**
     * 定义角色模型
     *
     * @access protected
     * @return string 对应角色的模型类名
     */
    abstract protected function roleModel();

    /**
     * 用户权限模型
     *
     * @access protected
     * @return string permissions配置中的键名
     */
    protected function permissionUserModel()
    {
        return 'user';
    }

    /**
     * 关联外部模型的用户ID字段名
     *
     * 一般用户主表用"id"，而扩展出的用户类型（如：admin）则需要修改字段名称（如：user_id）以使其与实际的用户ID对应
     *
     * @access protected
     * @return string
     */
    protected function relationForeignUserIdField()
    {
        return 'id';
    }

    /**
     * 检查是否有权限
     *
     * @access public
     * @param string $permission 权限码，多个以"|"分隔
     * @param string $relation
     * @return bool
     */
    public function auth($permission, $relation = 'OR')
    {
        if ($permission == '') {
            return false;
        }
        $relationAnd = strtolower($relation) == 'and';
        $checkPermissions = explode('|', $permission);
        foreach ($checkPermissions as $_permission) {
            if ($this->hasPermission($_permission) == true) {
                if ($relationAnd == false) {
                    return true;
                }
            } else {
                if ($relationAnd == true) {
                    return false;
                }
            }
        }
        return $relationAnd;
    }

    /**
     * 检查是否无权限
     *
     * @access public
     * @param string $permission 权限码，多个以"|"分隔
     * @param string $relation
     * @return bool
     */
    public function unauth($permission, $relation = 'OR')
    {
        return !$this->auth($permission, $relation);
    }

    /**
     * 判断数据是否为用户所有
     *
     * @access public
     * @param  Model|Collection|array|integer $data
     * @param string $userIdField
     * @return bool
     */
    public function isOwner($data, $userIdField = 'user_id')
    {
        $userId = $this->{$this->relationForeignUserIdField()};
        if (!$userId) {
            return false;
        }
        if (is_numeric($data)) {
            return $userId == $data;
        }
        if ($data instanceof Model) {
            return $userId == $data->{$userIdField};
        }
        if (is_array($data) && isset($data[$userIdField])) {
            return $userId == $data[$userIdField];
        }
        if ($data instanceof Collection) {
            return $data->offsetExists($userIdField) ? $userId == $data->get($userIdField) : false;
        }
        return false;
    }

    /**
     * 判断数据是否不为用户所有
     *
     * @access public
     * @param  Model|Collection|array|integer $data
     * @param string $userIdField
     * @return bool
     */
    public function isNotOwner($data, $userIdField = 'user_id')
    {
        return !$this->isOwner($data, $userIdField);
    }
}
