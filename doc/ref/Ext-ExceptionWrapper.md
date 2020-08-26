# DuckPhp\\Ext\\WrapperWithException

## 简介
    封装服务异常
## 选项
无选项
## 方法
public static function Wrap($object)

public static function Release()

public function doWrap($object)

public function doRelease()

public function __call($method, $args)

## 详解
```php
WrapperWithExceptionObject::G(WrapperWithException::Wrap(WrapperWithExceptionObject::G()));
$x=WrapperWithExceptionObject::G()->foo();
var_dump($x);
WrapperWithExceptionObject::G(WrapperWithException::Release());
class WrapperWithExceptionObject
{
    use SingletonEx;
    public function foo()
    {
        throw new \Exception("HHH");
    }
}
```