# Advanced Topics: Phase and Sub-Applications

## Phase System

DuckPHP's "Phase" is a core concept that enables running multiple isolated application environments within the same process. Each phase has its own independent singleton instance space.

### What is a Phase

Phase is the context isolation mechanism for DuckPHP's variable singleton (`::_()`). Within the same PHP process, by switching phases, the `::_()` of different phases will point to different instances without interfering with each other.

```
MainApp Phase
  ├── App::_()           → MainApp instance
  ├── Route::_()         → MainApp's routing
  ├── DbManager::_()     → MainApp's database
  └── UserService::_()   → MainApp's business service

SubApp Phase (DuckAdminApp)
  ├── App::_()           → DuckAdminApp instance
  ├── Route::_()         → DuckAdminApp's routing
  ├── DbManager::_()     → DuckAdminApp's database
  └── UserService::_()   → DuckAdminApp's business service
```

> **Note**: Phase only affects `::_()` variable singletons. Normal object instantiation (e.g., `new SomeClass()`) is not affected by phase.

### Switching Phases

```php
use DuckPhp\Core\App;

// Get current phase
$currentPhase = App::Phase();

// Switch to DuckAdminApp phase
App::Phase('DuckAdmin\System\DuckAdminApp');

// All singleton access is now DuckAdminApp's instance
$data = AdminService::_()->getData();

// Switch back to main application phase
App::Phase(App::Root()->getOverridingClass());
```

### Phase Isolation Use Cases

1. **Multi-tenant systems**: Each tenant has a phase, data is completely isolated
2. **API and Web coexistence**: API and Web applications share code but run independently
3. **Test environment isolation**: Switch phases in tests to simulate different application environments
4. **Plugin systems**: Each plugin has a phase to avoid naming conflicts

## Sub-Application System

Sub-applications are DuckPHP's core mechanism for implementing multi-application architecture. Through the `app` option configuration, multiple sub-applications can be mounted under the main application.

### Configuring Sub-Applications

A sub-application is another independent DuckPHP application, referenced by its class name. Configure it in the `app` option:

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            // Reference admin sub-application
            \DuckAdmin\System\DuckAdminApp::class => [
                'controller_url_prefix' => 'app/admin/',    // Access path prefix
                'controller_resource_prefix' => 'res/',     // Resource file prefix
            ],
            // Reference blog sub-application
            \BlogApp\System\App::class => [
                'controller_url_prefix' => 'blog/',
            ],
            // Reference API sub-application
            \ApiApp\System\App::class => [
                'controller_url_prefix' => 'api/',
                'ext' => [
                    \DuckPhp\Ext\JsonView::class => true,
                ],
            ],
        ],
    ];
}
```

The sub-application class must extend `DuckPhp`:

```php
<?php
// vendor/duckadmin/src/System/DuckAdminApp.php
namespace DuckAdmin\System;

use DuckPhp\DuckPhp;

class DuckAdminApp extends DuckPhp
{
    // Sub-application's own configuration
}
```

### Sub-Application Directory Structure

Sub-applications are usually introduced as Composer packages, but can also be placed in the project directory:

```
project/
├── src/
│   └── System/
│       └── App.php          # Main application configuration
├── vendor/
│   └── duckadmin/
│       └── src/
│           ├── Controller/   # Sub-application controllers
│           ├── Business/     # Sub-application business layer
│           ├── Model/        # Sub-application models
│           └── System/
│               └── DuckAdminApp.php  # Sub-application entry
└── view/
```

Or as an internal project module:

```
project/
├── src/
│   └── System/
│       └── App.php          # Main application configuration
├── admin/                    # Sub-application directory
│   ├── src/
│   │   ├── Controller/
│   │   ├── Business/
│   │   ├── Model/
│   │   └── System/
│   │       └── AdminApp.php # Sub-application entry
│   └── view/
└── view/
```

### Sub-Application Lifecycle

Sub-applications have the same lifecycle as the main application, initialized after the main application's `onInit()` and before `onInited()`:

```
MainApp::RunQuickly()
  ├── MainApp::init()
  │    ├── onPrepare()
  │    ├── initComponents()      ← Main application component initialization
  │    ├── onInit()              ← ① Main application initialization complete
  │    ├── onBeforeChildrenInit() ← ② Before sub-application initialization
  │    ├── DuckAdminApp::init()       ← Sub-application initialization
  │    │    ├── initComponents()
  │    │    └── onInit()
  │    ├── BlogApp::init()
  │    │    ├── initComponents()
  │    │    └── onInit()
  │    └── onInited()            ← ③ All initialization complete
  └── MainApp::run()
```

### Data Isolation Between Sub-Applications

Sub-applications share the main application's database connection by default, but can use an independent database through the `local_database` option:

```php
\BlogApp\System\App::class => [
    'controller_url_prefix' => 'blog/',
    'local_database' => true,           // Use independent database connection
    'database_list' => [
        ['dsn' => 'sqlite:' . __DIR__ . '/blog.db'],
    ],
],
```

Similarly, the `local_redis` option can enable independent Redis connections.

### Cross-Sub-Application Calls

In a sub-application, you can access other sub-application's services by switching phases:

```php
class BlogController
{
    public function action_index()
    {
        // Currently in BlogApp phase
        $posts = BlogModel::_()->getRecentPosts();
        
        // Switch to main application to get user info
        App::Phase('');  // Empty string means main application
        $user = UserService::_()->getCurrentUser();
        
        // Switch back to BlogApp
        App::Phase('BlogApp\System\App');
        
        Helper::Show(get_defined_vars(), 'blog/index');
    }
}
```

## Phase in CLI Applications

The phase system is also effective in CLI mode and can be used for batch processing data from different tenants:

```php
// cli.php
class App extends DuckPhp
{
    public function command_sync_all()
    {
        $tenants = ['tenant_a', 'tenant_b', 'tenant_c'];
        
        foreach ($tenants as $tenant) {
            // Switch to tenant phase
            App::Phase($tenant);
            
            // Execute sync operation
            SyncService::_()->syncData();
            
            echo "Synced: $tenant\n";
        }
        
        // Switch back to main phase
        App::Phase(App::Root()->getOverridingClass());
    }
}
```

## Best Practices

1. **Namespace isolation**: Each sub-application uses an independent namespace to avoid class name conflicts
2. **URL prefix distinction**: Distinguish different sub-application routes through `controller_url_prefix`
3. **Independent database configuration**: Use `local_database` in multi-tenant scenarios to ensure data isolation
4. **Avoid frequent phase switching**: Phase switching has performance overhead, minimize unnecessary switches
5. **Keep sub-applications lightweight**: Sub-applications should focus on specific functionality, avoid over-complication

## FAQ

### Q: Can sub-applications have nested sub-applications?
A: Yes. Sub-applications can continue to configure the `app` option to mount lower-level sub-applications, forming a tree structure.

### Q: Can sub-applications and main applications share views?
A: Not by default. Sub-applications use their own `path_view` directory. Templates can be shared through the `alias` option or view inheritance mechanisms.

### Q: How to access main application configuration in a sub-application?
A: You can access the main application's configuration options through `App::Root()->options`.

### Q: Will sub-application exceptions propagate to the main application?
A: Yes. Uncaught exceptions from sub-applications will propagate upward to the main application's exception handler.
