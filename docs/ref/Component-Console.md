# DuckPhp\Component\Console
[toc]
## 简介
`组件类` 完成命令行功能

## 选项
全部选项

        'cli_enable' => true,
命令行,启用命令行扩展

        'cli_mode' => 'replace',
命令行,模式，替换模式或者是路由钩子的模式

        'cli_command_alias' => [],
命令行,类别名列表

        'cli_default_command_class' => '',
命令行,默认类

        'cli_command_method_prefix' => 'command_',
命令行,默认方法前缀

        'cli_command_default' => 'help',
命令行,默认调用指令

## 方法

    public static function G($object = null)
    public function __construct()
    public function isInited(): bool
    public function init(array $options, ?object $context = null)
以上是常规流程方法

    public function run()
运行

    public function app()
获得调用的 $context 


    public function getCliParameters()
**重要**，获得 命令行参数

    public function regCommandClass($class, $alias = null)
注册某类为命令

    public static function DoRun($path_info = '')
传入path_info 运行，作为路由钩子的回调

    public function callObject($class, $method, $args, $input)
调用类方法

    public function getCommandListInfo()
得到可用命令列表


    protected function parseCliArgs($argv)
    
    protected function getClassAndMethod($cmd)
    
    protected function getCommandsByClass($class)
    
    protected function getCommandGroupInfo()

## 详解

Console 类是 DuckPhp 的 命令行支持类。
[DuckPhpCommand](Component-DuckPhpCommand.md)


添加自定义命令。

你的类里加 command_$cmd 。 即可
该方法的 phpdoc 第一行就是简介

使用参数
命令行中的 --XX 会成为方法中的 $XX 参数
cmd a b --x c d --y z  --abc-d
=>
command_cmd('a','b');

abc-d 会转成 $abc_d

你可以用
Console::G()->getCliParameters(); 获得参数的值

你的 app 类，还有其他 command_$cmd 会加入或覆盖 默认的 方法。

如果你要注册额外的 类 方法 ，使用 regCommandClass()

getCommandListInfo() 会把他们展示出来







