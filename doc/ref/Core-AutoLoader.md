# Core\AutoLoader

## 简介
`组件类` DuckPHP\AutoLoader 类是 psr-4 自动加载类

## 选项
'path' => null,

// 路径
'namespace' => 'MY',

//命名空间
'path_namespace' => 'app',

    // 命名空间的相对路径
'skip_system_autoload' => true,

    跳过系统加载
'skip_app_autoload' => false,

'enable_cache_classes_in_cli' => false,
    
    //
## 方法

public function init($options=[], $context=null)

    //
public function run()

    //
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
## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function run()
    public function _autoload($class)
    public function assignPathNamespace($input_path, $namespace = null)
    public function cacheClasses()
    public function cacheNamespacePath($path)
    public function clear()
