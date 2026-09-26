# DuckPhp\Ext\HookChain

## 简介

`HookChain` 表示一串「回调链」：`__invoke()` 时按顺序执行链中回调，任一回调返回真值即中断；也可作为数组使用（实现 `ArrayAccess`）。它常被用于“一组钩子按优先级执行、命中即停”的模式（框架内部曾用于状态检查等钩子组织）。

`Hook::Hook(&$var, $callable, ...)` 是个便捷入口：把已有回调/链与新回调合并成一条链并写回 `$var`。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class HookChain implements \ArrayAccess`

## 使用方式

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add(function () { /* 1 */ }, true, true);
$chain->add(function () { return true; }, true, true); // 返回 true → 中断
$chain(); // 顺序调用，遇 true 停

// 便捷合并：
HookChain::Hook($target, function () { /* … */ });
// $target 若是回调/数组/链/空，都会整理成一条 HookChain
```

## 注意事项

- `add($callable, bool $append, bool $once)`：`$append=true` 追加到尾部、`false` 插到头部；`$once=true` 时重复回调不加入。
- `__invoke()` 逐个执行，遇回调返回“真值”即 `break`。
- `ArrayAccess`：`$chain[$i]` 读写链上元素；`offsetSet(null,…)` 视为追加。
- `Hook()` 静态：`$var` 为 `HookChain` 时直接 add；为 `null` 时新建并 add；为其它值（如已有回调）时把旧值与新回调一并放入新链。

## 方法列表

### 公共方法

    public function __construct()
构造空链。

    public function __invoke(): void
顺序执行链上回调，任一返回真值即中断。

    public static function Hook(&$var, $callable, $append = true, $once = true)
把 `$callable` 与既有 `$var` 合并成一条链写回 `$var`。

    public function add(callable $callable, bool $append, bool $once)
向链中追加/前插回调（`once` 时去重）。

    public function remove(callable $callable): void
从链中移除指定回调。

    public function has(callable $callable): bool
链中是否包含指定回调。

    public function all(): array
返回整条回调链数组。

    public function offsetSet($offset, $value): void
数组写（null 偏移追加）。

    public function offsetExists($offset): bool
数组键是否存在。

    public function offsetUnset($offset): void
删除数组键。

    public function offsetGet($offset)
取数组键（不存在返回 `null`）。

## 相关链接

- [第 4-13 章 `Ext\*` 扩展类](../guide/ext-classes.md) §15 — 本类的定位，以及为什么它在推荐路径外
