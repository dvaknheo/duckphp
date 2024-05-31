<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserActionInterface
{
    public function id() : int;
    public function name() : string;
    public function service();

    public function login(array $post);
    public function logout();
    public function regist(array $post);
    
    public function urlForLogin($url_back = null, $ext = null) : string;
    public function urlForLogout($url_back = null, $ext = null) : string;
    public function urlForHome($url_back = null, $ext = null) : string;
    public function urlForRegist($url_back = null, $ext = null) : string;
}
