# 4-14 `Ext\*` 扩展类

> 解决什么问题：`src/Ext/` 下的可选组件都有什么、什么时候用得上、怎么挂上；哪些是推荐路径、哪些只是兼容层、哪些已经过时。
> 前置：[第 4-2 章 开发组件与扩展](custom-component.md)、[第 2-4 章 路由钩子](route-hooks.md)。预计 20 分钟。
> 本章是 `Ext\` 这一族的**总览**：卷二各章只讲「怎么用」（怎么开、一行示例），类名、`ext` 装配、选项与坑都集中到这里。

## 最小示例

`Ext\` 下的组件**不会自动装配**，要用就写进应用的 `ext`：

```php
// src/System/App.php
public $options = [
    'ext' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = 按默认方式装配
    ],
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // 顺带把它的 CLI 命令登记上
    ],
];
```

```bash
php bin/cli.php routes        # 装好之后就能列路由表了（第 2-16 章）
```

## 机制说明

### 1. 「不会自动装配」是什么意思

- **AutoLoader 只负责按需把类文件载进来**，这一点对所有类都一样；
- 但**装配进当前相位**（把实例放进容器、注册钩子/命令/事件）要靠 `ext` 声明——`ext` 的取值（`true` = **跟随本级应用的选项**、选项数组、`'选项键名'`、`'@方法名'`，以及 `EXT_*` 那几种）见[第 4-2 章](custom-component.md)；
- 框架**默认已装**的扩展都是 `Component\*`（`src/DuckPhp.php` 的 `common_options['ext']`：`Lang`、`RouteHookRewrite`、`RouteHookRouteMap`、`RouteHookResource`、`RouteHookPathInfoCompat`），**`Ext\` 下一个都不在里面**。

所以「写了却没反应」几乎总是这一条：类在、文件也加载了，但没写进 `ext`（[第 2-4 章](route-hooks.md)的常见错误里也有这条）。

### 2. 本章怎么分类

| 分类 | 有哪些 | 什么时候看 |
|---|---|---|
| **常用（要主动装配）** | `RouteHookManager`、`MyMiddlewareManager`、`RouteLister`、`PermissionMenu`、`SqlDumper`/`SqlDumperSupporter*`、`CallableView`/`EmptyView`/`JsonView`、`RouteHook*` 家族、`DuckPhpInstaller` | 需要中间件、路由表、后台菜单、SQL 导出、换视图实现时 |
| **兼容层（能用，不在推荐路径上）** | `EventManager`、`ExceptionWrapper`、`MyFacadesBase`/`MyFacadesAutoLoader`、`ExtendableStaticCallTrait` | 只在老代码里碰到，或明确知道自己要什么 |
| **过时与冷门** | `HookChain`、`ThrowOnTrait`、`StaticReplacer` | 在参考手册翻到它们时，知道自己站在哪一边 |
| **不是给业务用的页** | `RouteHookWebInstallerView`、`PermissionMenuMetaInterface`、`RouteHookDirectoryMode` | 改安装向导外观、菜单元数据模式、目录模式路由时 |

判断「过时」的依据只有源码里的标记，跑一次就知道当前名单：

```bash
grep -rn "@todo deprecate" src/     # 写作时命中 6 个类
```

⚠️ **「不推荐」不等于「会删」**：上表这些类都有测试兜底（覆盖率是本仓库的硬指标，[第 4-7 章](coverage.md)），1.x 里不打算移除。真正删掉的只有 `Ext\MiniRoute` 与 `Ext\Misc`（作者判定「没人用 / 不需要了」，源码、测试、参考页一起删）——所以**别照 `docs/en/` 那类陈旧副本里出现过的类名写配置**。

## 常用扩展

### 3. `Ext\RouteHookManager`：给钩子起名字、能挂能摘

[`RouteHookManager`](../reference/Ext-RouteHookManager.md) 是「钩子的具名管理层」：`Core\Route` 自己只有三个链表 + `addRouteHook($callback, $position, $once)`（[第 2-4 章](route-hooks.md)），一旦钩子多了，**谁先谁后、怎么摘掉**就不好办了。这个类把钩子登记成「名字 → 位置」，于是可以：

```php
use DuckPhp\Ext\RouteHookManager;

RouteHookManager::_()->attachPreRun()->append(MyHook::class);   // 挂到 pre 链尾
RouteHookManager::_()->moveBefore(NewHook::class, OldHook::class);
RouteHookManager::_()->removeAll('MyProj\Middleware\AuthHook'); // 按名字摘掉
echo RouteHookManager::_()->dump();                             // 打印当前钩子表
```

- `attachPreRun()` / `attachPostRun()` 选链，`append($name)` / `insertBefore()` / `moveBefore()` / `removeAll()` / `setHookList()` 调整；
- 它**也是 Ext**：要么写进 `ext`，要么在需要时手动 `RouteHookManager::_()` 调（它自己 `use` 了单例 trait）；
- 注意它管的是**名字与顺序**，钩子本体还是 `Route` 的三个链表——`getHookList()` 拿到的就是当前表。

### 4. 洋葱中间件：`Ext\MyMiddlewareManager`（兼容层）

说清楚定位：**中间件不是 DuckPHP 的主推路数**。框架的默认做法是「路由钩子 + 分层 Helper」，中间件只是给习惯了 Laravel / ThinkPHP 的中间件写法（或 PSR-15）的人留的一层兼容，用得上就用，用不上不用管。

它的接线方式（选项写在应用的 `$options` 里）：

```php
$options = [
    'ext' => [\DuckPhp\Ext\MyMiddlewareManager::class => true],
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

挂上之后，插在内置钩子的最内层（正是用 §3 的 [`RouteHookManager::_()->attachPreRun()->append()`](../reference/Ext-RouteHookManager.md) 挂的）。洋葱顺序实测如下（`tests/Ext/MyMiddlewareManagerTest.php` 里的 X/Y/Z 三个中间件）：

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
| **拦截请求**（鉴权不通过就直接返回/跳转） | **路由钩子**：`prepend-outter` 里返回 `true`（[第 2-4 章](route-hooks.md)） |
| 更细的接管（换请求/响应对象、自己的收尾） | 继承 [`MyMiddlewareManager`](../reference/Ext-MyMiddlewareManager.md) 覆盖 `getRequest()`/`getResponse()`/`runSelfMiddleware()`/`onPostMiddleware()` |

### 5. `Ext\RouteLister`：把路由表列出来

[`RouteLister`](../reference/Ext-RouteLister.md) 提供两件事：`listAll()` 扫出全部路由（调试时 `print_r(\DuckPhp\Ext\RouteLister::_()->listAll())` 就能看，[第 1-6 章](debugging.md)），以及 CLI 命令 `command_routes()`（`php bin/cli.php routes`）——**这个命令不在框架自带的那七个里**，要自己登记：

```php
// src/System/App.php
public $options = [
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = 用默认方法前缀 command_
    ],
];
```

之后 `php bin/cli.php routes --with_children=0 --only_admin=1` 就能用了（参数见参考页）；`cmd` 的值也可以写成前缀字符串（如 `'command_'`），写 `false` 或删掉就是关闭。**`Ext\PermissionMenu` 也依赖它**（§6）：菜单就是扫出来的路由。

### 6. `Ext\PermissionMenu`：后台菜单与权限树

[`PermissionMenu`](../reference/Ext-PermissionMenu.md) 用 `RouteLister` 扫出**后台控制器**（实现 [`AdminControllerInterface`](../reference/GlobalAdmin-AdminControllerInterface.md) 的类，继承 `AdminControllerBase` 就已经满足）的路由，生成菜单/权限树。三种模式（怎么标注释、怎么落盘）见[第 2-20 章 使用管理员系统](admin.md) §5——那是**用法**；这里交代它作为扩展的几件事：

- 菜单文件的路径由隐藏选项 `permission_menu_tree_for_admin` 指定（相对 `path_config`）；
- `loadAll()` 会把根应用与各子应用的菜单合并成一棵整树（**跨相位安全**）；
- 元数据模式要控制器实现 [`PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md)，`__permissionMenuMeta()` 直接返回整张表；
- 落盘模式在部署或定时任务里跑一次 `buildAndSaveToConfigJsonFile()`，运行时 `loadAdminPermissionMenu()` 读回，避免每请求扫路由。

### 7. `Ext\SqlDumper` + `SqlDumperSupporter*`：表结构/数据导成 SQL

需要把表结构/数据导出成 SQL（安装器、备份）时用 [`Ext\SqlDumper`](../reference/Ext-SqlDumper.md)，驱动细节由 [`SqlDumperSupporter*`](../reference/Ext-SqlDumperSupporter.md) 提供：

| 类 | 支持 |
|---|---|
| `Ext\SqlDumper` | 通用导出器（表前缀被写成 `{prefix}` 占位） |
| [`ByMysql`](../reference/Ext-SqlDumperSupporterByMysql.md) / [`BySqlite`](../reference/Ext-SqlDumperSupporterBySqlite.md) / [`ByPgsql`](../reference/Ext-SqlDumperSupporterByPgsql.md) | 各驱动方言；三个都**默认映射里就有**（`src/Ext/SqlDumperSupporter.php` 的 `database_driver_SqlDumperSupporter_map`，键就是 DSN 里 `:` 前面那段） |

要支持别的驱动（或换掉某个方言实现），继承 `SqlDumperSupporter` 实现两个方法，再覆盖 `database_driver_SqlDumperSupporter_map` 即可。导出的 SQL 里 `{prefix}` 表示表前缀，Web 安装流程（[第 3-6 章](installer.md)）执行时换成实际前缀。

### 8. 三种替换视图实现：`CallableView` / `EmptyView` / `JsonView`

框架的 `View` 是一个单例，可以被别的实现替换（扩展在自己的 `init()` 里做 `View::_(static::_())`，并各自留了 `*_skip_replace` 开关）。**视图这一层是第二个公开的替换点**（第一个是[第 4-3 章](replace-behavior.md)的类替换）：

| 扩展 | 视图长什么样 | 关键选项 |
|---|---|---|
| [`Ext\CallableView`](../reference/Ext-CallableView.md) | **函数/方法**：`Views::main_view($data)` | `callable_view_class`、`callable_view_header/footer`、`callable_view_is_object_call`、`callable_view_prefix` |
| [`Ext\EmptyView`](../reference/Ext-EmptyView.md) | 视图名即要输出的字符串（占位/降级） | `empty_view_key_view`、`empty_view_key_wellcome_class`、`empty_view_trim_view_wellcome` |
| [`Ext\JsonView`](../reference/Ext-JsonView.md) | 把数据直接 JSON 输出 | `json_view_skip_vars` |

挂法与「视图名怎么映射成回调」见参考页各自那篇；[第 2-6 章](views.md)只留一句「视图实现可替换、三种替代在这儿」。[`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) 的 `viewToCallback()` 就是 `CallableView` 思路的极简版（[第 4-4 章](embed.md)）。

### 9. `RouteHook*` 家族：都是要自己挂的

| 类 | 做什么 | 什么时候用 |
|---|---|---|
| [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) | 把「函数/控制器方法」直接绑到 URL（函数式路由） | 单文件小应用、`demo/public/traditional.php` 那类写法（[第 4-4 章](embed.md)） |
| [`RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md) | 目录模式（每目录一个入口） | 目录展开式部署 |
| [`RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md) | 把类方法当 API 暴露（`apiserver_*` 选项） | 快速做一个 JSON API（[第 4-6 章](multi-entry.md)） |
| [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md) | Web 安装向导（含 `web_installer_*` 选项） | 第三方应用的分发安装（[第 3-6 章](installer.md)） |

它们都**不会自动装配**：写进 `ext`（或者被别的扩展内部 `addRouteHook()`）才生效。

### 10. `Ext\DuckPhpInstaller`：建新项目

`php vendor/bin/duckphp new|show|help` 的实现（源码 `src/Ext/DuckPhpInstaller.php`），把 `skeleton/` 拷成你的工程。它是**安装器 CLI**，不是应用的命令集——用法见[第 1-2 章 安装与最小示例](install.md)。

## 兼容层（能用，不在推荐路径上）

### 11. `Ext\EventManager`

⚠️ **新代码别用它**。API 与 [`GlobalEvent`](../reference/Component-GlobalEvent.md) 类似（`OnEvent/FireEvent/AllEvents/RemoveEvent`），但**不带相位概念**——回调在哪个相位 `fire` 就在哪个相位执行，所以**跨相位事件（第三卷的多应用/子应用场景）处理不了**；框架内部也**没有任何引用**。

要用事件就用 `GlobalEvent`（[第 2-13 章](events.md)）。真需要「不带相位切换的纯进程内总线」，写一个几十行的类比引入它更可控。参考页保留：[DuckPhp\Ext\EventManager](../reference/Ext-EventManager.md)。

### 12. `Ext\ExceptionWrapper`

⚠️ **框架里已经没有内部使用者，新代码别用**。它要解决的两件事现在都有更直接的做法：

- 「一处调用不想让整条流程炸掉」→ 用 `Helper::XpCall($cb, ...$args)`（源码 `src/Core/CoreHelper.php` 的 `_XpCall()`：`try { return ($cb)(...$args); } catch (\Exception $ex) { return $ex; }`，异常当返回值拿回）；
- 「真需要处理异常」→ 直接 `try/catch` 分支处理，别把异常混进正常返回值。

老代码已经在用的可以继续用：[`ExceptionWrapper`](../reference/Ext-ExceptionWrapper.md) 把对象包一层，`__call` 里 try/catch，抛 `\Exception` 时把异常对象当返回值交回（只捕 `\Exception`，不捕 `\Error`）：

```php
$safe = ExceptionWrapper::Wrap($someClient);
$ret  = $safe->request('https://…');   // 成功→结果；抛异常→返回 $ex
$obj  = ExceptionWrapper::Release();    // 取回被包装对象
```

### 13. `Ext\MyFacadesBase` + `Ext\MyFacadesAutoLoader`：eval 出来的门面

[`MyFacadesBase`](../reference/Ext-MyFacadesBase.md) 与 [`MyFacadesAutoLoader`](../reference/Ext-MyFacadesAutoLoader.md) 是配套的：`MyFacadesAutoLoader` 注册 `spl_autoload_register()`，遇到 `facades_namespace`（默认 `MyFacades`）前缀或 `facades_map` 里的类名，就 **`eval`** 出一句 `namespace X { class Y extends MyFacadesBase {} }`（`src/Ext/MyFacadesAutoLoader.php` 55-56 行）；`MyFacadesBase::__callStatic()` 再把静态调用转发给 `getFacadesCallback()` 解析出的真实类，解析不到就抛 `\ErrorException("BadCall")`。

**为什么过时**：它要解决的是「静态调用没有 IDE 补全」——为了实现补全而**在运行时 eval 代码**。现在同样的需求用纯注释就能满足：`@method static` 标签。本仓库自己就是这么做的，[`Foundation\Helper`](../reference/Foundation-Helper.md) 与 [`DuckPhpAllInOne`](../reference/DuckPhpAllInOne.md) 上各有 96 条 `@method`（[第 2-9 章](helper.md)）。

⚠️ 反过来说：**`@method` 只是给 IDE 看的注释，`method_exists()`/反射看不到**这些方法。可调用的方法集合要看 `__callStatic()` 的分派顺序（[第 2-9 章](helper.md) 有 96 个方法的实测清单）。

### 14. `Ext\ExtendableStaticCallTrait`：动态登记静态方法

[`ExtendableStaticCallTrait`](../reference/Ext-ExtendableStaticCallTrait.md) `use` 之后类获得 `AssignExtendStaticMethod($key, $value)` / `GetExtendStaticMethodList()` / `__callStatic()`：后者的流程是「查登记表 → 取回调 → `call_user_func_array()`」。登记值可以是数组、`callable`，也可以是两种字符串简写——`Class@method`（走 `Class::_()`）或 `Class->method`（走 `new Class()`）。

**为什么过时**：这是上面门面机制的低配版，同样用 `__callStatic()` 实现了「反射看不见的方法」。现在要么直接手写 `__callStatic()`（[`Foundation\Helper`](../reference/Foundation-Helper.md) 的真实做法），要么根本不需要——用真实方法就行。框架 `src/` 里已无使用点，只有测试夹具还在用它（`tests/Core/AppTest.php` 755 行的别名 `use`）。

## 过时与冷门

### 15. `Ext\HookChain`：命中即停的链

[`HookChain`](../reference/Ext-HookChain.md) 是一个小工具类：把一串回调装成对象，`__invoke()` 时按顺序执行、**遇到返回真值的就断**，并实现 `ArrayAccess` 可直接当数组读写。

```php
use DuckPhp\Ext\HookChain;

$chain = new HookChain();
$chain->add($callback1, true, true);    // (回调, 追加?, 去重?)
$chain[] = $callback2;                  // ArrayAccess 追加
$chain();                               // 顺序执行，遇真值 break

HookChain::Hook($target, $callback3);   // 便捷：把已有回调/null 与新回调并成一条链写回 $target
```

它**没有被框架内部使用**（框架自己的钩子走 `Route` 的三个链表），属于「你想在自己的代码里表达『一组钩子、命中即停』时」的可选工具，而且源码里已标 `@todo deprecate`。要拦截请求还是用路由钩子（[第 2-4 章](route-hooks.md)）。

### 16. `Ext\ThrowOnTrait`：一行 trait

```php
trait ThrowOnTrait
{
    public static function ThrowOn($flag, $message, $code = 0)
    {
        if (!$flag) { return; }
        throw new static($message, $code);
    }
}
```

给异常类加一个「条件抛」的静态入口。**框架内部没有使用点**（`grep -rn ThrowOnTrait src/` 只命中 trait 自身），条件抛一律走分层的 [`Helper::ThrowOn()`](../reference/Foundation-Business-BusinessHelper.md)（`ProjectThrowOn`/`BusinessThrowOn`/`ControllerThrowOn`，[第 2-12 章](exception.md)）——异常类由选项集中决定，测试里也能整族换掉。要自定义异常类又想要同样的写法，直接抄这两行比 `use` 它更清楚。

### 17. `Ext\StaticReplacer`：把全局状态挪进组件

[`Ext\StaticReplacer`](../reference/Ext-StaticReplacer.md) 的三个方法都**按引用返回**，用法接近原生结构（`$v = &$sr->_GLOBALS('counter'); $v++;`）：

| 方法 | 仿真对象 |
|---|---|
| `_GLOBALS($k, $v)` | `$GLOBALS[$k]` |
| `_STATICS($name, $value, $parent)` | 函数内 `static` 变量（键由 `debug_backtrace` 的对象 hash + 类 + 函数名推出） |
| `_CLASS_STATICS($class, $var)` | 某类的静态属性（首次经反射读真值，之后返回本地副本） |

**为什么过时**：源码里就是 `@todo deprecate`（`src/Ext/StaticReplacer.php` 12 行），文件内还留着 `//TODO add Replace`（20 行）。它诞生于「测试要隔离全局状态」的年代，而本框架的答案已经换成了**相位容器 + `SingletonExTrait`**（[第 4-1 章](container-phases.md)）：要隔离状态就换相位（`App::Phase()`）或直接 `Class::_(new Class())` 换实例，不需要仿真 `$GLOBALS`。

**两个已知陷阱**（参考页也写了，动手前务必看）：`_CLASS_STATICS()` 返回的是**副本**，改它不会写回真实类静态属性；`_STATICS()` 的槽位按**调用位置**区分，同一个名字在不同函数里是两个槽。

### 18. 几个「不是给业务用」的冷门页

它们**没有过时**，只是用途固定：

| 类/文件 | 定位 | 在哪儿讲到 |
|---|---|---|
| [`Ext\RouteHookWebInstallerView`](../reference/Ext-RouteHookWebInstallerView.md) | 安装向导的**内置视图模板**（文件里没有任何 class/function，被 `RouteHookWebInstaller::show()` `include`） | [第 3-6 章](installer.md) |
| [`Ext\PermissionMenuMetaInterface`](../reference/Ext-PermissionMenuMetaInterface.md) | 菜单的元数据模式契约（§6） | [第 2-20 章](admin.md) |
| [`Ext\RouteHookDirectoryMode`](../reference/Ext-RouteHookDirectoryMode.md) | 目录模式路由（§9） | 本章 §9 |

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `Ext\` 下的组件写了却完全没反应 | `Ext\` 组件不会自动装配 | 写进应用的 `ext`（§1、[第 2-4 章](route-hooks.md)） |
| 中间件里 `return` 了响应，控制器还是执行了 | 中间件短路无效（框架当前行为） | 要拦截用路由钩子 `prepend-outter` 返回 `true`（§4） |
| `php bin/cli.php routes` 说命令不存在 | `routes` 由 `Ext\RouteLister` 提供，不在内置七命令里 | 登记进 `cmd`（§5） |
| 后台菜单空着 | 控制器没实现 `AdminControllerInterface`，或菜单文件路径没配 | 继承 `AdminControllerBase`；配 `permission_menu_tree_for_admin`（§6） |
| 换视图实现后页面没变 | 忘了 `*_skip_replace`，或没在 `init()` 里 `View::_(static::_())` | 见各扩展参考页（§8） |
| 在 `$GLOBALS`/静态属性上做隔离，测试还是串味 | 用的是 `Ext\StaticReplacer` 那套老办法 | 换相位或换实例（[第 4-1 章](container-phases.md)） |
| `ext` 里写 `DuckPhp\Ext\MiniRoute` / `DuckPhp\Ext\Misc` → 启动抛 `ext [...] not exists` | 这两个类**已经删除**（作者判定没人用 / 不需要了） | 把那行从 `ext` 里删掉：路由用 `Core\Route`（[第 2-3 章](routing.md)），引入库文件用 Composer、转义用 `__h()`、共享实例用相位容器 |

## 下一步

- [第 4-2 章 开发组件与扩展](custom-component.md)：`ext` 的取值与 `EXT_*` 模式、自己写扩展。
- [第 2-4 章 路由钩子](route-hooks.md)：钩子机制本体（本章只讲 `Ext\RouteHookManager` 这一层）。
- [第 2-9 章 Helper 与全局函数](helper.md)：`@method` 与 `__callStatic` 的推荐做法。
- [第 4-10 章 设计取舍与已知坑](design-notes.md)：哪些「看起来该改」的地方是刻意的。
- 参考手册：`Ext\*` 每类一页，见 [参考手册索引](../reference/index.md)；[DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md)、[Ext\RouteHookManager](../reference/Ext-RouteHookManager.md)、[Ext\RouteLister](../reference/Ext-RouteLister.md)、[Ext\PermissionMenu](../reference/Ext-PermissionMenu.md)、[Ext\SqlDumper](../reference/Ext-SqlDumper.md)、[Ext\CallableView](../reference/Ext-CallableView.md)。
