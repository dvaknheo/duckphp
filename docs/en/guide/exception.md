# Exception Handling

DuckPHP provides a complete exception handling mechanism, including exception hierarchy, conditional throwing, exception reporting, and custom exception handling.

## Exception Hierarchy

DuckPHP recommends organizing exceptions in the following hierarchy:

```
\Exception                              # PHP built-in exception
  └─ {project}\System\ProjectException     # Project exception base class
       ├─ {project}\System\BusinessException    # Business layer exception
       └─ {project}\System\ControllerException  # Controller layer exception
```

### Creating Exception Classes

Define project exceptions in the `src/System/` directory:

```php
<?php
// src/System/ProjectException.php
namespace MyProject\System;

use DuckPhp\Foundation\SimpleExceptionTrait;

class ProjectException
{
    use SimpleExceptionTrait;  // Provides ThrowOn() method
}
```

```php
<?php
// src/System/BusinessException.php
namespace MyProject\System;

class BusinessException extends ProjectException
{
}
```

```php
<?php
// src/System/ControllerException.php
namespace MyProject\System;

class ControllerException extends ProjectException
{
}
```

### Configuring Exception Classes

Configure exception classes for each layer in `App`:

```php
class App extends DuckPhp
{
    public $options = [
        'exception_for_project'    => ProjectException::class,
        'exception_for_business'   => BusinessException::class,
        'exception_for_controller' => ControllerException::class,
        'exception_reporter'       => ExceptionReporter::class,
    ];
}
```

| Option | Default Value | Description |
|---|---|---|
| `exception_for_project` | `\Exception::class` | Project exception base class |
| `exception_for_business` | `null` (inherits from `exception_for_project`) | Business layer exception |
| `exception_for_controller` | `null` (inherits from `exception_for_project`) | Controller layer exception |
| `exception_reporter` | `null` | Exception reporter class name |

## Conditional Throwing

### In Controller Layer

```php
use DuckPhp\Foundation\Controller\Helper;

class UserController
{
    public function action_profile()
    {
        $user = UserAction::_()->getCurrentUser();
        
        // Conditionally throw exception: throw ControllerException when not logged in
        Helper::ControllerThrowOn(!$user, 'Please log in first', 403);
        
        Helper::Show(get_defined_vars(), 'user/profile');
    }
}
```

### In Business Layer

```php
use DuckPhp\Foundation\Business\Helper;

class UserBusiness
{
    public function login($username, $password)
    {
        $user = UserModel::_()->findByUsername($username);
        
        // Conditionally throw exception: throw BusinessException when user does not exist or password is wrong
        Helper::BusinessThrowOn(!$user, 'User does not exist', 1001);
        Helper::BusinessThrowOn(!password_verify($password, $user['password']), 'Incorrect password', 1002);
        
        return $user;
    }
}
```

### Directly Throwing on Exception Class

```php
// Using ThrowOn method (requires SimpleExceptionTrait)
BusinessException::ThrowOn($balance < $amount, 'Insufficient balance', 2001);
```

## Exception Reporter

The exception reporter is responsible for capturing and handling thrown exceptions, and can dispatch to different handling methods based on exception type.

### Creating an Exception Reporter

```php
<?php
// src/Controller/ExceptionReporter.php
namespace MyProject\Controller;

use DuckPhp\Foundation\ExceptionReporterTrait;

class ExceptionReporter
{
    use ExceptionReporterTrait;
    
    // Handle BusinessException
    public function onBusinessException($ex)
    {
        // Log, send notifications, etc.
        Logger::_()->warning('Business error: ' . $ex->getMessage());
        
        // Return error response
        Helper::ShowJson(['error' => $ex->getMessage(), 'code' => $ex->getCode()]);
    }
    
    // Handle ControllerException
    public function onControllerException($ex)
    {
        // Permission errors, etc.
        Helper::Show302('login');
    }
    
    // Handle other project exceptions (fallback)
    public function defaultException($ex)
    {
        // Call framework default handling
        App::Current()->_OnDefaultException($ex);
    }
}
```

### How Exception Reporter Works

The `OnException` method of `ExceptionReporterTrait` dispatches according to the following logic:

1. Get the exception class name (e.g., `MyProject\System\BusinessException`)
2. Check if the exception belongs to the current project namespace
3. If not a project exception, call `defaultException()`
4. If it is a project exception, extract the class name (e.g., `BusinessException`)
5. Find the corresponding handling method (e.g., `onBusinessException()`)
6. If found, call it; otherwise, call `defaultException()`

### Method Naming Rules

| Exception Class Name | Handling Method |
|---------|---------|
| `BusinessException` | `onBusinessException($ex)` |
| `ControllerException` | `onControllerException($ex)` |
| `ProjectException` | `onProjectException($ex)` |
| Others (non-project exceptions) | `defaultException($ex)` |

## Framework Default Exception Handling

When `exception_reporter` is not configured or the exception is not handled by the reporter, the framework uses default handling:

### Debug Mode (`is_debug = true`)

- Display detailed error information (exception class, message, stack trace)
- Facilitates development and debugging

### Production Mode (`is_debug = false`)

- Display the view configured by `error_500` (e.g., `_sys/error_500`)
- Log error (if `default_exception_do_log` is enabled)
- Do not expose sensitive information

## Custom Exception Handling

### Overriding Default Exception Handling

Override the `_OnDefaultException` method in `App`:

```php
class App extends DuckPhp
{
    public function _OnDefaultException($ex): void
    {
        // Custom error handling logic
        
        // Log
        Logger::_()->error($ex->getMessage());
        
        // Send error notification (e.g., email, DingTalk)
        // NotifyService::_()->sendErrorAlert($ex);
        
        // Call parent default handling
        parent::_OnDefaultException($ex);
    }
}
```

### Registering Custom Handlers with ExceptionManager

```php
use DuckPhp\Core\ExceptionManager;

// Register handler for a specific exception type
ExceptionManager::_()->assignExceptionHandler(
    ValidationException::class,
    function ($ex) {
        // Return validation error response
        Helper::ShowJson(['errors' => $ex->errors]);
    }
);

// Register unified handler for multiple exception types
ExceptionManager::_()->setMultiExceptionHandler(
    [BusinessException::class, ControllerException::class],
    function ($ex) {
        // Unified handling
    }
);

// Set default exception handler
ExceptionManager::_()->setDefaultExceptionHandler(
    function ($ex) {
        // Handle all uncaught exceptions
    }
);
```

## Development Error Handling

The framework automatically captures PHP errors (Notice, Warning, etc.) and converts them to exceptions:

```php
// Trigger a Notice
$undefined = $nonexistent_var;  // Converted to ErrorException
```

### Configuring Development Error Handling

```php
$options = [
    'handle_all_dev_error' => true,   // Whether to handle PHP errors
    'handle_all_exception' => true,   // Whether to handle uncaught exceptions
];
```

## Exception Handling Flowchart

```
Throw exception
  │
  ▼
ExceptionManager._CallException()
  │
  ├─ Match registered handler in exceptionHandlers
  │     └─ Match found → Execute custom handler
  │
  └─ No match → Execute default_exception_handler
        │
        ├─ exception_reporter configured
        │     └─ ExceptionReporter::OnException()
        │           ├─ Match on{ExceptionClass}() → Execute
        │           └─ No match → defaultException()
        │
        └─ exception_reporter not configured
              └─ App::_OnDefaultException()
                    ├─ Debug mode → Display detailed error
                    └─ Production mode → Display error_500 view
```

## Best Practices

1. **Use corresponding exceptions by layer**: Use `ControllerException` in Controller layer, `BusinessException` in Business layer
2. **Use conditional throwing**: Use `Helper::ControllerThrowOn()` / `Helper::BusinessThrowOn()` instead of `if + throw`
3. **User-friendly exception messages**: Exception messages will eventually be shown to users, so use understandable language
4. **Meaningful exception codes**: Use meaningful error codes to facilitate frontend handling based on code
5. **Exception reporter for logging**: Log exception logs in `ExceptionReporter` for troubleshooting
6. **Do not expose details in production**: Ensure sensitive information is not leaked when `is_debug = false`
