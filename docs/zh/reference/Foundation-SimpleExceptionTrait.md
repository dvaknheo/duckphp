# DuckPhp\Foundation\ThrowOnableTrait

## 简介

ThrowOnableTrait 提供 ThrowOn 方法，并且隐藏异常方法
ThrowOnableTrait 需要配合 G 方法使用

## 选项

## 方法
### 公开方法

    public static function ThrowOn($flag, $message, $code = 0)
如果 $flag 成立， 则抛出内置异常类

    public static function ExceptionClass($new_class = null)
获得或者设置内置异常类

## 样例
```
use DuckPhp\Ext\ThrowOnableTrait;
use DuckPhp\SingletonEx\SingletonExTrait;

class ThrowOnableTraitTest
{
    public function testAll()
    {
        ThrowOnObject::ThrowOn(false, "123");
        ThrowOnObject::ExceptionClass(BaseException::class);
        try {
            ThrowOnObject::ThrowOn(true, "Message", 2);
        } catch (\Throwable $ex) {
            echo ThrowOnObject::ExceptionClass();
        }
        
    }
}
class BaseException extends \Exception
{
}
class ThrowOnObject
{
    use SingletonExTrait;
    use ThrowOnableTrait;
}
(new ThrowOnableTraitTest())->testAll();

