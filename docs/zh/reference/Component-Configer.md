# DuckPhp\Component\Configer

配置读取组件。

## 简介

`Configer` 组件负责从 `config/` 目录加载 PHP 配置文件。它通过文件基名（不含 `.php` 后缀）缓存并返回配置数组，支持按键读取默认值。

配置文件的查找路径为：`{path}/{path_config}/{file_basename}.php`。例如 `config/app.php` 对应基名 `app`。

该组件会在首次调用时自动初始化，通常通过 `Controller\Helper::Config()` 或 `Business\Helper::Config()` 间接使用。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根路径。通常由框架自动填充。 |
| `path_config` | `'config'` | 配置目录名（相对于 `path`）。 |

## 配置文件格式

配置文件是一个普通的 PHP 文件，返回一个关联数组：

```php
<?php
// config/app.php
return [
    'name' => 'MyApp',
    'version' => '1.0.0',
    'features' => [
        'cache' => true,
        'log' => false,
    ],
];
```

```php
<?php
// config/database.php
return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host' => '127.0.0.1',
            'dbname' => 'test',
        ],
    ],
];
```

## 使用方式

### 通过 Controller Helper

```php
use DuckPhp\Foundation\Controller\Helper;

$config = Helper::Config('app');                  // 获取整个 app.php 配置
$name = Helper::Config('app', 'name', 'Default'); // 获取 'name' 键，默认 'Default'
$cache = Helper::Config('app', 'features.cache', false); // 不支持点号，仅演示顶层键
```

### 通过 Business Helper

```php
use DuckPhp\Foundation\Business\Helper;

$dbConfig = Helper::Config('database');
$default = Helper::Config('database', 'default', 'mysql');
```

### 直接通过 Configer 组件

```php
use DuckPhp\Component\Configer;

$config = Configer::_()->_Config('app');
$name = Configer::_()->_Config('app', 'name', 'Default');
```

## 配置示例

```php
class App extends DuckPhp
{
    public $options = [
        'path' => __DIR__ . '/../../',
        'path_config' => 'config',
    ];
}
```

```php
<?php
// config/app.php
return [
    'name' => 'MyApp',
    'version' => '1.0.0',
];
```

## 注意事项

1. 配置文件的基名不需要带 `.php` 后缀，例如读取 `config/app.php` 时传入 `'app'`。
2. 如果文件不存在，`_Config()` 返回空数组或默认值。
3. 读取结果会缓存在 `$all_config` 中，避免重复加载文件。
4. `Configer` 只支持读取顶层键，不支持点号路径（如 `a.b.c`）。如需深层读取，请自行解析返回的数组。

## 全部选项

```
'path' => '',
'path_config' => 'config',
```

## 方法列表

### 公共方法

    public function _Config($file_basename = 'config', $key = null, $default = null)
加载并返回指定配置文件。`$key` 为 `null` 时返回整个配置数组；否则返回指定键，不存在时返回 `$default`。

### 受保护方法

    protected function _LoadConfig($file_basename)
加载指定基名的配置文件，并缓存到 `$all_config`。

    protected function loadFile($file)
使用 `require` 加载文件并返回配置数组。

## 相关链接

- [DuckPhp\Component\Cache](Component-Cache.md)
- [DuckPhp\Foundation\Controller\Helper](Foundation-Controller-Helper.md)
- [DuckPhp\Foundation\Business\Helper](Foundation-Business-Helper.md)
