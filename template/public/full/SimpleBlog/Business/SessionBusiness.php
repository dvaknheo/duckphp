<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\Business;

use SimpleBlog\Helper\ServiceHelper;
use SimpleBlog\System\App;

class SessionBusiness extends BaseBusiness
{
    // 注意这里是有状态的，和其他 Service 不同。
    public function __construct()
    {
        App::session_start();
    }
    public function logout()
    {
        App::session_destroy();
    }
    public function getCurrentUser()
    {
        $user = isset($_SESSION['user'])?$_SESSION['user']:[];
        
        return $user;
    }
    public function getCurrentUid()
    {
        $user = isset($_SESSION['user'])?$_SESSION['user']:[];
        
        return $user['id'];
    }
    public function setCurrentUser($user)
    {
        $_SESSION['user'] = $user;
    }
    //////////////////////
    public function adminLogin()
    {
        $_SESSION['admin_logined'] = true;
    }
    public function checkAdminLogin()
    {
        return isset($_SESSION['admin_logined'])?true:false;
    }
    public function adminLogout()
    {
        unset($_SESSION['admin_logined']);
    }
    public function csrf_token()
    {
        $_SESSION['_CSRF'] = uniqid();
        return $_SESSION['_CSRF'];
    }
    public function csrf_check($token)
    {
        return isset($_SESSION['_CSRF']) && $_SESSION['_CSRF'] === $token?true:false;
    }
}
