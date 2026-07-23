<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

use DuckPhp\Component\GlobalEvent;
use DuckPhp\Component\PhaseProxy;
use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;
use DuckPhp\Core\View;
use DuckPhp\GlobalAdmin\AdminActionInterface;
use DuckPhp\GlobalAdmin\AdminException;

final class GlobalAdmin extends ComponentBase implements AdminActionInterface
{
    use GlobalAdminTrait;

    const EVENT_LOGINED = 'logined';
    const EVENT_LOGOUTED = 'logouted';
    const EVENT_ACCESSED = 'accessed';
    
    public function id($check_login = true)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function name($check_login = true): string
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function login(array $post):array
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function logout(): void
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogin($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForLogout($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
    public function urlForHome($url_back = null, $ext = null)
    {
        throw new AdminException("No Impelment:".__METHOD__);
    }
}
