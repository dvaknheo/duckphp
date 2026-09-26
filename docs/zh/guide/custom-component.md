# 4-2 开发组件与扩展

> 解决什么问题：怎么写一个自己的组件/扩展（声明选项、初始化、挂进路由），并通过 `ext` 选项装配进应用；怎么替换框架自带的组件。
> 前置：[第 2-2 章 请求生命周期](lifecycle.md)、[第 4-1 章 容器与相位内部机制](container-phases.md)。预计 20 分钟。
> 本章引用的框架扩展全部来自 `src/Ext/`、`src/Component/`，装配示例来自 `tests/data_for_tests/ZThirdDemo` 与 `tests/data_for_tests/Ext/PermissionMenu`。

## 最小示例

一个最小扩展（示意；结构照抄 `src/Ext/RouteHookWebInstaller.php` 的写法）：

```php
namespace MyProj\Ext;

use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\Route;

class HelloBanner extends ComponentBase
{
    public $options = [
        'hello_banner_text' => 'Hello',
    ];

    protected function initContext(object $context): void
    {
        Route::_()->addRouteHook([static::class, 'Hook'], 'append-outter');
    }
    public static function Hook($path_info)
    {
        echo static::_()->options['hello_banner_text'];
        return false;   // 返回 false 让路由继续
    }
}
```

组件要满足的最小契约是 [`Core\ComponentInterface`](../reference/Core-ComponentInterface.md)：`_()` / `init()` / `isInited()` 三个方法。注意框架**没有**让 `ComponentBase` 真的 `implements` 它（`src/Core/ComponentBase.php` 12 行是注释掉的 `// implements ComponentInterface`）——它是一份"鸭子类型"契约，你写自己的组件时按它对齐即可，不必强制声明。

挂进应用（`tests/data_for_tests/ZThirdDemo/src/System/MainApp.php` 52-55 行的同款写法）：

```php
'ext' => [
    \DuckPhp\Component\GlobalEvent::class => true,   // 打开框架扩展
    \MyProj\Ext\HelloBanner::class => true,          // 打开自己的扩展
],
```

## 机制说明

### 组件与扩展：同一个骨架，两种身份

两者都 [`extends DuckPhp\Core\ComponentBase`](../reference/Core-ComponentBase.md)，都靠 `::_(new Xxx())->init($options, $context)` 被装配。差别只在**谁初始化它、默认开不开**：

|      | 组件 `DuckPhp\Component\*`                                                                                                      | 扩展 `DuckPhp\Ext\*`                                                                                                                     |
| ---- | ----------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------- |
| 例子   | `DbManager`、[`Configer`](../reference/Component-Configer.md)、[`RouteHookRewrite`](../reference/Component-RouteHookRewrite.md) | `JsonView`、[`PermissionMenu`](../reference/Ext-PermissionMenu.md)、[`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md) |
| 装载方式 | 框架在 `initComponents()` 里按内置表装载（部分标为共享，见第 4-1 章）                                                                          | 由你在 `options['ext']` 里声明才装载                                                                                                            |
| 默认状态 | 多数默认启用                                                                                                                        | 全部默认关闭                                                                                                                                 |

注意「扩展」也是普通类：[`JsonView extends View`](../reference/Core-View.md)、[`MyMiddlewareManager extends ComponentBase`](../reference/Ext-MyMiddlewareManager.md)——扩展可以就是另一个组件的子类。

### `ComponentBase::init()` 与选项白名单

`ComponentBase::init()`（`src/Core/ComponentBase.php` 35-48 行）是模板方法：

```php
$this->options = array_intersect_key(array_replace_recursive($this->options, $options), $this->options);
$this->initOptions($options);
if ($context !== null) { $this->initContext($context); }
```

第一行就是白名单机制：`array_intersect_key(..., $this->options)` 把传入选项**裁到组件自己声明的键**里。所以：

- 组件**必须在 `public $options = [...]` 里声明键**，外部传同名键才生效；没声明的键传了也会被丢弃。
- 这保证了 `EXT_FOLLOW_APP`（把整个应用的 `$options` 原样传进来）是安全的——组件只认领自己的那一小撮键。
- 想强制重初始化用 `reInit()`（52-56 行）；`init_once` 为 true 时重复 `init()` 会被忽略，除非传 `__force__`。

子类挂钩点只有两个：`initOptions(array $options)` 处理选项（如 `RouteHookRewrite` 合并 `rewrite_map`，`src/Component/RouteHookRewrite.php` 28-31 行）；`initContext(object $context)` 做初始化副作用（如挂路由钩子）。**不要重写 `init()` 本身**——`RouteHookWebInstaller` 重写了 `init()` 但第一件事就是 `parent::init()`（`src/Ext/RouteHookWebInstaller.php` 84-93 行），只为在初始化后补一句 [`Lang::_()->importDefaultSentences()`](../reference/Component-Lang.md)。

### `ext` 选项的取值（含 `EXT_*` 模式）

`ext` 是 `[类名 => 取值]` 表。取值决定装载方式（判定逻辑在 `src/Core/KernelTrait.php` 375-421 行）：

| 取值                       | 常量（值）            | 行为                                       | 什么时候用                                                                                                                           |
| ------------------------ | ---------------- | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `false` / `null` / `0`   | `EXT_DISABLE`(0) | 不装载（这类假值在 `array_filter()` 里就先被滤掉）        | 关掉框架默认开的扩展（如 [`GlobalEvent`](../reference/Component-GlobalEvent.md) 默认就是关的）                                               |
| `true`                   | `EXT_DEFAULT`(1) | **等价于下面的 `EXT_FOLLOW_APP`：拿「本级应用」的全部选项 `init($this->options, $this)`** | 最常见的「打开」——扩展与应用**共用同一份应用选项**，扩展要什么键就写在应用的 `$options` 里                                                                    |
| 数组                       | —                | `init(数组, $this)`                        | 只给这个扩展传它自己的选项                                                                                                                   |
| `'@方法名'`                 | —                | 调用本应用的该方法取返回值，再按返回值递归处理                  | 选项要运行期决定（`overriding.md` 的 `RouteHookRewrite::class => '@myRewriteOptions'`）                                                    |
| 选项键名字符串                  | —                | 取 `$this->options[该键]` 的值再递归处理           | 用某个开关选项控制扩展开/关                                                                                                                  |
| `App::EXT_FOLLOW_APP`(2) | —                | `init($this->options, $this)`：拿应用全部选项初始化 | `true` 的显式写法；框架内部对 [`Console`](../reference/Core-Console.md)/[`Route`](../reference/Core-Route.md) 也是这么传的（`src/Core/KernelTrait.php` 329、335 行） |
| `App::EXT_ROOT_HOLD_POSISION_ONLY`(0) | —    | 值就是 `EXT_DISABLE`：**只登记成共享类、不 init**，第一次 `::_()` 才创建 | 框架的根组件阶段（`DbManager`/`RedisManager`/`Admin`/`User`/`GlobalEvent`，见[第 2-2 章](lifecycle.md)） |
| `App::EXT_SKIP_INIT`(-1) | —                | 只 `::_()` 取实例，**不 init**                 | 想延迟初始化、或只要单例占位                                                                                                                  |
| `App::EXT_RENEW`(3)      | —                | 取旧实例的选项，**换新对象**重新 init                  | 每次请求重建（`prepareServe()` 以 `$default=EXT_RENEW` 走动态扩展，`src/Core/KernelTrait.php` 501-506 行）                                      |

> **`true` 到底等于什么？** 判定只有一句：`true` 与 `EXT_DEFAULT` 都是「用调用方传进来的 `$default`」。而 `initComponents()` 给**应用的 `ext` 表**传的 `$default` 就是 `EXT_FOLLOW_APP`（`src/Core/KernelTrait.php` 339-340 行），所以在应用自己的 `ext` 里写 `true` = **跟随本级应用的选项**（`init($this->options, $this)`），**不是**「用扩展自己的默认选项」。只想给扩展它自己的选项就写数组；写 `'选项键名'` 则取应用里那个键的值再递归判定（框架默认表里的 `RouteHookPathInfoCompat => 'path_info_compact_enable'` 就是这一种）。

框架内置的实际用法（照抄即可）：[`DuckPhp`](../reference/DuckPhp.md) 默认 `ext` 表（`src/DuckPhp.php` 38-43 行）里 `Lang`/`RouteHookRewrite`/[`RouteHookRouteMap`](../reference/Component-RouteHookRouteMap.md)/[`RouteHookResource`](../reference/Component-RouteHookResource.md) 是 `true`（= 跟随应用选项），[`RouteHookPathInfoCompat`](../reference/Component-RouteHookPathInfoCompat.md) 是选项键名 `'path_info_compact_enable'`；根组件阶段 `DbManager`/[`RedisManager`](../reference/Component-RedisManager.md)/[`Admin`](../reference/GlobalAdmin-Admin.md)/[`User`](../reference/GlobalUser-User.md)/`GlobalEvent` 用的是 **`EXT_ROOT_HOLD_POSISION_ONLY`（只占位，见[第 2-2 章](lifecycle.md)）**（`src/DuckPhp.php` 106-112 行）。

### 扩展的生命周期挂钩点：`initContext()`

扩展没有独立生命周期，它的「启动」就是 `init()`，而 `init()` 在应用 `initComponents()` 阶段被调用（[第 2-2 章](lifecycle.md)的时序图）。要介入请求处理，就在 `initContext()` 里挂路由钩子——这是框架扩展的标准姿势：

```php
// src/Ext/RouteHookWebInstaller.php 75-78 行
protected function initContext(object $context): void
{
    Route::_()->addRouteHook([static::class, 'Hook'], 'prepend-inner');
}
```

```php
// src/Ext/MyMiddlewareManager.php 31-35 行（等价写法，经 RouteHookManager）
protected function initContext(object $context): void
{
    RouteHookManager::_()->attachPreRun()->append([static::class, 'Hook']);
}
```

钩子的静态方法收 `$path_info`，返回真值表示「这条请求我处理了」（短路），返回 `false` 放行给后续钩子与默认路由。四个挂载位置与执行顺序见[第 2-4 章 路由钩子](route-hooks.md)。

### 替换框架组件

两条已核实的路径：

1. **直接换单例**（第 4-1 章的 `::_(新实例)`）：`JsonView::init()` 里 `View::_(static::_())`（`src/Ext/JsonView.php` 34 行）——`View::_()` 从此返回 [JsonView](../reference/Ext-JsonView.md)。任何 `extends View` 的类都可以这么接管视图。
2. **选项指定实现类**：`DbManager` 的 `database_class` 选项（`src/Component/DbManager.php` 172-177 行）——非空时 `new $class()` 代替默认的 [`DuckPhp\Db\Db`](../reference/Db-Db.md)。自己的数据库封装类实现 [`DuckPhp\Db\DbInterface`](../reference/Db-DbInterface.md) 后填进这个选项即可。

## 常见写法

```php
// 1) 只给某个扩展传它自己的选项（数组取值）
'ext' => [
    \DuckPhp\Ext\JsonView::class => ['json_view_skip_vars' => ['debug_info']],
],

// 2) 用本应用的方法动态返回扩展选项（'@方法' 取值）
'ext' => [
    \DuckPhp\Component\RouteHookRewrite::class => '@myRewriteOptions',
],
// 应用类里：protected function myRewriteOptions() { return ['rewrite_map' => [...]]; }

// 3) 测试里手动初始化一个扩展（不经应用装配）
MyExt::_(new MyExt())->init(['my_option' => 1], App::_());

// 4) 关掉框架默认打开的扩展
'ext' => [\DuckPhp\Component\RouteHookRewrite::class => false],

// 5) 参考真实装配：tests/data_for_tests/Ext/PermissionMenu/System/PermissionMenuApp.php
'ext' => [\DuckPhp\Ext\PermissionMenu::class => true],
```

## 常见错误

| 现象                              | 原因                                   | 改法                                                                                                 |
| ------------------------------- | ------------------------------------ | -------------------------------------------------------------------------------------------------- |
| 给扩展传了选项却没生效                     | 键没声明在该扩展的 `public $options` 里，被白名单裁掉 | 在扩展类里补上该键（带默认值）                                                                                    |
| 扩展的钩子从不执行                       | `initContext()` 没挂钩子，或挂的位置/返回值不对     | 照 `RouteHookWebInstaller::initContext()` 写；确认返回 `false` 放行                                         |
| `ext` 里写 `'@method'` 报方法不存在     | 方法必须是**本应用类**上的（可 protected）         | 在 [App](../reference/Core-App.md) 子类里加该方法，或改用数组取值                                                  |
| 扩展里 `App::_()` 拿到的是别的应用         | 扩展在子相位被 init，`App::_()` 是「当前应用」      | 用传入的 `$context`（init 的第二个参数），它就是宿主应用                                                               |
| 想替换 `Db` 却继承了 `DbManager`       | 换错层                                  | 实现 `DbInterface` 填 `database_class` 选项；只有要改连接管理才动 [DbManager](../reference/Component-DbManager.md) |
| 重写 `init()` 忘了 `parent::init()` | 选项白名单与 `is_inited` 都没走               | 第一句必须 `parent::init($options, $context)`                                                           |

## 下一步

- [第 4-3 章 替换框架行为](replace-behavior.md)：`::_(新实例)`、`database_class` 等替换手段的完整清单。
- [第 3-5 章 重写与覆盖](overriding.md)：从「使用方」视角看 `ext` 表与 `EXT_*` 的覆盖玩法。
- 参考手册：[DuckPhp\Core\ComponentBase](../reference/Core-ComponentBase.md)、[DuckPhp\Core\KernelTrait](../reference/Core-KernelTrait.md)、[DuckPhp\Ext\RouteHookWebInstaller](../reference/Ext-RouteHookWebInstaller.md)、[DuckPhp\Ext\MyMiddlewareManager](../reference/Ext-MyMiddlewareManager.md)、[DuckPhp\Ext\JsonView](../reference/Ext-JsonView.md)
