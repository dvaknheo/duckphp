# DuckPhp\Core\View

The view component: renders the data a controller/logic wants to show into output (template include), with head/foot wrapping, data assignment, and "render to string" capture.

## Introduction

`View` is DuckPHP's default view implementation (`class View extends ComponentBase`). It depends on no template engine; it renders plain PHP template files (include `.php`) — the files usually called `view/*.php`.

Core abilities:

- `extract()` a piece of data into local variables, then `include` the view (plus optional head/foot) for output;
- The rendering trio `Show` (immediate output), `Display` (output a given template), `Render` (capture → string) — each has a static shell (calling `View::Show(...)` from a Controller/Helper is the easiest);
- Data: `assignViewData()`/`getViewData()` pre-assign data into the instance's context;
- View-file location is based on `options[path]/options[path_view]` + `getOverrideableFile()` (supports phase-level override subdirectories).

You normally do not make a standalone `View()`; just name the "view name/file" in `<controller>_Show(...)`, or the global `View::_()->_Show(...)` / static shells. It is also wrapped into a higher-level `_Show` in App/DuckPhpAllInOne.

## Class info

- Namespace: `DuckPhp\Core`
- Declaration: `class View extends ComponentBase`
- Usual access: `View::_()` returns the current app phase's instance; or the symmetric static shells `View::Show/Display/Render`.
- Public properties: `$options`; `$data` (currently pre-assigned data).

## Options

`View::$options`: (defaults)

| Option | Default | Description |
|---|---|---|
| `path` | `''` | Project root path (the component layer's base for "relative paths"). |
| `path_view` | `'view'` | View directory relative name (relative to `path`; an absolute value is used as-is). `getViewFile()` joins `{$path}/{$path_view}/{$file}.php` with it. |
| `view_skip_notice_error` | `true` | Whether to temporarily silence `E_NOTICE` noise while rendering. With true, `_Show` removes E_NOTICE temporarily and restores it at the end. |

## Usage

### Rendering output directly (most common)

```php
use DuckPhp\Core\View;

// view name (relative to view/, without .php)
View::Show(['user' => $user], 'user/profile');
```

Equivalent static forwarding:

```php
View::_()->_Show(['msg'=>'hi'], 'welcome');   // outputs welcome.php
View::Display('welcome', ['x'=>1]);           // a one-shot output at the same spot (with optional data)
$html = View::Render('mail/body', ['order'=>$o]); // captured into a variable
```

### Pre-assigning data + reading it back

```php
View::_()->assignViewData('shop_name', 'DemoShop');
View::_()->assignViewData(['title'=>'首页', 'extra'=>1]);  // batch by array
$viewData = View::_()->getViewData();
```

> `_Show()` array-merges its own `$data` with the assigned data, then `extract`s — inside the view file these names are the variables.

### head/foot wrapping

```php
View::_()->setViewHeaderFooter('_layout/head', '_layout/foot');
View::_()->_Show(['name'=>'D'], 'another'); // hence: head, body, foot output in order
```

Note: `_Show` defaults the passed (object-level `$header_file`/`$footer_file`) to the current setting; without one only the body renders.

#### reset

After a cycle, call `View::_()->reset()` to clear the head/foot/data/view state, giving each request/test a zero state.

## Configuration example

```php
// config (usually in app options)
$viewOptions = [
    'path_view' => 'view',           // view/ 
];
```

Example: `View::Show(['count'=>$n],'list')` tries to include `<root>/view/list.php`; the template uses `$count` directly.

## Caveats

1. A missing view file does not error — include throws a warning / depends on the engine; keep the .php files properly placed in the structure.
2. A custom `path_view` allows multi-level paths like `mails/digest`.
3. Output is echoed directly: do not echo View values. To get a string, use `Render` (internal ob capture).
4. The static shells `Show/Display/Render` map one-to-one to the instance `_Show/_Display/_Render`; shells always act on the current phase instance.

## All options

```php
    public $options = [
        'path' => '',
        'path_view' => 'view',
        'view_skip_notice_error' => true,
    ];
```

## Methods

> All public/protected defined in the source (no static label means instance member). Static/non-static are interleaved in source order, with shells marked "static".

### Public methods

    public static function Show(array $data = [], ?string $view = null)
Static shell → `static::_()->_Show($data,$view)`; the most-used rendering entry (immediate output)

    public static function Display(string $view, ?array $data = null): void
Static shell → `static::_()->_Display(...)`

    public static function Render(string $view, ?array $data = null): string
Static shell: renders and captures the return string

    public function _Show(array $data, string $view)
(Core) If view_skip_notice_error, temporarily lowers E_NOTICE; resolves the view/head/foot files, merges data and extracts; includes in head → main view → foot order; restores the reporting boundary after rendering

    public function _Display(string $view, ?array $data = null): void
Outputs the given template as a single file (with merged data, excluding the 'this' key) and includes it

    public function _Render(string $view, ?array $data = null): string
Captures the "output" as a string: ob start → _Display → ob get contents → end

    public function reset()
Resets the instance: clears head/foot/view and temp files/old error level, giving every cycle a zero state

    public function getViewData(): array
Returns the currently assigned data array

    public function setViewHeaderFooter(?string $header_file, ?string $footer_file): void
Sets the header/footer templates for rendering wrap (view names; `_Show()` outputs header → body → footer in order)

    public function assignViewData($key, $value = null): void
Pre-assigns variables: an array (with $value null) merges wholesale, or a single $key=>$value

### Protected methods

    protected function getViewFile(?string $view): string
Completes a view name into `<path>/<path_view>/<name>.php` (not appended when `.php` is already there) and returns the absolute path; empty string when empty

## Related links

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md) — component init
- [DuckPhp\Core\App](Core-App.md) — the host layer's `_Show` view entry calls View
- [DuckPhp\Ext\CallableView](Ext-CallableView.md), [DuckPhp\Ext\JsonView](Ext-JsonView.md) — other view-style components
- Layering explanation: [Foundation\Controller](Foundation-Controller-Base.md); the page shell: see DuckPhpAllInOne's view_header/view_footer
- guide: [layers](../guide/layers.md)
