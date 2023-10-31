<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace AdvanceDemo\Model;

use AdvanceDemo\Model\Base;
use AdvanceDemo\Model\Helper;

class CrossModelEx extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
