# Laravel Roles

适用于 laravel5.5+ 的一套账号、多身份、多角色的权限系统。

## 环境要求

- laravel/laravel: ~5.5

## 定义

- 身份（identity）：用户在不同子系统或场景中有不同的身份，如在前台时身份为普通用户，在后台时身份则为管理员等

- 角色（role）：在确定身份后，对应身份可以包含一个或多个角色，如身份为后台管理员时，可以分为超级管理员、业务管理员等角色进行权限管理

## 适用场景

本包适用于项目要求用同一套账号适用不同场景的情况，如：论坛用户中某一些作为版主，除了可以作为普通用户进行论坛的日常活动，还需要行使作为版主的权力进行论坛的管理，甚至需要进入后台进行业务管理，同时管理员或业务人员等其他人员也需要同时在论坛与普通用户一样地进行日常活动。

## 安装

请先启用 laravel 自带的 Auth 再进行安装。

Via Composer

``` $  composer require daijulong/laravel-roles ```

composer.json

``` "daijulong/laravel-roles": "~1.0" ```

## 配置

生成配置文件：

``` php artisan vendor:publish --provider="Daijulong\LaravelRoles\Providers\PermissionProvider" ```

将在config目录下生成配置文件：permission.php，各配置项在此配置文件中有详细说明。

## 构建身份及角色

假设：在一个项目中，要求前台用户和后台管理员使用同一套账号进行登录，作为前台用户时，每用户仅对应一个"用户组"，而作为后台管理员时，可以拥有多个角色进行不同的业务管理。

### 普通用户增加单角色机制

```
class User extends Authenticatable
{
    use Notifiable, SingleRoleUserModel;
    
    // ...
    
    //定义普通用户角色（用户组）模型
    protected function roleModel()
    {
        return UserRole::class;
    }

    //定义与管理员模型关系，如果还有其他身份，也以此类推进行定义
    public function admin ()
    {
        return $this->hasOne(Admin::class);
    }   
}
```

> 对于单角色的用户模型，应 ``` use Daijulong\LaravelRoles\Traits\SingleRoleUserModel ``` 并实现其定义角色模型的抽象方法 ```roleModel()``` ，如果是新建的一个单角色用户模型，还应声明其与 User 的关系

### 创建用户角色表及模型 ```UserRole```

#### 用户表增加字段

```
$table->unsignedInteger('role_id')->default(0)->comment('角色ID');
```

#### 用户角色表migration

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserRolesTable extends Migration
{
    public function up()
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('角色名称');
            $table->string('description')->nullable()->comment('角色描述');
            $table->text('permissions')->nullable()->comment('角色权限');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE `" . config('database.connections.mysql.prefix') . "user_roles` comment '普通用户角色'");
    }

    public function down()
    {
        Schema::dropIfExists('user_roles');
    }
}
```

> 别忘了 ```php artisan migrate```

#### 用户角色模型

```
<?php

namespace App\Models;

use Daijulong\LaravelRoles\Traits\PermissionRoleModel;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
   use PermissionRoleModel;

   protected $fillable = [
       'name',
       'description',
       'permissions',
   ];
}
```

> 角色模型须 ```use Daijulong\LaravelRoles\Traits\PermissionRoleModel``` 

#### 后台对角色、权限、授权管理

``` UserRole ``` 中 ``` permissions ``` 字段为若干个权限码连接而成，连接符默认为"|"。权限码参考配置文件：```permission.php```

创建角色、角色授权等不再详述。

### 创建管理员及其多角色权限机制

#### 创建表，各 migration 如下

管理员表 admins

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminsTable extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->comment('用户ID');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
}
```

管理员角色表 admin_roles

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRolesTable extends Migration
{
    public function up()
    {
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('角色名称');
            $table->string('description')->nullable()->comment('角色描述');
            $table->text('permissions')->nullable()->comment('角色权限');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_roles');
    }
}
```

管理员-角色对应关系 admin_role

```
<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRoleTable extends Migration
{
    public function up()
    {
        Schema::create('admin_role', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index()->comment('管理员用户ID');
            $table->unsignedInteger('role_id')->comment('角色ID');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_role');
    }
}
```

#### 创建模型

Admin

```
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Daijulong\LaravelRoles\Traits\MultipleRoleUserModel;

class Admin extends Model
{

    use MultipleRoleUserModel;

    protected $fillable = ['user_id'];

    protected function roleModel()
    {
        return AdminRole::class;
    }

    protected function permissionUserModel()
    {
        return 'admin';
    }

    protected function relationForeignUserIdField()
    {
        return 'user_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

> 对于多角色的用户模型，应 ```use Daijulong\LaravelRoles\Traits\MultipleRoleUserModel``` 并实现其抽象方法，声明与 User 的关系

AdminRole

```
<?php

namespace App\Models;

use Daijulong\LaravelRoles\Traits\PermissionRoleModel;
use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    use PermissionRoleModel;

    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];
}
```

> 角色模型须 ```use Daijulong\LaravelRoles\Traits\PermissionRoleModel``` 

## 加载 Permission 中间件 Middleware

针对前台用户和后台管理员有不同的需求：

- 前台用户：登录则加载 permission
- 后台管理员：登录并具有管理员身份，才加载 permission

创建两个不同的中间件，并分别加入后适当位置（具体由项目路由部分决定）

UserPermission：

```
<?php

namespace App\Http\Middleware;

use Closure;
use Daijulong\LaravelRoles\Permission;
use Illuminate\Support\Facades\Auth;

class UserPermission
{
    public function handle ($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->enabled) {
            Permission::loadConfigAndSetUser(Auth::user());
        }
        return $next($request);
    }
}
```

AdminPermission：

```
<?php

namespace App\Http\Middleware;

use Closure;
use Daijulong\LaravelRoles\Permission;
use Illuminate\Support\Facades\Auth;

class AdminPermission
{
    public function handle($request, Closure $next)
    {
        if (!Auth::user()->admin) {
            return redirect(route('index'));
        }
        Permission::loadConfigAndSetUser(Auth::user()->admin);
        return $next($request);
    }
}
```

> AdminPermission 中间件应放在 auth 中间件之后，先保证已登录。

> 创建的 Permission 中间件同时还决定了进入某一个程序主体时用户的身份，程序主体中将默认使用此身份进行权限相关操作，但仍提供了使用其他身份进行检查的机制，具体见后续"使用"小节的说明。

> 可参照上述内容建立更多的身份及角色。后台各身份、角色、权限的管理在此不作赘述。

## 使用

### 取得用户实例

``` Permission::user() ```

可以以此来判定用户是否具有某身份，如判断是否为管理员：

```
if (Auth::user()->admin) {
    // ...
}
```

### 检查是否符合某（些）权限要求

``` Permission::can($permission [, $relation = 'OR']) ```

检查单个权限：``` Permission::can('article.add') ```
检查多个权限：``` Permission::can('article.add|article.edit', 'OR') ``` ，多个权限以"|"分隔，并可通过第二个参数使用"OR"或"AND"进行处理，默认"OR"

> 一般来说，在进入程序主体时，已经通过中间件确定了当前用户是以哪种身份进入的，默认为检查当前身份对应的权限码，如果需要检查另一种身份的相关权限时，则需要在权限码前加上身份码。例如在前台页面中，都是以前台用户身份进行权限操作，此时要检查是否是具备删除评论权限的管理员时，则可以如此进行检查：``` Permission::can('admin:comment.delete') ```

### 检查是否不符合某（些）权限要求

``` Permission::cannot($permission [, $relation = 'OR']) ```

与 ``` Permission::can() ``` 相反

### 检查数据是否为当前用户所有

``` Permission::isOwner($data [,$userIdField = 'user_id']) ```

```$data``` 可以是 ```Model|Collection|array|integer``` 等，将与当前用户的ID进行对比以判定其是否为当前用户所有。如果 ```$data``` 是 ```Model|Collection|array```时，可以通过第二个参数指定其比对的索引或成员属性名

### 检查数据是否不为当前用户所有

``` Permission::isNotOwner($data [, $userIdField = 'user_id']) ```

与 ``` Permission::isOwner() ``` 相反

# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.