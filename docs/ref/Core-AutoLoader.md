# DuckPhp\Core\AutoLoader
[toc]

## 简介
伪`组件类` DuckPhp\AutoLoader 类是 psr-4 自动加载类

## 选项
'path' => null,

    路径
'namespace' => 'LazyToChange',

    命名空间
'path_namespace' => 'app',

    命名空间的相对路径
'skip_app_autoload' => false,

'autoload_cache_in_cli' => false,
    
    在命令行模式下缓存
'autoload_path_namespace_map' => [],

    psr4 风格列表
## 公开方法
public $is_inited = false;

    //是否已初始化
public $namespace_paths = [];

    // 路径 => 命名空间的映射表
public function init($options=[], $context=null)

    初始化
public function isInited(): bool

    
public function run()

    //
public function runAutoLoader()

    // run() 的 别名，方便调用
public function _autoload($class)

    //
public function assignPathNamespace($input_path, $namespace=null)

    //
public function cacheClasses()

    //
public function cacheNamespacePath($path)

    //
public function clear()

    //
public static function DuckPhpSystemAutoLoader(string $class): void



    仅仅用于
## 详解

AutoLoader 类用于没 autoloader 的情况下临时用。

AutoLoader 类特意被设计成和其他类没联系
