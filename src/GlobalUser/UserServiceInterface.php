<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function checkAccess($user_id, string $class, string $method, ?string $url = null);
    public function log($user_id, string $string, ?string $type = null, array $ext =[]);

    public function batchGetUsernames(array $ids): array;
}
