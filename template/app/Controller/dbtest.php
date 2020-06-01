<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace MY\Controller;

use MY\Base\App;
use MY\Base\Helper\ModelHelper as M;

class dbtest
{
    public function main()
    {
        $ret = $this->foo();
        var_dump($ret);
    }
    public function foo()
    {
        $sql = "select 1+? as t";
        $ret = M::DB()->fetch($sql, 2);
        return $ret;
    }
}
