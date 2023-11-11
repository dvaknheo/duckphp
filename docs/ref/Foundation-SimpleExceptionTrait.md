# DuckPhp\ThrowOn\ThrowOnTrait
[toc]

## 简介
快速抛出异常的 trait，用于异常类扩充

## 方法

trait ThrowOnTrait 是为了写代码更偷懒，提供了三个静态方法:

    public static function ThrowOn($flag, $message, $code = 0)

这个方法用于如果 $flag 成立，则抛出当前异常类

PHP 有个函数 assert ， ThrowOn 和他逻辑相反。ThrowOn的方式会更直接些

    public static function Handle($class)

把本来 $class ThrowOn 到本类的异常 ， Throw 到当前异常类。

这个方法的作用是用于提供第三方异常类的时候。让人无缝处理异常类。

    public static function Proxy($ex)

相当于 `throw new static($ex->getMessage, $ex->getCode());`

用于把其他异常转成自己异常
## 例子
```php
class MyException extends \Exception
{
}
class SystemException extends \Exception
{
    use \DuckPhp\ThrowOn\ThrowOnTrait;
}


SystemException::ThrowOn(true,"something exception",142857);
// 丢出异常。

MyException::Handle(SystemException::class);
// 当你接管 SystemException 的错误的时候，丢出 MyException 异常
SystemException::ThrowOn(true,"something exception",142857);

```

ThrowOnTrait 的弊病是多了一层堆栈。调试的时候要注意。

ThrowOnTrait 非协程安全



