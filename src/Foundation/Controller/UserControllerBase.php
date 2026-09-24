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
        ControllerHelper::User()->id(true);
        $flag = ControllerHelper::User()->canAccess();
        if (!$flag) {
            $this->onNeedPermission();
            ControllerHelper::exit();
        }

        ControllerHelper::assignViewData('__use_logined_view_data', true);
        ControllerHelper::assignViewData('__use_logined_header_footer_file', true);
    }
    protected function onNeedPermission()
    {
        if (!ControllerHelper::IsAjax()) {
            $url_back = parse_url(ControllerHelper::SERVER('REQUEST_URI', ''), PHP_URL_PATH);
            ControllerHelper::Show302(ControllerHelper::User()->urlForLogin($url_back));
        } else {
            ControllerHelper::ShowJson([
                'error_code' => -2,
                'error_message' => 'NEED_PERMISSION',
            ]);
        }
    }
}
