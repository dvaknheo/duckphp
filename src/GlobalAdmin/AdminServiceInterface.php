<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

interface AdminServiceInterface
{
    /**
     * Same argument order as AdminActionInterface::canAccess(): $url comes first.
     *
     * @param int|string $admin_id
     */
    public function canAccess($admin_id, ?string $url, string $class, string $method): bool;
    /**
     * @param int|string $admin_id
     * @param array<string, mixed> $ext
     */
    public function log($admin_id, string $string, ?string $type = null, array $ext = []);
    public function isSuper($admin_id): bool;
    //public function getCurrentUIdBreakStatusLess();
}
