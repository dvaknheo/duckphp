<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

interface AdminActionInterface
{
    public function id($check_login = true);
    public function name($check_login = true);
    public function service();
    public function localService();

    public function login(array $post);
    public function logout();

    public function urlForLogin($url_back = null, $ext = null);
    public function urlForLogout($url_back = null, $ext = null);
    public function urlForHome($url_back = null, $ext = null);

    public function checkAccess($class, string $method, ?string $url = null);
    public function log(string $string, ?string $type = null);
    
    public function getHeaderFooterData(array $input): array;
    public function mergeView(array $data, bool $with_set_head_foot = true, ?string $header = null, ?string $footer = null): array;

    public function isSuper();
}
