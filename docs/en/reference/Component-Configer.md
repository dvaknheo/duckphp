# DuckPhp\Component\Configer

A minimal component that reads PHP config files from `config/`: requires by "basename", caches the results, supports whole-array / single-key reads.

## Introduction

`Configer extends ComponentBase` reads `{file_basename}.php` (which returns an array) into memory and caches it (`$all_config`).

- `_Config($file_basename,$key,$default)`: reads the whole array or a single key;
- a missing single file returns `[]` / default, no error thrown;
- the `.php` suffix is appended internally; the target is relative to `path_config` (relative to the project root) and resolved via `extendFullFile` (so it participates in Phase child-app overriding).

Business code usually does not use it directly, but goes through the per-layer Helpers (such as Business/Controller's `Config`) which end up here; this doc covers its methods themselves.

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class Configer extends ComponentBase`

## Options

`Configer::$options`:

| Option | Default | Description |
|---|---|---|
| `path` | `''` | Project root path (base for relative paths). |
| `path_config` | `'config'` | Config directory name (relative to `path`; an absolute path overrides). |

## Usage

```php
use DuckPhp\Component\Configer;

$c = Configer::_()->init(['path'=>__DIR__,'path_config'=>'config']);

$all = $c->_Config('app');              // returns the contents of config/app.php
$val = $c->_Config('app','debug',false); // read a single key
```

config/app.php looks like `return [ 'debug'=>true, 'db'=>... ];`, for example.

## Caveats

- Caching is basename-based: the same basename is not required a second time.
- A completely missing directory/file → empty array; no exception.
- A child app can override with the same file: the `App->getOverrideableFile` lookup can hit (phase takes precedence).

## All options

```php
    public $options = [
        'path' => '',
        'path_config' => 'config',
    ];
```

## Methods

### Public methods

    public function _Config($file_basename = 'config', $key = null, $default = null)
Reads a config: without a key returns the whole block (empty→default); with a key returns `$config[$key] ?? $default` (cached via _LoadConfig).

### Protected methods

    protected function _LoadConfig(string $file_basename): array
Returns directly if cached; otherwise appends `.php`, locates the file via `App->getOverrideableFile(path_config, file)` and requires it, writing the all_config cache on success. A missing file stores [] and returns [].

    protected function loadFile(string $file): array
`return require $file;` — actually reads the file as an array.

(init/`_()` etc. inherited from ComponentBase are not listed.)

## Related links

- [DuckPhp\Core\App](Core-App.md) (pre-registers Configer, etc.)
- The `Foundation` per-layer Helpers route Config through this
- For the config directory convention see the Project structure guide
