<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Controller;

use LazyToChange\Base\Helper\ControllerHelper as C;
use LazyToChange\Service\TestService;

class test
{
    public function done()
    {
        $var = TestService::G()->foo();
        C::Show(get_defined_vars());
    }
}
