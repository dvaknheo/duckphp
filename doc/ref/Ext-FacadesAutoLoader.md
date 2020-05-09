# Ext\FacadesAutoLoader
[toc]
## 简介
你们要的 Facades 伪静态方法

## 选项
'facades_namespace' => 'Facades',

    前缀
'facades_map' => [],
'facades_enable_autoload' => true,
## 方法


public function __construct()
public function init(array $options, object $context = null)

public function _autoload($class)

public function getFacadesCallback($input_class, $name)
public function clear()

## 详解


示例

```php
use Facades\MY\Model\TestModel;
TestModel::foo(); // <=> \MY\Model\TestModel::G()->foo();
```
