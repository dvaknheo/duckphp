<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace DuckPhp\Foundation\Controller;

use DuckPhp\Core\App;
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
        try {
            Helper::Admin()->id(true);
            $flag = Helper::Admin()->canAccess();
            Helper::ThrowOn(!$flag, AdminException::MESSAGE_NEED_PEMISSION, AdminException::CODE_NEED_PERMISSION, AdminException::class);
        } catch (AdminException $ex) {
            $this->onLoginedException($ex);
            Helper::exit();
        }
        Helper::assignViewData('__logined_enable_view', true);
        Helper::assignViewData('__logined_enable_header_footer', true);
    }
    protected function onLoginedException(AdminException $ex)
    {
        if (!Helper::IsAjax()) {
            Helper::Show302(Helper::Admin()->urlForLogin());
        } else {
            Helper::ShowJson([
                'error_code' => $ex->getCode(),
                'error_message' => $ex->getMessage()
            ]);
        }
    }
}
