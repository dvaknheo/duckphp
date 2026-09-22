# DuckPhp\Core\DuckPhpSystemException

## 简介

系统抛出的、携带 ThrowOn 能力的通用异常基类。

`DuckPhpSystemException extends Exception`（PHP 标准异常），并用 `ThrowOnTrait` 提供静态遍历“满足则抛”的入口。框架内部在各处用它的子类直接抛异常来描述问题（如“Phase 重名”“不可 init 基类”等）。

> ⚠️ **只用于框架内部系统级错误，外部/业务异常不要继承它。**
> 判断依据应该是「这是框架自己出的问题」还是「工程的业务/权限问题」：
> - 框架内部机制出错（Phase 名冲突、直接 init 基类、缺 provider…）→ 抛 `DuckPhpSystemException`（或框架内部的子类）；
> - 工程侧的业务/权限/登录异常 → 继承 `\Exception` 自己定义，例如 [AdminException](GlobalAdmin-AdminException.md)、[UserException](GlobalUser-UserException.md) 都是**直接继承 `\Exception`** 的。
>
> 原因：捕获 `DuckPhpSystemException` 等于「框架坏了」的兜底信号，业务异常混进来会让上层无法区分「该提示用户」还是「该报障」。
> 想要 `ThrowOn()` 那种守卫式抛法不必继承本类——像 `AdminException` 那样 `use DuckPhp\Ext\ThrowOnTrait;` 即可。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class DuckPhpSystemException extends Exception`
- 使用 Trait：`DuckPhp\Ext\ThrowOnTrait`

## 使用方式

```php
use DuckPhp\Core\DuckPhpSystemException;

// 守卫式：不满足即抛
DuckPhpSystemException::ThrowOn($user == null, 'not logged in');
```

要在工程里定义**自己的业务异常**，请继承 `\Exception`（而不是本类），需要守卫式抛法就再 `use ThrowOnTrait`：

```php
use DuckPhp\Ext\ThrowOnTrait;

class MyBizException extends \Exception
{
    use ThrowOnTrait;
}
```

### 作为“exit 换成异常”的基类
`DuckPhp\Core\ExitException extends DuckPhpSystemException`，供 `__EXIT_EXCEPTION` 语义（通过抛 ExitException 实现中断而不是真正的 exit）。可见 `use_exit_exception` 描述于 `Core-KernelTrait`。

## 方法列表

本类**自身没有显式声明任何方法**，可用能力来自以下来源：

- 静态抛出工具 `ThrowOn(...)`：来自 `use DuckPhp\Ext\ThrowOnTrait` —— 首位参数为真时 `throw new static($message,$code)`；详见 [Core-ThrowOnTrait](Ext-ThrowOnTrait.md)。
- 标准异常能力：来自 PHP 内置 `Exception`（`getMessage()`、`getCode()`、`getPrevious()`、`getLine()`、`getFile()`、`getTrace()`、`__toString()` 等），全部可用。

## 相关链接

- [DuckPhp\Ext\ThrowOnTrait](Ext-ThrowOnTrait.md) — 静态抛异常来源 Trait
- [DuckPhp\Core\ExitException](Core-ExitException.md) — 它的独特子类（exit 语义）
