# DuckPhp\GlobalUser\User

## 简介

`User` 是用户体系的**基类（默认实现）**：`DuckPhp\DuckPhp` 的 root 组件表里登记的就是 `User::class`，所以 `User::_()` 拿到的永远是「当前生效的用户实现」——没装任何 provider 时就是 `User` 自己（每个能力方法都直接抛 `DuckPhpSystemException`，等于「明确地不可用」，而不是返回空值），装了 `GlobalUser` 或你自己的实现后，就是被注册进 `User::class` 这个键上的那个对象。

它同时是**用户侧的常量集中地**：事件名（`EVENT_ACTION_USER_*` / `EVENT_SERVICE_USER_*`）与异常码/消息（`EXCEPTION_*`）都定义在本类，`GlobalUser`（子类）、`Foundation\Business\BusinessHelper`、`Foundation\Controller\ControllerHelper` 都通过 `User::XXX` 别名引用它们。

`GlobalUser` 是本类的完整实现（会话 + 回调）；本类只给「默认不可用」的桩实现与常量。

## 类信息

- 命名空间：`DuckPhp\GlobalUser`
- 声明：`class User extends DuckPhp\Core\ComponentBase implements UserActionInterface`
- 实现的接口：`UserActionInterface`（登录动作 `UserLoginActionInterface` 由子类 `GlobalUser` 实现）
- 使用的 trait：无（`ComponentBase` 已带 `SingletonExTrait`，`_()` 由它提供，本页不重复列）
- 本类**不声明任何选项**（`$options` 为空数组，继承自 `ComponentBase`），选项全在子类 `GlobalUser` 上
- 常量（共 16 个）：

```php
const EVENT_ACTION_USER_REGISTERING  = 'ACTION_USER_REGISTERING';
const EVENT_ACTION_USER_REGISTERED   = 'ACTION_USER_REGISTERED';
const EVENT_ACTION_USER_LOGINING     = 'ACTION_USER_LOGINING';
const EVENT_ACTION_USER_LOGINED      = 'ACTION_USER_LOGINED';
const EVENT_ACTION_USER_LOGOUTING    = 'ACTION_USER_LOGOUTING';
const EVENT_ACTION_USER_LOGOUTED     = 'ACTION_USER_LOGOUTED';
const EVENT_SERVICE_USER_REGISTERING = 'SERVICE_USER_REGISTERING';
const EVENT_SERVICE_USER_REGISTERED  = 'SERVICE_USER_REGISTERED';
const EVENT_SERVICE_USER_LOGINING    = 'SERVICE_USER_LOGINING';
const EVENT_SERVICE_USER_LOGINED     = 'SERVICE_USER_LOGINED';
const EVENT_SERVICE_USER_LOGOUTING   = 'SERVICE_USER_LOGOUTING';
const EVENT_SERVICE_USER_LOGOUTED    = 'SERVICE_USER_LOGOUTED';
const EXCEPTION_CODE_USER_NEED_LOGIN         = -1;
const EXCEPTION_MESSAGE_USER_NEED_LOGIN      = 'USER_NEED_LOGIN';
const EXCEPTION_CODE_USER_NEED_PERMISSION    = -2;
const EXCEPTION_MESSAGE_USER_NEED_PERMISSION = 'USER_NEED_PERMISSION';
```

## 使用方式

```php
// 1) 直接读「当前用户」——由 root 组件表决定的那个实现
use DuckPhp\GlobalUser\User;

$id = User::_()->id(true);      // 没装 provider 时抛 DuckPhpSystemException("No GlobalUser Provider.", -1)
$ok = User::_()->canAccess();   // 没装 provider 时抛 DuckPhpSystemException("Need Provider", -1)
```

```php
// 2) 换实现：继承 User（或 GlobalUser），在应用里当 ext 装载
namespace MyProject\User;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\GlobalUser\User;

class MyUser extends User
{
    public function init(array $options, ?object $context = null)
    {
        parent::init($options, $context);
        // 把自己注册到 User::class 这个键上，User::_() 才是你
        User::_(PhaseProxy::CreatePhaseProxy($context->getThisPhaseName(), $this));
        return $this;
    }
    public function id(bool $check_login = true)
    {
        $id = $_SESSION['user_id'] ?? 0;
        if ($check_login && !$id) {
            throw new \DuckPhp\Core\DuckPhpSystemException('no user login', -1);
        }
        return $id;
    }
}
// $options['ext'][MyUser::class] = true;
```

## 注意事项

- **`User::_()` 是「键」而不是「类」**：真正生效的对象是被注册进 `User::class` 这个容器键的那个实例（`GlobalUser::init()` 里就是这么做的），所以调用方一律写 `User::_()`，不要写具体的实现类名。
- 本类所有能力方法在**没有 provider 时抛异常**而不是返回空值：`id()`/`name()` 抛 `DuckPhpSystemException("No GlobalUser Provider.", -1 / -2)`，`data()`/`urlFor*()`/`localService()` 抛 `DuckPhpSystemException("Need Provider", -1)`。这是刻意设计（默认不可用要吵出来，别静默放行）。
- 常量放在本类而不是 `GlobalUser` 上，是为了「没装 provider 时也能引用事件名/异常码」。
- 用户侧比管理员侧多两样东西：注册相关（`urlForRegister()`、`register()`——后者在子类 `GlobalUser` 上）与批量取用户名（`batchGetUsernames()`）；相对地**没有** `isSuper()`。
- `User::mergeViewData()` 只填 `__logined_*` 五个字段，不碰页眉页脚——页眉页脚是 `GlobalUser::mergeViewData()` 的活儿。
- 本类只声明接口要求的 `canAccess(?string $url = null, ?string $class = null, ?string $method = null)`（**`$url` 在前**），`UserServiceInterface::canAccess()` 的参数顺序与它一致。

## 方法列表

### 公共方法

    public function id(bool $check_login = true)
取当前用户 ID；本类固定抛 `DuckPhpSystemException("No GlobalUser Provider.", -1)`。

    public function name(bool $check_login = true): string
取当前用户名；本类固定抛 `DuckPhpSystemException("No GlobalUser Provider.", -2)`。

    public function data(bool $check_login = true): array
取当前用户数据数组；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForHome(?string $url_back = null, ?array $ext = null): string
站内首页 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForRegister(?string $url_back = null, ?array $ext = null): string
注册页 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForLogin(?string $url_back = null, ?array $ext = null): string
登录页 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function urlForLogout(?string $url_back = null, ?array $ext = null): string
退出 URL；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

    public function mergeViewData(array $data): array
把登录信息填进视图数据：`__logined_id`（`id(true)`）、`__logined_name`（`name(false)`）、`__logined_data`（`data(false)`）、`__logined_url_home`、`__logined_url_logout`；页眉页脚由子类 `GlobalUser` 追加。

    public function service()
取可跨 Phase 调用的用户服务：`PhaseProxy::CreatePhaseProxy(当前相位, localService())`。

    public function canAccess(?string $url = null, ?string $class = null, ?string $method = null): bool
判断当前用户能否访问：委托 `localService()->canAccess($this->id(), $url, $class, $method)`。

    public function log(string $string, ?string $type = null, array $ext = [])
记一条用户操作日志：委托 `localService()->log($this->id(), $string, $type, $ext)`。

    public function batchGetUsernames(array $ids): array
按 ID 批量取用户名：委托 `localService()->batchGetUsernames($ids)`。

### 受保护方法

    protected function localService()
返回本地（当前 Phase）的 `UserServiceInterface` 实现；本类固定抛 `DuckPhpSystemException("Need Provider", -1)`。

## 相关链接

- [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) — 本类的完整实现（会话 + 回调）
- [DuckPhp\GlobalUser\UserActionInterface](GlobalUser-UserActionInterface.md) — 本类实现的接口
- [DuckPhp\GlobalUser\UserServiceInterface](GlobalUser-UserServiceInterface.md) — `localService()` 返回的服务契约
- [DuckPhp\Component\PhaseProxy](Component-PhaseProxy.md) — `service()` 与注册 `User::_()` 用的跨 Phase 代理
- [DuckPhp\GlobalAdmin\Admin](GlobalAdmin-Admin.md) — 管理员侧同构基类
