<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function urlForLogin($url_back = null, $ext = null);
    public function urlForLogout($url_back = null, $ext = null);
    public function urlForHome($url_back = null, $ext = null);
    public function urlForRegist($url_back = null, $ext = null);
    public function getUsernames(array $ids);
}
