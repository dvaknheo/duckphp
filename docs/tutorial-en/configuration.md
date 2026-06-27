# Application Options and Settings

## Application Settings

When you create a project using the scaffold, you will see `config/DuckPhpSettings.config.php`.
This file is used to save sensitive information, storing runtime configurations (database, Redis, etc.).

The term `app settings` refers to these options. They are global.
A typical settings file looks like this:

```php
<?php
return [
    'duckphp_is_debug' => true,         // Debug mode
    //'duckphp_platform' => 'default',  // Platform identifier
    //'duckphp_is_maintain' => false,   // Maintenance mode
    'database_list' => [
        [
            'dsn' => 'mysql:host=127.0.0.1;dbname=test;charset=utf8mb4;',
            'username' => 'root',
            'password' => 'password',
        ],
    ],
];
```
> - Debug mode: Enable debug mode during local development. Very useful.
> - Platform mode: Used to identify which machine you are on in multi-machine deployments.
> - Maintenance mode: When enabled, enters the page configured by the `error_maintain` application option.

## Application Options

What are `application options`? A typical DuckPHP application entry class looks like this:

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        //'path_info_compact_enable' => false,
        
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
		
        'exception_for_project'  => ProjectException::class,
        'exception_for_business'  => BusinessException::class,
        'exception_for_controller'  => ControllerException::class,
        'exception_reporter' =>  ExceptionReporter::class,
        //...
    ];
}
```

This `options` property is the `app options`.
These application options have a bunch of default values, which you can dump out.
By modifying these application options, you can get different application behaviors.

Can the default location of the settings file `config/DuckPhpSettings.config.php` be moved? Yes. The default values in the application options are:
```
    'setting_file' => 'config/DuckPhpSettings.config.php',
    'setting_file_enable' => true,
```
For example, with the application option `'use_env_file' => true`, the framework will automatically load `.env` from the project root as settings.

But this is not all of the application options. The framework's default loaded components also have their own options, which you can override in `App::$options`.

### Default Loaded Components

The framework automatically initializes the following components on startup, each with its own default options:

**Core Components**
- `Logger` — Logging
- `SuperGlobal` — Superglobal variable management
- `View` — View rendering
- `Route` — Routing system
- `ExceptionManager` — Exception handling

**Data Components**
- `DbManager` — Database manager (auto-loaded for root app)
- `RedisManager` — Redis manager (auto-loaded for root app)

**Extension Components** (enabled via `ext` option)
- `Lang` — Internationalization
- `RouteHookCheckStatus` — Maintenance mode check
- `RouteHookRewrite` — URL rewriting
- `RouteHookRouteMap` — Route mapping
- `RouteHookResource` — Static resource handling

### Overriding Component Default Options

When you set an option in `App::$options` with the same name as a component's option, you override that component's default value. For example:

```php
class App extends DuckPhp
{
    public $options = [
        // Change the route method prefix, default is 'action_'
        'controller_method_prefix' => 'call_',
    ];
}
```

After the change, the home page entry method changes from `MainController::action_index()` to `MainController::call_index()`.

Another example modifying log configuration:

```php
class App extends DuckPhp
{
    public $options = [
        'log_prefix' => 'MyApp',              // Log prefix
        'log_file_template' => 'app_%Y%m%d.log', // Log filename format
    ];
}
```

For a complete list of options for each component, see [Appendix: Application Options Reference](appendix-options.md).

## Quick Reference for Application Options

### Path Related

| Option | Default Value | Description |
|---|---|---|
| `path` | Auto-detect | Absolute path to the project root |
| `namespace` | Auto-detect | Project namespace |
| `path_view` | `'view'` | View template directory (relative to project root) |
| `path_config` | `'config'` | Configuration directory |
| `path_runtime` | `'runtime'` | Runtime directory (logs, etc.) |

### Debug and Error

| Option | Default Value | Description |
|---|---|---|
| `is_debug` | `false` | Whether to enable debug mode |
| `error_404` | `null` | 404 error view, `'_sys/error_404'`, etc. |
| `error_500` | `null` | 500 error view |
| `exception_for_project` | `\Exception::class` | Project exception base class |
| `exception_for_business` | `null` (inherits from `exception_for_project`) | Business layer exception class |
| `exception_for_controller` | `null` (inherits from `exception_for_project`) | Controller layer exception class |
| `exception_reporter` | `null` | Exception reporter class name |

### Routing Related

| Option | Default Value | Description |
|---|---|---|
| `namespace_controller` | `'Controller'` | Controller namespace segment |
| `controller_class_postfix` | `'Controller'` | Controller class suffix |
| `controller_method_prefix` | `'action_'` | Controller method prefix |
| `controller_welcome_class` | `'Main'` | Welcome page controller class name |
| `controller_welcome_method` | `'index'` | Default action method |
| `controller_url_prefix` | `''` | URL prefix |
| `controller_resource_prefix` | `''` | Static resource URL prefix |
| `controller_class_map` | `[]` | Controller class replacement mapping |
| `rewrite_map` | `[]` | URL rewrite mapping |
| `route_map` | `[]` | Route mapping |
| `route_map_important` | `[]` | Priority route mapping |
| `skip_404` | `false` | Skip 404 handling |
