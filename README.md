# DuckPhp

[English](README.md) | [中文](README-zh.md)

- Version: v1.3.4 / preparing for v1.3.5
- Author QQ: 85811616
- QQ Group: 714610448

Gitee repository: https://gitee.com/dvaknheo/duckphp
Github repository: https://github.com/dvaknheo/duckphp

## 1. What is DuckPhp

DuckPhp is a library-style PHP framework. It has no dependencies, every component can be replaced, and it is flexible for deployment and teamwork.

The name comes from **duck typing**:

> Duck Typing: if it looks like a duck, swims like a duck, and quacks like a duck, then it is probably a duck.

## 2. Features

### Install with Composer

```
composer require dvaknheo/duckphp # use require
./vendor/bin/duckphp new
```

The first command shows that DuckPhp is a library-style framework, not a bundle of many libraries.

**DuckPhp is added as a library.** So the project skeleton does not contain a lot of files that you cannot delete.

**DuckPhp has no dependencies.** You do not need to worry about changes in third-party packages. It works without importing 101 packages, so stability is fully under control.

The command `./vendor/bin/duckphp new` copies the skeleton files and replaces the project namespace with the `src` namespace you set in `composer.json`.

**DuckPhp does not limit your project namespace.**

You can also write code without the skeleton files.

### Example 1

#### Create the Example

We use a simple example to quickly understand DuckPhp.

Create a file `sample1.php` in the project directory:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Controller\Helper;

use MyApp as MyWelcomeController;
use MyApp as MyBusiness;
use MyApp as MyModel;
use MyApp as MyView;

use DuckPhp\DuckPhpAllInOne;
use DuckPhp\Foundation\Controller\Helper;

use MyApp as MyWelcomeController;
use MyApp as MyBusiness;
use MyApp as MyModel;
use MyApp as MyView;

class MyApp extends DuckPhpAllInOne
{
    public $options = [
        'path' => __DIR__,
        'controller_welcome_class' => MyWelcomeController::class,
        'callable_view_class' => MyView::class,
        // ...
    ];
    //@override
    public function onInited()
    {
        Helper::setViewHeadFoot('', '');
    }
    public function action_index()
    {
        $words = MyBusiness::_()->getTime();
        Helper::Show(['words' => $words], 'main');
    }

    public function view_main($data)
    {
        $url = __url('');
        echo "You are visit: $url; {$data['words']}";
    }
    public function getTime()
    {
        return "Hello, now is <" . MyModel::_()->getData() . '>';
    }
    public function getData()
    {
        return DATE(DATE_ATOM);
    }
}
MyApp::RunQuickly([]);
```

Run `sample1.php` as a server:

```bash
php sample1.php run --host 127.0.0.1 --port 9628 --path-document .
```

This uses the PHP built-in server. You can also run the PHP built-in server directly:

```bash
php -S 127.0.0.1:9628 -t .
```

Visit `http://127.0.0.1:9628/sample1.php`. The result is:

```
You are visit: /sample1.php; Hello, now is <2026-07-11T07:08:05+00:00>
```

#### Explanation

The entry point here is the `DuckPhp\DuckPhpAllInOne` class.

The flow is:

- `MyApp::RunQuickly()` → `MyWelcomeController::action_index()` → `MyBusiness::getTime()` → `MyModel::getData()`
- `MyApp::RunQuickly()` → `MyWelcomeController::action_index()` → `Helper::Show()` → `MyView::view_main()`

Details:

- The application entry `MyApp::RunQuickly()` shows the application options in `MyApp->$options`.
- The entry routes to `MyWelcomeController::action_index()`.
- The controller layer `MyWelcomeController::action_index()` calls the business layer `MyBusiness::getTime()`.
- The business layer `MyBusiness::getTime()` calls the model layer `MyModel::getData()`.
- The model layer `MyModel::getData()` gets the current time.
- The controller layer `MyApp::action_index()` calls the helper class `Helper::Show()` to display output.
- `Helper::Show()` calls the view layer `MyApp::view_main()` to show the final output.
- The view layer uses the global function `__url()` to get the current URL.

### Some Features Shown in Example 1

From this example, we can see DuckPhp's features:

**DuckPhp supports both web mode and command line mode**

*We do not recommend using PHP's built-in command-line web server. Set nginx or Apache `document_root` to the `public` directory and deploy in the usual way.*

**DuckPhp does not limit your directory and supports full-site routing, partial-path routing, and no-PATH_INFO routing**

> Many PHP frameworks today only allow one application per domain. DuckPhp returns to the fast PHP development style.
> DuckPhp can be used without changing server settings, and you can also put it in a subdirectory. The `DuckPhpAllInOne` class enables no-PATH_INFO routing by default, unlike the `DuckPhp` class.

DuckPhp supports Workerman through the Composer package `dvaknheo/workermanhttpd`. You can run it without changing project code. More platforms will be supported in the future.

**DuckPhp does not limit your project namespace**

> The sample code uses `MyApp` as its namespace.

**DuckPhp does not need a lot of config files**

> Most of its settings use default values. You can get different behavior by changing options.
> `$options` here are the application options. You can turn on debug mode. There are many application options available. See the docs for details.

**DuckPhp does not need manual routing**

Auto-routing is enough for most cases. If not, you can also write your own routes.

**DuckPhp is non-invasive and avoids global function conflicts**

> There are only a few global functions starting with `__`. You can also override them.

### Example 2: Embed Another Project

**A DuckPhp application can embed other DuckPhp applications as child applications**

This is an important feature of DuckPhp. You do not need to do secondary development on the existing DuckPhp application. You can use it directly as a plugin.

Sample file `sample2.php`:

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne;

class ChildApp extends DuckPhpAllInOne
{
    public function action_index()
    {
        echo "I'm child.";
    }
}

class ParentApp extends DuckPhpAllInOne
{
    public $options = [
        'app' => [
            ChildApp::class => [
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

ParentApp::RunQuickly([]);
```

Run it under the same server as above.

Visit `http://127.0.0.1:9628/sample2.php`. The result is:

`I'm Parent. Goto child`. Click the link to jump to the child application. The content is `I'm child.`.

Here, `ParentApp` and `ChildApp` are both independent DuckPhp applications. `ParentApp` uses `ChildApp` as a child application.

If you do not want to write a user system for an API, you can embed the user system from the Composer package `dvaknheo/duckadmin`. Then get the user ID with `Helper::UserId()` and the admin ID with `Helper::AdminId()`.

This is just a simple embedding demo. Child applications involve complex issues such as static resources, inter-app communication, and component sharing. See the docs for details.

### Example 3: Replace Components

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use DuckPhp\DuckPhpAllInOne as DuckPhpAllInOne;
use DuckPhp\Core\CoreHelper;

function __h($str)
{
    return CoreHelper::H($str);
}

class MyCoreHelper extends CoreHelper
{
    //@override
    public function _H(&$str)
    {
        return '<b>' . CoreHelper::_H($str) . '</b>';
    }
}

class ExtApp extends DuckPhpAllInOne
{
    //@override
    public function onInited()
    {
        CoreHelper::_(MyCoreHelper::_());
        ExtApp::setViewHeadFoot('', '');
    }

    public function action_index()
    {
        ExtApp::Show([], 'main');
    }

    public function view_main($data)
    {
        echo __h('<h!>');
    }
}

$options = [
    'path' => __DIR__,
];
ExtApp::RunQuickly($options);
```

Run it under the same server as above.

Visit `http://127.0.0.1:9628/sample3.php`. The output is:

```html
<b>&lt;h!&gt;</b>
```

This example replaces the implementation of `__h()` and shows DuckPhp's flexibility.

**As a modern PHP library, DuckPhp makes all components replaceable.** If you are not happy with the default implementation, you can easily switch to another one, even if it needs a third-party dependency. DuckPhp uses mutable singletons so the calling interface stays the same while the implementation can change. This means you can fix problems or switch components without hacking the framework.

**DuckPhp applications have clear stack traces, which makes debugging easy.** You can quickly find problems with `debug_print_backtrace(2)`. Frameworks that use a lot of middleware usually have less clear stack traces.

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

## 4. Simple Tutorial

// To be added: a CRUD example

## 5. Other Features

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

## 6. Version History

At first, this was meant to be a simple PHP web framework. Now it is simple to use, but the inside is not simple.

+ 1.0.* is the DNMVCS single-file version.
+ 1.1.* is the DNMVCS multi-file version.
+ 1.2.* is the renamed DuckPhp version. As ideas change, there may be big changes.
+ 1.3.* is the stable version planned for large-scale use. It will be responsible for history.

The biggest change in version 1.3 is the phase concept, which makes different applications insert into each other without problems.

Version 1.3.4 added Docker support, fixed multi-language support, and prepares for 1.3.5.

## 7. What DuckPhp Will Do Next

![logo](duckphp.jpg)
