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
        ControllerHelper::checkInstall(null);
        try {
            ControllerHelper::Admin()->id(true);
            $flag = ControllerHelper::Admin()->canAccess();
            ControllerHelper::ThrowOn(!$flag, AdminException::MESSAGE_NEED_PEMISSION, AdminException::CODE_NEED_PERMISSION, AdminException::class);
        } catch (AdminException $ex) {
            $this->onLoginedException($ex);
            ControllerHelper::exit();
        }
        ControllerHelper::assignViewData('__use_logined_view_data', true);
        ControllerHelper::assignViewData('__use_logined_header_footer_file', true);
    }
    protected function onLoginedException(AdminException $ex)
    {
        if (!ControllerHelper::IsAjax()) {
            ControllerHelper::Show302(ControllerHelper::Admin()->urlForLogin());
        } else {
            ControllerHelper::ShowJson([
                'error_code' => $ex->getCode(),
                'error_message' => $ex->getMessage()
            ]);
        }
    }
}
