# DuckPhp\Core\Console

The command line (Console) execution / command routing core: turns CLI argv into command invocations plus direct scanning/running.

## Introduction

`Console` is DuckPHP's command-handling root for the CLI: the CLI entry point (Kernel's `execute()`) makes it read arguments from `$_SERVER['argv']`, parse out "command name + option name/values", then find the class and method to invoke from a set of "namespace → command classes", instantiate it and invoke it by reflection (supports binding values by method parameter name + default arguments/errors).

It also provides:

- commands organized by namespace: `ns:sub:command` separated by colons; `getThisCommandPrefix()` (Core-level Phase names normalizing command groups) and the various help outputs are expanded by `DuckPhp\Component\Command`;
- registration APIs: `regCommandClasses / regCommandClassSingle / DoRun`;
- argument reading: `getArgs()`/`getCliParameters()` and readLines interactive prompt input;
- structure-agnostic: it does not do `execute/exit` itself; the host handles exceptions → see Kernel.runException (Console errors throw `DuckPhpSystemException`…).

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class Console extends ComponentBase`
- The inheritance chain offers `Console::_()`/`RunQuickly` (when used as a singleton); init idempotency follows the app conventions by default.

## Options

`Console::$options`

| Option | Default | Description |
|---|---|---|
| `console_command_classes` | `[]` | Command registry: `namespace => [ className => method prefix ]`. A prefix of `true` is interpreted as `command_`. |
| `console_command_phase` | `[]` | A `namespace => phase` map; before running a command of a namespace, switch to the corresponding Phase first. |
| `console_command_default` | `'help'` | When there is no trailing positional argument (still give `--` a command string), used as the default command word. |
| `console_readlines_logfile` | `''` | When a path is given: `readLines` writes each echoed input to this file (relative to `path_runtime`); relative to the actual `path_runtime`. |

(Protected options such as `context_class` need no overriding.)

## Usage

Framework CLI scenario: business code usually does not touch the `Console` underpinnings itself; a CLI command most naturally follows this skeleton:

```php
class DemoApp extends \DuckPhp\DuckPhp { }
\DemoApp::_()->init([]);
\Admin\ConsoleCommand 类等放 app 的 cmd ...

// skeletonized — actually run via; a Controller/D user's own command class looks like:
class MyCmd {
    public function command_hello() { echo "hello\n"; }
}

// register & run the "hello" command:
use DuckPhp\Core\Console;
$c = Console::_()->init([]);
$c->regCommandClassSingle('', MyCmd::class, true);   // true => command_
$c->run();   // usually with argv (e.g. `php x.php hello …`)
```

## Configuration example

A typical usage registers command classes for the current namespace in the App:

```php
// in MyApp::regConsoleCommand … or directly:
Console::_()->regCommandClasses('sub', [
    StatusCmd::class => 'command_',     // the prefix for that phase's name
]);
// the command "sub:ping" tries StatusCmd->command_ping()…
```

How the CLI is reached from the entry point, in short: see KernelTrait execute() which is `Console::_()->run()`.

## readLines (interactive)

`readLines` prints prompts / reads a line based on the `{key}` placeholders + trailing default values in the multi-line `$desc`, and returns an array. It is the general foundation of the "question-and-answer wizard when scaffolding new". (The sample prompt string below is deliberately kept in Chinese as example data.)

```php
$answers = Console::_()->readLines(
    ['db' => 'mysql', 'name' => 'x'],
    "{db} 打数据库类型  \n{name} 打 名称:"
);
```

`readLinesFill/readLinesCleanFill` are for test injection (feeding a string as stdin); `console_readlines_logfile` can record it.

## Caveats

1. Parser rules (`parseCliArgs`): `--option[=value]` keys are underscore-ified; a valueless `--flag` is recorded as `true`; repeated values aggregate into an array; `--` (positional) values are collected into `$ret['--']`, filled with `console_command_default` when absent.
2. `run()` first splits colons with `splitCommand` → namespace/trailing command; `getCommandCallback` returns the first prefix whose method exists = true; when no command is found it throws `DuckPhpSystemException(…Command Not Found…)`.
3. Automatic phase jump: when `console_command_phase` matches, it first executes under `App::Phase(predetermined)` and then restores.
4. The command method may bind values by parameter name via reflection (`callObject` checks argument names, default values, etc.; a missing required one throws an exception).
5. `DoRun($path)` is just a static alias of `run()`; for `app()` see context.
6. This file contains no outward exit; exceptions after crossing phases are left to the upstream.

## All options

```php
    public $options = [
        'console_command_classes' => [],
        'console_command_phase' => [],
        'console_command_default' => 'help',
        'console_readlines_logfile' => '',
    ];
```

## Methods

> `__construct` is inherited from ComponentBase (not repeated here); the following are only Console's source methods (including protected utilities).

### Public methods

    public function init(array $options, ?object $context = null)
Whitelist option merge (component semantics as usual); sets context_class; marks is_inited.

    public function getCliParameters()
Returns the bound parameters (parses argv once if none) — includes named parameters and the `--` positional array.

    public function getArgs()
Returns only the positional arguments (the `$ret['--']` array).

    public function app()
Returns the owning application (same as `context()`, the common semantics).

    public function regCommmandPrefixPhase($prefix, $phase)
Registers a command prefix → Phase mapping (switches Phase before running that namespace).

    public function regCommandClasses($prefix, array $classes)
Merges the command-class map for a given prefix (add/replace); classes look like `[X::class => method prefix]`.

    public function regCommandClassSingle(string $prefix, string $class, $method_prefix)
Registers a single command precisely (prefix name, class, method_prefix — pass `true`→'command_').

    public static function DoRun($path_info = '')
One-shot wrapper: calls run(). $path is a placeholder by convention.

    public function run()
Reads argv → parseCli → takes the command → splitCommand → getCommandCallback; looks up the namespace → switches phase → callObject; throws an exception on error/not found.

    public function getCommandCallback($cmd)
Given a command string, returns `[class,method]` or `[null,null]`: looks up the prefix's classes; returns on a method hit (last-registered first?).

### Run/registration helpers

    public function readLinesFill($data)
Treats $data directly as content to be read (for tests / scripted interaction mode).

    public function readLinesCleanFill()
Clears the data, returning readLines to normal stdin mode.

    public function readLines($options, $desc, $validators = [], $fp_in = null, $fp_out = null)
Prompts line by line per the descriptor: supports {key} placeholder default values, validators (filter_var_array), read logging and echo.

    public function callObject($class, $method, $args, $input)
Instantiates/gets the command object by reflection and binds values from input/arg by the method's parameter names; a missing required one throws DuckPhpSystemException(-2).

### Protected methods

    protected function splitCommand($cmd)
Splits `ns:cmd` into `[namespace, trailing method]`.

    protected function parseCliArgs(array $argv): array
State-machine parsing of argv: collects --flags/single-dash/equals/values, outputs a structure containing `--`, `key…` and the positional array.

    protected function getObject(string $class): object
Prefers calling `class::_()` (when callable), otherwise `new $class`.

## Related links

- [DuckPhp\Core\KernelTrait](Core-KernelTrait.md) — the host of `execute()`→`Console::run()`
- [DuckPhp\Component\Command](Component-Command.md) — the default CLI examples/help collection
- [DuckPhp\Core\App](Core-App.md) — the application and command storage in options
- guide: [cli](../guide/cli.md)
