<?php declare(strict_types=1);
/**
 *  /test/done
 */
namespace YourProjectName\Controller;

use YourProjectName\Business\DemoBusiness;

class testController
{
    public function done()
    {
        $var = DemoBusiness::_()->foo();
        Helper::Show(get_defined_vars());
    }
}
