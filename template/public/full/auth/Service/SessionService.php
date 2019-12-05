<?php
namespace Project\Service;

use Project\Base\BaseService;
use Project\Base\Helper\ServiceHelper as S;
use Project\Base\App;

// 这个类用于管理所有 session
class SessionService extends BaseService
{
    public function getCurrentUser()
    {
        
    }
    public function setCurrentUser($user)
    {
        //
    }
    public function getRegisterOldInfo()
    {
    }
    public function getRegisterErrors()
    {
    }
    public function getLoginOldInfo()
    {
    }
    public function getLoginErrors()
    {
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