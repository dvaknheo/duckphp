<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Controller;

use AdvanceDemo\Business\DemoBusiness;
use AdvanceDemo\Controller\Base;
use AdvanceDemo\Controller\Helper;

class MainController extends Base
{
    public function action_index()
    {
        //change it if  you can
        $var = __h(DemoBusiness::_()->foo());
        Helper::Show(get_defined_vars(), 'main');
    }
    public function action_files()
    {
        Helper::Show(get_defined_vars(), 'files');
    }
    public function action_i()
    {
        phpinfo();
    }
    protected  function action_foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
}
