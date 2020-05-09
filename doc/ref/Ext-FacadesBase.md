# Ext\FacadesBase

## 简介
`可变单例` Facades 的基类
## 依赖关系


## 选项

## 公开方法
public static function __callStatic($name, $arguments)
    
    魔术方法已经重写

## 详解


FacadesBase 类是 Facades 的基类。伪静态方法。

    
```php
use Facades\MY\Model\TestModel;
TestModel::foo(); // => \MY\Model\TestModel::G()->foo();
```
这其中，Facades\MY\Model\TestModel 就是 extends DuckPhhp\Ext\FacadesBase 的类。
然后根据类名，