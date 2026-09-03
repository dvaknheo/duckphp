# DuckPhp\Ext\ExceptionWrapper

## 简介

`ExceptionWrapper` 是一个“异常安全调用包装”：把一个对象包进本组件后，对它发起的任何方法调用（经魔术 `__call`）都会被 try/catch 包裹——成功返回调用结果，失败则把 `\Exception` 对象作为返回值交回调用方（而不是抛出）。

常用于“调用第三方/容易抛异常的对象，且希望把异常当返回值处理”的场景。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class ExceptionWrapper extends DuckPhp\Core\ComponentBase`

## 使用方式

```php
use DuckPhp\Ext\ExceptionWrapper;

$safe = ExceptionWrapper::Wrap($httpClient);
$ret  = $safe->request('https://…');   // 正常→结果；抛异常→返回 $ex 对象

$obj = ExceptionWrapper::Release();     // 取回被包装对象并清空
```

## 注意事项

- `Wrap($object)`（静态）等价 `doWrap`；`Release()`（静态）等价 `doRelease`（返回被包装对象并置空内部引用）。
- 只捕获 `\Exception`（不捕获 `\Error`/`\Throwable` 中的非 Exception）。
- 通过 `static::_()` 单例持有当前对象，同一时刻只包装一个对象；需要并行包装请各自实例化。

## 方法列表

### 公共方法

    public static function Wrap($object)
静态：把 `$object` 交给当前实例包装。

    public static function Release()
静态：取回被包装对象并清空。

    public function doWrap($object): self
保存待包装对象，返回自身。

    public function doRelease(): ?object
返回被包装对象并置空内部引用（无则 `null`）。

    public function __call(string $method, array $args)
代理调用被包装对象的方法；抛 `\Exception` 时返回该异常对象。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — 组件基类
