<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace MY\Controller;

use MY\Base\Helper\ControllerHelper as C;
use MY\Service\TestService;

class test
{
    public function done()
    {
        $var = TestService::G()->foo();
        C::Show(get_defined_vars());
    }
}
