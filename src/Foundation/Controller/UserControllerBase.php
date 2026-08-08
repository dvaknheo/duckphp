<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\GlobalUser\UserControllerInterface;

class UserControllerBase implements UserControllerInterface
{
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()
    {
        Helper::checkInstall(null);
        Helper::User()->checkAccess();
    }
}
