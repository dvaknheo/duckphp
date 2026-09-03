# DuckPhp\Core\ExitException

## 简介

用于把“程序应提前结束”的地方转成**可捕获的异常**来处理，而不是直接 `exit`/`die`：框架常以抛一个 `ExitException` 代替直接退出，使外层（如输出缓冲、资源清理、测试闭环）有机会统一收尾。

`ExitException extends DuckPhp\Core\DuckPhpSystemException`，因此也具备 `DuckPhpSystemException`（及其 `ThrowOnTrait`）能力；此外它提供 `Init()` 来建立全局常量 `__EXIT_EXCEPTION`。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class ExitException extends DuckPhpSystemException`
- 相关全局常量：`__EXIT_EXCEPTION`（由 Init 写入，值即本类名）

## 使用方式

框架层判断到“请求应在此中断”时会被包装为抛 ExitException 让上层在 `try…catch` 里识别并完成清理与停止。通常不直接在业务里用到；如需保持“退出也是一种可控对象”，可：

```php
// 框架正常会初始化：ExitException::Init(); 使 __EXIT_EXCEPTION 已定义
throw new \DuckPhp\Core\ExitException('stop here', 200);
```

`SystemWrapper` 的 `exit` 或其他需收尾场景，可选择由外层捕获 ExitException 后做 finally。

## 注意事项

- 与 PHP 原生 `exit` 不同：`ExitException` 让“结束”留在可捕获层，测试可用它避免真正终止进程。
- `use_exit_exception` 打开时，框架会把必要引入的 `ExitException::Init()` 带出来——参见 `KernelTrait`/`App` 的 `initException` （会 `define('__EXIT_EXCEPTION', ExitException::class)`）。

## 方法列表

### 公共方法

    public static function Init()
当 `__EXIT_EXCEPTION` 尚未定义时，把它 `define` 为 `static::class`（即本类名）。

## 相关链接

- [DuckPhp\Core\DuckPhpSystemException](Core-DuckPhpSystemException.md) — 父类
- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — 包装 exit 语义以便抛出 ExitException
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — 在 initException 阶段 Init/define
