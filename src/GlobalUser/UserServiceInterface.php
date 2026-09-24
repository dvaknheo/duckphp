<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    /**
     * Same argument order as UserActionInterface::canAccess(): $url comes first.
     *
     * @param int|string $user_id
     */
    public function canAccess($user_id, ?string $url, string $class, string $method): bool;
    /**
     * @param int|string $user_id
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
