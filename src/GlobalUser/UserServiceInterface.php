<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function canAccess($user_id, string $class, string $method, ?string $url = null): bool;
    /**
     * @param array<string, mixed> $ext
     */
    public function log($user_id, string $string, ?string $type = null, array $ext = []);

    /**
     * @param array<string, mixed> $ids
     * @param array<string, mixed> $ids
     */
    public function batchGetUsernames(array $ids): array;
    //public function getCurrentUIdBreakStatusLess();
}
