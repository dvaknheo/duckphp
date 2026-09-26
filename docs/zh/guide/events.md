# 2-13 事件系统

> 解决什么问题：在**不改动对方代码**的前提下，让应用的各个部分（甚至跨应用的组件）能对「某件事发生了」作出响应。
> 前置：[第 2-4 章 路由钩子](route-hooks.md)、[第 2-9 章 Helper 与全局函数](helper.md)。预计 15 分钟。
> 示例来自 `tests/data_for_tests/ZThirdDemo`，可用 `wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/ZThirdDemoTest.php"` 实跑（**在仓库根目录下**跑）。

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

**开箱可用，不需要先「打开」**：`GlobalEvent::_()` 是惰性单例，不写任何 `ext` 配置，`GlobalEvent::_()->on(...)` + `fire(...)` 也照常触发（`Helper::OnGlobalEvent()` / `Helper::FireGlobalEvent()` 同理）。

```php
// tests/data_for_tests/ZThirdDemo/src/System/MainApp.php
'ext' => [
    GlobalEvent::class => true,
],
```

> 源码 `src/DuckPhp.php` 第 117 行的 `GlobalEvent::class => self::EXT_DISABLE` 很容易被误读成「默认关闭」：它只表示**组装阶段不把这个组件预先放进当前相位**（这类 `EXT_DISABLE` 项在 init 之后也不会留在 `options['ext']` 里）。组件按需创建，所以事件能不能用与这一行无关；ZThirdDemo 里显式写一行属于「表意清晰的可选项」，不是必需。

## 机制说明

### 事件回调绑定在**相位**上

`GlobalEvent` 的核心设计：每个监听都记为 `[事件名, 相位, 回调]` 三元组。`fire()` 派发时，会**先把当前相位切到注册时的相位、执行回调、再切回来**（`src/Component/GlobalEvent.php` 第 27–39 行）。这意味着：

- 回调里所有 `::_()` 取到的都是**它注册那个相位**里的单例；
- 监听方与被监听方可以处在不同相位（典型场景：主应用监听子应用的事件），不需要手动切相位。

### API 一览

| 方法                                                | 作用                                                                                       |
| ------------------------------------------------- | ---------------------------------------------------------------------------------------- |
| `on($event, $callback)`                           | 绑定到**当前相位**（内部调 [`globalOn($event, App::Phase(), $callback)`](../reference/Core-App.md)） |
| `globalOn($event, $phase, $callback)`             | 绑定到指定相位（`''` = 根相位）                                                                      |
| `fire($event, ...$args)`                          | 按注册顺序逐个调用回调；**无返回值**                                                                     |
| `all()`                                           | 返回整个注册表（排错用）                                                                             |
| `remove($event, $phase = null, $callback = null)` | 不传后两者则清空该事件；否则按相位+回调过滤                                                                   |

### Helper 侧入口

工程的 `Helper`（[`System\SystemHelper`](../reference/Foundation-System-SystemHelper.md) / [`Business\BusinessHelper`](../reference/Foundation-Business-BusinessHelper.md) / [`Controller\ControllerHelper`](../reference/Foundation-Controller-ControllerHelper.md)）都提供了同名静态方法，语义一致：

```php
Helper::OnGlobalEvent('third.ordered', function ($order_id) { /* … */ });  // = GlobalEvent::_()->on(...)
Helper::FireGlobalEvent('third.ordered', $order_id);                       // = GlobalEvent::_()->fire(...)
Helper::RemoveEvent('third.ordered');                                      // = GlobalEvent::_()->remove(...)
```

### 事件名约定：「进行中 / 已完成」后缀

框架内置的事件名是**常量**，定义在两处，值就是常量名本身：

- [`User`](../reference/GlobalUser-User.md)：`EVENT_ACTION_USER_REGISTERING` / `_REGISTERED` / `_LOGINING` / `_LOGINED` / `_LOGOUTING` / `_LOGOUTED`（值形如 `'ACTION_USER_LOGINED'`）与 `EVENT_SERVICE_USER_*`（同六个）；
- [`Admin`](../reference/GlobalAdmin-Admin.md)：`EVENT_ACTION_ADMIN_LOGINING` / `_LOGINED` / `_LOGOUTING` / `_LOGOUTED` 与 `EVENT_SERVICE_ADMIN_LOGINED` 等四个（管理员没有注册）。

两组的分工：

| 组 | 谁派发 | 用途 |
|---|---|---|
| `EVENT_ACTION_*`（Action＝动作） | 框架派发：[`GlobalUser`](../reference/GlobalUser-GlobalUser.md) 的 `register()/login()/logout()`、[`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md) 的 `login()/logout()` | 监听「这次登录/注册/登出发生了什么」，例如登录后发站内信 |
| `EVENT_SERVICE_*`（Service＝服务） | **框架不派发**，是你的登录服务自己 `fire()` 的名字 | 给你在 Service 层细分「校验前 / 落库后」这类阶段用（`src/Foundation/Business/BusinessHelper.php` 只提供同名别名常量） |

在 Action 层监听（登录成功做善后）：

```php
Helper::OnGlobalEvent('ACTION_USER_LOGINED', function ($post) {
    // $post 就是传给 login() 的那份数据
});
Helper::OnGlobalEvent('ACTION_ADMIN_LOGOUTED', function ($admin_id) {
    // 登出后拿到的是管理员 id
});
```

在 Service 层自己派发（框架只给名字）：

```php
use DuckPhp\Component\GlobalEvent;
use DuckPhp\GlobalUser\User;

GlobalEvent::_()->fire(User::EVENT_SERVICE_USER_REGISTERING, $post);
// …校验、落库…
GlobalEvent::_()->fire(User::EVENT_SERVICE_USER_REGISTERED, $post);
```

> 事件名是**字符串常量**，用字面量 `'ACTION_USER_LOGINED'` 也能通，但建议用 `User::EVENT_ACTION_USER_LOGINED` 这类常量（改名时能立刻发现引用点）。

**约定**：`xxxING` 表示「正在进行中」（还可以干预），`xxxED` 表示「已完成」（做善后）。自定义事件请沿用同一后缀，例如 `order.creating` / `order.created`。

### 与第 2-4 章路由钩子的分工

| | 钩子（[Route](../reference/Core-Route.md) Hook） | 事件（[GlobalEvent](../reference/Component-GlobalEvent.md)） |
|---|---|---|
| 结构 | **单链**：一个位置一个回调串 | **广播**：一个事件多个监听 |
| 返回值 | **有返回值**，返回真值可短路后续钩子 | **无返回值**，`fire()` 始终逐个调完 |
| 典型用途 | 拦截/重写/映射请求（如 [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md)） | 通知「某件事发生了」（如下单成功） |
| 跨相位 | 否（在路由当前相位内执行） | 是（回调在注册时的相位里执行） |
| 配置入口 | `Route::addRouteHook()` / 选项 `ext` | 选项 `ext` 里打开 `GlobalEvent::class => true` |

一句话：**要拦截请求用钩子，要广播状态用事件**。详见 [第 2-4 章](route-hooks.md)。

### `Ext\EventManager`（**不推荐**）

⚠️ **新代码别用它**：API 与 `GlobalEvent` 类似，但**不带相位概念**（跨相位事件处理不了），框架内部也没有任何引用。要用事件就用 `GlobalEvent`（本章其余部分）——完整理由见[第 4-13 章](ext-classes.md) §11。

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

| 现象                                      | 原因                                          | 改法                                                                 |
| --------------------------------------- | ------------------------------------------- | ------------------------------------------------------------------ |
| 监听不到任何事件                                | `GlobalEvent` 没打开（默认 `EXT_DISABLE`）         | 在根应用 `ext` 里声明 `GlobalEvent::class => true`                        |
| 事件回调里 `::_()` 拿错实例                      | `fire()` 会把相位切到「注册时的相位」再执行回调                | 注册时就选对相位（`globalOn` 的第二个参数）                                        |
| 重复注册导致回调执行多次                            | 同一事件+相位+回调三元组已存在时 `globalOn` 会跳过；但不同闭包算不同回调 | 排重时用同一个 callable（如 `[Class::class, 'method']`），不要每次 `fire` 前都 `on` |
| 想让事件「拦截」后续流程                            | 事件是广播、无返回值、不可短路                             | 改用路由钩子（[第 2-4 章](route-hooks.md)）或直接在业务里判断                          |
| `remove($event, $phase, $callback)` 删不掉 | 源码的过滤条件是「相位与回调**都**不同才保留」                   | 传入与注册时**完全一致**的相位与回调                                               |

## 下一步

- [第 2-4 章 路由钩子](route-hooks.md)：钩子的单链/短路语义，与事件的分工。
- [第 2-14 章 缓存与 Redis](cache.md)：事件回调里如果要写共享状态，缓存是可靠的去处。
- 参考手册：[DuckPhp\Component\GlobalEvent](../reference/Component-GlobalEvent.md)、[DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md)。
