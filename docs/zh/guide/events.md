# 19 事件系统

> 解决什么问题：在**不改动对方代码**的前提下，让应用的各个部分（甚至跨应用的组件）能对「某件事发生了」作出响应。
> 前置：[第 17 章 请求生命周期与钩子点](lifecycle.md)、[第 14 章 Helper 与全局函数](helper.md)。预计 15 分钟。
> 示例来自 `tests/data_for_tests/ZThirdDemo`，可用 `wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` 实跑。

## 最小示例

`ZThirdDemo` 里，子应用下单后广播一个事件，主应用在自己的相位里监听它：

```php
// 主应用（根相位）监听：tests/data_for_tests/ZThirdDemo/src/System/MainApp.php :: onInit()
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    self::$orders[] = $order_id;   // 真实项目里这里可能是写日志、发消息队列
});

// 子应用（子相位）广播：tests/data_for_tests/ZThirdDemo/third/Business/ShopBusiness.php :: placeOrder()
GlobalEvent::_()->fire('third.ordered', $order_id);
```

`GlobalEvent` **默认是关闭的**（`DuckPhp.php` 里默认 `EXT_DISABLE`），要在根应用的 `ext` 里先打开：

```php
// tests/data_for_tests/ZThirdDemo/src/System/MainApp.php
'ext' => [
    GlobalEvent::class => true,
],
```

## 机制说明

### 事件回调绑定在**相位**上

`GlobalEvent` 的核心设计：每个监听都记为 `[事件名, 相位, 回调]` 三元组。`fire()` 派发时，会**先把当前相位切到注册时的相位、执行回调、再切回来**（`src/Component/GlobalEvent.php` 第 27–39 行）。这意味着：

- 回调里所有 `::_()` 取到的都是**它注册那个相位**里的单例；
- 监听方与被监听方可以处在不同相位（典型场景：主应用监听子应用的事件），不需要手动切相位。

### API 一览

| 方法 | 作用 |
|---|---|
| `on($event, $callback)` | 绑定到**当前相位**（内部调 `globalOn($event, App::Phase(), $callback)`） |
| `globalOn($event, $phase, $callback)` | 绑定到指定相位（`''` = 根相位） |
| `fire($event, ...$args)` | 按注册顺序逐个调用回调；**无返回值** |
| `all()` | 返回整个注册表（排错用） |
| `remove($event, $phase = null, $callback = null)` | 不传后两者则清空该事件；否则按相位+回调过滤 |

### Helper 侧入口

工程的 `Helper`（`use DuckPhp\Helper\AppHelperTrait` / `BusinessHelperTrait` / `ControllerHelperTrait`）都提供了同名静态方法，语义一致：

```php
Helper::OnGlobalEvent('third.ordered', function ($order_id) { /* … */ });  // = GlobalEvent::_()->on(...)
Helper::FireGlobalEvent('third.ordered', $order_id);                       // = GlobalEvent::_()->fire(...)
Helper::RemoveEvent('third.ordered');                                      // = GlobalEvent::_()->remove(...)
```

### 事件名约定：「进行中 / 已完成」后缀

框架内置的事件常量分两组（`src/Helper/BusinessHelperTrait.php` 第 23–27 行、`src/Helper/ControllerHelperTrait.php` 第 28–35 行）：

| 层 | 常量 | 值 |
|---|---|---|
| Business | `$EVENT_REGISTERING` / `$EVENT_REGISTERED` | `registering` / `registered` |
| Business | `$EVENT_LOGINING` / `$EVENT_LOGINED` | `logining` / `logined` |
| Controller | `$EVENT_ACTION_REGISTERING` / `$EVENT_ACTION_REGISTERED` | `action_registering` / `action_registered` |
| Controller | `$EVENT_ACTION_LOGINING` / `$EVENT_ACTION_LOGINED` | `action_logining` / `action_logined` |
| Controller | `$EVENT_ACTION_LOGOUTING` / `$EVENT_ACTION_LOGOUTED` | `action_logouting` / `action_logouted` |

**约定**：`xxxing` 表示「正在进行中」（还可以干预），`xxxed` 表示「已完成」（做善后）。自定义事件请沿用同一后缀，例如 `order.creating` / `order.created`。

### 与第 17 章钩子的分工

| | 钩子（Route Hook） | 事件（GlobalEvent） |
|---|---|---|
| 结构 | **单链**：一个位置一个回调串 | **广播**：一个事件多个监听 |
| 返回值 | **有返回值**，返回真值可短路后续钩子 | **无返回值**，`fire()` 始终逐个调完 |
| 典型用途 | 拦截/重写/映射请求（如 `RouteHookRewrite`） | 通知「某件事发生了」（如下单成功） |
| 跨相位 | 否（在路由当前相位内执行） | 是（回调在注册时的相位里执行） |
| 配置入口 | `Route::addRouteHook()` / 选项 `ext` | 选项 `ext` 里打开 `GlobalEvent::class => true` |

一句话：**要拦截请求用钩子，要广播状态用事件**。详见 [第 17 章](lifecycle.md)。

### `Ext\EventManager` 是什么

`src/Ext/EventManager.php` 是另一个独立的事件管理器扩展，API 与 `GlobalEvent` 类似（`OnEvent/FireEvent/AllEvents/RemoveEvent`），但**不带相位概念**——回调在哪个相位 `fire` 就在哪个相位执行。框架内部**没有使用它**（`src/` 里无引用），属于可选工具；需要「不带相位切换的纯进程内事件总线」时才引入。参考 [DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md)。

## 常见写法

```php
// 1) 在当前相位监听（最常用）
Helper::OnGlobalEvent('order.created', function ($order) {
    Logger::_()->info('order created: ' . $order['id']);
});

// 2) 显式指定相位监听（跨应用场景）
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    // 这个回调永远在根相位里执行
});

// 3) 广播事件
Helper::FireGlobalEvent('order.created', ['id' => 42, 'amount' => 199.00]);

// 4) 排错：看当前都注册了哪些监听
var_dump(GlobalEvent::_()->all());

// 5) 取消监听
Helper::RemoveEvent('order.created');                    // 清空该事件的全部监听
GlobalEvent::_()->remove('order.created', '', $callback); // 只删指定相位+回调
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 监听不到任何事件 | `GlobalEvent` 没打开（默认 `EXT_DISABLE`） | 在根应用 `ext` 里声明 `GlobalEvent::class => true` |
| 事件回调里 `::_()` 拿错实例 | `fire()` 会把相位切到「注册时的相位」再执行回调 | 注册时就选对相位（`globalOn` 的第二个参数） |
| 重复注册导致回调执行多次 | 同一事件+相位+回调三元组已存在时 `globalOn` 会跳过；但不同闭包算不同回调 | 排重时用同一个 callable（如 `[Class::class, 'method']`），不要每次 `fire` 前都 `on` |
| 想让事件「拦截」后续流程 | 事件是广播、无返回值、不可短路 | 改用路由钩子（[第 17 章](lifecycle.md)）或直接在业务里判断 |
| `remove($event, $phase, $callback)` 删不掉 | 源码的过滤条件是「相位与回调**都**不同才保留」 | 传入与注册时**完全一致**的相位与回调 |

## 下一步

- [第 17 章 请求生命周期与钩子点](lifecycle.md)：钩子的单链/短路语义，与事件的分工。
- [第 20 章 缓存与 Redis](cache.md)：事件回调里如果要写共享状态，缓存是可靠的去处。
- 参考手册：[DuckPhp\Component\GlobalEvent](../reference/Component-GlobalEvent.md)、[DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md)。
