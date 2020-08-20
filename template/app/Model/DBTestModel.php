<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace MY\Model;

use MY\Base\BaseModel;
use MY\Base\Helper\ModelHelper as M;

class DBTestModel extends BaseModel
{
    public function foo()
    {
        $sql = "select 1+? as t";
        $ret = M::DB()->fetch($sql, 2);
        return $ret;
    }
}
