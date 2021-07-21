<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace LazyToChange\System;

use DuckPhp\SingletonEx\SingletonExTrait;

class SessionManager
{
    use SingletonExTrait;
    
    public $prefix ='';
    public function __construct()
    {
        App::session_start();
    }
    //扩充你的类
}
