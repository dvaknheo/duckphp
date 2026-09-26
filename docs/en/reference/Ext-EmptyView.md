# DuckPhp\Ext\EmptyView

## Introduction

`EmptyView` extends `Core\View`: it does **not render template files**; instead it assembles the "view information to be rendered" into the data and hands it to the outer layer — suited to delegating the rendering work to another template engine, a frontend framework, or your own outputter.

Behavior:
- `_Show($data, $view)`: merges the data, then writes `$view` into `$data['view']` (the key name is set by `empty_view_key_view`), and attaches `view_header`/`view_footer` (the full template file paths resolved from the parent `$header_file`/`$footer_file`). When `empty_view_trim_view_wellcome` is on, the `Main/` prefix (`empty_view_key_wellcome_class`) is stripped from view names that start with it.
- `_Display($view, $data)`: only sets `$data['view']` to the full view file path.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class EmptyView extends DuckPhp\Core\View`

## Options

| Option | Default | Description |
|---|---|---|
| `empty_view_key_view` | `'view'` | The "view key name" assembled into the data. |
| `empty_view_key_wellcome_class` | `'Main/'` | Welcome view prefix (used together with trim). |
| `empty_view_trim_view_wellcome` | `true` | Whether to strip the welcome prefix from view names. |
| `empty_view_skip_replace` | `false` | When `true`, do not replace the global `View::_()`. |

## Usage

```php
\DuckPhp\Ext\EmptyView::_()->init([], $app);

// Afterwards Helper::Show($data, 'user/list') does not render a file;
// it leaves $data['view']='user/list' (plus view_header/view_footer paths) to the outer layer.
```

## Caveats

- Neither `_Show` nor `_Display` outputs any HTML; the actual output is produced by the caller based on `$data`.
- `view_header`/`view_footer` hold the full paths resolved by `getViewFile()`, not contents.

## Methods

### Public methods

    public function __construct()
Constructs after merging the parent options.

    public function init(array $options, ?object $context = null)
Initializes; replaces `View::_()` with this instance by default.

    public function _Show(array $data, string $view)
Assembles the view information (view/view_header/view_footer) into the data without rendering a file.

    public function _Display(string $view, ?array $data = null): void
Only sets `$data['view']` to the full view file path.

## Related links

- [DuckPhp\Core\View](Core-View.md) — the parent class
- [DuckPhp\Ext\CallableView](Ext-CallableView.md) — extension that replaces templates with callbacks
- [DuckPhp\Ext\JsonView](Ext-JsonView.md) — extension that outputs JSON
