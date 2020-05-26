<?php declare(strict_types=1);
/**
 * DuckPHP
 * From this time, you never be alone~
 */
namespace MY\Service;

use MY\Base\BaseService;
use MY\Model as M;

class TestService extends BaseService
{
    public function foo()
    {
        return M\TestModel::G()->foo();
    }
}
