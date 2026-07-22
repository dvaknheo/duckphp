<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null): void;
    public function doBatchGetUsernames(array $ids): array;
    public function doCheckAccess(int $id, string $class, string $method, ?string $url = null): void;
}
