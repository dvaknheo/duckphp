# DuckPhp\Ext\HookChain

钩子链扩展组件。

## 简介

`HookChain` 实现了一个可调用钩子链。它按顺序执行一组回调，直到某个回调返回 `true` 时停止。该组件实现了 `ArrayAccess` 接口，可以直接当作数组操作。

## 选项

该组件没有独立选项。

## 使用方式

### 创建并执行钩子链

```php
$chain = new \DuckPhp\Ext\HookChain();
$chain->add(function () {
    echo 'first';
    return false;
}, true, true);
$chain->add(function () {
    echo 'second';
    return true;
}, true, true);

($chain)(); // 输出 first second
```

### 使用 `Hook()` 静态方法挂载

```php
$callback = null;
\DuckPhp\Ext\HookChain::Hook($callback, function () {
    return true;
});

($callback)();
```

### 作为数组使用

```php
$chain = new \DuckPhp\Ext\HookChain();
$chain[] = function () { return true; };
isset($chain[0]); // true
unset($chain[0]);
```

## 配置示例

`HookChain` 为独立工具类，通常无需在框架配置中加载。

## 注意事项

1. 调用钩子链时，按 `chain` 数组顺序执行，直到某个回调返回 `true` 停止。
2. `add()` 支持 `append` 和 `once` 参数：`append` 为 `true` 时追加，`false` 时前置；`once` 为 `true` 时避免重复添加。
3. `Hook()` 静态方法会把原变量与新的回调组合成新的 `HookChain`。
4. 实现了 `ArrayAccess` 的四个方法：赋值、存在检测、删除、读取。

## 全部选项

无

## 方法列表

### 公共方法

    public function __invoke(): void
执行钩子链，按顺序调用回调，遇到返回 `true` 时停止。

    public static function Hook(&$var, $callable, $append = true, $once = true)
将回调挂载到变量。如果变量已是 `HookChain`，则添加到该链；如果为 `null`，则创建新链；否则创建新链并包含原变量和新回调。

    public function add(callable $callable, bool $append, bool $once)
添加回调到链中。`once` 为 `true` 时，如果回调已存在则跳过。

    public function remove(callable $callable): void
从链中移除指定回调。

    public function has(callable $callable): bool
判断链中是否包含指定回调。

    public function all(): array
返回链中所有回调。

    public function offsetSet($offset, $value)
实现 `ArrayAccess`：设置回调。

    public function offsetExists($offset)
实现 `ArrayAccess`：判断是否存在。

    public function offsetUnset($offset)
实现 `ArrayAccess`：删除回调。

    public function offsetGet($offset)
实现 `ArrayAccess`：获取回调。

## 相关链接

- [DuckPhp\Ext\RouteHookManager](Ext-RouteHookManager.md)
