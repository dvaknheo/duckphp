<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Core\ComponentBase;

class GlobalUser extends ComponentBase
{
    public static function CallInPhase($phase)
    {
        return new PhaseProxy($phase, static::class);
    }
    public function checkLogin()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function current()
    {
        $this->checkLogin();
        return new \stdClass();
    }
    public function id()
    {
        $this->checkLogin();
        return $this->data['id'] ?? 0; /** @phpstan-ignore-line */
    }
    public function data()
    {
        $this->checkLogin();
        return $this->data ?? []; /** @phpstan-ignore-line */
    }
    ////////////
    public function action()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function service()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    ////////////
    public function getUsernames($ids)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
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
    public function urlForHome($url_back = null, $ext = null)
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
    public function logout($post)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
}
