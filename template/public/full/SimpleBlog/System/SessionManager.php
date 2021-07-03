<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleBlog\System;

use DuckPhp\Core\App;
use DuckPhp\Core\ComponentBase;

class SessionManager extends ComponentBase
{
    public $options = [
        'session_prefix' => '',
    ];
    public function __construct()
    {
        App::session_start();
    }
    public function get($key, $defuault)
    {
        App::SessionGet($this->options['session_prefix'] . $key, $default);
    }
    public function set($key, $value)
    {
        App::SessionSet($this->options['session_prefix'] . $key, $value);
    }
    public function unset($key)
    {
        App::SessionUnset($this->options['session_prefix'] . $key);
    }
    /////////////////////////////////////
    public function logout()
    {
        return $this->set('user', []);
    }
    public function getCurrentUser()
    {
        return $this->get('user', []);
    }
    public function getCurrentUid()
    {
        return $this->getCurrentUser()['id'] ?? null;
    }
    public function setCurrentUser($user)
    {
        $this->set('user', $user);
    }
    //////////////////////
    public function adminLogin()
    {
        $this->set('admin_logined', true);
    }
    public function checkAdminLogin()
    {
        return $this->get('admin_logined', false);
    }
    public function adminLogout()
    {
        $this->unset('admin_logined');
    }
    public function csrf_token()
    {
        $ret = uniqid();
        $this->set('_CSRF',$ret);
        return $ret;
    }
    public function csrf_check($token)
    {
        return  $this->get('_CSRF', '') === $token?true:false;
    }
}
