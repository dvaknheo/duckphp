# 31 安装器与 Web 安装流程

> 解决什么问题：「安装」在 DuckPHP 里其实是三件不同的事 —— 建项目、判安装状态、跑安装向导。这章把它们分清。
> 前置：[第 2 章 安装与最小示例](install.md)、[第 27 章](mount-app.md)。预计 10 分钟。

## 三种「安装」，别混

| # | 是什么 | 用什么 |
|---|---|---|
| ① | **建一个新项目**（脚手架） | `vendor/bin/duckphp new`（`DuckPhpInstaller`），模板来自仓库的 `skeleton/` |
| ② | **应用自身的安装状态** | 选项 `installed` + `url_install`，用 `Helper::checkInstall()` 做守卫 |
| ③ | **在浏览器里跑安装向导** | 扩展 `RouteHookWebInstaller`，它接管安装 URL 并渲染向导 |

## ① 用脚手架建项目

```bash
composer require dvaknheo/duckphp
php vendor/bin/duckphp new      # 交互式，按 skeleton/ 生成工程
php vendor/bin/duckphp show     # 看当前安装/配置情况
php vendor/bin/duckphp help     # 三个命令：new / show / help
```

生成的结构（`public/index.php` 与 `bin/cli.php` 通常不用改）：

```
project/
├── public/index.php      ├── config/          ├── view/{main.php,_sys/}
├── bin/cli.php           ├── src/{Controller,Business,Model,System}/
└── runtime/              └── composer.json
```

## ② 安装状态：`installed` + 一次守卫

```php
class MyApp extends DuckPhp
{
    public $options = [
        'installed' => false,      // 还没装好
        'url_install' => 'install',// 装好之前访问哪里去装
    ];
}

// 想拦住「没装好就用」的入口（常见放在控制器/中间件式钩子的最前面）
Helper::checkInstall();            // installed=false → 302 到 url_install，然后 exit
```

它做的事就三行（[参考手册 Core-App](../reference/Core-App.md)）：

```php
public function checkInstallToPage(?string $url_install = null): void
{
    $url_install = $url_install ?? ($this->options['url_install'] ?? 'install');
    if (!$this->options['installed']) {
        SystemWrapper::_()->_header('location: '.Route::Url($url_install), true, 302);
        SystemWrapper::_()->_exit();
    }
}
```

`ZThirdDemo` 里的实测断言（`/install` 动作的第一行就是 `Helper::checkInstall()`）：

| 条件 | 结果 |
|---|---|
| `installed = false` | 输出一次 `location: …/install` 头 + 调用一次 `exit`（请求就此结束） |
| `installed = true` | 什么都不做，继续往下走 |

> ⚠️ **不要在 `SystemWrapper` 里替换掉 `exit` 之后又依赖「后面的代码不会执行」**：测试里我们把 `exit` 换成了空函数，于是 `checkInstall()` 之后的行**照样执行**（`ZThirdDemoTest` 正是据此断言的）。生产里 `exit` 是真的退出。

## ③ Web 安装向导

装上扩展，它就接管 `url_install` 那个地址：

```php
'ext' => [
    \DuckPhp\Ext\RouteHookWebInstaller::class => [
        'web_installer_use_database' => true,
        'web_installer_use_redis' => true,
        'web_installer_database_drivers' => ['sqlite' => true, 'pgsql' => true],
        'web_installer_view' => '',                  // 自定义向导视图（空则用内置）
        'web_installer_force' => false,              // true 覆盖已存在的配置
        'web_installer_check_custom_callback' => null,  // 判断「能不能装」
        'web_installer_do_custom_callback' => null,     // 真正执行安装
        'web_installer_render_custom_callback' => null, // 自己渲染页面
        'web_installer_default_sentences' => [],        // 覆盖文案（会并入 Lang）
    ],
],
```

流程：

```
GET /install
  ├─ 钩子比对 url_install（用 __url() 归一，所以带不带 index.php 都认）
  ├─ 已安装（installed=true）→ 302 走人
  └─ 未安装 → 采集数据库/Redis 参数 → 写配置 → 置 installed
```

三个回调分别对应「能否安装 / 怎么安装 / 怎么显示」，只填你需要的那一个即可；文案走 `Lang`，所以向导是多语言的（第 22 章）。

## 上线检查清单

- [ ] `installed` 设为 `true`（否则线上会一直往安装页跳）。
- [ ] 安装向导要么卸载扩展、要么在 Web 层按 IP/鉴权挡住（它能写配置，属敏感入口）。
- [ ] 脚手架自带的一次性示例文件（`SomeAction`、`testController`、`DemoBusiness`…）删掉，别把示例带上线。
- [ ] `runtime/` 可写；`config/` 不要在 Web 根下暴露。
- [ ] 文档根指向 `public/`（第 7 章）。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| 每个页面都跳到安装页 | `installed` 还是 `false` | 置 `true`，或把守卫放在你真正想拦的入口 |
| `checkInstall()` 之后代码还在跑 | 你在测试里替换了 `exit` | 那是测试行为；生产是真空退出，别依赖「后面不执行」 |
| 向导页 404 | 扩展没装，或 `url_install` 与访问地址不一致 | 确认 `ext` 里有 `RouteHookWebInstaller`，地址与 `url_install` 一致 |
| 向导写不进配置 | `config/` 不可写，或 `web_installer_force=false` 且文件已存在 | 给写权限；要覆盖就 `force=true` |
| 想换成自己的安装页 | 不想用内置向导 | 用 `web_installer_view` 或 `web_installer_render_custom_callback` |

## 相关参考

- [DuckPhp\Ext\DuckPhpInstaller](../reference/Ext-DuckPhpInstaller.md)、[DuckPhp\Ext\RouteHookWebInstaller](../reference/Ext-RouteHookWebInstaller.md)
- [DuckPhp\Core\App](../reference/Core-App.md) 的 `checkInstallToPage()`、`installed`、`url_install`
- 第 7 章[上线最小清单](deployment.md)
