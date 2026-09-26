# DuckPhp\Component\Command

A set of utility CLI commands built into the framework: version / help / embedded HTTP server / fetch a route / call a method / list routes / toggle debug.

## Introduction

`Command extends ComponentBase` is the command pack DuckPHP registers by default (DuckPhp/DuckPhpAllInOne merges it into the current App command space when needed — see its onPrepare). When you run `command help` etc. in the CLI, the response usually comes from the methods here.

Command naming: a method starting with `command_xxx` in a command class is one action; Command provides:

- `command_version()` version
- `command_help()` shows usage and the command list (aggregated from Console registrations and each method's @command_desc)
- `command_run()` runs the current application as an embedded HTTP session via HttpServer;
- `command_fetch($uri,$post)` fetches a private path from the CLI (written via the SuperGlobal context, then calls App→serve)
- `command_call(<class>@<method>…)` calls a business method (business convenience)
- `command_debug($off=false)` toggles the dev flag (requires data_file capability)

It also contains several referenced fetch/parse helper methods (see the method list below).

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Command extends ComponentBase`

## Usage

(Usually already registered as the default commands, so you can directly) in the CLI:

```text
php xx run                 # start embedded http via command_run
php xx version
php xx help
php xx routes
php xx call foo/MyBiz@doWork a --x=1
php xx fetch '/account/detail'
php xx debug --off
```

The command description copy (such as the README text in help) comes from the `@command_desc statement` in each method's docblock (supports `[[lang|fallback]]`, translated via `translateCommandDesc/langText`).

## Implementation notes (where command descriptions come from)

help, inside `Command::command_help()`, collects the method names of each class registered with `Console` (optionally per class via `__commandMeta()`), and takes the description from `@command_desc`/the first doc line of each; an empty namespace is shown as `*Default*`.

## Caveats

1. Command collection is **independent per class**: as soon as a command class defines `__commandMeta()` (see [CommandMetaInterface](Component-CommandMetaInterface.md)), its return value takes over that class's command table wholesale — the "method prefix" configured for it in `console_command_classes` no longer takes effect (the method internally uses the fixed `command_` prefix).
2. The way `getCommandsByClasses()` handles the value shapes of `console_command_classes` is a **defensive alignment with the upstream `Console`** (matching, case by case, how `Console` reads them when executing commands): `false` or `null`/unset → skip; `true` → use the default prefix `command_`; a string → that string is the prefix. This is not a semantic invented by this class; see the `console_command_classes` notes in [Core-Console](Core-Console.md).

## Methods

### Public methods (commands)

    public function command_version(): void
Outputs the owning App::version().

    public function command_help(): void
Outputs version + the usual help text + the full command list (getCommandListInfo).

    public function command_run()
Reads Console CLI parameters and runs the App as an embedded http-server via HttpServer (temporarily switches cli_enable off).

    public function command_fetch($uri = '', $post = false)
"Fetch" from the CLI: writes REQUEST_URI/PATH_INFO/METHOD into __SUPERGLOBAL_CONTEXT (or globals), then context->serve().

    public function command_call()
`namespace/Business@method +args`: resolves the business class and invokes it via reflection (through Console::callObject).


    public function command_debug(bool $off = false): void
Toggles the debug flag (writes is_debug in ext options), with restricted prerequisites: data_file_enable + data_file_bump_allowed…

### Public methods (for class extension / description)

    public function __commandMeta()
Reflects this class's methods with the `command_` prefix and returns `[command name => description]`; as soon as a command class defines this method (i.e. implements [CommandMetaInterface](Component-CommandMetaInterface.md)), `getCommandsByClass()` adopts its return value directly (no more passed prefix and reflection).

### Protected methods

    protected function getCommandListInfo(): string
Iterates the Console classes, printing each namespace (default `*Default*`)->command group (prefix stripped + padded).

    protected function getCommandsByClasses(array $classes): array
Aggregates multi-class mappings into a command table: value `false` skips, `true` is treated as `command_`, anything else is used as the prefix (no longer accepts a phase parameter).

    protected function getCommandsByClass(string $class, string $method_prefix): array
Single command class: if the class defines `__commandMeta()` call it directly; otherwise extract by reflection with `$method_prefix`.

    protected function getCommandsByClassReflection(\ReflectionClass $ref, string $method_prefix): array
Reflects all methods, filters command names by prefix, takes @command_desc (or the first doc line) as the description, and translates it.

    protected function translateCommandDesc(string $desc): string
Translates `[[key|fallback]]` fragments in the description via `App::langText()`.

## Related links

- [DuckPhp\Core\Console](Core-Console.md) — the command execution host
- [DuckPhp\Component\CommandMetaInterface](Component-CommandMetaInterface.md) — command table metadata interface (`__commandMeta()`)
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) — command_run starts the server
- [DuckPhp\Ext\RouteLister](Ext-RouteLister.md) — the actual implementation of the `command_routes` command (moved out of this class into there)
- [DuckPhp\Core\App](Core-App.md)/langText — source of the version / lang translation
