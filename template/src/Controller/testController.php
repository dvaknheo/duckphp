<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Controller;

use AdvanceDemo\Business\DemoBusiness;

class testController
{
    public function done()
    {
        $var = DemoBusiness::_()->foo();
        Helper::Show(get_defined_vars());
    }
}
