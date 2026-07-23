<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\View;
use DuckPhp\GlobalUser\UserActionInterface;
use DuckPhp\GlobalUser\UserException;

final class GlobalUser extends ComponentBase implements UserActionInterface
{
    use GlobalUserTrait;
    
    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    
    public function id($check_login = true)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function name($check_login = true) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function login(array $post)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function logout(): void
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function regist(array $post)
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    ///////////////
    public function urlForLogin($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForLogout($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForHome($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    public function urlForRegist($url_back = null, $ext = null) : string
    {
        throw new UserException("No Impelment:".__METHOD__);
    }
    ///////////////
}
