<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Controller;

use AdvanceDemo\Controller\DefaultAction as C;
use AdvanceDemo\Business\DemoBusiness;

class testController
{
    public function done()
    {
        $var = DemoBusiness::G()->foo();
        C::Show(get_defined_vars());
    }
}
