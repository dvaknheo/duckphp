<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
// use MY\Base\BaseController;
use MY\Service\TestService;

class Main // extends BaseController
{
    public function index()
    {
        //change if  you can
        $var = C::H(TestService::G()->foo());
        C::Show(get_defined_vars(), 'main');
    }
    // change if  you can
    public function i()
    {
        phpinfo();
    }
}
