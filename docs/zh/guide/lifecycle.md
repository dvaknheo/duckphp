# 2-2 请求生命周期

> 解决什么问题：一次请求从入口到输出，框架内部按什么顺序做了哪些事、**默认给你装了哪些内置组件**、你想插手时该覆盖哪个方法。
> 前置：[第 2-1 章 四层架构与调用规范](layers.md)。预计 20 分钟。
> 分工：本章只讲**时序与内置组件**。要「拦请求」看[第 2-4 章 路由钩子](route-hooks.md)；异常见[第 2-12 章](exception.md)、事件见[第 2-13 章](events.md)、CLI 支线见[第 2-16 章](cli.md)。
> 示例：`demo/src/System/App.php`（真实的 `onPrepare()`/`onInited()` 覆盖）。

## 最小示例

**① 覆盖生命周期方法**——全部接线都写在 `src/System/App.php` 里（真实文件：`demo/src/System/App.php`）：

```php
namespace MyProj\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public function onPrepare(): void
    {
        parent::onPrepare();
        // 准备阶段：组件还没装配，只能改选项、挂子应用
        $this->options['app']['AdminApp'] = ['controller_url_prefix' => 'admin/'];
    }
    protected function onInit(): void
    {
        parent::onInit();
        // 组件已就绪：注册事件、（必要时）路由钩子放这里
    }
    protected function onInited(): void
    {
        parent::onInited();
        // 全部就绪：注册命令、做最后的接线
    }
}
```

**② 看这个相位里到底有哪些组件**——排查时第一招，比读源码快：

```php
\DuckPhp\Core\PhaseContainer::_()->dumpAllObject();   // 打印当前相位容器里的实例清单
```

下面第 2 节的组件表可以拿它的输出现场核对（`tests/Core/PhaseContainerTest.php` 里就是这么打印的）。

## 机制说明

### 1. 启动：`init()` 的八步

`RunQuickly($options, $after_init = null)` 做的事极简：`init()` → 跑你传的 `$after_init` 回调 → CLI 下走 `execute()`，否则走 `serve()`。

`init()` 内部顺序（`src/Core/KernelTrait.php`）：

| 顺序 | 调用 | 此时能做什么 | 典型用途 |
|---|---|---|---|
| 1 | `initOptions()` | 只有选项 | 补 `namespace`/`path` |
| 2 | `initContainer()`（内含 `onAfterCreatePhases()`） | 相位容器已建 | 极少数需要改容器的场景 |
| 3 | `initException()` | 异常/错误处理器已就位 | — |
| 4 | **`onPrepare()`** | 组件**还没**装配 | 改选项、挂子应用（`app`） |
| 5 | `initComponents()` | 组件陆续装配（见第 2 节） | 不建议在这里读组件 |
| 6 | **`onInit()`** | 组件已就绪 | 注册事件/命令/钩子 |
| 7 | `initChildren()` | 逐个初始化子应用（[第 3-1 章](advanced-phase.md)） | — |
| 8 | **`onInited()`** | 全部就绪，`is_inited = true` | 最后的接线窗口 |

注意 `onPrepare()` 在**根应用**里还有一件特殊事：框架的 [`App::onPrepare()`](../reference/Core-App.md) 会调用 `loadSetting()` 读设置文件，所以 `Setting()` 的键只在根应用、且只在 `onPrepare()` 之后可用（见[第 1-5 章](configuration.md)）。

### 2. 装配：框架默认给你装了哪些组件

第 5 步 `initComponents()` 分三层装配，**装配范围（哪些相位共用一套）是理解后续一切行为的前提**：

**① root 层**——只有根应用装配这一批，而且它们的类名会被登记成「公共类」，**各相位 `::_()` 拿到的都是根应用里那一个实例**：

| 组件                                                                                                            | 装配方式                                                                               | 作用                                                                      |
| ------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------- | ----------------------------------------------------------------------- |
| [`Console`](../reference/Core-Console.md)                                                                     | 跟随应用选项                                                                             | CLI 命令根（[第 2-16 章](cli.md)）                                             |
| [`SystemWrapper`](../reference/Core-SystemWrapper.md)                                                         | 只建实例、不 `init()`                                                                    | 可替换的系统调用包装（`header`/`exit`/`setcookie`…，[第 4-3 章](replace-behavior.md)） |
| [`Logger`](../reference/Core-Logger.md)                                                                       | 只建实例、不 `init()`                                                                    | 日志                                                                      |
| [`CoreHelper`](../reference/Core-CoreHelper.md)                                                               | 只建实例、不 `init()`                                                                    | `Helper::` 静态门面的实现体                                                     |
| [`DbManager`](../reference/Component-DbManager.md) / [`RedisManager`](../reference/Component-RedisManager.md) | **只占位**；有 `database`/`redis`（或 setting 里的 `database_list`/`redis_list`）时才 `init()` | 连接管理（[第 2-7 章](database.md)、[第 2-14 章](cache.md)）                       |
| [`Admin`](../reference/GlobalAdmin-Admin.md) / [`User`](../reference/GlobalUser-User.md)                      | **只占位**                                                                            | 管理员/用户体系的容器键（[第 2-19 章](user.md)、[第 2-20 章](admin.md)）                  |
| [`GlobalEvent`](../reference/Component-GlobalEvent.md)                                                        | **只占位**                                                                            | 事件总线（[第 2-13 章](events.md)）                                             |
| [`ExtOptionsLoader`](../reference/Component-ExtOptionsLoader.md)                                              | 仅 `data_file_enable` 为真时                                                           | 运行时配置文件（选项落盘）                                                           |

**② inner 层**——**每个相位各一套**（子应用有自己的路由与视图）：

| 组件 | 装配方式 | 作用 |
|---|---|---|
| [`Route`](../reference/Core-Route.md) | 跟随应用选项 | 路由（[第 2-3 章](routing.md)） |
| [`View`](../reference/Core-View.md) | 跟随应用选项 | 视图渲染（[第 2-6 章](views.md)） |
| [`Configer`](../reference/Component-Configer.md) | 默认开 | 配置读取（`config/<名>.php`，[第 1-5 章](configuration.md)） |

**③ ext 层**——应用选项 `ext` 里声明的那些。框架**默认打开**这几个：

| 组件 | 作用 |
|---|---|
| [`Lang`](../reference/Component-Lang.md) | 多语言（[第 2-15 章](i18n.md)） |
| [`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) / [`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md) / [`RouteHookResource`](../reference/Component-RouteHookResource.md) / [`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) | 四个内置路由钩子（[第 2-4 章](route-hooks.md)），最后一个受 `path_info_compact_enable` 控制 |

`ext` 里的写法（`true` / 数组 / `'@方法'` / 选项键名 / `EXT_*` 常量）与九种语义见[第 4-2 章 开发组件与扩展](custom-component.md)。

**④ 另外两样不是「组件」但会就位**：

- [`ExceptionManager`](../reference/Core-ExceptionManager.md)：在第 3 步 `initException()` 里就位，**早于组件**（[第 2-12 章](exception.md)）；
- [`Runtime`](../reference/Core-Runtime.md)：用到时才创建，`use_output_buffer` 选项的输出缓冲就靠它（[第 2-18 章](security-performance.md)）。

**读表要点**（这几条决定了「为什么我的替换在子应用里不生效」）：

- **root 与 inner 的区别就是「跨相位共享」**：`DbManager`、`Admin`/`User`、`GlobalEvent` 是 root 级的，所以在子应用里调 `Admin::_()` 拿到的仍是根应用那一份；而 `Route::_()`、`View::_()` 在子应用里是**另一个实例**。
- **「只占位」= 登记类名但不在这里创建**：框架用的装配值 `EXT_ROOT_HOLD_POSISION_ONLY`（值就是 `EXT_DISABLE`）只完成「登记成公共类」这一步，真正的创建留给第一次 `::_()`，所以没配数据库也不会白建一个 `DbManager`。
- **「只建实例、不 `init()`」= `EXT_SKIP_INIT`**：`SystemWrapper`/`Logger`/`CoreHelper` 是纯工具，没有选项要读。
- 被 `ext` 关掉的组件（例如默认不在表里的 `GlobalEvent`）**只是不装配**；`GlobalEvent::_()` 仍可用，只是它不会被框架预先 init（[第 2-13 章](events.md)）。

### 3. 请求：`serve()` 的完整时序

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
 ├─ !命中 → _On404()          404 处理（可被 error_404 选项替换，见第 2-12 章）
 ├─ 抛异常 → runException()   交给异常管理器（第 2-12 章）
 └─ finally                   phaseToCurrent() + Route::_()->clear() + Runtime::_()->clear()
                              ↑ Route::clear() 里跑 finally_run_hook_list
```

三个容易忽略的点：

- **`onRequest()` 不是「每个进程一次」而是「每个应用一次」**：父应用没命中会把请求交给子应用，子应用的 `serve()` 又会跑一次自己的 `onRequest()`。
- **[`Route::clear()`](../reference/Core-Route.md) 在 `finally` 里**，所以 `finally-inner`/`finally-outter` 钩子一定会执行（包括异常路径），适合做收尾、清理、统计上报。
- **`run()` 是 `serve()` 与 `execute()` 的分流点**：`cli_enable` 为真且是 CLI 时走 [`Console::_()->run()`](../reference/Core-Console.md)（[第 2-16 章](cli.md)），Web 走 `serve()`。判断当前形态用 `App::_()->isCli()`，别去猜 `PHP_SAPI`。

> 时序里那三个钩子链表是下一章的主角：[第 2-4 章 路由钩子](route-hooks.md)。

### 4. 输出：`onBeforeOutput()`

`App::_Show()` 与 404/500 的错误视图路径都会先调 `onBeforeOutput()`，再交给 [`View`](../reference/Core-View.md) 渲染。它是**输出前最后一个钩子**，适合统一注入变量、埋点、或最后修改响应头。它会**被调用多次**（正常输出一次；错误视图路径各自一次），所以里面别写「只该跑一次」的逻辑。

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

**③ 在 `onBeforeOutput()` 里统一注入**（所有视图都拿得到的变量、最后改响应头）：

```php
public function onBeforeOutput()          // 注意：它是 public，覆盖时别收窄成 protected
{
    parent::onBeforeOutput();
    // 应用类在 System 层，这一层没有 assignViewData()，直接用 View 组件
    \DuckPhp\Core\View::_()->assignViewData('site_name', static::Setting('site_name', 'DuckPHP'));
}
```

**④ 排错时先问三件事**：`PhaseContainer::_()->dumpAllObject()`（这个相位里有什么）、`App::_()->isCli()`（走的是哪条支线）、`App::_()->isRoot()`（我是不是根应用）。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 覆盖 `onBeforeRun()`/`onAfterRun()` 完全不生效 | 这两个方法**不存在**（框架里没有这两个钩子） | 实际可用的是 `onAfterCreatePhases()`、`onPrepare()`、`onInit()`、`onInited()`、`onRequest()`（都是 `protected`）与 `onBeforeOutput()`（**`public`**）；覆盖时可见性只能放宽、不能收窄 |
| `onPrepare()` 里读组件报错/读不到 | 组件此时还没装配完 | 改选项放 `onPrepare()`，读组件放 `onInit()`/`onInited()` |
| `onBeforeOutput()` 里的逻辑跑了两次 | 错误视图路径也会调用它 | 用标志位判断，或把「只跑一次」的逻辑放到 `onRequest()` |
| 子应用里 `Admin::_()`/`DbManager::_()` 拿到的和根应用是同一份，改了互相影响 | 这几个是 **root 级公共类**（跨相位共享） | 要每相位独立就用 inner 级组件，或自己 `new`（[第 4-1 章](container-phases.md)） |
| 以为 `GlobalEvent::_()`/`Admin::_()` 已经初始化好了 | 它们在 root 表里只是**占位** | 用之前显式装配（`ext` 里声明）或别依赖它们已被 init |
| 没配数据库，却想知道 `DbManager` 在哪 | 没配就不 init（只登记类名） | `::_()` 仍可用；要连接就配 `database`/`database_list` |
| 用 `PHP_SAPI === 'cli'` 判断形态，子应用里判断错了 | 形态应由应用统一判断 | 用 `App::_()->isCli()` |
| 开了 `use_output_buffer` 之后 `header()` 报「已发送输出」 | 输出缓冲会改变响应时序 | 需要发头的地方先 `header()`，或关掉缓冲（[第 2-18 章](security-performance.md)） |

## 下一步

- [第 2-4 章 路由钩子](route-hooks.md)：本章时序里那三个钩子链表怎么用、谁先谁后、怎么拦请求。
- [第 2-12 章 异常与错误处理](exception.md)：`_On404()`/`runException()` 之后的完整流程。
- [第 2-13 章 事件系统](events.md)：广播式的介入点。
- [第 2-16 章 命令行与定时任务](cli.md)：`execute()` 这条支线。
- [第 3-1 章 应用树与相位基础](advanced-phase.md)：`runChildren()` 背后的多应用机制。
- 参考手册：[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Core\App](../reference/Core-App.md)、[DuckPhp\Core\PhaseContainer](../reference/Core-PhaseContainer.md)。
