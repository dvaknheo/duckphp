# DuckPhp\Foundation\ZCallTrait

跨相位调用 trait。

## 简介

`ZCallTrait` 提供了一个 `_Z()` 静态方法，用于创建 `DuckPhp\Component\PhaseProxy` 代理对象。通过该代理对象，可以在当前代码中调用其他相位应用中的对象方法。

## 使用场景

在多应用、多子应用的项目中，不同应用拥有各自的可变单例。直接调用其他应用的类会导致获取到当前相位的实例，而不是目标应用的实例。`ZCallTrait` 结合 `PhaseProxy` 可以临时切换到目标相位，执行调用后再切回来。

## 使用方法

在目标应用的服务类中使用 `ZCallTrait`：

```php
namespace OtherApp\Service;

use DuckPhp\Foundation\ZCallTrait;

class UserService
{
    use ZCallTrait;
    
    public function getUser($id)
    {
        // ...
    }
}
```

在另一个应用中跨相位调用：

```php
use OtherApp\Service\UserService;
use OtherApp\System\App as OtherApp;

$user = UserService::_Z(OtherApp::class)->getUser(1);
```

## 参数说明

    public static function _Z($phase = null)

| 参数 | 说明 |
|---|---|
| `$phase` | 目标相位类名，通常是一个 `DuckPhp` 应用类。为 `null` 时使用当前相位。 |

## 返回值

返回一个 `DuckPhp\Component\PhaseProxy` 代理对象，对该对象的所有方法调用都会临时切换到目标相位执行。

## 完整示例

```php
// src/Admin/Service/AccountService.php
namespace Admin\Service;

use DuckPhp\Foundation\ZCallTrait;

class AccountService
{
    use ZCallTrait;

    public function getAdmin($id)
    {
        return AdminModel::getById($id);
    }
}
```

```php
// src/Controller/HomeController.php
namespace Controller;

use Admin\Service\AccountService;
use Admin\System\AdminApp;

class HomeController
{
    public function test()
    {
        $admin = AccountService::_Z(AdminApp::class)->getAdmin(1);
    }
}
```

## 注意事项

1. `_Z()` 返回的是代理对象，不是真实服务对象。如果需要获取真实对象，调用 `self()`：
   ```php
   $service = UserService::_Z()-self();
   ```
2. `ZCallTrait` 只解决跨相位调用问题，不解决类加载或命名空间问题。目标类必须能被自动加载。
3. 多次连续调用可以复用同一个代理对象，避免重复切换相位：
   ```php
   $proxy = UserService::_Z(OtherApp::class);
   $proxy->doA();
   $proxy->doB();
   ```

## 相关链接

- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md)
- [高级主题：相位与子应用](../guide/advanced-phase.md)
