<?php
namespace MY\Service;

use MY\Base\BaseService;
//use MY\Base\Helper\BaseService;
use MY\Model\TestModel;

class TestService extends BaseService
{
    public function foo()
    {
        return "<" . TestModel::G()->foo().">";
    }
}
