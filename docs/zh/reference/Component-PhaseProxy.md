# DuckPhp\Component\PhaseProxy

相位代理组件。

## 简介

`PhaseProxy` 用于在调用某个对象的方法前临时切换到指定相位（应用上下文），调用完成后自动恢复原始相位。它常与 `ZCallTrait` 配合使用，解决跨应用或跨相位调用单例对象的问题。

## 使用场景

在多应用、多子应用或相位隔离的场景下，一个应用中的对象可能需要访问另一个相位中的对象。直接调用会导致单例对象指向错误相位。`PhaseProxy` 通过切换相位、执行调用、恢复相位的方式解决这一问题。

## 创建方式

### 直接实例化

```php
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;

$proxy = new PhaseProxy(\OtherApp\System\App::class, \OtherApp\Service\UserService::class);
```

### 通过工厂方法

```php
$proxy = PhaseProxy::CreatePhaseProxy(\OtherApp\System\App::class, \OtherApp\Service\UserService::class);
```

如果第一个参数为 `null`，则使用当前相位：

```php
$proxy = PhaseProxy::CreatePhaseProxy(null, \OtherApp\Service\UserService::class);
```

## 与 ZCallTrait 配合使用

`ZCallTrait` 提供 `_Z()` 方法，简化跨相位调用：

```php
namespace OtherApp\Service;

use DuckPhp\Component\ZCallTrait;

class UserService
{
    use ZCallTrait;
    
    public function getUser($id)
    {
        // ...
    }
}

// 在另一个相位中调用
$username = UserService::_Z(\OtherApp\System\App::class)->getUser(1);
```

## 使用方式

```php
use DuckPhp\Component\PhaseProxy;
use OtherApp\Service\UserService;
use OtherApp\System\App as OtherApp;

$proxy = PhaseProxy::CreatePhaseProxy(OtherApp::class, UserService::class);

// 方法调用时，PhaseProxy 会临时切换到 OtherApp 相位，调用完成后恢复
$user = $proxy->getUser(1);
```

### 获取原始对象

```php
$service = $proxy->self();  // 返回 UserService::_()
```

## 注意事项

1. `PhaseProxy` 通过 `App::Phase()` 切换上下文，只对使用可变单例的组件生效。
2. 被代理的对象通常是支持 `_()` 的组件或服务类。
3. 调用完成后会自动恢复原始相位，避免影响后续代码。
4. 代理对象本身不持有实际对象，调用时才通过 `getObjectForPhaseProxy()` 获取。

## 方法列表

### 公共方法

    public function __construct($container_class, $overriding)
创建相位代理。`container_class` 为目标相位，`overriding` 为被代理对象或类名

    public static function CreatePhaseProxy($container_class, $overriding)
工厂方法。`container_class` 为 `null` 时使用当前相位

    public function __call($method, $args)
代理方法调用。切换相位 → 执行方法 → 恢复相位

    public function self()
获取被代理的原始对象

### 受保护方法

    protected function getObjectForPhaseProxy()
获取被代理对象实例。如果是类名，则通过 `::_()` 获取

## 相关链接

- [DuckPhp\Component\ZCallTrait](Component-ZCallTrait.md)
- [DuckPhp\Core\App](Core-App.md)
