# DuckPhp\Ext\DefaultCommand
[toc]
## 简介
`组件类` 完成DuckPhp 的命令行功能
## 选项
    无选项
## 公开方法

### 组件方法

### 其他方法

## 详解

Console 类是 DuckPhp 的 命令行支持类。

入口

vendor/bin/duckphp
duckphp-project
php duckphp-project

入口差异

vendor/bin/duckphp 是默认的 duckphp 类
dukphp-project 是使用 public/index.php 里的配置。


创建工程的时候，因为还没有 duckphp-project，
你需要使用
vendor/bin/duckphp new 

启动服务器， 启动服务器两者都可以。
duckphp-project run

将来需要 swoole/workerman 启动的时候
只能使用 duckphp-project 入口了。


默认命令

```
call    调用一个类的方法
depoly  部署脚本，未override前只是提示
fetch   模拟一个 url 请求
help    详细介绍    
install 安装
list    显示方法列表
new     创建工程
run     运行 server （v1.2.8 只运行默认的httpserver。以后改进可以使用其他 httpserer
test    未override前只是提示
version 显示版本号码
```
