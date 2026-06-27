# Appendix: Application Options Reference

This appendix lists all application options (`App::$options`) organized by class. **Bold** indicates default options.

## Core Options (DuckPhp\Core\App)

| Option | Default | Description |
|--------|---------|-------------|
| **`alias`** | `NULL` | Alias, currently only used for view directory |
| **`app`** | `[]` | Sub-applications, stores class name => options pairs |
| **`cli_command_classes`** | `[]` | Additional CLI command classes |
| **`cli_command_method_prefix`** | `'command_'` | CLI command method prefix |
| **`cli_command_prefix`** | `NULL` | CLI command prefix |
| **`cli_enable`** | `true` | Enable CLI mode |
| **`close_resource_at_output`** | `false` | Close resource output when outputting (for third-party extensions) |
| **`default_exception_do_log`** | `true` | Log exceptions when they occur |
| **`error_404`** | `NULL` | 404 error view or callback, only effective for root app |
| **`error_500`** | `NULL` | 500 error view or callback, only effective for root app |
| **`exception_for_project`** | `NULL` | Exception class for exception reporting |
| **`exception_reporter`** | `NULL` | Exception reporter class |
| **`ext`** | `[]` | Extensions to enable |
| **`html_handler`** | `NULL` | HTML encoding function callback |
| **`is_debug`** | `false` | Debug mode |
| **`lang_handler`** | `NULL` | Language encoding callback |
| **`namespace`** | `NULL` | Project namespace |
| **`on_init`** | `NULL` | Callback after initialization completes |
| **`override_class`** | `NULL` | If this class exists, create a new `override_class` instance |
| **`override_class_from`** | `NULL` | Saves old `override_class` when switching |
| **`path`** | `NULL` | Project path |
| **`path_runtime`** | `'runtime'` | Runtime directory |
| **`setting_file`** | `'config/DuckPhpSettings.config.php'` | Settings file name, only effective for root app |
| **`setting_file_enable`** | `true` | Use settings file |
| **`setting_file_ignore_exists`** | `true` | Do not error if settings file does not exist |
| **`skip_404`** | `false` | Do not handle 404, for use with other frameworks |
| **`skip_exception_check`** | `false` | Do not check exceptions in Run flow, throw them out |
| **`use_env_file`** | `false` | Use .env file, only effective for root app |

## Routing Options (DuckPhp\Core\Route)

| Option | Default | Description |
|--------|---------|-------------|
| **`controller_class_adjust`** | `''` | Controller class name case adjustment (e.g., `'uc_class'`) |
| **`controller_class_base`** | `''` | Controller base class restriction |
| **`controller_class_map`** | `[]` | Controller class replacement mapping |
| **`controller_class_postfix`** | `'Controller'` | Controller class suffix |
| **`controller_fix_mistake_path_info`** | `true` | Fix mistaken PATH_INFO |
| **`controller_method_prefix`** | `'action_'` | Controller method prefix |
| **`controller_path_ext`** | `''` | Controller path extension |
| **`controller_prefix_post`** | `'do_'` | POST method prefix |
| **`controller_resource_prefix`** | `''` | Static resource URL prefix |
| **`controller_url_prefix`** | `''` | URL prefix |
| **`controller_welcome_class`** | `'Main'` | Welcome page controller class |
| **`controller_welcome_class_visible`** | `false` | Whether welcome class is visible in URL |
| **`controller_welcome_method`** | `'index'` | Default action method |
| **`namespace`** | `''` | Namespace |
| **`namespace_controller`** | `'Controller'` | Controller namespace segment |
| **`rewrite_map`** | `[]` | URL rewrite mapping |
| **`route_map`** | `[]` | Route mapping (executed after default route) |
| **`route_map_important`** | `[]` | Priority route mapping (executed before default route) |

## Database Options (DuckPhp\Component\DbManager)

| Option | Default | Description |
|--------|---------|-------------|
| **`database`** | `NULL` | Single database configuration |
| **`database_class`** | `''` | Database class, defaults to `Db::class` |
| **`database_driver`** | `''` | Database driver |
| **`database_list`** | `NULL` | Multiple database configurations |
| **`database_list_reload_by_setting`** | `true` | Reload database config from settings |
| **`database_list_try_single`** | `true` | Try using single database configuration |
| **`database_log_sql_level`** | `'debug'` | SQL log level |
| **`database_log_sql_query`** | `false` | Log SQL queries |

## Exception Options (DuckPhp\Core\ExceptionManager)

| Option | Default | Description |
|--------|---------|-------------|
| **`default_exception_handler`** | `NULL` | Default exception handler callback |
| **`dev_error_handler`** | `NULL` | Development error handler callback |
| **`handle_all_dev_error`** | `true` | Capture development errors |
| **`handle_all_exception`** | `true` | Capture all exceptions |
| **`handle_exception_on_init`** | `true` | Handle exceptions during initialization |
| **`system_exception_handler`** | `NULL` | System exception handler callback |

## View Options (DuckPhp\Core\View)

| Option | Default | Description |
|--------|---------|-------------|
| **`path`** | `''` | Project path |
| **`path_view`** | `'view'` | View directory path |
| **`view_skip_notice_error`** | `true` | Suppress View notice errors |

## Logger Options (DuckPhp\Core\Logger)

| Option | Default | Description |
|--------|---------|-------------|
| **`log_file_template`** | `'log_%Y-%m-%d_%H_%i.log'` | Log file name template |
| **`log_prefix`** | `'DuckPhpLog'` | Log prefix |
| **`path`** | `''` | Project path |
| **`path_log`** | `'runtime'` | Log directory path |

## Language Options (DuckPhp\Component\Lang)

| Option | Default | Description |
|--------|---------|-------------|
| **`lang_cookie_name`** | `'lang'` | Language cookie name |
| **`lang_default`** | `NULL` | Default language |
| **`lang_detect_mode`** | `['url', 'cookie', 'header', 'cli', 'default']` | Language detection modes |
| **`lang_file_path`** | `'lang/'` | Language file path |
| **`lang_final`** | `NULL` | Final language (overrides detection) |
| **`lang_follow_root`** | `true` | Follow root app language settings |
| **`lang_simple_mode_only_sentences`** | `[]` | Simple mode sentences |
| **`lang_url_param`** | `'lang'` | Language URL parameter |

## Redis Options (DuckPhp\Component\RedisManager)

| Option | Default | Description |
|--------|---------|-------------|
| **`redis`** | `NULL` | Single Redis configuration |
| **`redis_list`** | `NULL` | Multiple Redis configurations |
| **`redis_list_reload_by_setting`** | `true` | Reload Redis config from settings |
| **`redis_list_try_single`** | `true` | Try using single Redis configuration |

## CLI Options (DuckPhp\Core\Console)

| Option | Default | Description |
|--------|---------|-------------|
| **`cli_command_default`** | `'help'` | Default CLI command |
| **`cli_command_group`** | `[]` | CLI command groups |
| **`cli_readlines_logfile`** | `''` | CLI readlines log file |

## Extension Options

### CallableView (DuckPhp\Ext\CallableView)

| Option | Default | Description |
|--------|---------|-------------|
| **`callable_view_class`** | `NULL` | CallableView class restriction |
| **`callable_view_foot`** | `NULL` | CallableView footer function |
| **`callable_view_head`** | `NULL` | CallableView header function |
| **`callable_view_is_object_call`** | `true` | Use object call |
| **`callable_view_prefix`** | `NULL` | CallableView method prefix |
| **`callable_view_skip_replace`** | `false` | Skip replacing default View |

### JsonView (DuckPhp\Ext\JsonView)

| Option | Default | Description |
|--------|---------|-------------|
| **`json_view_skip_replace`** | `false` | Skip replacing default View |
| **`json_view_skip_vars`** | `[]` | Variables to exclude from JSON output |

### JsonRpcExt (DuckPhp\Ext\JsonRpcExt)

| Option | Default | Description |
|--------|---------|-------------|
| **`jsonrpc_backend`** | `'https://127.0.0.1'` | JSON-RPC backend address |
| **`jsonrpc_check_token_handler`** | `NULL` | Token handler callback |
| **`jsonrpc_enable_autoload`** | `true` | Enable autoloading |
| **`jsonrpc_is_debug`** | `false` | Debug mode |
| **`jsonrpc_namespace`** | `'JsonRpc'` | Default namespace |
| **`jsonrpc_service_interface`** | `''` | Service interface restriction |
| **`jsonrpc_service_namespace`** | `''` | Service namespace |
| **`jsonrpc_timeout`** | `5` | Request timeout |
| **`jsonrpc_wrap_auto_adjust`** | `true` | Wrap auto adjustment |

### MiniRoute (DuckPhp\Ext\MiniRoute)

| Option | Default | Description |
|--------|---------|-------------|
| **`controller_class_postfix`** | `''` | No suffix (simplified routing) |
| **`controller_method_prefix`** | `''` | No prefix (simplified routing) |

## Other Options

### DuckPhp\HttpServer\HttpServer

| Option | Default | Description |
|--------|---------|-------------|
| **`host`** | `'127.0.0.1'` | Server host |
| **`port`** | `'8080'` | Server port |
| **`path`** | `''` | Project path |
| **`path_document`** | `'public'` | Document root |

### DuckPhp\Component\Pager

| Option | Default | Description |
|--------|---------|-------------|
| **`url`** | `null` | Base URL |
| **`current`** | `null` | Current page |
| **`page_size`** | `30` | Items per page |
| **`page_key`** | `'page'` | Page parameter key |
| **`rewrite`** | `null` | URL rewrite |
| **`pager_context_class`** | `null` | Context class |
