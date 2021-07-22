<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\ControllerEx;

use SimpleAuth\System\App;
use SimpleAuth\System\ProjectSession;

class SessionManager extends ProjectSession
{
    /////////////////////////////////////
    public function getCurrentUser()
    {
        $ret = $this->get('user',[]);
        static::ThrowOn(empty($ret), '请重新登录');
        return $ret;
    }
    
    public function getCurrentUid()
    {
        $user = $this->getCurrentUser();
        return $user['id'];
    }
    
    public function setCurrentUser($user)
    {
        $this->set('user',$user);
    }
    public function logout()
    {
        $this->set('user',[]);
    }

    ////////////////////////////////////////////////////////////////////////
    public function csrf_token()
    {
        $token = $this->get('_token');
        if (true || !isset($token)) {
            $token = $this->randomString(40);
            $this->set('_token', $token);
        }
        return $token;
    }
    
    public function checkCsrf()
    {
        if( empty(App::POST()) ){ return ;}
        $referer = App::SERVER('HTTP_REFERER','');
        $domain = App::Domain(true).'/';
            
        if (substr($referer, 0, strlen($domain)) !== $domain) {
            static::ThrowOn(true, "CRSF", 419);
        }
        $token = App::Post('_token');
        $session_token =  $this->get('_token');
        //static::ThrowOn($token !== $session_token, "csrf_token 失败[$token !== $session_token]", 419);
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
