# 26 把外部应用挂进来

> 解决什么问题：手上有一个**不是你写的** DuckPHP 应用（同事的模块、老项目、`vendor/` 里的包），如何原封不动地挂进当前项目。
> 前置：[第 25 章 应用树与相位基础](advanced-phase.md)。预计 15 分钟。
> 示例：`tests/data_for_tests/ZThirdDemo`（主应用 + `third/` 里的第三方应用），跑 `tests/ZThirdDemoTest.php` 可验证。

## 三步走

### 第 1 步：让它的类能被加载

| 它是什么形态 | 怎么做 |
|---|---|
| Composer 包 | 什么都不用做，包的 `autoload` 已经注册 |
| 项目里的一个目录（如 `third/`） | 加一条命名空间映射 |

```php
// 主应用入口或测试里
AutoLoader::_()->init(['path' => $path . 'src/', 'namespace' => 'ZThirdDemo', 'path_namespace' => ''])->run();
AutoLoader::_()->assignPathNamespace($path . 'third/', 'ZThirdDemo\Third');
```

> 注意映射的**基准目录要与该应用的 `path` 对齐**：`ZThirdDemo\Third\System\ThirdApp` 必须能从 `third/` 下找到，因为控制器的扫描路径是从「应用类文件所在目录」推算的。

### 第 2 步：在 `app` 选项里登记

```php
class MainApp extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'namespace' => 'ZThirdDemo',
        'app' => [
            ThirdApp::class => [
                'name' => 'shop',                    // 相位名 → ':shop'
                'controller_url_prefix' => 'shop/',  // URL → /shop/
            ],
        ],
    ];
}
```

到这里它就已经挂上了：`GET /shop/` 由它处理，`GET /` 仍归主应用。

### 第 3 步：检查它自带的假设

| 要检查的                    | 为什么                                                         | 怎么办                                           |
| ----------------------- | ----------------------------------------------------------- | --------------------------------------------- |
| `path` / `namespace`    | 决定它的视图、配置、控制器从哪找                                            | 它自己声明了就不用管；没声明就在 `app` 里补                     |
| 欢迎页控制器是不是 `Main`        | 只有 welcome 类是 `Main` 时它的首页才是 `/shop/`，否则是 `/shop/Xxx/index` | 保持默认，或用 `controller_welcome_class`            |
| `controller_url_prefix` | 少一个尾斜杠就会拼出 `/shopXxx/index`                                 | 写 `'shop/'`                                   |
| 资源目录                    | 它的 `res/` 要能通过 URL 访问                                       | 配 `controller_resource_prefix`（第 27 章）        |
| 数据库                     | 它是否该用**独立的**连接                                              | 注入 `local_database => true` + `database_list` |
| CLI 命令                  | 它的命令会带相位前缀                                                  | `php cli.php shop-<命令>`（第 22 章）               |
|                         |                                                             |                                               |

## 它其实是「一个完整应用」

被挂进来的应用保留自己的一切：

```
third/                        ← 它自己的根（path）
├── System/ThirdApp.php       ← 它的入口类，继承 DuckPhp
├── Controller/MainController.php
├── Business/ShopBusiness.php
├── view/                     ← 它的视图（主应用可以按相位覆盖，见第 29 章）
├── config/                   ← 它的配置（同上）
└── res/                      ← 它的资源（同上）
```

```php
namespace ZThirdDemo\Third\System;

class ThirdApp extends \DuckPhp\DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../',
        'namespace' => 'ZThirdDemo\Third',
        'controller_resource_prefix' => 'res/',
        'shop_name' => 'Third Party Shop',
    ];
}
```

**不要为了接进来而改它的文件**：需要调整就在 `app` 选项里注入（第 2 步的表），需要改行为就走覆盖（第 29 章）。这样它下次升级你仍然能直接替换目录。

## 多入口共用一套代码

主应用和子应用共用同一批入口文件；入口里只做「初始化 + 跑」：

```php
// public/index.php —— Web 入口
AutoLoader::_()->init([...])->run();
MainApp::RunQuickly([]);

// bin/cli.php —— CLI 入口（同一套代码）
MainApp::RunQuickly(['cli_enable' => true]);
```

所以「挂一个后台应用」不需要第二个入口、也不需要第二个域名。

## 验证清单

```
GET /              → 主应用首页
GET /shop/         → 第三方应用首页（视图来自它自己，除非被覆盖）
GET /shop/native   → 它的其它动作
GET /shop/res/*.css→ 它的静态资源
GET /legacy-shop   → 主应用写的重写规则指到了它（第 29 章）
```

`ZThirdDemoTest` 就是照这个清单断言的，可以直接照抄成你自己项目的冒烟测试。

## 常见错误

| 现象                               | 原因                                | 改法                                                  |
| -------------------------------- | --------------------------------- | --------------------------------------------------- |
| 子应用 404，但类都在                     | 路由前缀不匹配                           | 前缀带尾斜杠；确认 `path` 指向它的根目录                            |
| 找到的是主应用的同名类                      | 两个应用用了同一命名空间                      | 每个应用一个独立命名空间（相位隔离的是**实例**，不是类）                      |
| 它的视图渲染报「变量不存在」                   | 它的视图依赖它自己的 `Helper`/数据            | 别只拷视图文件；要么整目录挂，要么按第 29 章「覆盖但不搬走」                    |
| CLI 里 `php cli.php help` 看不到它的命令 | 命令按相位加前缀了                         | 用 `php cli.php shop-help`，或看第 22 章的命令前缀规则           |
| 上线后它的资源 404                      | 生产用静态服务器直出，需要把 `res/` 部署到 docroot | 用 `RouteHookResource::_()->cloneResource()`（第 27 章） |
|                                  |                                   |                                                     |

## 下一步

- [第 27 章 静态资源与文档根](static-resources.md)
- [第 29 章 重写与覆盖](overriding.md)：不改它的文件，换掉它的视图/配置/控制器
