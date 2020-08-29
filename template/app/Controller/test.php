<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\System\Helper\ControllerHelper as C;
use LazyToChange\Business\TestBusiness;

class test
{
    public function done()
    {
        $var = TestBusiness::G()->foo();
        C::Show(get_defined_vars());
    }
}
