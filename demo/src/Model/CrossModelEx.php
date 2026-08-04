<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Model;

use ProjectNameTemplate\Model\Base;
use ProjectNameTemplate\Model\Helper;

class CrossModelEx extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
