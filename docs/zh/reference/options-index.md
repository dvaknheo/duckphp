# 应用选项（按字母顺序索引）

> 本页由 `docs/scripts/gen-options-docs.php` 生成，**请勿手改**：改选项请改 `src/` 与对应类文档，然后重跑生成器。

一共 **221** 个选项；同名选项出现在多个类时**合并为一行**，来源类并列。隐藏选项见 [按类分组](options-by-class.md#隐藏选项) 文末（本页只收正式选项）。

按类查看：[应用选项（按类分组）](options-by-class.md) · 选项机制：[应用选项总览](options.md)

**跳转**：[A](#a) · [C](#c) · [D](#d) · [E](#e) · [F](#f) · [H](#h) · [I](#i) · [J](#j) · [L](#l) · [M](#m) · [N](#n) · [O](#o) · [P](#p) · [R](#r) · [S](#s) · [U](#u) · [V](#v) · [W](#w)

---

## a

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `admin_callback_for_add_ext_view_data` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `admin_callback_for_data` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 取当前管理员数据（数组）的回调。 |
| `admin_callback_for_id` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 取当前管理员 id 的回调。 |
| `admin_callback_for_local_service` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 返回本地 `AdminServiceInterface` 实现的回调。 |
| `admin_callback_for_login_service` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 登录服务回调（`login()/logout()` 经 `getLoginBusiness()` 调用它）。 |
| `admin_callback_for_name` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 取当前管理员名的回调。 |
| `admin_callback_for_session` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 管理员会话实现回调（返回 `AdminSessionInterface`）；配置后 `id()/name()` 优先读会话。 |
| `admin_callback_for_url_for_home` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 生成首页 URL 的回调（优先于 `admin_url_home`）。 |
| `admin_callback_for_url_for_login` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 生成登录 URL 的回调（优先于 `admin_url_login`）。 |
| `admin_callback_for_url_for_logout` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 生成退出 URL 的回调（优先于 `admin_url_logout`）。 |
| `admin_default_exception_class` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 未登录时抛出的异常类（缺省用 `AdminException::class`）；只在会话模式（配了 `admin_callback_for_session`）下的 `id()/name()` 里生效。 |
| `admin_enable_callback_singleton` | `true` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 回调为 `[类名, 方法]` 数组时，是否先把类名转成 `类名::_()` 单例实例。 |
| `admin_loginout_auto_redirect` | `true` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | `login()/logout()` 完成后是否自动 302（登录跳 home、退出跳 login）。 |
| `admin_provider` | `''` | [DuckPhp\DuckPhp](DuckPhp.md) | 自定义管理员提供者类名；非空时会在内部阶段实例化并交给 `GlobalAdmin`。 |
| `admin_url_home` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 后台首页 URL（未配回调时用 `__url()` 生成）。 |
| `admin_url_login` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 后台登录 URL。 |
| `admin_url_logout` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 后台退出 URL。 |
| `admin_view_file_footer` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 后台页脚视图文件（渲染时并入 `__view_data.footer`）。 |
| `admin_view_file_header` | `null` | [DuckPhp\GlobalAdmin\GlobalAdmin](GlobalAdmin-GlobalAdmin.md) | 后台页头视图文件（渲染时并入 `__view_data.header`）。 |
| `api_server_404_as_exception` | `false` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 未命中时是否抛 `ReflectionException("404")`。 |
| `api_server_base_class` | `''` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 服务类基类约束（支持 `~` 占位前缀；服务类须为其子类）。 |
| `api_server_class_postfix` | `''` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 服务类名后缀。 |
| `api_server_namespace` | `'Api'` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 服务类所在命名空间。 |
| `api_server_use_singletonex` | `false` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 为 `true` 时经 `_()` 取单例（且动作名为 `G` 时拒绝）。 |
| `app` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 子应用声明表。形如 `[类 => ['namespace'=>…,'controller_url_prefix'=>…, …]]`；`initChildren()` 会把它们逐个做成独立子 Phase。 |
| `app_children_allow_mix_mode` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 允许子应用简写：用表键当 `controller_url_prefix`、值内含 `'class'` 指定真实子应用类。false 则要求结构化写法。 |
| `autoload_cache_in_cli` | `false` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 为真时 CLI 模式 `run()` 里会先 `cacheClasses()`（opcache 预编译）。 |
| `autoload_path_namespace_map` | `[]` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 额外 `path => namespace` 映射（不会翻转方向）。 |
| `autoloader` | `'vendor/autoload.php'` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 使用的 autoload 路径。 |

## c

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `callable_view_class` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 提供视图回调的类（可为类名字符串或对象）。 |
| `callable_view_foot` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | foot 视图回调名（缺省用父类 `foot_file`）。 |
| `callable_view_head` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | head 视图回调名（缺省用父类 `head_file`）。 |
| `callable_view_is_object_call` | `true` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 类名为字符串且可 `_()` 时转为单例实例，否则 `new`。 |
| `callable_view_prefix` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 视图名加工前缀（如 `view_`，与 `/`→`_` 替换）。 |
| `callable_view_skip_replace` | `false` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 为 `true` 时不把自身替换成全局 `View` 单例。 |
| `classes_to_get_controller_path` | `[]` | [DuckPhp\Component\RouteLister](Component-RouteLister.md) | 额外“待尝试的类/控制器文件”候选：仅用于**寻找控制器目录**（同 welcome。config path；缺文件会继续下一个），被找到后再递归枚举其下 .php 判定 Controller）。 |
| `cli_command_with_common` | `true` | [DuckPhp\DuckPhp](DuckPhp.md) | 是否把内置默认 CLI 命令集（`DuckPhp\Component\Command`）登记进当前应用的命令列表。 |
| `cli_enable` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | CLI 环境下是否进入命令处理（`execute()`）。为 `false` 即使 CLI 也走 Web。 |
| `close_resource_at_output` | `false` | [DuckPhp\Core\App](Core-App.md) | 输出结束是否统一关闭/回收资源（默认关闭）。 |
| `cmd` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 当前应用要注册的 CLI 命令类映射（`类 => "默认方法前缀"`）。由 Console 消费。 |
| `console_command_classes` | `[]` | [DuckPhp\Core\Console](Core-Console.md) | 命令注册表：`namespace => [ className => 方法前缀 ]`。前缀 `true` 会解释为 `command_`。 |
| `console_command_default` | `'help'` | [DuckPhp\Core\Console](Core-Console.md) | 无尾部位置参数时（仍给 `--` 一个命令串）用作默认命令词。 |
| `console_command_phase` | `[]` | [DuckPhp\Core\Console](Core-Console.md) | `namespace => phase` 的映射，运行某命名空间命令前先切到对应 Phase。 |
| `console_readlines_logfile` | `''` | [DuckPhp\Core\Console](Core-Console.md) | 若给路径：`readLines` 在每次输入回显后把它写入该文件（相对 `path_runtime`）；相对实际 `path_runtime`。 |
| `controller_class_adjust` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 类名/方法名**归一化规则**。字符串可含多个分号分隔指令：`uc_class`（路径最后一块 ucfirst）、`uc_method`（方法名 ucfirst）、`uc_full_class`（每段都 ucfirst）。也支持给它传数组。 |
| `controller_class_base` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 控制器基类约束。设置后目标控制器必须 `is_subclass_of` 该基类，否则 `E004`。字符串里可用 `~` 占位并在判断时替换为控制器命名空间前缀。 |
| `controller_class_map` | `[]` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器类名映射表：`旧全名 => 新全名`。可静态配置；也可运行时用 `replaceController()` 写入。 |
| `controller_class_postfix` | `'Controller'` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 类名后缀（读路径得到的块再补上它）。 |
| `controller_fix_mistake_path_info` | `true` | [DuckPhp\Core\Route](Core-Route.md) | 当没有 PATH_INFO 且脚本即 `/index.php` 时，自动从 `REQUEST_URI` 的 path 补齐并回写 PATH_INFO。 |
| `controller_method_prefix` | `''` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 方法名前缀。留空意味着读到的路径方法段原样成为要调用的方法；很多宿主（如 `DuckPhp` 应用）把它配成 `action_`。 |
| `controller_path_ext` | `''` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 路径扩展名过滤（如 `.html`）。设置后只有路径带此后缀才通过匹配，匹配成功后该后缀会被剔除。留空表示不校验。 |
| `controller_prefix_post` | `'do_'` | [DuckPhp\Core\Route](Core-Route.md) | POST 专用次级前缀。POST 请求时先尝试 `prefix + do_ + 方法名`（即 `action_do_*`），命中才替换调用。留空则不启用该逻辑。 |
| `controller_resource_prefix` | `''` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) / [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 访问前缀（例如 `res/` 或 `//cdn/…`）；决定 hook/clone 行为。 |
| `controller_url_prefix` | `null` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) / [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) / [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) / [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 路由资源 URL 前缀段（可选）。 |
| `controller_welcome_class` | `'Main'` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | “欢迎控制器”：URL 很短 / 路径块为空时使用的控制器类名段。 |
| `controller_welcome_class_visible` | `false` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 是否允许路径真正显式写 `Main/…` 这一段。`false` 时显式出现欢迎控制器名会判 `E009` 并 404。 |
| `controller_welcome_method` | `'index'` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 当 URL 末尾“方法段”为空时作为默认方法名。 |
| `current` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | 手动指定当前页；缺省由 GET {page_key} 得。 |

## d

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `data` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 预留数据槽（源码注释“no use in, just align”）。 |
| `data_file_bump_allowed` | `true` | [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | bump 时是否允许把段写回 app options。 |
| `data_file_bump_keys` | `['installed' => true, 'redis' => true, 'database' => true, 'local_redis' => true, 'local_database' => true]` | [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | 允许直接整体回写的这些键。 |
| `data_file_bump_prefix_keys` | `['redis_' => true, 'database_' => true]` | [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | 这些前缀下的任意键会被回写。 |
| `data_file_enable` | `true` | [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) / [DuckPhp\DuckPhp](DuckPhp.md) | 是否启用持久外置 options 文件机制（调用装配的上层再决定）。 |
| `data_file_json_file` | `'DuckPhpData.config.json'` | [DuckPhp\Component\ExtOptionsLoader](Component-ExtOptionsLoader.md) | 数据文件名（相对 root runtime 或绝对）。 |
| `database` | `null` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 单个连接配置（dsn/…）便捷。 |
| `database_class` | `''` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 自定义连接类空则 `Db`。 |
| `database_driver` | `''` | [DuckPhp\Component\DbManager](Component-DbManager.md) / [DuckPhp\DuckPhp](DuckPhp.md) | 驱动返回（init/setting 推导并回填）。 |
| `database_driver_SqlDumperSupporter_map` | `[...]` | [DuckPhp\Ext\SqlDumperSupporter](Ext-SqlDumperSupporter.md) | 驱动名 → 适配子类映射。 |
| `database_list` | `null` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 连接配置列表(数组)。给此项优先。 |
| `database_list_reload_by_setting` | `true` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 未显式 configuration 时（不提供 database）允许从 setting 取 database_list。 |
| `database_list_try_single` | `true` | [DuckPhp\Component\DbManager](Component-DbManager.md) | database_list 空时可回退 单 `database`/`setting.database` 包数组。 |
| `database_log_sql_level` | `'debug'` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 上面 SQL 日志级别。 |
| `database_log_sql_query` | `false` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 是否对每条 SQL 写日志。 |
| `default_exception_do_log` | `true` | [DuckPhp\Core\App](Core-App.md) | 默认异常处理器是否写日志。 |
| `default_exception_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 未匹配的自定义回退（通常填 App::OnDefaultException）。 |
| `dev_error_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | dev错误回调（通常 App::OnDevErrorHandler）。 |

## e

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `empty_view_key_view` | `'view'` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 装配进数据的“视图键名”。 |
| `empty_view_key_wellcome_class` | `'Main/'` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 欢迎视图前缀（配合 trim 用）。 |
| `empty_view_skip_replace` | `false` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 为 `true` 时不替换全局 `View::_()`。 |
| `empty_view_trim_view_wellcome` | `true` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 是否去掉视图名的欢迎前缀。 |
| `error_404` | `null` | [DuckPhp\Core\App](Core-App.md) | 404 时用（路径或可调用）。null → 内置 404 占位/开发信息。 |
| `error_500` | `null` | [DuckPhp\Core\App](Core-App.md) | 异常默认总页（路径或可调用）。null → debug 下详细、非 debug 精简。 |
| `error_debug` | `null` | [DuckPhp\Core\App](Core-App.md) | 开发期错误视图/可调用。null → 内置 fieldset 回执。 |
| `error_maintain` | `null` | [DuckPhp\Core\App](Core-App.md) | 维护页视图/可调用。null → 内置 “Maintaining.”。 |
| `exception_for_project` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | project 主异常类名；给了 exception_reporter 时作为 assign 目标基类。 |
| `exception_map` | `[]` | [DuckPhp\Core\App](Core-App.md) | 异常类映射表（`原异常类 => 替代异常类`）；`ProjectThrowOn/BusinessThrowOn/ControllerThrowOn` 抛异常前会先按它替换类名。 |
| `exception_reporter` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 项目级“异常报告类”（要有静态 OnException），会在 init 时 assign。 |
| `ext` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) / [DuckPhp\DuckPhp](DuckPhp.md) | 层内要初始化的扩展组件列表：`类 => true|数组|'@方法'|EXT_* 常量`。在初始化阶段按 `initOptions→…→initComponents…OfExt` 生效。 |

## f

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `facades_enable_autoload` | `true` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 是否注册 `spl_autoload`（关闭则需自行触发）。 |
| `facades_map` | `[]` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 门面类名 → 真实类名的映射。 |
| `facades_namespace` | `'MyFacades'` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 门面类命名空间（会 trim 反斜杠）。 |
| `force` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 目标已存在文件时是否强制覆盖。 |
| `function_route` | `false` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 是否启用（保留开关位）。 |
| `function_route_404_to_index` | `false` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 未命中时是否回退调用 `{prefix}index`。 |
| `function_route_method_prefix` | `'action_'` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 回调名前缀。 |

## h

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `handle_all_dev_error` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 是否接管 PHP 错误处理器（dev path）。 |
| `handle_all_exception` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 是否接管全局异常处理器。 |
| `handle_exception_on_init` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | init 时立即 run 接管。 |
| `help` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 帮助开关。 |
| `host` | `'127.0.0.1'` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 监听地址；也可被 CLI 参数 `--host`/`-H` 覆盖。 |
| `html_handler` | `null` | [DuckPhp\Core\App](Core-App.md) | （预留/扩展用）任意 html 处理器回调。 |

## i

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `installed` | `false` | [DuckPhp\Core\App](Core-App.md) | 应用是否“已安装”（false 会触发安装跳转）。 |
| `is_debug` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 调试开关字段（源码注释：“no use, just align”，仅占位对齐；真正取舍请用 App::IsDebug() 等汇总逻辑）。 |
| `is_maintain` | `false` | [DuckPhp\Core\App](Core-App.md) | 维护标记。命中时 `prepareServe()` 渲维护页（`error_maintain`）。 |

## j

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `json_view_skip_replace` | `false` | [DuckPhp\Ext\JsonView](Ext-JsonView.md) | 为 `true` 时不替换全局 `View::_()`。 |
| `json_view_skip_vars` | `[]` | [DuckPhp\Ext\JsonView](Ext-JsonView.md) | 输出前需要从 `$data` 中剔除的键列表。 |
| `jsonrpc_backend` | `'https:` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 服务端地址（也可传 `[base, real_host]` 以使用 `CURLOPT_CONNECT_TO`）。 |
| `jsonrpc_check_token_handler` | `null` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 可选：给 curl 会话加 token 的回调。 |
| `jsonrpc_enable_autoload` | `true` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 是否注册客户端类自动加载。 |
| `jsonrpc_is_debug` | `false` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 失败时是否在异常消息中带原始返回。 |
| `jsonrpc_namespace` | `'JsonRpc'` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 客户端自动加载的命名空间前缀。 |
| `jsonrpc_service_interface` | `''` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 服务端校验：服务类需是该接口的子类才接受。 |
| `jsonrpc_service_namespace` | `''` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 服务类命名空间（客户端/服务端两侧补全用）。 |
| `jsonrpc_timeout` | `5` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | curl 超时秒数。 |
| `jsonrpc_wrap_auto_adjust` | `true` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | （保留配置）自动调整包装行为。 |

## l

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `lang_cookie_name` | `'lang'` | [DuckPhp\Component\Lang](Component-Lang.md) | cookie 名。 |
| `lang_default` | `null` | [DuckPhp\Component\Lang](Component-Lang.md) / [DuckPhp\DuckPhp](DuckPhp.md) | 兜底：别的方式都不中时返回它。 |
| `lang_detect_mode` | `['url', 'cookie','header', 'cli','default']` | [DuckPhp\Component\Lang](Component-Lang.md) | 探测顺序与可用入口名单。 |
| `lang_file_path` | `'lang/'` | [DuckPhp\Component\Lang](Component-Lang.md) | 语言目录（相对配置，实际由 Configer 拼 `{path}.php`）。 |
| `lang_final` | `null` | [DuckPhp\Component\Lang](Component-Lang.md) / [DuckPhp\DuckPhp](DuckPhp.md) | 最终语言，设就别再探测；非根子层 follow root 会取根值。 |
| `lang_follow_root` | `true` | [DuckPhp\Component\Lang](Component-Lang.md) | 子应用时候跟随根 Final。 |
| `lang_handler` | `null` | [DuckPhp\Core\App](Core-App.md) | 传入后 `lang()`/`langText()` 将优先走它，而不再 fallback 简易替换。 |
| `lang_simple_mode_only_sentences` | `[]` | [DuckPhp\Component\Lang](Component-Lang.md) | 简单模式句子集：语言=>[key=>sentence]。非空则不读配置直接用它。 |
| `lang_url_param` | `'lang'` | [DuckPhp\Component\Lang](Component-Lang.md) | url 探测参数名（例如 `?lang=zh_CN`）。 |
| `local_database` | `false` | [DuckPhp\DuckPhp](DuckPhp.md) | 为 `true` 时，本 App（含其子 app Phase）新建一份独立的 `DbManager`（不计入公共容器共享，互不干扰）。 |
| `local_redis` | `false` | [DuckPhp\DuckPhp](DuckPhp.md) | 同 semantics 的 Redis：true 时独立 `RedisManager`。 |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | [DuckPhp\Core\Logger](Core-Logger.md) | 日志文件名模板；`%X` 由 `date(X)` 展开。 |
| `log_prefix` | `'DuckPhpLog'` | [DuckPhp\Core\Logger](Core-Logger.md) | 写到行的前缀标识。 |

## m

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `middleware` | `[]` | [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | 中间件列表（自外向内执行）。每项可为 callable，或字符串 `Class@method`（`_()` 单例）/ `Class->method`（`new`）。 |
| `mode_dir_basepath` | `''` | [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | 站点根目录（用于把文件路径折算成控制器路径）。 |

## n

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `name` | `''` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 该应用的短名；用于子应用 Phase 命名。 |
| `namespace` | `''` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) / [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) / [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) / [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 应用主命名空间（如 `App`）。非空且 `skip_app_autoload` 为假时，会把 `namespace` 指到 `path_namespace`。 |
| `namespace_controller` | `'Controller'` | [DuckPhp\Core\Route](Core-Route.md) / [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器所在子命名空间；以 `\\` 开头表示绝对子命名空间（不需拼上 `namespace`）。 |

## o

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `on_init` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | (`callable`) 初始化中 `onInit()` 阶段被调。 |
| `on_inited` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | (`callable`) 全部初始化完毕 `onInited()` 阶段被调。 |
| `on_request` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | (`callable`) 每次 `serve()` 开头被调。 |
| `override_class` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 若给出，`init()` 时用此类接管当前初始化（构造后以 `override_from=get_class(原)` 重入 init）。 |
| `override_from` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 记“自己被谁改写覆盖”的类名。`override_class` 流程使用。 |

## p

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `page_key` | `'page'` | [DuckPhp\Component\Pager](Component-Pager.md) | 当前页 URL 参数名。 |
| `page_size` | `30` | [DuckPhp\Component\Pager](Component-Pager.md) | 每页条数。 |
| `path` | `''` | [DuckPhp\Component\Configer](Component-Configer.md) / [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) / [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) / [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) / [DuckPhp\Core\View](Core-View.md) / [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) / [DuckPhp\Ext\Misc](Ext-Misc.md) / [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) / [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 项目根路径（相对路径基准）。 |
| `path_config` | `'config'` | [DuckPhp\Component\Configer](Component-Configer.md) / [DuckPhp\Core\App](Core-App.md) | 配置目录名（相对 `path`，可给绝对覆盖）。 |
| `path_document` | `'public'` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) / [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 发布根目录名（clone 目标）。 |
| `path_info_compact_action_key` | `'_r'` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 动作路由所在的 query 键。 |
| `path_info_compact_class_key` | `''` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 可选“模块（类路径段）”所在 query 键；留空则整路径都放 action键。 |
| `path_info_compact_enable` | `true` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 开关（init 才装 hook/url handler）。 |
| `path_lib` | `'lib'` | [DuckPhp\Ext\Misc](Ext-Misc.md) | 库目录；以 `/` 开头视为绝对目录，否则拼在 `path` 下。 |
| `path_log` | `'runtime'` | [DuckPhp\Core\Logger](Core-Logger.md) | 日志目录（可绝对，可相对根）。 |
| `path_namespace` | `'app'` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 应用目录（主命名空间对应目录），可绝对；相对则相对 `path`。默认把 `app/` → `${namespace}\`。 |
| `path_resource` | `'res'` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 资源源目录（默认 `res`）。 |
| `path_runtime` | `'runtime'` | [DuckPhp\Core\App](Core-App.md) | 运行期相对项目根目录（或绝对路径）。`getRuntimePath()` 返回。 |
| `path_sql_dump` | `'config'` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | SQL 文件存放子目录。 |
| `path_view` | `'view'` | [DuckPhp\Core\View](Core-View.md) | 视图目录相对名（相对 `path`；可绝对则用之）。`getViewFile()` 用它拼 `{$path}/{$path_view}/{$file}.php`。 |
| `port` | `'8080'` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 监听端口；也可被 CLI 参数 `--port`/`-P` 覆盖。 |
| `psr-4` | `[]` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | PSR-4 风格映射：`namespace => path`（初始化时翻转成 path=>namespace 装入 `namespace_paths`）。 |

## r

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `redis` | `null` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 单条连接（对象/数组 host,port,…）。 |
| `redis_cache_prefix` | `''` | [DuckPhp\Component\RedisCache](Component-RedisCache.md) | 本缓存键前缀。 |
| `redis_cache_skip_replace` | `false` | [DuckPhp\Component\RedisCache](Component-RedisCache.md) | 若 true 不 replace 全局 Cache（由自己取舍）。 |
| `redis_list` | `null` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 多条配置数组（每项 `['host'=>…,'port'=>…,'auth'=>…,'select'=>…]`）。 |
| `redis_list_reload_by_setting` | `true` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 未给主机要时允许由 setting (`redis_list`/`redis`) 提供。 |
| `redis_list_try_single` | `true` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 列表缺时按单条 redis 包装。 |
| `rewrite` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | （callable）自定义写 URL 的回调当作 getUrl 分支；否则走 defaultGetUrl。 |
| `rewrite_map` | `[]` | [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | 映射表 `匹配模板 => 内部url`；模板以 `~` 开头时被当正则。 |
| `route_map` | `[]` | [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 普通组（后探）。 |
| `route_map_important` | `[]` | [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 重要组（先探）。 |

## s

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `setting` | `[]` | [DuckPhp\Core\App](Core-App.md) | 直接以数组形式给出的应用设置（setting）初值；作为 `loadSetting()` 的起点，随后再合并 `.env`（可选）与设置文件。 |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | [DuckPhp\Core\App](Core-App.md) | Setting 文件（相对根或绝对）。 |
| `setting_file_enable` | `true` | [DuckPhp\Core\App](Core-App.md) | 是否加载设置文件。 |
| `setting_file_ignore_exists` | `true` | [DuckPhp\Core\App](Core-App.md) | 设置文件缺失时是否忽略（并不抛错）。 |
| `skip_404` | `false` | [DuckPhp\Core\App](Core-App.md) | 跳过 404 展示（`skip404Handler()` 会置真）。 |
| `skip_app_autoload` | `false` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 为真则不再自动把主 namespace 映射到 app 目录。 |
| `skip_exception_check` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 为 `true` 时 `runException` 直接把异常再次抛出，不再进入框架统一的异常分发。 |
| `sql_dump_data_tables` | `[]` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 需要导出数据的表（写进 `.data.sql`）。 |
| `sql_dump_debug_show_sql` | `false` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 执行 SQL 时打印每条语句。 |
| `sql_dump_exclude_tables` | `[]` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 排除的表。 |
| `sql_dump_include_tables` | `[]` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 显式包含的表（支持 `@` 占位替换为表前缀）。 |
| `sql_dump_include_tables_all` | `false` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 为 `true` 时导出数据库全部表（忽略 by_model）。 |
| `sql_dump_include_tables_by_model` | `true` | [DuckPhp\Ext\SqlDumper](Ext-SqlDumper.md) | 按工程 Model 类（`namespace\Model\Base` 同目录下类）自动搜集表。 |
| `superglobal_auto_define` | `false` | [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | init 时若为真：自动 `DefineSuperGlobalContext()` 并 `_LoadSuperGlobalAll()`（把当前超全局快照入属性）。 |
| `system_exception_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 自定义异常安装回调 `function(callable $handler)`，可替代内建 set_exception_handler（极端/跨运行时用）。 |

## u

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `url` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | 手工给分页基底 URL（空则 requestUri）。可含 `{page}` 占位符替换。 |
| `url_install` | `'install'` | [DuckPhp\Core\App](Core-App.md) | 未安装时跳到的安装 URL。 |
| `use_env_file` | `false` | [DuckPhp\Core\App](Core-App.md) | 为真则在 loadSetting 一并读根 `.env`(INI) 合并。 |
| `use_exit_exception` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否定义全局 `__EXIT_EXCEPTION`（为 `ExitException::class`）以让 exit 语义可捕获。 |
| `use_output_buffer` | `false` | [DuckPhp\Core\Runtime](Core-Runtime.md) | 是否开启整体输出缓冲；开启时 run() 开始一个 ob、clear() 负责 flush。 |
| `user_callback_for_add_ext_view_data` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 追加视图数据的回调（不设时默认注入 `__logined_id/name/url_logout`）。 |
| `user_callback_for_data` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 取当前用户数据（数组）的回调。 |
| `user_callback_for_id` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 取当前用户 id 的回调。 |
| `user_callback_for_local_service` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 返回本地 `UserServiceInterface` 实现的回调。 |
| `user_callback_for_login_service` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 登录服务回调（`register()/login()/logout()` 经 `getLoginBusiness()` 调用它）。 |
| `user_callback_for_name` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 取当前用户名的回调。 |
| `user_callback_for_session` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 用户会话实现回调（返回 `UserSessionInterface`）；配置后 `id()/name()` 优先读会话。 |
| `user_callback_for_url_for_home` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 生成首页 URL 的回调（优先于 `user_url_home`）。 |
| `user_callback_for_url_for_login` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 生成登录 URL 的回调。 |
| `user_callback_for_url_for_logout` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 生成退出 URL 的回调。 |
| `user_callback_for_url_for_register` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 生成注册 URL 的回调（键名与 `urlForRegister()` 拼写一致）。 |
| `user_default_exception_class` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 未登录时抛出的异常类（缺省用 `UserException::class`）；只在会话模式（配了 `user_callback_for_session`）下的 `id()/name()` 里生效。 |
| `user_enable_callback_singleton` | `true` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 回调为 `[类名, 方法]` 时是否先把类名转成 `类名::_()` 单例实例。 |
| `user_loginout_auto_redirect` | `true` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | `register()/login()/logout()` 完成后是否自动 302（注册/登录跳 home、退出跳 login）。 |
| `user_provider` | `''` | [DuckPhp\DuckPhp](DuckPhp.md) | 自定义用户提供者类名；非空时同理交给 `GlobalUser`（可用 `PhaseProxy` 包装）。 |
| `user_url_home` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 站内首页 URL（未配回调时用 `__url()` 生成）。 |
| `user_url_login` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 登录 URL。 |
| `user_url_logout` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 退出 URL。 |
| `user_url_register` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 注册 URL（键名由旧 `user_url_regist` 更名）。 |
| `user_view_file_footer` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 用户页脚视图文件。 |
| `user_view_file_header` | `null` | [DuckPhp\GlobalUser\GlobalUser](GlobalUser-GlobalUser.md) | 用户页头视图文件。 |

## v

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `validator_exception_class` | `\Exception::class` | [DuckPhp\Component\Validator](Component-Validator.md) | `check()`/`filter()` 校验失败时抛出的异常类（必须可被 `new` 且接受一个字符串消息）。 |
| `validator_skip_empty` | `true` | [DuckPhp\Component\Validator](Component-Validator.md) | 非 `required` 字段值为空（`null` 或 `''`）时是否跳过其余规则。 |
| `verbose` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 是否打印过程信息。 |
| `view_skip_notice_error` | `true` | [DuckPhp\Core\View](Core-View.md) | 渲染时是否临时屏蔽 `E_NOTICE` 噪声。为 true 时 `_Show` 会临时去掉 E_NOTICE 再到结束恢复。 |

## w

| 选项 | 默认值 | 来源类 | 说明 |
|---|---|---|---|
| `web_installer_check_custom_callback` | `null` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 自定义“环境检查”回调。 |
| `web_installer_database_drivers` | `['sqlite' => true, 'pgsql' => true, 'duckdb' => false]` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 可选的数据库驱动。 |
| `web_installer_default_sentences` | `[]` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 界面文案覆盖（空则用内置英文默认文案 `builtin_default_sentences`）。 |
| `web_installer_do_custom_callback` | `null` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 自定义“安装执行”回调。 |
| `web_installer_force` | `false` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 是否强制重装（先清表）。 |
| `web_installer_render_custom_callback` | `null` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 自定义“渲染”回调。 |
| `web_installer_use_database` | `true` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 安装流程是否包含数据库。 |
| `web_installer_use_redis` | `true` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 安装流程是否包含 Redis。 |
| `web_installer_view` | `''` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 自定义安装视图文件（空用内置视图）。 |
| `web_installer_view_block_custom` | `null` | [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) | 自定义区块模板（视图内嵌）。 |
| `workers` | `null` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 非空时用 `PHP_CLI_SERVER_WORKERS=N` 启动多进程内置服务器。 |

---

## 按前缀分组（便于成组记忆）

- **`admin_*`**（19）：`admin_callback_for_add_ext_view_data`、`admin_callback_for_data`、`admin_callback_for_id`、`admin_callback_for_local_service`、`admin_callback_for_login_service`、`admin_callback_for_name`、`admin_callback_for_session`、`admin_callback_for_url_for_home`、`admin_callback_for_url_for_login`、`admin_callback_for_url_for_logout`、`admin_default_exception_class`、`admin_enable_callback_singleton`、`admin_loginout_auto_redirect`、`admin_provider`、`admin_url_home`、`admin_url_login`、`admin_url_logout`、`admin_view_file_footer`、`admin_view_file_header`
- **`api_*`**（5）：`api_server_404_as_exception`、`api_server_base_class`、`api_server_class_postfix`、`api_server_namespace`、`api_server_use_singletonex`
- **`callable_*`**（6）：`callable_view_class`、`callable_view_foot`、`callable_view_head`、`callable_view_is_object_call`、`callable_view_prefix`、`callable_view_skip_replace`
- **`console_*`**（4）：`console_command_classes`、`console_command_default`、`console_command_phase`、`console_readlines_logfile`
- **`controller_*`**（13）：`controller_class_adjust`、`controller_class_base`、`controller_class_map`、`controller_class_postfix`、`controller_fix_mistake_path_info`、`controller_method_prefix`、`controller_path_ext`、`controller_prefix_post`、`controller_resource_prefix`、`controller_url_prefix`、`controller_welcome_class`、`controller_welcome_class_visible`、`controller_welcome_method`
- **`data_*`**（5）：`data_file_bump_allowed`、`data_file_bump_keys`、`data_file_bump_prefix_keys`、`data_file_enable`、`data_file_json_file`
- **`database_*`**（8）：`database_class`、`database_driver`、`database_driver_SqlDumperSupporter_map`、`database_list`、`database_list_reload_by_setting`、`database_list_try_single`、`database_log_sql_level`、`database_log_sql_query`
- **`empty_*`**（4）：`empty_view_key_view`、`empty_view_key_wellcome_class`、`empty_view_skip_replace`、`empty_view_trim_view_wellcome`
- **`error_*`**（4）：`error_404`、`error_500`、`error_debug`、`error_maintain`
- **`exception_*`**（3）：`exception_for_project`、`exception_map`、`exception_reporter`
- **`facades_*`**（3）：`facades_enable_autoload`、`facades_map`、`facades_namespace`
- **`function_*`**（3）：`function_route`、`function_route_404_to_index`、`function_route_method_prefix`
- **`handle_*`**（3）：`handle_all_dev_error`、`handle_all_exception`、`handle_exception_on_init`
- **`jsonrpc_*`**（9）：`jsonrpc_backend`、`jsonrpc_check_token_handler`、`jsonrpc_enable_autoload`、`jsonrpc_is_debug`、`jsonrpc_namespace`、`jsonrpc_service_interface`、`jsonrpc_service_namespace`、`jsonrpc_timeout`、`jsonrpc_wrap_auto_adjust`
- **`lang_*`**（9）：`lang_cookie_name`、`lang_default`、`lang_detect_mode`、`lang_file_path`、`lang_final`、`lang_follow_root`、`lang_handler`、`lang_simple_mode_only_sentences`、`lang_url_param`
- **`on_*`**（3）：`on_init`、`on_inited`、`on_request`
- **`path_*`**（12）：`path_config`、`path_document`、`path_info_compact_action_key`、`path_info_compact_class_key`、`path_info_compact_enable`、`path_lib`、`path_log`、`path_namespace`、`path_resource`、`path_runtime`、`path_sql_dump`、`path_view`
- **`redis_*`**（5）：`redis_cache_prefix`、`redis_cache_skip_replace`、`redis_list`、`redis_list_reload_by_setting`、`redis_list_try_single`
- **`setting_*`**（3）：`setting_file`、`setting_file_enable`、`setting_file_ignore_exists`
- **`skip_*`**（3）：`skip_404`、`skip_app_autoload`、`skip_exception_check`
- **`sql_*`**（6）：`sql_dump_data_tables`、`sql_dump_debug_show_sql`、`sql_dump_exclude_tables`、`sql_dump_include_tables`、`sql_dump_include_tables_all`、`sql_dump_include_tables_by_model`
- **`use_*`**（3）：`use_env_file`、`use_exit_exception`、`use_output_buffer`
- **`user_*`**（21）：`user_callback_for_add_ext_view_data`、`user_callback_for_data`、`user_callback_for_id`、`user_callback_for_local_service`、`user_callback_for_login_service`、`user_callback_for_name`、`user_callback_for_session`、`user_callback_for_url_for_home`、`user_callback_for_url_for_login`、`user_callback_for_url_for_logout`、`user_callback_for_url_for_register`、`user_default_exception_class`、`user_enable_callback_singleton`、`user_loginout_auto_redirect`、`user_provider`、`user_url_home`、`user_url_login`、`user_url_logout`、`user_url_register`、`user_view_file_footer`、`user_view_file_header`
- **`web_*`**（10）：`web_installer_check_custom_callback`、`web_installer_database_drivers`、`web_installer_default_sentences`、`web_installer_do_custom_callback`、`web_installer_force`、`web_installer_render_custom_callback`、`web_installer_use_database`、`web_installer_use_redis`、`web_installer_view`、`web_installer_view_block_custom`

## 相关链接

- [应用选项（按类分组）](options-by-class.md)
- [应用选项总览（首页）](options.md)
- [应用设置 Setting](setting.md)
