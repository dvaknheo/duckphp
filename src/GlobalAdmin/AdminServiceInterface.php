<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function checkAccess($admin_id, string $class, string $method, ?string $url = null);
    public function log($admin_id, string $string, ?string $type = null, array $ext = []);

    public function isSuper($admin_id): bool;
}
