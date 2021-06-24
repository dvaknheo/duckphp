<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Business;

use LazyToChange\Model\Base as B;
use LazyToChange\Model\DemoModel;

class DemoBusiness extends Base
{
    public function foo()
    {
        return "<" . DemoModel::G()->foo().">";
    }
}
