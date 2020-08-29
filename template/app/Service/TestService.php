<?php declare(strict_types=1);
/**
 * DuckPhp
 * From this time, you never be alone~
 */

namespace LazyToChange\Service;

use LazyToChange\Base\BaseService;
use LazyToChange\Base\Helper\ServiceHelper as S;
use LazyToChange\Model\TestModel;

class TestService extends BaseService
{
    public function foo()
    {
        return "<" . TestModel::G()->foo().">";
    }
}
