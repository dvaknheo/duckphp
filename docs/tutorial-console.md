# 命令行模式
[toc]
## 相关类
[DuckPhp\Component\Console](ref/Component-Console.md) 命令行类

[DuckPhp\Component\DuckPhpCommand](ref/Component-DuckPhpCommand.md) 默认的命令行类


## 相关选项
'cli_enable' => true,
    启用命令行模式
'cli_mode' => 'replace',    

    命令行模式是通过路由钩子或者是替换默认运行入口实现 
'cli_command_alias'=>[],

    命令和类的别名 ， 键为类名， 值为别名。
## 说明

DuckPhp 的命令行模式是通过 [DuckPhp\Component\Console](ref/Component-Console.md) 这个扩展实现的

## 入口差异
```
vendor/bin/duckphp
duckphp-project
php duckphp-project
```
vendor/bin/duckphp 是默认的 duckphp 类

`dukphp-project` 是使用 public/index.php ！


创建工程的时候，因为还没有 duckphp-project，
你需要使用
`vendor/bin/duckphp new `

启动服务器， 启动服务器两者都可以。

`duckphp-project run`


### 默认命令

call    调用一个类的方法
depoly  部署脚本（未实现）
fetch   模拟一个 url 请求
help    详细介绍    
install 安装
list    查看有什么命令。
new     创建工程
start     启动服务器
test    启动测试
version 显示版本


### 添加自定义命令。

你的类里加 command_$cmd 。 即可
该方法的 doc 第一行就是简介

使用参数
命令行中的 --XX 会成为方法中的 $XX 参数

duckphp-project a b --x c d --y z =>

App::G()->command_a('a','b',$x=['c','d']);
方法名的 doc 文档将作为提示显示




App 的同名 command_*() 会覆盖 DuckPhp\\Ext\\Console 的 command_*() 被调用

用 getCliParameters() 获得 Parameters

regCliCommandGroup($class, $alias) 则可以注册别名

 用 / 代替 \ 表示类名。 如命令  NS/Class:Method 
对应 \NS\Class::G()->command_Method();
