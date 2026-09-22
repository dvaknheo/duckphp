# 2-10 请求生命周期与钩子点

> 解决什么问题：一次请求从入口到输出，框架在**哪些点**允许你插手；钩子怎么挂、谁先谁后、什么时候该用钩子而不是继承基类。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)、[第 2-2 章 路由进阶](routing.md)。预计 25 分钟。
> 分工：本章只讲**时序与钩子**。会话见[第 2-9 章](session.md)、异常见[第 2-11 章](exception.md)、事件见[第 2-12 章](events.md)、调试开关见[第 1-6 章](debugging.md)。
> 示例：`demo/src/System/App.php`（真实的 `onPrepare()`/`onInited()` 覆盖）与 `tests/Ext/MyMiddlewareManagerTest.php`（中间件真实跑法）。

```bash
# 看钩子实际怎么跑
wsl -e bash -lc "cd /mnt/e/ProjectGoat/DNMVCS && php vendor/bin/phpunit --no-coverage tests/Ext/MyMiddlewareManagerTest.php"
```

## 最小示例

**① 覆盖生命周期钩子**——全部接线都写在 `src/System/App.php` 里（真实文件：`demo/src/System/App.php`）：

```php
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public function onPrepare(): void
    {
        parent::onPrepare();
        // 准备阶段：此时组件还没装配完，适合改选项、挂子应用
        $this->options['app']['AdminApp'] = ['controller_url_prefix' => 'admin/'];
    }
    protected function onInited(): void
    {
        parent::onInited();
        // 一切就绪，适合注册命令、事件、路由钩子
    }
}
```

**② 挂一个路由钩子**（在 `onInited()` 里，或任何组件就绪之后）：

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

**③ 效果**：请求 `/health` 时输出 `ok`，控制器完全不执行；请求其它路径时什么都不发生。这就是「钩子取代继承」的最小形态——不用为了两个特例去改控制器基类。

## 机制说明

### 1. 启动：`init()` 的六步

`RunQuickly($options, $after_init = null)` 做的事极简：`init()` → 跑你传的 `$after_init` 回调 → CLI 下走 `execute()`，否则走 `serve()`。

`init()` 内部顺序（`src/Core/KernelTrait.php`）：

| 顺序 | 调用 | 此时能做什么 | 典型用途 |
|---|---|---|---|
| 1 | `initOptions()` | 只有选项 | 补 `namespace`/`path` |
| 2 | `initContainer()`（内含 `onAfterCreatePhases()`） | 相位容器已建 | 极少数需要改容器的场景 |
| 3 | `initException()` | 异常/错误处理器已就位 | — |
| 4 | **`onPrepare()`** | 组件**还没**装配 | 改选项、挂子应用（`app`） |
| 5 | `initComponents()` | 组件陆续装配 | 不建议在这里读组件 |
| 6 | **`onInit()`** | 组件已就绪 | 注册事件/命令/钩子 |
| 7 | `initChildren()` | 逐个初始化子应用（[第 3-1 章](advanced-phase.md)） | — |
| 8 | **`onInited()`** | 全部就绪，`is_inited = true` | 最后的接线窗口 |

注意 `onPrepare()` 在**根应用**里还有一件特殊事：框架的 [`App::onPrepare()`](../reference/Core-App.md) 会调用 `loadSetting()` 读设置文件，所以 `Setting()` 的键只在根应用、且只在 `onPrepare()` 之后可用（见[第 1-5 章](configuration.md)）。

### 2. 请求：`serve()` 的完整时序

```
serve()
 ├─ prepareServe()            切回自己的相位 + 重建 EXT_RENEW 组件
 ├─ onRequest()               ★ 每个请求都跑（子应用被父应用问到时也算一次）
 ├─ Runtime::_()->run()       可选：开启输出缓冲（选项 use_output_buffer）
 ├─ Route::_()->run()         路由三阶段（见下）
 │    ├─ pre_run_hook_list     逐个执行，**任何一个返回真值就立刻结束路由**
 │    ├─ 默认路由回调          ← 控制器方法在这里执行（enable_default_callback 为真时）
 │    └─ post_run_hook_list    默认路由没命中后的兜底（404 视图、资源等）
 ├─ runChildren()             父应用没命中 → 依次问每个子应用（第三卷）
 ├─ phaseToCurrent()          回到自己的相位
 ├─ !命中 → _On404()          404 处理（可被 error_404 选项替换，见第 2-11 章）
 ├─ 抛异常 → runException()   交给异常管理器（第 2-11 章）
 └─ finally                   phaseToCurrent() + Route::_()->clear() + Runtime::_()->clear()
                              ↑ Route::clear() 里跑 finally_run_hook_list
```

三个容易忽略的点：

- **`onRequest()` 不是「每个进程一次」而是「每个应用一次」**：父应用没命中会把请求交给子应用，子应用的 `serve()` 又会跑一次自己的 `onRequest()`。
- **[`Route::clear()`](../reference/Core-Route.md) 在 `finally` 里**，所以 `finally-inner`/`finally-outter` 钩子一定会执行（包括异常路径），适合做收尾、清理、统计上报。
- **`run()` 是 `serve()` 与 `execute()` 的分流点**：`cli_enable` 为真且是 CLI 时走 [`Console::_()->run()`](../reference/Core-Console.md)（[第 2-15 章](cli.md)），Web 走 `serve()`。判断当前形态用 `App::_()->isCli()`，别去猜 `PHP_SAPI`。

### 3. 输出：`onBeforeOutput()`

`App::_Show()` 与 404/500 的错误视图路径都会先调 `onBeforeOutput()`，再交给 [`View`](../reference/Core-View.md) 渲染。它是**输出前最后一个钩子**，适合统一注入变量、埋点、或最后修改响应头。它会**被调用多次**（正常输出一次；错误视图路径各自一次），所以里面别写「只该跑一次」的逻辑。

### 4. 路由钩子的六个位置与短路语义

`Route::addRouteHook($callback, $position = 'append-outter', $once = true)`：

| 位置 | 落在哪个链表 | 相对默认路由 | 语义 |
|---|---|---|---|
| `prepend-outter` | pre（**最前**） | 之前 | 最先跑：状态检查、URL 重写 |
| `prepend-inner` | pre（最后） | 之前 | 靠近路由：路由映射 |
| `append-inner` | post（最前） | 之后 | 兜底第一顺位 |
| `append-outter` | post（最后） | 之后 | 最后的兜底（静态资源） |
| `finally-inner` | finally（最前） | 请求收尾 | `Route::clear()` 时跑 |
| `finally-outter` | finally（最后） | 请求收尾 | 同上，最后跑 |

**短路是 pre 钩子的核心语义**：pre 链上任何一个钩子返回真值，`Route::run()` 立刻返回、控制器不再执行。返回假值（`false`/`null`）就是「我不处理，继续往下」。

另外两个开关（都在 `Route` 上）：

```php
Route::_()->defaultToggleRouteCallback(false); // 关掉默认路由回调（自己接管路由）
Route::_()->forceFail();                       // 强制本次路由算失败（让上层走 404/兜底）
```

子应用常用 `App::_()->skip404Handler()`：让没命中的子应用不要抢着输出 404，交给父应用决定。

### 5. 钩子的增删改查

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

### 6. 内置钩子都挂在哪

框架自己的路由能力也是钩子，位置如下（都可用 `dump()` 看到）：

| 钩子                                                                             | 位置                                | 作用                                            |
| ------------------------------------------------------------------------------ | --------------------------------- | --------------------------------------------- |
| [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | `prepend-outter`                  | PATH_INFO 兼容（`?_r=` 形式，[第 2-2 章](routing.md)） |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md)               | `prepend-outter`                  | URL 重写                                        |
| [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md)             | `prepend-inner` + `append-outter` | 路由映射（前段匹配 + 后段兜底）                             |
| [`RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md)（扩展）             | `prepend-inner`                   | API 服务                                        |
| [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md)（扩展）       | `prepend-inner`                   | Web 安装流程（[第 3-6 章](installer.md)）             |
| [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md)（扩展）     | `append-inner`                    | 函数式路由                                         |
| [`RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md)（扩展）     | `prepend-outter`                  | 目录模式（多入口）                                     |
| [`RouteHookResource`](../reference/Component-RouteHookResource.md)             | `append-outter`                   | 静态资源代发（[第 3-3 章](static-resources.md)）        |
//TODO 说明 Ext 的组件是不会自动加载的
### 7. 兼容性扩展：洋葱中间件（`Ext\MyMiddlewareManager`）

说清楚定位：**中间件不是 DuckPHP 的主推路数**。框架的默认做法是「路由钩子 + 分层 Helper」，中间件只是给习惯了 Laravel/PSR-15 那种写法的人留的一层兼容，用得上就用，用不上不用管。

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

### 8. 附：`Ext\HookChain`（命中即停的链）

[`DuckPhp\Ext\HookChain`](../reference/Ext-HookChain.md) 是一个小工具类：把一串回调装成对象，`__invoke()` 时按顺序执行、**遇到返回真值的就断**，并实现 `ArrayAccess` 可直接当数组读写。

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add($callback1, true, true);    // (回调, 追加?, 去重?)
$chain[] = $callback2;                  // ArrayAccess 追加
$chain();                               // 顺序执行，遇真值 break

HookChain::Hook($target, $callback3);   // 便捷：把已有回调/null 与新回调并成一条链写回 $target
```

它**没有被框架内部使用**（框架自己的钩子走 `Route` 的三个链表），属于「你想在自己的代码里表达『一组钩子、命中即停』时」的可选工具。参考页：[DuckPhp\Ext\HookChain](../reference/Ext-HookChain.md)。

### 9. 选型：到底该用哪种介入方式

| 需求 | 首选 | 为什么 |
|---|---|---|
| 某几个 URL 特例处理、拦截 | 路由钩子（pre） | 有短路语义，能真正拦住 |
| 请求前后的对称逻辑 | 中间件（兼容扩展） | 洋葱结构天生适合「前/后」 |
| 改某个控制器的行为 | 覆盖控制器类 / `controller_class_map`（[第 3-5 章](overriding.md)） | 精确到类，配置即生效 |
| 广播「发生了某事」 | 全局事件（[第 2-12 章](events.md)） | 一对多、无返回值、可跨相位 |
| 换掉框架某个能力 | 覆盖扩展 / 替换单例 | 从装配层解决（[第 4-3 章 替换框架行为](replace-behavior.md)） |
| 统一给所有控制器加东西 | **先想钩子**，其次才是继承基类 | 继承会把「可变的能力」变成「不可变的血缘」 |

## 常见写法

**① 在 `onInited()` 里注册一切接线**（命令、事件、钩子）——此时组件已就绪，比 `onPrepare()` 安全。

**② 用 `onRequest()` 做请求级初始化**，并注意它每个应用都会跑一次：

```php
protected function onRequest(): void
{
    parent::onRequest();
    if ($this->isCli()) { return; }   // CLI 下不跑 Web 专属逻辑
    // 例如：按域名切换设置、初始化租户上下文
}
```

**③ 用 pre 钩子做拦截**（IP 白名单、强制改道、灰度分流）：

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

**④ 用 append/finally 钩子做兜底与收尾**：post 链适合自定义 404、动态资源；`finally` 链适合「无论成败都要做」的统计与清理。

**⑤ 排查钩子顺序**：

```php
echo RouteHookManager::_()->dump();   // 三个链表全打印
```

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 覆盖 `onBeforeRun()`/`onAfterRun()` 完全不生效 | 这两个方法**不存在**（框架里没有这两个钩子） | 实际可用的是 `onAfterCreatePhases()`、`onPrepare()`、`onInit()`、`onInited()`、`onRequest()`、`onBeforeOutput()` |
| 中间件里 `return` 了响应，页面却是 404 或控制器照跑 | 短路对中间件无效（见 §7 的坑） | 拦截改用路由钩子并 `return true`；中间件只做前后置装饰 |
| 钩子里 `return;` 却发现控制器还是执行了 | pre 钩子必须返回**真值**才算命中 | 明确写 `return true;` |
| 钩子被挂了两次、日志出现两遍 | 重复调用 `addRouteHook()` | 用第三个参数 `$once = true`（默认已开），或先 `RouteHookManager::_()->removeAll()` |
| `onPrepare()` 里读组件报错/读不到 | 组件此时还没装配完 | 改选项放 `onPrepare()`，读组件放 `onInit()`/`onInited()` |
| `onBeforeOutput()` 里的逻辑跑了两次 | 错误视图路径也会调用它 | 用标志位判断，或把「只跑一次」的逻辑放到 `onRequest()` |
| 用 `PHP_SAPI === 'cli'` 判断形态，子应用里判断错了 | 形态应由应用统一判断 | 用 `App::_()->isCli()` |
| 开了 `use_output_buffer` 之后 `header()` 报「已发送输出」 | 输出缓冲会改变响应时序 | 需要发头的地方先 `header()`，或关掉缓冲 |

## 下一步

- [第 2-11 章 异常与错误处理](exception.md)：`_On404()`/`runException()` 之后的完整流程。
- [第 2-12 章 事件系统](events.md)：广播式的介入点，和钩子的分工。
- [第 2-15 章 命令行与定时任务](cli.md)：`execute()` 这条支线。
- [第 3-1 章 应用树与相位基础](advanced-phase.md)：`runChildren()` 背后的多应用机制。
- 参考手册：[DuckPhp\Core\Route](../reference/Core-Route.md)、[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Ext\RouteHookManager](../reference/Ext-RouteHookManager.md)、[DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md)。
