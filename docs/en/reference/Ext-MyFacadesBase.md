# DuckPhp\Ext\MyFacadesBase

## Introduction

`MyFacadesBase` is the base class for Facade classes: any "facade class" extending it sends its undefined static calls into `__callStatic`, where `MyFacadesAutoLoader` resolves the real object and forwards the call (and throws `ErrorException("BadCall")` when no target is found).

It works together with `MyFacadesAutoLoader`: the classes the auto-loader generates dynamically extend this class, so the facade call chain is facade class → `MyFacadesBase::__callStatic` → `MyFacadesAutoLoader::getFacadesCallback` → the real object's method.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class MyFacadesBase extends DuckPhp\Core\ComponentBase`

## Usage

Normally you do not write facade classes by hand — `MyFacadesAutoLoader` generates them:

```php
// with facades_map configured:
MyFacades\Mail::send($to, $body); // -> MailService::_()->send(...)
```

To define a facade manually, just extend this class to get the same static forwarding.

## Caveats

- `__callStatic` resolves through `MyFacadesAutoLoader::_()->getFacadesCallback(static::class, $name)`; when that returns `null` it throws `ErrorException("BadCall")`.
- The real target object is fetched as the `$class::_()` singleton.

## Methods

### Public methods

    public function __construct()
An empty constructor.

    public static function __callStatic($name, $arguments)
Forwards an undefined static call to the real object (resolved through MyFacadesAutoLoader).

## Related links

- [DuckPhp\Ext\MyFacadesAutoLoader](Ext-MyFacadesAutoLoader.md) — facade auto-loading and resolution
- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — the component base class
