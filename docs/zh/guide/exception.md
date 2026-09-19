# 18 异常与错误处理

> 解决什么问题：异常怎么分层、条件抛怎么写、错误页怎么配、异常报告器怎么接。
> 前置：[第 10 章 控制器](controllers.md)、[第 15 章 表单与数据验证](validator.md)。预计 20 分钟。
> 示例：`demo/src/Controller/ExceptionReporter.php`（报告器骨架）、`demo/view/_sys/error_404.php` / `error_500.php` / `error_maintain.php`（错误视图）。

## 最小示例

```php
<?php declare(strict_types=1);
namespace MyProj\System;

use DuckPhp\Core\ThrowOnTrait;
use Exception;

class ProjectException extends Exception
{
    use ThrowOnTrait;                       // 提供 ProjectException::ThrowOn(...)
}
class BusinessException extends ProjectException {}
class ControllerException extends ProjectException {}
```

```php
// Business 层
Helper::BusinessThrowOn($balance < $amount, '余额不足', 2001);
// 等价于：BusinessException::ThrowOn($balance < $amount, '余额不足', 2001);（exception_for_business 配成它时）
```

异常沿框架的异常管理器（[DuckPhp\Core\ExceptionManager](../reference/Core-ExceptionManager.md)）走一圈：命中已注册处理器 → 执行；否则交给默认出口 `_OnDefaultException()` 出 500 页或调试详情。

## 机制说明

### 异常分层：一条铁律

> ⚠️ **`DuckPhpSystemException` 只表示「框架自己出了问题」**（相位重名、直接 init 基类、缺 provider 等），工程的业务/权限/登录异常请**直接 `extends \Exception`**。想要守卫式抛法不必继承它——`use DuckPhp\Core\ThrowOnTrait;` 即可。详见 [Core-DuckPhpSystemException](../reference/Core-DuckPhpSystemException.md)。

```
\Exception                              ← PHP 内置
  ├─ DuckPhp\Core\DuckPhpSystemException   ← 框架内部专用（工程不要继承）
  │     └─ DuckPhp\Core\ExitException      ← exit 语义（__EXIT_EXCEPTION）
  └─ MyProj\System\ProjectException        ← 工程异常基类（直接继承 \Exception）
        ├─ MyProj\System\BusinessException
        └─ MyProj\System\ControllerException
```

框架自带的 [UserException](../reference/GlobalUser-UserException.md) / [AdminException](../reference/GlobalAdmin-AdminException.md) 也是**直接继承 `\Exception`** 的（源码 `src/GlobalUser/UserException.php`、`src/GlobalAdmin/AdminException.php`），可作参照。

### 条件抛：ThrowOn 家族

[DuckPhp\Core\ThrowOnTrait](../reference/Core-ThrowOnTrait.md) 提供静态守卫：`XxxException::ThrowOn($flag, $message, $code)`——`$flag` 为真就抛。Helper 侧按层封装（[CoreHelper](../reference/Core-CoreHelper.md)）：

| 写法 | 抛出的异常类由谁定 |
|---|---|
| `Helper::ControllerThrowOn($flag, 'msg', $code)` | 选项 `exception_for_controller`（缺省 `\Exception`） |
| `Helper::BusinessThrowOn($flag, 'msg', $code)` | 选项 `exception_for_business`（缺省 `\Exception`） |
| `Helper::ProjectThrowOn($flag, 'msg', $code)` | 选项 `exception_for_project`（缺省 `\Exception`） |
| `MyException::ThrowOn($flag, 'msg', $code)` | 就是 `MyException` 自己 |

三个 `exception_for_*` 都是**隐藏选项**（`src/DuckPhp.php` 第 100–101 行只列了 business/controller；project 走 `ExceptionManager` 的同名选项），可在 App 选项里覆盖。另有 `exception_map` 可做异常类名映射（[CoreHelper](../reference/Core-CoreHelper.md)）。

### 错误视图四选项 + 维护页

`DuckPhp\Core\App` 的 `core_options`（源码 `src/Core/App.php` 第 51–57 行）声明了错误出口：

| 选项 | 默认 | 谁触发 |
|---|---|---|
| `error_404` | `null` | `_On404()`：路由没匹配上 |
| `error_500` | `null` | `_OnDefaultException()`：未捕获异常 |
| `error_debug` | `null` | `_OnDevErrorHandler()`：Notice/Deprecated 等开发期错误 |
| `error_maintain` | `null` | `prepareServe()`：`is_maintain` 或 Setting `duckphp_is_maintain` 命中 |

值可以是**视图名**（字符串）或**可调用**。`demo/view/_sys/` 下有三个现成视图可抄：

- `error_404.php`：`$is_debug = __is_debug();` 为真时附路由错误与回溯；
- `error_500.php`：非 debug 只输出 `500`，debug 输出异常类/消息/文件/行/追踪（变量 `$ex`/`$class`/`$message`/`$code`/`$trace`/`$file`/`$line`/`$is_debug` 由框架注入）；
- `error_maintain.php`：维护页。

未配置时框架输出占位文本（404：`404 File Not Found<!-- … -->`；500：`Internal Error<!-- … -->`），debug 下附详情。

### is_debug 与 IsRealDebug 的差别

- `App::_()->options['is_debug']`：本应用的选项值；
- `App::IsDebug()`（`_IsDebug()`，源码 `src/Core/App.php` 第 462–468 行）：**或**上 Setting 里的 `duckphp_is_debug` 与**根应用**的 `is_debug`——任一真即为真；
- `App::IsRealDebug()`（`_IsRealDebug()`，第 473–476 行）：默认等同 `IsDebug()`，是留给上层「要区分真假 debug 再覆盖」的口子。

视图里用全局函数 `__is_debug()`（即 `IsRealDebug()`）决定要不要显示调试块。

### 异常报告器 ExceptionReporter

配了 `exception_reporter` 选项后，`ExceptionManager` 初始化时（源码 `src/Core/ExceptionManager.php` 第 45–48 行）会把「`exception_for_project`（缺省 `\Exception`）的异常」统一指给报告器的 `OnException()`。[DuckPhp\Foundation\ExceptionReporterTrait](../reference/Foundation-ExceptionReporterTrait.md) 的分发逻辑：

1. 异常类名不在当前应用命名空间下 → `defaultException($ex)`（缺省转 `_OnDefaultException()`）；
2. 取类短名拼方法名 `on{短名}()`，可调用则执行；
3. 否则 `defaultException($ex)`。

`demo/src/Controller/ExceptionReporter.php` 是骨架：

```php
namespace ProjectNameTemplate\Controller;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
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

对应关系：`BusinessException` → `onBusinessException()`、`ControllerException` → `onControllerException()`、`ProjectException` → `onProjectException()`、其它 → `defaultException()`。

### 异常安全包装：Ext\ExceptionWrapper

[DuckPhp\Ext\ExceptionWrapper](../reference/Ext-ExceptionWrapper.md)（源码 `src/Ext/ExceptionWrapper.php`）把对象包一层：经 `__call` 的方法调用被 try/catch 包住——正常返回结果，抛 `\Exception` 时**把异常对象当返回值**交回（不抛）。只捕获 `\Exception`，不捕获 `\Error`。

```php
$safe = ExceptionWrapper::Wrap($someClient);
$ret  = $safe->request('https://…');   // 成功→结果；抛异常→返回 $ex
$obj  = ExceptionWrapper::Release();    // 取回被包装对象
```

## 常见写法

### 1. 配置异常体系

```php
// MyProj\System\App
public $options = [
    'exception_for_project'    => ProjectException::class,
    'exception_for_business'   => BusinessException::class,
    'exception_for_controller' => ControllerException::class,
    'exception_reporter'       => ExceptionReporter::class,

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

| 现象 | 原因 | 改法 |
|---|---|---|
| 业务异常继承了 `DuckPhpSystemException` | 把「框架坏了」和「业务出错」混在一起 | 改 `extends \Exception`；要 `ThrowOn()` 就 `use ThrowOnTrait` |
| 配了 `exception_reporter` 却没被调用 | 异常类不在当前应用命名空间，或 `exception_for_project` 没配 | 异常类放到 `namespace` 选项对应的命名空间下；或给 `exception_for_project` 配公共基类 |
| 报告器方法没命中 | 方法名不是 `on{异常类短名}` | 对照 `ExceptionReporterTrait::OnException()` 的拼法 |
| 404 页不出来，只有占位文本 | `error_404` 没配，或应用在 init 完成前就 404 | 配 `'error_404' => '_sys/error_404'`；init 完成前的错误只出占位 |
| 生产环境泄露了异常详情 | `is_debug` 为真，或视图里没判 `__is_debug()` | 上线置 `false`；错误视图里用 `__is_debug()` 包调试块 |
| `IsDebug()` 莫名为真 | 根应用或 Setting 里 `duckphp_is_debug` 为真 | 用 `IsRealDebug()` 或检查根/设置 |
| 维护页不生效 | 只配了 `error_maintain` 没置 `is_maintain` | 同时置 `'is_maintain' => true` 或 Setting `duckphp_is_maintain` |
| `ExceptionWrapper` 没接住 `\Error` | 它只捕获 `\Exception` | `\Error` 属编程错误，应让它抛出来修 |

## 下一步

- [第 19 章 事件系统](events.md)：登录/登出、异常前后都能挂事件。
- [第 17 章 请求生命周期与钩子点](lifecycle.md)：异常发生在请求时序的哪一环。
- [第 6 章 调试、日志与 CLI 初体验](debugging.md)：`is_debug` 与日志分级的入门。
- 参考手册：[Core-ExceptionManager](../reference/Core-ExceptionManager.md)、[Core-App](../reference/Core-App.md)、[Foundation-ExceptionReporterTrait](../reference/Foundation-ExceptionReporterTrait.md)、[Ext-ExceptionWrapper](../reference/Ext-ExceptionWrapper.md)、[Core-ThrowOnTrait](../reference/Core-ThrowOnTrait.md)
