<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Controller;

use ProjectNameTemplate\Business\DemoBusiness;

class testController
{
    public function action_done()
    {
        $var = DemoBusiness::_()->foo();
        Helper::Show(get_defined_vars());
    }
}
