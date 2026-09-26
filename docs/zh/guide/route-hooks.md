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

Route::_()->addRouteHook($cb, 'prepend-inner');          // 直接挂（默认 append-outter）
Helper::addRouteHook($cb, 'prepend-inner');              // 应用/接线层的 Helper（System\SystemHelper）
echo Route::_()->dumpAllRouteHooksAsString();            // ★ 排查：把三条链打印出来
```

排查顺序建议：先 dump 看链上到底有哪些钩子、什么顺序，再怀疑自己的回调没被调用。

> **想「按名字挂 / 挪位 / 摘掉」**（`append()`、`insertBefore()`、`moveBefore()`、`removeAll()`）就用 [`Ext\RouteHookManager`](../reference/Ext-RouteHookManager.md)——它是 `Ext\*` 扩展，写进应用的 `ext` 才装配；用法见[第 4-13 章](ext-classes.md) §3。

### 3. 内置钩子都挂在哪

框架自己的路由能力也是钩子，位置如下（都可用 `dump()` 看到）：

| 钩子 | 位置 | 作用 |
|---|---|---|
| [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | `prepend-outter` | PATH_INFO 兼容（`?_r=` 形式，[第 2-3 章](routing.md)） |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) | `prepend-outter` | URL 重写 |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) | `prepend-inner` + `append-outter` | 路由映射（前段匹配 + 后段兜底） |
| [`RouteHookResource`](../reference/Component-RouteHookResource.md) | `append-outter` | 静态资源代发（[第 3-3 章](static-resources.md)） |

> 这四条都在 `common_options['ext']` 里、**开箱即用**（另外还有 `Lang`）。`Ext\` 下还有四个钩子——`RouteHookApiServer`、`RouteHookWebInstaller`、`RouteHookFunctionRoute`、`RouteHookDirectoryMode`——它们**不会自动装配**，要写进应用的 `ext` 才挂上（见[第 4-13 章](ext-classes.md) §9）。注意这与「类能不能被加载」是两件事：AutoLoader 只负责按需把类文件载进来，**装配进当前相位**要靠 `ext` 声明。

### 4. 选型：到底该用哪种介入方式

| 需求 | 首选 | 为什么 |
|---|---|---|
| 某几个 URL 特例处理、拦截 | 路由钩子（pre） | 有短路语义，能真正拦住 |
| 请求前后的对称逻辑 | 中间件（[`Ext\MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md)，见[第 4-13 章](ext-classes.md) §4） | 洋葱结构天生适合「前/后」 |
| 改某个控制器的行为 | 覆盖控制器类 / `controller_class_map`（[第 3-5 章](overriding.md)） | 精确到类，配置即生效 |
| 广播「发生了某事」 | 全局事件（[第 2-13 章](events.md)） | 一对多、无返回值、可跨相位 |
| 换掉框架某个能力 | 覆盖扩展 / 替换单例 | 从装配层解决（[第 4-3 章 替换框架行为](replace-behavior.md)） |
| 统一给所有控制器加东西 | **先想钩子**，其次才是继承基类 | 继承会把「可变的能力」变成「不可变的血缘」 |

### 5. 中间件呢？（`Ext\MyMiddlewareManager`）

中间件不是 DuckPHP 的主推路数：默认做法是「路由钩子 + 分层 Helper」，中间件只是给习惯了 Laravel / ThinkPHP 写法（或 PSR-15）的人留的一层兼容。要用就在应用的 `ext` 里挂上，再把中间件写进 `middleware` 选项（列表里第一个是最外层）：

```php
$options = [
    'ext' => [\DuckPhp\Ext\MyMiddlewareManager::class => true],
    'middleware' => [\MyProj\Middleware\AuthMiddleware::class . '@handle'],
];
```

中间件里**不调 `$next`、直接 `return` 一个响应**就是短路：管理器会把响应输出出去并声明「请求已处理」——控制器不会再执行。注意 **`return null`/`false` 视为「我没处理」**，那条路会照旧放行给默认路由。所以：

- **拦截请求**（鉴权不通过就返回/跳转）→ 中间件短路 `return '内容'`，或路由钩子 `prepend-outter` 里 `return true`（见本章 §1、§2）；
- **请求前后做对称处理**（计时、日志、统一加响应头）→ 中间件；
- 短路响应的三条边界（只按字符串输出、内层跑过后不再加工、`null`/`false` 放行）、接线细节、洋葱顺序实测、可覆盖的 `getRequest()`/`getResponse()`/`runSelfMiddleware()`/`outputResponse()` 见[第 4-13 章](ext-classes.md) §4。

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
echo Route::_()->dumpAllRouteHooksAsString();   // 三条链全打印
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 钩子里 `return;` 却发现控制器还是执行了 | pre 钩子必须返回**真值**才算命中 | 明确写 `return true;` |
| 钩子被挂了两次、日志出现两遍 | 重复调用 `addRouteHook()` | 用第三个参数 `$once = true`（默认已开），或先按名字摘掉（`Ext\RouteHookManager`，[第 4-13 章](ext-classes.md) §3） |
| 中间件里 `return` 了响应，控制器还是执行了 | 返回的是 `null`/`false`（或压根没写返回值），那按「没处理」放行了 | 短路要返回响应本身或 `true`；另见[第 4-13 章](ext-classes.md) §4 的边界表 |
| `Ext\` 下的钩子写了却完全没反应 | `Ext\` 组件不会自动装配 | 在应用 `ext` 里声明（如 `'ext' => [RouteHookFunctionRoute::class => true]`） |
| post 钩子里的 404 视图被别人的 404 抢先输出 | 子应用先兜底了 | 子应用里 `App::_()->skip404Handler()`，交给父应用决定 |
| 想知道「这条请求到底被谁处理了」 | 三个链表都是动态的 | 先 `Route::_()->dumpAllRouteHooksAsString()`，再怀疑自己的回调 |
| 钩子在 CLI 下也跑了 | 钩子挂在路由上，`execute()` 支线里也可能触发路由 | 用 `App::_()->isCli()` 区分（[第 2-16 章](cli.md)） |

## 下一步

- [第 2-2 章 请求生命周期](lifecycle.md)：钩子插在时序的哪一步、`finally` 为什么一定跑。
- [第 2-3 章 路由进阶](routing.md)：`route_map`/`route_map_important` 与钩子的关系。
- [第 2-13 章 事件系统](events.md)：广播式介入点，与钩子的分工。
- [第 3-5 章 重写与覆盖](overriding.md)：不写钩子也能换掉某个控制器的实现。
- 参考手册：[DuckPhp\Core\Route](../reference/Core-Route.md)；`Ext\` 那几个（`RouteHookManager`/`MyMiddlewareManager`/`HookChain`）见[第 4-13 章](ext-classes.md)。
