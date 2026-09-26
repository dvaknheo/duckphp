# 2-6 Views and templates

> What this solves: where view files live and how they are found; how to add a header and footer (a layout); how data gets in; how to escape output; and the ways to write views that are not PHP files.
> Prerequisites: [Chapter 2-1 The four-layer architecture and calling rules](layers.md), [Chapter 2-5 Controllers](controllers.md). About 20 minutes.
> Examples: `skeleton/view/main.php` (the smallest view), `demo/view/` (including the error pages in `_sys/`), `tests/data_for_tests/ZThirdDemo/view/shop/index.php` (an overridden view).

```bash
php -S 127.0.0.1:8080 -t demo/public
# open http://127.0.0.1:8080/demo.php to see the result (demo.php uses "function views", see §5)
```

## Minimal example

The controller (`skeleton/src/Controller/MainController.php`):

```php
public function index()
{
    $var = __h(DemoBusiness::_()->foo());
    Helper::Show(get_defined_vars(), 'main');   // view name 'main'
}
```

The view (`skeleton/view/main.php`):

```php
<h1><?= $var ?></h1>
```

That is the whole thing: `main.php` under `path_view` (default `view/`) is included, and the variables the controller passed are `extract()`ed into the view as `$var`.

## How it works

### 1. How a view file is located

The logic in [`View::getViewFile()`](../reference/Core-View.md) is short:

1. if the view name has no `.php` suffix, append it (`main` → `main.php`);
2. hand it to `getOverrideableFile($options['path_view'], $file)` — `path_view` defaults to `'view'` (relative to the project root);
3. that lookup **falls back phase by phase**: a child app's view can be overridden by the parent app at `view/<child app name>/xxx.php` ([Chapter 3-5](overriding.md)).

Where the view name comes from:

| Scenario | View name |
|---|---|
| `Helper::Show($data, 'note/show')` | `note/show` → `view/note/show.php` |
| `Helper::Show($data)` (omitted) | the current route path, e.g. `/about/me` → `about/me` |
| The error page `error_404 => '_sys/error_404'` | `view/_sys/error_404.php` |

> Mind the edge case of "omitting the view name": for the root path `/` the route path is **`Main/index`** (filled in by the welcome-class rule), so omitting the view name on the root path looks for `view/Main/index.php`. To render `view/main.php`, write the view name explicitly.

### 2. The render flow: header → view → footer

What `View::_Show($data, $view)` does:

```
1. view_skip_notice_error defaults to true -> E_NOTICE is suppressed while rendering (undefined variables stop spamming)
2. resolve the three file paths: view / header_file / footer_file
3. $this->data = array_merge($this->data, $data)   <- data preset by assignViewData plus what was passed in now
4. extract($this->data)  -> the array keys become variables in the view
5. include the header view -> include the main view -> include the footer view
```

The three entry points:

| Method | Header/footer | Returns | Use it for |
|---|---|---|---|
| `Helper::Show($data, $view)` | yes | outputs directly | ordinary pages |
| `Helper::Render($view, $data)` | **no** | a string | emails, fragments, further processing |
| `View::_()->_Display($view, $data)` | no | outputs directly | minimal output |

### 3. Header and footer: `setViewHeaderFooter()`

```php
public function __construct()
{
    Helper::setViewHeaderFooter('header', 'footer');   // view names, likewise relative to path_view
}
```

From then on every `Helper::Show()` renders `view/header.php` → the main view → `view/footer.php`. That is exactly what `MainController::__construct()` in `demo/public/demo.php` does (its header/footer are the function views `Views::header()` / `Views::footer()`).

### 4. Passing data and escaping

```php
Helper::Show(get_defined_vars(), 'main');          // (1) every variable in the current scope
Helper::Show(['notes' => $notes], 'note/list');    // (2) an explicit array
Helper::assignViewData('site_name', 'MyProj');     // (3) preset (carried by every Show)
```
`assignViewData()` and `Show()`'s `$data` end up in **the same place**: the `$data` property of the [`View`](../reference/Core-View.md) component (`View::_()`, one singleton per phase). `Helper::assignViewData($k, $v)` is just `View::_()->data[$k] = $v` (pass an array to merge); `Show($data, $view)` does `array_merge($this->data, $data)` before rendering, so **a same-named key in the argument overrides the preset value**.

Precisely because it is "a property on a component", three things deserve care:

- **preset values are phase-level shared state**: once written, **every later render in the same phase** carries them (header/footer views included, and same-phase child apps' views too). Good for things that are the same site-wide, such as `site_name` or the current user; request-specific data belongs in `Show()`'s `$data` argument.
- **do not put sensitive data there**: every view can see it; `__h()` solves escaping, not "should this view see it".
- **to clean up**: `View::_()->reset()` (clears `$data` and the header/footer settings); or simply rely only on each action's own `$data`.
Reading data in a view is reading a variable (`$notes`). **Escaping is mandatory**, and the framework provides the global functions:

| Function | Use |
|---|---|
| `__h($str)` | HTML escaping (XSS defence) — always use it when outputting user data |
| `__url('note/show')` | A site URL (still correct when deployed into a subdirectory) |
| `__res('app.css')` | A static resource URL ([Chapter 3-3](static-resources.md)) |
| `__l('hello')` / `__hl('hello')` | Translate / translate and escape ([Chapter 2-15](i18n.md)) |
| `__json($data)` | JSON-encode and output |

```php
<h1><?= __h($note['title']) ?></h1>
<a href="<?= __url('note/show?id=' . (int)$note['id']) ?>">details</a>
<img src="<?= __res('img/logo.png') ?>">
```

Inside a view you may **use global functions only** — do not query the database and do not call Business; that is explicitly forbidden by the violation matrix in [Chapter 2-1](layers.md).

### 5. The view implementation can be replaced (all three alternatives live in `Ext\`)

The framework's `View` is a singleton that another implementation can replace (an extension does `View::_(static::_())` in its own `init()`, and each ships a `*_skip_replace` switch):

- [`Ext\CallableView`](../reference/Ext-CallableView.md): use **functions/methods** as views — once attached, `Helper::Show($data, 'main_view')` calls `MySpace\View\Views::main_view($data)`;
- [`Ext\EmptyView`](../reference/Ext-EmptyView.md): the view name is the string to output (placeholders/degradation);
- [`Ext\JsonView`](../reference/Ext-JsonView.md): output the data as JSON directly.

Their option tables, wiring and full examples are in [Chapter 4-13](ext-classes.md) §8 — these are `Ext\*` extensions and are **not assembled automatically**.

### 6. Views can be overridden too

The same view name may exist once per child/parent app; the lookup **falls back by phase** (`getOverrideableFile()`). `tests/data_for_tests/ZThirdDemo` has a ready example: `third/view/shop/index.php` is overridden by the main app's `view/shop/index.php`; for the rules and troubleshooting see [Chapter 3-5](overriding.md).

## Common patterns

**① Split the header/footer and write the page skeleton once** (see §3).

**② A list plus pagination** (the paging markup comes from `Helper::PageHtml()`, [Chapter 2-7](database.md))

```php
<table>
<?php foreach ($notes as $note): ?>
    <tr><td><?= __h($note['title']) ?></td></tr>
<?php endforeach; ?>
</table>
<?= Helper::PageHtml($total) ?>
```

**③ Fragments and emails with `Render()`**

```php
$row = Helper::Render('note/row', ['note' => $note]);   // a fragment
$body = Helper::Render('mail/welcome', ['user' => $user]); // an email body
```

**④ Pass fewer layout variables: preset the common ones with `assignViewData()`**

```php
// in the controller's constructor
Helper::assignViewData(['site_name' => 'MyProj', 'year' => date('Y')]);
```

**⑤ Error pages are ordinary views**

`demo/view/_sys/error_404.php`, `_sys/error_500.php` and `_sys/error_maintain.php` are ordinary view files, named by the `error_404`/`error_500`/`error_maintain` options ([Chapter 2-12](exception.md)).

## Common errors

| Symptom                                         | Cause                                                    | Fix                                        |
| ------------------------------------------ | ----------------------------------------------------- | ----------------------------------------- |
| The view is not found (include fails / blank page)                       | The view name does not match the file path, or the file is not under `path_view`                         | view name = the path relative to `view/`; check that `view/<name>.php` exists |
| Omitting the view name on the root path reports a missing `Main/index`                 | The root path's route path is the welcome class's `Main/index`                         | Pass the view name explicitly (e.g. `Helper::Show($data, 'main')`)   |
| `$var` in a view is `null` without any error                   | `view_skip_notice_error` defaults to `true`, so undefined variables are only notices | Check that the variable really reaches `Show()`; turn that option off temporarily while debugging           |
| Unescaped user input appears on the page                              | You output it with a bare `<?= $title ?>`                                 | Always `<?= __h($title) ?>`                   |
| Links inside views 404 after deploying into a subdirectory                          | The view hard-codes `/note/show`                                    | Use `__url('note/show')`                    |
| A page produced by `Helper::Render()` has no header/footer               | `Render()` **has no** header/footer (it renders a single view)                          | Use `Show()` for pages; if you really want a layout, compose it yourself or include the views |
| `CallableView` is enabled but `Show()` still looks for `.php` files | `callable_view_class` is unset, or the extension is not in `ext`                 | Configure both (see §5)                               |
| Business logic inside a view, so every change touches the template                           | The classic layer violation                                               | Prepare data in the controller/Business; the view only displays                  |

## Next steps

- [Chapter 2-7 Databases](database.md) and [Chapter 2-8 The model layer](model.md): preparing the data a view needs.
- [Chapter 2-15 Internationalisation and wording](i18n.md): `__l()` / `__hl()` inside views.
- [Chapter 3-3 Static resources and the document root](static-resources.md): `__res()` and the resource directories.
- [Chapter 3-5 Overriding and replacement](overriding.md): the complete view-level override rules.
- Reference manual: [DuckPhp\Core\View](../reference/Core-View.md); the three replacements have their own pages, see [Chapter 4-13](ext-classes.md) §8.
