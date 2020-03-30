##### 6.兼容 Swoole

    如果想让你们的项目在 swoole 下也能运行，那就要加上这几点
    用 C::SG() 代替 超全局变量的 $ 前缀 如 $_GET =>  C::SG()->_GET
    
    使用以下参数格式都一样的 swoole 兼容静态方法，代替同名全局方法。
    
    C::session_start(),
    C::session_destroy(),
    C::session_id()，
    如 session_start() => C::session_start();
    
    编写 Swoole 相容的代码，还需要注意到一些写法的改动。
全局变量
```php
global $a='val'; =>  $a=C::GLOBALS('a','val');
```
静态变量 
```php
static $a='val'; =>  $a=C::STATICS('a','val');
```
类内静态变量
```php
$x=static::$abc; => $x=C::CLASS_STATICS(static::class,'abc');
```