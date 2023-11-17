<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\SingletonTrait;

class UserSystem
{
    use SingletonTrait;
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
    public function current()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function id()
    {
        return $this->current()->id();
    }
    public function data()
    {
        return $this->current()->data();
    }
    public function urlForRegist($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function regist($post)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function login($post)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function logout()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
}
