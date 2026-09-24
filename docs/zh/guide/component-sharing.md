# 3-4 组件共享与应用间通信

> 解决什么问题：多个应用之间，哪些东西该共用一份（数据库、日志、缓存），哪些必须各用各的（视图、语言、配置）；应用之间怎么互相调用、怎么广播事件、共享数据放哪。
> 前置：[第 3-1 章](advanced-phase.md)、[第 3-2 章](mount-app.md)。预计 20 分钟。
> 示例：`tests/data_for_tests/ZThirdDemo` 的 `/visit`、`/proxy`、`/orders` 三个动作，以及 `ZThirdDemoTest` 的对应断言。

## 一份还是各一份：先认清两种归宿

| 归宿 | 谁属于它 | 表现 |
|---|---|---|
| **共享**（public） | 根应用在**根相位**初始化的公共组件：`Logger`、[`SystemWrapper`](../reference/Core-SystemWrapper.md)、[`CoreHelper`](../reference/Core-CoreHelper.md)、[`Console`](../reference/Core-Console.md)、[`DbManager`](../reference/Component-DbManager.md)、[`RedisManager`](../reference/Component-RedisManager.md)（打开时还有 [`GlobalAdmin`](../reference/GlobalAdmin-GlobalAdmin.md)/[`GlobalUser`](../reference/GlobalUser-GlobalUser.md)/[`GlobalEvent`](../reference/Component-GlobalEvent.md)） | 在**任何**相位里 `::_()` 都拿到同一个实例 |
| **每应用一份** | 各应用自己 `init` 的组件与扩展：[`Route`](../reference/Core-Route.md)、[`View`](../reference/Core-View.md)、[`Lang`](../reference/Component-Lang.md)、[`Configer`](../reference/Component-Configer.md)、自定义扩展… | 每个相位里各有一个实例（选项也可以不同） |

实测（`ZThirdDemoTest.php`）：

```php
$root_logger = spl_object_id(Logger::_());
$root_lang   = spl_object_id(Lang::_());
App::_()->toThisChild(ThirdApp::class);              // 进子应用相位
spl_object_id(Logger::_()) === $root_logger;         // true  → 共享
spl_object_id(Lang::_())   !== $root_lang;           // true  → 各一份
App::Phase('');
```

为什么这样设计：日志/数据库「整个进程一份」才合理；而每个应用的**语言与文案、路由表、视图目录**天然该独立。

### 想让某个东西共享 / 想让它独立

```php
// 共享：在根应用的 ext 里声明（框架会把它标成 public）
'ext' => [ \DuckPhp\Component\GlobalEvent::class => true ],

// 独立：在当前相位新建一份，别用共享的那个
$this->createLocalObject(DbManager::class);          // 框架内部对 local_database 就是这么做的
```

省事的开关：子应用的 `local_database => true` / `local_redis => true`（配 `database_list`/`redis_list`）就等于「这个应用各用一份连接」。

```php
ThirdApp::class => [
    'local_database' => true,
    'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/shop.db']],
],
```

## 应用间通信：三种姿势

### ① 直接切相位（最直白）

```php
// ZThirdDemo/src/Controller/MainController.php :: visit()
$phase_before = App::Phase();
App::_()->toThisChild(ThirdApp::class);
$greet = ShopBusiness::_()->greetWho();     // ← 这里的单例属于子应用相位
App::Phase($phase_before);                  // ← 必须切回来
```

要点：`ShopBusiness::_()` 在子相位里是**另一个实例**（连同它的选项、配置解析结果都是子应用的）。

### ② 相位代理 `PhaseProxy`（不离开当前相位）

```php
// ZThirdDemo :: proxy()
$child_phase = App::_()->options['app'][ThirdApp::class]['__phase__'];   // ':shop'
$proxy = PhaseProxy::CreatePhaseProxy($child_phase, ShopBusiness::class);
$ret = $proxy->placeOrder(2002);      // 在 :shop 相位里执行，回来后相位不变
```

[`PhaseProxy::__call()`](../reference/Component-PhaseProxy.md) 会临时切到目标相位、调用、再切回。它适合「调一次就走」的场景。

> ⚠️ 传**类名**时它用 `new` 造对象（**不是**那个相位的 `::_()` 单例）。要操作相位里的单例，就传对象：`PhaseProxy::CreatePhaseProxy($phase, ShopBusiness::_())`，或者干脆用姿势 ①。

### ③ 拿到根应用（子应用视角）

```php
$root = App::Root();          // 根应用实例（不切相位）
$root = App::Root(true);      // 取根实例并把当前相位切回根
$root->options['namespace'];
```

## 事件总线：`GlobalEvent`

**它默认是关闭的**（`EXT_DISABLE`），要用先在根应用打开：

```php
'ext' => [ \DuckPhp\Component\GlobalEvent::class => true ],
```

```php
// 主应用（根相位）监听子应用的事件：ZThirdDemo/src/System/MainApp.php :: onInit()
GlobalEvent::_()->globalOn('third.ordered', '', function ($order_id) {
    self::$orders[] = $order_id;          // 真实项目里这里可能是写日志、发消息队列
});

// 子应用（子相位）广播：ZThirdDemo/third/Business/ShopBusiness.php :: placeOrder()
GlobalEvent::_()->fire('third.ordered', $order_id);
```

API 一览：

| 方法 | 作用 |
|---|---|
| `on($event, $callback)` | 绑定到**当前相位** |
| `globalOn($event, $phase, $callback)` | 绑定到指定相位（`''` = 根） |
| `fire($event, ...$args)` | 触发；**回调会在它注册时的相位里执行**，执行完自动切回 |
| `all()` | 看所有监听（排错用） |
| `remove($event, $phase = null, $callback = null)` | 取消监听 |

命名约定：事件名用「进行中 / 已完成」后缀（`registering` / `registered`、`logining` / `logined`），与框架内置事件一致（第 2-13 章）。

## 共享数据放哪：一张决策表

| 数据 | 放哪 | 说明 |
|---|---|---|
| 只读配置（各应用自己一份） | 各应用的 `config/<name>.php` | 第 3-5 章讲的按相位覆盖也在这里生效 |
| 全局设置（密码、环境） | `DuckPhpSettings.config.php` / `.env` → `Setting()` | 第 1-5 章 |
| **运行期可改写**的选项（如 `is_debug`） | [`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md)（`data_file_enable`） | 它的文件是 `DuckPhpApps.config.php`，`data_file_bump_allowed`/`data_file_bump_keys` 决定哪些键可写回 |
| 请求级临时数据 | [`Runtime`](../reference/Core-Runtime.md) | 随请求清空 |
| 跨请求的共享状态 | [`Cache`](../reference/Component-Cache.md) / [`RedisCache`](../reference/Component-RedisCache.md)，或业务自己的表 | 多应用多点部署时唯一可靠的共享方式 |

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 监听不到子应用的事件 | `GlobalEvent` 没打开（默认 `EXT_DISABLE`） | 在根应用 `ext` 里声明 |
| 事件回调里 `::_()` 拿错实例 | `fire()` 会把相位切到「注册时的相位」再执行回调 | 注册时就选对相位（`globalOn` 的第二个参数） |
| `PhaseProxy` 调用的对象没有相位里的状态 | 传类名时它 `new` 了一个新对象 | 传对象，或用 `toThisChild()` 切相位 |
| 子应用改了 `options` 影响到了主应用 | 你改的是**共享实例**的选项 | 共享组件是同一个对象；要隔离就 `local_*` 或 `createLocalObject()` |
| 两个应用各写各的日志文件 | 它们用的是同一个 `Logger`（共享） | 日志路径来自 `path_log`，想让子应用分文件就给它独立选项或独立 [Logger](../reference/Core-Logger.md) |

## 下一步

- [第 3-5 章 重写与覆盖](overriding.md)：不改对方代码换掉它的行为。
- [第 3-7 章 综合实战：前台 + 后台 + API](case-multi-app.md)：本卷所有机制的合体。
