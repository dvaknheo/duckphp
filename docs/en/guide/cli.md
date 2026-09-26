# 2-16 CLI and Scheduled Tasks

> What this solves: how to add commands to the application, what built-in commands exist, how commands map to "phases/child apps", and how to hook them into crontab.
> Prerequisites: [Chapter 2-2 The Request Lifecycle](lifecycle.md) (the `execute()` branch). About 20 minutes.
> Example: `demo/cli.php` (the project CLI entry) + `command_hello()` in `demo/src/System/App.php`. Runnable directly:

```bash
php demo/cli.php help                    # list all commands (including the child app's group)
php demo/cli.php routes                  # print the route table
php demo/cli.php DbTestApp:version       # call a child app's command with the phase prefix
```

## Minimal example

The project CLI entry is "same as the Web entry, in a different file" (the real structure of `demo/cli.php`):

```php
require __DIR__ . '/../vendor/autoload.php';

$options = [
    //'is_debug' => true,
];
\ProjectNameTemplate\System\App::RunQuickly($options);
```

Commands live in classes (method names carry the `command_` prefix) and are registered in `onInited()`:

```php
class App extends DuckPhp
{
    protected function onInited(): void
    {
        parent::onInited();
        $this->regConsoleCommand(MyCommands::class);      // register a command class
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

(This was **verified by experiment**: `regConsoleCommand()` + `command_xxx()` + `@command_desc` makes it appear in help.)

## How it works

### 1. CLI and Web are the same application, just going through `execute()`

```
RunQuickly() → init() → 分流：
   PHP_SAPI === 'cli' && cli_enable  → execute() → Console::_()->run()
   否则                              → serve()   → 路由
```

So under the CLI **initialization is equally complete**: `onPrepare()`/`onInit()`/`onInited()` all run ([Chapter 2-2](lifecycle.md)); settings, components, and the singleton container are all there. The only difference is that at the end it hands over to [`Console`](../reference/Core-Console.md) instead of [`Route`](../reference/Core-Route.md).

- To make the CLI go through the Web flow ("request" a path on the command line): `'cli_enable' => false`;
- The built-in `run` command does the reverse: it sets `cli_enable` back to `false` and starts the built-in HTTP server ([`Command::command_run()`](../reference/Component-Command.md)).

### 2. Where commands come from

| Source | How | Description |
|---|---|---|
| Framework built-ins | `cli_command_with_common => true` (default) | Merges `DuckPhp\Component\Command`, providing the 7 commands in the table below |
| Your own class | `$this->regConsoleCommand(MyCommands::class)` | Method names in the class start with `command_` (the prefix can be changed) |
| One-shot declaration via option | `'cmd' => [MyCommands::class => 'command_']` | `regConsoleCommand()` internally just stuffs entries into this option |

Built-in commands (`src/Component/Command.php`, verified visible with `php demo/cli.php help`):

| Command | Purpose |
| --------- | --- |
| `version` | Print `(app class)version number` |
| `help` | Print the command list (grouped by command group) |

| `run`     | Start the built-in HTTP server |
| `fetch`   | Fetch a URL on the command line (`--uri=…`, `--post=…`) |
| `call`    | Call a method directly: `namespace/class@method arg1 --k=v` |
| `debug`   | Toggle debug mode (`debug off`) |
> **The `routes` command is not in that class**: it is provided by [`Ext\RouteLister`](../reference/Ext-RouteLister.md) (an `Ext\*` extension, **not auto-assembled**). To use it, register it in the app's `cmd`:

```php
// src/System/App.php
public $options = [
    'cmd' => [
        \DuckPhp\Ext\RouteLister::class => true,   // true = use the default method prefix command_
    ],
];
```

Then `php bin/cli.php routes --with_children=0 --only_admin=1` works (the `cmd` value can also be written as a prefix string like `'command_'`; writing `false` or deleting it turns it off); parameter descriptions and `listAll()` are in [Chapter 4-13](ext-classes.md) §5.

**The framework's own `bin/duckphp`** is another matter: it is the **installer CLI**, with only the three commands `new`/`help`/`show`, used to create new projects ([Chapter 1-2](install.md)); it does not provide the common commands above.

### 3. Command names, parameters, and phase prefixes

A command name is `<command group prefix>:<command>`:

```bash
php demo/cli.php version              # the root app's version
php demo/cli.php DbTestApp:version    # a child app's version — verified output (DbTestApp)1.4.1
```

A child app gets its own set of built-in commands (the `DbTestApp:` group in `help`); the command group prefix is derived from the phase name by default (`getThisCommandPrefix()` replaces `/` in the phase name with `-`); you can also customize the "prefix → phase" mapping with `Console::_()->regCommmandPrefixPhase($prefix, $phase)`.

Parameter parsing (`Console::_()->getCliParameters()`, verified):

```php
// php cli.php hi Duck --n=3
[
    '--' => ['hi', 'Duck'],   // positional parameters (the command name is in there too)
    'n'  => '3',              // --key=value → key name without --
]
```

So **option key names carry no leading `--`**; positional parameters are taken via `Console::_()->getArgs()` or `getCliParameters()['--']`.

### 4. Command descriptions and metadata

```php
/** @command_desc 打个招呼 */      // doc comment, shown in help
public function command_hi($name = 'world') { … }
```

Or manage the list centrally:

```php
class MyCommands implements \DuckPhp\Component\CommandMetaInterface
{
    public function __commandMeta(): array
    {
        return ['hi' => '打个招呼', 'clean' => '清理临时文件'];
    }
}
```

When [`CommandMetaInterface`](../reference/Component-CommandMetaInterface.md) is not implemented, the framework uses reflection to read `command_` methods + `@command_desc` (see the reference page).

### 5. Scheduled tasks: just ordinary commands in crontab

```cron
* * * * * cd /srv/myproj && /usr/bin/php cli.php clean >> runtime/cron.log 2>&1
```

Three practical points:

1. **`cd` to the project directory** (or explicitly pass `'path' => '/srv/myproj/'`), otherwise `path` can't be derived;
2. **Use an absolute-path php** (crontab's PATH is bare);
3. **Redirect logs into `path_runtime`** and configure rotation — `Helper::PathOfRuntime()` is how you get that directory.

For protections like "only one instance at a time", take a lock yourself at the start of the command (file lock / Redis lock): the framework **does not provide a task scheduler**; scheduling is left to crontab / systemd timer.

## Common patterns

**① One domain, one command class**

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

(`demo/src/Controller/Commands.php` is a sample the framework ships, but its `use DuckPhp\Foundation\CommonCommandTrait;` **no longer works** in the current version — that trait doesn't exist; don't copy that line.)

**② Reuse the business layer in commands; don't write SQL in commands**

```php
public function command_report($date = null)
{
    $rows = ReportBusiness::_()->daily($date ?? date('Y-m-d'));   // shares the same business code with Web
    foreach ($rows as $r) { echo "{$r['name']}\t{$r['total']}\n"; }
}
```

**③ Force a prefix to run in a phase**

```php
Console::_()->regCommmandPrefixPhase('admin', 'admin');   // admin-xxx → switch to the admin phase
```

**④ Call a method ad hoc: use `call`**

```bash
php cli.php call MyProj/Controller/NoteCommands@reindex --verbose=1
```

**⑤ Watch the teardown of long tasks**

Under the command line, `echo` is immediately visible; when `use_output_buffer` is on ([Chapter 2-2](lifecycle.md)) it's even more important to use `Helper::exit()` instead of `exit`, so teardown logic stays consistent.

## Common errors

| Symptom | Cause | Fix |
|---|---|---|
| `(xxx)Command Not Found In All` | The command isn't registered, or the method name lacks the `command_` prefix | Register with `regConsoleCommand()`; name methods `command_` (or specify the prefix when registering) |
| A command method written in the [App](../reference/Core-App.md) class can't be found | The app class's own `command_` methods are **not** auto-registered | In `onInited()` do `$this->regConsoleCommand(static::class)`, or create a separate command class |
| `--key=value` can't be read | Option key names carry no `--` | Use `$params['key']` (`$params['--']` holds the positional parameters) |
| A child app's command can't be called | The command group prefix is missing | Use `ChildAppName:command`, or customize the prefix mapping |
| crontab reports the project can't be found | No `cd` to the project directory, so `path` derivation fails | `cd /srv/myproj && php cli.php …`, or pass `'path'` explicitly |
| `@command_desc` shows a trailing `*/` in help | The description reads to end of line and picks up the comment closer | Put the description on its own line and `*/` on the next line |
| Reading `$_GET` in a command gets nothing | There is no Web request under the CLI | Use `getCliParameters()` for parameters; to "request" something use the `fetch` command |
| Thinking `bin/duckphp run` starts a server | `bin/duckphp` is the installer CLI (`new`/`help`/`show`) | Start a server with the project CLI's `run` command (`php cli.php run`) |

## Next steps

- [Chapter 2-17 Testing](testing.md): the command line is also an entry point for "testing business without starting a server".
- [Chapter 3-1 The Application Tree and Phase Basics](advanced-phase.md): the phase mechanism behind command group prefixes.
- [Chapter 3-6 The Installer and Web Install Flow](installer.md): `bin/duckphp new` and the Web install page.
- The reference manual: [DuckPhp\Component\Command](../reference/Component-Command.md), [DuckPhp\Component\CommandMetaInterface](../reference/Component-CommandMetaInterface.md), [DuckPhp\Core\Console](../reference/Core-Console.md).
