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



    public function getCliParameters()
    public function regCommandClass($class, $alias = null)
    public function getCommandListInfo()
    public function app()
    public function run()

## 详解

Console 类是 DuckPhp 的 命令行支持类。
[DuckPhpCommand](Component-DuckPhpCommand.md)


添加自定义命令。

你的类里加 command_$cmd 。 即可
该方法的 doc 第一行就是简介

使用参数
命令行中的 --XX 会成为方法中的 $XX 参数
cmd a b --x c d --y z  --abc-d
=>
command_cmd('a','b');

abc-d 会转成 $abc_d

你可以用
Console::G()->getCliParameters(); 获得参数的值

你的 app 类，还有其他 command_$cmd 会加入或覆盖 默认的 方法。


