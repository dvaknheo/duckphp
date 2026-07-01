# DuckPhp\Ext\MyFacadesBase
[toc]

## 简介
MyFacadesAutoLoader 子类 的基类
## 选项

无选项
## 方法

    public function __construct()
构造方法

    public static function __callStatic($name, $arguments)
静态调用方法



## 详解


示例

```php
use Facades\LazyToChange\Model\TestModel;
TestModel::foo(); // <=> \LazyToChange\Model\TestModel::G()->foo();
```
注意， ComponentBase 的方法无法这么来。

