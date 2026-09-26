# DuckPhp\Ext\MyFacadesAutoLoader

## Introduction

`MyFacadesAutoLoader` implements "facade namespace auto-loading": when code touches an undefined class under `facades_namespace` (default `MyFacades\`), or hits a key in `facades_map`, the component dynamically `eval`s a class extending `MyFacadesBase`, so that any static call on it is forwarded to the real class through `MyFacadesBase::__callStatic`.

In practice: register an implementation class in `facades_map` (e.g. `MyFacades\Foo => RealFoo::class`), or simply place the real class name under the `MyFacades\` prefix, and you can call it statically the facade way.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class MyFacadesAutoLoader extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `facades_namespace` | `'MyFacades'` | The facade class namespace (backslashes are trimmed). |
| `facades_map` | `[]` | Facade class name → real class name map. |
| `facades_enable_autoload` | `true` | Whether to register `spl_autoload` (when off you must trigger it yourself). |

## Usage

```php
\DuckPhp\Ext\MyFacadesAutoLoader::_()->init([
    'facades_namespace' => 'MyFacades',
    'facades_map' => ['MyFacades\User' => \MyProject\UserService::class],
], $app);

// from then on, static calls are forwarded to the matching method of the UserService::_() instance:
MyFacades\User::getById(1);
```

## Caveats

- `_autoload()`: when a class name starts with `MyFacades\` or is a key of `facades_map`, it generates `class X extends \DuckPhp\Ext\MyFacadesBase {}` (via `eval`).
- `getFacadesCallback($input_class,$name)`: first an exact match in `facades_map`, otherwise the prefix is stripped to get the real class; the real class must support `_()` (`is_callable([$class,'_'])`), and the method returns `[$object,$name]`.
- `clear()`: empties the map and unregisters the autoloader.

## Methods

### Public methods

    public function _autoload($class): void
The autoload hook: dynamically generates a facade class for the facade namespace.

    public function getFacadesCallback(string $input_class, string $name): ?array
Resolves the target of a facade static call: returns `[real object, method name]` or `null`.

    public function clear(): void
Empties the mapping and unregisters the autoloader.

### Protected methods

    protected function initOptions(array $options): void
Initialises the prefix and the map; registers `spl_autoload` according to the options.

## Related links

- [DuckPhp\Ext\MyFacadesBase](Ext-MyFacadesBase.md) — the facade base class (the `__callStatic` entry point)
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class
