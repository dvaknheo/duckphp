# DuckPhp\Core\SingletonTrait
[toc]

## 简介

**可变单例类**
使用 phasecontainer
## 使用于

## 方法

    public static function _($object = null)

如果传入 $object,把当前类设置为 $object。
如果默认传入空，则创建使用者类。
    
## 详解

例一
```php
class A
{
    use DuckPhp\Core\SingletonTrait;
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

B::_()->foo();
B::_(A::_());
B::_()->foo();
```
输出
```
猪年快乐
鼠年快乐
```





