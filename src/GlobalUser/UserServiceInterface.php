<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function doLog(int $user_id, string $string, ?string $type = null);
    public function doBatchGetUsernames(array $ids);
}
