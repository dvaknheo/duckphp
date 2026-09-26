# DuckPhp\Ext\DuckPhpInstaller

## Introduction

`DuckPhpInstaller` is the CLI installer behind `bin/duckphp`, providing three CLI commands:

- `new`: copies a new project from the framework `skeleton` into the target directory, and replaces the `ProjectNameTemplate` namespace/App class name with the namespace you set (`command_new`);
- `show`: runs the framework demo pages with the built-in HttpServer (`command_show` → `runDemo`);
- `help`: prints the help text (`command_help` → `showHelp`).

Rarely used directly inside a project; usually triggered via `./vendor/bin/duckphp new/show`.

## Class info

- Namespace: `DuckPhp\Ext`
- Declaration: `class DuckPhpInstaller extends DuckPhp\Core\ComponentBase`

## Options

| Option | Default | Description |
|---|---|---|
| `path` | `''` | Target path (the directory `new` writes to / overwrites). |
| `namespace` | `''` | Namespace of the new project (auto-detected/asked when omitted). |
| `force` | `false` | Whether to force-overwrite when files already exist at the target. |
| `autoloader` | `'vendor/autoload.php'` | The autoload path to use. |
| `verbose` | `false` | Whether to print progress information. |
| `help` | `false` | Help switch. |

## Usage

```text
./vendor/bin/duckphp new --namespace MyProject --path ./myproj --force
./vendor/bin/duckphp new --help
./vendor/bin/duckphp show --port 8080
```

## Caveats

- `newProject()`: namespace priority = CLI `--namespace` → composer.json `psr-4` (the entry mapped to `src/`, via `getNameSpaceByComposer`) → console prompt (`getNamespaceByConsole`).
- `dumpDir()`: recursively copies `skeleton`; `src/System/App.php` is renamed to `{NamespaceBasename}App.php` and `class App extends` is rewritten to the corresponding class name; file contents are processed by `filteText` (namespace/macro filtering); aborts when `force=false` and the target has files with the same names (`checkFilesExist`).
- `runDemo()`: serves from the built-in `demo/` directory (`getDemoPath()`) and starts a server with `HttpServer::RunQuickly($options)`; supports `--port` and a custom `--http_server`.

## Methods

### Public methods

    public function init(array $options, ?object $context = null)
After the parent init, registers its own command class prefix (`regCommandClassSingle`).

    public function command_new(): void
The `new` command: parses CLI arguments then `newProject()` (with `--help`, only prints the help).

    public function command_help()
The `help` command: prints the help.

    public function command_show()
The `show` command: runs the demo server.

    public function showHelp(): void
Prints the help text.

    public function newProject($options = [])
Creates a new project: determines the namespace and copies/rewrites the skeleton.

    public function runDemo(): void
Starts the HttpServer demo from the built-in `demo/` directory.

### Protected methods

    protected function getDemoPath(): string
The directory served by `show`/`runDemo`: the framework's bundled `demo/` (historically called `template/`; this method did not follow the rename, which once made the command serve nothing).

    protected function getNameSpaceByComposer(string $path): string
Infers the namespace from composer.json `psr-4` (`src/`).

    protected function getNamespaceByConsole(): string
Interactively asks for the namespace (default `Demo`).

    protected function dumpDir(string $source, string $dest, bool $force = false): void
Recursively copies a directory and rewrites each file's contents.

    protected function getNamespaceBasename()
Takes the last segment of the namespace (used for the App class rename).

    protected function checkFilesExist()
Checks whether the target already has files with the same names (aborts when not force).

    protected function createDirectories()
Creates the directory structure at the target.

    protected function filteText()
Applies macro/namespace replacement filtering to a single file's contents.

    protected function filteMacro()
Replaces template macro placeholders.

    protected function filteNamespace()
Replaces namespace placeholders.

    protected function changeHeadFile()
Processes the head file template.

    protected function genProjectName()
Generates the project name.

    protected function detectedClass()
Detects class information (used for rewriting).

## Related links

- [DuckPhp\Core\Console](Core-Console.md) — CLI argument reading
- [DuckPhp\HttpServer\HttpServer](HttpServer-HttpServer.md) — the demo server
