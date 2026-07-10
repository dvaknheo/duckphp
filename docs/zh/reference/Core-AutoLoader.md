# DuckPhp\Core\AutoLoader

自动加载器。

## 简介

`AutoLoader` 提供了简单的 PSR-4 风格自动加载能力。它支持命名空间到目录路径的映射，也支持 PSR-4 数组配置。整个框架的核心类就是通过 `DuckPhpSystemAutoLoader` 方法自加载的。

## 选项

| 选项 | 默认值 | 说明 |
|---|---|---|
| `path` | `''` | 项目根目录。为空时自动取当前工作目录的上一级。 |
| `namespace` | `''` | 应用主命名空间。 |
| `path_namespace` | `'app'` | 应用主命名空间对应的目录。 |
| `skip_app_autoload` | `false` | 是否跳过应用主命名空间的自动加载。 |
| `autoload_cache_in_cli` | `false` | 是否在 CLI 模式下缓存类到 opcache。 |
| `autoload_path_namespace_map` | `[]` | 路径到命名空间的映射数组。 |
| `psr-4` | `[]` | PSR-4 映射数组，键为命名空间，值为路径。 |

## 使用方式

### 框架内部启动

```php
\DuckPhp\Core\AutoLoader::DuckPhpSystemAutoLoader::class();  // 加载 DuckPhp 核心类
```

### 普通项目启动

```php
\DuckPhp\Core\AutoLoader::RunQuickly([
    'path' => __DIR__ . '/..',
    'namespace' => 'MyApp',
    'path_namespace' => 'app',
]);
```

### 添加 PSR-4 映射

```php
\DuckPhp\Core\AutoLoader::addPsr4('Vendor\\', __DIR__ . '/vendor');
```

## 映射规则

`namespace_paths` 是一个“路径 => 命名空间前缀”的映射。例如：

```php
[
    '/path/to/src/' => 'App\\',
]
```

当加载 `App\Controller
oo` 时，会查找 `/path/to/src/Controller/foo.php`。

## 路径处理

`path_namespace` 支持绝对路径和相对路径。如果是相对路径，会基于 `path` 选项拼接。

## 注意事项

1. `AutoLoader` 不是 `ComponentBase` 的子类，它是独立组件，用于在框架启动前加载其他类。
2. 同一个类文件只会被 `include_once` 一次。
3. 开启 `autoload_cache_in_cli` 后，会使用 `opcache_compile_file()` 预编译类文件。
4. `addPsr4()` 的参数形式是 `namespace => path`，内部会翻转成 `path => namespace`。

## 全部选项

```php
public $options = [
    'path' => '',
    'namespace' => '',
    'path_namespace' => 'app',
    'skip_app_autoload' => false,
    'autoload_cache_in_cli' => false,
    'autoload_path_namespace_map' => [],
    'psr-4' => [],
];
```

## 方法列表

### 公共方法

    public static function _($object = null)
获取或设置单例对象

    public static function RunQuickly(array $options = [])
快速初始化并运行自动加载器

    public static function addPsr4($namespace, $input_path = null)
添加 PSR-4 映射

    public static function AutoLoad(string $class): void
`spl_autoload_register` 的回调入口

    public function init(array $options, object $context = null)
初始化自动加载器

    public function isInited(): bool
是否已初始化

    public function run()
注册自动加载函数到 `spl_autoload_register`

    public function runAutoLoader()
`run()` 的别名

    public function _Autoload(string $class):void
实际执行类文件查找和加载

    public function assignPathNamespace($input_path, $namespace = null)
添加路径到命名空间映射

    public function cacheClasses()
缓存所有命名空间路径下的类文件

    public function cacheNamespacePath($path)
缓存指定路径下的类文件

    public function clear(): void
注销自动加载函数

    public static function DuckPhpSystemAutoLoader(string $class): void
DuckPhp 核心类自加载方法

### 受保护方法

    protected function getNamespacePath(string $sub_path, string $main_path): string
获取完整命名空间路径，处理绝对路径和相对路径

## 相关链接

- [DuckPhp\Core\ComponentBase](Core-ComponentBase.md)
- [DuckPhp\Core\App](Core-App.md)
