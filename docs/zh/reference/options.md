# 应用选项总览

> 这是「应用选项」这套功能的首页：先讲选项**从哪来、谁盖谁**，再给两个索引的入口。
> 按类看：[应用选项（按类分组）](options-by-class.md) · 按名查：[应用选项（按字母顺序索引）](options-index.md) · 另一套配置：[应用设置 Setting](setting.md)

## 一句话

`$options` 是框架**唯一的配置入口**：无论写在应用类的 `$options` 属性里、`init($options)` 里、`ext` 表里，还是由命令行动态改，最后都归并到当前 App 实例的 `$options`（`App::$options`）。

## 选项从哪来（合并链）

```php
// 1) 实例化时：先合成"声明默认值"（后者盖前者）
//    App::__construct()
$this->options = array_replace_recursive($this->kernel_options, $this->core_options, $this->common_options, $this->options);

// 2) 初始化时：再合并调用方传进来的选项（优先级最高）
//    KernelTrait::initOptions()
$this->options = array_replace_recursive($this->options, $options);
```

| 次序 | 来源 | 谁写 | 备注 |
|---|---|---|---|
| 1 | `KernelTrait::$kernel_options` | 框架 | 应用骨架：`path`/`namespace`/`app`/`cmd`/`ext`/`cli_enable`/`on_*` |
| 2 | `App::$core_options` | 框架 | 核心：`path_runtime`、`path_config`、`setting*`、`error_*`、`exception_map` |
| 3 | `DuckPhp::$common_options` | 框架 | 入口类默认：默认 `ext` 组件表、`*_provider`、`lang_*`、`local_database` 等 |
| 4 | **子类 `$options` 属性** | 工程 | 你的应用类写在这里；同键覆盖前三层 |
| 5 | `ext` 表里给出的**数组**选项 | 工程 | 组件初始化时各自合并 |
| 6 | `init($options)` | 工程 | **最后合并、优先级最高**（`initOptions()` 不做白名单） |
| — | 设置文件 / `.env` | 环境 | 不进 `$options`，用 `Setting()` 读：见 [应用设置](setting.md) |
| — | 数据文件（`ExtOptionsLoader`） | 运行时 | 运行时可改的选项落在 `runtime/DuckPhpData.config.json` |

> ⚠️ **组件有白名单，App 没有**：组件（`ComponentBase` 子类）的 `init()` 会做 `array_intersect_key($this->options, $options)`——**组件自己 `$options` 里没有的键，传进去会被静默丢掉**；而 `App` 走的是 `KernelTrait::initOptions()`，任意键都能进来（所以「隐式选项」在 App 上可用）。

## 配置层小抄

<!-- GEN:layers start -->
| 层 | 内容 |
|---|---|
| `DuckPhp\Core\KernelTrait::$kernel_options` | 应用骨架：path/namespace/app/cmd/ext/cli_enable/on_* 等 |
| `DuckPhp\Core\App::$core_options` | 核心：path_runtime、path_config、setting*、error_*、exception_map… |
| `DuckPhp::$common_options` | 入口类默认：`ext` 默认组件表、provider、lang_*、data_file_*… |
| 各组件自己的 `$options` | 组件被 init 时用自己的白名单合并，见 [按类分组](options-by-class.md) |
| `ext` 表装载的扩展 | `类 => true/数组/'选项键'/EXT_* 常量`；字符串值是**选项键名** |
| `init($options)` 运行时传入 | 最后合并，**优先级最高**（`KernelTrait::initOptions()` 直接 `array_replace_recursive`） |
| 设置文件 / `.env` | 不进 `$options`，用 `Setting()` 读，见 [应用设置](setting.md) |
| 数据文件（`ExtOptionsLoader`） | 运行时可改的选项落在 `runtime/DuckPhpData.config.json` |
<!-- GEN:layers end -->

## 隐藏选项

框架会读、但**故意不写进任何 `$options`** 的键。它们可以像普通选项一样用（写进应用类 `$options`，或在 `init($options)` 里给），只是不进上面的正式清单——因为它们是「框架内部开关」或「给外部工具用的钩子」，不想让人当成常规配置项。

<!-- GEN:hidden start -->
| 隐藏选项 | 默认值 | 出处 | 说明 |
|---|---|---|---|
| `session_prefix` | `''` | Foundation\SessionTrait | 会话名的前缀（根应用的设置也走这里）。 |
| `table_prefix` | `''` | Ext\SqlDumper / Ext\RouteHookWebInstaller | 数据库表名前缀，导出/安装 SQL 时用 `{prefix}` 占位替换。 |
| `use_user_view` | `false` | DuckPhp::_Show() | 为 true 时，前台控制器（`UserControllerInterface`）的 `_Show()` 交给 `GlobalUser::_Show()` 渲染。 |
| `use_admin_view` | `false` | DuckPhp::_Show() | 同上，后台控制器（`AdminControllerInterface`）交给 `GlobalAdmin::_Show()`。 |
| `use_user_view_header_footer` | `false` | GlobalUser::_Show() | 是否把 `user_view_file_header/footer` 套到视图上（配合 `use_user_view`）。 |
| `use_admin_view_header_footer` | `false` | GlobalAdmin::_Show() | 是否把 `admin_view_file_header/footer` 套到视图上（配合 `use_admin_view`）。 |
| `exception_for_business` | `\Exception::class` | CoreHelper::_BusinessThrowOn() | `BusinessThrowOn()` 未显式指定时的异常类。 |
| `exception_for_controller` | `\Exception::class` | CoreHelper::_ControllerThrowOn() | `ControllerThrowOn()` 未显式指定时的异常类。 |
| `duckphp_all_in_one_wrap_header_foot` | `false` | DuckPhpAllInOne::onInited() | AllInOne 入口是否给 `_Show()` 包 head/foot 视图（该类自己会置 true）。 |
| `permission_menu_tree_for_admin` | `null` | Ext\PermissionMenu::getMenuJsonFileConfig() | 后台权限菜单树的配置文件（相对 `path_config`）。 |
| `duckcoverage_test_lister` | `null` | 外部包 dvaknheo/duckcoverage | 配合该 composer 包做覆盖测试使用，框架自身不读。 |
| `background` | `false` | HttpServer::run*() | 内置服务器是否后台运行；CLI 开关 `-b/--background` 会把它置 true。 |
<!-- GEN:hidden end -->

约定：

- 想要全量机器可读清单：`php scripts/gen-options-docs.php --json`，或人类可读的 `python3 scripts/scan-options.py`；
- 扫描器会核对隐藏表的默认值与源码兜底是否一致、有没有人真的读它、以及是否被构造流程清空；
- 隐藏表里那行 `// @used-by <包名>` 表示**该项由外部包读取**（本仓库里没有读取点，扫描器因此不再告警）。

## 两条动态通道（所以「选项清单」不可能穷尽）

1. **回调键动态取用**：`GlobalAdmin` / `GlobalUser` 的 `run_callback_by_key($key)` 用 `$this->options[$key]` 取键，键名由各自内部常量给出（如 `admin_callback_for_login_service`）。
2. **`ext` 表的字符串是选项键名**：`initExtensionsByOptions()` 里，当某组件在 `ext` 表中的值写成**字符串**时，那个字符串会被当作**选项键名**去 `$this->options[...]` 取值。例：`DuckPhp::$common_options` 里

   ```php
   RouteHookPathInfoCompat::class => 'path_info_compact_enable',
   ```

   于是 `path_info_compact_enable`（由 `RouteHookPathInfoCompat::$options` 自己声明）成了「这个扩展的开关」。

## 扩展与数据文件

`ext` 表的值有四种写法（`KernelTrait::initExtensionsByOptions()`）：

| 写法 | 含义 |
|---|---|
| `true` | 用默认方式初始化（跟随 App 选项） |
| `false` / `null` / `EXT_DISABLE(0)` | 不加载 |
| 数组 | 用这个数组初始化该组件（组件白名单仍生效） |
| `'选项键名'` 字符串 | 去 `$options[选项键名]` 取值，再按取到的值决定 |
| `EXT_SKIP_INIT(-1)` / `EXT_DEFAULT(1)` / `EXT_FOLLOW_APP(2)` / `EXT_RENEW(3)` | 只取实例不初始化 / 默认初始化 / 跟随 App 选项 / 每请求重建（动态组件） |

**数据文件**（`ExtOptionsLoader`，`data_file_*` 选项）：把「运行时可改的选项」写到 `path_runtime` 下的 JSON 文件里，下次启动时覆盖回来；命令行 `php xx debug` 就是改其中的 `is_debug`。文件里还有两个框架自写的落盘字段：`__class__`（选项来源类）与 `__date__`（落盘时间）——都是内部机制，不要手写。

## 常见配方

- **多子应用差异化**：`'app' => [AppA::class => ['name' => 'a', 'controller_url_prefix' => 'a']]`；子应用的选项在 `initChildren()` 里独立合并，互不影响。混写模式还支持 `['class' => AppA::class]`（表键当 `controller_url_prefix`）。
- **按 Phase 覆盖文件**：`getOverrideableFile()` 会从当前 Phase 逐层回退找文件——同一份 `config/x.php` 可以给子应用放一份覆盖版。
- **三处调试开关**：选项 `is_debug`、设置 `duckphp_is_debug`（两者取或，见 `App::IsDebug()`）、以及数据文件里的 `is_debug`（命令行可改）。
- **临时改选项**：`App::_()->options['某键'] = 值;` 只影响当前实例；要持久化就写进设置文件或数据文件。
- **CLI 传参**：`php xx run --port=8080` 这类是**命令行参数**（`Console::getCliParameters()`），不是 app 选项——两者别混。

## 相关链接

- [应用选项（按类分组）](options-by-class.md) —— 想了解"某个类支持哪些配置"
- [应用选项（按字母顺序索引）](options-index.md) —— 想快速查"某个键是什么意思"
- [应用设置 Setting](setting.md) —— 数据库口令、调试/维护开关这类"环境数据"
- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) —— `kernel_options` 与 `initOptions()` 的实现处
- [DuckPhp\Core\App](Core-App.md) —— `core_options` 与设置文件加载
- [DuckPhp\DuckPhp](DuckPhp.md) —— `common_options` 与隐藏选项表
