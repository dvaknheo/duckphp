<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace MY\Service;

use MY\Base\BaseService;
use MY\Base\Helper\ServiceHelper as S;
use MY\Model\TestModel;

class TestService extends BaseService
{
    public function foo()
    {
        return "<" . TestModel::G()->foo().">";
    }
}
