# DuckPhp\HttpServer\HttpServerInterface

## 简介

`HttpServerInterface` 是 DuckPHP 内置 HTTP 服务器启动器的契约接口，规定实现方必须提供：快捷启动（`RunQuickly`）、启动运行（`run()`）、取 PID（`getPid()`）、关闭（`close()`）。框架的 `HttpServer` 即按此接口实现。

## 类信息

- 命名空间：`DuckPhp\HttpServer`
- 声明：`interface HttpServerInterface`

## 使用方式

一般直接使用 `HttpServer::RunQuickly($options)` 即可；需要自定义启动器时实现本接口，并保持“`RunQuickly($options)` 一次性启动”的用法一致。

## 方法列表

### 公共方法

    public static function RunQuickly($options)
快捷启动：按 `$options` 初始化并开始运行（约定返回后服务器已启动或已派发到系统命令）。

    public function run()
启动/运行服务器主流程。

    public function getPid()
取后台运行进程 PID。

    public function close()
结束/关闭服务器进程。

## 相关链接

- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) — 本接口的默认实现
