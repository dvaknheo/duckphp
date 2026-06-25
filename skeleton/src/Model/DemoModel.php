<?php declare(strict_types=1);
/**
 * DuckPhp
 */
namespace YourProjectName\Model;

class DemoModel extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }

}
