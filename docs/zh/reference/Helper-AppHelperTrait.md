# DuckPhp\Helper\AppHelperTrait

应用层 Helper Trait。

## 简介

`DuckPhp\Helper\AppHelperTrait` 提供应用层通用的静态方法，涵盖异常调用、事件管理、运行状态判断、路由操作、视图数据获取、数据库与 Redis 访问、Session/Cookie 操作、系统包装函数、CLI 参数以及核心辅助功能。

## 选项

无。

## 使用方式

### 在类中引入

```php
use DuckPhp\Helper\AppHelperTrait;

class MyHelper
{
    use AppHelperTrait;
}
```

### 常用操作

```php
use DuckPhp\Foundation\System\Helper;

// 关闭所有数据库连接
Helper::DbCloseAll();

// 获取 Redis 对象
$redis = Helper::Redis();

// 获取 CLI 参数
$params = Helper::getCliParameters();

// 获取项目路径
$projectPath = Helper::PathOfProject();
$runtimePath = Helper::PathOfRuntime();

// 注册扩展命令类
Helper::regExtCommandClass(\MyApp\Command\MyCommand::class);
```

## 注意事项

1. 该 Trait 使用 `DuckPhp\Core\SingletonTrait`，引入类后具备单例访问能力。
2. 方法依赖多个核心组件（如 `App`、`Route`、`View`、`DbManager`、`RedisManager`、`SystemWrapper` 等），需确保相关组件已初始化。
3. `system_wrapper_replace` 等系统包装函数主要用于测试或替换全局函数，需谨慎使用。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `CallException($ex)` | 调用异常管理器 |
| `RemoveEvent($event, $callback = null)` | 移除事件监听 |
| `isRunning()` | 判断应用是否运行中 |
| `isInException()` | 判断是否处于异常处理中 |
| `addRouteHook($callback, $position = 'append-outter', $once = true)` | 添加路由钩子 |
| `replaceController($old_class, $new_class)` | 替换控制器类 |
| `getViewData()` | 获取视图数据 |
| `DbCloseAll()` | 关闭所有数据库连接 |
| `SESSION($key = null, $default = null)` | 获取 `$_SESSION` 数据 |
| `FILES($key = null, $default = null)` | 获取 `$_FILES` 数据 |
| `SessionSet($key, $value)` | 设置 Session 值 |
| `SessionUnset($key)` | 删除 Session 值 |
| `SessionGet($key, $default = null)` | 获取 Session 值 |
| `CookieSet($key, $value, $expire = 0)` | 设置 Cookie |
| `CookieGet($key, $default = null)` | 获取 Cookie |
| `system_wrapper_replace(array $funcs)` | 替换系统包装函数 |
| `system_wrapper_get_providers(): array` | 获取系统包装函数提供者 |
| `header($output, bool $replace = true, int $http_response_code = 0)` | 发送 HTTP 头 |
| `setcookie(string $key, string $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)` | 设置 Cookie |
| `exit($code = 0)` | 终止程序 |
| `set_exception_handler(callable $exception_handler)` | 设置异常处理器 |
| `register_shutdown_function(callable $callback, ...$args)` | 注册关闭函数 |
| `session_start(array $options = [])` | 启动 Session |
| `session_id($session_id = null)` | 获取/设置 Session ID |
| `session_destroy()` | 销毁 Session |
| `session_set_save_handler(\SessionHandlerInterface $handler)` | 设置 Session 保存处理器 |
| `mime_content_type($file)` | 获取文件 MIME 类型 |
| `setBeforeGetDbHandler($db_before_get_object_handler)` | 设置获取数据库前的回调 |
| `Redis($tag = 0)` | 获取 Redis 对象 |
| `getRouteMaps()` | 获取路由映射 |
| `assignRoute($key, $value = null)` | 分配路由映射 |
| `assignImportantRoute($key, $value = null)` | 分配高优先级路由映射 |
| `assignRewrite($key, $value = null)` | 分配重写规则 |
| `getRewrites()` | 获取所有重写规则 |
| `getCliParameters()` | 获取 CLI 参数 |
| `FireEvent($event, ...$args)` | 触发事件 |
| `OnEvent($event, $callback)` | 注册事件监听 |
| `PathOfProject()` | 获取项目根目录 |
| `PathOfRuntime()` | 获取运行时目录 |
| `recursiveApps(&$arg, $callback, ?string $app_class = null)` | 递归遍历应用 |
| `getAllAppClass()` | 获取所有应用类 |
| `getAppClassByComponent(string $class)` | 根据组件类获取应用类 |
| `regExtCommandClass(string $class)` | 注册扩展命令类 |

## 相关链接

- [DuckPhp\Foundation\System\Helper](Foundation-System-Helper.md)
