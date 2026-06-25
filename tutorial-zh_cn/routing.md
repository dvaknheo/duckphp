# 路由系统

DuckPHP 的路由由 `src/Core/Route.php` 实现，遵循**约定优于配置**的设计。

## 默认路由规则

URL 路径格式：

```
/{Controller类名}/{action方法名}
```

映射到控制器类的方法：

```
/Main/index        → 命名空间\Controller\MainController::action_index()
/user/profile      → 命名空间\Controller\UserController::action_profile()
/admin/user/list   → 命名空间\Controller\Admin\UserController::action_list()
```

### 关键约定

| 约定 | 默认值 | 说明 |
|---|---|---|
| 控制器类后缀 | `Controller` | `FooController` |
| 方法前缀 | `action_` | `action_index()` |
| 欢迎页类 | `Main` | `/` 或 `/index` 路由到 |
| 欢迎页方法 | `index` | 控制器默认方法 |
| 命名空间 | 自动检测 | 项目中 `Controller` 段 |
| 类名大小写调整 | 空 | 默认不会把 URL 中的 `user` 转成 `User`，需要配置 `controller_class_adjust` |

### URL 中的控制器类名大小写

默认情况下，`controller_class_adjust` 为空，URL 段会原样拼接到类名中。例如：

```
/user/profile → 命名空间\Controller\userController::action_profile()  # 找不到类，404
```

若控制器类使用大驼峰命名（如 `UserController`），请在 `App::$options` 中开启自动首字母大写：

```php
public $options = [
    'controller_class_adjust' => 'uc_class',
];
```

开启后：

```
/user/profile → 命名空间\Controller\UserController::action_profile()
```

### 访问非欢迎控制器的默认方法

默认欢迎类是 `Main`。如果直接写 `__url('user')`，会被解析为 `MainController::action_user()`，而不是 `UserController::action_index()`。访问子控制器默认页应写完整路径：

```php
__url('user/index');   // UserController::action_index()
__url('user/add');     // UserController::action_add()
```

### POST 方法特殊处理

如果请求是 POST，且存在 `action_do_{方法名}()` 方法，则优先调用它：

```
POST /user/login
→ UserController::action_do_login()   # 优先
→ UserController::action_login()      # 回退
```

这个特性通过 `controller_prefix_post = 'do_'` 选项控制。

## URL 生成

在控制器或视图中生成 URL：

```php
__url('')            // 当前控制器基 URL
__url('user/login')  // /user/login
__url('?page=2')     // 当前路径 + 查询参数
__url('#section')    // 当前路径 + 锚点
__url('/absolute/path') // 绝对路径

// 资源 URL（带 controller_resource_prefix 时）
__res('css/style.css')  // /res/css/style.css 或 CDN 地址
```

## 高级话题：路由钩子系统

路由过程通过钩子（Hook）串联。钩子执行顺序：

```
prepend-outter → prepend-inner → 默认路由 → append-inner → append-outter
```

内置钩子（按注册位置）：

| 钩子 | 位置 | 作用 |
|---|---|---|
| `RouteHookCheckStatus` | prepend-outter | 检查维护模式/安装状态 |
| `RouteHookRewrite` | prepend-outter | URL 重写 |
| `RouteHookRouteMap` (important) | prepend-inner | 优先路由映射匹配 |
| `RouteHookRouteMap` (normal) | append-outter | 普通路由映射匹配 |
| `RouteHookResource` | append-outter | 静态资源处理 |

### 添加自定义钩子

```php
use DuckPhp\Core\Route;

// 在 App::onInit() 或任意初始化阶段
Route::_()->addRouteHook(function ($path_info) {
    if ($path_info === '/special') {
        echo "Special route handled!";
        return true; // 返回 true 表示已处理，后续钩子不再执行
    }
    return false;
}, 'prepend-inner');
```

## URL 重写（Rewrite）

通过 `rewrite_map` 将公开 URL 映射到内部 URL：

```php
$options = [
    'rewrite_map' => [
        'article/123' => 'blog/show?id=123',
        '~^/u/(\d+)$' => '/user/profile?id=$1',  // 正则模式，以 ~ 开头
    ],
];
```

- 普通模式：精确路径匹配
- 正则模式：以 `~` 开头，使用正则匹配

## 路由映射（Route Map）

通过 `route_map` 或 `route_map_important` 将 URL 直接绑定到可调用体：

```php
$options = [
    'route_map_important' => [
        '/' => function () { echo "Home"; },
        '/hello' => '命名空间\Controller\MainController@action_say',  // @ 分隔类和方法
    ],
    'route_map' => [
        '/blog/{id:\d+}' => function ($params) {
            extract($params);
            echo "Blog post #$id";
        },
        '/page/*' => function ($params) {
            // * 匹配剩余路径，$params 为路径段数组
        },
        '@^/api/(\w+)$' => '~Controller\ApiController@action_$1',  // @ 开头表示正则编译
    ],
];
```

### 路由映射匹配规则

| 模式 | 说明 | 示例 |
|---|---|---|
| `/path` | 精确匹配 | `/user/login` |
| `/path/*` | 前缀匹配，剩余路径作为参数 | `/blog/2024/01` |
| `@^{regex}$` | 正则匹配（以 `@` 开头） | `@^/api/(\w+)$` |
| `~Controller\Xxx` | `~` 替换为控制器的命名空间前缀 | `~Controller\MainController` |


## 路由参数

路由映射匹配时，可以通过 `Route::Parameter()` 获取参数：

```php
// 路由映射：'/user/{id:\d+}' => 'Controller\UserController@action_show'
class UserController
{
    public function action_show()
    {
        $id = Route::Parameter('id');  // 获取 id 参数
        // 或
        $id = Helper::Parameter('id');
        // 或
        $id = Helper::GET('id');
    }
}
```
