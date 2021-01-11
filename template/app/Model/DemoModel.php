<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Model;

use LazyToChange\Model\BaseModel;
// use LazyToChange\Helper\ModelHelper as M;

class DemoModel extends BaseModel
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
