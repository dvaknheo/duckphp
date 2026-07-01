# 高级主题：相位与子应用

## 相位（Phase）系统

DuckPHP 的"相位"（Phase）是一个核心概念，它实现了在同一进程中运行多个相互隔离的应用环境。每个相位拥有独立的单例实例空间。

### 什么是相位

相位是 DuckPHP 可变单例（`::_()`）的上下文隔离机制。在同一个 PHP 进程中，通过切换相位，不同相位的 `::_()` 会指向不同的实例，而不会相互干扰。



```
主应用相位 (MainApp)
  ├── App::_()           → MainApp 实例
  ├── Route::_()         → MainApp 的路由
  ├── DbManager::_()     → MainApp 的数据库
  └── UserService::_()   → MainApp 的业务服务

子应用相位 (DuckAdminApp)
  ├── App::_()           → DuckAdminApp 实例
  ├── Route::_()         → DuckAdminApp 的路由
  ├── DbManager::_()     → DuckAdminApp 的数据库
  └── UserService::_()   → DuckAdminApp 的业务服务
```

> **注意**：相位仅对 `::_()` 可变单例生效。普通对象实例化（如 `new SomeClass()`）不受相位影响。
> 可以使用
> \Duckphp\Core\PhaseContainer::GetContainerInstanceEx()->dumpAllObject();
> 查看什么相位
### 切换相位

```php
use DuckPhp\Core\App;

// 获取当前相位
$currentPhase = App::Phase();

// 切换到 DuckAdminApp 相位
App::Phase('DuckAdmin\System\DuckAdminApp');

// 此时所有单例访问都是 DuckAdminApp 的实例
$data = AdminService::_()->getData();

// 切回主应用相位
App::Phase(App::Root()->getOverridingClass());
```
> php cli.php require DuckUser\System\DuckUserApp
### 相位隔离的应用场景

1. **多租户系统**：每个租户一个相位，数据完全隔离
2. **API 与 Web 共存**：API 应用和 Web 应用共享代码但独立运行
3. **测试环境隔离**：在测试中切换相位模拟不同应用环境
4. **插件系统**：每个插件一个相位，避免命名冲突

## 子应用系统

子应用是 DuckPHP 实现多应用架构的核心机制。通过配置 `app` 选项，可以在主应用下挂载多个子应用。

### 配置子应用

子应用是另一个独立的 DuckPHP 应用，通过类名引用。在 `app` 选项中配置：

```php
class App extends DuckPhp
{
    public $options = [
        'app' => [
            // 引用后台管理子应用
            \DuckAdmin\System\DuckAdminApp::class => [
                'controller_url_prefix' => 'app/admin/',    // 访问路径前缀
                'controller_resource_prefix' => 'res/',     // 资源文件前缀
            ],
            // 引用博客子应用
            \BlogApp\System\App::class => [
                'controller_url_prefix' => 'blog/',
            ],
            // 引用 API 子应用
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

子应用类需要继承 `DuckPhp`：

```php
<?php
// vendor/duckadmin/src/System/DuckAdminApp.php
namespace DuckAdmin\System;

use DuckPhp\DuckPhp;

class DuckAdminApp extends DuckPhp
{
    // 子应用自己的配置
}
```

### 子应用目录结构

子应用通常以 Composer 包的形式引入，也可以放在项目目录中：

```
project/
├── src/
│   └── System/
│       └── App.php          # 主应用配置
├── vendor/
│   └── duckadmin/
│       └── src/
│           ├── Controller/   # 子应用控制器
│           ├── Business/     # 子应用业务层
│           ├── Model/        # 子应用模型
│           └── System/
│               └── DuckAdminApp.php  # 子应用入口
└── view/
```

或者作为项目内部模块：

```
project/
├── src/
│   └── System/
│       └── App.php          # 主应用配置
├── admin/                    # 子应用目录
│   ├── src/
│   │   ├── Controller/
│   │   ├── Business/
│   │   ├── Model/
│   │   └── System/
│   │       └── AdminApp.php # 子应用入口
│   └── view/
└── view/
```

### 子应用的生命周期

子应用拥有与主应用相同的生命周期，在主应用 `onInit()` 之后、`onInited()` 之前初始化：

```
MainApp::RunQuickly()
  ├── MainApp::init()
  │    ├── onPrepare()
  │    ├── initComponents()      ← 主应用组件初始化
  │    ├── onInit()              ← ① 主应用初始化完成
  │    ├── onBeforeChildrenInit() ← ② 子应用初始化前
  │    ├── DuckAdminApp::init()       ← 子应用初始化
  │    │    ├── initComponents()
  │    │    └── onInit()
  │    ├── BlogApp::init()
  │    │    ├── initComponents()
  │    │    └── onInit()
  │    └── onInited()            ← ③ 全部初始化完成
  └── MainApp::run()
```

### 子应用间的数据隔离

子应用默认共享主应用的数据库连接，但可以通过 `local_database` 选项使用独立数据库：

```php
\BlogApp\System\App::class => [
    'controller_url_prefix' => 'blog/',
    'local_database' => true,           // 使用独立数据库连接
    'database_list' => [
        ['dsn' => 'sqlite:' . __DIR__ . '/blog.db'],
    ],
],
```

同理，`local_redis` 选项可以启用独立的 Redis 连接。

### 跨子应用调用

在子应用中可以通过相位切换访问其他子应用的服务：

```php
class BlogController
{
    public function action_index()
    {
        // 当前在 BlogApp 相位
        $posts = BlogModel::_()->getRecentPosts();
        
        // 切换到主应用获取用户信息
        App::Phase('');  // 空字符串表示主应用
        $user = UserService::_()->getCurrentUser();
        
        // 切回 BlogApp
        App::Phase('BlogApp\System\App');
        
        Helper::Show(get_defined_vars(), 'blog/index');
    }
}
```

## 相位在 CLI 中的应用

相位系统在 CLI 模式下同样有效，可以用于批量处理不同租户的数据：

```php
// cli.php
class App extends DuckPhp
{
    public function command_sync_all()
    {
        $tenants = ['tenant_a', 'tenant_b', 'tenant_c'];
        
        foreach ($tenants as $tenant) {
            // 切换到租户相位
            App::Phase($tenant);
            
            // 执行同步操作
            SyncService::_()->syncData();
            
            echo "Synced: $tenant\n";
        }
        
        // 切回主相位
        App::Phase(App::Root()->getOverridingClass());
    }
}
```

## 最佳实践

1. **子应用命名空间隔离**：每个子应用使用独立的命名空间，避免类名冲突
2. **URL 前缀区分**：通过 `controller_url_prefix` 区分不同子应用的路由
3. **独立数据库配置**：多租户场景下使用 `local_database` 确保数据隔离
4. **避免跨相位频繁切换**：相位切换有性能开销，尽量减少不必要的切换
5. **子应用保持轻量**：子应用应专注于特定功能，避免过度复杂化

## 常见问题

### Q: 子应用可以嵌套子应用吗？
A: 可以。子应用可以继续配置 `app` 选项挂载更下级的子应用，形成树状结构。

### Q: 子应用和主应用的视图可以共享吗？
A: 默认不共享。子应用使用自己的 `path_view` 目录。可以通过 `alias` 选项或视图继承机制共享模板。

### Q: 如何在子应用中访问主应用的配置？
A: 通过 `App::Root()->options` 可以访问主应用的配置选项。

### Q: 子应用异常会传递到主应用吗？
A: 会。子应用未捕获的异常会向上传递到主应用的异常处理器。
