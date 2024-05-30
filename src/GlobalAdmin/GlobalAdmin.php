<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class GlobalAdmin extends ComponentBase implements AdminActionInterface
{
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    const EVENT_ACCESSED = 'accessed';
    
    public function service()
    {
        throw new \Exception("No Impelment");
    }
    public function id()
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
    public function checkAccess($class, string $method, ?string $url = null)
    {
        throw new \Exception("No Impelment");
    }
    public function isSuper()
    {
        throw new \Exception("No Impelment");
    }
    ///////////////
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
}
