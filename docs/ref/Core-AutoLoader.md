# DuckPhp\Core\AutoLoader
[toc]

## 简介
伪`组件类` DuckPhp\AutoLoader 类是 psr-4 自动加载类，一般用 Composer 加载而不使用他

## 选项
全部选项

        'path' => '',
路径

        'namespace' => '',
命名空间

        'path_namespace' => 'app',
自动加载，命名空间的相对路径

        'skip_app_autoload' => false,
跳过 app 的加载

        'autoload_cache_in_cli' => false,
在命令行模式下开启 opcache 缓存

        'autoload_path_namespace_map' => [],
psr4 风格自动加载路径和命名空间映射

## 方法
public $is_inited = false;

    //是否已初始化
public $namespace_paths = [];

    // 路径 => 命名空间的映射表
    public function init(array $options, object $context = null)

    初始化
    public function isInited(): bool

    是否已经初始化
    public function run()

    //
    public function runAutoLoader()

    // run() 的 别名，方便调用
    public static function AutoLoad(string $class): void
    public function _Autoload(string $class):void

    //
    public function assignPathNamespace($input_path, $namespace = null)

    //
    public function cacheClasses()

    //
    public function cacheNamespacePath($path)

    //
    public function clear()

    //
    public static function DuckPhpSystemAutoLoader(string $class): void //@codeCoverageIgnoreStart
仅仅用于autoload.php 加载 DuckPhp 文件
    
    public static function G($object = null)

    public function __construct()


## 说明

AutoLoader 类用于没 autoloader 的情况下临时用。

AutoLoader 类特意被设计成和其他类没联系

这是样例里，不在 `composer.json` 加载主类而是用 AutoLoader 加载主类的例子
```php
if (!class_exists(\LazyToChange\System\App::class)) {
    \DuckPhp\DuckPhp::assignPathNamespace(__DIR__ . '/../app', "LazyToChange\\"); 
    \DuckPhp\DuckPhp::runAutoLoader();
}
```
