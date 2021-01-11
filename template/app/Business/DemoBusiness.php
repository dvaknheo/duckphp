<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Business;
use LazyToChange\Helper\BusinessHelper as B;

use LazyToChange\Model\DemoModel;

class DemoBusiness extends BaseBusiness
{
    public function foo()
    {
        return "<" . DemoModel::G()->foo().">";
    }
}
