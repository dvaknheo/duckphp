# DuckPhp\HttpServer\HttpServerInterface

HTTP 服务器接口。

## 简介

`HttpServerInterface` 定义了 DuckPhp HTTP 服务器组件应实现的基本契约。实现类需要支持快速启动、运行、获取进程 ID 和关闭服务器等能力。

## 选项

无。

## 使用方式

### 实现接口

自定义 HTTP 服务器类可实现该接口：

```php
use DuckPhp\HttpServer\HttpServerInterface;

class MyHttpServer implements HttpServerInterface
{
    public static function RunQuickly($options)
    {
        // ...
    }

    public function run()
    {
        // ...
    }

    public function getPid()
    {
        // ...
    }

    public function close()
    {
        // ...
    }
}
```

### 作为依赖类型

该接口可用于类型提示，便于替换不同的 HTTP 服务器实现。

## 配置示例

无。

## 注意事项

1. 接口本身不定义选项，具体选项由实现类决定。
2. `RunQuickly` 是静态方法，通常用于一次性快速启动服务器。
3. 实现类应保证 `run()` 方法能够真正启动服务或进入服务循环。

## 方法列表

### 公共方法

    public static function RunQuickly($options)
快速启动服务器。接收配置数组并返回运行结果。

    public function run()
运行服务器。启动 HTTP 服务或进入服务循环。

    public function getPid()
获取服务器进程 ID。

    public function close()
关闭服务器。

## 相关链接

- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md)
