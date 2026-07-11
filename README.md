# DuckPhp

- Version: v1.3.4 / preparing for v1.3.5
- Author QQ: 85811616
- QQ Group: 714610448

Gitee repository: https://gitee.com/dvaknheo/duckphp
Github repository: https://github.com/dvaknheo/duckphp

## 1. What is DuckPhp

DuckPhp is a library-style PHP framework. It has zero dependencies, every component can be replaced, and it is flexible for deployment and teamwork.

The name comes from **duck typing**:

> Duck Typing: if it looks like a duck, swims like a duck, and quacks like a duck, then it is probably a duck.

## 2. Advantages in Detail

### Install with Composer

```
composer require dvaknheo/duckphp # use require
./vendor/bin/duckphp new
```

The first command shows that DuckPhp is a library-style framework, not a bundle of many libraries.

**DuckPhp is added as a library.** So the project skeleton does not contain a lot of files that you cannot delete.

**DuckPhp has zero dependencies.** You do not need to worry about changes in third-party packages. It works without importing 101 packages, so stability is fully under control.

The command `./vendor/bin/duckphp new` copies the skeleton files and replaces the project namespace with the `src` namespace you set in `composer.json`.

**DuckPhp does not limit your project namespace.**

You can also write code without the skeleton files.

### Example 1

The simplest example: you want to make an API without authentication. Just write an `api.php` file.

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne as DuckPhpAllInOne;
use DuckPhp\DuckPhpAllInOne as Helper;

class MyHello extends DuckPhpAllInOne
{
    public function action_index()
    {
        $words = __h("<b>Hello, this is all in one</b>");
        Helper::Show(['words' => $words], 'main');
    }

    public function view_main($data)
    {
        echo $data['words'];
    }
}

$options = [
    // 'is_debug' => true,
];
MyHello::RunQuickly($options);
```

Explanation:

The entry point here is the `DuckPhp\DuckPhpAllInOne` class. You can see `use DuckPhp\DuckPhpAllInOne as Helper;`. The Helper class wraps all the other classes together. This example also shows the `__h()` function.

The flow is: `action_index()` → `Show()` → `view_main()`.

From this example, we can see DuckPhp's features:

**DuckPhp does not limit your project namespace**

> The sample code uses `MyHello` as its namespace.

**DuckPhp does not limit your directory**

> Many PHP frameworks today only allow one application per domain. DuckPhp returns to the fast PHP development style.

DuckPhp supports full-site routing, partial-path routing, and no-PATH_INFO routing. You can use it without changing server settings, and you can also put it in a subdirectory. The `DuckPhpAllInOne` class enables no-PATH_INFO routing by default, unlike the `DuckPhp` class.

**DuckPhp does not need a lot of config files.** Most of its settings use default values. You can get different behavior by changing options.

> `$options` here are the application options. You can turn on debug mode. See the docs for details.

**DuckPhp is non-invasive and avoids global function conflicts**

> There are only a few global functions starting with `__`. You can also override them.

### Example 2: Embed Another Project

**DuckPhp can embed other DuckPhp projects into the current project.** You do not need to do secondary development on the existing DuckPhp application. You can use it directly as a plugin.

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;

class MyApi2 extends DuckPhpAllInOne
{
    public function action_index()
    {
        echo "I'm child.";
    }
}

class MyApi extends DuckPhpAllInOne
{
    public $options = [
        'app' => [
            MyApi2::class => [
                'controller_url_prefix' => 'child/',
            ],
        ]
    ];

    public function action_index()
    {
        $url_child = __url('child/index');
        echo "I'm Parent. Goto <a href='{$url_child}'>child</a>";
    }
}

MyApi::RunQuickly();
```

Here, `MyApi2` and `MyApi` are both independent DuckPhp applications. `MyApi` uses `MyApi2` as a child application.

If you do not want to write a user system for an API, you can embed the DuckAdmin user system and get the user ID with `Helper::UserId()`.

*Child applications are complex. They involve static resources, inter-app communication, and component sharing. Use them carefully.*

DuckPhp supports Workerman through the [dvaknheo/workermanhttpd](https://packagist.org/packages/dvaknheo/workermanhttpd) extension. You can run it without changing project code. More platforms will be supported in the future.

*We do not recommend using PHP's built-in command-line web server. Set nginx or Apache `document_root` to the `public` directory and deploy in the usual way.*

### Example 3: Replace Components

//TODO code

This example is not as visual as the first two, but it shows DuckPhp's flexibility.

**As a modern PHP library, DuckPhp makes all components replaceable.** If you are not happy with the default implementation, you can easily switch to another one, even if it needs a third-party dependency. DuckPhp uses mutable singletons so the calling interface stays the same while the implementation can change. This means you can fix problems or switch components without hacking the framework.

DuckPhp makes debugging easy. The stack trace is clear. You can quickly find problems with `debug_print_backtrace(2)`. Frameworks that use a lot of middleware usually have less clear stack traces.

> For debugging, use `__trace_dump()`.

## 3. Regular Project

When you create a project with `./vendor/bin/duckphp new`, you get the following skeleton files. See `RULES.md` for more details.

```
project/
├── composer.json
├── config/
│   └── DuckPhpSettings.config.php  # global settings
├── public/
│   └── index.php                     # web entry
├── src/
│   ├── Controller/                   # controller layer: HTTP/CLI entry
│   │   ├── Base.php
│   │   ├── ConsoleCommand.php        # CLI command example (disabled by default)
│   │   ├── ExceptionReporter.php     # exception reporter (disabled by default)
│   │   ├── Helper.php
│   │   ├── MainController.php
│   │   ├── Session.php               # session management
│   │   ├── SomeAction.php            # action example
│   │   └── testController.php        # test controller
│   ├── Business/                     # business layer: business logic
│   │   ├── Base.php
│   │   ├── DemoBusiness.php          # business example
│   │   ├── Helper.php
│   │   └── SomeService.php           # service example
│   ├── Model/                        # model layer: data access
│   │   ├── Base.php
│   │   └── DemoModel.php             # model example
│   └── System/                       # system layer: config and exceptions
│       ├── App.php                   # main application config
│       ├── BusinessException.php     # business exception (disabled by default)
│       ├── ControllerException.php   # controller exception (disabled by default)
│       └── ProjectException.php      # project exception base class (disabled by default)
├── view/                             # view directory
│   ├── _sys/                         # system views
│   │   ├── error_404.php
│   │   └── error_500.php
│   └── main.php                      # default view example
├── runtime/                          # runtime directory (logs, etc.)
├── cli.php                           # CLI entry
├── RULES.md                          # rules documentation
└── vendor/
```

> **Note**:
> - `SomeAction.php`, `testController.php`, `DemoBusiness.php`, `SomeService.php`, and `DemoModel.php` are sample files. In a real project, delete them and write similar classes for your business.
> - `ConsoleCommand.php`, `ExceptionReporter.php`, `BusinessException.php`, `ControllerException.php`, and `ProjectException.php` are disabled by default. You can:
>   - Simplify the project by deleting the files you do not need.
>   - Enable the feature by uncommenting the matching option in `src/System/App.php` (`cli_command_classes`, `exception_reporter`, `exception_for_project` / `exception_for_business` / `exception_for_controller`).
>
> The `runtime/` directory needs write permission.

**DuckPhp projects have clear layers and no cross-layer calls.**

System → Controller → Business → Model

DuckPhp users are divided into two roles: `business developers` and `core developers`.

- `Business developers` only need to study business code.
- `Core developers` study the system core code.

> After reading the helper class tutorial, a `business developer` can start writing business code. Ask a `core developer` when you do not understand something.

### Simple Tutorial

## 4. Other Features

DuckPhp supports Composer, but it can also run without Composer. DuckPhp is a Composer library, so it does not need a separate scaffolding project.

> Having your own loader is possible but not very useful in practice.

`DuckPhp\Core\App` is a sub-framework of `DuckPhp`. In some cases, you can use `DuckPhp\Core\App` directly.

DuckPhp controllers are easy to switch. They are independent from other classes and simple to understand.

DuckPhp routing can also be used on its own.

> In real projects, these three are rarely used separately.

DuckPhp supports extensions. These extensions can be independent and do not have to be used only with DuckPhp.

> Any class that supports `init([], $context)` can be used as an extension.

DuckPhp can make your application and DuckPhp system code connect in only one line. Your business code is mostly independent from DuckPhp system code. You only need to study business code, not the framework code.

> This is done by changing options.

DuckPhp has an extension that can stop you from writing SQL directly in controllers. Some frameworks prevent developers from making mistakes by sacrificing performance. DuckPhp does this with almost no performance loss.

> It is not very useful right now.

DuckPhp is loosely coupled, extensions are flexible, and it is easy to modify.

> DuckPhp's database class is simple, and you can easily replace it.

DuckPhp classes try to stay stateless.

DuckPhp components do not directly reference each other, so you can see this with `var_dump(AnyComponent::_())`.

### Development Philosophy

DuckPhp code is simple and does not do extra things. The latest version's default demo runs with CodeCoverage statistics showing only 376 / 4381 lines (v1.2.13-dev), which is executed lines / total executable lines.

DuckPhp design principle: does this thing really need to be built into the framework? Can it work without being built in?

Every DuckPhp release passes full-code-coverage tests, so it is very robust.

DuckPhp has no ORM and does not hide SQL, so checking SQL through logs is easy. It wraps PDO in a simple way. You can also use your own DB class or a third-party ORM (the tutorial has an example using ThinkPHP-DB. [link](docs/tutorial-db.db)).

DuckPhp does not include a template engine, because PHP itself is a template engine.

DuckPhp does not write widgets, because that goes against MVC separation.

## 5. Version History

At first, this was meant to be a simple PHP web framework. Now it is simple to use, but the inside is not simple.

+ 1.0.* is the DNMVCS single-file version.
+ 1.1.* is the DNMVCS multi-file version.
+ 1.2.* is the renamed DuckPhp version. As ideas change, there may be big changes.
+ 1.3.* is the stable version planned for large-scale use. It will be responsible for history.

The biggest change in version 1.3 is the phase concept, which makes different applications insert into each other without problems.

Version 1.3.4 added Docker support, fixed multi-language support, and prepares for 1.3.5.

## 6. What DuckPhp Will Do Next

![logo](duckphp.jpg)
