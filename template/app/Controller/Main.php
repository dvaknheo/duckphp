<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

// use LazyToChange\Controller\BaseController;
use LazyToChange\Business\TestBusiness;
use LazyToChange\Helper\ControllerHelper as C;

class Main // extends BaseController
{
    public function index()
    {
        //change it if  you can
        $var = C::H(TestBusiness::G()->foo());
        C::Show(get_defined_vars(), 'main');
    }
    // change it  if  you can
    public function i()
    {
        phpinfo();
    }
}
