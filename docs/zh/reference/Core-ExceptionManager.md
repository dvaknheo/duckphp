# DuckPhp\Core\ExceptionManager

框架异常/错误处理的中枢：自定义 PHP 的 `set_error_handler/set_exception_handler`，把错误与异常按既定路由分发到“项目处理器/默认处理器/调试器”。

## 简介

`ExceptionManager`（`class ExceptionManager extends ComponentBase`）做了两件事：

- **接管 PHP 错误**：当 `handle_all_dev_error` 打开则以 `on_error_handler` 作错误回调（注意级别才触发 `dev_error_handler`；其它会转 `ErrorException`）。
- **接管异常**：当 `handle_all_exception` 打开则 `set_exception_handler(CallException)`，由 `_CallException`：如果异常是 `__EXIT_EXCEPTION` 则直接放行；否则从 `exceptionHandlers`（按注册顺序反转）里找第一个是 a 该异常的类回调并执行，都找不到最后交给 `default_exception_handler`。

框架（Core `KernelTrait`）会在初始化阶段 `init` 它，并把 root App 的 `OnDefaultException` 作为 `default_exception_handler`、`OnDevErrorHandler` 作 `dev_error_handler`，所以多数项目不必手调这些。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class ExceptionManager extends ComponentBase`

## 选项

`ExceptionManager::$options`：

| 选项                          | 默认值  | 说明                                                                             |
| --------------------------- | ---- | ------------------------------------------------------------------------------ |
| `handle_all_dev_error`      | true | 是否接管 PHP 错误处理器（dev path）。                                                      |
| `handle_all_exception`      | true | 是否接管全局异常处理器。                                                                   |
| `system_exception_handler`  | null | 自定义异常安装回调 `function(callable $handler)`，可替代内建 set_exception_handler（极端/跨运行时用）。 |
| `handle_exception_on_init`  | true | init 时立即 run 接管。                                                               |
| `default_exception_handler` | null | 未匹配的自定义回退（通常填 App::OnDefaultException）。                                        |
| `dev_error_handler`         | null | dev错误回调（通常 App::OnDevErrorHandler）。                                            |

## 使用方式（一般不需要手工大改）

> 隐藏选项（不在本类 `$options` 里声明，但 init 时会读）：`exception_reporter`（项目级“异常报告类”，需有静态 `OnException`）、`exception_for_project`（project 主异常类名，配了 reporter 时作为 assign 的目标基类）。它们通常写在应用入口的 options 里。
```php
use DuckPhp\Core\ExceptionManager;

// 预分配某个异常族“ 自己的回调”
ExceptionManager::_()->assignExceptionHandler(MyAppException::class, function ($ex) {
    // 自定义处理
});
```

```php
// 主动把一个异常按规则宣判（内部 call default/report ... 都经 _CallException）
ExceptionManager::CallException($ex);
```

### 全局接管/清除

普通 run/clear 场景极少手动做（框架处理好）。若独自加载：

```php
$em = ExceptionManager::_()->init([]);
$em->run();     // 装上 error/exception 处理器
// ...
$em->clear();   // restore 掉
```

## 配置示例

```php
// 在业务 app options…
'exception_reporter'   => \App\ExceptionReporter::class,  // class with OnException()
'exception_for_project'=> \App\BusinessException::class,
```

## 注意事项

1. `on_error_handler` 把 非 notice 级别的错误声为 `ErrorException` 抛出；notice/deprecated 交给 `dev_error_handler`。
2. `_CallException` 匹配是按**注册的倒序**找第一个 is_a 打中的，越晚 assign 声明匹配优先（通常用它做“最具体类型优先”）。
3. `__EXIT_EXCEPTION`（ExitException）会跳过分发，让“退出”不被误拦。
4. 未安装 `exception_reporter`/无子类匹配时仅 default；若 default 为 null，异常最终不会再被处理（会交由 PHP）。
5. `clear()` restore 掉 PHP 处理器并复位运行标记。

## 方法列表

### 公共方法

    public function init(array $options, ?object $context = null)
父 init 后按 options 生效：`handle_exception_on_init` 时 run()；并 if exception_reporter then assign default了 exception_for_project 的回调。

    public static function CallException($ex)
把异常丢进 `_CallException`（shell）供 set_exception_handler/捕获方。

    public function setDefaultExceptionHandler($default_exception_handler)
设默认回退处理器。

    public function assignExceptionHandler($class, $callback = null)
登记：单类(string=>callback) 或 批量数组 类=>callback。

    public function setMultiExceptionHandler(array $classes, $callback)
同一回调用于多个异常类。

    public function on_error_handler($errno, $errstr, $errfile, $errline)
被 `set_error_handler` 使用；notice/deprecation→dev_error_handler，其余 throw ErrorException；返回 true 阻止 PHP 默认。

    public function _CallException($ex)
分发核心（见简介）。

    public function isInited(): bool
是否已初始化。

    public function run()
装 error/exception 处理器（避免重复），保存 last handlers。

    public function reset()
（占位）只是返回 this（示意准备清空接口，具体清空由 clear 做）。

    public function clear()
restore_error/exception_handler（或 system 清空），标记 is_running/is_inited 复位。

### 受保护方法

    protected function initOptions(array $options): void
从 options 取出 default/system 两个 handler 存到 properties。


### （register up 存在原样示例，多数走 init）

## 相关链接

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — 初始化时装配它（默认/dev handler 为 App 的 on404 等）
- [DuckPhp\Core\App](Core-App.md) —`OnDefaultException`/`OnDevErrorHandler`
- [DuckPhp\Core\ExitException](Core-ExitException.md) — 被 _CallException 直接放行的类型
- guid：exception.md
