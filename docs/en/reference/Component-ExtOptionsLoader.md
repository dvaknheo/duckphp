# DuckPhp\Component\ExtOptionsLoader

Persists runtime options that must survive across processes / long-term runs (such as is_debug, installed, database/redis selection) into a JSON file (default `runtime/DuckPhpData.config.json`), and "bumps" them into the current app options on the root / phase side.

## Introduction

`ExtOptionsLoader` handles a class of dynamic options that "must be remembered and still take effect on the next run" (common in web-ified CLI, e.g. `php xx debug --on / --off`).

Workflow:

- at root (root app) init, `loadAllOptions()`: reads `runtime/DuckPhpData.config.json` (may be absent/empty) into `all_ext_options`;
- data is organized with "phase" as the key: `app options data and its (allowed) keys`; use `root_get_options_by_phase($phase)` to get the current Phase's segment;
- `bumpOptions($ext_options)`: writes the fetched segment directly into `$app->options['data']`, and copies back into the main options per the `data_file_bump_keys / prefix_keys` rules (such as `is_debug`, `installed`, `redis`, `database`, and the `redis_*` / `database_*` prefixes);
- `saveExtOptions($options)`: writes a new options segment (with `__class__` + timestamp) back to JSON via root, then bumps it into effect immediately.

The entry-point semantics are wired up by the `DuckPhp` component when `data_file_enable` is on: `Component\ExtOptionsLoader` (for the registration inside DuckPhp/DuckPhp see its initComponents).

## Class info

- Namespace: `DuckPhp\Component`
- Declaration: `class ExtOptionsLoader extends ComponentBase`

## Options

`ExtOptionsLoader::$options`:

| Option | Default | Description |
|---|---|---|
| `data_file_enable` | true | Whether to enable the persisted external options file mechanism (decided by the upper layer doing the wiring). |
| `data_file_json_file` | `'DuckPhpData.config.json'` | Data file name (relative to root runtime, or absolute). |
| `data_file_bump_allowed` | true | Whether bumping is allowed to write the segment back into app options. |
| `data_file_bump_keys` | `['installed'=>true,'redis'=>true,'database'=>true,'local_redis'=>true,'local_database'=>true]` | These keys may be written back wholesale. |
| `data_file_bump_prefix_keys` | `['redis_'=>true,'database_'=>true]` | Any key under these prefixes is written back. |

## Usage

```php
use DuckPhp\Component\ExtOptionsLoader;

ExtOptionsLoader::_()->init([], App::_());
// take an external segment and bump it into effect, e.g. inside a command:
ExtOptionsLoader::_()->saveExtOptions(['is_debug'=>false]);
```

A typical upper layer (the DuckPhp root), after data_file_enable, initializes its own external options at root, then bumps them into the phases in turn.

## Caveats

- `saveExtOptions()` in the root phase checks `data_file_enable`: if the root component has not enabled `data_file_enable` it throws `DuckPhpSystemException("must enable 'data_file_enable' in root!")` (note it checks the **root** component's own option, not the current instance's).
- The JSON file's default location is `runtime/DuckPhpData.config.json` (root `path_runtime`); a missing file is treated as an empty object, and `__date__` is filled in on first save.
- Root/child distinction uses App::Phase/Root semantics; each Phase reads its own segment.
- The bump rule is a whitelist, not a full copy; it avoids poisoning other options (only keys allowed by bump_keys/prefix are written into the App options main table; additionally the whole segment goes into `data`).
- If you don't need persistence, set `data_file_bump_allowed` / the upper layer's data_file_enable=false, and everything goes through plain option config.

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
Parent init: at root, loadAllOptions first; then fetches "this phase"'s ext_options segment and bumpOptions it.

    public function bumpOptions(array $ext_options): void
If bump_allowed (and ext non-empty), copies the segment into app->options[data] and copies it into the main options per the bump keys/prefixes.

    public function saveExtOptions(array $options): void
Under the current phase, hands this segment (with __class__) to root (root_set… & writes JSON), then bumps onto itself.

### Protected methods

    protected function getRoot()
Returns the ExtOptionsLoader instance belonging to the root app (phase-switch safe).

    protected function loadAllOptions(): void
Reads the data JSON into all_ext_options (missing => null).

    protected function saveAllOptions(): void
Writes all_ext_options (+__date__) back to the JSON file, clearstatcache.

    protected function get_ext_options_file(): string
Composes the runtime-relative (or absolute) data file path.

    protected function root_get_options_by_phase(string $phase): array
Takes a phase segment from all_ext_options (empty when missing).

    protected function root_set_options_by_phase(string $phase, array $options): void
Writes options into all_ext_options[$phase].

## Related links

- [DuckPhp\DuckPhp](DuckPhp.md) (carries data_file_enable)
- [DuckPhp\Core\App](Core-App.md) — when multiple layers need is_debug changed by command
- `Component\Command::command_debug` (writes the data file toggle)
