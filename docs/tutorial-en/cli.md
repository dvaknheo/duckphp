# Command Line Tools

## CLI Entry Point

### Standard Entry Point

```bash
php cli.php {command} [arguments]
```

The `cli.php` file is located in the project root directory (see `template/cli.php`).

### Using the Framework CLI Entry Point

```bash
php vendor/bin/duckphp run
php vendor/bin/duckphp new
php vendor/bin/duckphp help
```

## Built-in Commands

### `run` — Start Development Server

```bash
php vendor/bin/duckphp run
# Default port 8080, can be modified via options
```

### `new` — Create New Project

```bash
php vendor/bin/duckphp new
# Interactive guided project creation
```

### `help` — View Help

```bash
php vendor/bin/duckphp help
# Display all available commands
```

## Registering Custom Commands

### Defining Command Methods in App

```php
namespace MyProject\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    // Method prefix is command_
    public function command_hello($name = 'World')
    {
        echo "Hello, $name!\n";
    }
    
    public function command_cache_clear()
    {
        // Cache clearing logic
        echo "Cache cleared.\n";
    }
}
```

### Registering Commands in Other Classes

```php
namespace MyProject\Controller;

class Commands
{
    public function command_import($file)
    {
        echo "Importing $file...\n";
    }
    
    public function command_export($format = 'json')
    {
        echo "Exporting as $format...\n";
    }
}
```

Then register in `App`:

```php
class App extends DuckPhp
{
    public $options = [
        'cli_command_classes' => [
            \MyProject\Controller\Commands::class,
        ],
    ];
}
```

## CLI Command Argument Parsing

```bash
# Basic command
php cli.php hello

# With arguments
php cli.php hello --name=Duck

# With positional arguments
php cli.php import file.csv

# Switch namespace
php cli.php mynamespace:command --arg=value
```

CLI arguments are automatically parsed as named parameters of the method:

```php
public function command_import($file, $format = 'csv')
{
    // php cli.php import data.csv --format=xml
    // $file = 'data.csv', $format = 'xml'
}
```

## Quick Installer

The framework provides `FastInstaller` for quickly setting up database and Redis:

```bash
php cli.php install
# Interactive installation wizard
```
