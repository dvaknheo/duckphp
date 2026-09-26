# DuckPhp\DuckPhpAllInOne

It merges App, the routing entry point, actions, callable views and the four business Helper groups into one class — good for demos, scaffolds or very small single-file applications.

## Introduction

`DuckPhpAllInOne extends DuckPhp` is the "the whole application is one class" version of the entry point:

- the application's actions (`action_*`), view callbacks (`view_*`), the welcome action and CLI self-registration are all written **in the same class**;
- `__callStatic` "embeds" the union of the four Helper layers into this class: when a static method that does not exist on this class is called, it looks for the first layer Helper that declares it in the order **System → Controller → Business → Model** and forwards the call, so actions can use the whole set of conveniences directly — `Setting()`, `Db()`, `POST()`, `Show()` and so on;
- the class adds no options array of its own; instead, at compile/runtime the parent `DuckPhp::$common_options` is overlaid with the keys injected by `embedMe()`, and `onInited()` decides whether header/footer wrapping is wanted.

Who it is for: the tutorial/playground style of "one file explains a whole demo application" (see sample1 in the README), or very small prototypes. Larger projects tend to extend `DuckPhp` and layer with `Foundation/*`.

Behaviour at a glance: this class is itself the welcome class (`controller_welcome_class` points at it); the root-path URL calls `action_index`, which then renders the `index` view; header/body/footer are assembled by `view_header()`/`view_index()`/`view_footer()` respectively.

## Class info

- Namespace: `DuckPhp`
- Declaration: `class DuckPhpAllInOne extends DuckPhp`
- Traits used: none (the Helper methods are dispatched by `__callStatic`)
- Union dispatch order (exactly the same as [DuckPhp\Foundation\Helper](Foundation-Helper.md)):

```php
public static function __callStatic($method, $args)
{
    $classes = [
        \DuckPhp\Foundation\System\SystemHelper::class,      // the first hit wins
        \DuckPhp\Foundation\Controller\ControllerHelper::class,
        \DuckPhp\Foundation\Business\BusinessHelper::class,
        \DuckPhp\Foundation\Model\ModelHelper::class,
    ];
    foreach ($classes as $class) {
        if (method_exists($class, $method)) {
            return $class::$method(...$args);
        }
    }
    trigger_error("Call to undefined method " . static::class . "::$method()", E_USER_ERROR);
}
```

The 12 cross-layer duplicate method names therefore settle their winner by that order (`ThrowOn` → System, the `Setting` group → Controller, `header/setcookie/exit` → System, `AdminService/UserService` → Controller); for the complete comparison table and "how it differs from the old `insteadof` form" see [DuckPhp\Foundation\Helper](Foundation-Helper.md#caveats).

There are also `protected $header_view='header'` and `protected $footer_view='footer'`, the template names (view names) `_Show()` uses to wrap the page with a header/footer.

## The keys embedMe / onInited use at runtime

This class has **no** `$options=[]` of its own; the switches below are either `array_merge`d into the options by embedMe in the constructor, or read in onInited:

| Key / purpose | Where it is written / used | Default / behaviour |
|---|---|---|
| `namespace_controller` | embedMe | the namespace the short name of this class lives in (with a leading `\\`). It makes the controller namespace that of this project. |
| `name` | embedMe | `'@'`, the Phase name is handled with the class name. |
| `controller_welcome_class` | embedMe | this class's `static::class` (the welcome/root action is the class itself). |
| `controller_class_postfix` | embedMe | `''` (this class name already contains Controller, so no postfix is appended). |
| `controller_method_prefix` | embedMe | `'action_'`. |
| `cli_enable` | embedMe | true. |
| `path_info_compact_enable` | embedMe | true (compact URL mapping). |
| `duckphp_all_in_one_wrap_header_footer` | embedMe/onInited | true; when truthy, header_view/footer_view take part in the `_Show` assembly. |
| header_view / footer_view | set by `onInited()` according to wrap | `'header'`/`'footer'`. |
| cmd | `onPrepare()` | registers `static::class` and (when cli_command_with_common) `DuckPhp\Component\Command` as command entry points. |

> Every other option that works (db/redis/route rewriting…) comes from the parent `DuckPhp::$common_options` plus the Kernel's `kernel_options`: see `DuckPhp.md` / `Core-KernelTrait.md`.

## Usage

```php
use DuckPhp\DuckPhpAllInOne;

class MyApi extends DuckPhpAllInOne {
    public $options = [
        'path' => __DIR__,
        'is_debug' => false,
    ];

    // action
    public function action_hello() { $this->_Show(['m'=>'Hi'], 'hello'); }

    // view callback
    public function view_hello($data) { echo 'hello '.__h($data['m']); }
}
MyApi::RunQuickly([]);
```

So the built-in templates `view_header()/view_index()/view_footer()` are available, but you do not have to follow them: defining `view_{name}` in a subclass makes it the view callback of the `name` view.

## Configuration example

```php
class Tiny extends \DuckPhp\DuckPhpAllInOne {
    public $options = [
        'namespace'  => 'Tiny',
        'path'       => __DIR__.'/..',
    ];
    // optional actions and view_* …
}
Tiny::RunQuickly([]);
```

Note: any method in the class whose name starts with `action_` is collected as a "routable action"; to reach business data inside a method body, use the merged-in helpers directly (`Db()/Setting()/…` work the same as with inheritance).

## Caveats

1. Conveniences such as DB / Setting / Session come from `__callStatic` dispatching to the four Helper layers (not from trait composition): to change the behaviour of one method, **override it on this class** with the same name, or change the matching layer Helper; this class no longer carries the 10 `$EVENT_*` static properties itself (they live on `Business\BusinessHelper` / `Controller\ControllerHelper`).
2. The welcome class is `static::class`: internally, routing treats an empty URL as a call to this class's `action_index`.
3. With `duckphp_all_in_one_wrap_header_footer=false`, `_Show` still matches a direct callback that does not wrap a header/footer.
4. The class adds no options: for the full generic configuration, it still lands at the parent layer (see the links below).
5. Reflection cannot see the 96 dispatched methods (`method_exists` is false); the source carries 96 `@method` annotations for the IDE.

## Built-in view methods usable for display (as template samples)

`view_header($data)` outputs `<html>…<body>` and `view_footer($data)` outputs `</body></html>`; the welcome home page `view_index($data)` prints "`class name` main page work at…" — any of these can be overridden in a subclass with a method of the same name.

## Methods

> Only the methods this class overrides/adds in `DuckPhpAllInOne.php` are listed; the parent `DuckPhp`/`Core` shells (RunQuickly, Setting and so on) are on their own pages and are not repeated.

### Public methods

    public static function __callStatic($method, $args)
The entrance to the four-Helper union: it looks for the first layer Helper where `method_exists` is true in the order System → Controller → Business → Model and forwards the call; if none has it, `trigger_error(..., E_USER_ERROR)`. The signatures of the 96 dispatchable methods are on [Foundation\Helper](Foundation-Helper.md) and the four layer-Helper pages.

    public function __construct()
It calls embedMe() first to inject the defaults (welcome class = this class/action_/wrap and so on), then parent::__construct()

    public function onInited(): void
If duckphp_all_in_one_wrap_header_footer is truthy → header_view='header' and footer_view='footer' take effect (otherwise no wrapping)

    public function action_index()
The root action: by default it renders the 'index' view with the currently visible variables as data:

    public function _Show(array $data, string $view = '')
The view window: views become class-method callbacks view_; when the match fails the parent continues; otherwise it outputs in the order header → body → footer

    public function view_header($data)
The built-in header string (<html><head>…<body>)

    public function view_index($data)
The built-in welcome body: it prints the class name main page …, plus the implementation time for scanning views

    public function view_footer($data)
The built-in footer (</body></html>)

### Protected methods

    protected function embedMe(): void
The constructor injects ext_options (namespace_controller\\ then namespace/name @/welcome=this class/clear postfix/method action_/cli/path compact/wrap), and merges them into options

    protected function onPrepare(): void
After the parent's prepare, it registers `static::class` (and the `Command` decided by `cli_command_with_common`) into `options['cmd']`

    protected function viewToCallback(?string $func): ?\Closure
Turns a view name (which may contain `/`→`_`) into [$this,'view_'+…], checks is_callable, and wraps it as a Closure when it is

## Related links

- [DuckPhp\DuckPhp](DuckPhp.md) — the parent class; the generic options/component assembly comes from there
- [DuckPhp\Core\App](Core-App.md) / [Core-KernelTrait](Core-KernelTrait.md)
- The four Helper layers (`__callStatic`'s dispatch targets): [System\SystemHelper](Foundation-System-SystemHelper.md), [Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md), [Business\BusinessHelper](Foundation-Business-BusinessHelper.md), [Model\ModelHelper](Foundation-Model-ModelHelper.md); for the union see [DuckPhp\Foundation\Helper](Foundation-Helper.md)
- [DuckPhp\Ext\CallableView](Ext-CallableView.md) (this class builds the same capability in as view_)
- guide: [quickstart](../guide/quickstart.md), [layers](../guide/layers.md)
