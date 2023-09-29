<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Business;

use AdvanceDemo\Business\Base as Helper;
use AdvanceDemo\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::G()->foo().">";
    }
}
