# 32 综合实战：前台 + 后台 + API

> 目标：把第三卷的机制拼成一个真实架构 —— 一个进程里跑三个应用，资源共享得明白、边界划得清楚、第三方后台不改一行代码就能改造。
> 前置：本卷第 26–31 章。预计 40 分钟（照抄骨架 + 改名字）。
> 起点：`tests/data_for_tests/ZThirdDemo`（两应用版，已验证）；本章把它扩成三应用。

## 目标架构

```
                     URL                      相位        资源前缀
前台 MainApp          /                        ''          /res/
├── 后台 AdminApp     /admin/…                 ':admin'    /admin/res/
└── API ApiApp        /api/…                   ':api'      /api/res/
```

```
                     前台      后台       API
视图（view/）          有       有        无（JsonView）
数据库               共享 DB   独立库     共享 DB（只读连接）
Redis                共享      共享       共享
事件总线             监听      广播       广播
CLI 命令             默认组    admin-*    api-*
覆盖清单              —        覆盖它的视图/配置/控制器   —
```

## 落地步骤

### 1. 目录与命名空间

```
project/
├── public/index.php                 ← 三个应用共用这一个入口
├── bin/cli.php
├── src/{Controller,Business,Model,System}/     ← 前台（命名空间 MyProj\…）
├── view/ config/ res/
├── admin/                            ← 后台（命名空间 MyProj\Admin\…，path=admin/）
│   └── {System,Controller,Business,Model,view,config,res}/
└── api/                              ← API（命名空间 MyProj\Api\…，path=api/）
    └── {System,Controller,Business,Model,config}/
```

自动加载（项目内部挂应用时加两条映射，Composer 包则自带）：

```php
AutoLoader::_()->init(['path' => __DIR__ . '/../src/', 'namespace' => 'MyProj', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace(__DIR__ . '/../admin/', 'MyProj\Admin');
AutoLoader::_()->assignPathNamespace(__DIR__ . '/../api/', 'MyProj\Api');
```

### 2. 主应用声明两个子应用

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'MyProj',

        'controller_url_prefix' => '',
        'controller_resource_prefix' => '/res/',      // 第 28 章的斜杠规则

        'installed' => false,                         // 第 31 章
        'url_install' => 'install',

        'ext' => [
            GlobalEvent::class => true,               // 事件总线默认关（第 29 章）
            PermissionMenu::class => true,            // 后台菜单（第 17 章）
        ],

        'app' => [
            AdminApp::class => [
                'name' => 'admin',
                'controller_url_prefix' => 'admin/',
                'controller_resource_prefix' => 'res/',
                'local_database' => true,                       // 后台用独立库
                'database_list' => [['dsn' => 'sqlite:' . __DIR__ . '/../../runtime/admin.db']],
                'controller_class_map' => [                     // 第 30 章：换掉它的控制器实现
                    \MyProj\Admin\Controller\MainController::class => \MyProj\Override\AdminMainController::class,
                ],
            ],
            ApiApp::class => [
                'name' => 'api',
                'controller_url_prefix' => 'api/',
                'controller_resource_prefix' => 'res/',
                'ext' => [JsonView::class => true],             // API 只出 JSON（第 11 章）
            ],
        ],
    ];

    protected function onInited(): void
    {
        parent::onInited();
        // 后台下的单，前台要收到通知（第 29 章）
        GlobalEvent::_()->globalOn('admin.order.paid', '', function ($order_id) {
            // 记流水、发消息、清缓存…
        });
    }
}
```

### 3. 共享决策表（照表决定，别临时起意）

| 资源 | 前台 | 后台 | API | 怎么实现 |
|---|---|---|---|---|
| 代码（框架与公共库） | 共享 | 共享 | 共享 | 同一个 vendor |
| 数据库连接 | 共享一份 | 独立一份 | 共享一份 | 后台加 `local_database => true` |
| Redis | 共享 | 共享 | 共享 | 什么都不用做（`RedisManager` 是公共组件） |
| 日志 | 共享 | 共享 | 共享 | 默认共享；要分开就各自 `path_log` |
| 语言/文案 | 各自一份 | 各自一份 | 各自一份 | 默认就是各自一份（第 29 章实测） |
| 路由/视图/配置 | 各自一份 | 各自一份 | 各自一份 | 默认行为 |

### 4. 覆盖清单（集中写在一处，别散落）

```php
// MyProj/System/Overrides.php 只是给人看的清单，不参与运行
// 1) 后台首页视图        view/admin/index.php            ← 覆盖 admin 应用的 view/index.php
// 2) 后台结算配置        config/admin/pay.php             ← 覆盖 admin/config/pay.php
// 3) 后台 logo 资源      res/admin/logo.png               ← 覆盖 admin/res/logo.png
// 4) 后台主控制器的 index src/Override/AdminMainController.php
// 5) 旧地址 /backend 重写到后台首页
RouteHookRewrite::_()->assignRewrite('/backend', 'admin/');   // 键必须带前导 '/'
```

### 5. 权限与菜单

```php
// 后台控制器统一实现标记接口，权限菜单与后台视图才会认它
class AdminMainController extends Base implements \DuckPhp\GlobalAdmin\AdminControllerInterface
{
    /** @menu_directory Admin\Order */
    public function index() { /* … */ }
}
```

```php
// 生成菜单（第 17 章 + Ext-PermissionMenu）
PermissionMenu::_()->buildAndSaveToConfigJsonFile();   // 一次：把注释扫成菜单配置
$tree = PermissionMenu::_()->loadAll();                // 每次请求：含所有子应用的菜单合并
$side = PermissionMenu::_()->permissionMenuTreeToSideMenuTree($tree);
```

### 6. 安装与上线

```php
// 前台控制器里守住安装状态
Helper::checkInstall();
```

- `installed=true` 后，安装入口要么关掉扩展要么加鉴权（第 31 章）。
- 生产直出资源：`RouteHookResource::_()->cloneResource()`，或构建脚本 `cp -r res/* public/`。
- 文档根指 `public/`；`admin/`、`api/` 的源码目录不要在 Web 根下。

### 7. CLI 命令按应用分组

```bash
php bin/cli.php help            # 前台与公共命令
php bin/cli.php admin-help      # 后台应用的命令（相位名前缀，第 23 章）
php bin/cli.php api-help        # API 应用的命令
```

## 冒烟测试：照抄这个模式

`ZThirdDemoTest` 用的「一次 init + 多次 request」模式最省事，直接拿来改成你的清单：

```php
private function request(string $path_info): string
{
    $_SERVER['PATH_INFO'] = $path_info;
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    ob_start();
    MainApp::_()->serve();          // 主应用会把请求交给子应用
    return (string) ob_get_clean();
}

// 清单（每条对应一个断言）
// GET /                 前台首页
// GET /admin/           后台首页（应是覆盖后的视图）
// GET /admin/res/logo.png
// GET /api/ping         JSON
// GET /backend          重写生效
// 事件：后台下单 → 前台监听器收到
```

要连 HTTP 层一起验（含真实 header/静态文件），再用 `ZAllDemoTest` 那套：起内置服务器 + `curl` 各路由比对输出（第 39 章，M4 落稿）。

## 排错速查

| 现象 | 先查这里 |
|---|---|
| 子应用 404 | 前缀是否带尾斜杠；`path`/命名空间映射是否对（第 27 章） |
| 视图不是期望的那份 | `getOverrideableFile('view','index.php',true,true)` 看命中谁；覆盖目录名是否等于子应用 `name`（第 30 章） |
| 资源 404 | 前缀斜杠规则：根 `'/res/'`、子应用 `'res/'`（第 28 章） |
| 事件收不到 | `GlobalEvent` 是否打开；`fire` 在哪个相位注册（第 29 章） |
| 后台连了前台的库 | 忘了 `local_database => true`（第 29 章） |
| 命令提示找不到 | 命令属于子应用 → 加相位前缀（第 23 章） |
| 一直跳安装页 | `installed` 还是 `false`（第 31 章） |

## 收尾

把这套结构（目录、前缀、共享决策、覆盖清单、冒烟清单）写进项目的 README，让下一个人不用重新推一遍；第三卷的 26–31 章就是他需要读的全部背景。

## 相关参考

- [第 26 章](advanced-phase.md) 相位 · [第 27 章](mount-app.md) 挂载 · [第 28 章](static-resources.md) 资源 · [第 29 章](component-sharing.md) 共享与通信 · [第 30 章](overriding.md) 覆盖 · [第 31 章](installer.md) 安装
- [Ext-PermissionMenu](../reference/Ext-PermissionMenu.md) · [GlobalAdmin](../reference/GlobalAdmin-GlobalAdmin.md) · [GlobalUser](../reference/GlobalUser-GlobalUser.md)
