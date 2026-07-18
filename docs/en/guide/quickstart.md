# Quick Start

This chapter demonstrates typical DuckPHP usage through a complete example.

## Example: Blog Display Page

### 1. Project Structure

```
myblog/
├── composer.json
├── config/
│   └── DuckPhpSettings.config.php
├── public/
│   └── index.php
├── src/
│   ├── Controller/
│   │   ├── Base.php
│   │   └── MainController.php
│   ├── Business/
│   │   └── BlogBusiness.php
│   ├── Model/
│   │   └── BlogModel.php
│   └── System/
│       └── App.php
├── view/
│   ├── _sys/
│   │   ├── error_404.php
│   │   └── error_500.php
│   └── main.php
└── runtime/
```

### 2. Entry File

```php
<?php
// public/index.php
require_once __DIR__.'/../vendor/autoload.php';

$options = [
    'is_debug' => true,
];
\MyBlog\System\App::RunQuickly($options);
```

### 3. Application Configuration

```php
<?php
// src/System/App.php
namespace MyBlog\System;

use DuckPhp\DuckPhp;

class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'error_404' => '_sys/error_404',
        'error_500' => '_sys/error_500',
    ];
}
```

### 4. Controller

```php
<?php
// src/Controller/Base.php
namespace MyBlog\Controller;

use DuckPhp\Foundation\ControllerTrait;

class Base
{
    use ControllerTrait;
}
```

```php
<?php
// src/Controller/MainController.php
namespace MyBlog\Controller;

use MyBlog\Business\BlogBusiness;
use DuckPhp\Foundation\Controller\Helper;

class MainController extends Base
{
    public function action_index()
    {
        $data = BlogBusiness::_()->getIndexData();
        Helper::Show(get_defined_vars(), 'main');
    }
    
    public function action_post()
    {
        $id = Helper::GET('id');
        $post = BlogBusiness::_()->getPost($id);
        if (!$post) {
            Helper::Show404();
            return;
        }
        Helper::Show(get_defined_vars(), 'post');
    }
}
```

### 5. Business Layer

```php
<?php
// src/Business/BlogBusiness.php
namespace MyBlog\Business;

use DuckPhp\Foundation\BusinessTrait;
use MyBlog\Model\BlogModel;

class BlogBusiness
{
    use BusinessTrait;
    
    public function getIndexData()
    {
        $title = "My Blog";
        $posts = BlogModel::_()->getRecentPosts();
        return [
            'title' => $title,
            'posts' => $posts,
        ];
    }
    
    public function getPost($id)
    {
        return BlogModel::_()->find($id);
    }
}
```

### 6. Model Layer

```php
<?php
// src/Model/BlogModel.php
namespace MyBlog\Model;

use DuckPhp\Foundation\Model\Base;

class BlogModel extends Base
{
    protected $table_name = 'posts';
    
    public function getRecentPosts($limit = 10)
    {
        return $this->fetchAll(
            "SELECT * FROM `'TABLE'` ORDER BY created_at DESC LIMIT ?",
            $limit
        );
    }
}
```

### 7. View

```php
<?php
// view/main.php
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= __h($title) ?></title>
</head>
<body>
    <h1><?= __h($title) ?></h1>
    
    <?php foreach ($posts as $post): ?>
        <article>
            <h2><a href="<?= __url('post?id=' . $post['id']) ?>">
                <?= __h($post['title']) ?>
            </a></h2>
            <p><?= __h(mb_substr($post['content'], 0, 200)) ?>...</p>
            <small><?= $post['created_at'] ?></small>
        </article>
    <?php endforeach; ?>
</body>
</html>
```

### 8. Configuration File

```php
<?php
// config/DuckPhpSettings.config.php
return [
    'database_list' => [
        [
            'dsn' => 'sqlite:' . __DIR__ . '/../runtime/blog.db',
            'username' => '',
            'password' => '',
            'driver_options' => [],
        ],
    ],
];
```

> **Note**: Even when using SQLite, the `username` and `password` keys must be present, otherwise `DuckPhp\Db\Db::check_connect()` will throw an `Undefined array key "username"` error.

### 9. Run

```bash
# Start development server
php -S localhost:8080 -t public

# Or use the framework CLI
php vendor/bin/duckphp run
```

Visit `http://localhost:8080/` to see the blog homepage.

## Complete Example (Single File)

If you want a minimal experience, you can refer to `template/public/demo.php`, which is a single file containing all content:

```php
// demo.php core structure
namespace {
    // Autoload
}

namespace MySpace\System {
    class App extends \DuckPhp\DuckPhp { }
}

namespace MySpace\Controller {
    class MainController {
        use \DuckPhp\Foundation\ControllerTrait;
        public function action_index() { ... }
    }
}

namespace MySpace\Business {
    class MyBusiness {
        use \DuckPhp\Foundation\BusinessTrait;
    }
}

namespace MySpace\Model {
    class MyModel {
        use \DuckPhp\Foundation\ModelTrait;
    }
}

namespace {
    \MySpace\System\App::RunQuickly($options);
}
```

## Quick Development Commands

```bash
# Create new project
php vendor/bin/duckphp new

# Start development server
php vendor/bin/duckphp run

# View help
php vendor/bin/duckphp help
```
