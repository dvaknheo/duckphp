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
    public function getCurrentAdmin();
    public function getCurrentAdminName();
    public function getCurrentAdminId();
}
