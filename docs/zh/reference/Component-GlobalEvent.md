# DuckPhp\Component\GlobalEvent

极简全局事件总线：`on/fire`，事件回调可绑定到特定 Phase（phase 在触发回调时临时切过去）。

## 简介

`GlobalEvent extends ComponentBase` 提供跨组件触发事件的小机制：

- `on($event,$callback)`：在当前 Phase 注册事件回调；
- `globalOn($event, $phase, $callback)`：显式把回调与一个 Phase 绑定；
- `fire($event,...$args)`：按注册顺序调用回调（每个回调先切到它绑定的 Phase 再调用）；
- `remove($event,?,?)` 与 `all()` 供查询移除/检索。

不引入异步/顺序优先级：简单顺序执行。使能由 DuckPhp 以 `GlobalEvent` 与 `EXT`（若需）装配（默认被 Engine 提供 events）。

## 类信息

- 命名空间：`DuckPhp\Component`
- 声明：`class GlobalEvent extends ComponentBase`

## 使用方式

```php
use DuckPhp\Component\GlobalEvent;

GlobalEvent::_()->on('user.login_after', function ($u) { /* … */ });
GlobalEvent::_()->globalOn('site.visit', 'admin', function () { /* in admin phase */ });
GlobalEvent::_()->fire('user.login_after', $user);
```

业务多处希望通过 Helper 层 Fire/On（HelperAppTrait/Business/… 提供 FireGlobalEvent/OnGlobalEvent 语义即委托它）。

## 注意事项

- `on` 内部使用当前 `App::Phase()` 作为绑定 phase？实际 on()=globalOn(event,App::Phase(),cb)，调 fire 的时候无论任何 phase，都会切回绑定 phase。
- remove 参数带 phase/callback 时按二者都不同才保留；即只删与给定 phase(或回调)完全不同的？——注意筛选逻辑：保留当且仅当 calling_phase != phase（且 calling_callback != callback）。若二者都提供则两个不同才算删除语义需看实现。它实现成一留当两者均不同。
- all() 返回内部注册表方便调试。

## 方法列表

### 公共方法

    public function on($event, $callback)
在当前 Phase 上注册回调（委托 globalOn(App::Phase(),…)）。

    public function globalOn($event, ?string $phase, callable $callback)
（相 event 已有同 pair 则不再重复加）记录 [phase,cb]。

    public function fire($event, ...$args)
存在则遍历：先 `App::Phase(绑定phase)`，调用回调，恢复上一 phase。

    public function all()
返回整个注册映射（events）。

    public function remove($event, ?string $phase = null, $callback = null)
删除：phase 与 callback 都缺则 unset 全 event；否则移除不匹配者（即删匹配）。

## 相关链接

- DuckPhp\DuckPhp 若使用 global events，将会注册此组件
- AppHelper/…的 Fire(&On) shell：见 Foundation/Helper
