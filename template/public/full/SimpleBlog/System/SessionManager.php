<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use SimpleBlog\Helper\ServiceHelper;
use SimpleBlog\System\App;

class SessionManager
{
    use SingletonEx;
    
    protected $prefix = '';
    public function __construct()
    {
        App::session_start();
    }
    public function logout()
    {
        return App::SessionSet($this->prefix.'user', []);
    }
    public function getCurrentUser()
    {
        return App::SessionGet($this->prefix.'user', []);
    }
    public function getCurrentUid()
    {
        return $this->getCurrentUser()['id'] ?? null;
    }
    public function setCurrentUser($user)
    {
        App::SessionSet($this->prefix.'user', $user);
    }
    //////////////////////
    public function adminLogin()
    {
        App::SessionSet($this->prefix.'admin_logined', true);
    }
    public function checkAdminLogin()
    {
        return App::SessionGet($this->prefix.'admin_logined', false);
    }
    public function adminLogout()
    {
        App::SessionSet($this->prefix.'admin_logined', false);
        unset($_SESSION['admin_logined']);
    }
    public function csrf_token()
    {
        $ret = uniqid();
        App::SessionSet($this->prefix.'_CSRF',$ret);
        return $ret;
    }
    public function csrf_check($token)
    {
        return  App::SessionGet($this->prefix.'_CSRF', '') === $token?true:false;
    }
}
