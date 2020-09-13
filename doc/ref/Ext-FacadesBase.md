# DuckPhp\Ext\FacadesBase

## 简介
伪`组件类` Facades 的基类
## 依赖关系


## 选项

## 公开方法
public static function __callStatic($name, $arguments)
    
    魔术方法已经重写

## 详解


FacadesBase 类是 Facades 的基类。伪静态方法。

    
```php
use Facades\LazyToChange\Model\TestModel;
TestModel::foo(); // => \LazyToChange\Model\TestModel::G()->foo();
```
这其中，Facades\LazyToChange\Model\TestModel 就是 extends DuckPhhp\Ext\FacadesBase 的类。
