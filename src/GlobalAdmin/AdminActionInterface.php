<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

interface AdminActionInterface
{
    public function id();
    public function name();
    public function login(array $post);
    public function logout(array $post);
    public function checkAccess($class, string $method, ?string $url = null);
    public function isSuper();
}
