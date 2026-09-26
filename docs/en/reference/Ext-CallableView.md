# DuckPhp\Ext\CallableView

## Introduction

`CallableView` extends `Core\View`: it replaces the "view" from a "template file" with a "callable object/function". Once enabled, `_Show($data, $view)` processes `$view` into a callback name with `callable_view_prefix` (e.g. `view_index`), resolves a callable from the class given by `callable_view_class` (object / class name / `_()` singleton supported), and calls it in head → body → foot order.

Use case: scenarios where you skip template files and output directly from PHP methods (or closures); `DuckPhpAllInOne`'s internal `_Show` follows a similar idea.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class CallableView extends DuckPhp\Core\View`

## Options

| Option | Default | Description |
|---|---|---|
| `callable_view_header` | `null` | Header view callback name (falls back to the parent `View::$header_file`). |
| `callable_view_footer` | `null` | Footer view callback name (falls back to the parent `View::$footer_file`). |
| `callable_view_class` | `null` | Class that provides the view callbacks (a class name string or an object). |
| `callable_view_is_object_call` | `true` | When the class name is a string and `_()` works, resolve it to the singleton instance, otherwise `new`. |
| `callable_view_prefix` | `null` | Prefix applied to the view name (e.g. `view_`, together with the `/`→`_` replacement). |
| `callable_view_skip_replace` | `false` | When `true`, do not replace the global `View` singleton with this instance. |

## Usage

```php
$options = [
    'callable_view_class' => MyView::class,   // MyView provides view_index() etc.
    'callable_view_prefix' => 'view_',
];
// Enable (by default points View::_() at this instance):
\DuckPhp\Ext\CallableView::_()->init($options, $app);
```

```php
class MyView
{
    public function view_index($data)
    {
        echo 'Index: ' . ($data['title'] ?? '');
    }
}
```

## Caveats

- `viewToCallback()`: replaces `/` in the view name with `_` and adds the prefix; when `callable_view_class` is set, assembles `[$obj, $func]`; returns `null` when not callable (then it falls back to the parent `_Show` and renders the template).
- `_Show`: header/footer are also resolved as callbacks and called before/after; same for `_Display` (no head/foot).
- `init()` replaces the global `View::_()` by default (unless `callable_view_skip_replace`), so `Helper::Show()` etc. inside the framework route into this implementation.

## Methods

### Public methods

    public function __construct()
Constructs after merging the parent `View` default options.

    public function init(array $options, ?object $context = null)
Initializes (parent flow); replaces `View::_()` with this instance by default.

    public function _Show(array $data, string $view)
Resolves the view into a callback and calls it with head/foot; falls back to the parent template rendering when not callable.

    public function _Display(string $view, ?array $data = null): void
Resolves the view into a callback and calls it directly; falls back to the parent when not callable.

### Protected methods

    protected function viewToCallback(?string $func)
Processes the view name into a callable (prefix / `/`→`_` / class instantiation); returns `null` when not callable.

## Related links

- [DuckPhp\Core\View](Core-View.md) — the parent class
- [DuckPhp\Ext\EmptyView](Ext-EmptyView.md) / [DuckPhp\Ext\JsonView](Ext-JsonView.md) — other view extensions
