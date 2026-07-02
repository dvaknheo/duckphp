# DuckPhp\Ext\RouteHookManager

路由钩子管理器扩展。

## 简介

`RouteHookManager` 用于管理 `DuckPhp\Core\Route` 中注册的路由钩子。它可以附加到 `pre_run_hook_list` 或 `post_run_hook_list`，并提供插入、移除、移动、追加等操作。

## 选项

该组件没有独立选项。

## 使用方式

### 附加到前置钩子列表

```php
$manager = \DuckPhp\Ext\RouteHookManager::_();
$manager->attachPreRun();
$hooks = $manager->getHookList();
```

### 调整钩子顺序

```php
$manager->attachPreRun();
$manager->moveBefore('NewHook', 'OldHook');
$manager->removeAll('SomeHook');
$manager->append('AnotherHook');
```

### 查看所有钩子

```php
$info = $manager->dump();
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'ext' => [
            \DuckPhp\Ext\RouteHookManager::class => true,
        ],
    ];
}
```

## 注意事项

1. 必须先调用 `attachPreRun()` 或 `attachPostRun()` 才能操作对应钩子列表。
2. 钩子列表以引用方式绑定，`detach()` 后不再同步。
3. `moveBefore()` 会先移除所有同名钩子，再插入到目标钩子之前。
4. `dump()` 调用 `Route::dumpAllRouteHooksAsString()` 输出当前所有钩子信息。

## 全部选项

无

## 方法列表

### 公共方法

    public function attachPreRun()
绑定到 `Route` 的 `pre_run_hook_list`。

    public function attachPostRun()
绑定到 `Route` 的 `post_run_hook_list`。

    public function detach()
解除钩子列表引用。

    public function getHookList()
获取当前钩子列表。

    public function setHookList($hook_list)
设置当前钩子列表。

    public function moveBefore($new, $old)
将 `new` 钩子移动到 `old` 钩子之前。

    public function insertBefore($new, $old)
在 `old` 钩子之前插入 `new` 钩子。

    public function removeAll($name)
移除所有名为 `name` 的钩子。

    public function append($name)
在钩子列表末尾追加一个钩子。

    public function dump()
返回当前所有路由钩子的字符串描述。

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\Route](Core-Route.md)
