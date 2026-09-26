# 2-4 路由钩子

> 解决什么问题：要在请求进入控制器**之前**拦住它、在没命中时**兜底**、或者在请求收尾时做统计——用路由钩子，而不是去继承控制器基类。
> 前置：[第 2-2 章 请求生命周期](lifecycle.md)（钩子插在时序的哪个位置）、[第 2-3 章 路由进阶](routing.md)。预计 20 分钟。
> 示例：`demo/src/System/App.php`（真实接线）、`tests/Ext/MyMiddlewareManagerTest.php`（中间件实跑，含洋葱顺序）。
> 想跑一遍：`wsl -e bash -lc "php vendor/bin/phpunit --no-coverage tests/Ext/MyMiddlewareManagerTest.php"`（**在仓库根目录下**跑）

## 最小示例

挂一个钩子，把 `/health` 从控制器手里「抢过来」：

```php
use DuckPhp\Core\Route;

Route::_()->addRouteHook(function (string $path_info) {
    if ($path_info !== 'health') {
        return false;          // 不处理，交给后面的钩子和默认路由
    }
    header('Content-Type: text/plain');   // 也可用 Helper::header()（可替换的系统包装，便于测试）
    echo 'ok';
    return true;               // 命中 → 路由到此为止，控制器不会执行
}, 'prepend-outter');
```

请求 `/health` 输出 `ok`、控制器完全不执行；请求其它路径时什么都不发生。这就是「钩子取代继承」的最小形态——不用为了两个特例去改控制器基类。

## 机制说明

### 1. 六个位置与短路语义

`Route::addRouteHook($callback, $position = 'append-outter', $once = true)`：

| 位置 | 落在哪个链表 | 相对默认路由 | 语义 |
|---|---|---|---|
| `prepend-outter` | pre（**最前**） | 之前 | 最先跑：状态检查、URL 重写、鉴权拦截 |
| `prepend-inner` | pre（最后） | 之前 | 靠近路由：路由映射 |
| `append-inner` | post（最前） | 之后 | 兜底第一顺位 |
| `append-outter` | post（最后） | 之后 | 最后的兜底（静态资源） |
| `finally-inner` | finally（最前） | 请求收尾 | `Route::clear()` 时跑 |
| `finally-outter` | finally（最后） | 请求收尾 | 同上，最后跑 |

**短路是 pre 钩子的核心语义**：pre 链上任何一个钩子返回真值，[`Route::_()->run()`](../reference/Core-Route.md) 立刻返回、控制器不再执行。返回假值（`false`/`null`）就是「我不处理，继续往下」。

另外两个开关（都在 `Route` 上）：

```php
Route::_()->defaultToggleRouteCallback(false); // 关掉默认路由回调（自己接管路由）
Route::_()->forceFail();                       // 强制本次路由算失败（让上层走 404/兜底）
```

子应用常用 `App::_()->skip404Handler()`：让没命中的子应用不要抢着输出 404，交给父应用决定。

### 2. 钩子的增删改查

```php
use DuckPhp\Core\Route;
use DuckPhp\Ext\RouteHookManager;

Route::_()->addRouteHook($cb, 'prepend-inner');          // 直接挂（默认 append-outter）
Helper::addRouteHook($cb, 'prepend-inner');              // 应用/接线层的 Helper（System\SystemHelper）

RouteHookManager::_()->attachPreRun()                    // 拿 pre 链的引用，然后…
    ->append([MyHook::class, 'Hook'])                    // 追加到末尾
    ->insertBefore($new, $old)                           // 插到某个钩子前面
    ->moveBefore($new, $old)                             // 已有钩子挪位
    ->removeAll($old);                                   // 去掉某个钩子
RouteHookManager::_()->attachPostRun();                  // 换成 post 链
RouteHookManager::_()->dump();                           // ★ 排查：把三条链打印出来
```

排查顺序建议：先 `dump()` 看链上到底有哪些钩子、什么顺序，再怀疑自己的回调没被调用。

### 3. 内置钩子都挂在哪

框架自己的路由能力也是钩子，位置如下（都可用 `dump()` 看到）：

| 钩子 | 位置 | 作用 |
|---|---|---|
| [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | `prepend-outter` | PATH_INFO 兼容（`?_r=` 形式，[第 2-3 章](routing.md)） |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) | `prepend-outter` | URL 重写 |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | `prepend-inner` + `append-outter` | 路由映射（前段匹配 + 后段兜底） |
| [`RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md)（扩展） | `prepend-inner` | API 服务 |
| [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md)（扩展） | `prepend-inner` | Web 安装流程（[第 3-6 章](installer.md)） |
| [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md)（扩展） | `append-inner` | 函数式路由 |
| [`RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md)（扩展） | `prepend-outter` | 目录模式（多入口） |
| [`RouteHookResource`](../reference/Component-RouteHookResource.md) | `append-outter` | 静态资源代发（[第 3-3 章](static-resources.md)） |

> 表里标**（扩展）**的四行属于 `Ext\*`：**`Ext\` 下的组件不会自动装配**，必须写进应用的 `ext`（如 `'ext' => [\DuckPhp\Ext\RouteHookFunctionRoute::class => true]`）才会挂上。注意这与「类能不能被加载」是两件事：AutoLoader 只负责按需把类文件载进来，**装配进当前相位**要靠 `ext` 声明。不标（扩展）的几行是框架默认已装的（`src/DuckPhp.php` 的 `common_options['ext']` 里：`Lang`、`RouteHookRewrite`、`RouteHookRouteMap`、`RouteHookResource`、`RouteHookPathInfoCompat`）。

### 4. 选型：到底该用哪种介入方式

| 需求 | 首选 | 为什么 |
|---|---|---|
| 某几个 URL 特例处理、拦截 | 路由钩子（pre） | 有短路语义，能真正拦住 |
| 请求前后的对称逻辑 | 中间件（兼容扩展，见第 5 节） | 洋葱结构天生适合「前/后」 |
| 改某个控制器的行为 | 覆盖控制器类 / `controller_class_map`（[第 3-5 章](overriding.md)） | 精确到类，配置即生效 |
| 广播「发生了某事」 | 全局事件（[第 2-13 章](events.md)） | 一对多、无返回值、可跨相位 |
| 换掉框架某个能力 | 覆盖扩展 / 替换单例 | 从装配层解决（[第 4-3 章 替换框架行为](replace-behavior.md)） |
| 统一给所有控制器加东西 | **先想钩子**，其次才是继承基类 | 继承会把「可变的能力」变成「不可变的血缘」 |

### 5. 兼容性扩展：洋葱中间件（`Ext\MyMiddlewareManager`）

说清楚定位：**中间件不是 DuckPHP 的主推路数**。框架的默认做法是「路由钩子 + 分层 Helper」，中间件只是给习惯了 Laravel / ThinkPHP 的中间件写法（或 PSR-15）的人留的一层兼容，用得上就用，用不上不用管。

它的接线方式（选项写在应用的 `$options` 里）：

```php
$options = [
    'ext' => [
        \DuckPhp\Ext\MyMiddlewareManager::class => true,
    ],
    // 自外向内：列表里**第一个是最外层**
    'middleware' => [
        \MyProj\Middleware\AuthMiddleware::class . '@handle',   // 走 Class::_() 单例
        \MyProj\Middleware\LogMiddleware::class . '->handle',   // 走 new Class()
        'MyProj\Middleware\StaticMiddleware::handle',           // 原生可调用字符串
        function ($request, \Closure $next) { /* callable 也行 */ },
    ],
];
```

中间件的签名是 `handle($request, \Closure $next)`：`$request` 由管理器给你（默认是个空 `\stdClass`，可以用子类改成自己的请求对象），`$next($request)` 就是「继续往里走」，返回值是内层的结果。

挂上之后，插在内置钩子的最内层（[`RouteHookManager::_()->attachPreRun()->append()`](../reference/Ext-RouteHookManager.md)），洋葱顺序实测如下（`tests/Ext/MyMiddlewareManagerTest.php` 里的 X/Y/Z 三个中间件）：

```
P1>  P2>  CTRL#1  P2<  P1<        ← 列表第一个最外层；控制器只执行一次
```

**这里有一个必须知道的坑**（实测，框架当前行为）：中间件里**不调 `$next` 直接 `return` 响应，是拦不住请求的**——`doHook()` 只认最内层 `runSelfMiddleware()` 的结果，短路时它仍是 `false`，于是 `Route::run()` 会**自己再跑一次默认路由回调**，结果是：

- 控制器**照样执行**（你返回的那个响应被丢弃）；
- 如果控制器本来就不存在，用户看到的是 **404**，而不是你在中间件里返回的内容。

所以：

| 你想做的事 | 该用什么 |
|---|---|
| 请求前后做对称处理（计时、日志、统一加响应头） | 洋葱中间件（它的强项） |
| **拦截请求**（鉴权不通过就直接返回/跳转） | **路由钩子**：`prepend-outter` 里返回 `true` |
| 更细的接管（换请求/响应对象、自己的收尾） | 继承 [`MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) 覆盖 `getRequest()`/`getResponse()`/`runSelfMiddleware()`/`onPostMiddleware()` |

细节见参考手册 [DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md)。

### 6. 附：`Ext\HookChain`（命中即停的链）

[`DuckPhp\Ext\HookChain`](../reference/Ext-HookChain.md) 是一个小工具类：把一串回调装成对象，`__invoke()` 时按顺序执行、**遇到返回真值的就断**，并实现 `ArrayAccess` 可直接当数组读写。

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add($callback1, true, true);    // (回调, 追加?, 去重?)
$chain[] = $callback2;                  // ArrayAccess 追加
$chain();                               // 顺序执行，遇真值 break

HookChain::Hook($target, $callback3);   // 便捷：把已有回调/null 与新回调并成一条链写回 $target
```

它**没有被框架内部使用**（框架自己的钩子走 `Route` 的三个链表），属于「你想在自己的代码里表达『一组钩子、命中即停』时」的可选工具，而且源码里已标 `@todo deprecate`（[第 4-11 章](deprecated-exts.md)）。参考页：[DuckPhp\Ext\HookChain](../reference/Ext-HookChain.md)。

## 常见写法

**① 用 pre 钩子做拦截**（IP 白名单、强制改道、灰度分流）：

```php
Route::_()->addRouteHook(function (string $path_info) {
    if (strpos($path_info, 'admin/') !== 0) { return false; }   // 只管后台
    $ip = App::_()->isCli() ? '' : ($_SERVER['REMOTE_ADDR'] ?? '');
    if (preg_match('/^10\./', $ip)) { return false; }            // 内网放行，继续走默认路由
    header('HTTP/1.1 403 Forbidden', true, 403);
    echo 'Forbidden';
    return true;                                                 // 拦住：控制器不会执行
}, 'prepend-outter');
```

> 「维护模式」不需要自己写钩子：框架已内置——`is_maintain` 选项或设置项 `duckphp_is_maintain`，配 `error_maintain` 指向错误视图（`demo/view/_sys/error_maintain.php` 就是现成的）。

**② 用 append/finally 钩子做兜底与收尾**：post 链适合自定义 404、动态资源；`finally` 链适合「无论成败都要做」的统计与清理，例如：

```php
Route::_()->addRouteHook(function () {
    // 请求收尾：无论命中、404 还是抛异常都会跑到（第 2-2 章的 finally）
    MyMetrics::_()->flush();
    return false;
}, 'finally-outter');
```

**③ 排查钩子顺序**：

```php
echo RouteHookManager::_()->dump();   // 三个链表全打印
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 钩子里 `return;` 却发现控制器还是执行了 | pre 钩子必须返回**真值**才算命中 | 明确写 `return true;` |
| 钩子被挂了两次、日志出现两遍 | 重复调用 `addRouteHook()` | 用第三个参数 `$once = true`（默认已开），或先 `RouteHookManager::_()->removeAll()` |
| 中间件里 `return` 了响应，页面却是 404 或控制器照跑 | 短路对中间件无效（第 5 节的坑） | 拦截改用路由钩子并 `return true`；中间件只做前后置装饰 |
| `Ext\` 下的钩子写了却完全没反应 | `Ext\` 组件不会自动装配 | 在应用 `ext` 里声明（如 `'ext' => [RouteHookFunctionRoute::class => true]`） |
| post 钩子里的 404 视图被别人的 404 抢先输出 | 子应用先兜底了 | 子应用里 `App::_()->skip404Handler()`，交给父应用决定 |
| 想知道「这条请求到底被谁处理了」 | 三个链表都是动态的 | 先 `RouteHookManager::_()->dump()`，再怀疑自己的回调 |
| 钩子在 CLI 下也跑了 | 钩子挂在路由上，`execute()` 支线里也可能触发路由 | 用 `App::_()->isCli()` 区分（[第 2-16 章](cli.md)） |

## 下一步

- [第 2-2 章 请求生命周期](lifecycle.md)：钩子插在时序的哪一步、`finally` 为什么一定跑。
- [第 2-3 章 路由进阶](routing.md)：`route_map`/`route_map_important` 与钩子的关系。
- [第 2-13 章 事件系统](events.md)：广播式介入点，与钩子的分工。
- [第 3-5 章 重写与覆盖](overriding.md)：不写钩子也能换掉某个控制器的实现。
- 参考手册：[DuckPhp\Core\Route](../reference/Core-Route.md)、[DuckPhp\Ext\RouteHookManager](../reference/Ext-RouteHookManager.md)、[DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md)、[DuckPhp\Ext\HookChain](../reference/Ext-HookChain.md)。
