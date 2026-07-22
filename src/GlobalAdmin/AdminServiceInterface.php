<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function doCheckAccess(int $admin_id, string $class, string $method, ?string $url = null): void;
    public function doIsSuper(int $admin_id): bool;
    public function doLog(int $admin_id, string $string, ?string $type = null): void;
}
