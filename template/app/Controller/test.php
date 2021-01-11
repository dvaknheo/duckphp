<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\Helper\ControllerHelper as C;
use LazyToChange\Business\DemoBusiness;

class test
{
    public function done()
    {
        $var = DemoBusiness::G()->foo();
        C::Show(get_defined_vars());
    }
}
