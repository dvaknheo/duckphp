# 应用选项（按类分组）

DuckPhp 的选项通过 `$options` 属性传入，最终合并到 `DuckPhp\Core\App::$options` 中。
以下按来源类分组列出所有选项，默认值为源码中的初始值。

---

> **说明**：加粗选项表示。`DuckPhp\DuckPhp` 。`DuckPhp\Core\App` 默认提供的选项；未加粗选项通常来自特定组件，需要手动启用对应组件或通过 `ext` 选项加载。

---

## 入口类选项

### DuckPhp\DuckPhp

`DuckPhp` 。`common_options` 中定义了框架默认加载组件与通用选项。

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `ext_options_file_enable` | `true` | `DuckPhp\DuckPhp` | 是否启用 `DuckPhpApps.config.php` 额外选项文件。|
| `ext_options_file` | `'config/DuckPhpApps.config.php'` | `DuckPhp\DuckPhp` | 额外选项文件路径。|
| `ext` | `[]` | `DuckPhp\DuckPhp` | 默认加载的扩展组件映射。|
| `session_prefix` | `null` | `DuckPhp\DuckPhp` | Session 前缀。|
| `table_prefix` | `null` | `DuckPhp\DuckPhp` | 数据库表前缀。|
| `path_info_compact_enable` | `false` | `DuckPhp\DuckPhp` | 是否启用。PATH_INFO 兼容模式。|
| `class_admin` | `''` | `DuckPhp\DuckPhp` | 管理员类名，设置后自动启用全局管理员。|
| `class_user` | `''` | `DuckPhp\DuckPhp` | 用户类名，设置后自动启用全局用户。|
| `database_driver` | `''` | `DuckPhp\DuckPhp` | 数据库驱动类型（。`mysql`、`sqlite`）。|
| `cli_command_with_app` | `true` | `DuckPhp\DuckPhp` | 是否将当前应用类加入 CLI 命令类列表。|
| `cli_command_with_common` | `true` | `DuckPhp\DuckPhp` | 是否将默认命令类加入 CLI 命令类列表。|
| `cli_command_with_fast_installer` | `true` | `DuckPhp\DuckPhp` | 是否将安装器加入 CLI 命令类列表。|
| `allow_require_ext_app` | `true` | `DuckPhp\DuckPhp` | 是否允许 `require` 命令安装外部子应用。|
| `lang_default` | `null` | `DuckPhp\DuckPhp` | 默认语言，与 `Lang` 组件共享。|
| `lang_final` | `null` | `DuckPhp\DuckPhp` | 最终语言，与 `Lang` 组件共享。|

---

## 核心选项

### DuckPhp\Core\KernelTrait

`kernel_options` 定义了应用初始化所需的核心选项。

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `null` | `DuckPhp\Core\KernelTrait` | 项目根路径。|
| `override_class` | `null` | `DuckPhp\Core\KernelTrait` | 重载类名，存在则切换到该类初始化。|
| `override_class_from` | `null` | `DuckPhp\Core\KernelTrait` | 切换前保存的。`override_class`。|
| `cli_enable` | `true` | `DuckPhp\Core\KernelTrait` | 是否启用命令行模式。|
| `is_debug` | `false` | `DuckPhp\Core\KernelTrait` | 是否调试模式。|
| `ext` | `[]` | `DuckPhp\Core\KernelTrait` | 扩展组件映射。|
| `app` | `[]` | `DuckPhp\Core\KernelTrait` | 子应用映射，键为类名，值为对应选项。|
| `skip_404` | `false` | `DuckPhp\Core\KernelTrait` | 是否跳过 404 处理。|
| `skip_exception_check` | `false` | `DuckPhp\Core\KernelTrait` | 是否跳过异常检查，用于配合其他框架。|
| `on_init` | `null` | `DuckPhp\Core\KernelTrait` | 初始化完成后回调。|
| `namespace` | `null` | `DuckPhp\Core\KernelTrait` | 项目命名空间。|
| `setting_file` | `'config/DuckPhpSettings.config.php'` | `DuckPhp\Core\KernelTrait` | 设置文件路径。|
| `setting_file_ignore_exists` | `true` | `DuckPhp\Core\KernelTrait` | 设置文件不存在时是否忽略。|
| `setting_file_enable` | `true` | `DuckPhp\Core\KernelTrait` | 是否启用设置文件。|
| `use_env_file` | `false` | `DuckPhp\Core\KernelTrait` | 是否加载 `.env` 文件。|
| `exception_reporter` | `null` | `DuckPhp\Core\KernelTrait` | 异常报告类。|
| `exception_for_project` | `null` | `DuckPhp\Core\KernelTrait` | 异常报告仅针对的异常类。|
| `options_file` | `'config/DuckPhpOptions.config.php'` | `DuckPhp\Core\KernelTrait` | 选项文件路径。|
| `options_file_enable` | `false` | `DuckPhp\Core\KernelTrait` | 是否启用选项文件。|
| `path_installed_options` | `'config'` | `DuckPhp\Core\KernelTrait` | 已安装选项文件所在目录。|
| `installed_options_file` | `'DuckPhpInstalled.config.php'` | `DuckPhp\Core\KernelTrait` | 已安装选项文件名。|
| `installed_options_enable` | `false` | `DuckPhp\Core\KernelTrait` | 是否启用已安装选项文件。|
| `cli_command_classes` | `[]` | `DuckPhp\Core\KernelTrait` | CLI 命令类列表。|
| `cli_command_prefix` | `null` | `DuckPhp\Core\KernelTrait` | CLI 命令命名空间前缀。|
| `cli_command_method_prefix` | `'command_'` | `DuckPhp\Core\KernelTrait` | CLI 命令方法前缀。|

### DuckPhp\Core\App

`core_options` 定义。`App` 核心组件所需的选项。

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path_runtime` | `'runtime'` | `DuckPhp\Core\App` | 运行目录路径。|
| `alias` | `null` | `DuckPhp\Core\App` | 子应用别名，用于视图等资源路径调整。|
| `default_exception_do_log` | `true` | `DuckPhp\Core\App` | 发生异常时是否记录日志。|
| `close_resource_at_output` | `false` | `DuckPhp\Core\App` | 输出时是否关闭资源（第三方扩展参考）。|
| `html_handler` | `null` | `DuckPhp\Core\App` | HTML 编码函数回调。|
| `lang_handler` | `null` | `DuckPhp\Core\App` | 语言处理回调。|
| `error_404` | `null` | `DuckPhp\Core\App` | 404 错误处理视图或回调。|
| `error_500` | `null` | `DuckPhp\Core\App` | 500 错误处理视图或回调。|

---

## 核心组件选项

### DuckPhp\Core\Route

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `namespace` | `''` | `DuckPhp\Core\Route` | 项目命名空间。|
| `namespace_controller` | `'Controller'` | `DuckPhp\Core\Route` | 控制器命名空间。|
| `controller_path_ext` | `''` | `DuckPhp\Core\Route` | 控制器路径后缀。|
| `controller_welcome_class` | `'Main'` | `DuckPhp\Core\Route` | 默认欢迎控制器类名。|
| `controller_welcome_class_visible` | `false` | `DuckPhp\Core\Route` | 是否允许 URL 中显示欢迎控制器名。|
| `controller_welcome_method` | `'index'` | `DuckPhp\Core\Route` | 默认欢迎方法名。|
| `controller_class_adjust` | `''` | `DuckPhp\Core\Route` | 控制器类名调整规则。|
| `controller_class_base` | `''` | `DuckPhp\Core\Route` | 控制器基类限制。|
| `controller_class_postfix` | `'Controller'` | `DuckPhp\Core\Route` | 控制器类名后缀。|
| `controller_method_prefix` | `'action_'` | `DuckPhp\Core\Route` | 控制器方法前缀。|
| `controller_prefix_post` | `'do_'` | `DuckPhp\Core\Route` | POST 请求方法额外前缀。|
| `controller_class_map` | `[]` | `DuckPhp\Core\Route` | 控制器类映射表。|
| `controller_resource_prefix` | `''` | `DuckPhp\Core\Route` | 资源 URL 前缀。|
| `controller_url_prefix` | `''` | `DuckPhp\Core\Route` | 控制。URL 前缀。|
| `controller_fix_mistake_path_info` | `true` | `DuckPhp\Core\Route` | 是否修复缺失。PATH_INFO。|

### DuckPhp\Core\Runtime

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `use_output_buffer` | `false` | `DuckPhp\Core\Runtime` | 是否启用输出缓冲。|
| `path_runtime` | `'runtime'` | `DuckPhp\Core\Runtime` | 运行目录路径。|

### DuckPhp\Core\Logger

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Core\Logger` | 项目路径。|
| `path_log` | `'runtime'` | `DuckPhp\Core\Logger` | 日志目录路径。|
| `log_file_template` | `'log_%Y-%m-%d_%H-%i.log'` | `DuckPhp\Core\Logger` | 日志文件名模板。|
| `log_prefix` | `'DuckPhpLog'` | `DuckPhp\Core\Logger` | 日志前缀。|

### DuckPhp\Core\View

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Core\View` | 项目路径。|
| `path_view` | `'view'` | `DuckPhp\Core\View` | 视图目录路径。|
| `view_skip_notice_error` | `true` | `DuckPhp\Core\View` | 渲染视图时是否忽。notice 错误。|

### DuckPhp\Core\SuperGlobal

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `superglobal_auto_define` | `false` | `DuckPhp\Core\SuperGlobal` | 初始化时是否定义 `__SUPERGLOBAL_CONTEXT` 宏。|

### DuckPhp\Core\Console

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `cli_command_group` | `[]` | `DuckPhp\Core\Console` | 命令分组注册信息。|
| `cli_command_default` | `'help'` | `DuckPhp\Core\Console` | 默认命令。|
| `cli_readlines_logfile` | `''` | `DuckPhp\Core\Console` | `readLines` 输入日志文件。|

### DuckPhp\Core\ExceptionManager

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `handle_all_dev_error` | `true` | `DuckPhp\Core\ExceptionManager` | 是否捕获开发错误。|
| `handle_all_exception` | `true` | `DuckPhp\Core\ExceptionManager` | 是否捕获全部异常。|
| `system_exception_handler` | `null` | `DuckPhp\Core\ExceptionManager` | 系统异常处理回调。|
| `handle_exception_on_init` | `true` | `DuckPhp\Core\ExceptionManager` | 初始化时是否启动异常处理。|
| `default_exception_handler` | `null` | `DuckPhp\Core\ExceptionManager` | 默认异常处理回调。|
| `dev_error_handler` | `null` | `DuckPhp\Core\ExceptionManager` | 开发错误处理回调。|

### DuckPhp\Core\AutoLoader

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Core\AutoLoader` | 项目路径。|
| `namespace` | `''` | `DuckPhp\Core\AutoLoader` | 应用命名空间。|
| `path_namespace` | `'app'` | `DuckPhp\Core\AutoLoader` | 应用代码目录。|
| `skip_app_autoload` | `false` | `DuckPhp\Core\AutoLoader` | 是否跳过应用命名空间自动加载。|
| `autoload_cache_in_cli` | `false` | `DuckPhp\Core\AutoLoader` | CLI 下是否缓存类文件。|
| `autoload_path_namespace_map` | `[]` | `DuckPhp\Core\AutoLoader` | 命名空间与路径映射。|
| `psr-4` | `[]` | `DuckPhp\Core\AutoLoader` | PSR-4 映射。|

---

## 自带组件选项

### DuckPhp\Component\Configer

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Component\Configer` | 项目路径。|
| `path_config` | `'config'` | `DuckPhp\Component\Configer` | 配置文件目录。|

### DuckPhp\Component\DbManager

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `database_driver` | `''` | `DuckPhp\Component\DbManager` | 数据库驱动类型。|
| `database` | `null` | `DuckPhp\Component\DbManager` | 单一数据库配置。|
| `database_list` | `null` | `DuckPhp\Component\DbManager` | 多数据库配置列表。|
| `database_list_reload_by_setting` | `true` | `DuckPhp\Component\DbManager` | 是否从设置文件重新加载数据库配置。|
| `database_list_try_single` | `true` | `DuckPhp\Component\DbManager` | 是否尝试将单库配置转为列表。|
| `database_log_sql_query` | `false` | `DuckPhp\Component\DbManager` | 是否记录 SQL 查询。|
| `database_log_sql_level` | `'debug'` | `DuckPhp\Component\DbManager` | 记录 SQL 的日志级别。|
| `database_class` | `''` | `DuckPhp\Component\DbManager` | 自定义数据库类名，为空使。`DuckPhp\Db\Db`。|

### DuckPhp\Component\RedisManager

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `redis` | `null` | `DuckPhp\Component\RedisManager` | 单一 Redis 配置。|
| `redis_list` | `null` | `DuckPhp\Component\RedisManager` | 。Redis 配置列表。|
| `redis_list_reload_by_setting` | `true` | `DuckPhp\Component\RedisManager` | 是否从设置文件重新加。Redis 配置。|
| `redis_list_try_single` | `true` | `DuckPhp\Component\RedisManager` | 是否尝试将单 Redis 配置转为列表。|

### DuckPhp\Component\RedisCache

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `redis_cache_skip_replace` | `false` | `DuckPhp\Component\RedisCache` | 是否跳过替换默认 `Cache` 组件。|
| `redis_cache_prefix` | `''` | `DuckPhp\Component\RedisCache` | Redis 缓存键前缀。|

### DuckPhp\Component\Pager

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `url` | `null` | `DuckPhp\Component\Pager` | 基础 URL。|
| `current` | `null` | `DuckPhp\Component\Pager` | 当前页码。|
| `page_size` | `30` | `DuckPhp\Component\Pager` | 每页大小。|
| `page_key` | `'page'` | `DuckPhp\Component\Pager` | 页码 URL 参数名。|
| `rewrite` | `null` | `DuckPhp\Component\Pager` | URL 重写回调。|

### DuckPhp\Component\Lang

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `lang_final` | `null` | `DuckPhp\Component\Lang` | 最终语言，设置后跳过自动检测。|
| `lang_default` | `null` | `DuckPhp\Component\Lang` | 默认回退语言。|
| `lang_detect_mode` | `['url', 'cookie', 'header', 'cli', 'default']` | `DuckPhp\Component\Lang` | 语言检测顺序。|
| `lang_follow_root` | `true` | `DuckPhp\Component\Lang` | 子应用是否跟随根应用语言。|
| `lang_url_param` | `'lang'` | `DuckPhp\Component\Lang` | URL 语言参数名。|
| `lang_cookie_name` | `'lang'` | `DuckPhp\Component\Lang` | Cookie 语言键名。|
| `lang_file_path` | `'lang/'` | `DuckPhp\Component\Lang` | 语言文件目录（相对于 `config/`）。|
| `lang_simple_mode_only_sentences` | `[]` | `DuckPhp\Component\Lang` | 简单模式下的翻译句子数组。|

### DuckPhp\Component\RouteHookCheckStatus

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `error_maintain` | `null` | `DuckPhp\Component\RouteHookCheckStatus` | 维护页面视图或回调。|
| `error_need_install` | `null` | `DuckPhp\Component\RouteHookCheckStatus` | 未安装提示页面视图或回调。|

### DuckPhp\Component\RouteHookPathInfoCompat

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path_info_compact_enable` | `true` | `DuckPhp\Component\RouteHookPathInfoCompat` | 是否启用兼容模式。|
| `path_info_compact_action_key` | `'_r'` | `DuckPhp\Component\RouteHookPathInfoCompat` | 替代 PATH_INFO 。action 参数键。|
| `path_info_compact_class_key` | `''` | `DuckPhp\Component\RouteHookPathInfoCompat` | 替代 PATH_INFO 。class 参数键。|

### DuckPhp\Component\RouteHookResource

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Component\RouteHookResource` | 项目路径。|
| `path_resource` | `'res'` | `DuckPhp\Component\RouteHookResource` | 资源目录。|
| `path_document` | `'public'` | `DuckPhp\Component\RouteHookResource` | 文档根目录。|
| `controller_url_prefix` | `''` | `DuckPhp\Component\RouteHookResource` | 控制。URL 前缀。|
| `controller_resource_prefix` | `''` | `DuckPhp\Component\RouteHookResource` | 资源 URL 前缀。|

### DuckPhp\Component\RouteHookRewrite

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `controller_url_prefix` | `''` | `DuckPhp\Component\RouteHookRewrite` | 控制。URL 前缀。|
| `rewrite_map` | `[]` | `DuckPhp\Component\RouteHookRewrite` | 重写映射表。|

### DuckPhp\Component\RouteHookRouteMap

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `controller_url_prefix` | `''` | `DuckPhp\Component\RouteHookRouteMap` | 控制。URL 前缀。|
| `route_map_important` | `[]` | `DuckPhp\Component\RouteHookRouteMap` | 在默认路由前执行的路由映射。|
| `route_map` | `[]` | `DuckPhp\Component\RouteHookRouteMap` | 在默认路由失败后执行的路由映射。|

---

## 扩展选项

### DuckPhp\Ext\CallableView

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `callable_view_head` | `null` | `DuckPhp\Ext\CallableView` | 页眉回调函数。|
| `callable_view_foot` | `null` | `DuckPhp\Ext\CallableView` | 页脚回调函数。|
| `callable_view_class` | `null` | `DuckPhp\Ext\CallableView` | 视图回调限定类名。|
| `callable_view_is_object_call` | `true` | `DuckPhp\Ext\CallableView` | 是否以对象方式调用。|
| `callable_view_prefix` | `null` | `DuckPhp\Ext\CallableView` | 视图方法前缀。|
| `callable_view_skip_replace` | `false` | `DuckPhp\Ext\CallableView` | 是否跳过替换默认 `View` 组件。|

### DuckPhp\Ext\DuckPhpInstaller

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Ext\DuckPhpInstaller` | 项目路径。|
| `namespace` | `''` | `DuckPhp\Ext\DuckPhpInstaller` | 项目命名空间。|
| `force` | `false` | `DuckPhp\Ext\DuckPhpInstaller` | 是否强制覆盖现有文件。|
| `autoloader` | `'vendor/autoload.php'` | `DuckPhp\Ext\DuckPhpInstaller` | 自动加载文件路径。|
| `verbose` | `false` | `DuckPhp\Ext\DuckPhpInstaller` | 是否显示详情。|
| `help` | `false` | `DuckPhp\Ext\DuckPhpInstaller` | 是否显示帮助。|

### DuckPhp\Ext\EmptyView

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `empty_view_key_view` | `'view'` | `DuckPhp\Ext\EmptyView` | `_Show` 。`$data` 中保存视图名的键。|
| `empty_view_key_wellcome_class` | `'Main/'` | `DuckPhp\Ext\EmptyView` | 该前缀的视图名会被裁剪。|
| `empty_view_trim_view_wellcome` | `true` | `DuckPhp\Ext\EmptyView` | 是否裁剪欢迎类前缀。|
| `empty_view_skip_replace` | `false` | `DuckPhp\Ext\EmptyView` | 是否跳过替换默认 `View` 组件。|

### DuckPhp\Ext\FinderForController

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `classes_to_get_controller_path` | `[]` | `DuckPhp\Ext\FinderForController` | 用于推断控制器路径的类名列表。|

### DuckPhp\Ext\JsonRpcExt

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `jsonrpc_namespace` | `'JsonRpc'` | `DuckPhp\Ext\JsonRpcExt` | 客户端类命名空间。|
| `jsonrpc_backend` | `'https://127.0.0.1'` | `DuckPhp\Ext\JsonRpcExt` | JsonRpc 后端地址。|
| `jsonrpc_is_debug` | `false` | `DuckPhp\Ext\JsonRpcExt` | 是否调试。|
| `jsonrpc_enable_autoload` | `true` | `DuckPhp\Ext\JsonRpcExt` | 是否启用客户端类自动加载。|
| `jsonrpc_check_token_handler` | `null` | `DuckPhp\Ext\JsonRpcExt` | Token 处理回调。|
| `jsonrpc_wrap_auto_adjust` | `true` | `DuckPhp\Ext\JsonRpcExt` | 是否自动调整封装。|
| `jsonrpc_service_interface` | `''` | `DuckPhp\Ext\JsonRpcExt` | 服务接口或基类限制。|
| `jsonrpc_service_namespace` | `''` | `DuckPhp\Ext\JsonRpcExt` | 服务限定命名空间。|
| `jsonrpc_timeout` | `5` | `DuckPhp\Ext\JsonRpcExt` | 请求超时秒数。|

### DuckPhp\Ext\JsonView

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `json_view_skip_replace` | `false` | `DuckPhp\Ext\JsonView` | 是否跳过替换默认 `View` 组件。|
| `json_view_skip_vars` | `[]` | `DuckPhp\Ext\JsonView` | 输出 JSON 时排除的变量名。|

### DuckPhp\Ext\MiniRoute

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `namespace` | `''` | `DuckPhp\Ext\MiniRoute` | 项目命名空间。|
| `namespace_controller` | `'Controller'` | `DuckPhp\Ext\MiniRoute` | 控制器命名空间。|
| `controller_path_ext` | `''` | `DuckPhp\Ext\MiniRoute` | 控制器路径后缀。|
| `controller_welcome_class` | `'Main'` | `DuckPhp\Ext\MiniRoute` | 默认欢迎控制器类名。|
| `controller_welcome_class_visible` | `false` | `DuckPhp\Ext\MiniRoute` | 是否允许 URL 中显示欢迎控制器名。|
| `controller_welcome_method` | `'index'` | `DuckPhp\Ext\MiniRoute` | 默认欢迎方法名。|
| `controller_class_postfix` | `''` | `DuckPhp\Ext\MiniRoute` | 控制器类名后缀。|
| `controller_method_prefix` | `''` | `DuckPhp\Ext\MiniRoute` | 控制器方法前缀。|
| `controller_class_map` | `[]` | `DuckPhp\Ext\MiniRoute` | 控制器类映射表。|
| `controller_resource_prefix` | `''` | `DuckPhp\Ext\MiniRoute` | 资源 URL 前缀。|
| `controller_url_prefix` | `''` | `DuckPhp\Ext\MiniRoute` | 控制。URL 前缀。|

### DuckPhp\Ext\Misc

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\Ext\Misc` | 项目路径。|
| `path_lib` | `'lib'` | `DuckPhp\Ext\Misc` | Import 库目录路径。|

### DuckPhp\Ext\MyFacadesAutoLoader

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `facades_namespace` | `'MyFacades'` | `DuckPhp\Ext\MyFacadesAutoLoader` | 门面类命名空间。|
| `facades_map` | `[]` | `DuckPhp\Ext\MyFacadesAutoLoader` | 门面映射表。|
| `facades_enable_autoload` | `true` | `DuckPhp\Ext\MyFacadesAutoLoader` | 是否启用门面自动加载。|

### DuckPhp\Ext\MyMiddlewareManager

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `middleware` | `[]` | `DuckPhp\Ext\MyMiddlewareManager` | 中间件回调列表。|

### DuckPhp\Ext\RouteHookApiServer

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `namespace` | `''` | `DuckPhp\Ext\RouteHookApiServer` | 项目命名空间。|
| `api_server_base_class` | `''` | `DuckPhp\Ext\RouteHookApiServer` | 限定 API 基类或接口。|
| `api_server_namespace` | `'Api'` | `DuckPhp\Ext\RouteHookApiServer` | API 类命名空间。|
| `api_server_class_postfix` | `''` | `DuckPhp\Ext\RouteHookApiServer` | API 类名后缀。|
| `api_server_use_singletonex` | `false` | `DuckPhp\Ext\RouteHookApiServer` | 是否使用可变单例模式。|
| `api_server_404_as_exception` | `false` | `DuckPhp\Ext\RouteHookApiServer` | 404 是否抛出异常。|

### DuckPhp\Ext\RouteHookDirectoryMode

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `mode_dir_basepath` | `''` | `DuckPhp\Ext\RouteHookDirectoryMode` | 目录模式的基准路径。|

### DuckPhp\Ext\RouteHookFunctionRoute

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `function_route` | `false` | `DuckPhp\Ext\RouteHookFunctionRoute` | 是否启用函数模式路由。|
| `function_route_method_prefix` | `'action_'` | `DuckPhp\Ext\RouteHookFunctionRoute` | 函数模式路由方法前缀。|
| `function_route_404_to_index` | `false` | `DuckPhp\Ext\RouteHookFunctionRoute` | 404 是否。`index` 函数执行。|

### DuckPhp\Ext\StrictCheck

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `namespace` | `''` | `DuckPhp\Ext\StrictCheck` | 项目命名空间。|
| `namespace_controller` | `'Controller'` | `DuckPhp\Ext\StrictCheck` | 控制器命名空间。|
| `namespace_business` | `''` | `DuckPhp\Ext\StrictCheck` | 业务类命名空间。|
| `namespace_model` | `''` | `DuckPhp\Ext\StrictCheck` | 模型类命名空间。|
| `controller_base_class` | `null` | `DuckPhp\Ext\StrictCheck` | 控制器基类。|
| `is_debug` | `false` | `DuckPhp\Ext\StrictCheck` | 是否启用严格检查。|
| `strict_check_context_class` | `null` | `DuckPhp\Ext\StrictCheck` | 指定上下文类。|
| `strict_check_enable` | `true` | `DuckPhp\Ext\StrictCheck` | 是否启用严格检查模式。|
| `postfix_batch_business` | `'BatchBusiness'` | `DuckPhp\Ext\StrictCheck` | 批量业务类后缀。|
| `postfix_business_lib` | `'Lib'` | `DuckPhp\Ext\StrictCheck` | 业务库类后缀。|
| `postfix_ex_model` | `'ExModel'` | `DuckPhp\Ext\StrictCheck` | 混合模型后缀。|
| `postfix_model` | `'Model'` | `DuckPhp\Ext\StrictCheck` | 模型后缀。|

---

## 数据库选项

`DuckPhp\Db\Db` 本身通过 `init($config)` 接收配置而非 `$options`，常用配置键如下。

| 配置。| 说明 |
|---|---|
| `dsn` | PDO DSN 字符串。|
| `username` | 数据库用户名。|
| `password` | 数据库密码。|

详细说明、[Db-Db.md](Db-Db.md)。

---

## 命令行安装器选项

### DuckPhp\FastInstaller\FastInstaller

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `install_input_validators` | `[]` | `DuckPhp\FastInstaller\FastInstaller` | 安装输入校验器。|
| `install_default_options` | `[]` | `DuckPhp\FastInstaller\FastInstaller` | 安装默认选项。|
| `install_input_desc` | `''` | `DuckPhp\FastInstaller\FastInstaller` | 安装输入提示描述。|
| `install_callback` | `null` | `DuckPhp\FastInstaller\FastInstaller` | 安装完成回调。|
| `install_support_database_list` | `''` | `DuckPhp\FastInstaller\FastInstaller` | 支持的数据库列表。|
| `allow_require_ext_app` | `false` | `DuckPhp\FastInstaller\FastInstaller` | 是否允许 `require` 外部子应用。|

### DuckPhp\FastInstaller\SqlDumper

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `path` | `''` | `DuckPhp\FastInstaller\SqlDumper` | 项目路径。|
| `path_sql_dump` | `'config'` | `DuckPhp\FastInstaller\SqlDumper` | SQL 导出目录。|
| `sql_dump_file` | `'install.sql'` | `DuckPhp\FastInstaller\SqlDumper` | SQL 导出文件名。|
| `sql_dump_include_tables` | `[]` | `DuckPhp\FastInstaller\SqlDumper` | 显式包含的表。|
| `sql_dump_exclude_tables` | `[]` | `DuckPhp\FastInstaller\SqlDumper` | 排除的表。|
| `sql_dump_data_tables` | `[]` | `DuckPhp\FastInstaller\SqlDumper` | 需要导出数据的表。|
| `sql_dump_include_tables_all` | `false` | `DuckPhp\FastInstaller\SqlDumper` | 是否导出所有表。|
| `sql_dump_include_tables_by_model` | `true` | `DuckPhp\FastInstaller\SqlDumper` | 是否通过模型扫描表。|
| `sql_dump_install_replace_prefix` | `true` | `DuckPhp\FastInstaller\SqlDumper` | 安装时是否替换表前缀。|
| `sql_dump_prefix` | `''` | `DuckPhp\FastInstaller\SqlDumper` | SQL 中的表前缀。|
| `sql_dump_debug_show_sql` | `false` | `DuckPhp\FastInstaller\SqlDumper` | 是否显示执行。SQL。|

---

## HTTP 服务器选项

### DuckPhp\HttpServer\HttpServer

| 选项 | 默认。| 来源、| 说明 |
|---|---|---|---|
| `host` | `'127.0.0.1'` | `DuckPhp\HttpServer\HttpServer` | 服务器监听地址。|
| `port` | `'8080'` | `DuckPhp\HttpServer\HttpServer` | 服务器监听端口。|
| `path` | `''` | `DuckPhp\HttpServer\HttpServer` | 项目路径。|
| `path_document` | `'public'` | `DuckPhp\HttpServer\HttpServer` | 文档根目录。|

`HttpServer` 同时支持以下命令行参数：

| 参数 | 说明 |
|---|---|
| `--host` / `-H` | 监听地址。|
| `--port` / `-P` | 监听端口。|
| `--docroot` / `-t` | 文档根目录。|
| `--dry` | 仅显示命令，不执行。|
| `--background` / `-b` | 后台运行。|
| `--help` / `-h` | 显示帮助。|

---

## 助手。Foundation 选项

`DuckPhp\Helper` 。`DuckPhp\Foundation` 命名空间下的类通常不直接声。`$options`，而是依赖当前应用。`App::$options`。具体方法参考对应文档：

- `Helper` 助手：[Helper-AppHelperTrait.md](Helper-AppHelperTrait.md)、[Helper-BusinessHelperTrait.md](Helper-BusinessHelperTrait.md)、[Helper-ControllerHelperTrait.md](Helper-ControllerHelperTrait.md)、[Helper-ModelHelperTrait.md](Helper-ModelHelperTrait.md)
- `Foundation` 助手：[Foundation-Helper.md](Foundation-Helper.md)

---

## 全局管理选项

`DuckPhp\GlobalAdmin` 。`DuckPhp\GlobalUser` 命名空间下的类不直接声明额外选项，通过 `DuckPhp\DuckPhp` 。`class_admin` 。`class_user` 选项启用。详细说明参考：

- [GlobalAdmin-GlobalAdmin.md](GlobalAdmin-GlobalAdmin.md)
- [GlobalUser-GlobalUser.md](GlobalUser-GlobalUser.md)

---

## 按字母顺序索。

| 选项。| 默认。| 来源、| 说明 |
|---|---|---|---|
| `alias` | `null` | [DuckPhp\Core\App](Core-App.md) | 子应用别名，用于视图等资源路径调整。|
| `allow_require_ext_app` | `true` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否允许 `require` 命令安装外部子应用。|
| `allow_require_ext_app` | `false` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 是否允许 `require` 外部子应用。|
| `api_server_404_as_exception` | `false` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 404 是否抛出异常。|
| `api_server_base_class` | `''` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 限定 API 基类或接口。|
| `api_server_class_postfix` | `''` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | API 类名后缀。|
| `api_server_namespace` | `'Api'` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | API 类命名空间。|
| `api_server_use_singletonex` | `false` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 是否使用可变单例模式。|
| `app` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 子应用映射，键为类名，值为对应选项。|
| `autoload_cache_in_cli` | `false` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | CLI 下是否缓存类文件。|
| `autoload_path_namespace_map` | `[]` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 命名空间与路径映射。|
| `autoloader` | `'vendor/autoload.php'` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 自动加载文件路径。|
| `callable_view_class` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 视图回调限定类名。|
| `callable_view_foot` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 页脚回调函数。|
| `callable_view_head` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 页眉回调函数。|
| `callable_view_is_object_call` | `true` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 是否以对象方式调用。|
| `callable_view_prefix` | `null` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 视图方法前缀。|
| `callable_view_skip_replace` | `false` | [DuckPhp\Ext\CallableView](Ext-CallableView.md) | 是否跳过替换默认 `View` 组件。|
| `class_admin` | `''` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 管理员类名，设置后自动启用全局管理员。|
| `class_user` | `''` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 用户类名，设置后自动启用全局用户。|
| `classes_to_get_controller_path` | `[]` | [DuckPhp\Ext\FinderForController](Ext-FinderForController.md) | 用于推断控制器路径的类名列表。|
| `cli_command_classes` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | CLI 命令类列表。|
| `cli_command_default` | `'help'` | [DuckPhp\Core\Console](Core-Console.md) | 默认命令。|
| `cli_command_group` | `[]` | [DuckPhp\Core\Console](Core-Console.md) | 命令分组注册信息。|
| `cli_command_method_prefix` | `'command_'` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | CLI 命令方法前缀。|
| `cli_command_prefix` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | CLI 命令命名空间前缀。|
| `cli_command_with_app` | `true` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否将当前应用类加入 CLI 命令类列表。|
| `cli_command_with_common` | `true` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否将默认命令类加入 CLI 命令类列表。|
| `cli_command_with_fast_installer` | `true` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否将安装器加入 CLI 命令类列表。|
| `cli_enable` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否启用命令行模式。|
| `cli_readlines_logfile` | `''` | [DuckPhp\Core\Console](Core-Console.md) | `readLines` 输入日志文件。|
| `close_resource_at_output` | `false` | [DuckPhp\Core\App](Core-App.md) | 输出时是否关闭资源（第三方扩展参考）。|
| `controller_base_class` | `null` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 控制器基类。|
| `controller_class_adjust` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 控制器类名调整规则。|
| `controller_class_base` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 控制器基类限制。|
| `controller_class_map` | `[]` | [DuckPhp\Core\Route](Core-Route.md) | 控制器类映射表。|
| `controller_class_map` | `[]` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器类映射表。|
| `controller_class_postfix` | `'Controller'` | [DuckPhp\Core\Route](Core-Route.md) | 控制器类名后缀。|
| `controller_class_postfix` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器类名后缀。|
| `controller_fix_mistake_path_info` | `true` | [DuckPhp\Core\Route](Core-Route.md) | 是否修复缺失。PATH_INFO。|
| `controller_method_prefix` | `'action_'` | [DuckPhp\Core\Route](Core-Route.md) | 控制器方法前缀。|
| `controller_method_prefix` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器方法前缀。|
| `controller_path_ext` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 控制器路径后缀。|
| `controller_path_ext` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器路径后缀。|
| `controller_prefix_post` | `'do_'` | [DuckPhp\Core\Route](Core-Route.md) | POST 请求方法额外前缀。|
| `controller_resource_prefix` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 资源 URL 前缀。|
| `controller_resource_prefix` | `''` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 资源 URL 前缀。|
| `controller_resource_prefix` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 资源 URL 前缀。|
| `controller_url_prefix` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 控制。URL 前缀。|
| `controller_url_prefix` | `''` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 控制。URL 前缀。|
| `controller_url_prefix` | `''` | [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | 控制。URL 前缀。|
| `controller_url_prefix` | `''` | [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 控制。URL 前缀。|
| `controller_url_prefix` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制。URL 前缀。|
| `controller_welcome_class` | `'Main'` | [DuckPhp\Core\Route](Core-Route.md) | 默认欢迎控制器类名。|
| `controller_welcome_class` | `'Main'` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 默认欢迎控制器类名。|
| `controller_welcome_class_visible` | `false` | [DuckPhp\Core\Route](Core-Route.md) | 是否允许 URL 中显示欢迎控制器名。|
| `controller_welcome_class_visible` | `false` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 是否允许 URL 中显示欢迎控制器名。|
| `controller_welcome_method` | `'index'` | [DuckPhp\Core\Route](Core-Route.md) | 默认欢迎方法名。|
| `controller_welcome_method` | `'index'` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 默认欢迎方法名。|
| `current` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | 当前页码。|
| `database` | `null` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 单一数据库配置。|
| `database_class` | `''` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 自定义数据库类名，为空使。`DuckPhp\Db\Db`。|
| `database_driver` | `''` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 数据库驱动类型（。`mysql`、`sqlite`）。|
| `database_driver` | `''` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 数据库驱动类型。|
| `database_list` | `null` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 多数据库配置列表。|
| `database_list_reload_by_setting` | `true` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 是否从设置文件重新加载数据库配置。|
| `database_list_try_single` | `true` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 是否尝试将单库配置转为列表。|
| `database_log_sql_level` | `'debug'` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 记录 SQL 的日志级别。|
| `database_log_sql_query` | `false` | [DuckPhp\Component\DbManager](Component-DbManager.md) | 是否记录 SQL 查询。|
| `default_exception_do_log` | `true` | [DuckPhp\Core\App](Core-App.md) | 发生异常时是否记录日志。|
| `default_exception_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 默认异常处理回调。|
| `dev_error_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 开发错误处理回调。|
| `empty_view_key_view` | `'view'` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | `_Show` 。`$data` 中保存视图名的键。|
| `empty_view_key_wellcome_class` | `'Main/'` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 该前缀的视图名会被裁剪。|
| `empty_view_skip_replace` | `false` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 是否跳过替换默认 `View` 组件。|
| `empty_view_trim_view_wellcome` | `true` | [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) | 是否裁剪欢迎类前缀。|
| `error_404` | `null` | [DuckPhp\Core\App](Core-App.md) | 404 错误处理视图或回调。|
| `error_500` | `null` | [DuckPhp\Core\App](Core-App.md) | 500 错误处理视图或回调。|
| `error_maintain` | `null` | [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md) | 维护页面视图或回调。|
| `error_need_install` | `null` | [DuckPhp\Component\RouteHookCheckStatus](Component-RouteHookCheckStatus.md) | 未安装提示页面视图或回调。|
| `exception_for_project` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 异常报告仅针对的异常类。|
| `exception_reporter` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 异常报告类。|
| `ext` | `[]` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 默认加载的扩展组件映射。|
| `ext` | `[]` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 扩展组件映射。|
| `ext_options_file` | `'config/DuckPhpApps.config.php'` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 额外选项文件路径。|
| `ext_options_file_enable` | `true` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否启用 `DuckPhpApps.config.php` 额外选项文件。|
| `facades_enable_autoload` | `true` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 是否启用门面自动加载。|
| `facades_map` | `[]` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 门面映射表。|
| `facades_namespace` | `'MyFacades'` | [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) | 门面类命名空间。|
| `force` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 是否强制覆盖现有文件。|
| `function_route` | `false` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 是否启用函数模式路由。|
| `function_route_404_to_index` | `false` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 404 是否。`index` 函数执行。|
| `function_route_method_prefix` | `'action_'` | [DuckPhp\Ext\RouteHookFunctionRoute](Ext-RouteHookFunctionRoute.md) | 函数模式路由方法前缀。|
| `handle_all_dev_error` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 是否捕获开发错误。|
| `handle_all_exception` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 是否捕获全部异常。|
| `handle_exception_on_init` | `true` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 初始化时是否启动异常处理。|
| `help` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 是否显示帮助。|
| `host` | `'127.0.0.1'` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 服务器监听地址。|
| `html_handler` | `null` | [DuckPhp\Core\App](Core-App.md) | HTML 编码函数回调。|
| `install_callback` | `null` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 安装完成回调。|
| `install_default_options` | `[]` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 安装默认选项。|
| `install_input_desc` | `''` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 安装输入提示描述。|
| `install_input_validators` | `[]` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 安装输入校验器。|
| `install_support_database_list` | `''` | [DuckPhp\FastInstaller\FastInstaller](FastInstaller-FastInstaller.md) | 支持的数据库列表。|
| `installed_options_enable` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否启用已安装选项文件。|
| `installed_options_file` | `'DuckPhpInstalled.config.php'` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 已安装选项文件名。|
| `is_debug` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否调试模式。|
| `is_debug` | `false` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 是否启用严格检查。|
| `json_view_skip_replace` | `false` | [DuckPhp\Ext\JsonView](Ext-JsonView.md) | 是否跳过替换默认 `View` 组件。|
| `json_view_skip_vars` | `[]` | [DuckPhp\Ext\JsonView](Ext-JsonView.md) | 输出 JSON 时排除的变量名。|
| `jsonrpc_backend` | `'https://127.0.0.1'` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | JsonRpc 后端地址。|
| `jsonrpc_check_token_handler` | `null` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | Token 处理回调。|
| `jsonrpc_enable_autoload` | `true` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 是否启用客户端类自动加载。|
| `jsonrpc_is_debug` | `false` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 是否调试。|
| `jsonrpc_namespace` | `'JsonRpc'` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 客户端类命名空间。|
| `jsonrpc_service_interface` | `''` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 服务接口或基类限制。|
| `jsonrpc_service_namespace` | `''` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 服务限定命名空间。|
| `jsonrpc_timeout` | `5` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 请求超时秒数。|
| `jsonrpc_wrap_auto_adjust` | `true` | [DuckPhp\Ext\JsonRpcExt](Ext-JsonRpcExt.md) | 是否自动调整封装。|
| `lang_cookie_name` | `'lang'` | [DuckPhp\Component\Lang](Component-Lang.md) | Cookie 语言键名。|
| `lang_default` | `null` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 默认语言，与 `Lang` 组件共享。|
| `lang_default` | `null` | [DuckPhp\Component\Lang](Component-Lang.md) | 默认回退语言。|
| `lang_detect_mode` | `['url', 'cookie', 'header', 'cli', 'default']` | [DuckPhp\Component\Lang](Component-Lang.md) | 语言检测顺序。|
| `lang_file_path` | `'lang/'` | [DuckPhp\Component\Lang](Component-Lang.md) | 语言文件目录（相对于 `config/`）。|
| `lang_final` | `null` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 最终语言，与 `Lang` 组件共享。|
| `lang_final` | `null` | [DuckPhp\Component\Lang](Component-Lang.md) | 最终语言，设置后跳过自动检测。|
| `lang_follow_root` | `true` | [DuckPhp\Component\Lang](Component-Lang.md) | 子应用是否跟随根应用语言。|
| `lang_handler` | `null` | [DuckPhp\Core\App](Core-App.md) | 语言处理回调。|
| `lang_simple_mode_only_sentences` | `[]` | [DuckPhp\Component\Lang](Component-Lang.md) | 简单模式下的翻译句子数组。|
| `lang_url_param` | `'lang'` | [DuckPhp\Component\Lang](Component-Lang.md) | URL 语言参数名。|
| `log_file_template` | `'log_%Y-%m-%d_%H-%i.log'` | [DuckPhp\Core\Logger](Core-Logger.md) | 日志文件名模板。|
| `log_prefix` | `'DuckPhpLog'` | [DuckPhp\Core\Logger](Core-Logger.md) | 日志前缀。|
| `middleware` | `[]` | [DuckPhp\Ext\MyMiddlewareManager](Ext-MyMiddlewareManager.md) | 中间件回调列表。|
| `mode_dir_basepath` | `''` | [DuckPhp\Ext\RouteHookDirectoryMode](Ext-RouteHookDirectoryMode.md) | 目录模式的基准路径。|
| `namespace` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 项目命名空间。|
| `namespace` | `''` | [DuckPhp\Core\Route](Core-Route.md) | 项目命名空间。|
| `namespace` | `''` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 应用命名空间。|
| `namespace` | `''` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 项目命名空间。|
| `namespace` | `''` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 项目命名空间。|
| `namespace` | `''` | [DuckPhp\Ext\RouteHookApiServer](Ext-RouteHookApiServer.md) | 项目命名空间。|
| `namespace` | `''` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 项目命名空间。|
| `namespace_business` | `''` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 业务类命名空间。|
| `namespace_controller` | `'Controller'` | [DuckPhp\Core\Route](Core-Route.md) | 控制器命名空间。|
| `namespace_controller` | `'Controller'` | [DuckPhp\Ext\MiniRoute](Ext-MiniRoute.md) | 控制器命名空间。|
| `namespace_controller` | `'Controller'` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 控制器命名空间。|
| `namespace_model` | `''` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 模型类命名空间。|
| `on_init` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 初始化完成后回调。|
| `options_file` | `'config/DuckPhpOptions.config.php'` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 选项文件路径。|
| `options_file_enable` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否启用选项文件。|
| `override_class` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 重载类名，存在则切换到该类初始化。|
| `override_class_from` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 切换前保存的。`override_class`。|
| `page_key` | `'page'` | [DuckPhp\Component\Pager](Component-Pager.md) | 页码 URL 参数名。|
| `page_size` | `30` | [DuckPhp\Component\Pager](Component-Pager.md) | 每页大小。|
| `path` | `null` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 项目根路径。|
| `path` | `''` | [DuckPhp\Core\Logger](Core-Logger.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Core\View](Core-View.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Component\Configer](Component-Configer.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 项目路径。|
| `path` | `''` | [DuckPhp\Ext\Misc](Ext-Misc.md) | 项目路径。|
| `path` | `''` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 项目路径。|
| `path` | `''` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 项目路径。|
| `path_config` | `'config'` | [DuckPhp\Component\Configer](Component-Configer.md) | 配置文件目录。|
| `path_document` | `'public'` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 文档根目录。|
| `path_document` | `'public'` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 文档根目录。|
| `path_info_compact_action_key` | `'_r'` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 替代 PATH_INFO 。action 参数键。|
| `path_info_compact_class_key` | `''` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 替代 PATH_INFO 。class 参数键。|
| `path_info_compact_enable` | `false` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 是否启用。PATH_INFO 兼容模式。|
| `path_info_compact_enable` | `true` | [DuckPhp\Component\RouteHookPathInfoCompat](Component-RouteHookPathInfoCompat.md) | 是否启用兼容模式。|
| `path_installed_options` | `'config'` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 已安装选项文件所在目录。|
| `path_lib` | `'lib'` | [DuckPhp\Ext\Misc](Ext-Misc.md) | Import 库目录路径。|
| `path_log` | `'runtime'` | [DuckPhp\Core\Logger](Core-Logger.md) | 日志目录路径。|
| `path_namespace` | `'app'` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 应用代码目录。|
| `path_resource` | `'res'` | [DuckPhp\Component\RouteHookResource](Component-RouteHookResource.md) | 资源目录。|
| `path_runtime` | `'runtime'` | [DuckPhp\Core\App](Core-App.md) | 运行目录路径。|
| `path_runtime` | `'runtime'` | [DuckPhp\Core\Runtime](Core-Runtime.md) | 运行目录路径。|
| `path_sql_dump` | `'config'` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | SQL 导出目录。|
| `path_view` | `'view'` | [DuckPhp\Core\View](Core-View.md) | 视图目录路径。|
| `port` | `'8080'` | [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) | 服务器监听端口。|
| `postfix_batch_business` | `'BatchBusiness'` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 批量业务类后缀。|
| `postfix_business_lib` | `'Lib'` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 业务库类后缀。|
| `postfix_ex_model` | `'ExModel'` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 混合模型后缀。|
| `postfix_model` | `'Model'` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 模型后缀。|
| `psr-4` | `[]` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | PSR-4 映射。|
| `redis` | `null` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 单一 Redis 配置。|
| `redis_cache_prefix` | `''` | [DuckPhp\Component\RedisCache](Component-RedisCache.md) | Redis 缓存键前缀。|
| `redis_cache_skip_replace` | `false` | [DuckPhp\Component\RedisCache](Component-RedisCache.md) | 是否跳过替换默认 `Cache` 组件。|
| `redis_list` | `null` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 。Redis 配置列表。|
| `redis_list_reload_by_setting` | `true` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 是否从设置文件重新加。Redis 配置。|
| `redis_list_try_single` | `true` | [DuckPhp\Component\RedisManager](Component-RedisManager.md) | 是否尝试将单 Redis 配置转为列表。|
| `rewrite` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | URL 重写回调。|
| `rewrite_map` | `[]` | [DuckPhp\Component\RouteHookRewrite](Component-RouteHookRewrite.md) | 重写映射表。|
| `route_map` | `[]` | [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 在默认路由失败后执行的路由映射。|
| `route_map_important` | `[]` | [DuckPhp\Component\RouteHookRouteMap](Component-RouteHookRouteMap.md) | 在默认路由前执行的路由映射。|
| `session_prefix` | `null` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | Session 前缀。|
| `setting_file` | `'config/DuckPhpSettings.config.php'` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 设置文件路径。|
| `setting_file_enable` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否启用设置文件。|
| `setting_file_ignore_exists` | `true` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 设置文件不存在时是否忽略。|
| `skip_404` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否跳过 404 处理。|
| `skip_app_autoload` | `false` | [DuckPhp\Core\AutoLoader](Core-AutoLoader.md) | 是否跳过应用命名空间自动加载。|
| `skip_exception_check` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否跳过异常检查，用于配合其他框架。|
| `sql_dump_data_tables` | `[]` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 需要导出数据的表。|
| `sql_dump_debug_show_sql` | `false` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 是否显示执行。SQL。|
| `sql_dump_exclude_tables` | `[]` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 排除的表。|
| `sql_dump_file` | `'install.sql'` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | SQL 导出文件名。|
| `sql_dump_include_tables` | `[]` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 显式包含的表。|
| `sql_dump_include_tables_all` | `false` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 是否导出所有表。|
| `sql_dump_include_tables_by_model` | `true` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 是否通过模型扫描表。|
| `sql_dump_install_replace_prefix` | `true` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | 安装时是否替换表前缀。|
| `sql_dump_prefix` | `''` | [DuckPhp\FastInstaller\SqlDumper](FastInstaller-SqlDumper.md) | SQL 中的表前缀。|
| `strict_check_context_class` | `null` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 指定上下文类。|
| `strict_check_enable` | `true` | [DuckPhp\Ext\StrictCheck](Ext-StrictCheck.md) | 是否启用严格检查模式。|
| `superglobal_auto_define` | `false` | [DuckPhp\Core\SuperGlobal](Core-SuperGlobal.md) | 初始化时是否定义 `__SUPERGLOBAL_CONTEXT` 宏。|
| `system_exception_handler` | `null` | [DuckPhp\Core\ExceptionManager](Core-ExceptionManager.md) | 系统异常处理回调。|
| `table_prefix` | `null` | [DuckPhp\DuckPhp](DuckPhp-DuckPhp.md) | 数据库表前缀。|
| `url` | `null` | [DuckPhp\Component\Pager](Component-Pager.md) | 基础 URL。|
| `use_env_file` | `false` | [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) | 是否加载 `.env` 文件。|
| `use_output_buffer` | `false` | [DuckPhp\Core\Runtime](Core-Runtime.md) | 是否启用输出缓冲。|
| `verbose` | `false` | [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) | 是否显示详情。|
| `view_skip_notice_error` | `true` | [DuckPhp\Core\View](Core-View.md) | 渲染视图时是否忽。notice 错误。|

---

> 查看所有选项的按字母顺序索引：[options-index.md](options-index.md)
