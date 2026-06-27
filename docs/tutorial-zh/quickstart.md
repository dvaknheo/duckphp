# 快速入门

本章通过完整示例演示 DuckPHP 的典型用法。

## 示例：博客展示页面

### 1. 项目结构

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

### 2. 入口文件

```php
<?php
// public/index.php
require_once __DIR__.'/../vendor/autoload.php';

$options = [
    'is_debug' => true,
];
\MyBlog\System\App::RunQuickly($options);
```

### 3. 应用配置

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

### 4. 控制器

```php
<?php
// src/Controller/Base.php
namespace MyBlog\Controller;

use DuckPhp\Foundation\SimpleControllerTrait;

class Base
{
    use SimpleControllerTrait;
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

### 5. 业务层

```php
<?php
// src/Business/BlogBusiness.php
namespace MyBlog\Business;

use DuckPhp\Foundation\SimpleBusinessTrait;
use MyBlog\Model\BlogModel;

class BlogBusiness
{
    use SimpleBusinessTrait;
    
    public function getIndexData()
    {
        $title = "我的博客";
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

### 6. 模型层

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

### 7. 视图

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

### 8. 配置文件

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

> **注意**：即使使用 SQLite，`username` 和 `password` 键也必须存在，否则 `DuckPhp\Db\Db::check_connect()` 会抛出 `Undefined array key "username"` 错误。

### 9. 运行

```bash
# 启动开发服务器
php -S localhost:8080 -t public

# 或使用框架命令行
php vendor/bin/duckphp run
```

访问 `http://localhost:8080/`，看到博客首页。

## 完整示例（单一文件）

如果你想要最小化体验，可以参考 `template/public/demo.php`，它是单一文件包含所有内容的示例：

```php
// demo.php 核心结构
namespace {
    // 自动加载
}

namespace MySpace\System {
    class App extends \DuckPhp\DuckPhp { }
}

namespace MySpace\Controller {
    class MainController {
        use \DuckPhp\Foundation\SimpleControllerTrait;
        public function action_index() { ... }
    }
}

namespace MySpace\Business {
    class MyBusiness {
        use \DuckPhp\Foundation\SimpleBusinessTrait;
    }
}

namespace MySpace\Model {
    class MyModel {
        use \DuckPhp\Foundation\SimpleModelTrait;
    }
}

namespace {
    \MySpace\System\App::RunQuickly($options);
}
```

## 快捷开发命令

```bash
# 创建新项目
php vendor/bin/duckphp new

# 启动开发服务器
php vendor/bin/duckphp run

# 查看帮助
php vendor/bin/duckphp help
```
