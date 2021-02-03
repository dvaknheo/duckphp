# DuckPhp\Component\Console
[toc]
## 简介
`组件类` 完成命令行功能
## 选项

'cli_enable' => true,

    开启本扩展
'cli_mode' => 'replace',

    替换模式或者是路由钩子的模式
'cli_command_alias' => [],

    命令别名列表
'cli_default_command_class' => '',

    默认类
'cli_command_method_prefix' => 'command_',

    默认方法前缀
'cli_command_default' => 'help',

    默认调用指令
## 公开方法


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

添加自定义命令。

你的类里加 command_$cmd 。 即可
该方法的 doc 第一行就是简介

使用参数
命令行中的 --XX 会成为方法中的 $XX 参数
cmd a b --x c d --y z 
=>
command_cmd('a','b');

你可以用
Console::G()->getCliParameters(); 获得参数的值

你的 app 类，还有其他 command_$cmd 会加入或覆盖 默认的 方法。