<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Model;

class DemoModel extends BaseModel
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
