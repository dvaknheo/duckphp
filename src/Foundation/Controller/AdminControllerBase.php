<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\GlobalAdmin\AdminControllerInterface;
use DuckPhp\GlobalAdmin\AdminException;

class AdminControllerBase implements AdminControllerInterface
{
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()
    {
        Helper::checkInstall(null);
        $flag = Helper::Admin()->canAccess();
        if (!$flag) {
            if (!Helper::IsAjax()) {
                Helper::Show302(Helper::Admin()->urlForLogin());
                Helper::exit();
            } else {
                throw new AdminException("can not access", -1);
            }
        }
    }
}
