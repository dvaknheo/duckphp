# DuckPhp\Helper\BusinessHelperTrait

业务层 Helper Trait。

## 简介

`DuckPhp\Helper\BusinessHelperTrait` 提供业务层常用的静态方法，包括应用设置、配置读取、异常捕获、业务异常断言、缓存访问、事件触发、路径获取以及全局管理员/用户服务访问。

## 选项

无。

## 使用方式

### 在类中引入

```php
use DuckPhp\Helper\BusinessHelperTrait;

class MyBusinessHelper
{
    use BusinessHelperTrait;
}
```

### 常用操作

```php
use DuckPhp\Foundation\Business\Helper;

// 读取设置
$siteName = Helper::Setting('site_name');

// 读取配置
$config = Helper::Config('database');

// 业务异常断言
Helper::BusinessThrowOn($user === null, '用户不存在', 404);

// 获取缓存
$cache = Helper::Cache();

// 触发事件
Helper::FireEvent('order.paid', $order);

// 获取服务
$userService = Helper::UserService();
$adminService = Helper::AdminService();
```

## 注意事项

1. 该 Trait 使用 `DuckPhp\Core\SingletonTrait`，引入类后具备单例访问能力。
2. 部分方法依赖 `DuckPhp\Core\App`、`DuckPhp\Component\Configer`、`DuckPhp\Component\Cache` 等组件，需确保应用已初始化。

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

- [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md)
