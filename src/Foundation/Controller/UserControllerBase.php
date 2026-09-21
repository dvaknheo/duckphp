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
        try {
            Helper::User()->id(true);
            $flag = Helper::User()->canAccess();
            Helper::ThrowOn(!$flag, UserException::MESSAGE_NEED_PEMISSION, UserException::CODE_NEED_PERMISSION, UserException::class);
        } catch (UserException $ex) {
            $this->onLoginedException($ex);
            Helper::exit();
        }
        Helper::assignViewData('__logined_enable_view', true);
        Helper::assignViewData('__logined_enable_header_footer', true);
    }
    protected function onLoginedException(UserException $ex)
    {
        if (!Helper::IsAjax()) {
            Helper::Show302(Helper::User()->urlForLogin());
        } else {
            Helper::ShowJson([
                'error_code' => $ex->getCode(),
                'error_message' => $ex->getMessage()
            ]);
        }
    }
}
