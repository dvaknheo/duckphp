<?php
//if your don't need, remove this file;

namespace MY\Service;

use MY\Base\BaseService;
//use MY\Base\Helper\ServiceHelper as S;
use MY\Model\TestModel;

class TestService extends BaseService
{
    public function foo()
    {
        return "<" . TestModel::G()->foo().">";
    }
}
