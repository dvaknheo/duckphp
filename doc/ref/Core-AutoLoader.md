# Core\AutoLoader

## 简介

DuckPHP 的自动加载类

## 选项
'path' => null,
'namespace' => 'MY',
'path_namespace' => 'app',

'skip_system_autoload' => true,
'skip_app_autoload' => false,

'enable_cache_classes_in_cli' => false,
## 公开方法

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

