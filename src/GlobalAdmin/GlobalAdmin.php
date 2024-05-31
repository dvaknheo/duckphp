<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Component\ZCallTrait;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalAdmin extends ComponentBase implements AdminActionInterface
{
    use ZCallTrait;
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    const EVENT_ACCESSED = 'accessed';
    
    
    public function service()
    {
        //return MyAdminService::_Z();
        throw new \Exception("No Impelment");
    }
    public function id() : int
    {
        throw new \Exception("No Impelment");
    }
    public function name()
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
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new \Exception("No Impelment");
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new \Exception("No Impelment");
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        throw new \Exception("No Impelment");
    }
    ///////////////
    public function checkAccess($class, string $method, ?string $url = null)
    {
        return $this->service()->doIsSuper($this->id(), $class, $method, $url);
    }
    public function isSuper()
    {
        return $this->service()->doIsSuper($this->id());
    }
    public function log(string $string, ?string $type = null)
    {
        return $this->service()->doIsSuper($this->id(), $string, $type);
    }
}
