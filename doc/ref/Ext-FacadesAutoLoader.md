# Ext\FacadesAutoLoader

## 简介

## 选项
        'facades_namespace' => 'Facades',
        'facades_map' => [],
        'facades_enable_autoload' => true,
## 公开方法


## 详解

    public function __construct()
    public function init(array $options, object $context = null)
    public function _autoload($class)
    public function getFacadesCallback($input_class, $name)
    public function clear()
    
    ### FacadesAutoLoader

你们要的 Facades 伪静态方法
'facades_namespace'=>'Facades', // 前缀
'facades_map'=>[],

#### 示例

```php
use Facades\MY\Model\TestModel;
TestModel::foo(); // <=> \MY\Model\TestModel::G()->foo();
```



### 