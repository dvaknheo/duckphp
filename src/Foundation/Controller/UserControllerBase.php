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
        ControllerHelper::checkInstall(null);
        try {
            ControllerHelper::User()->id(true);
            $flag = ControllerHelper::User()->canAccess();
            ControllerHelper::ThrowOn(!$flag, UserException::MESSAGE_NEED_PEMISSION, UserException::CODE_NEED_PERMISSION, UserException::class);
        } catch (UserException $ex) {
            $this->onLoginedException($ex);
            ControllerHelper::exit();
        }
        ControllerHelper::assignViewData('__logined_enable_view', true);
        ControllerHelper::assignViewData('__logined_enable_header_footer', true);
    }
    protected function onLoginedException(UserException $ex)
    {
        if (!ControllerHelper::IsAjax()) {
            ControllerHelper::Show302(ControllerHelper::User()->urlForLogin());
        } else {
            ControllerHelper::ShowJson([
                'error_code' => $ex->getCode(),
                'error_message' => $ex->getMessage()
            ]);
        }
    }
}
