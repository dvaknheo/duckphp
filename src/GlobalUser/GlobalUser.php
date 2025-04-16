<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\ZCallTrait;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalUser extends ComponentBase implements UserActionInterface
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    
    use ZCallTrait;
    public function service()
    {
        //return MyUserService::_Z();
        throw new \Exception("No Impelment");
    }
    public function id($check_login = true) : int
    {
        throw new \Exception("No Impelment");
    }
    public function name($check_login = true) : string
    {
        throw new \Exception("No Impelment");
    }
    public function login(array $post)
    {
        throw new \Exception("No Impelment");
    }
    public function logout()
    {
        throw new \Exception("No Impelment");
    }
    public function regist(array $post)
    {
        throw new \Exception("No Impelment");
    }
    ///////////////
    public function urlForLogin($url_back = null, $ext = null) : string
    {
        throw new \Exception("No Impelment");
    }
    public function urlForLogout($url_back = null, $ext = null) : string
    {
        throw new \Exception("No Impelment");
    }
    public function urlForHome($url_back = null, $ext = null) : string
    {
        throw new \Exception("No Impelment");
    }
    public function urlForRegist($url_back = null, $ext = null) : string
    {
        throw new \Exception("No Impelment");
    }
    public function batchGetUsernames($ids)
    {
        return $this->service()->batchGetUsernames($ids);
    }
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return true;
    }
}
