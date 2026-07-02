# DuckPhp\Foundation\System\Helper

系统层 Helper 类。

## 简介

`DuckPhp\Foundation\System\Helper` 聚合了 `DuckPhp\Helper\AppHelperTrait`，为系统层提供统一的静态方法入口，用于访问路由、视图、数据库、Redis、Session、Cookie、事件、系统包装函数以及核心辅助功能等。

## 选项

无。

## 使用方式

### 静态调用

```php
use DuckPhp\Foundation\System\Helper;

// 注册扩展命令类
Helper::regExtCommandClass(\MyApp\System\MyCommand::class);

// 获取所有应用类
$apps = Helper::getAllAppClass();

// 关闭所有数据库连接
Helper::DbCloseAll();

// 获取 Redis 对象
$redis = Helper::Redis();
```

## 注意事项

1. 该类没有任何自有方法，所有方法均来自 `DuckPhp\Helper\AppHelperTrait`。
2. 部分方法（如 `system_wrapper_replace`）用于测试或替换系统函数，需谨慎使用。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `CallException($ex)` | 调用异常管理器 |
| `RemoveEvent($event, $callback = null)` | 移除事件监听 |
| `isRunning()` | 应用是否运行中 |
| `isInException()` | 是否处于异常处理中 |
| `addRouteHook($callback, $position = 'append-outter', $once = true)` | 添加路由钩子 |
| `replaceController($old_class, $new_class)` | 替换控制器类 |
| `getViewData()` | 获取视图数据 |
| `DbCloseAll()` | 关闭所有数据库连接 |
| `SESSION($key = null, $default = null)` | 获取 SESSION |
| `FILES($key = null, $default = null)` | 获取 FILES |
| `SessionSet($key, $value)` | 设置 Session |
| `SessionUnset($key)` | 删除 Session |
| `SessionGet($key, $default = null)` | 获取 Session |
| `CookieSet($key, $value, $expire = 0)` | 设置 Cookie |
| `CookieGet($key, $default = null)` | 获取 Cookie |
| `system_wrapper_replace(array $funcs)` | 替换系统包装函数 |
| `system_wrapper_get_providers(): array` | 获取系统包装提供者 |
| `header($output, bool $replace = true, int $http_response_code = 0)` | 发送 HTTP 头 |
| `setcookie(...)` | 设置 Cookie |
| `exit($code = 0)` | 终止程序 |
| `set_exception_handler(callable $exception_handler)` | 设置异常处理器 |
| `register_shutdown_function(callable $callback, ...$args)` | 注册关闭函数 |
| `session_start(array $options = [])` | 启动 Session |
| `session_id($session_id = null)` | 获取/设置 Session ID |
| `session_destroy()` | 销毁 Session |
| `session_set_save_handler(\SessionHandlerInterface $handler)` | 设置 Session 保存处理器 |
| `mime_content_type($file)` | 获取文件 MIME 类型 |
| `setBeforeGetDbHandler($db_before_get_object_handler)` | 设置获取数据库前回调 |
| `Redis($tag = 0)` | 获取 Redis 对象 |
| `getRouteMaps()` | 获取路由映射 |
| `assignRoute($key, $value = null)` | 分配路由 |
| `assignImportantRoute($key, $value = null)` | 分配高优先级路由 |
| `assignRewrite($key, $value = null)` | 分配重写规则 |
| `getRewrites()` | 获取所有重写规则 |
| `getCliParameters()` | 获取 CLI 参数 |
| `FireEvent($event, ...$args)` | 触发事件 |
| `OnEvent($event, $callback)` | 注册事件监听 |
| `PathOfProject()` | 获取项目根目录 |
| `PathOfRuntime()` | 获取运行时目录 |
| `recursiveApps(&$arg, $callback, ?string $app_class = null)` | 递归遍历应用 |
| `getAllAppClass()` | 获取所有应用类 |
| `getAppClassByComponent(string $class)` | 根据组件获取应用类 |
| `regExtCommandClass(string $class)` | 注册扩展命令类 |

## 相关链接

- [DuckPhp\Helper\AppHelperTrait](Helper-AppHelperTrait.md)
