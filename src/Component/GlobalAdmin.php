<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\Component;

use DuckPhp\Component\ZCallTrait;
use DuckPhp\Core\ComponentBase;

class GlobalAdmin extends ComponentBase
{
    const EVENT_REGISTED = 'registed';
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    
    use ZCallTrait;
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
    ///////////////
    public function action()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function service()
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    ///////////////
    public function isSuper()
    {
        $this->checkLogin();
        return true;
    }
    public function canAccessCurrent()
    {
        $this->checkLogin();
        return true;
    }
    public function canAccessUrl($url)
    {
        $this->checkLogin();
        return true;
    }
    public function canAccessCall($class, $method)
    {
        $this->checkLogin();
        return true;
    }
    public function getUsernames($ids)
    {
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    //////////////////////
    public function urlForRegist($url_back = null, $ext = null)
    {
        //return $this->service()->urlForRegist();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    //////////////////////
    public function regist($post)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function login($post)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
    public function logout($post)
    {
        //return $this->service()->urlForLogout();
        throw new \ErrorException('DuckPhp: No Impelement');
    }
}
