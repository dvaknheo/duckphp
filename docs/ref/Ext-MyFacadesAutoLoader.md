# DuckPhp\Ext\FacadesAutoLoader
[toc]

## 简介
你们要的 Facades 伪静态方法

## 选项
全部选项

        'facades_namespace' => 'MyFacades',
前缀

        'facades_map' => [],
门面映射

        'facades_enable_autoload' => true,
启用自动加载

## 方法


public function __construct()
public function init(array $options, object $context = null)

public function _autoload($class)

public function getFacadesCallback($input_class, $name)
public function clear()

## 详解


示例

```php
use MyFacades\LazyToChange\Model\TestModel;
TestModel::foo(); // <=> \LazyToChange\Model\TestModel::G()->foo();
```
注意， ComponentBase 的方法无法这么来。