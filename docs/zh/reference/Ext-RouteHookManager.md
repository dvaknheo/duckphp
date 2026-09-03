# DuckPhp\Ext\RouteHookManager

## 简介

`RouteHookManager` 是路由钩子列表的管理器：它把 `Route` 的 `pre_run_hook_list` 或 `post_run_hook_list` 以引用方式绑到自己（`attachPreRun`/`attachPostRun`），随后用 `append/insertBefore/moveBefore/removeAll/setHookList/getHookList` 等操作精细调整钩子顺序（而不是每次 `addRouteHook` 只能往两头加）。

配合使用：其它扩展（如 `MyMiddlewareManager`）通过 `RouteHookManager::_()->attachPreRun()->append([…,'Hook'])` 把自身挂到 pre-run 链。

## 类信息

- 命名空间：`DuckPhp\Ext`
- 声明：`class RouteHookManager extends DuckPhp\Core\ComponentBase`

## 选项

本组件不新增选项（`$options = []`，沿用组件基类）。

## 使用方式

```php
use DuckPhp\Ext\RouteHookManager;

$mgr = RouteHookManager::_();
$mgr->attachPreRun();                      // 绑定 Route 的 pre_run 列表
$mgr->append([MyHook::class, 'run']);      // 追加
$mgr->insertBefore([A::class, 'run'], [B::class, 'run']);
$mgr->removeAll([B::class, 'run']);

$mgr->attachPostRun();                     // 换绑 post_run 列表
$mgr->append([AnotherHook::class, 'run']);
```

## 注意事项

- `attachPreRun`/`attachPostRun` 之后，`$this->hook_list` 是对 `Route` 相应属性**的引用**，之后的增删改会直接反映到路由执行链。
- `moveBefore($new,$old)` = `removeAll($new)` + `insertBefore($new,$old)`；元素按“===”比较。
- `dump()` 委托 `Route::dumpAllRouteHooksAsString()`。

## 方法列表

### 公共方法

    public function attachPreRun(): self
绑定 Route 的 `pre_run_hook_list` 到本管理器。

    public function attachPostRun(): self
绑定 Route 的 `post_run_hook_list` 到本管理器。

    public function detach(): void
解绑（把本地 hook_list 置空，不再指向 Route）。

    public function getHookList(): array
返回当前钩子列表。

    public function setHookList(array $hook_list): void
整体设置钩子列表。

    public function moveBefore($new, $old): self
把 `$new` 移动到 `$old` 之前。

    public function insertBefore($new, $old): self
在 `$old` 之前插入 `$new`。

    public function removeAll($name): self
删除所有等于 `$name` 的钩子。

    public function append($name): void
向列表末尾追加钩子。

    public function dump(): string
输出 Route 全部钩子信息字符串。

## 相关链接

- [DuckPhp\Core\Route](Core-Route.md) — 钩子宿主（pre/post_run_hook_list）
- [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) — 使用 attachPreRun 的示例
