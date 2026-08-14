<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\GlobalUser\UserControllerInterface;
use DuckPhp\GlobalUser\UserException;

class UserControllerBase implements UserControllerInterface
{
    public function __construct()
    {
        $this->initController();
    }
    protected function initController()
    {
        Helper::checkInstall(null);
        $flag = Helper::User()->canAccess();
        if (!$flag) {
            if (!Helper::IsAjax()) {
                Helper::Show302(Helper::User()->urlForLogin());
                Helper::exit();
            } else {
                throw new UserException("can not access", -1);
            }
        }
    }
}
