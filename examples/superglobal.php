<?php
use DNMVCS\SwooleHttpd as DN;
require (__DIR__.'/../autoload.php');

global $n;
// =>
$n=&DN::GLOBALS('n');

static $n;
// =>
$n=&DN::STATICS('n');  //别漏掉了 &

$n++; //=>1
var_dump($n);

class B
{
    protected static $var=10;
    public static function foo()
    {
        //static::$var++;
        //var_dump(static::$var);
        $_=&DN::CLASS_STATICS(static::class,'var');$_   ++;
        // 把 static::$var 替换成  $_=&DN::CLASS_STATICS(static::class,'var');$_
        //别漏掉了 &
        var_dump(DN::CLASS_STATICS(static::class,'var')); // 没等号或 ++ -- 之类非左值不用 &
    }
}
class C extends B
{
    protected static $var=100;
}
C::foo();C::foo();C::foo();
