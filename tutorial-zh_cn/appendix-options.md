# 附录：应用选项参考

本附录按类名顺序列出所有应用选项（`App::$options`）。**粗体**表示默认选项。

## 核心选项 (DuckPhp\Core\App)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`alias`** | `NULL` | 别名，目前只用于视图目录 |
| **`app`** | `[]` | 子应用，保存类名=>选项对 |
| **`cli_command_classes`** | `[]` | 额外的 CLI 命令类 |
| **`cli_command_method_prefix`** | `'command_'` | CLI 命令方法前缀 |
| **`cli_command_prefix`** | `NULL` | CLI 命令前缀 |
| **`cli_enable`** | `true` | 启用命令行模式 |
| **`close_resource_at_output`** | `false` | 输出时关闭资源输出（仅供第三方扩展参考） |
| **`default_exception_do_log`** | `true` | 发生异常时记录日志 |
| **`error_404`** | `NULL` | 404 错误处理的视图或回调，仅根应用有效 |
| **`error_500`** | `NULL` | 500 错误处理的视图或回调，仅根应用有效 |
| **`exception_for_project`** | `NULL` | 异常报告仅针对的异常类 |
| **`exception_reporter`** | `NULL` | 异常报告类 |
| **`ext`** | `[]` | 要启用的扩展 |
| **`html_handler`** | `NULL` | HTML 编码函数回调 |
| **`is_debug`** | `false` | 是否调试模式 |
| **`lang_handler`** | `NULL` | 语言编码回调 |
| **`namespace`** | `NULL` | 项目命名空间 |
| **`on_init`** | `NULL` | 初始化完成后处理回调 |
| **`override_class`** | `NULL` | 如果此类存在，则新建 `override_class` 初始化 |
| **`override_class_from`** | `NULL` | `override_class` 切换时保存旧的 `override_class` |
| **`path`** | `NULL` | 工程路径 |
| **`path_runtime`** | `'runtime'` | 运行时目录 |
| **`setting_file`** | `'config/DuckPhpSettings.config.php'` | 设置文件名，仅根应用有效 |
| **`setting_file_enable`** | `true` | 使用设置文件 |
| **`setting_file_ignore_exists`** | `true` | 设置文件不存在也不报错 |
| **`skip_404`** | `false` | 不处理 404，用于配合其他框架使用 |
| **`skip_exception_check`** | `false` | 不在 Run 流程检查异常，把异常抛出外面 |
| **`use_env_file`** | `false` | 使用 .env 文件，仅根应用有效 |

## 路由选项 (DuckPhp\Core\Route)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`controller_class_adjust`** | `''` | 控制器类名大小写调整（如 `'uc_class'`） |
| **`controller_class_base`** | `''` | 控制器基类限制 |
| **`controller_class_map`** | `[]` | 控制器类替换映射 |
| **`controller_class_postfix`** | `'Controller'` | 控制器类后缀 |
| **`controller_fix_mistake_path_info`** | `true` | 修正错误的 PATH_INFO |
| **`controller_method_prefix`** | `'action_'` | 控制器方法前缀 |
| **`controller_path_ext`** | `''` | 控制器路径扩展名 |
| **`controller_prefix_post`** | `'do_'` | POST 方法前缀 |
| **`controller_resource_prefix`** | `''` | 静态资源 URL 前缀 |
| **`controller_url_prefix`** | `''` | URL 前缀 |
| **`controller_welcome_class`** | `'Main'` | 欢迎页控制器类 |
| **`controller_welcome_class_visible`** | `false` | 欢迎类是否在 URL 中可见 |
| **`controller_welcome_method`** | `'index'` | 默认 action 方法 |
| **`namespace`** | `''` | 命名空间 |
| **`namespace_controller`** | `'Controller'` | 控制器命名空间段 |
| **`rewrite_map`** | `[]` | URL 重写映射 |
| **`route_map`** | `[]` | 路由映射（默认路由后执行） |
| **`route_map_important`** | `[]` | 优先路由映射（默认路由前执行） |

## 数据库选项 (DuckPhp\Component\DbManager)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`database`** | `NULL` | 单一数据库配置 |
| **`database_class`** | `''` | 数据库类，默认为 `Db::class` |
| **`database_driver`** | `''` | 数据库驱动 |
| **`database_list`** | `NULL` | 多数据库配置 |
| **`database_list_reload_by_setting`** | `true` | 从设置里重新加载数据库配置 |
| **`database_list_try_single`** | `true` | 尝试使用单一数据库配置 |
| **`database_log_sql_level`** | `'debug'` | SQL 日志级别 |
| **`database_log_sql_query`** | `false` | 记录 SQL 查询 |

## 异常选项 (DuckPhp\Core\ExceptionManager)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`default_exception_handler`** | `NULL` | 默认的异常处理回调 |
| **`dev_error_handler`** | `NULL` | 调试错误的回调 |
| **`handle_all_dev_error`** | `true` | 抓取调试错误 |
| **`handle_all_exception`** | `true` | 抓取全部异常 |
| **`handle_exception_on_init`** | `true` | 初始化时处理异常 |
| **`system_exception_handler`** | `NULL` | 系统的异常调试回调 |

## 视图选项 (DuckPhp\Core\View)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`path`** | `''` | 工程路径 |
| **`path_view`** | `'view'` | 视图路径 |
| **`view_skip_notice_error`** | `true` | 关闭视图的 notice 警告 |

## 日志选项 (DuckPhp\Core\Logger)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`log_file_template`** | `'log_%Y-%m-%d_%H_%i.log'` | 日志文件名模板 |
| **`log_prefix`** | `'DuckPhpLog'` | 日志前缀 |
| **`path`** | `''` | 工程路径 |
| **`path_log`** | `'runtime'` | 日志目录路径 |

## 语言选项 (DuckPhp\Component\Lang)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`lang_cookie_name`** | `'lang'` | 语言 cookie 名 |
| **`lang_default`** | `NULL` | 默认语言 |
| **`lang_detect_mode`** | `['url', 'cookie', 'header', 'cli', 'default']` | 语言检测模式 |
| **`lang_file_path`** | `'lang/'` | 语言文件路径 |
| **`lang_final`** | `NULL` | 最终语言（覆盖检测） |
| **`lang_follow_root`** | `true` | 跟随根应用语言设置 |
| **`lang_simple_mode_only_sentences`** | `[]` | 简单模式句子 |
| **`lang_url_param`** | `'lang'` | 语言 URL 参数 |

## Redis 选项 (DuckPhp\Component\RedisManager)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`redis`** | `NULL` | 单一 Redis 配置 |
| **`redis_list`** | `NULL` | 多 Redis 配置 |
| **`redis_list_reload_by_setting`** | `true` | 从设置里重新加载 Redis 配置 |
| **`redis_list_try_single`** | `true` | 尝试使用单一 Redis 配置 |

## CLI 选项 (DuckPhp\Core\Console)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`cli_command_default`** | `'help'` | 默认 CLI 命令 |
| **`cli_command_group`** | `[]` | CLI 命令组 |
| **`cli_readlines_logfile`** | `''` | CLI 读取日志文件 |

## 扩展选项

### CallableView (DuckPhp\Ext\CallableView)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`callable_view_class`** | `NULL` | CallableView 限定类 |
| **`callable_view_foot`** | `NULL` | CallableView 页脚函数 |
| **`callable_view_head`** | `NULL` | CallableView 页眉函数 |
| **`callable_view_is_object_call`** | `true` | 使用对象调用 |
| **`callable_view_prefix`** | `NULL` | CallableView 方法前缀 |
| **`callable_view_skip_replace`** | `false` | 跳过替换默认 View |

### JsonView (DuckPhp\Ext\JsonView)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`json_view_skip_replace`** | `false` | 跳过替换默认 View |
| **`json_view_skip_vars`** | `[]` | JSON 输出排除的变量 |

### JsonRpcExt (DuckPhp\Ext\JsonRpcExt)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`jsonrpc_backend`** | `'https://127.0.0.1'` | JSON-RPC 后端地址 |
| **`jsonrpc_check_token_handler`** | `NULL` | Token 处理回调 |
| **`jsonrpc_enable_autoload`** | `true` | 启用自动加载 |
| **`jsonrpc_is_debug`** | `false` | 调试模式 |
| **`jsonrpc_namespace`** | `'JsonRpc'` | 默认命名空间 |
| **`jsonrpc_service_interface`** | `''` | 服务接口限制 |
| **`jsonrpc_service_namespace`** | `''` | 服务命名空间 |
| **`jsonrpc_timeout`** | `5` | 请求超时时间 |
| **`jsonrpc_wrap_auto_adjust`** | `true` | 封装自动调整 |

### MiniRoute (DuckPhp\Ext\MiniRoute)

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`controller_class_postfix`** | `''` | 无后缀（简化路由） |
| **`controller_method_prefix`** | `''` | 无前缀（简化路由） |

## 其他选项

### DuckPhp\HttpServer\HttpServer

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`host`** | `'127.0.0.1'` | 服务器主机 |
| **`port`** | `'8080'` | 服务器端口 |
| **`path`** | `''` | 工程路径 |
| **`path_document`** | `'public'` | 文档根目录 |

### DuckPhp\Component\Pager

| 选项 | 默认值 | 说明 |
|------|--------|------|
| **`url`** | `null` | 基础 URL |
| **`current`** | `null` | 当前页 |
| **`page_size`** | `30` | 每页条数 |
| **`page_key`** | `'page'` | 页码参数键 |
| **`rewrite`** | `null` | URL 重写 |
| **`pager_context_class`** | `null` | 上下文类 |
