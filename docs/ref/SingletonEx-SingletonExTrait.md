# DuckPhp\SingletonEx\SingletonExTrait
[toc]

## 简介

**可变单例类**

## 使用于

## 方法

    public static function G($object = null)

如果传入 $object,把当前类设置为 $object。
如果默认传入空，则创建使用者类。
    
## 详解

例一
```php
class A
{
    use DuckPhp\SingletonEx\SingletonExTrait;
    public function foo()
    {
        echo "猪年快乐\n";
    }
}
class B extends A
{
    public function foo()
    {
        echo "鼠年快乐\n";
    }
}

B::G()->foo();
B::G(A::G());
B::G()->foo();
```
输出
```
猪年快乐
鼠年快乐
```
### 宏 __SINGLETONEX_REPALACER 的作用

如果你定义了宏 `__SINGLETONEX_REPALACER` 则 G 函数 不是使用默认实新，而是 返回 `(__SINGLETONEX_REPALACER)($class, $object)`

SwooleHttpd 通过这个方法，实现了 协程单例。

这个宏也可以用于特殊调试的场合， 如 StrictCheck 就用到了 __SINGLETONEX_REPALACER

以下类内嵌实现了  SingletonExTrait  ， 目的是少层关联，或者是最终可单提出来使用

1. DuckPhp\Core\AutoLoader

2. DuckPhp\Core\ComponentBase
3. DuckPhp\Component\Console




