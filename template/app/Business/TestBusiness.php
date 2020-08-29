<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Business;

use LazyToChange\System\BaseBusiness;
use LazyToChange\System\Helper\BusinessHelper as B;
use LazyToChange\Model\TestModel;

class TestBusiness extends BaseBusiness
{
    public function foo()
    {
        return "<" . TestModel::G()->foo().">";
    }
}
