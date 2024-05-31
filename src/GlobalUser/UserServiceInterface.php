<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace DuckPhp\GlobalUser;

interface UserServiceInterface
{
    public function batchGetUsernames(array $ids);
}
