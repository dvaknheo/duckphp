# 3-6 The installer and the web install flow

> What this solves: in DuckPHP, "installation" is really three different things — creating a project, tracking install state, and running an install wizard. This chapter keeps them apart.
> Prerequisites: [Chapter 1-2 Installation and minimal example](install.md), [Chapter 3-2](mount-app.md). About 10 minutes.

## Three kinds of "install" — do not mix them up

| # | What it is | What you use |
|---|---|---|
| ① | **Create a new project** (scaffold) | `vendor/bin/duckphp new` ([`DuckPhpInstaller`](../reference/Ext-DuckPhpInstaller.md)), with the template coming from the repository's `skeleton/` |
| ② | **The app's own install state** | The `installed` + `url_install` options, guarded with `Helper::checkInstall()` |
| ③ | **Run the install wizard in a browser** | The [`RouteHookWebInstaller`](../reference/Ext-RouteHookWebInstaller.md) extension, which takes over the install URL and renders the wizard |

## ① Creating a project with the scaffold

```bash
composer require dvaknheo/duckphp
php vendor/bin/duckphp new      # interactive; generates the project from skeleton/
php vendor/bin/duckphp show     # show current install/configuration state
php vendor/bin/duckphp help     # three commands: new / show / help
```

The generated structure (`public/index.php` and `bin/cli.php` usually need no changes):

```
project/
├── public/index.php      ├── config/          ├── view/{main.php,_sys/}
├── bin/cli.php           ├── src/{Controller,Business,Model,System}/
└── runtime/              └── composer.json
```

## ② Install state: `installed` plus one guard

```php
class MyApp extends DuckPhp
{
    public $options = [
        'installed' => false,      // not installed yet
        'url_install' => 'install',// where to go and install while it is not ready
    ];
}

// to block an entry point until the app is installed (commonly the very front of a controller/middleware-style hook)
Helper::checkInstall();            // installed=false -> 302 to url_install, then exit
```

It does just three lines ([reference manual Core-App](../reference/Core-App.md)):

```php
public function checkInstallToPage(?string $url_install = null): void
{
    $url_install = $url_install ?? ($this->options['url_install'] ?? 'install');
    if (!$this->options['installed']) {
        SystemWrapper::_()->_header('location: '.Route::Url($url_install), true, 302);
        SystemWrapper::_()->_exit();
    }
}
```

The measured assertions in `ZThirdDemo` (the first line of the `/install` action is `Helper::checkInstall()`):

| Condition | Result |
|---|---|
| `installed = false` | emits one `location: …/install` header + calls `exit` once (the request ends right there) |
| `installed = true` | does nothing and carries on |

> ⚠️ **Do not replace `exit` inside [`SystemWrapper`](../reference/Core-SystemWrapper.md) and then rely on "the code after it will not run"**: in the tests we swapped `exit` for an empty function, so the lines after `checkInstall()` **do run** (`ZThirdDemoTest` asserts exactly that). In production `exit` really exits.

## ③ The web install wizard

Install the extension and it takes over the `url_install` address:

```php
'ext' => [
    \DuckPhp\Ext\RouteHookWebInstaller::class => [
        'web_installer_use_database' => true,
        'web_installer_use_redis' => true,
        'web_installer_database_drivers' => ['sqlite' => true, 'pgsql' => true],
        'web_installer_view' => '',                  // custom wizard view (empty = built-in)
        'web_installer_force' => false,              // true overwrites an existing config
        'web_installer_check_custom_callback' => null,  // decide "may it install"
        'web_installer_do_custom_callback' => null,     // actually perform the install
        'web_installer_render_custom_callback' => null, // render the page yourself
        'web_installer_default_sentences' => [],        // wording overrides (merged into Lang)
    ],
],
```

The flow:

```
GET /install
  ├─ the hook compares url_install (normalised through __url(), so it matches with or without index.php)
  ├─ installed (installed=true) -> 302 away
  └─ not installed -> collect database/Redis parameters -> write the config -> set installed
```

The wizard page is rendered by the built-in view [`Ext\RouteHookWebInstallerView`](../reference/Ext-RouteHookWebInstallerView.md) — a **pure view file** (it defines no class), which the extension `include`s in `show()`. To change the look, copy it out, edit it and point `web_installer_view` at it; to add fields only, use `web_installer_view_block_custom`.

The three callbacks cover "can it install / how does it install / how is it shown" — fill in only the one you need; the wording goes through [`Lang`](../reference/Component-Lang.md), so the wizard is multilingual (Chapter 2-15).

## Go-live checklist

- [ ] Set `installed` to `true` (otherwise production keeps jumping to the install page).
- [ ] Either remove the install extension or block it at the web layer by IP/auth (it can write config, so it is a sensitive entry point).
- [ ] Delete the scaffold's one-off example files (`SomeAction`, `testController`, `DemoBusiness`…); do not ship the examples.
- [ ] `runtime/` must be writable; `config/` must not be exposed under the web root.
- [ ] Point the document root at `public/` (Chapter 1-7).

## Common errors

| Symptom                       | Cause                                                 | Fix                                                              |
| ------------------------ | -------------------------------------------------- | --------------------------------------------------------------- |
| Every page jumps to the install page | `installed` is still `false`                             | Set it to `true`, or move the guard to the entry point you really want to block |
| Code after `checkInstall()` still runs | You replaced `exit` in a test                                    | That is test behaviour; production really exits — do not rely on "nothing after it runs" |
| The wizard page 404s | The extension is not installed, or `url_install` does not match the address you visit | Check that `ext` contains `RouteHookWebInstaller` and that the address matches `url_install` |
| The wizard cannot write the config | `config/` is not writable, or `web_installer_force=false` and the file already exists | Grant write permission; use `force=true` to overwrite |
| You want your own install page | You do not want the built-in wizard | Use `web_installer_view` or `web_installer_render_custom_callback` |
|                          |                                                    |                                                                 |

## Related references

- [DuckPhp\Ext\DuckPhpInstaller](../reference/Ext-DuckPhpInstaller.md), [DuckPhp\Ext\RouteHookWebInstaller](../reference/Ext-RouteHookWebInstaller.md), [DuckPhp\Ext\RouteHookWebInstallerView](../reference/Ext-RouteHookWebInstallerView.md)
- [DuckPhp\Core\App](../reference/Core-App.md)'s `checkInstallToPage()`, `installed`, `url_install`
- Chapter 1-7, the [minimal go-live checklist](deployment.md)
