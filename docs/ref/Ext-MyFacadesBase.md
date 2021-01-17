# DuckPhp\Ext\MyFacadesBase
[toc]

## 简介
MyFacadesAutoLoader 子类 的基类
## 选项

## 方法


public function __construct()
public function init(array $options, object $context = null)

public function _autoload($class)

public function getFacadesCallback($input_class, $name)
public function clear()

## 详解


示例

```php
use Facades\LazyToChange\Model\TestModel;
TestModel::foo(); // <=> \LazyToChange\Model\TestModel::G()->foo();
```
注意， ComponentBase 的方法无法这么来。