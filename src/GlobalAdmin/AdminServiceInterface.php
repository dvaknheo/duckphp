<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    public function canAccess($admin_id, string $class, string $method, ?string $url = null): bool;
    /**
     * @param array<string, mixed> $ext
     */
    /**
     * @param array<string, mixed> $ext
     */
    public function log($admin_id, string $string, ?string $type = null, array $ext = []);

    public function isSuper($admin_id): bool;
    //public function getCurrentUIdBreakStatusLess();
}
