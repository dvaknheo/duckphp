# ThrowOn

## 简介

这是 [Core\ThrowOn](Core-ThrownOn.md) 的快捷类


为什么使用快捷类？为了使得关系清晰。
## 方法
public static function ThrowOn($flag, $message, $code=0, $exception_class=null)

    如果 $flag成立，则抛出异常
    如果未指定 $exception_class，则判断当前类是否是 Exception 类的子类，并抛出。
    如果不是，则默认为 Exception 类，并抛出
## 详解

trait ThrowOn 是为了写代码更偷懒。


## 例子
```
class MyClass extends \Exception
{
    use \DuckPhp\ThrowOn;
}
class X
{
    use \DuckPhp\ThrowOn;
}
MyException::ThrowOn(true,"something exception",142857);

X::ThrowOn(true,"second",MyException::class);

X::ThrowOn(true,"thr",22,MyException::class);

```

ThrowOn 的弊病是多了一层堆栈。调试的时候要注意。


