<?php
namespace Project\Service;

use Project\Base\BaseService;
use Project\Base\Helper\ServiceHelper as S;
use Project\Base\App;

class SessionService extends BaseService
{
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        return App::SG()->_SESSION['user']??[];
    }
    public function setCurrentUser($user)
    {
        App::SG()->_SESSION['user']=$user;
    }
    public function getRegisterInfo()
    {
        $olds=App::SG()->_SESSION['reg_olds']??[];
        $errors=App::SG()->_SESSION['reg_errors']??[];
        
        return [$olds,$errors];
    }
    public function setRegisterInfo($olds,$errors)
    {
        App::SG()->_SESSION['reg_olds']=$olds;
        App::SG()->_SESSION['reg_errors']=$errors;
    }
    public function getLoginInfo()
    {
        $olds=App::SG()->_SESSION['reg_olds']??[];
        $errors=App::SG()->_SESSION['reg_errors']??[];
        
        return [$olds,$errors];
    }
    public function setLoginInfo($olds,$errors)
    {
        App::SG()->_SESSION['reg_olds']=$olds;
        App::SG()->_SESSION['reg_errors']=$errors;
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