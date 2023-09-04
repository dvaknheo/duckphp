<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Controller;

use AdvanceDemo\Business\DemoBusiness;
use AdvanceDemo\Controller\DefaultAction as C;

class MainController extends Base
{
    public function index()
    {
        //change it if  you can
        $var = __h(DemoBusiness::G()->foo());
        C::Show(get_defined_vars(), 'main');
    }
    public function files()
    {
        C::Show(get_defined_vars(), 'files');
    }
    public function i()
    {
        phpinfo();
    }
    protected  function foo()
    {
        var_dump(DATE(DATE_ATOM));
    }
}
