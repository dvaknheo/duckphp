# DuckPhp\Foundation\Business\Helper

业务层 Helper 类。

## 简介

`DuckPhp\Foundation\Business\Helper` 聚合了 `DuckPhp\Helper\BusinessHelperTrait`，为业务层提供统一的静态方法入口，用于访问配置、设置、缓存、事件、路径以及全局用户与管理员服务等。

## 选项

无。

## 使用方式

### 静态调用

```php
use DuckPhp\Foundation\Business\Helper;

// 读取配置
$config = Helper::Config('database');

// 读取设置
$setting = Helper::Setting('site_name');

// 获取缓存对象
$cache = Helper::Cache();

// 触发自定义事件
Helper::FireEvent('user.created', $user);

// 获取用户/管理员服务
$userService = Helper::UserService();
$adminService = Helper::AdminService();
```

### 在 Business 类中使用

业务类通常使用 `DuckPhp\Foundation\BusinessTrait`，它组合了单例和快速调用能力，也可以直接通过 `Helper` 类访问这些能力。

## 注意事项

1. 该类没有任何自有方法，所有方法均来自 `DuckPhp\Helper\BusinessHelperTrait`。
2. 部分方法（如 `Setting`、`Config`、`XpCall`）依赖 `DuckPhp\Core\App` 及相关组件，需确保应用已初始化。

## 方法列表

### 公共方法

| 方法 | 说明 |
|---|---|
| `Setting($key = null, $default = null)` | 读取应用设置 |
| `Config($file_basename, $key = null, $default = null)` | 读取配置文件 |
| `XpCall($callback, ...$args)` | 执行回调并捕获异常 |
| `BusinessThrowOn(bool $flag, string $message, int $code = 0, $exception_class = null)` | 业务异常断言 |
| `Cache($object = null)` | 获取或设置缓存对象 |
| `PathOfProject()` | 获取项目根目录 |
| `PathOfRuntime()` | 获取运行时目录 |
| `FireEvent($event, ...$args)` | 触发事件 |
| `OnEvent($event, $callback)` | 注册事件监听 |
| `AdminService()` | 获取全局管理员服务 |
| `UserService()` | 获取全局用户服务 |

## 相关链接

- [DuckPhp\Helper\BusinessHelperTrait](Helper-BusinessHelperTrait.md)
