<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */
namespace ProjectNameTemplate\Model;

use ProjectNameTemplate\Model\Base;
use ProjectNameTemplate\Model\Helper;

class DemoModel extends Base
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
    public function testdb()
    {
        $sql = "select 1+? as t";
        $ret = Helper::Db()->fetch($sql, 2);
        return $ret;
    }
}
