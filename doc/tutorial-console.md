# 命令行模式
## 相关类
DuckPhp\Ext\Console
## 相关选项
'cli_enable' => true,

    启用命令行模式
'cli_mode' => 'replace',    

    命令行模式是通过路由钩子或者是替换默认运行入口实现 (v1.2.8  只实现了后者)
'cli_command_alias'=>[],

    命令和类的别名 ， 键为别名， 值为类名。
## 说明

DuckPhp 的命令行模式是通过 DuckPhp\\Ext\\Console 这个扩展实现的

## 入口差异
```
vendor/bin/duckphp
duckphp-project
php duckphp-project
```
vendor/bin/duckphp 是默认的 duckphp 类

dukphp-project 是使用 public/index.php！


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
list
new     创建工程
run
test
version


添加自定义命令。

你的类里加 command_$cmd 。 即可
该方法的 doc 第一行就是简介

使用参数
命令行中的 --XX 会成为方法中的 $XX 参数
cmd a b --x c d --y z =>
command_cmd('a','b');