# DuckPhp\GlobalAdmin\Admin

## 简介

`Admin` 是管理员体系的**基类（默认实现）**：`DuckPhp\DuckPhp` 的 root 组件表里登记的就是 `Admin::class`，所以 `Admin::_()` 拿到的永远是「当前生效的管理员实现」——没装任何 provider 时就是 `Admin` 自己（每个能力方法都直接抛 `DuckPhpSystemException`，等于「明确地不可用」，而不是返回空值），装了 `GlobalAdmin` 或你自己的实现后，就是被注册进 `Admin::class` 这个键上的那个对象。

它同时是**管理员侧的常量集中地**：事件名（`EVENT_ACTION_ADMIN_*` / `EVENT_SERVICE_ADMIN_*`）与异常码/消息（`EXCEPTION_*`）都定义在本类，`GlobalAdmin`（子类）、`Foundation\Business\BusinessHelper`、`Foundation\Controller\ControllerHelper` 都通过 `Admin::XXX` 别名引用它们。

`GlobalAdmin` 是本类的完整实现（会话 + 回调）；本类只给「默认不可用」的桩实现与常量。

## 类信息

- 命名空间：`DuckPhp\GlobalAdmin`
- 声明：`class Admin extends DuckPhp\Core\ComponentBase implements AdminActionInterface`
- 实现的接口：`AdminActionInterface`
- 使用的 trait：无（`ComponentBase` 已带 `SingletonExTrait`，`_()` 由它提供，本页不重复列）
- 本类**不声明任何选项**（`$options` 为空数组，继承自 `ComponentBase`），选项全在子类 `GlobalAdmin` 上
- 常量（共 12 个）：

```php
const EVENT_ACTION_ADMIN_LOGINING  = 'ACTION_ADMIN_LOGINING';
const EVENT_ACTION_ADMIN_LOGINED   = 'ACTION_ADMIN_LOGINED';
const EVENT_ACTION_ADMIN_LOGOUTING = 'ACTION_ADMIN_LOGOUTING';
const EVENT_ACTION_ADMIN_LOGOUTED  = 'ACTION_ADMIN_LOGOUTED';
const EVENT_SERVICE_ADMIN_LOGINING = 'SERVICE_ADMIN_LOGINING';
const EVENT_SERVICE_ADMIN_LOGINED  = 'SERVICE_ADMIN_LOGINED';
const EVENT_SERVICE_ADMIN_LOGOUTING = 'SERVICE_ADMIN_LOGOUTING';
const EVENT_SERVICE_ADMIN_LOGOUTED = 'SERVICE_ADMIN_LOGOUTED';
const EXCEPTION_CODE_ADMIN_NEED_LOGIN          = -1;
const EXCEPTION_MESSAGE_ADMIN_NEED_LOGIN       = 'ADMIN_NEED_LOGIN';
const EXCEPTION_CODE_ADMIN_NEED_PERMISSION     = -2;
const EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION  = 'ADMIN_NEED_PERMISSION';
```

## 使用方式

```php
// 1) 直接读「当前管理员」——由 root 组件表决定的那个实现
use DuckPhp\GlobalAdmin\Admin;

$id = Admin::_()->id(true);      // 没装 provider 时抛 DuckPhpSystemException("No GlobalAdmin Provider.", -1)
$ok = Admin::_()->canAccess();   // 没装 provider 时抛 DuckPhpSystemException("Need Provider", -1)
```

```php
// 2) 换实现：继承 Admin（或 GlobalAdmin），在应用里当 ext 装载
namespace MyProject\Admin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\GlobalAdmin\Admin;

class MyAdmin extends Admin
{
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        // 把自己注册到 Admin::class 这个键上，Admin::_() 才是你
        Admin::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
        return $this;
    }
    public function id(bool $check_login = true)
    {
        $id = $_SESSION['admin_id'] ?? 0;
        if ($check_login && !$id) {
            throw new \DuckPhp\Core\DuckPhpSystemException('no admin login', -1);
        }
        return $id;
    }
}
// $options['ext'][MyAdmin::class] = true;
```

## 注意事项

- **`Admin::_()` 是「键」而不是「类」**：真正生效的对象是被注册进 `Admin::class` 这个容器键的那个实例（`GlobalAdmin::init()` 里就是这么做的），所以调用方一律写 `Admin::_()`，不要写具体的实现类名。
- 本类所有能力方法在**没有 provider 时抛异常**而不是返回空值：`id()`/`name()` 抛 `DuckPhpSystemException("No GlobalAdmin Provider.", -1 / -2)`，`data()`/`urlFor*()`/`localService()` 抛 `DuckPhpSystemException("Need Provider", -1)`。这是刻意设计（默认不可用要吵出来，别静默放行）。
- 常量放在本类而不是 `GlobalAdmin` 上，是为了「没装 provider 时也能引用事件名/异常码」（例如工程侧只想 `Admin::EVENT_ACTION_ADMIN_LOGINED` 做监听，不想加载整个 `GlobalAdmin`）。
- 管理员侧**没有注册接口**：只注册（注册管理员）不属于后台体系，所以本类没有 `urlForRegister()` / `register()`，那是用户侧 `User` 的能力。
- 本类只声明接口要求的 `canAccess(?string $url = null, ?string $class = null, ?string $method = null)`（**`$url` 在前**），`AdminServiceInterface::canAccess()` 的参数顺序与它一致。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
取当前管理员 ID；本类固定抛 `DuckPhpSystemException("No GlobalAdmin Provider.", -1)`。

    public function name(bool $check_login = true): string
取当前管理员名；本类固定抛 `DuckPhpSystemException("No GlobalAdmin Provider.", -2)`。

    public function data(bool $check_login = true): array
取当前管理员数据数组；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForHome(): string
后台首页 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForLogin(?string $url_back = null): string
后台登录 URL（`$url_back` 用于登录后回跳）；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForLogout(): string
后台退出 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function mergeViewData(array $data): array
把登录信息填进视图数据：`__logined_id`（`id(true)`）、`__logined_name`（`name(false)`）、`__logined_data`（`data(false)`）、`__logined_url_home`、`__logined_url_logout`；本类的实现只做这五件事，页眉页脚由子类 `GlobalAdmin` 追加。

    public function service()
取可跨 Phase 调用的管理员服务：`PhaseProxy::CreatePhaseProxy(当前相位, localService())`。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前管理员能否访问：委托 `localService()->canAccess($this->id(), $url, $class, $method)`。

    public function log(string $string, ?string $type = null, array $ext = [])
记一条管理员操作日志：委托 `localService()->log($this->id(), $string, $type, $ext)`。

    public function isSuper(): bool
当前管理员是否超管：委托 `localService()->isSuper($this->id())`。

### 受保护方法

    protected function localService()
返回本地（当前 Phase）的 `AdminServiceInterface` 实现；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

## 相关链接

- [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) — 本类的完整实现（会话 + 回调）
- [DuckPhp\GlobalAdmin\AdminActionInterface](GlobalAdmin-AdminActionInterface.md) — 本类实现的接口
- [DuckPhp\GlobalAdmin\AdminServiceInterface](GlobalAdmin-AdminServiceInterface.md) — `localService()` 返回的服务契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — `service()` 与注册 `Admin::_()` 用的跨 Phase 代理
- [DuckPhp\GlobalUser\User](GlobalUser-User.md) — 用户侧同构基类
