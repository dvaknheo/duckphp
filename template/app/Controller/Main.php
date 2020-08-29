<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\System\Helper\ControllerHelper as C;
// use LazyToChange\System\BaseController;
use LazyToChange\Business\TestBusiness;

class Main // extends BaseController
{
    public function index()
    {
        //change if  you can
        $var = C::H(TestBusiness::G()->foo());
        C::Show(get_defined_vars(), 'main');
    }
    // change if  you can
    public function i()
    {
        phpinfo();
    }
}
