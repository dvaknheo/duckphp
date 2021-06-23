<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\System;

use DuckPhp\SingletonEx\SingletonExTrait;
use SimpleAuth\System\App;

class SessionManager
{
    use SingletonExTrait;
    
    public $prefix ='';
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        $ret = App::SessionGet($this->prefix.'user',[]);;
        SessionException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
    
    public function getCurrentUid()
    {
        $user = $this->getCurrentUser();
        return $user['id'];
    }
    
    public function setCurrentUser($user)
    {
        App::SessionSet($this->prefix.'user',$user);
    }
    public function logout()
    {
        App::SessionSet($this->prefix.'user',[]);

    }

    public function getExceptionClass()
    {
        return SessionException::class;
    }
    
    ////////////////////////////////////////////////////////////////////////
    public function csrf_token()
    {
        $token = App::SessionGet($this->prefix.'_token');
        if (!isset($token)) {
            $token = $this->randomString(40);
           App::SessionGet($this->prefix.'_token', $token);
        }
        return $token;
    }
    
    public function checkCsrf()
    {
        if( empty(App::POST()) ){ return ;}
        $referer = App::SERVER('HTTP_REFERER','');
        $domain = App::Domain(true).'/';
            
        if (substr($referer, 0, strlen($domain)) !== $domain) {
            SessionException::ThrowOn(true, "CRSF", 419);
            //防止 csrf 攻击，用于站内无跳板的简单情况
        }
        $token = App::Post('_token');
        $session_token =  App::SessionGet($this->prefix.'_token');
        SessionException::ThrowOn($token !== $session_token, 'csrf_token 失败', 419);
    }
    public function isCsrfException($ex)
    {
        return is_a($ex,SessionException::class) && $ex->getCode=419;
    }
    

    public function csrf_field()
    {
        return '<input type="hidden" name="_token" value="'.$this->csrf_token().'">';
    }
    protected function randomString($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }
}
