# DuckPhp\DuckPhp

框架最常用的入口类：继承 `DuckPhp\Core\App`，并把“默认加装哪些常用组件/选项”一次给好，是新项目最常见的起点。

## 简介

`DuckPhp` 是 `DuckPhp\Core\App` 的子类，本身不定义复杂的业务，而是：

- 用 `protected $common_options` 声明一组“框架默认”的应用选项：默认开启多语言、Rewrite/Map/资源/兼容路由钩子；预留 DB 前缀、用户/管理视图、数据库/Redis provider 与 language 等。
- 重写（override）`initComponentsOfRoot` / `initComponentsOfInner`，在父类（`App`）装 System 组件的基础上，把**框架内置的业务组件**逐步接入当前应用：
  - 根组件阶段：`DbManager`、`RedisManager` 以 `EXT_DEFAULT` 建实例；`GlobalAdmin` 先 `SKIP_INIT`（仅创建单例），`GlobalUser`、`GlobalEvent` 默认 `EXT_DISABLE`（关闭，用到才开）。
  - 内部阶段：注册默认命令行插件与 `Configer`；按 `local_database` / `local_redis` 决定是否为本 Phase 新建独立 DB/Redis；接 `data_file_enable`；并处理 `admin_provider` / `user_provider`。
- 提供 `_Show()`（根据控制器类型转给用户/管理视图或父默认）、`lang()`（交给可选的 `lang_handler` 或 `Content\Lang`）等两个实例便捷口。

绝大多数项目只需要：

```php
class MyApp extends \DuckPhp\DuckPhp { /* 覆盖/合并 $options */ }
```

然后 `MyApp::RunQuickly([])` 即可投入使用。少数需要离线/深度控制的场景才直接继承 `App`。

## 类信息

- 命名空间：`DuckPhp`
- 声明：`class DuckPhp extends App`（`App = DuckPhp\Core\App`）
- 引入方式：通常 `class 你的App extends \DuckPhp\DuckPhp` 即可获得这些能力，无需再手动拼组件。
- 只用在本类文件 import 的相关（`use DuckPhp\…`）控制组件装配；子类写代码时建议经由 Helper（`DuckPhp\Foundation\*`/静态 Shell），不要直接依赖框架命名空间。

## 选项

下表列 `DuckPhp::$common_options` 中真实生效的键（源码其后被注释掉的历史项不在此列）：

| 选项 | 默认值 | 说明 |
|---|---|---|
| `data_file_enable` | `false` | 是否打开“数据属性/外部扩展 options 文件”机制。开启后会在对应初始化阶段调用 `ExtOptionsLoader` 读取额外定义（如多应用共用配置）。 |
| `ext` | 数组（见下） | 本类默认启用的扩展钩子映射。值为源码 `ext` 五项：`Lang`、`RouteHookRewrite`、`RouteHookRouteMap`、`RouteHookResource`、`RouteHookPathInfoCompat`(=那个开关值)。 |
| `session_prefix` | `null` | Session 键命名前缀（宿主用来避免 / 多应用并发覆盖）。 |
| `table_prefix` | `null` | 数据库表名前缀（DB 层在所需 CRUD 时用它拼接表名）。 |
| `use_user_view` | `true` | 当前控制器实现用户界面基类时，是否把渲染（`_Show`）转交给用户系统（`GlobalUser`）。 |
| `use_admin_view` | `true` | 同类，处理器是管理控制器时把渲染转给管理页面（`GlobalAdmin`）。 |
| `admin_provider` | `''` | 自定义管理员提供者类名；非空时会在内部阶段实例化并交给 `GlobalAdmin`。 |
| `user_provider` | `''` | 自定义用户提供者类名；非空时同理交给 `GlobalUser`（可用 `PhaseProxy` 包装）。 |
| `database_driver` | `''` | 数据库驱动标签（如 `mysql`）。初始化后会把 `DbManager` 得到的真实驱动回填到该 options 供上层读取。 |
| `cli_command_with_common` | `true` | 是否把内置默认 CLI 命令集（`DuckPhp\Component\Command`）登记进当前应用的命令列表。 |
| `duckcoverage_test_lister` | `null` | 仅供本仓/测试使用的覆盖测试枚举；普通项目通常不设。 |
| `lang_default` | `null` | 多语言默认语言（与 Lang 组件共享；不做进一步检测的兜底）。 |
| `lang_final` | `null` | 最终语言；设置后不再自动检测、直接以它为准。 |
| `local_database` | `false` | 为 `true` 时，本 App（含其子 app Phase）新建一份独立的 `DbManager`（不计入公共容器共享，互不干扰）。 |
| `local_redis` | `false` | 同 semantics 的 Redis：true 时独立 `RedisManager`。 |

## 使用方式

传统入口：

```php
namespace Demo\System;
use DuckPhp\DuckPhp;

class App extends DuckPhp {
    public $options = [
        'namespace'  => 'Demo',
        'path'       => __DIR__.'/../..',
        'use_user_view' => true,
        'user_provider' => \Demo\UserProvider::class,
    ];
}

\Demo\System\App::RunQuickly([]);
```

要点：

- `name → namespace` 自动反推项目目录/命名空间（由 Kernel 层核）。
- 想用自己的控制器后缀/前缀，同样在 `$options` 覆盖（如 `controller_method_prefix => 'action_'`）。
- 用户/管理后台默认关闭 —— 若 `user_provider` / `admin_provider` 是空的就直接不启用相应界面。
- CLI 命令 <hint>默认提供；想只**关闭某个**可用 `cli_command_with_common=false`。</hint>

## 配置示例

```php
class MyApp extends \DuckPhp\DuckPhp {
    public $options = [
        'namespace'  => 'Demo',
        'path'       => __DIR__.'/../..',
        'lang_default'   => 'zh_CN',
        'lang_final'     => 'zh_CN',
        'use_admin_view' => true,
        'admin_provider' => \Demo\Admin\Provider::class,
        'database_driver'=> 'mysql',
        'local_database' => false,     // 应用间共享默认 Db
    ];
}
```

运行时可用静态壳/单例取得：

```php
use DuckPhp\DuckPhp;
DuckPhp::Platform();
DuckPhp::Setting('shop_name','demo');
\App::_()->lang('no.result'); // 若有 lang_handler 走它，否则交给 Lang
```

## 注意事项

1. 类别的“是否可直 init”：`App` 在 `haltInitInBaseClass` 会阻止不加继承的 base 初始化；`DuckPhp` 把这些钩子做合理默认，直接用即可。
2. 要关闭默认扩展：合并 `ext => [Lang::class => false, RouteHookRewrite::class => false, …]`（数组层会覆盖默认）。
3. 开启 administrator/user：需设 `use_admin_view/use_user_view` + 对应 `provider`；为空即关闭。
4. 独立 DB/Redis：在多 app 或沙盒场景用 `local_database/local_redis`，否则共享根 Manager。
5. 配置后参数 `database_driver` 会被框架回填（读到值而不是空）。
6. 本类方法不多，框架主体在 `App`/`KernelTrait`；本文档的方法列表只列 `DuckPhp.php` 本身新增的钩子-外壳。

## 全部选项

以下为 `protected $common_options` 的源码内“激活键”部分（本文件已把被注释掉的历史选项剔除不列）：

```php
    protected $common_options = [
        'data_file_enable' => false,
        'ext' => [
            Lang::class                 => true,
            RouteHookRewrite::class     => true,
            RouteHookRouteMap::class    => true,
            RouteHookResource::class    => true,
            RouteHookPathInfoCompat::class => 'path_info_compact_enable',
        ],
        'session_prefix' => null,
        'table_prefix' => null,
        // 'use_user_view' => true,
        // 'use_admin_view' => true,
        'admin_provider' => '',
        'user_provider' => '',
        'database_driver' => '',
        'cli_command_with_common' => true,
        'duckcoverage_test_lister' => null,
        'lang_default' => null,
        'lang_final' => null,
        'local_database' => false,
        'local_redis' => false,
    ];
```

## 方法列表

> 只列 `DuckPhp.php` 中本类 override/新增的方法。继承自 Core-（App/KernelTrait/Route 等）的壳与静态写在各自文档：`Core-App`、`Core-KernelTrait`。

### 公共方法

    public function _Show(array $data, string $view = '')
当 current calling controller 是 User/Admin 类型且 use_*_view 开启时，把渲染转发给 GlobalUser/GlobalAdmin；否则回落 parent::_Show()

    public function lang($str, $args = [], $fallback = null)
翻译：有 lang_handler 回调则委托；否则交给 Lang(_)::language()（可 fallback）

### 受保护方法

    protected function initComponentsOfRoot($components, $default): void
父类完成后为本“根默认”补上 DbManager/RedisManager(EXT_DEFAULT)、GlobalAdmin(SKIP_INIT)、GlobalUser/GlobalEvent(disable)；开启时 init ExtOptionsLoader，并对 Db/Redis init 后回填 database_driver

    protected function initComponentsOfInner($components, $default): void
父内部装载基础上：子若 data_file_enable 引入外部；默认注册 Command/Configer；并在 local_database/local_redis=真(或驱动不符)时 createLocalObject 自有 Db/Redis

    protected function initComponentsOfExt($classes, $default): void
扩展装载：先走父类 `initComponentsByClasseOptions()` 处理 `ext` 表，再按 `admin_provider`/`user_provider` 把 GlobalAdmin/GlobalUser 指向工程自有实现（用 `PhaseProxy::CreatePhaseProxy()` 包一层挂到当前 Phase）

    protected function haltInitInBaseClass(): void
**空实现是刻意的**：父类 `App` 在这个钩子里抛异常，用来阻止「直接 init 基类 App」；DuckPhp 是可用的入口类，所以覆盖成空以放行

    protected function isLocalDatabase(): bool
判断是否需要独立的 DB（local_database 真，或显式 database_driver 与当前 Manager 推导驱动不同）

    protected function isLocalRedis(): bool
仅在 local_redis=true 时返回真

## 相关链接

- [DuckPhp\DuckPhpAllInOne](DuckPhpAllInOne.md) — 单文件 AllInOne 版入口
- [DuckPhp\Core\App](Core-App.md) — 父类（含 use KernelTrait）
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — init/serve/组件装载框架
- 组件页：`DuckPhp\Component\DbManager`(Component-DbManager.md)、`RedisManager`、`Lang`、`Configer` 等
- guide：[configuration](../guide/configuration.md)、[project-structure](../guide/project-structure.md)、[quickstart](../guide/quickstart.md)
