<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

// use LazyToChange\Controller\BaseController;
use LazyToChange\Business\DemoBusiness;
use LazyToChange\Helper\ControllerHelper as C;

class Main // extends BaseController
{
    public function index()
    {
        //change it if  you can
        $var = __h(DemoBusiness::_()->foo());
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
