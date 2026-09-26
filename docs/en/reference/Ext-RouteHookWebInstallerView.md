# DuckPhp\Ext\RouteHookWebInstallerView

## Introduction

`RouteHookWebInstallerView` is the **built-in install view** of `RouteHookWebInstaller`. Note that this file **defines no PHP class or function**: it is a view template mixing HTML and PHP, which `RouteHookWebInstaller` brings in after `extract($data)` when rendering `show()` (see the comment at the top of the file).

What the page does:

- installed (`$installed` non-empty): shows "Install Complete" and redirects to the home page after 5 seconds;
- not installed: shows the environment check table (`$checks`), the database/Redis configuration forms (with the `controller_resource_prefix` hint), the `web_installer_force` checkbox and the custom block.

Every UI string goes through `__hl('webinstaller.*')` for translation (the default sentences come from `RouteHookWebInstaller::builtin_default_sentences`, and can be overridden by `config/lang-{locale}-for_webinstaller.php` or the main language file).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: none (a pure view file, no class/interface/trait)

## Usage

You normally do not reference this file directly: configure `RouteHookWebInstaller` (leaving `web_installer_view` empty) and the built-in view is used.

```php
// example App option:
'ext' => [\DuckPhp\Ext\RouteHookWebInstaller::class => true],
```

To customise the look, copy the built-in file out, modify it and point `web_installer_view` at it; to append custom fields to the form, use `web_installer_view_block_custom`.

## Caveats

- The view data is injected by the host: `$title`, `$installed`, `$checks`, `$controller_resource_prefix`, the configuration table entries and so on; do not touch framework APIs directly in this file.
- The sentence keys are uniformly `webinstaller.*`; passing an array to `web_installer_default_sentences` overrides the default English sentences.
- The file follows the "braces for control structures, HTML keeps its own indentation" style (see the header comment).

## Methods

This file is a view template and has no methods.

## Related links

- [DuckPhp\Ext\RouteHookWebInstaller](Ext-RouteHookWebInstaller.md) — the host (it owns the data and the rendering)
- [DuckPhp\Component\Lang](Component-Lang.md) — where `__hl()` translations come from
