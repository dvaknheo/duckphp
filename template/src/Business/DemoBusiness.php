<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Business;

use ProjectNameTemplate\Business\Base;
use ProjectNameTemplate\Business\Helper;
use ProjectNameTemplate\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::_()->foo().">";
    }
}
