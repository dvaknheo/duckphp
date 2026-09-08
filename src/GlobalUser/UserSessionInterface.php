<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalUser;

interface UserSessionInterface
{
    public function setCurrentUser($user);
    public function unsetCurrentUser();
    public function getCurrentUser();
    public function getCurrentUserName();
    public function getCurrentUserId();
}
