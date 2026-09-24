<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\GlobalAdmin;

interface AdminSessionInterface
{
    public function setCurrentAdmin($admin);
    public function unsetCurrentAdmin();
    public function getCurrentAdmin(bool $check_login = false);
    public function getCurrentAdminName(bool $check_login = false);
    public function getCurrentAdminId(bool $check_login = false);
}
