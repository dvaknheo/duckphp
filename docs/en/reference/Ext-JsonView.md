# DuckPhp\Ext\JsonView

## Introduction

`JsonView` extends `Core\View`: it changes the rendering output to **JSON**. Once enabled, both `Helper::Show($data, $view)` and `View::_Display()` output the (optional) `$data` as JSON through `CoreHelper::ShowJson()` — suited to pure API/JSON-returning applications.

`json_view_skip_vars` configures the keys to strip from the data (e.g. the framework-injected `__view_data`).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class JsonView extends DuckPhp\Core\View`

## Options

| Option | Default | Description |
|---|---|---|
| `json_view_skip_replace` | `false` | When `true`, do not replace the global `View::_()`. |
| `json_view_skip_vars` | `[]` | List of keys to strip from `$data` before output. |

## Usage

```php
\DuckPhp\Ext\JsonView::_()->init([
    'json_view_skip_vars' => ['__view_data'],
], $app);

// Afterwards in a Controller:
Helper::ShowJson(['ok' => true]);      // outputs directly
Helper::Show($data, 'unused');         // also turned into JSON output by this component
```

## Caveats

- `_Show`/`_Display` ignore the `$view` argument (the view name no longer matters) and only output JSON.
- `json_view_skip_vars` keys are `unset` one by one before output, avoiding leaking internal data that should not be exposed.

## Methods

### Public methods

    public function __construct()
Constructs after merging the parent options.

    public function init(array $options, ?object $context = null)
Initializes; replaces `View::_()` with this instance by default.

    public function _Show(array $data, string $view)
Outputs `$data` as JSON after stripping the skip keys.

    public function _Display(string $view, ?array $data = null): void
Outputs `$data` as JSON after stripping the skip keys.

## Related links

- [DuckPhp\Core\View](Core-View.md) — the parent class
- [DuckPhp\Core\CoreHelper](Core-CoreHelper.md) — where ShowJson is implemented
