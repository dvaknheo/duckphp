# 2-12 异常与错误处理

> 解决什么问题：异常怎么分层、条件抛怎么写、错误页怎么配、异常报告器怎么接。
> 前置：[第 2-5 章 控制器](controllers.md)、[第 2-10 章 表单与数据验证](validator.md)。预计 20 分钟。
> 示例：`demo/src/Controller/ExceptionAction.php`（报告器骨架）、`demo/view/_sys/error_404.php` / `error_500.php` / `error_maintain.php`（错误视图）。

## 最小示例

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use Exception;

class ProjectException extends Exception {}
class BusinessException extends ProjectException {}
class ControllerException extends ProjectException {}
```

```php
// Business 层
Helper::BusinessThrowOn($balance < $amount, '余额不足', 2001);
// 等价于：BusinessException::ThrowOn(...)（在自定义异常类上加静态守卫的写法见第 4-13 章 §16）
```

异常沿框架的异常管理器（[DuckPhp\Core\ExceptionManager](../reference/Core-ExceptionManager.md)）走一圈：命中已注册处理器 → 执行；否则交给默认出口 `_OnDefaultException()` 出 500 页或调试详情。

## 机制说明

### 异常分层：一条铁律

> ⚠️ **[`DuckPhpSystemException`](../reference/Core-DuckPhpSystemException.md) 只表示「框架自己出了问题」**（相位重名、直接 init 基类、缺 provider 等），工程的业务/权限/登录异常请**直接 `extends \Exception`**。想要守卫式抛法不必继承它——用 `Helper::ThrowOn($flag, 'msg', $code, MyException::class)`（在异常类上 `use Ext\ThrowOnTrait` 是老写法，见[第 4-13 章](ext-classes.md) §16）。详见 Core-DuckPhpSystemException。

```
\Exception                              ← PHP 内置
  ├─ DuckPhp\Core\DuckPhpSystemException   ← 框架内部专用（工程不要继承）
  │     └─ DuckPhp\Core\ExitException      ← exit 语义（__EXIT_EXCEPTION）
  └─ MyProj\System\ProjectException        ← 工程异常基类（直接继承 \Exception）
        ├─ MyProj\System\BusinessException
        └─ MyProj\System\ControllerException
```

框架自己的**登录与权限**场景不走异常类：错误码/消息是 [`User`](../reference/GlobalUser-User.md) / [`Admin`](../reference/GlobalAdmin-Admin.md) 上的常量（`User::EXCEPTION_CODE_USER_NEED_LOGIN`、`Admin::EXCEPTION_MESSAGE_ADMIN_NEED_PERMISSION` 等），处理方式是「未登录 → `throwLoginOn()`」「没权限 → 控制器的 `onNeedPermission()`」，两者都是**自定义回调 / 302 到登录页 / Ajax 出 JSON** 三选一然后 `exit()`（细节见[第 2-19 章](../guide/user.md)第 5 节与[第 2-20 章](../guide/admin.md)第 3–4 节）。所以工程侧不需要、也不应该为登录/权限再造异常类。

树上那个 [`ExitException`](../reference/Core-ExitException.md) 属于**框架内部**：打开 `use_exit_exception` 选项后，[`SystemWrapper::exit()`](../reference/Core-SystemWrapper.md) 会抛它（`src/Core/SystemWrapper.php` 167-168 行），[`ExceptionManager`](../reference/Core-ExceptionManager.md) 把它原样放行（`src/Core/ExceptionManager.php` 90 行）——业务代码不要抛它。

### 条件抛：ThrowOn 家族

**推荐用 Helper 侧的条件抛**（[CoreHelper](../reference/Core-CoreHelper.md)）——`$flag` 为真就抛，异常类由应用选项集中决定：

| 写法 | 抛出的异常类由谁定 |
| --- | --- |
| `Helper::ThrowOn($flag, 'msg', $code)` | 看是哪一层的 Helper：System 层 = Project 版；Business / Controller 层各自用本层选项 |
| `Helper::ProjectThrowOn($flag, 'msg', $code)` | 选项 `exception_for_project`（缺省 `\Exception`） |
| `Helper::BusinessThrowOn($flag, 'msg', $code)` | 选项 `exception_for_business`（缺省 `\Exception`） |
| `Helper::ControllerThrowOn($flag, 'msg', $code)` | 选项 `exception_for_controller`（缺省 `\Exception`） |
| `Helper::ThrowOn($flag, 'msg', $code, MyException::class)` | 第 4 个参数直接点名异常类 |

> ⚠️ **不再推荐**「在异常类上 `use DuckPhp\Ext\ThrowOnTrait`，然后 `MyException::ThrowOn(...)`」这种写法（[第 4-13 章](ext-classes.md) §16 讲了为什么）：条件抛一律走 Helper——异常类由选项集中决定，测试里也能整族换掉。

`demo/src/Controller/ExceptionAction.php` 是骨架：

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\Controller\ExceptionReporterTrait;
use DuckPhp\Foundation\SingletonTrait;

class ExceptionAction
{
    use SingletonTrait;            // 提供 _()：OnException() 靠它拿实例
    use ExceptionReporterTrait;

    public function onBusinessException($ex)
    {
        // 记日志 / 出 JSON / 302 …
    }
    public static function onControllerException($ex)
    {
        // 静态方法也可以
    }
}
```

两个 Trait 缺一不可：`ExceptionReporterTrait::OnException()` 里写的是 `static::_()->_OnException($ex)`，所以报告器类必须自己带一个 `_()`——用 [`DuckPhp\Foundation\SingletonTrait`](../reference/Foundation-SingletonTrait.md)（骨架里 `Controller\Base` 也是这么给的），或者让报告器继承你的 `Base`。**只 `use ExceptionReporterTrait` 会在异常真的抛出来那一刻**报 `Call to undefined method …::_()`——启动时看不出来，因为那时只校验 `is_callable()`。

对应关系：`BusinessException` → `onBusinessException()`、`ControllerException` → `onControllerException()`、`ProjectException` → `onProjectException()`；没有对应方法（或异常本身就是报告器方法名撞上的那两个）→ `App::_()->_OnDefaultException()`。

### 异常安全包装：`Ext\ExceptionWrapper`（**不推荐**）

⚠️ 框架里已经没有内部使用者，新代码别用：

- 「一处调用不想让整条流程炸掉」→ 用 `Helper::XpCall($cb, ...$args)`（异常当返回值拿回）；
- 「真需要处理异常」→ 直接 `try/catch`，别把异常混进正常返回值。

老代码已经在用的可以继续用——它的机制（只捕 `\Exception`、不捕 `\Error`）与示例见[第 4-13 章](ext-classes.md) §12。

## 常见写法

### 1. 配置异常体系

```php
// MyProj\System\App
public $options = [
    'exception_for_project'    => ProjectException::class,
    'exception_for_business'   => BusinessException::class,
    'exception_for_controller' => ControllerException::class,
    'exception_reporter'       => [ExceptionAction::class, 'OnException'],

    'error_404'      => '_sys/error_404',
    'error_500'      => '_sys/error_500',
    'error_debug'    => '_sys/error-debug',
    'error_maintain' => '_sys/error_maintain',
];
```

### 2. 按层条件抛

```php
// Controller
Helper::ControllerThrowOn(!$user, '请先登录', 403);
// Business
Helper::BusinessThrowOn(!password_verify($password, $user['password']), '密码错误', 1002);
```

### 3. 在 App 里覆盖默认出口

```php
public function _OnDefaultException($ex): void
{
    Logger::_()->error($ex->getMessage());
    // 发通知、打点 …
    parent::_OnDefaultException($ex);
}
```

### 4. 用 ExceptionManager 注册处理器

```php
use DuckPhp\Core\ExceptionManager;

ExceptionManager::_()->assignExceptionHandler(ValidationException::class, function ($ex) {
    Helper::ShowJson(['errors' => $ex->errors]);
});
ExceptionManager::_()->setMultiExceptionHandler(
    [BusinessException::class, ControllerException::class],
    function ($ex) { /* 统一处理 */ }
);
ExceptionManager::_()->setDefaultExceptionHandler(function ($ex) { /* 兜底 */ });
```

### 5. 接管 PHP 错误（开发期）

`ExceptionManager` 选项（默认全开）：`handle_all_dev_error`（Notice/Deprecated 走 `_OnDevErrorHandler()`，其它级别转 `\ErrorException`）、`handle_all_exception`（未捕获异常进 `_CallException()`）。生产环境通常保持默认；只想关错误接管就把 `handle_all_dev_error` 设为 `false`。

## 常见错误

| 现象                                                                      | 原因                                         | 改法                                                                                                             |
| ----------------------------------------------------------------------- | ------------------------------------------ | -------------------------------------------------------------------------------------------------------------- |
| 业务异常继承了 `DuckPhpSystemException`                                        | 把「框架坏了」和「业务出错」混在一起                         | 改 `extends \Exception`；条件抛改用 `Helper::ThrowOn()`（不再推荐 `use ThrowOnTrait`）                                     |
| 配了 `exception_reporter` 却启动就报 `config error`                              | 值不是**可调用**的：只写类名（哪怕有静态 `OnException()`）在 PHP 里不是 callable（入口类 `is_callable()` 校验，源码 `src/DuckPhp.php` 第 140–146 行） | 写 `[报告器类::class, 'OnException']`（Trait 给的静态入口），或用闭包 / 可调用对象                              |
| 报告器方法没命中                                                                | 方法名不是 `on{异常类短名}`                          | 对照 [`ExceptionReporterTrait::OnException()`](../reference/Foundation-Controller-ExceptionReporterTrait.md) 的拼法 |
| 404 页不出来，只有占位文本                                                         | `error_404` 没配，或应用在 init 完成前就 404          | 配 `'error_404' => '_sys/error_404'`；init 完成前的错误只出占位                                                            |
| 生产环境泄露了异常详情                                                             | `is_debug` 为真，或视图里没判 `__is_debug()`        | 上线置 `false`；错误视图里用 `__is_debug()` 包调试块                                                                         |
| `IsDebug()` 莫名为真                                                        | 根应用或 Setting 里 `duckphp_is_debug` 为真       | 用 `IsHiddenDebug()` 或检查根/设置                                                                                    |
| 维护页不生效                                                                  | 只配了 `error_maintain` 没置 `is_maintain`      | 同时置 `'is_maintain' => true` 或 Setting `duckphp_is_maintain`                                                    |
| [`ExceptionWrapper`](../reference/Ext-ExceptionWrapper.md)（不推荐用）没接住 `\Error` | 它只捕获 `\Exception`                          | `\Error` 属编程错误，应让它抛出来修；不推荐用的理由见上文                                                                          |

## 下一步

- [第 2-13 章 事件系统](events.md)：登录/登出、异常前后都能挂事件。
- [第 2-2 章 请求生命周期](lifecycle.md)：异常发生在请求时序的哪一环。
- [第 1-6 章 调试、日志与 CLI 初体验](debugging.md)：`is_debug` 与日志分级的入门。
- 参考手册：[Core-ExceptionManager](../reference/Core-ExceptionManager.md)、[Core-App](../reference/Core-App.md)、[Core-ExitException](../reference/Core-ExitException.md)、[Foundation-ExceptionReporterTrait](../reference/Foundation-Controller-ExceptionReporterTrait.md)；`Ext\ExceptionWrapper` / `Ext\ThrowOnTrait` 见[第 4-13 章](ext-classes.md)
