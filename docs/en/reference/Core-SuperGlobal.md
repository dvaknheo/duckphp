# DuckPhp\Core\SuperGlobal

An isolated access layer for the superglobals ($_GET/POST/REQUEST/SERVER): it can "move superglobals into the container", exposes read shells, and provides SESSION/COOKIE conveniences (cookie writes go through SystemWrapper).

## Introduction

`SuperGlobal` provides ways to operate on the HTTP superglobals without polluting global symbols: it copies `$_GET/$_POST/$_REQUEST/$_SERVER/$_COOKIE/$_SESSION/$_FILES` into its own public property copies (`_GET…`), readable via static shells, and can read/write the real superglobals depending on context; it also establishes the global constant `__SUPERGLOBAL_CONTEXT` (used inside global functions/components to fetch superglobals from the "context object", enabling test-isolated multi-request environments).

- The context way: `static::DefineSuperGlobalContext()` defines `__SUPERGLOBAL_CONTEXT` = `SuperGlobal::_`; afterwards `( __SUPERGLOBAL_CONTEXT )()->_GET` reads from the object copy instead of using `$_GET` directly.
- Shell reads: `_GET('key',default)` etc. go through `getSuperGlobalData()`: with the context enabled it reads the context object's data, otherwise it falls back to `$GLOBALS[$superglobal_key]`.
- Session/file conveniences + Cookie: `_Session… / _Cookie…`

A `/*…*/`-commented-out draft of "static GET/POST/…SERVE" in the source is not enabled and is not listed here (to prevent misuse).

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class SuperGlobal extends ComponentBase`
- Public properties: `$_GET,$_POST,$_REQUEST,$_SERVER,$_COOKIE,$_SESSION,$_FILES`

## Options

`SuperGlobal` has only:

| Option | Default | Description |
|---|---|---|
| `superglobal_auto_define` | `false` | When true at init: automatically `DefineSuperGlobalContext()` and `_LoadSuperGlobalAll()` (snapshots the current superglobals into the properties). |

## Usage

```php
use DuckPhp\Core\SuperGlobal;

SuperGlobal::DefineSuperGlobalContext();      // establish __SUPERGLOBAL_CONTEXT (once)
SuperGlobal::LoadSuperGlobalAll();            // real superglobals -> properties

SuperGlobal::_()->_GET('id');               // read id (context/OO)
SuperGlobal::_()->_POST('name','');
SuperGlobal::_()->_SessionSet('uid', 5);
SuperGlobal::_()->_SessionGet('uid');
SuperGlobal::_()->_CookieSet('theme','dark', 3600);   // goes through SystemWrapper::setcookie underneath
```

The framework presets both steps automatically depending on `superglobal_auto_define`; business code normally uses `CoreHelper`'s `GET()`-style shells directly rather than bypassing them. The same implementation degrades gracefully between the context (constant) and globals.

## Caveats

- One-shot snapshot: `_LoadSuperGlobalAll` only copies at init or on explicit call; later changes to the real `$_GET` are not synced automatically (call again, or as needed per layer).
- `_SessionUnset/_CookieGet` etc. handle the with/without-context cases separately; `_CookieSet`'s lifetime handling is merged into SystemWrapper.
- Older "read superglobal" scripts may hand-write the `defined('__SUPERGLOBAL_CONTEXT')?…` ternary: that is dual-use for isolated scenarios; with this component you are on the official wrapper.
- The commented-out static GET/POST/… is not in effect; do not cite it as API in external docs.

## Methods

### Public static methods

    public static function DefineSuperGlobalContext()
Defines the constant `__SUPERGLOBAL_CONTEXT` (value `SuperGlobal::_`) when undefined; returns whether it was newly defined.

    public static function LoadSuperGlobalAll()
Static shell: forwards to the instance `_LoadSuperGlobalAll`.

    public static function SaveSuperGlobalAll()
Static shell: forwards to the instance `_SaveSuperGlobalAll`.

    public static function LoadSuperGlobal($key)
Static shell: forwards to the instance `_LoadSuperGlobal`.

    public static function SaveSuperGlobal($key)
Static shell: forwards to the instance `_SaveSuperGlobal`.

    public static function GET($key = null, $default = null)
Reads `$_GET` (static shell → `_GET`).

    public static function POST($key = null, $default = null)
Reads `$_POST`.

    public static function REQUEST($key = null, $default = null)
Reads `$_REQUEST`.

    public static function COOKIE($key = null, $default = null)
Reads `$_COOKIE`.

    public static function SERVER($key = null, $default = null)
Reads `$_SERVER`.

    public static function SESSION($key = null, $default = null)
Reads `$_SESSION`.

    public static function FILES($key = null, $default = null)
Reads `$_FILES`.

    public static function SessionSet($key, $value)
Writes SESSION (static shell → `_SessionSet`).

    public static function SessionUnset($key)
Deletes a SESSION key.

    public static function SessionGet($key, $default = null)
Reads a SESSION key.

    public static function CookieSet($key, $value, $expire = 0)
Sends a cookie (static shell → `_CookieSet`, via SystemWrapper).

    public static function CookieGet($key, $default = null)
Reads a cookie key.

### Public instance methods

    public function _LoadSuperGlobalAll()
Snapshots the global superglobals into the `_GET…_FILES` fields.

    public function _SaveSuperGlobalAll()
Writes the fields back to the corresponding global superglobals.

    public function _LoadSuperGlobal($key)
Single key: field ← `$GLOBALS[$key]`.

    public function _SaveSuperGlobal($key)
Single key: `$GLOBALS[$key]` ← field.

    public function _GET($key = null, $default = null)
Reads the `_GET` container (context first, otherwise `$GLOBALS`), by key or as a whole.

    public function _POST($key = null, $default = null)
Reads the POST container.

    public function _REQUEST($key = null, $default = null)
Reads the REQUEST container.

    public function _COOKIE($key = null, $default = null)
Reads the COOKIE container.

    public function _SERVER($key = null, $default = null)
Reads the SERVER container.

    public function _SESSION($key = null, $default = null)
Reads the SESSION container.

    public function _FILES($key = null, $default = null)
Reads the FILES container.

    public function _SessionSet($key, $value)
Writes SESSION (context or global branch).

    public function _SessionUnset($key)
Deletes a SESSION key.

    public function _SessionGet($key, $default = null)
Reads a SESSION key.

    public function _CookieGet($key, $default = null)
Reads from COOKIE.

    public function _CookieSet($key, $value, $expire = 0)
Sends a cookie (via `SystemWrapper::setcookie`, with expire timing).

### Protected methods

    protected function initOptions(array $options): void
When `superglobal_auto_define` is true, `DefineSuperGlobalContext()` and `LoadSuperGlobalAll()`.

    protected function getSuperGlobalData(string $superglobal_key, ?string $key, $default)
The actual fetch: context first reads the context object's property, otherwise `$GLOBALS[$key ? index : all]`.

## Related links

- [DuckPhp\Core\SystemWrapper](Core-SystemWrapper.md) — the low level that sends cookies
- [DuckPhp\Core\App](Core-App.md) — deployment/debug/platform reads mostly use this
- See also the related constant `__SUPERGLOBAL_CONTEXT`;
- [DuckPhp\Core\SingletonExTrait](Core-SingletonExTrait.md) — the singleton entry point; the isolated access is used for sub-requests and in tests (`demo/`, `tests/`).
