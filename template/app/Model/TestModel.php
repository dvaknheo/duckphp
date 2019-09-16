<?php
namespace MY\Model;

use MY\Base\Helper\BaseModel;

class TestModel extends BaseModel
{
    public function foo()
    {
        return DATE(DATE_ATOM);
    }
}
