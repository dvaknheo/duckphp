# 4-5 常驻进程与内嵌 HTTP

> 解决什么问题：框架自带的 HTTP 服务器（`DuckPhp\HttpServer\HttpServer`）怎么起、怎么停、为什么它能跑通 RPC 回环；以及"一个进程处理多个请求"时哪些状态会残留。
> 前置：[第 2-10 章 请求生命周期与钩子点](lifecycle.md)、[第 4-6 章 多入口·多域名·多 SAPI](multi-entry.md)。预计 20 分钟。
> 示例：`tests/data_for_tests/ZAllDemoTest.config.php`（真起服务器 + curl 各入口的冒烟测试）、`demo/public/rpc.php`（多 worker 才能跑的回环示例）。

```bash
# ① 用项目 CLI 起（内部调 HttpServer）
php cli.php run
# ② 直接调 HttpServer（等价，参数更直观）
php -r 'require "autoload.php"; \DuckPhp\HttpServer\HttpServer::RunQuickly(["path"=>__DIR__."/demo/","port"=>8080,"path_document"=>"public"]);'
```

## 最小示例

仓库里真实存在的用法（`tests/data_for_tests/ZAllDemoTest.config.php`，测试会照着它起服务器）：

```php
'server_options' => [
    'path'          => realpath(__DIR__ . '/../../demo/') . '/',
    'path_document' => 'public',      // 文档根（相对 path）
    'port'          => 9802,
    'background'    => true,          // 后台起，便于测试随后 curl
    'workers'       => 4,             // PHP 内置服务器多进程（7.4+）
],
```

```php
HttpServer::RunQuickly($server_options);
sleep(1);                              // 丑但实用：等它起来
$data = file_get_contents('http://127.0.0.1:9802/demo.php');
HttpServer::_()->close();              // 收工，别留孤儿进程
```

## 机制说明

### 1. 它其实就是 `php -S`

`HttpServer::runHttpServer()` 拼出来的命令（可用 `--dry` 打印而不执行）：

```
PHP_CLI_SERVER_WORKERS=<workers> php -S <host>:<port> -t <docroot>
```

- `workers` 有值时才加 `PHP_CLI_SERVER_WORKERS`——这是 PHP 7.4+ 内置服务器的多进程模式，**RPC 回环（服务器自己请求自己）必须靠它**（`demo/public/rpc.php` 就是这个场景）；
- `background` 为真时命令尾部加 `> /dev/null 2>&1 & echo $!;`，把 PID 记到 `getPid()`；
- **Windows 走另一条分支**（`proc_open` 数组形式，不经 shell），因为 `& echo $!` 在 cmd.exe 下会输出伪 PID。

### 2. 选项与 CLI 短参数

| 选项 | 默认 | 说明 |
|---|---|---|
| `host` | `127.0.0.1` | 监听地址（对外暴露要显式改，注意安全） |
| `port` | `8080` | 端口 |
| `path` | `''` | 项目根 |
| `path_document` | `public` | 文档根（相对 `path`） |
| `workers` | `null` | 多进程数；`null`/`0` 表示单进程 |
| `background` | `false` | **隐藏选项**：后台运行（也可用 `-b/--background` 触发） |

同时支持命令行参数：`-H/--host`、`-P/--port`、`-t/--docroot`、`-b/--background`、`--dry`（只打印命令）、`-h/--help`。命令行参数的优先级高于选项。

```bash
php cli.php run -H 0.0.0.0 -P 9000 -t public --dry     # 先看看它要执行什么
```

### 3. 起停与生命周期

| 方法 | 作用 |
|---|---|
| `HttpServer::RunQuickly($options)` | `init($options)->run()` |
| `HttpServer::_()->getPid()` | 后台模式下的子进程 PID |
| `HttpServer::_()->close()` | 结束：POSIX 用 `posix_kill($pid, 9)`；Windows 用 `proc_terminate`/`taskkill /T` |
| `HttpServer::_()->isInited()` | 初始化状态 |

`close()` 返回 `false` 说明**当前进程里没有可关的服务器**（比如 `background` 没开、或 PID 已在别处被清掉）。

### 4. 与 CLI 的关系：`php cli.php run`

内置命令 `run`（[`Command::command_run()`](../reference/Component-Command.md)）做的事就是：

1. 取 CLI 参数当服务器选项（还会把当前应用类塞进 `http_app_class`）；
2. 如果参数里指定了别的 `http_server` 类，就换掉 `HttpServer` 单例；
3. 临时把 `cli_enable` 置为 `false`（让子进程按 Web 模式跑），起服务器，起完再置回。

所以"常驻"在框架里的含义是：**内置服务器进程 + 每请求一次完整的 `RunQuickly` 流程**。它不是 php-fpm 那种 worker 池，也没有跨请求的对象复用。

### 5. 一个进程处理多请求时，什么会残留

内置服务器在多 worker 模式下每个请求仍是独立的 PHP 进程（`php -S` 的模型），所以**框架单例不会跨请求存活**。真正需要注意残留的是这两种情况：

- **你自己写的常驻脚本**（比如 while 循环里反复 `RunQuickly()`，或把框架嵌进别的常驻进程）：单例、[`Runtime`](../reference/Core-Runtime.md) 状态、数据库连接都会留着。要清就显式来做：[`Route::_()->clear()`](../reference/Core-Route.md)、`Runtime::_()->clear()`、[`PhaseContainer::_()->RestAllContainerForTesting()`](../reference/Core-PhaseContainer.md)（[第 4-1 章](container-phases.md)）；
- **开了输出缓冲**（`use_output_buffer = true`）：`Runtime` 会 `ob_start()`，请求结束必须走到 `Runtime::_()->clear()` 才把缓冲刷出去；长跑脚本里别忘了这一步，否则响应会"攒着不发"。

几个相关的运行期查询：

```php
Helper::isRunning();        // Runtime 是否处于"正在处理请求"
Helper::isInException();    // 是否在异常处理中
Runtime::_()->isOutputed(); // 是否已经输出过
```

### 6. 生产别用它

内置服务器是**单进程（或少量 worker）的玩具服务器**：没有进程管理、没有超时/限流、不支持 HTTPS、并发能力有限。生产用 php-fpm + nginx（[第 1-7 章](deployment.md)、[第 2-17 章](security-performance.md)）。需要常驻高性能方案（RoadRunner/Swoole/FrankenPHP）时，框架**不内置**，需要你自己把 `RunQuickly` 接进它们的生命周期，并特别注意上一条的"状态残留"。

## 常见写法

**① 开发起服务器**

```bash
php cli.php run -H 127.0.0.1 -P 8080
```

**② 测试里后台起 + 断言 + 关闭**

```php
HttpServer::RunQuickly(['path' => $app_path, 'port' => 9802, 'background' => true, 'workers' => 4]);
try {
    $body = file_get_contents('http://127.0.0.1:9802/demo.php');
    $this->assertStringContainsString('Hello', $body);
} finally {
    HttpServer::_()->close();
}
```

**③ 起之前先看看要跑什么命令**

```bash
php cli.php run --dry
```

**④ 需要"服务器请求自己"（RPC/内部 API）时开 workers**

```php
['workers' => 4]     // 没有多 worker，回环请求会死等
```

**⑤ 让某个入口换用别的 [HttpServer](../reference/HttpServer-HttpServer.md) 实现**

```bash
php cli.php run --http_server=MyProj/Http/MyServer
```

（`command_run` 会把 `http_server` 参数解析成类名并替换单例。）

## 常见错误

| 现象                         | 原因                                             | 改法                                                            |
| -------------------------- | ---------------------------------------------- | ------------------------------------------------------------- |
| 端口被占用 / 起不来                | 上一个后台服务器没关掉                                    | `HttpServer::_()->close()`；或换端口；`ps aux \| grep "php -S"` 找残留 |
| 测试偶发失败、报连接拒绝               | `background` 起了但没等它 ready                      | 起完 `sleep(1)`（仓库现状就是这么做的），或重试探测端口                             |
| RPC 示例卡住/超时                | 没开 `workers`                                   | 设 `workers`（≥2）                                               |
| 后台模式没有任何输出                 | 输出被重定向到 `/dev/null`                            | 调试时用前台模式或 `--dry`                                             |
| `close()` 返回 `false`       | 当前进程没有 PID 可关                                  | 确认是在**起服务器的那个进程**里 close；别跨进程关                                |
| Windows 下起服务器报"系统找不到指定的路径" | 早先用 shell 起，cmd.exe 无法解析 `& echo $!`           | 现版本 Windows 走 `proc_open` 分支；升级到当前代码即可                        |
| 长跑脚本里响应"攒着不发"              | 开了 `use_output_buffer` 但没走到 `Runtime::clear()` | 保证请求收尾逻辑（`Route::clear()`/`Runtime::clear()`）被执行              |
| 把内置服务器放到生产                 | 它不是生产级服务器                                      | 上 php-fpm/nginx（[第 1-7 章](deployment.md)）                       |

## 下一步

- [第 4-6 章 多入口·多域名·多 SAPI](multi-entry.md)：同一个代码库的多种入口。
- [第 4-7 章 测试基建与覆盖率流水线](coverage.md)：起服务器做端到端冒烟的那套流程。
- [第 4-9 章 性能调优与排错手册](troubleshooting.md)：端口/进程类问题的排查路径。
- 参考手册：[DuckPhp\HttpServer\HttpServer](../reference/HttpServer-HttpServer.md)、[DuckPhp\Component\Command](../reference/Component-Command.md)、[DuckPhp\Core\Runtime](../reference/Core-Runtime.md)。
