<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace SimpleAuth\Service;

use SimpleAuth\Base\BaseService;
use SimpleAuth\Base\Helper\ServiceHelper as S;
use SimpleAuth\Base\App;

class SessionService extends BaseService
{
    public function __construct()
    {
        App::session_start();
    }
    public function getCurrentUser()
    {
        $ret = $_SESSION['user'] ?? [];
        SessionServiceException::ThrowOn(empty($ret), '请重新登录');
        
        return $ret;
    }
    
    public function getCurrentUid()
    {
        $user = $this->getCurrentUser();
        return $user['id'];
    }
    
    public function setCurrentUser($user)
    {
        $_SESSION['user'] = $user;
    }
    public function logout()
    {
        unset($_SESSION['user']);
        App::session_destroy();
    }
    public function checkCsrf($token)
    {
        $session_token = $_SESSION['_token'] ?? null;
        SessionServiceException::ThrowOn($token !== $session_token, 'csrf_token 失败', 419);
    }
    ////////////////////////////////////////////////////////////////////////
    public function csrf_token()
    {
        if (!isset($_SESSION['_token'])) {
            $token = $this->randomString(40);
            $_SESSION['_token'] = $token;
        }
        return $_SESSION['_token'];
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
