<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\SingletonTrait;

class GlobalUser
{
    use SingletonTrait;
    public function __construct()
    {
        throw new \Exception('No Impelement');
    }
    public function id()
    {
        return 0;
    }
    public function data()
    {
        return [];
    }
    public function logoutUrl($ext)
    {
        return '';
    }
    public function nick()
    {
        return $this->username();
    }
    public function username()
    {
        return '';
    }
}
