# 2-16 命令行与定时任务

> 解决什么问题：怎么给应用加命令、内置命令有哪些、命令怎么和「相位/子应用」对上号、以及怎么把它挂进 crontab。
> 前置：[第 2-2 章 请求生命周期](lifecycle.md)（`execute()` 那条支线）。预计 20 分钟。
> 示例：`demo/cli.php`（工程 CLI 入口）+ `demo/src/System/App.php` 的 `command_hello()`。可直接跑：

```bash
php demo/cli.php help                    # 列出所有命令（含子应用那一组）
php demo/cli.php routes                  # 打印路由表
php demo/cli.php DbTestApp:version       # 带相位前缀调用子应用的命令
```

## 最小示例

工程 CLI 入口就是「和 Web 入口一样，换个文件」（`demo/cli.php` 的真实结构）：

```php
require __DIR__ . '/../vendor/autoload.php';

$options = [
    //'is_debug' => true,
];
\ProjectNameTemplate\System\App::RunQuickly($options);
```

命令写在类里（方法名 `command_` 前缀），并在 `onInited()` 里注册：

```php
class App extends DuckPhp
{
    protected function onInited(): void
    {
        parent::onInited();
        $this->regConsoleCommand(MyCommands::class);      // 注册命令类
    }
}

class MyCommands
{
    /** @command_desc 打个招呼 */
    public function command_hi($name = 'world')
    {
        $args = \DuckPhp\Core\Console::_()->getCliParameters();
        echo "HI {$name} ; n=" . ($args['n'] ?? '-') . PHP_EOL;
    }
}
```

```bash
$ php cli.php hi Duck --n=3
HI Duck ; n=3
$ php cli.php help
  hi                  say hi to someone
```

（这段是**实测过**的：`regConsoleCommand()` + `command_xxx()` + `@command_desc` 就会出现在 help 里。）

## 机制说明

### 1. CLI 与 Web 是同一套应用，只是走 `execute()`

```
RunQuickly() → init() → 分流：
   PHP_SAPI === 'cli' && cli_enable  → execute() → Console::_()->run()
   否则                              → serve()   → 路由
```

所以 CLI 下**初始化一样完整**：`onPrepare()`/`onInit()`/`onInited()` 都会跑（[第 2-2 章](lifecycle.md)），设置、组件、单例容器都在。区别只在最后交给 [`Console`](../reference/Core-Console.md) 而不是 [`Route`](../reference/Core-Route.md)。

- 想让 CLI 也走 Web 流程（在命令行里「请求」一个路径）：`'cli_enable' => false`；
- 内置 `run` 命令反过来：它把 `cli_enable` 置回 `false` 再起内置 HTTP 服务器（[`Command::command_run()`](../reference/Component-Command.md)）。

### 2. 命令从哪来

| 来源 | 写法 | 说明 |
|---|---|---|
| 框架内置 | `cli_command_with_common => true`（默认） | 合并 `DuckPhp\Component\Command`，提供下表 7 个命令 |
| 你自己的类 | `$this->regConsoleCommand(MyCommands::class)` | 类里方法名以 `command_` 开头（前缀可改） |
| 选项一次性声明 | `'cmd' => [MyCommands::class => 'command_']` | `regConsoleCommand()` 内部就是往这个选项里塞 |

内置命令（`src/Component/Command.php`，`php demo/cli.php help` 实测可见）：

| 命令        | 作用                                                  |
| --------- | --------------------------------------------------- |
| `version` | 打印 `(应用类)版本号`                                       |
| `help`    | 打印命令清单（按命令组分组）                                      |

| `run`     | 起内置 HTTP 服务器                                        |
| `fetch`   | 在命令行里抓一个 URL（`--uri=…`、`--post=…`）                  |
| `call`    | 直接调方法：`namespace/class@method arg1 --k=v`           |
| `debug`   | 开关调试模式（`debug off`）                                 |
> **`routes` 命令不在上面这个类里**：它实现于 [`DuckPhp\Ext\RouteLister::command_routes()`](../reference/Ext-RouteLister.md)（源码 `src/Ext/RouteLister.php` 第 27 行），属于 `Ext\*` 扩展，**不会自动装配**（见[第 2-3 章 路由钩子](route-hooks.md)）。要用就把它登记进应用的 `cmd`：

```php
// src/System/App.php
public $options = [
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = 用默认方法前缀 command_
    ],
];
```

之后 `php cli.php routes --with_children=0 --only_admin=1` 就能用了（参数见参考页）；`cmd` 的值也可以写成前缀字符串（如 `'command_'`），写 `false` 或删掉就是关闭。
**框架自带的 `bin/duckphp`** 是另一回事：它是**安装器 CLI**，只有 `new`/`help`/`show` 三个命令（`src/Ext/DuckPhpInstaller.php`），用来建新项目，不提供上面那套常用命令。

### 3. 命令名、参数与相位前缀

命令名的构成是 `<命令组前缀>:<命令>`：

```bash
php demo/cli.php version              # 根应用的 version
php demo/cli.php DbTestApp:version    # 子应用的 version —— 实测输出 (DbTestApp)1.4.1
```

子应用会有自己的一组内置命令（`help` 里 `DbTestApp:` 那一组就是），命令组前缀默认由相位名推出（`getThisCommandPrefix()` 把相位名里的 `/` 换成 `-`）；也可以用 `Console::_()->regCommmandPrefixPhase($prefix, $phase)` 自定义「前缀 → 相位」映射。

参数解析（`Console::_()->getCliParameters()`，实测）：

```php
// php cli.php hi Duck --n=3
[
    '--' => ['hi', 'Duck'],   // 位置参数（命令名也在里面）
    'n'  => '3',              // --key=value → 键名不带 --
]
```

所以**选项键名不带前导 `--`**；位置参数用 `Console::_()->getArgs()` 或 `getCliParameters()['--']` 取。

### 4. 命令的描述与元信息

```php
/** @command_desc 打个招呼 */      // 文档注释，help 里显示
public function command_hi($name = 'world') { … }
```

或者集中管理清单：

```php
class MyCommands implements \DuckPhp\Component\CommandMetaInterface
{
    public function __commandMeta(): array
    {
        return ['hi' => '打个招呼', 'clean' => '清理临时文件'];
    }
}
```

没实现 [`CommandMetaInterface`](../reference/Component-CommandMetaInterface.md) 时，框架用反射读 `command_` 方法 + `@command_desc`（参考页）。

### 5. 定时任务：crontab 里就是普通命令

```cron
* * * * * cd /srv/myproj && /usr/bin/php cli.php clean >> runtime/cron.log 2>&1
```

三个实践要点：

1. **`cd` 到项目目录**（或显式传 `'path' => '/srv/myproj/'`），否则 `path` 推导不到；
2. **用绝对路径的 php**（crontab 的 PATH 很干净）；
3. **日志重定向到 `path_runtime` 下**并配轮转——`Helper::PathOfRuntime()` 就是取这个目录。

需要「同一时刻只跑一个实例」这类保护，在命令开头自己抢锁（文件锁 / Redis 锁）：框架**不提供任务调度器**，调度交给 crontab / systemd timer。

## 常见写法

**① 一个领域一个命令类**

```php
namespace MyProj\Controller;

class NoteCommands
{
    /** @command_desc 重建便签搜索索引 */
    public function command_reindex()
    {
        $n = NoteBusiness::_()->reindexAll();
        echo "reindexed: {$n}\n";
    }
}
```

（`demo/src/Controller/Commands.php` 是框架留的样板，但它的 `use DuckPhp\Foundation\CommonCommandTrait;` 在当前版本里**已经失效**——那个 trait 不存在，别照抄那一行。）

**② 命令里复用业务层，别在命令里写 SQL**

```php
public function command_report($date = null)
{
    $rows = ReportBusiness::_()->daily($date ?? date('Y-m-d'));   // 与 Web 共用一套业务
    foreach ($rows as $r) { echo "{$r['name']}\t{$r['total']}\n"; }
}
```

**③ 强制某个前缀跑在某个相位**

```php
Console::_()->regCommmandPrefixPhase('admin', 'admin');   // admin-xxx → 切到 admin 相位
```

**④ 临时调个方法：用 `call`**

```bash
php cli.php call MyProj/Controller/NoteCommands@reindex --verbose=1
```

**⑤ 长任务注意收尾**

命令行下 `echo` 即时可见；开了 `use_output_buffer`（[第 2-2 章](lifecycle.md)）时更要用 `Helper::exit()` 而不是 `exit`，保证收尾逻辑一致。

## 常见错误

| 现象 | 原因 | 改法 |
|---|---|---|
| `(xxx)Command Not Found In All` | 命令没注册，或方法名没有 `command_` 前缀 | `regConsoleCommand()` 注册；方法名用 `command_`（或注册时指定前缀） |
| 命令方法写在 [App](../reference/Core-App.md) 类里却找不到 | 应用类自己的 `command_` 方法**不会**自动注册 | 在 `onInited()` 里 `$this->regConsoleCommand(static::class)`，或单独建命令类 |
| `--key=value` 取不到 | 选项键名不带 `--` | 用 `$params['key']`（`$params['--']` 是位置参数） |
| 子应用命令调不到 | 少了命令组前缀 | 用 `子应用名:命令`，或自定义前缀映射 |
| crontab 里报找不到项目 | 没 `cd` 到项目目录，`path` 推导失败 | `cd /srv/myproj && php cli.php …`，或显式传 `'path'` |
| `@command_desc` 在 help 里带了 `*/` | 描述取到行尾，把注释结束符也带上了 | 描述单独一行，`*/` 放下一行 |
| 命令里读 `$_GET` 拿不到东西 | CLI 下没有 Web 请求 | 参数用 `getCliParameters()`；要「请求」就用 `fetch` 命令 |
| 以为 `bin/duckphp run` 能起服务器 | `bin/duckphp` 是安装器 CLI（`new`/`help`/`show`） | 起服务器用工程 CLI 的 `run` 命令（`php cli.php run`） |

## 下一步

- [第 2-17 章 测试](testing.md)：命令行也是「不起服务器就能测业务」的入口。
- [第 3-1 章 应用树与相位基础](advanced-phase.md)：命令组前缀背后的相位机制。
- [第 3-6 章 安装器与 Web 安装流程](installer.md)：`bin/duckphp new` 与 Web 安装页。
- 参考手册：[DuckPhp\Component\Command](../reference/Component-Command.md)、[DuckPhp\Component\CommandMetaInterface](../reference/Component-CommandMetaInterface.md)、[DuckPhp\Core\Console](../reference/Core-Console.md)。
