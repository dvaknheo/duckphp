<?php
namespace UserSystemDemo\Service;

use UserSystemDemo\Base\BaseService;
use UserSystemDemo\Base\Helper\ServiceHelper as S;
use UserSystemDemo\Base\App;

class SessionService extends BaseService
{
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        $ret = App::SG()->_SESSION['user']??[];
        SessionServiceException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
    
    public function getCurrentUid()
    {
        $user=$this->getCurrentUser();
        return $user['id'];
    }
    
    public function setCurrentUser($user)
    {
        App::SG()->_SESSION['user']=$user;
    }
    public function logout()
    {
        unset(App::SG()->_SESSION['user']);
        App::session_destroy();
    }
    ////////////////////////////////////////////////////////////////////////
    public function csrf_token()
    {
        //$this->put('_token', Str::random(40));
        if(!isset(App::SG()->_SESSION['_token'])){
            $token=$this->randomString(40);
            App::SG()->_SESSION['_token']=$token;
        }
        return App::SG()->_SESSION['_token'];
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