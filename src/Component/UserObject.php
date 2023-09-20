<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\SingletonEx\SingletonExTrait;

class UserObject
{
    use SingletonExTrait;
    public function __construct()
    {
        throw new \Exception('No Impelement');
    }
    public function id()
    {
        return 0;
    }
    public function isSuper()
    {
        return false;
    }
}
