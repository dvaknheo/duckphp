# DuckPhp\ThrowOn\ThrowOnTrait
[toc]

## 简介
快速抛出异常的 trait，用于异常类扩充

## 方法
public static function ThrowOn($flag, $message, $code=0)

    如果 $flag成立，则抛出异常
## 详解

trait ThrowOn 是为了写代码更偷懒。


## 例子
```
class MyException extends \Exception
{
}
class SystemException extends \Exception
{
    use \DuckPhp\ThrowOn\ThrowOnTrait;
}


SystemException::ThrowOn(true,"something exception",142857);
// 丢出异常。

SystemException::ThrowTo(MyException::class);
// 当你要接管 SystemException 的错误的时候，丢出 MyException 异常
SystemException::ThrowOn(true,"something exception",142857);

```

ThrowOnTrait 的弊病是多了一层堆栈。调试的时候要注意。

