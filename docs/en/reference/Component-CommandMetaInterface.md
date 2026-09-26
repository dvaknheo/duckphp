# DuckPhp\Component\CommandMetaInterface

## Introduction

`CommandMetaInterface` is the "command table metadata" contract: a command class implementing it can return its own command table (command name → description) **in code**, for [DuckPhp\Component\Command](Component-Command.md) to use directly when collecting the CLI help list, instead of reading method names and `@command_desc` comments via reflection.

Typical scenario: commands are generated dynamically (assembled from config/plugins), or descriptions must be translated at runtime — use it wherever comments cannot express it.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `interface CommandMetaInterface`
- The only member is `__commandMeta()`; no constants, no inheritance.

## Usage

```php
use DuckPhp\Component\CommandMetaInterface;

class MyCommand implements CommandMetaInterface
{
    public function __commandMeta(): array
    {
        // command name => description (keys do not include method_prefix)
        return [
            'ping'  => 'ping the service',
            'clean' => '[[command.clean|clean the cache]]',   // supports [[key|fallback]] translation
        ];
    }
    public function command_ping() { }
    public function command_clean() { }
}
```

Once registered into Console (`'cmd' => [MyCommand::class => 'command_']`, or `regConsoleCommand()`), `command help` lists the table above.

## Caveats

1. **It takes over wholesale**: `Command::getCommandsByClass()` checks `hasMethod('__commandMeta')` first; on hit it **returns its result directly** — the "method prefix" configured for the class in `console_command_classes` no longer participates, and `@command_desc` on methods is no longer parsed. Internally the fixed `command_` prefix is used (i.e. `Command::__commandMeta()`'s own implementation reflects the class with `command_`).
2. **Keys are "command names", not method names**: `Command` only uses the returned keys as command words, so strip the `command_` prefix from the keys yourself; keys must not carry a namespace either (namespaces are decided by `Console`'s command registry).
3. **Returns `array<string, mixed>`**: values are description strings; consumers pass them through `translateCommandDesc()`/`langText()`, so `[[key|fallback]]` placeholders work as-is.
4. **This interface is a "recommendation", not a hard requirement**: `Command::getCommandsByClass()` duck-types via `hasMethod('__commandMeta')` — **not `implements`-ing this interface but having the same method name and signature works just the same**. Implementing it makes the contract explicit (static analysis / callers see it at a glance), not runtime-validated.
5. **Instantiation**: `Command` goes through `(new $class)->__commandMeta()` (**the constructor runs**; no singleton factory, no constructor arguments), so do not do heavy work in the constructor or rely on it being skipped; by contrast, [PermissionMenu](Ext-PermissionMenu.md)'s similar hook `__permissionMenuMeta()` goes through `ReflectionClass::newInstanceWithoutConstructor()`, **not running the constructor** — the two differ deliberately (command classes are usually lightweight singletons; controller classes should not be constructed while menus are being built).

## Methods

### Public methods

    public function __commandMeta(): array
Returns this command class's command table `[command name => description]`; `Command` adopts its return value wholesale when collecting CLI help.

## Related links

- [DuckPhp\Component\Command](Component-Command.md) — the consumer (`getCommandsByClass()` / `getCommandListInfo()`)
- [DuckPhp\Core\Console](Core-Console.md) — command execution and registration (`console_command_classes`)
