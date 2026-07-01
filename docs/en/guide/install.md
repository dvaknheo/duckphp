# Installation and Quick Start

## Requirements

- PHP >= 7.4.0
- Composer (recommended)
- PDO extension (if database is needed)

## Install via Composer

```bash
composer require dvaknheo/duckphp
```

## Minimal Running Example (After Composer Installation)

If you have already installed via Composer, here is a minimal entry file:

```php
<?php
// public/index.php
require_once __DIR__.'/../vendor/autoload.php';

class MyApp extends \DuckPhp\DuckPhp
{
    // Use default configuration
}

// One-line startup
MyApp::RunQuickly([
    'is_debug' => true,
]);
```

Create your first controller:

```php
<?php
// src/Controller/MainController.php
namespace MyApp\Controller;

class MainController
{
    public function action_index()
    {
        echo "Hello DuckPHP!";
    }
}
```

Start the built-in server:

```bash
php -S localhost:8080 -t public
php vendor/bin/duckphp run    # Or use the framework CLI
```

Visit `http://localhost:8080/` and you will see "Hello DuckPHP!".

## Next Steps

- See [Project Structure and Coding Rules](project-structure.md) for the standard project structure and coding conventions
- See [Quick Start](quickstart.md) for a complete example
