# DuckPhp\Core\Console

命令行（Console）执行 / 命令路由核心：把 CLI 的 argv 转成命令调用与直接扫描/运行。

## 简介

`Console` 是 DuckPHP 对 CLI 的命令处理根：CLI 入口（Kernel 的 `execute()`）会让它从 `$_SERVER['argv']` 读参数、解析出“命令名 + 选项名值”，再从一组“命名空间 → 命令类”里找到要调用的类与方法，实例化并反射调用（支持按方法参数名自动传值 + 缺省参数/报错）。

它同时提供：

- 命令按命名空间组织：`ns:sub:command` 用冒号分隔，`getThisCommandPrefix()`（Core 级别 Phase 名规范化命令组）/各类 help 由 `DuckPhp\Component\Command` 展开；
- 注册 API：`regCommandClasses / regCommandClassSingle / DoRun`；
- 参数读取：`getArgs()`/`getCliParameters()` 与 readLines 交互式提示输入；
- 结构无关：不做 `execute/exit` 自身，而由宿主做异常 → 见 Kernel.runException（Console 出错就抛 `DuckPhpSystemException`…）。

## 类信息

- 命名空间：`DuckPhp\Core`
- 声明：`class Console extends ComponentBase`
- 继承链可用 `Console::_()`/`RunQuickly`（若单件使用），init 幂等常规默认同 app。

## 选项

`Console::$options`

| 选项 | 默认值 | 说明 |
|---|---|---|
| `console_command_classes` | `[]` | 命令注册表：`namespace => [ className => 方法前缀 ]`。前缀 `true` 会解释为 `command_`。 |
| `console_command_phase` | `[]` | `namespace => phase` 的映射，运行某命名空间命令前先切到对应 Phase。 |
| `console_command_default` | `'help'` | 无尾部位置参数时（仍给 `--` 一个命令串）用作默认命令词。 |
| `console_readlines_logfile` | `''` | 若给路径：`readLines` 在每次输入回显后把它写入该文件（相对 `path_runtime`）；相对实际 `path_runtime`。 |

（`context_class` 等受保护选项不需覆盖。）

## 使用方式

框架 CLI 场景：业务通常不自己碰 `Console` 底层，CLI 命令最自然照下面雏形：

```php
class DemoApp extends \DuckPhp\DuckPhp { }
\DemoApp::_()->init([]);
\Admin\ConsoleCommand 类等放 app 的 cmd ...

// 骨架化 —— 实际经由运行，Controller/D 用户自己命令类形如：
class MyCmd {
    public function command_hello() { echo "hello\n"; }
}

// 注册 & 运行命令“hello”：
use DuckPhp\Core\Console;
$c = Console::_()->init([]);
$c->regCommandClassSingle('', MyCmd::class, true);   // true => command_
$c->run();   // 通常带 argv（如 `php x.php hello …`）
```

## 配置示例

以较典型用法，在 App 将命令类登记给当前 namespace:

```php
// 在 MyApp::regConsoleCommand … 或直接：
Console::_()->regCommandClasses('sub', [
    StatusCmd::class => 'command_',     // 为该 phase 名字的前缀
]);
// 命令 “sub:ping” 会尝试 StatusCmd->command_ping()…
```

CLI 如何被入口接到决定略：见到 KernelTrait execute() 即 `Console::_()->run()`。

## readLines（交互）

`readLines` 依据多行 `$desc` 中 `{key}` 占位 + 末尾默认值输出提示 / 读一行，返回数组。它是“脚手架 new 时的问答式向导”通用地基。

```php
$answers = Console::_()->readLines(
    ['db' => 'mysql', 'name' => 'x'],
    "{db} 打数据库类型  \n{name} 打 名称:"
);
```

`readLinesFill/readLinesCleanFill` 用于测试注入（把字符串当 stdin 喂）、`console_readlines_logfile` 可记录。

## 注意事项

1. parser 规则（`parseCliArgs`）：`--option[=value]` 采用下划线化键；`--flag` 无值会记 `true`；重复传值会聚成数组；`--`（位置）值收集进 `$ret['--']`，若无则填 `console_command_default`。
2. `run()` 先用 `splitCommand` 拆冒号 → 命名空间/尾部命令；`getCommandCallback` 返回首个 存在方法的前缀=真，否则没有命令会抛 `DuckPhpSystemException(…Command Not Found…)`。
3. phase 自动跳：`console_command_phase` 找到的话先 `App::Phase(predetermined)` 执行后恢复。
4. 命令方法可能反射用参数名绑值（`callObject` 检查入参名、默认值等；缺失必填抛异常）。
5. `DoRun($path)` 只是 `run()` 的一静态别名；`app()` 见 context。
6. 该文件不含对外 exit；跨 phase 后的异常交给上游。

## 全部选项

```php
    public $options = [
        'console_command_classes' => [],
        'console_command_phase' => [],
        'console_command_default' => 'help',
        'console_readlines_logfile' => '',
    ];
```

## 方法列表

> `__construct` 继承自 ComponentBase（不在此重复）;以下仅为 Console 源码方法（含 protected 工具）.

### 公共方法

    public function init(array $options, ?object $context = null)
选项白名单合并（沿用 component 语义）；设 context_class；标记 is_inited。

    public function getCliParameters()
返回绑定参数（无则解析一遍 argv）──包含命名参数与 `--` 位置数组。

    public function getArgs()
只返回位置参数（`$ret['--']` 数组）。

    public function app()
返回所属应用（同 `context()`，语义常用）。

    public function regCommmandPrefixPhase($prefix, $phase)
登记命令前缀 → Phase 映射（执行该 namespace 前切 Phase）。

    public function regCommandClasses($prefix, array $classes)
合并给定前缀的命令类映射（新增/替换）；classes 形如 `[X::class => 方法前缀]`。

    public function regCommandClassSingle(string $prefix, string $class, $method_prefix)
精确登记单条命令（prefix 名,class,method_prefix——传 `true`→‘command_’）。

    public static function DoRun($path_info = '')
一次性 wrapper：调 run()。$path 为惯例占位。

    public function run()
读取 argv →parseCli →取命令 → splitCommand→getCommandCallback；查 namespace →切 phase →callObject;错误/没找到则抛异常。

    public function getCommandCallback($cmd)
给定命令串返回 `[class,method]` 或 `[null,null]`：查前缀 classes；（按后注册先？）用方法命中返回。

### 运行/注册辅助

    public function readLinesFill($data)
把 $data 直接视为待读内容附加（供测试 / 交互脚本模式）。

    public function readLinesCleanFill()
清空数据，让 readLines 回到普通 stdin 模式。

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
按描述符逐行 Prompt 输入：支撑 {key} 占位默认值、校验器(filter_var_array)、读日志与 echo 回显。

    public function callObject($class, $method, $args, $input)
用反射实例化/取 command 对象并同方法参数名从 input/arg 填绑定调用；缺失必填抛 DuckPhpSystemException(-2)。

### 受保护方法

    protected function splitCommand($cmd)
把 `ns:cmd` 拆成 `[命名空间, 尾部方法]`。

    protected function parseCliArgs(array $argv): array
状态机解析 argv：--放/单横/等号/value 收集，输出含 `--`,`key…` 与位置数组等结构。

    protected function getObject(string $class): object
优先调用 `class::_()`（若可调用）否则 `new $class`。

## 相关链接

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — `execute()`→`Console::run()` 的宿主
- [DuckPhp\Component\Command](Component-Command.md) — 默认 CLI 例子/help 集合
- [DuckPhp\Core\App](Core-App.md) — 应用及存取 command 在 options
- guide：[cli](../guide/cli.md)
