# Application settings (Setting)

> `options` governs "**how the framework runs**" (behaviour switches); `setting` governs "**what this environment is**" (database passwords, Redis addresses, the platform marker, the debug and maintenance state).
> Settings **do not take part** in the `$options` merge and are read with `App::Setting()`; for who wins between the two, see the end of this page.

## Introduction

Settings are the configuration layer that "follows the environment and normally stays out of version control". It has three sources, merged in order (the later one wins), and it is **loaded only once, in the root application**: child applications and child Phases all read the root application's copy.

## Where settings come from (three-step merge)

| Order | Source | When it is read | Note |
|---|---|---|---|
| 1 | the `setting` option (an array) | always | `[]` by default in `App::$core_options`; just write the array in the application options |
| 2 | `<path>/.env` | when `use_env_file` is true | `parse_ini_file()` reads the `.env` in the root directory (INI syntax) |
| 3 | `<path>/<setting_file>` | when `setting_file_enable` is true | `require` that file, which must `return` an array; `setting_file` defaults to `config/DuckPhpSettings.config.php` and may be an absolute path |

- When the step-3 file does not exist: with `setting_file_ignore_exists` true (the default) it is **skipped silently**, and with it false an `ErrorException('DuckPhp: no Setting File')` is thrown.
- The implementation all lives in `App::loadSetting()` (plus `dealWithEnvFile()` / `dealWithSettingFile()`).
- ⚠️ **Loaded only in the root application**: `App::onPrepare()` calls `loadSetting()` only after checking `is_root`; child applications and child Phases **do not load it again** and always read the root application's copy.

## How to read them

```php
$dsn  = App::Setting('database_list');     // one key; null when it is absent
$all  = App::Setting();                    // no key → the whole setting array
$home = App::Setting('my_home', '/');      // with a default
```

- The static entry point is `App::Setting($key = null, $default = null)`; the instance form is `App::_()->_Setting(...)`.
- The implementation is `static::Root()->setting[$key] ?? $default` —— it **always reads the root application's settings**, so the same values are available inside a child Phase.
- The Helpers have it too: `Helper::Setting()` (provided by both [Foundation\Business\BusinessHelper](Foundation-Business-BusinessHelper.md) and [Foundation\Controller\ControllerHelper](Foundation-Controller-ControllerHelper.md)), so the business/controller layers need not depend on `App` directly.

## Setting keys the framework itself understands

<!-- GEN:settingkeys start -->
| Setting key | Who reads it | What it does |
|---|---|---|
| `duckphp_is_debug` | Core\App::_IsDebug() | The debug switch; OR-ed with the `is_debug` option. |
| `duckphp_platform` | Core\App::_Platform() | The platform marker, read out by App::Platform(). |
| `duckphp_is_maintain` | Core\App::prepareServe() | Maintenance mode; OR-ed with the `is_maintain` option, and when truthy the maintenance page is output straight away. |
| `database_list` | Component\DbManager | The database connection list (`dsn/username/password/driver_options`); `database_list_reload_by_setting` together with "the option was not given" decides whether it is used. |
| `database` | Component\DbManager | A shorthand for a single database connection; it takes effect when `database_list_try_single` is true. |
| `redis_list` | Component\RedisManager | The Redis connection list; `redis_list_reload_by_setting` together with "the option was not given" decides whether it is used. |
| `redis` | Component\RedisManager / Ext\RouteHookWebInstaller | A shorthand for a single Redis connection; it takes effect when `redis_list_try_single` is true. |
<!-- GEN:settingkeys end -->

> Apart from the table above, **every other key belongs entirely to your project** (`Setting('my_key')`); the framework neither touches nor validates it.

## Related options

<!-- GEN:settingoptions start -->
| Option | Default | Description |
|---|---|---|
| `setting` | `[]` | Application settings (setting) given directly as an array; the starting point of `loadSetting()`, then `.env` (optional) and the settings file are merged. |
| `setting_file` | `'config/DuckPhpSettings.config.php'` | Settings file (relative to root or absolute). |
| `setting_file_enable` | `true` | Whether to load the settings file. |
| `setting_file_ignore_exists` | `true` | Whether to ignore a missing settings file (no error thrown). |
| `use_env_file` | `false` | When true, loadSetting also reads the root `.env` (INI) and merges it. |
<!-- GEN:settingoptions end -->

- writing an array in `setting` is "step 1", equivalent to writing a setting file (just without a file outside version control);
- to make the setting file optional: keep `setting_file_ignore_exists = true` (the default);
- to keep passwords in `.env`: set `use_env_file` true.

## Who wins: `options` or `setting`

| Scenario | Rule |
|---|---|
| the debug switch | `Setting('duckphp_is_debug')` **or** `options['is_debug']` —— whichever is true wins (`App::IsDebug()`) |
| maintenance mode | `Setting('duckphp_is_maintain')` **or** `options['is_maintain']` —— when truthy the maintenance page is output straight away |
| the platform marker | only `duckphp_platform` in the settings is honoured (`App::Platform()`) |
| database / Redis | **the option wins**: a non-empty `options['database_list']` (or `options['database']`) is used; only when it is empty and `database_list_reload_by_setting` (Redis: `redis_list_reload_by_setting`) is true does it fall back to the settings |
| what the installer writes | after a successful installation `RouteHookWebInstaller` **writes `database_list` / `redis_list` into the setting file** and sets the matching `*_reload_by_setting` to false, so the options cannot override them |

## Environment separation and deployment

- The setting file **normally does not go into git**: the header of `skeleton/config/DuckPhpSettings.config.php` in the scaffold literally says `Do no save me in git`.
- `.env` uses INI syntax (`parse_ini_file`) and is a good place for passwords; a leading `#` is a comment.
- For production: set `installed` true and switch `is_debug` off (turning it off in either the option or the setting is not enough, since the two are OR-ed — **to switch it off, switch it off in both**).
- Deployment details are in the configuration / deployment chapters of the user guide; this page covers only the mechanism and the keys.

## Real samples in this repository

| File | Purpose |
|---|---|
| `skeleton/config/DuckPhpSettings.config.php` | the template the scaffold generates (`duckphp_*` and `database_list`/`redis_list` are all commented out) |
| `demo/config/DuckPhpSettings.config.php` | used by the demo application |
| `tests/data_for_tests/setting.sample.php` | the sample used by tests (two databases in `database_list`, plus `redis_list`) |

## Related links

- [Application options overview (home)](options.md) —— the `options` set
- [Application options (by class)](options-by-class.md) / [Application options (A-Z index)](options-index.md)
- [DuckPhp\Core\App](Core-App.md) —— `loadSetting()`, `Setting()` and the `setting*` options
- [DuckPhp\Component\DbManager](Component-DbManager.md) / [DuckPhp\Component\RedisManager](Component-RedisManager.md) —— who reads `database_list` / `redis_list`
