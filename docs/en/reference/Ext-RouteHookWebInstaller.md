# DuckPhp\Ext\RouteHookWebInstaller

## Introduction

`RouteHookWebInstaller` is the **web installation wizard**: while the application has not been installed, it leads the visitor to a form page where the user fills in the database/Redis configuration and "installs in one click" (writing the configuration and creating the tables). It hangs off Route's `prepend-inner`, for the framework's web installation scenario (complementing the CLI `DuckPhpInstaller`).

The main flow: `_Hook` decides "is installation needed / is this an installation request" → `installAction()` handles the form (environment checks → an optional custom callback → writing the configuration → running the table-creation SQL) → `show()` renders the installation view (the built-in view `RouteHookWebInstallerView` by default, with the wording going through `__hl('webinstaller.*')` for multi-language).

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class RouteHookWebInstaller extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Meaning |
|---|---|---|
| `web_installer_use_database` | `true` | Whether the installation flow includes the database. |
| `web_installer_use_redis` | `true` | Whether the installation flow includes Redis. |
| `web_installer_database_drivers` | `['sqlite'=>true,'pgsql'=>true,'duckdb'=>false]` | The selectable database drivers. |
| `web_installer_view` | `''` | A custom installation view file (empty uses the built-in view). |
| `web_installer_view_block_custom` | `null` | A custom block template (embedded in the view). |
| `web_installer_force` | `false` | Whether to force a reinstall (cleaning the tables first). |
| `web_installer_check_custom_callback` | `null` | A custom "environment check" callback. |
| `web_installer_do_custom_callback` | `null` | A custom "installation execution" callback. |
| `web_installer_render_custom_callback` | `null` | A custom "render" callback. |
| `web_installer_default_sentences` | `[]` | Interface wording overrides (empty uses the built-in English default wording `builtin_default_sentences`). |

## Usage

```php
\DuckPhp\Ext\RouteHookWebInstaller::_()->init([
    'web_installer_use_database' => true,
    'web_installer_use_redis'    => false,
    'web_installer_database_drivers' => ['sqlite' => true],
], $app);
// visiting the site while it is not installed → shows the installation form; submitting → environment checks + creating the database and tables + writing the configuration
```

## Caveats

- The wording system: every UI text in the view uses `__hl('webinstaller.*')`; the default sentences come from `builtin_default_sentences`, and `init()` imports them into Lang through `Lang::_()->loadLanguageFrag('for_webinstaller', …)`, so every language can supply wording for the installation page with `config/lang-{locale}-for_webinstaller.php`. A real translation wins: main language file > that frag file > the default sentence.
- The installation action: `checkEnv()` first (PHP extensions and so on) → Redis/database checks → the custom callback (when configured) → writing the configuration (through `ExtOptionsLoader` and so on) → running `{driver}.sql` to create the tables (`doSchema`/`executeSqlFile`); with `web_installer_force` it runs clean first.
- For the details of the installed/not-installed decision and the redirects, the source's `_Hook`/`installAction` are authoritative; this component is mainly aimed at the framework's built-in installation-page scenario.

## Methods

### Public methods

    public static function Hook($path_info)
The static hook entry point, forwarding to `_Hook`.

    public function init(array $options, ?object $context = null)
Initialisation (the parent flow plus importing the default wording).

    public function _Hook($path_info)
The route-hook body: it decides from "is this the installation page/an installation request/already installed" whether to show the wizard or let it through.

    public function installAction()
Handles the installation form submission: checks → writing the configuration → running the table-creation SQL.

    public function installBusiness()
The installation business entry point (called internally by installAction).

### Protected methods

    protected function initContext(object $context): void
Hangs `Hook` on Route's `prepend-inner`.

    protected function getInstallPath()
Computes the installation configuration path.

    protected function buildPageData()
Assembles the view data (environment check items, configuration items and so on).

    protected function renderCustom()
Runs `web_installer_render_custom_callback`.

    protected function checkRootHasRedis()
Checks whether the root application has Redis configured.

    protected function checkRootHasDatabase()
Checks whether the root application has a database configured.

    protected function checkCustom()
Runs `web_installer_check_custom_callback`.

    protected function doCustom()
Runs `web_installer_do_custom_callback`.

    protected function checkEnv()
The environment check (returns the list of check items).

    protected function checkRedis()
Checks/tests the Redis connection.

    protected function getEnabledDatabaseDrivers()
Returns the list of available database drivers.

    protected function checkDatabase()
Checks/tests the database connection.

    protected function makeDsn()
Builds the DSN from the form.

    protected function getSchemaSqlFile()
Locates the table-creation SQL file.

    protected function doSchema()
Runs the table creation (cleaning first when force is on).

    protected function executeSqlFile()
Runs the SQL file.

    protected function getCurrentDriver()
Gets the current driver name.

    protected function executeSql()
Runs a single SQL statement.

    protected function show()
Renders the installation page (the built-in view by default).

## Related links

- [DuckPhp\Ext\RouteHookWebInstallerView](Ext-RouteHookWebInstallerView.md) — the built-in installation view
- [DuckPhp\Ext\DuckPhpInstaller](Ext-DuckPhpInstaller.md) — the CLI installer
