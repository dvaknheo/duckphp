# 应用选项（按类分组）

> 本页由 `docs/scripts/gen-options-docs.php` 生成，**请勿手改**：改选项请改 `src/` 与对应类文档，然后重跑生成器。

选项来自各类的 `$options` / `$core_options` / `$kernel_options` / `$common_options`；**默认值取自源码**，说明取自该类的参考文档。共 **42** 个类、**206** 个选项（同名选项在不同类各自声明，合计 240 处；另有 11 个隐藏选项见文末）。

想按名字找？看 [应用选项（按字母顺序索引）](options-index.md)；选项机制见 [应用选项总览](options.md)。

---

## 入口类

### DuckPhp\DuckPhp

DuckPhp\Core\App 的子类，本身不定义复杂的业务，而是

类文档：[DuckPhp\DuckPhp](DuckPhp.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `data_file_enable` | `false` | 是否打开"数据属性/外部扩展 options 文件"机制。开启后会在对应初始化阶段调用 `ExtOptionsLoader` 读取额外定义（如多应用共用配置）。 |
| `ext` | `[...]` | 本类默认启用的扩展钩子映射。值为源码 `ext` 五项：`Lang`、`RouteHookRewrite`、`RouteHookRouteMap`、`RouteHookResource`、`RouteHookPathInfoCompat`(=那个开关值)。 |
| `database_driver` | `''` | 数据库驱动标签（如 `mysql`）。初始化后会把 `DbManager` 得到的真实驱动回填到该 options 供上层读取。 |
| `cli_command_with_common` | `true` | 是否把内置默认 CLI 命令集（`DuckPhp\Component\Command`）登记进当前应用的命令列表。 |
| `lang_default` | `null` | 多语言默认语言（与 Lang 组件共享；不做进一步检测的兜底）。 |
| `lang_final` | `null` | 最终语言；设置后不再自动检测、直接以它为准。 |
| `local_database` | `false` | 为 `true` 时，本 App（含其子 app Phase）新建一份独立的 `DbManager`（不计入公共容器共享，互不干扰）。 |
| `local_redis` | `false` | 同 semantics 的 Redis：true 时独立 `RedisManager`。 |
| `exception_reporter` | `null` | （该类文档未写说明） |
| `exception_for_project` | `null` | （该类文档未写说明） |

## 核心

### DuckPhp\Core\App

DuckPhp\Core\App

类文档：[DuckPhp\Core\App](Core-App.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_runtime` | `'runtime'` | 运行期相对项目根目录（或绝对路径）。`getRuntimePath()` 返回。 |
| `path_config` | `'config'` | 配置文件目录（`getConfigFile()` 基于它+Phase覆盖查找）。 |
| `default_exception_do_log` | `true` | 默认异常处理器是否写日志。 |
| `close_resource_at_output` | `false` | 输出结束是否统一关闭/回收资源（默认关闭）。 |
| `html_handler` | `null` | （预留/扩展用）任意 html 处理器回调。 |
| `lang_handler` | `null` | 传入后 `lang()`/`langText()` 将优先走它，而不再 fallback 简易替换。 |
| `is_maintain` | `false` | 维护标记。命中时 `prepareServe()` 渲维护页（`error_maintain`）。 |
| `skip_404` | `false` | 跳过 404 展示（`skip404Handler()` 会置真）。 |
| `error_404` | `null` | 404 时用（路径或可调用）。null → 内置 404 占位/开发信息。 |
| `error_500` | `null` | 异常默认总页（路径或可调用）。null → debug 下详细、非 debug 精简。 |
| `error_debug` | `null` | 开发期错误视图/可调用。null → 内置 fieldset 回执。 |
| `error_maintain` | `null` | 维护页视图/可调用。null → 内置 “Maintaining.”。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Setting 文件（相对根或绝对）。 |
| `setting_file_ignore_exists` | `true` | 设置文件缺失时是否忽略（并不抛错）。 |
| `setting_file_enable` | `true` | 是否加载设置文件。 |
| `use_env_file` | `false` | 为真则在 loadSetting 一并读根 `.env`(INI) 合并。 |
| `setting` | `[]` | 直接以数组形式给出的应用设置（setting）初值；作为 `loadSetting()` 的起点，随后再合并 `.env`（可选）与设置文件。 |
| `installed` | `false` | 应用是否“已安装”（false 会触发安装跳转）。 |
| `url_install` | `'install'` | 未安装时跳到的安装 URL。 |
| `exception_map` | `[]` | 异常类映射表（`原异常类 => 替代异常类`）；`ProjectThrowOn/BusinessThrowOn/ControllerThrowOn` 抛异常前会先按它替换类名。 |

### DuckPhp\Core\AutoLoader

Core\AutoLoader 提供一套很轻的“命名空间 → 目录”自动加载

类文档：[DuckPhp\Core\AutoLoader](Core-AutoLoader.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根目录。缺省尝试 `realpath(getcwd().'/../')`。用于把相对路径归一。 |
| `namespace` | `''` | 应用主命名空间（如 `App`）。非空且 `skip_app_autoload` 为假时，会把 `namespace` 指到 `path_namespace`。 |
| `path_namespace` | `'app'` | 应用目录（主命名空间对应目录），可绝对；相对则相对 `path`。默认把 `app/` → `${namespace}\`。 |
| `skip_app_autoload` | `false` | 为真则不再自动把主 namespace 映射到 app 目录。 |
| `autoload_cache_in_cli` | `false` | 为真时 CLI 模式 `run()` 里会先 `cacheClasses()`（opcache 预编译）。 |
| `autoload_path_namespace_map` | `[]` | 额外 `path => namespace` 映射（不会翻转方向）。 |
| `psr-4` | `[]` | PSR-4 风格映射：`namespace => path`（初始化时翻转成 path=>namespace 装入 `namespace_paths`）。 |

### DuckPhp\Core\Console

DuckPHP 对 CLI 的命令处理根

类文档：[DuckPhp\Core\Console](Core-Console.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `console_command_classes` | `[]` | 命令注册表：`namespace => [ className => 方法前缀 ]`。前缀 `true` 会解释为 `command_`。 |
| `console_command_phase` | `[]` | `namespace => phase` 的映射，运行某命名空间命令前先切到对应 Phase。 |
| `console_command_default` | `'help'` | 无尾部位置参数时（仍给 `--` 一个命令串）用作默认命令词。 |
| `console_readlines_logfile` | `''` | 若给路径：`readLines` 在每次输入回显后把它写入该文件（相对 `path_runtime`）；相对实际 `path_runtime`。 |

### DuckPhp\Core\ExceptionManager

ExceptionManager（class ExceptionManager…

类文档：[DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `handle_all_dev_error` | `true` | 是否接管 PHP 错误处理器（dev path）。 |
| `handle_all_exception` | `true` | 是否接管全局异常处理器。 |
| `system_exception_handler` | `null` | 自定义异常安装回调 `function(callable $handler)`，可替代内建 set_exception_handler（极端/跨运行时用）。 |
| `handle_exception_on_init` | `true` | init 时立即 run 接管。 |
| `default_exception_handler` | `null` | 未匹配的自定义回退（通常填 App::OnDefaultException）。 |
| `dev_error_handler` | `null` | dev错误回调（通常 App::OnDevErrorHandler）。 |

### DuckPhp\Core\KernelTrait

KernelTrait 把“一个应用是什么、怎么跑”这件事写在一个 Trait…

类文档：[DuckPhp\Core\KernelTrait](Core-KernelTrait.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `is_debug` | `false` | 调试开关字段（源码注释：“no use, just align”，仅占位对齐；真正取舍请用 App::IsDebug() 等汇总逻辑）。 |
| `path` | `null` | 项目根目录。显式缺省时按 `SCRIPT_FILENAME`…上下两层推导后在 `initOptions()` 中补成绝对路径并以 `/` 结尾。 |
| `namespace` | `null` | 项目默认命名空间。缺省由当前实现类反推（`getDefaultProjectNameSpace`）。 |
| `name` | `''` | 该应用的短名；用于子应用 Phase 命名。 |
| `app` | `[]` | 子应用声明表。形如 `[类 => ['namespace'=>…,'controller_url_prefix'=>…, …]]`；`initChildren()` 会把它们逐个做成独立子 Phase。 |
| `cmd` | `[]` | 当前应用要注册的 CLI 命令类映射（`类 => "默认方法前缀"`）。由 Console 消费。 |
| `data` | `[]` | 预留数据槽（源码注释“no use in, just align”）。 |
| `ext` | `[]` | 层内要初始化的扩展组件列表：`类 => true|数组|'@方法'|EXT_* 常量`。在初始化阶段按 `initOptions→…→initComponents…OfExt` 生效。 |
| `cli_enable` | `true` | CLI 环境下是否进入命令处理（`execute()`）。为 `false` 即使 CLI 也走 Web。 |
| `skip_exception_check` | `false` | 为 `true` 时 `runException` 直接把异常再次抛出，不再进入框架统一的异常分发。 |
| `use_exit_exception` | `true` | 是否定义全局 `__EXIT_EXCEPTION`（为 `ExitException::class`）以让 exit 语义可捕获。 |
| `override_from` | `null` | 记“自己被谁改写覆盖”的类名。`override_class` 流程使用。 |
| `override_class` | `null` | 若给出，`init()` 时用此类接管当前初始化（构造后以 `override_from=get_class(原)` 重入 init）。 |
| `app_children_allow_mix_mode` | `true` | 允许子应用简写：用表键当 `controller_url_prefix`、值内含 `'class'` 指定真实子应用类。false 则要求结构化写法。 |
| `on_init` | `null` | (`callable`) 初始化中 `onInit()` 阶段被调。 |
| `on_inited` | `null` | (`callable`) 全部初始化完毕 `onInited()` 阶段被调。 |
| `on_request` | `null` | (`callable`) 每次 `serve()` 开头被调。 |

### DuckPhp\Core\Logger

Logger（PSR-3 注释，非 implements，尽力对齐接口）适合“…

类文档：[DuckPhp\Core\Logger](Core-Logger.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_log` | `'runtime'` | 日志目录（可绝对，可相对根）。 |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | 日志文件名模板；`%X` 由 `date(X)` 展开。 |
| `log_prefix` | `'DuckPhpLog'` | 写到行的前缀标识。 |

### DuckPhp\Core\Route

DuckPHP 的默认路由核心

类文档：[DuckPhp\Core\Route](Core-Route.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 项目命名空间（不含 `\\`）。控制器最终全名 = `namespace + namespace_controller + 路径类名 + controller_class_postfix`。 |
| `namespace_controller` | `'Controller'` | 控制器所在子命名空间；以 `\\` 开头表示绝对子命名空间（不需拼上 `namespace`）。 |
| `controller_path_ext` | `''` | 路径扩展名过滤（如 `.html`）。设置后只有路径带此后缀才通过匹配，匹配成功后该后缀会被剔除。留空表示不校验。 |
| `controller_welcome_class` | `'Main'` | “欢迎控制器”：URL 很短 / 路径块为空时使用的控制器类名段。 |
| `controller_welcome_class_visible` | `false` | 是否允许路径真正显式写 `Main/…` 这一段。`false` 时显式出现欢迎控制器名会判 `E009` 并 404。 |
| `controller_welcome_method` | `'index'` | 当 URL 末尾“方法段”为空时作为默认方法名。 |
| `controller_class_adjust` | `''` | 类名/方法名**归一化规则**。字符串可含多个分号分隔指令：`uc_class`（路径最后一块 ucfirst）、`uc_method`（方法名 ucfirst）、`uc_full_class`（每段都 ucfirst）。也支持给它传数组。 |
| `controller_class_base` | `''` | 控制器基类约束。设置后目标控制器必须 `is_subclass_of` 该基类，否则 `E004`。字符串里可用 `~` 占位并在判断时替换为控制器命名空间前缀。 |
| `controller_class_postfix` | `'Controller'` | 类名后缀（读路径得到的块再补上它）。 |
| `controller_method_prefix` | `''` | 方法名前缀。留空意味着读到的路径方法段原样成为要调用的方法；很多宿主（如 `DuckPhp` 应用）把它配成 `action_`。 |
| `controller_prefix_post` | `'do_'` | POST 专用次级前缀。POST 请求时先尝试 `prefix + do_ + 方法名`（即 `action_do_*`），命中才替换调用。留空则不启用该逻辑。 |
| `controller_class_map` | `[]` | 控制器类名映射表：`旧全名 => 新全名`。可静态配置；也可运行时用 `replaceController()` 写入。 |
| `controller_resource_prefix` | `''` | 静态资源前缀。供 `_Res()/Res()` 生成资源 URL（可为 `https://cdn…` / `//cdn…` / 相对 `res/`）。 |
| `controller_url_prefix` | `''` | URL 路径前缀。会被参与 URL 校验与生成（见 `pathToClassAndMethod()` 与 `getUrlBasePath()`）。 |
| `controller_fix_mistake_path_info` | `true` | 当没有 PATH_INFO 且脚本即 `/index.php` 时，自动从 `REQUEST_URI` 的 path 补齐并回写 PATH_INFO。 |

### DuckPhp\Core\Runtime

Runtime（class Runtime extends Component…

类文档：[DuckPhp\Core\Runtime](Core-Runtime.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `use_output_buffer` | `false` | 是否开启整体输出缓冲；开启时 run() 开始一个 ob、clear() 负责 flush。 |

### DuckPhp\Core\SuperGlobal

SuperGlobal 提供在不污染全局符号的前提下操作 HTTP 超全局的手段

类文档：[DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `superglobal_auto_define` | `false` | init 时若为真：自动 `DefineSuperGlobalContext()` 并 `_LoadSuperGlobalAll()`（把当前超全局快照入属性）。 |

### DuckPhp\Core\View

DuckPHP 的默认视图实现（class View extends Comp…

类文档：[DuckPhp\Core\View](Core-View.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（组件层对“相对路径”的基准）。 |
| `path_view` | `'view'` | 视图目录相对名（相对 `path`；可绝对则用之）。`getViewFile()` 用它拼 `{$path}/{$path_view}/{$file}.php`。 |
| `view_skip_notice_error` | `true` | 渲染时是否临时屏蔽 `E_NOTICE` 噪声。为 true 时 `_Show` 会临时去掉 E_NOTICE 再到结束恢复。 |

## 自带组件

### DuckPhp\Component\Configer

Configer extends ComponentBase 负责把 {fil…

类文档：[DuckPhp\Component\Configer](Component-Configer.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（相对路径基准）。 |
| `path_config` | `'config'` | 配置目录名（相对 `path`，可给绝对覆盖）。 |

### DuckPhp\Component\DbManager

DbManager extends ComponentBase 是 DuckP…

类文档：[DuckPhp\Component\DbManager](Component-DbManager.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver` | `''` | 驱动返回（init/setting 推导并回填）。 |
| `database` | `null` | 单个连接配置（dsn/…）便捷。 |
| `database_list` | `null` | 连接配置列表(数组)。给此项优先。 |
| `database_list_reload_by_setting` | `true` | 未显式 configuration 时（不提供 database）允许从 setting 取 database_list。 |
| `database_list_try_single` | `true` | database_list 空时可回退 单 `database`/`setting.database` 包数组。 |
| `database_log_sql_query` | `false` | 是否对每条 SQL 写日志。 |
| `database_log_sql_level` | `'debug'` | 上面 SQL 日志级别。 |
| `database_class` | `''` | 自定义连接类空则 `Db`。 |

### DuckPhp\Component\ExtOptionsLoader

ExtOptionsLoader处理一类“要记得、要能在下次跑时仍生效”的动态…

类文档：[DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `data_file_enable` | `true` | 是否启用持久外置 options 文件机制（调用装配的上层再决定）。 |
| `data_file_json_file` | `'DuckPhpData.config.json'` | 数据文件名（相对 root runtime 或绝对）。 |
| `data_file_bump_allowed` | `true` | bump 时是否允许把段写回 app options。 |
| `data_file_bump_keys` | `['installed' => true, 'redis' => true, 'database' => true, 'local_redis' => true, 'local_database' => true]` | 允许直接整体回写的这些键。 |
| `data_file_bump_prefix_keys` | `['redis_' => true, 'database_' => true]` | 这些前缀下的任意键会被回写。 |

### DuckPhp\Component\Lang

Lang extends ComponentBase 提供简单而完整的界面翻译

类文档：[DuckPhp\Component\Lang](Component-Lang.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `lang_final` | `null` | 最终语言，设就别再探测；非根子层 follow root 会取根值。 |
| `lang_default` | `null` | 兜底：别的方式都不中时返回它。 |
| `lang_detect_mode` | `['url', 'cookie','header', 'cli','default']` | 探测顺序与可用入口名单。 |
| `lang_follow_root` | `true` | 子应用时候跟随根 Final。 |
| `lang_url_param` | `'lang'` | url 探测参数名（例如 `?lang=zh_CN`）。 |
| `lang_cookie_name` | `'lang'` | cookie 名。 |
| `lang_file_path` | `'lang/'` | 语言目录（相对配置，实际由 Configer 拼 `{path}.php`）。 |
| `lang_simple_mode_only_sentences` | `[]` | 简单模式句子集：语言=>[key=>sentence]。非空则不读配置直接用它。 |

### DuckPhp\Component\Pager

Pager extends ComponentBase implements …

类文档：[DuckPhp\Component\Pager](Component-Pager.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `url` | `null` | 手工给分页基底 URL（空则 requestUri）。可含 `{page}` 占位符替换。 |
| `current` | `null` | 手动指定当前页；缺省由 GET {page_key} 得。 |
| `page_size` | `30` | 每页条数。 |
| `page_key` | `'page'` | 当前页 URL 参数名。 |
| `rewrite` | `null` | （callable）自定义写 URL 的回调当作 getUrl 分支；否则走 defaultGetUrl。 |

### DuckPhp\Component\RedisCache

RedisCache extends ComponentBase（注释 ali…

类文档：[DuckPhp\Component\RedisCache](Component-RedisCache.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `redis_cache_skip_replace` | `false` | 若 true 不 replace 全局 Cache（由自己取舍）。 |
| `redis_cache_prefix` | `''` | 本缓存键前缀。 |

### DuckPhp\Component\RedisManager

RedisManager extends ComponentBase 是 Du…

类文档：[DuckPhp\Component\RedisManager](Component-RedisManager.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `redis` | `null` | 单条连接（对象/数组 host,port,…）。 |
| `redis_list` | `null` | 多条配置数组（每项 `['host'=>…,'port'=>…,'auth'=>…,'select'=>…]`）。 |
| `redis_list_reload_by_setting` | `true` | 未给主机要时允许由 setting (`redis_list`/`redis`) 提供。 |
| `redis_list_try_single` | `true` | 列表缺时按单条 redis 包装。 |

### DuckPhp\Component\RouteHookPathInfoCompat

RouteHookPathInfoCompat extends Compone…

类文档：[DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path_info_compact_enable` | `true` | 开关（init 才装 hook/url handler）。 |
| `path_info_compact_action_key` | `'_r'` | 动作路由所在的 query 键。 |
| `path_info_compact_class_key` | `''` | 可选“模块（类路径段）”所在 query 键；留空则整路径都放 action键。 |

### DuckPhp\Component\RouteHookResource

RouteHookResource extends ComponentBase…

类文档：[DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（相对路径基准）。 |
| `path_resource` | `'res'` | 资源源目录（默认 `res`）。 |
| `path_document` | `'public'` | 发布根目录名（clone 目标）。 |
| `controller_url_prefix` | `null` | 路由资源 URL 前缀段（可选）。 |
| `controller_resource_prefix` | `''` | 访问前缀（例如 `res/` 或 `//cdn/…`）；决定 hook/clone 行为。 |

### DuckPhp\Component\RouteHookRewrite

RouteHookRewrite extends ComponentBase …

类文档：[DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 可选 url 前缀（命中前去掉、重写后会补回）。 |
| `rewrite_map` | `[]` | 映射表 `匹配模板 => 内部url`；模板以 `~` 开头时被当正则。 |

### DuckPhp\Component\RouteHookRouteMap

RouteHookRouteMap extends ComponentBase…

类文档：[DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `controller_url_prefix` | `''` | 匹配前可统一去掉 url 前缀。 |
| `route_map_important` | `[]` | 重要组（先探）。 |
| `route_map` | `[]` | 普通组（后探）。 |

### DuckPhp\Component\Validator

DuckPHP 的数据验证组件，采用“字段 => 规则字符串”的声明式写法

类文档：[DuckPhp\Component\Validator](Component-Validator.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `validator_exception_class` | `\Exception::class` | `check()`/`filter()` 校验失败时抛出的异常类（必须可被 `new` 且接受一个字符串消息）。 |
| `validator_skip_empty` | `true` | 非 `required` 字段值为空（`null` 或 `''`）时是否跳过其余规则。 |

## 可选扩展

### DuckPhp\Ext\CallableView

Core\View 的扩展

类文档：[DuckPhp\Ext\CallableView](Ext-CallableView.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `callable_view_head` | `null` | head 视图回调名（缺省用父类 `head_file`）。 |
| `callable_view_foot` | `null` | foot 视图回调名（缺省用父类 `foot_file`）。 |
| `callable_view_class` | `null` | 提供视图回调的类（可为类名字符串或对象）。 |
| `callable_view_is_object_call` | `true` | 类名为字符串且可 `_()` 时转为单例实例，否则 `new`。 |
| `callable_view_prefix` | `null` | 视图名加工前缀（如 `view_`，与 `/`→`_` 替换）。 |
| `callable_view_skip_replace` | `false` | 为 `true` 时不把自身替换成全局 `View` 单例。 |

### DuckPhp\Ext\DuckPhpInstaller

bin/duckphp 背后的命令行安装器，提供三个 CLI 命令

类文档：[DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 目标路径（`new` 的落盘目录 / 覆盖）。 |
| `namespace` | `''` | 新工程的命名空间（缺省自动探测/询问）。 |
| `force` | `false` | 目标已存在文件时是否强制覆盖。 |
| `autoloader` | `'vendor/autoload.php'` | 使用的 autoload 路径。 |
| `verbose` | `false` | 是否打印过程信息。 |
| `help` | `false` | 帮助开关。 |

### DuckPhp\Ext\EmptyView

Core\View 的扩展

类文档：[DuckPhp\Ext\EmptyView](Ext-EmptyView.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `empty_view_key_view` | `'view'` | 装配进数据的“视图键名”。 |
| `empty_view_key_wellcome_class` | `'Main/'` | 欢迎视图前缀（配合 trim 用）。 |
| `empty_view_trim_view_wellcome` | `true` | 是否去掉视图名的欢迎前缀。 |
| `empty_view_skip_replace` | `false` | 为 `true` 时不替换全局 `View::_()`。 |

### DuckPhp\Ext\JsonRpcExt

JSON-RPC 扩展的总控

类文档：[DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `jsonrpc_namespace` | `'JsonRpc'` | 客户端自动加载的命名空间前缀。 |
| `jsonrpc_backend` | `'https:` | 服务端地址（也可传 `[base, real_host]` 以使用 `CURLOPT_CONNECT_TO`）。 |
| `jsonrpc_is_debug` | `false` | 失败时是否在异常消息中带原始返回。 |
| `jsonrpc_enable_autoload` | `true` | 是否注册客户端类自动加载。 |
| `jsonrpc_check_token_handler` | `null` | 可选：给 curl 会话加 token 的回调。 |
| `jsonrpc_wrap_auto_adjust` | `true` | （保留配置）自动调整包装行为。 |
| `jsonrpc_service_interface` | `''` | 服务端校验：服务类需是该接口的子类才接受。 |
| `jsonrpc_service_namespace` | `''` | 服务类命名空间（客户端/服务端两侧补全用）。 |
| `jsonrpc_timeout` | `5` | curl 超时秒数。 |

### DuckPhp\Ext\JsonView

Core\View 的扩展

类文档：[DuckPhp\Ext\JsonView](Ext-JsonView.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `json_view_skip_replace` | `false` | 为 `true` 时不替换全局 `View::_()`。 |
| `json_view_skip_vars` | `[]` | 输出前需要从 `$data` 中剔除的键列表。 |

### DuckPhp\Ext\MiniRoute

极简版 MVC 路由（Core\Route 的子集）

类文档：[DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间。 |
| `namespace_controller` | `'Controller'` | 控制器子命名空间。 |
| `controller_path_ext` | `''` | 路径后缀要求（如 `.php`，为空不校验）。 |
| `controller_welcome_class` | `'Main'` | 欢迎控制器（空路径时用）。 |
| `controller_welcome_class_visible` | `false` | 欢迎类是否允许通过 URL 显式访问。 |
| `controller_welcome_method` | `'index'` | 欢迎方法。 |
| `controller_class_postfix` | `''` | 控制器类后缀。 |
| `controller_method_prefix` | `''` | 方法名前缀（如 `action_`）。 |
| `controller_class_map` | `[]` | 类名映射（替换）。 |
| `controller_resource_prefix` | `''` | 资源前缀（保留）。 |
| `controller_url_prefix` | `''` | URL 前缀（剥除）。 |

### DuckPhp\Ext\Misc

Misc 收集若干“杂项”工具

类文档：[DuckPhp\Ext\Misc](Ext-Misc.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（拼接相对 `path_lib` 用）。 |
| `path_lib` | `'lib'` | 库目录；以 `/` 开头视为绝对目录，否则拼在 `path` 下。 |

### DuckPhp\Ext\MyFacadesAutoLoader

MyFacadesAutoLoader 实现“Facade（门面）命名空间自动…

类文档：[DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `facades_namespace` | `'MyFacades'` | 门面类命名空间（会 trim 反斜杠）。 |
| `facades_map` | `[]` | 门面类名 → 真实类名的映射。 |
| `facades_enable_autoload` | `true` | 是否注册 `spl_autoload`（关闭则需自行触发）。 |

### DuckPhp\Ext\MyMiddlewareManager

中间件管理器扩展

类文档：[DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `middleware` | `[]` | 中间件列表（自外向内执行）。每项可为 callable，或字符串 `Class@method`（`_()` 单例）/ `Class->method`（`new`）。 |

### DuckPhp\Ext\RouteHookApiServer

“API 服务器”路由扩展

类文档：[DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `namespace` | `''` | 应用命名空间（拼相对子命名空间用）。 |
| `apiserver_base_class` | `''` | 服务类基类约束（支持 `~` 占位前缀；服务类须为其子类）。 |
| `apiserver_namespace` | `'Api'` | 服务类所在命名空间。 |
| `apiserver_class_postfix` | `''` | 服务类名后缀。 |
| `apiserver_use_singletonex` | `false` | 为 `true` 时经 `_()` 取单例（且动作名为 `G` 时拒绝）。 |
| `apiserver_404_as_exception` | `false` | 未命中时是否抛 `ReflectionException("404")`。 |

### DuckPhp\Ext\RouteHookDirectoryMode

RouteHookDirectoryMode 实现“目录/文件模式”路由

类文档：[DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `mode_dir_basepath` | `''` | 站点根目录（用于把文件路径折算成控制器路径）。 |

### DuckPhp\Ext\RouteHookFunctionRoute

“函数式路由”扩展

类文档：[DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `function_route` | `false` | 是否启用（保留开关位）。 |
| `function_route_method_prefix` | `'action_'` | 回调名前缀。 |
| `function_route_404_to_index` | `false` | 未命中时是否回退调用 `{prefix}index`。 |

### DuckPhp\Ext\RouteHookWebInstaller

**网页安装向导**

类文档：[DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `web_installer_use_database` | `true` | 安装流程是否包含数据库。 |
| `web_installer_use_redis` | `true` | 安装流程是否包含 Redis。 |
| `web_installer_database_drivers` | `['sqlite' => true, 'pgsql' => true, 'duckdb' => false]` | 可选的数据库驱动。 |
| `web_installer_view` | `''` | 自定义安装视图文件（空用内置视图）。 |
| `web_installer_view_block_custom` | `null` | 自定义区块模板（视图内嵌）。 |
| `web_installer_force` | `false` | 是否强制重装（先清表）。 |
| `web_installer_check_custom_callback` | `null` | 自定义“环境检查”回调。 |
| `web_installer_do_custom_callback` | `null` | 自定义“安装执行”回调。 |
| `web_installer_render_custom_callback` | `null` | 自定义“渲染”回调。 |
| `web_installer_default_sentences` | `[]` | 界面文案覆盖（空则用内置英文默认文案 `builtin_default_sentences`）。 |

### DuckPhp\Ext\RouteLister

RouteLister extends ComponentBase 提供“把系…

类文档：[DuckPhp\Ext\RouteLister](Ext-RouteLister.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `classes_to_get_controller_path` | `[]` | 额外“待尝试的类/控制器文件”候选：仅用于**寻找控制器目录**（同 welcome。config path；缺文件会继续下一个），被找到后再递归枚举其下 .php 判定 Controller）。 |

### DuckPhp\Ext\SqlDumper

数据库“结构/数据导出与安装”扩展

类文档：[DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径（配合 `path_sql_dump` 定位导出目录）。 |
| `path_sql_dump` | `'config'` | SQL 文件存放子目录。 |
| `sql_dump_include_tables` | `[]` | 显式包含的表（支持 `@` 占位替换为表前缀）。 |
| `sql_dump_exclude_tables` | `[]` | 排除的表。 |
| `sql_dump_data_tables` | `[]` | 需要导出数据的表（写进 `.data.sql`）。 |
| `sql_dump_include_tables_all` | `false` | 为 `true` 时导出数据库全部表（忽略 by_model）。 |
| `sql_dump_include_tables_by_model` | `true` | 按工程 Model 类（`namespace\Model\Base` 同目录下类）自动搜集表。 |
| `sql_dump_debug_show_sql` | `false` | 执行 SQL 时打印每条语句。 |

### DuckPhp\Ext\SqlDumperSupporter

SqlDumper 的驱动适配基类

类文档：[DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `database_driver_SqlDumperSupporter_map` | `[...]` | 驱动名 → 适配子类映射。 |

## HTTP 服务器

### DuckPhp\HttpServer\HttpServer

DuckPHP 内置的“用 PHP 内置服务器跑项目”的启动器

类文档：[DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `host` | `'127.0.0.1'` | 监听地址；也可被 CLI 参数 `--host`/`-H` 覆盖。 |
| `port` | `'8080'` | 监听端口；也可被 CLI 参数 `--port`/`-P` 覆盖。 |
| `path` | `''` | 项目根路径，与 `path_document` 拼成 docroot。 |
| `path_document` | `'public'` | 文档目录名；实际 docroot = `path/path_document`。 |
| `workers` | `null` | 非空时用 `PHP_CLI_SERVER_WORKERS=N` 启动多进程内置服务器。 |

## 管理员系统

### DuckPhp\GlobalAdmin\GlobalAdmin

管理员体系的**完整实现**

类文档：[DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `globaladmin_is_authed_redirect` | `true` | `login()`/`logout()` 完成后是否自动 302（登录跳 `urlForHome()`、退出跳 `urlForLogin()`）。 |
| `globaladmin_url_home` | `null` | 后台首页 URL；未配时取 App 的同名上下文选项 `url_admin_home`，再退回 `'/'`。 |
| `globaladmin_url_login` | `null` | 后台登录 URL；未配时退回 `'/'`（这是 `throwLoginOn()` 的 302 目标）。 |
| `globaladmin_url_logout` | `null` | 后台退出 URL；未配时取 App 的上下文选项 `url_admin_logout`，再退回 `'/'`。 |
| `globaladmin_view_file_header` | `null` | 后台页眉视图文件（非空才渲染，结果并入 `__view_data.header`）。 |
| `globaladmin_view_file_footer` | `null` | 后台页脚视图文件（非空才渲染，结果并入 `__view_data.footer`）。 |
| `globaladmin_enable_callback_singleton` | `true` | 回调是 `[类名, 方法]` 时，是否先把类名换成 `类名::_()` 单例实例。 |
| `globaladmin_local_service` | `null` | 返回 `AdminServiceInterface` 实现的回调（键缺失时 `localService()` 抛异常）。 |
| `globaladmin_login_service` | `null` | 返回 `AdminLoginServiceInterface` 实现的回调。 |
| `globaladmin_login_session` | `null` | 返回 `AdminSessionInterface` 实现的回调。 |
| `globaladmin_ext_view_data_callback` | `null` | 追加视图数据的回调（键缺失时**不报错**，跳过即可；与上面三个「必需」回调不同）。 |
| `globaladmin_need_login_callback` | `null` | 未登录时的自定义处理；配了就**不会**走默认的 302/JSON，只回调再 `exit()`。 |

## 用户系统

### DuckPhp\GlobalUser\GlobalUser

用户体系的**完整实现**

类文档：[DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md)

| 选项 | 默认值 | 说明 |
|---|---|---|
| `globaluser_is_authed_redirect` | `true` | `register()`/`login()`/`logout()` 完成后是否自动 302（注册/登录跳 `urlForHome()`、退出跳 `urlForLogin()`）。 |
| `globaluser_url_home` | `null` | 站内首页 URL；未配时取 App 的上下文选项 `url_user_home`，再退回 `'/'`。 |
| `globaluser_url_register` | `null` | 注册页 URL；未配时退回 `'/'`。 |
| `globaluser_url_login` | `null` | 登录页 URL；未配时退回 `'/'`（这是 `throwLoginOn()` 的 302 目标）。 |
| `globaluser_url_logout` | `null` | 退出 URL；未配时取 App 的上下文选项 `url_user_logout`，再退回 `'/'`。 |
| `globaluser_view_file_header` | `null` | 用户页眉视图文件（非空才渲染，结果并入 `__view_data.header`）。 |
| `globaluser_view_file_footer` | `null` | 用户页脚视图文件（非空才渲染，结果并入 `__view_data.footer`）。 |
| `globaluser_enable_callback_singleton` | `true` | 回调是 `[类名, 方法]` 时，是否先把类名换成 `类名::_()` 单例实例。 |
| `globaluser_local_service` | `null` | 返回 `UserServiceInterface` 实现的回调（键缺失时 `localService()` 抛异常）。 |
| `globaluser_login_service` | `null` | 返回 `UserLoginServiceInterface` 实现的回调。 |
| `globaluser_login_session` | `null` | 返回 `UserSessionInterface` 实现的回调。 |
| `globaluser_ext_view_data_callback` | `null` | 追加视图数据的回调（键缺失时**不报错**，跳过即可；与上面三个「必需」回调不同）。 |
| `globaluser_need_login_callback` | `null` | 未登录时的自定义处理；配了就**不会**走默认的 302/JSON，只回调再 `exit()`。 |

## 隐藏选项

框架会读、但**故意不写进 `$options`** 的键（源码里集中在 `$hidden_options` 表）。它们可以像普通选项一样在 `init()`/`$options` 里给出，只是不出现在上面的正式选项里。

| 选项 | 默认值 | 出处 | 说明 |
|---|---|---|---|
| `not_empty` | `true` | DuckPhp::$common_options | 声明在默认选项里、但源码中没有任何读取点（历史遗留，可忽略）。 |
| `url_admin_home` | `null` | GlobalAdmin\Admin::urlForHome() | 后台首页 URL 的「应用级」覆盖：优先于组件的 `globaladmin_url_home`。 |
| `url_user_home` | `null` | GlobalUser\User::urlForHome() | 站内首页 URL 的「应用级」覆盖：优先于组件的 `globaluser_url_home`。 |
| `session_prefix` | `''` | Foundation\Controller\SessionTrait | 会话名的前缀（根应用的设置也走这里）。 |
| `table_prefix` | `''` | Ext\SqlDumper / Ext\RouteHookWebInstaller | 数据库表名前缀，导出/安装 SQL 时用 `{prefix}` 占位替换。 |
| `exception_for_business` | `\Exception::class` | CoreHelper::_BusinessThrowOn() | `BusinessThrowOn()` 未显式指定时的异常类。 |
| `exception_for_controller` | `\Exception::class` | CoreHelper::_ControllerThrowOn() | `ControllerThrowOn()` 未显式指定时的异常类。 |
| `duckphp_all_in_one_wrap_header_foot` | `false` | DuckPhpAllInOne::onInited() | AllInOne 入口是否给 `_Show()` 包 head/foot 视图（该类自己会置 true）。 |
| `permission_menu_tree_for_admin` | `null` | Ext\PermissionMenu::getMenuJsonFileConfig() | 后台权限菜单树的配置文件（相对 `path_config`）。 |
| `duckcoverage_test_lister` | `null` | 外部包 dvaknheo/duckcoverage | 配合该 composer 包做覆盖测试使用，框架自身不读。 |
| `background` | `false` | HttpServer::run*() | 内置服务器是否后台运行；CLI 开关 `-b/--background` 会把它置 true。 |

## 相关链接

- [应用选项（按字母顺序索引）](options-index.md)
- [应用选项总览（首页）](options.md)
- [应用设置 Setting](setting.md)
