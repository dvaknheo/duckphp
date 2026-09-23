# 4-6 多入口·多域名·多 SAPI

> 解决什么问题：同一套 `src/` 怎么被多个入口（`index.php` / `demo.php` / `api.php` / `rpc.php` / `cli.php`）复用；按域名/子目录区分应用；web / cli / rpc 三种 SAPI 的分流点在哪。
> 前置：[第 1-7 章 上线最小清单](deployment.md)、[第 2-2 章 路由进阶](routing.md)、[第 2-15 章 命令行与定时任务](cli.md)、[第 3-1 章 应用树与相位基础](advanced-phase.md)、[第 3-2 章 把外部应用挂进来](mount-app.md)。预计 20 分钟。
> 示例全部来自 `demo/public/` 与 `demo/src/System/`，可用 `php -S 127.0.0.1:8080 -t demo/public` 起服务后逐个入口访问。

## 最小示例

`demo/public/` 下真实存在的入口，各自演示一种「一库多入口」的姿态：

| 入口 | 演示什么 |
|---|---|
| `index.php` | 标准 Web 入口：探测 Composer → 回落 [AutoLoader](../reference/Core-AutoLoader.md) → `App::RunQuickly()`（`demo/src/System/App.php`） |
| `demo.php` | 单文件五层：[App](../reference/Core-App.md)/Controller/Business/Model/View 全写在一个文件里（`namespace MySpace\…`），配 [`CallableView`](../reference/Ext-CallableView.md) |
| `helloworld.php` | 最小姿态：一个控制器类 + 一次 `RunQuickly()`（[第 4-4 章](embed.md)） |
| `traditional.php` | 全函数模式：`action_*` 函数 + [`RouteHookFunctionRoute`](../reference/Ext-RouteHookFunctionRoute.md) + [`EmptyView`](../reference/Ext-EmptyView.md)，视图是文件末尾的原生 PHP |
| `just-route.php` | 只要路由：连应用类都不用，直接 `Route::RunQuickly()` |
| `api.php` | API 服务器：[`Ext\RouteHookApiServer`](../reference/Ext-RouteHookApiServer.md) 把 `/api.php/test.foo2?a=1&b=2` 映射到 `\Api\test::foo2(1,2)`，返回 JSON |
| `rpc.php` | JSON-RPC 双端：[`JsonRpcExt`](../reference/Ext-JsonRpcExt.md) 既做服务端分发（`onRpcCall`）又做客户端（`JsonRpc\` 命名空间自动加载） |
| `dbtest.php` | 模型/分页/CRUD 全链路 + 作为子应用被 `App.php` 挂到 `/db_test/`（见 `demo/src/System/App.php` 的 `onPrepare()`） |
| `doc.php` | 文档阅读器：读取 `docs/` 下的 md/svg 经 marked.js 渲染（演示「非框架页面」共存） |

> `i.php` 之类的 `phpinfo()` 文件只是本地探针，不属于示例入口，这里不列。

这些入口**共用同一个 `demo/src/`**：`index.php` 走完整分层，`demo.php` 把五层写在一个文件里，`dbtest.php` 既是独立入口又被挂为子应用——「一库多入口」不是特例，是默认能力。

## 机制说明

### 入口的分流点：`KernelTrait::run()`

所有入口最终都汇到 [DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md) 的两个方法（`src/Core/KernelTrait.php`）：

```
RunQuickly($options, $after_init = null)          // 第 89–100 行
  └─ init($options) → 调 $after_init() → 分流：
       PHP_SAPI === 'cli' && isRoot() && cli_enable → execute()   // Console
       其它                                            → serve()    // Web

run()                                              // 第 468–475 行
  └─ cli_enable ? execute() : serve()

serve()   // 第 476–500 行：Runtime + Route 跑一遍，失败再 runChildren()，最后 _On404()
execute() // 第 534–545 行：Console::_()->run()
```

所以「这个请求由谁处理」的第一道判断是：**SAPI 是不是 cli、根应用有没有开 `cli_enable`**。Web 请求永远走 `serve()`；CLI 请求在 `cli_enable=true` 时走 `execute()`，否则也走 `serve()`（可以用命令行「请求」一个 URL，见 [第 2-15 章](cli.md)）。

### `cli_enable` 的作用

- `RunQuickly()` 里它决定 **CLI 下**是进 [Console](../reference/Core-Console.md) 还是进 Web 流程（`KernelTrait.php` 第 95 行）。
- `run()` 里它决定**任何 SAPI** 的走向（第 470 行）。
- 子应用不单独判断：根应用开了 `cli_enable`，整个进程（含所有子应用）的 CLI 命令都注册进同一个 Console，命令按相位加前缀（`php cli.php shop-<命令>`，见 [第 2-15 章](cli.md) 与 [第 3-1 章](advanced-phase.md)）。

### 多入口复用同一套 `src/`

入口文件本身只做三件事：找 autoload、给 `RunQuickly()` 传少量选项、跑。业务代码全部在 `demo/src/`：

- `demo/src/System/App.php`：标准入口类，`options` 里配异常分层、`controller_method_prefix => 'action_'`，`onPrepare()` 里把 `dbtest.php` 挂为子应用（`'controller_url_prefix' => 'db_test/'`）。
- `demo/src/System/AppWithAllOptions.php`：把**全部可用选项**以注释形式列出的样板（由 `tests/genoptions.php` 生成），当选项字典查。

新增一个入口 = 在 `public/` 下加一个 php 文件，`require` 同一个 autoload，`XxxApp::RunQuickly($options)`。`$options` 里可以覆盖类内默认（`demo.php` 末尾的注释「你也可以在这里调整选项」就是这个意思）。

### 多域名 / 多站点

同一个代码库按域名或子目录区分应用，有两条正路：

**A. 一个入口 + `onPrepare()` 按域名改选项**（同一应用类，不同域名不同行为）：

```php
protected function onPrepare(): void
{
    parent::onPrepare();
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === 'admin.example.com') {
        $this->options['controller_url_prefix'] = 'admin/';
        $this->options['path_view'] = 'view-admin';
    }
}
```

**B. 多个入口 + 子应用**（推荐，[第 3-1 章](advanced-phase.md)、[第 3-2 章](mount-app.md)）：每个域名/子目录一个入口文件，入口里 `RunQuickly` 同一个根应用，根应用把不同子应用挂到不同 `controller_url_prefix`。`dbtest.php` 被挂到 `/db_test/` 就是现成例子。

`path` 与 `controller_url_prefix` 的关系：`path` 决定**文件从哪找**（视图/配置/控制器），`controller_url_prefix` 决定 **URL 从哪开始**匹配（[第 2-2 章](routing.md) 的 E001 规则：前缀对不上就直接失败，父应用才有机会把请求转给其它子应用）。两者互不替代——子目录部署时常常 `path` 不变、`controller_url_prefix` 变成子目录名。

### 子目录部署

应用不放在域名根、而放在 `/myapp/` 下时，三件事要对上：

1. **URL 生成**：站内链接一律 `__url('about/me')`，它会自动带上 basepath（即子目录前缀），手写的 `/about/me` 会 404（[第 2-2 章](routing.md)）。
2. **路由解析**：nginx/apache 的 rewrite 把 `/myapp/xxx` 转成 `index.php` 的 PATH_INFO；拿不到 PATH_INFO 的服务器开 `'path_info_compact_enable' => true` 改从查询串解析（[第 1-7 章](deployment.md)、[第 2-2 章](routing.md)）。
3. **静态资源**：由框架代发的资源走 `controller_resource_prefix`，前缀按 `'/' . controller_url_prefix . controller_resource_prefix` 拼（[第 3-3 章](static-resources.md)）；生产环境更推荐把资源直出到 docroot。

### 多 SAPI：web / cli / api / rpc

- **web**：`serve()`，走 [Runtime](../reference/Core-Runtime.md) + [Route](../reference/Core-Route.md)（[第 2-10 章](lifecycle.md)）。
- **cli**：`execute()`，走 Console（[第 2-15 章](cli.md)）。
- **api**：仍是 `serve()`，但由 [DuckPhp\Ext\RouteHookApiServer](../reference/Ext-RouteHookApiServer.md) 挂在 `prepend-inner` 位置**在默认路由前接管**：`api.php/test.foo2?a=1&b=2` → 调 `\Api\test::foo2(1, 2)`，按反射参数名取参，结果 JSON 输出。选项见 `demo/public/api.php` 的 `apiserver_namespace` / `apiserver_base_class`（`~BaseApi` 表示当前命名空间下的 `BaseApi`，服务类须实现它，否则按未命中处理）。
- **rpc**：`demo/public/rpc.php` 一个文件同时演两端。[DuckPhp\Ext\JsonRpcExt](../reference/Ext-JsonRpcExt.md) 的 `onRpcCall($_POST)` 在服务端把 `Namespace.Service.method` 分发到本地服务类；客户端用 `JsonRpcExt::Wrap(服务类::class)` 或 `\JsonRpc\服务名::_()`（`jsonrpc_namespace` 前缀自动加载，类继承 [DuckPhp\Ext\JsonRpcClientBase](../reference/Ext-JsonRpcClientBase.md)），调用经 `jsonrpc_backend` POST 到服务端。

## 常见写法

**1. 加一个 API 入口**（照 `api.php`）：

```php
$options = [
    'namespace' => '',
    'ext' => [
        \DuckPhp\Ext\RouteHookApiServer::class => [
            'apiserver_namespace' => '\\Api',
            'apiserver_base_class' => '~BaseApi',
            'apiserver_404_as_exception' => true,
        ],
    ],
];
\DuckPhp\DuckPhp::RunQuickly($options);
```

**2. 加一个 RPC 入口**（照 `rpc.php`）：服务端动作里 `JsonRpcExt::_()->onRpcCall($_POST)`，客户端 `CalcService::_(JsonRpcExt::Wrap(CalcService::class))` 后照常调用，自动变远程。

**3. CLI 与 Web 共用同一入口**：

```php
// public/index.php 同时是 Web 入口和 CLI 入口
\MyProj\System\App::RunQuickly(['cli_enable' => true]);
```

`php public/index.php help` 进 Console，`curl http://…/index.php` 进 Web。

**4. 按域名切配置**（见上面「多域名」A 方案）。

## 常见错误

| 现象                             | 原因                                                          | 改法                                                                     |
| ------------------------------ | ----------------------------------------------------------- | ---------------------------------------------------------------------- |
| `php cli.php` 进了 Web 流程而不是命令列表 | `cli_enable` 是 `false` 或没传                                  | 入口里给 `'cli_enable' => true`（[第 2-15 章](cli.md)）                          |
| API 入口全 404                    | `RouteHookApiServer` 没启用，或 `apiserver_namespace` 与实际命名空间不符 | 对照 `demo/public/api.php` 检查 `ext` 选项                                   |
| API 类不满足基类约束 → 静默 404          | `apiserver_base_class` 写错或漏配                                  | 用 `~BaseApi` 形式（`~` = 当前 `namespace` + `apiserver_namespace`）             |
| RPC 客户端报「找不到类」                 | `JsonRpc\` 前缀的自动加载没注册                                       | 确认 `JsonRpcExt` 在 `ext` 里且 `jsonrpc_enable_autoload` 为真                |
| 子目录部署后所有站内链接 404               | 手写了 `/xxx` 绝对路径                                             | 一律 `__url()`（[第 2-2 章](routing.md)）                                      |
| 子目录部署后路由全 404                  | rewrite 没把子目录剥掉，或 PATH_INFO 丢失                              | [第 1-7 章](deployment.md) 的 nginx/apache 写法；或开 `path_info_compact_enable` |
| 多入口下「这个请求到底谁处理了」查不到            | 入口多、子应用多，没有判断顺序                                             | 按下面的排查顺序走一遍                                                            |

**「谁处理这个请求」的排查顺序**：

1. 看 URL 落在哪个**入口文件**（`/api.php/...` 进 `api.php`，`/db_test/...` 被子应用前缀接管，其余进 `index.php`）。
2. 进了入口后，看 SAPI：`PHP_SAPI === 'cli'` 且 `cli_enable` → Console；否则 `serve()`。
3. `serve()` 内：默认路由 → 失败 → `runChildren()` 按 `options['app']` 顺序问每个子应用 → 都失败 → `_On404()`。
4. 还查不到：开 `is_debug`，看 `Route::_()->options['route_error']`（E001 = 前缀不匹配），或临时 `var_dump(App::Phase())` 确认当前在哪个相位。

## 下一步

- [第 4-7 章 测试基建与覆盖率流水线](coverage.md)：把多入口的冒烟固化成测试。
- 回看[第 3-2 章 把外部应用挂进来](mount-app.md)：子应用 + 多入口的组合拳。
- 参考手册：[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Ext\RouteHookApiServer](../reference/Ext-RouteHookApiServer.md)、[DuckPhp\Ext\JsonRpcExt](../reference/Ext-JsonRpcExt.md)、[DuckPhp\Ext\JsonRpcClientBase](../reference/Ext-JsonRpcClientBase.md)
