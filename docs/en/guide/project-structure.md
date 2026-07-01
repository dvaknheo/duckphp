# Project Structure and Coding Rules

## Standard Project Structure

Create a project quickly using the Composer script (available after installation):

```bash
php vendor/bin/duckphp new
```

This will use the scaffold template from the `skeleton/` directory.

Project root directory structure:

```
project/
├── composer.json
├── config/
│   └── DuckPhpSettings.config.php    # Global settings
├── public/
│   └── index.php                     # Web entry point
├── src/
│   ├── Controller/                   # Controller layer - HTTP request entry
│   │   ├── Base.php
│   │   ├── ExceptionReporter.php     # Exception reporter
│   │   ├── Helper.php
│   │   ├── MainController.php
│   │   ├── Session.php               # Session management
│   │   ├── SomeAction.php            # Action example
│   │   └── testController.php        # Test controller
│   ├── Business/                     # Business layer - Business logic
│   │   ├── Base.php
│   │   ├── DemoBusiness.php          # Business example
│   │   ├── Helper.php
│   │   └── SomeService.php           # Service example
│   ├── Model/                        # Model layer - Data access
│   │   ├── Base.php
│   │   ├── DemoModel.php             # Model example
│   │   └── Helper.php
│   └── System/                       # System layer - App config and exceptions
│       ├── App.php                   # Application core configuration
│       ├── BusinessException.php     # Business exception
│       ├── ControllerException.php   # Controller exception
│       └── ProjectException.php      # Project exception base class
├── view/                             # View directory
│   └── _sys/                         # System views
│       ├── error_404.php
│       └── error_500.php
├── runtime/                          # Runtime directory (logs, etc.)
├── cli.php                           # CLI entry point
└── vendor/
```

> **Note**: `SomeAction.php`, `testController.php`, `DemoBusiness.php`, `SomeService.php`, and `DemoModel.php` are example files. They should be deleted in actual projects and replaced with similar classes based on business requirements.
>
> The `runtime/` directory needs write permissions.

## Coding Rules

### Naming Conventions

| Type | Naming Rule | Example | Description |
|------|-------------|---------|-------------|
| Controller class | `{Name}Controller` | `UserController` | Route entry, handles input/output |
| Controller method | `action_{method}` | `action_index()` | Route method prefix |
| Action class | `{Name}Action` | `UserAction` | Reusable controller functionality |
| Session class | `Session` | `Session` | State container |
| Business class | `{Name}Business` | `UserBusiness` | Business logic orchestration |
| Service class | `{Name}Service` | `CommonService` | Reusable business functionality |
| Model class | `{Name}Model` | `UserModel` | Data access |
| Exception class | `{Name}Exception` | `ProjectException` | Exception hierarchy |

### Core Principles

#### System Layer

1. Framework-related calls are centralized in the **System layer**.
2. The `System` namespace is responsible for framework-related calls, exception definitions, and application configuration.

#### Controller Layer

1. The Controller layer should not directly call classes under the `DuckPhp` namespace.
2. Session operations are centralized in the `Session` class.
3. Controller classes serve as HTTP request entry points, handling input/output. Controllers should not call each other.
4. Shared operations between controllers can be encapsulated into **Action classes**.
5. Controller classes and Action classes should inherit from the `Base` class.
6. Controller classes and Action classes call **Business classes**.
7. Action classes are stateless, so they must have an empty `__construct()` to override the base class constructor.

#### Business Layer

1. The Business layer should not directly call classes under the `DuckPhp` namespace.
2. The Business layer remains **purely stateless** and can be reused in any environment such as CLI, tests, and APIs.
3. Shared operations between Business classes can be encapsulated into **Service classes**.
4. Business classes and Service classes should inherit from the `Base` class.
5. Business classes and Service classes call **Model classes**.

#### Model Layer

1. The Model layer should not directly call classes under the `DuckPhp` namespace.
2. The Model layer remains **purely stateless** and is only responsible for data access.
3. Models generally correspond to database table names.
4. Model classes should not throw exceptions; the caller handles exceptions.

#### View Layer

1. View files should not perform complex calculations; they are only for display output.
2. `view/_sys/` stores system views (such as error pages).
3. `view/{ControllerName}/{ActionName}` is used for storing views corresponding to controllers.

### Auxiliary Rules

1. Controller classes and Action classes use `Helper::ControllerThrowOn()` to throw exceptions.
2. Business classes and Service classes use `Helper::BusinessThrowOn()` to throw exceptions.
3. The `Helper` classes at each layer generally do not need additional methods.
4. In project conventions, the Model layer's `Helper` class can be merged into the `Base` class.
