# 6 调试、日志与 CLI 初体验

> 解决什么问题：出错时怎么最快看到真相；日志写哪、怎么写；以及两个立刻能用的命令行入口。
> 前置：[第 5 章](configuration.md)。预计 15 分钟。

## 一、先打开调试开关

```php
// 本地入口里临时打开（生产别开）
\MyProj\System\App::RunQuickly(['is_debug' => true]);
```

调试模式的判定是「**设置里的 `duckphp_is_debug` 或根应用的 `is_debug` 选项**」（`App::IsDebug()`），所以本机用设置文件打开也行。打开后：

| 变化 | 说明 |
|---|---|
| 异常页显示堆栈 | 走 `error_debug` 视图（没配就由框架内建渲染：异常类、消息、文件行号、完整调用栈） |
| 开发期警告升级 | `_OnDevErrorHandler` 把 PHP 的 notice/warning 变成可见错误（第 19 章） |
| 404 页附带路由信息 | 会打印 route error（例如「can't Reflection class (…)」这类提示），比干巴巴的 404 好定位 |

错误页的四个选项（都是**视图名**，相对 `path_view`）：

```php
'error_404'      => '_sys/error_404',       // 404
'error_500'      => '_sys/error_500',       // 生产环境的错误页
'error_debug'    => '_sys/error-debug',     // 调试页（与上面两个互斥使用）
'error_maintain' => null,                   // 维护页（is_maintain 或 duckphp_is_maintain 为真时）
```

脚手架 `skeleton/view/_sys/` 里已经放好了 `error_404.php` 与 `error_500.php`，照抄改文案即可。

## 二、日志

日志组件是 `Logger`（PSR-3 风格的八个级别）：

```php
use DuckPhp\Core\Logger;

Logger::_()->info('user {id} login', ['id' => 7]);
Logger::_()->warning('something odd');
Logger::_()->error('db failed: ' . $e->getMessage());
// 等价入口：\DuckPhp\Core\CoreHelper::Logger()->info(...)
```

八个级别：`emergency` / `alert` / `critical` / `error` / `warning` / `notice` / `info` / `debug`；也可以用 `log($level, $message, $context)` 直接指定。

落盘位置由三个选项决定：

| 选项 | 默认 | 说明 |
|---|---|---|
| `path_log` | `'runtime'` | 日志目录（相对**项目根** `path`，也可给绝对路径） |
| `log_file_template` | `'log_%Y-%m-%d_%H_%i.log'` | 文件名模板，`%X` 交给 `date(X)` 展开 |
| `log_prefix` | `'DuckPhpLog'` | 每行前缀 |

于是默认写到 `<项目根>/runtime/log_2026-09-19_10_30.log`。

两个要知道的行为：

- **框架默认会记录未捕获异常**（选项 `default_exception_do_log` 默认 `true`）：`_OnDefaultException()` 里把异常写进日志 —— 线上出 500 时先来 `runtime/` 找日志。
- **写不进去不会炸**：`Logger::log()` 内部 try/catch，失败返回 `false`。所以看到「日志目录不可写」时页面是正常的，只是日志没了（第 7 章的上线清单要检查 `runtime/` 可写）。

## 三、CLI 初体验：两个入口

框架自带的命令入口 `bin/cli.php`（与 Web 入口共用一套代码）：

```bash
php bin/cli.php help        # 列出全部命令
php bin/cli.php version     # 版本
php bin/cli.php routes      # 列出所有路由（含来源标记：controller / route_map / rewrite_map）
php bin/cli.php run         # 用内置 HTTP 服务把应用跑起来（默认 127.0.0.1:8080）
php bin/cli.php debug       # 开关调试标记（需要 data_file 能力，见下）
```

安装器入口 `vendor/bin/duckphp`（只有三个命令）：

```bash
php vendor/bin/duckphp new      # 用 skeleton/ 生成新工程
php vendor/bin/duckphp show     # 看当前安装情况
php vendor/bin/duckphp help
```

自定义命令就是在控制器层/命令类里写 `command_xxx()` 方法（第 23 章）；子应用的命令要加相位前缀（`php bin/cli.php shop-help`）。

> `debug` 命令会去改写「额外选项文件」（`ExtOptionsLoader`，文件 `DuckPhpApps.config.php`），所以需要先开 `data_file_enable`，并把 `is_debug` 放进 `data_file_bump_keys`；否则它只会提示你去配。

## 四、出问题时的四板斧

```php
// 1) 现在有哪些单例、在哪个相位（ZAllDemo 的首页视图就是这么展示的）
\DuckPhp\Core\PhaseContainer::Dump();          // 或 PhaseContainer::_()->dumpAllObject()

// 2) 现在有哪些路由（排查 404 的第一站）
print_r(\DuckPhp\Component\RouteLister::_()->listAll());

// 3) 当前请求走到了哪个控制器/方法/路径
\DuckPhp\Core\Route::_()->getRouteCallingClass();
\DuckPhp\Core\Route::_()->getRouteCallingMethod();
\DuckPhp\Core\Route::_()->getRouteCallingPath();

// 4) 生效的选项与实际设置
var_dump(\DuckPhp\Core\App::_()->options);
var_dump(\DuckPhp\Core\App::_Setting());
```

配合 `php bin/cli.php routes` 与日志，绝大多数「页面 404 / 500 / 行为不对」都能在几分钟内定位。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `runtime/` 里没有日志 | 目录不可写；或 `path_log` 改过 | `chmod` 给写权限；确认 `App::_()->options['path_log']` |
| 页面报错但看不到堆栈 | `is_debug` 没开，走的是 `error_500` | 临时 `is_debug=true`；或改 `error_500` 视图 |
| 自定义错误页不生效 | 选项里写的是**文件路径**而不是视图名 | 写相对 `view/` 的视图名，如 `'_sys/error_404'` |
| `php bin/cli.php run` 打不开 | 端口被占 / 文档根不对 | 换端口 `--port=9000`；文档根由 `path_document` 决定（默认 `public`） |
| CLI 里命令找不到 | 命令属于某个子应用 | 加相位前缀（`shop-help`），见第 23/26 章 |
| 调试页显示的信息太多，想给用户看简洁版 | 生产应关 `is_debug` 并配 `error_500` | 见第 7 章上线清单 |

## 下一步

- [第 7 章 上线最小清单](deployment.md)：把调试开关关回去，并检查该检查的。
- [第 19 章 异常与错误处理](exception.md)：异常分层与自定义处理。
- [第 23 章 命令行与定时任务](cli.md)：命令类、参数解析、crontab。
